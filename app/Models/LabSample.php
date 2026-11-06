<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LabSample extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_order_id',
        'sample_type',
        'barcode',
        'collected_at',
        'collected_by',
        'status',
        'notes',
        'received_at',
        'received_by',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'collected_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function labOrder(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class);
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(LabOrderItem::class, 'lab_sample_id');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers & Accessors
    |--------------------------------------------------------------------------
    */

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'collected' => 'info text-dark',
            'received' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'collected' => 'Collected (Phlebotomy)',
            'received' => 'Received in Lab',
            'rejected' => 'Sample Rejected',
            default => ucfirst($this->status ?? 'Collected'),
        };
    }
}
