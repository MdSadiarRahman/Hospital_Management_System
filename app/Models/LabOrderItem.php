<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LabOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_order_id',
        'lab_test_id',
        'lab_sample_id',
        'test_name',
        'test_charge',
        'status',
        'instructions',
    ];

    protected function casts(): array
    {
        return [
            'test_charge' => 'decimal:2',
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

    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTestCatalog::class, 'lab_test_id');
    }

    public function sample(): BelongsTo
    {
        return $this->belongsTo(LabSample::class, 'lab_sample_id');
    }

    public function report(): HasOne
    {
        return $this->hasOne(LabReport::class);
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
            'pending' => 'warning text-dark',
            'sample_collected' => 'info text-dark',
            'processing' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Awaiting Sample',
            'sample_collected' => 'Sample Collected',
            'processing' => 'Processing in Lab',
            'completed' => 'Test Completed',
            'cancelled' => 'Cancelled',
            default => ucwords(str_replace('_', ' ', $this->status ?? 'Pending')),
        };
    }

    public function getFormattedChargeAttribute(): string
    {
        return '৳ ' . number_format((float) $this->test_charge, 2);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
