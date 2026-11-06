<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'bill_id',
        'refund_voucher',
        'amount',
        'refund_method',
        'transaction_ref',
        'reason',
        'approved_by',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refunded_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $clean = trim($term);
        return $query->where(function (Builder $q) use ($clean) {
            $q->where('refund_voucher', 'like', "%{$clean}%")
                ->orWhere('transaction_ref', 'like', "%{$clean}%")
                ->orWhere('reason', 'like', "%{$clean}%")
                ->orWhereHas('bill', function (Builder $bq) use ($clean) {
                    $bq->where('invoice_number', 'like', "%{$clean}%");
                });
        });
    }
}
