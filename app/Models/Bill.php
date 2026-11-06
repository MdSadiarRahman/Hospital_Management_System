<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'admission_id',
        'visit_id',
        'invoice_number',
        'bill_date',
        'subtotal_amount',
        'discount_amount',
        'discount_reason',
        'discount_authorized_by',
        'total_amount',
        'paid_amount',
        'due_amount',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'bill_date' => 'datetime',
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function discountAuthorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'discount_authorized_by');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Calculations & Status
    |--------------------------------------------------------------------------
    */

    /**
     * Recalculate bill financial totals based on current line items and discount.
     */
    public function recalculateTotals(): void
    {
        $subtotal = '0.00';
        if ($this->relationLoaded('items')) {
            foreach ($this->items as $item) {
                $subtotal = bcadd($subtotal, (string) $item->total_price, 2);
            }
        } else {
            $subtotal = (string) ($this->subtotal_amount ?? '0.00');
        }

        $discount = (string) ($this->discount_amount ?? '0.00');
        if (bccomp($discount, $subtotal, 2) > 0) {
            $discount = $subtotal;
        }

        $total = bcsub($subtotal, $discount, 2);

        // Calculate paid amount from payments & refunds if relations are loaded
        if ($this->relationLoaded('payments')) {
            $paymentsSum = '0.00';
            foreach ($this->payments as $payment) {
                if ($payment->status !== 'refunded') {
                    $paymentsSum = bcadd($paymentsSum, (string) $payment->amount, 2);
                }
            }

            $refundsSum = '0.00';
            if ($this->relationLoaded('refunds')) {
                foreach ($this->refunds as $refund) {
                    $refundsSum = bcadd($refundsSum, (string) $refund->amount, 2);
                }
            } else {
                foreach ($this->payments as $payment) {
                    $refundsSum = bcadd($refundsSum, (string) ($payment->refunded_amount ?? '0.00'), 2);
                }
            }

            $paid = bcsub($paymentsSum, $refundsSum, 2);
            if (bccomp($paid, '0.00', 2) < 0) {
                $paid = '0.00';
            }
        } else {
            $paid = (string) ($this->paid_amount ?? '0.00');
        }

        if (bccomp($paid, $total, 2) >= 0) {
            $due = '0.00';
            $status = 'paid';
        } elseif (bccomp($paid, '0.00', 2) > 0) {
            $due = bcsub($total, $paid, 2);
            $status = 'partial';
        } else {
            $due = $total;
            $status = 'unpaid';
        }

        $this->subtotal_amount = $subtotal;
        $this->discount_amount = $discount;
        $this->total_amount = $total;
        $this->paid_amount = $paid;
        $this->due_amount = $due;
        $this->status = $status;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('status', 'unpaid');
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->where('due_amount', '>', 0);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function scopeForPatient(Builder $query, int $patientId): Builder
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $clean = trim($term);
        return $query->where(function (Builder $q) use ($clean) {
            $q->where('invoice_number', 'like', "%{$clean}%")
                ->orWhereHas('patient', function (Builder $pq) use ($clean) {
                    $pq->where('name', 'like', "%{$clean}%")
                        ->orWhere('patient_code', 'like', "%{$clean}%")
                        ->orWhere('phone', 'like', "%{$clean}%");
                });
        });
    }
}
