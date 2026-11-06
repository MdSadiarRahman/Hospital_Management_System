<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_number',
        'consultation_id',
        'advice',
        'follow_up_date',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'follow_up_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }

    public function getPatientAttribute(): ?Patient
    {
        return $this->consultation?->visit?->patient;
    }

    public function getPatientIdAttribute(): ?int
    {
        return $this->consultation?->visit?->patient_id;
    }

    public function getDoctorAttribute(): ?User
    {
        return $this->consultation?->doctor;
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'success',
            'draft' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Generate standard sequential unique prescription number: RX-YYYYMMDD-XXXX
     */
    public static function generatePrescriptionNumber(): string
    {
        $datePrefix = now()->format('Ymd');
        $prefix = "RX-{$datePrefix}-";

        return DB::transaction(function () use ($prefix) {
            $latest = self::where('prescription_number', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $sequence = 1;
            if ($latest && preg_match('/-(\d+)$/', $latest->prescription_number, $matches)) {
                $sequence = ((int) $matches[1]) + 1;
            }

            return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        });
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeForPatient($query, int $patientId)
    {
        return $query->whereHas('consultation.visit', fn($q) => $q->where('patient_id', $patientId));
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('prescription_number', 'like', "%{$term}%")
              ->orWhere('advice', 'like', "%{$term}%")
              ->orWhereHas('items', fn($iq) => $iq->where('medicine_name', 'like', "%{$term}%"))
              ->orWhereHas('consultation.visit.patient', fn($pq) => $pq->where('name', 'like', "%{$term}%")->orWhere('patient_code', 'like', "%{$term}%"))
              ->orWhereHas('consultation.doctor', fn($docQ) => $docQ->where('name', 'like', "%{$term}%"));
        });
    }
}
