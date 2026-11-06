<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LabOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'patient_id',
        'doctor_id',
        'order_number',
        'source',
        'lab_partner_id',
        'status',
        'priority',
        'total_charge',
        'clinical_indication',
    ];

    protected function casts(): array
    {
        return [
            'total_charge' => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function labPartner(): BelongsTo
    {
        return $this->belongsTo(LabPartner::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LabOrderItem::class);
    }

    public function samples(): HasMany
    {
        return $this->hasMany(LabSample::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(LabOrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeInternal(Builder $query): Builder
    {
        return $query->where('source', 'internal');
    }

    public function scopeExternal(Builder $query): Builder
    {
        return $query->where('source', 'external');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->whereIn('status', ['sample_collected', 'processing']);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(!empty($filters['q']), function (Builder $q) use ($filters) {
                $term = $filters['q'];
                $q->where(function (Builder $sub) use ($term) {
                    $sub->where('order_number', 'like', "%{$term}%")
                        ->orWhereHas('patient', function (Builder $pq) use ($term) {
                            $pq->where('name', 'like', "%{$term}%")
                               ->orWhere('patient_code', 'like', "%{$term}%")
                               ->orWhere('phone', 'like', "%{$term}%");
                        })
                        ->orWhereHas('items', function (Builder $iq) use ($term) {
                            $iq->where('test_name', 'like', "%{$term}%");
                        });
                });
            })
            ->when(!empty($filters['status']) && $filters['status'] !== 'all', fn($q) => $q->where('status', $filters['status']))
            ->when(!empty($filters['source']) && $filters['source'] !== 'all', fn($q) => $q->where('source', $filters['source']))
            ->when(!empty($filters['priority']) && $filters['priority'] !== 'all', fn($q) => $q->where('priority', $filters['priority']))
            ->when(!empty($filters['patient_id']), fn($q) => $q->where('patient_id', $filters['patient_id']))
            ->when(!empty($filters['date_from']), fn($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
            ->when(!empty($filters['date_to']), fn($q) => $q->whereDate('created_at', '<=', $filters['date_to']));
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
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
            'processing' => 'In Processing',
            'completed' => 'Results Completed',
            'cancelled' => 'Cancelled',
            default => ucwords(str_replace('_', ' ', $this->status ?? 'Pending')),
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'warning text-dark',
            'stat' => 'danger',
            default => 'secondary',
        };
    }

    public function getSourceBadgeClassAttribute(): string
    {
        return match ($this->source) {
            'internal' => 'teal',
            'external' => 'warning text-dark',
            default => 'secondary',
        };
    }

    public function getFormattedTotalChargeAttribute(): string
    {
        return '৳ ' . number_format((float) $this->total_charge, 2);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isEditable(): bool
    {
        return !in_array($this->status, ['completed', 'cancelled']);
    }
}
