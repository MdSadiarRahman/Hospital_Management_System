<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
        'department_id',
        'admission_number',
        'admission_date',
        'discharge_date',
        'attending_doctor_id',
        'admission_reason',
        'notes',
        'clinical_clearance',
        'clinical_clearance_at',
        'clinical_clearance_by',
        'financial_clearance',
        'financial_clearance_at',
        'financial_clearance_by',
        'status',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
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
            'cancelled_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function attendingDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attending_doctor_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
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
        return $this->hasMany(BedAssignment::class)->orderByDesc('assigned_at');
    }

    public function currentBedAssignment(): HasOne
    {
        return $this->hasOne(BedAssignment::class)->whereNull('released_at')->latestOfMany();
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

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'admitted');
    }

    public function scopeDischarged(Builder $query): Builder
    {
        return $query->where('status', 'discharged');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('admission_number', 'like', "%{$term}%")
                ->orWhereHas('patient', function (Builder $pq) use ($term) {
                    $pq->where('name', 'like', "%{$term}%")
                        ->orWhere('patient_code', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                })
                ->orWhereHas('currentBedAssignment.bed', function (Builder $bq) use ($term) {
                    $bq->where('bed_number', 'like', "%{$term}%")
                        ->orWhere('room_number', 'like', "%{$term}%");
                });
        });
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(!empty($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when(!empty($filters['department_id']), fn($q) => $q->where('department_id', $filters['department_id']))
            ->when(!empty($filters['doctor_id']), fn($q) => $q->where('attending_doctor_id', $filters['doctor_id']))
            ->when(!empty($filters['date_from']), fn($q) => $q->whereDate('admission_date', '>=', $filters['date_from']))
            ->when(!empty($filters['date_to']), fn($q) => $q->whereDate('admission_date', '<=', $filters['date_to']));
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'admitted' => 'success',
            'discharged' => 'info',
            'referred_external' => 'warning',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'admitted' => 'Admitted (Inpatient)',
            'discharged' => 'Discharged',
            'referred_external' => 'Referred Out',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status ?? 'Unknown'),
        };
    }

    public function getCurrentBedAttribute(): ?Bed
    {
        return $this->currentBedAssignment?->bed;
    }

    public function getStayDurationAttribute(): string
    {
        $start = $this->admission_date ?? $this->created_at;
        $end = $this->discharge_date ?? ($this->status === 'cancelled' ? $this->cancelled_at : now());

        if (!$start) {
            return '—';
        }

        $diffInHours = (int) $start->diffInHours($end);
        $days = intdiv($diffInHours, 24);
        $hours = $diffInHours % 24;

        if ($days > 0) {
            return "{$days}d {$hours}h";
        }

        return "{$hours}h";
    }
}
