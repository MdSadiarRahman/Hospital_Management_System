<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Admission extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'patient_id',
        'admission_number',
        'admission_date',
        'discharge_date',
        'attending_doctor_id',
        'admission_reason',
        'clinical_clearance',
        'clinical_clearance_at',
        'clinical_clearance_by',
        'financial_clearance',
        'financial_clearance_at',
        'financial_clearance_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'admission_date' => 'datetime',
            'discharge_date' => 'datetime',
            'clinical_clearance' => 'boolean',
            'clinical_clearance_at' => 'datetime',
            'financial_clearance' => 'boolean',
            'financial_clearance_at' => 'datetime',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function attendingDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attending_doctor_id');
    }

    public function clinicalClearanceBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'clinical_clearance_by');
    }

    public function financialClearanceBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'financial_clearance_by');
    }

    public function bedAssignments(): HasMany
    {
        return $this->hasMany(BedAssignment::class);
    }

    public function currentBedAssignment(): HasOne
    {
        return $this->hasOne(BedAssignment::class)->whereNull('released_at');
    }

    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(TreatmentPlan::class);
    }

    public function nursingNotes(): HasMany
    {
        return $this->hasMany(NursingNote::class);
    }

    public function medicationAdministrations(): HasMany
    {
        return $this->hasMany(MedicationAdministration::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }

    public function bill(): HasOne
    {
        return $this->hasOne(Bill::class);
    }

    public function dischargeSummary(): HasOne
    {
        return $this->hasOne(DischargeSummary::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'admitted');
    }
}
