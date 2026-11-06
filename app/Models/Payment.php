<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'receipt_number',
        'amount',
        'refunded_amount',
        'payment_method',
        'transaction_ref',
        'received_by',
        'status',
        'payment_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'payment_date' => 'datetime',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }

    /**
     * Get net remaining un-refunded amount for this payment.
     */
    public function getNetAmountAttribute(): string
    {
        return bcsub((string) $this->amount, (string) ($this->refunded_amount ?? '0.00'), 2);
    }

    public function getIsFullyRefundedAttribute(): bool
    {
        return bccomp((string) $this->refunded_amount, (string) $this->amount, 2) >= 0;
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', '!=', 'refunded');
    }

    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('status', 'refunded');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $clean = trim($term);
        return $query->where(function (Builder $q) use ($clean) {
            $q->where('receipt_number', 'like', "%{$clean}%")
                ->orWhere('transaction_ref', 'like', "%{$clean}%")
                ->orWhereHas('bill', function (Builder $bq) use ($clean) {
                    $bq->where('invoice_number', 'like', "%{$clean}%")
                        ->orWhereHas('patient', function (Builder $pq) use ($clean) {
                            $pq->where('name', 'like', "%{$clean}%")
                                ->orWhere('patient_code', 'like', "%{$clean}%")
                                ->orWhere('phone', 'like', "%{$clean}%");
                        });
                });
        });
    }
}
