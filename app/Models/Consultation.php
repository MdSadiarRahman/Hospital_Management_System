<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'doctor_id',
        'chief_complaints',
        'clinical_notes',
        'status',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'finalized_at' => 'datetime',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class);
    }

    public function prescription(): HasOne
    {
        return $this->hasOne(Prescription::class);
    }

    public function labOrders(): HasMany
    {
        return $this->hasMany(LabOrder::class);
    }

    public function auditLogs(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }

    public function getPatientAttribute(): ?Patient
    {
        return $this->visit?->patient;
    }

    public function getIsFinalizedAttribute(): bool
    {
        return $this->status === 'finalized';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'finalized' => 'success',
            'draft' => 'warning',
            default => 'secondary',
        };
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeFinalized($query)
    {
        return $query->where('status', 'finalized');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('chief_complaints', 'like', "%{$term}%")
              ->orWhere('clinical_notes', 'like', "%{$term}%")
              ->orWhereHas('diagnoses', fn($dq) => $dq->where('condition_name', 'like', "%{$term}%")->orWhere('icd_code', 'like', "%{$term}%"))
              ->orWhereHas('visit.patient', fn($pq) => $pq->where('name', 'like', "%{$term}%")->orWhere('patient_code', 'like', "%{$term}%"))
              ->orWhereHas('doctor', fn($docQ) => $docQ->where('name', 'like', "%{$term}%"));
        });
    }
}
