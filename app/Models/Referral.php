<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_number',
        'patient_id',
        'visit_id',
        'admission_id',
        'referral_type',
        'is_critical',
        'requires_icu',
        'facility_name',
        'destination_department',
        'receiving_doctor_contact',
        'clinical_reason',
        'emergency_assessment',
        'interventions_given',
        'vitals_snapshot',
        'referral_notes',
        'transport_arranged',
        'transport_type',
        'ambulance_driver_contact',
        'accompanying_staff',
        'oxygen_support_transit',
        'status',
        'authorized_by',
        'referred_at',
        'transferred_at',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
    ];

    protected function casts(): array
    {
        return [
            'is_critical' => 'boolean',
            'requires_icu' => 'boolean',
            'transport_arranged' => 'boolean',
            'oxygen_support_transit' => 'boolean',
            'referred_at' => 'datetime',
            'transferred_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'vitals_snapshot' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ReferralHistory::class)->orderBy('created_at', 'desc');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['cancelled']);
    }

    public function scopeEmergencyIcu(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('referral_type', 'icu_ccu_emergency')
              ->orWhere('requires_icu', true);
        });
    }

    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('is_critical', true);
    }

    public function scopeInTransit(Builder $query): Builder
    {
        return $query->where('status', 'in_transit');
    }

    public function scopeTransferred(Builder $query): Builder
    {
        return $query->where('status', 'transferred');
    }

    // Accessors & Badges
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'initiated' => '<span class="badge bg-primary"><i class="bi bi-clock-history me-1"></i>Initiated</span>',
            'in_transit' => '<span class="badge bg-warning text-dark"><i class="bi bi-truck me-1"></i>In Transit</span>',
            'transferred' => '<span class="badge bg-success"><i class="bi bi-check2-all me-1"></i>Transferred</span>',
            'completed' => '<span class="badge bg-info text-dark"><i class="bi bi-check-circle me-1"></i>Completed</span>',
            'cancelled' => '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>Cancelled</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->referral_type) {
            'icu_ccu_emergency' => '<span class="badge bg-danger"><i class="bi bi-heart-pulse-fill me-1"></i>External ICU/CCU</span>',
            'tertiary_transfer' => '<span class="badge bg-info text-dark"><i class="bi bi-hospital me-1"></i>Tertiary Medical College</span>',
            'specialist' => '<span class="badge bg-light text-dark border"><i class="bi bi-person-badge me-1"></i>Specialist Consult</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->referral_type) . '</span>',
        };
    }

    public function getCriticalBadgeAttribute(): ?string
    {
        if ($this->is_critical) {
            return '<span class="badge bg-danger text-white"><i class="bi bi-exclamation-triangle-fill me-1"></i>CRITICAL CONDITION</span>';
        }
        return null;
    }

    public function canBeDispatched(): bool
    {
        return $this->status === 'initiated';
    }

    public function canBeTransferred(): bool
    {
        return in_array($this->status, ['initiated', 'in_transit']);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['initiated', 'in_transit']);
    }
}
