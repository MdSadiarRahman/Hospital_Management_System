<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class NursingNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_id',
        'nurse_id',
        'shift',
        'temperature',
        'temperature_unit',
        'blood_pressure',
        'blood_pressure_unit',
        'pulse_rate',
        'pulse_rate_unit',
        'respiratory_rate',
        'respiratory_rate_unit',
        'spo2',
        'spo2_unit',
        'weight',
        'weight_unit',
        'observations',
        'is_flagged_abnormal',
        'clinical_flags',
        'clinical_review_status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:1',
            'spo2' => 'decimal:1',
            'weight' => 'decimal:2',
            'is_flagged_abnormal' => 'boolean',
            'clinical_flags' => 'array',
            'recorded_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('recorded_at', 'desc');
    }

    public function scopeFlagged(Builder $query): Builder
    {
        return $query->where('is_flagged_abnormal', true);
    }

    public function scopeNeedsReview(Builder $query): Builder
    {
        return $query->where('clinical_review_status', 'flagged_for_review');
    }

    public function scopeForShift(Builder $query, string $shift): Builder
    {
        return $query->where('shift', $shift);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(!empty($filters['admission_id']), fn($q) => $q->where('admission_id', $filters['admission_id']))
            ->when(!empty($filters['shift']), fn($q) => $q->where('shift', $filters['shift']))
            ->when(!empty($filters['status']), function ($q) use ($filters) {
                if ($filters['status'] === 'flagged') {
                    $q->where('is_flagged_abnormal', true);
                } elseif ($filters['status'] === 'normal') {
                    $q->where('is_flagged_abnormal', false);
                } elseif ($filters['status'] === 'reviewed') {
                    $q->where('clinical_review_status', 'reviewed');
                }
            })
            ->when(!empty($filters['nurse_id']), fn($q) => $q->where('nurse_id', $filters['nurse_id']))
            ->when(!empty($filters['date_from']), fn($q) => $q->whereDate('recorded_at', '>=', $filters['date_from']))
            ->when(!empty($filters['date_to']), fn($q) => $q->whereDate('recorded_at', '<=', $filters['date_to']));
    }

    /*
    |--------------------------------------------------------------------------
    | Clinical Threshold Evaluation (No automated medical diagnosis)
    |--------------------------------------------------------------------------
    */

    /**
     * Evaluate measured physiological values and extract observational alerts.
     * Note: This detects abnormal readings for physician review without diagnosing pathology.
     *
     * @return array<int, string>
     */
    public function evaluateClinicalFlags(): array
    {
        $flags = [];

        // 1. Body Temperature (°F reference)
        if ($this->temperature !== null) {
            $tempF = (float) $this->temperature;
            if ($this->temperature_unit === '°C' || $this->temperature_unit === 'C') {
                $tempF = ($tempF * 9 / 5) + 32;
            }

            if ($tempF >= 104.0) {
                $flags[] = "High Temperature (Hyperpyrexia: {$this->temperature}{$this->temperature_unit})";
            } elseif ($tempF >= 100.4) {
                $flags[] = "Elevated Temperature (Fever: {$this->temperature}{$this->temperature_unit})";
            } elseif ($tempF < 95.0) {
                $flags[] = "Low Body Temperature (Hypothermia: {$this->temperature}{$this->temperature_unit})";
            }
        }

        // 2. Blood Pressure (mmHg)
        if ($this->blood_pressure && str_contains($this->blood_pressure, '/')) {
            $parts = explode('/', $this->blood_pressure);
            $sys = (int) trim($parts[0]);
            $dia = isset($parts[1]) ? (int) trim($parts[1]) : 0;

            if ($sys >= 180 || $dia >= 110) {
                $flags[] = "Severely Elevated Blood Pressure ({$this->blood_pressure} mmHg)";
            } elseif ($sys >= 140 || $dia >= 90) {
                $flags[] = "Elevated Blood Pressure ({$this->blood_pressure} mmHg)";
            } elseif ($sys < 90 || $dia < 60) {
                $flags[] = "Low Blood Pressure ({$this->blood_pressure} mmHg)";
            }
        }

        // 3. Pulse Rate (bpm)
        if ($this->pulse_rate !== null) {
            if ($this->pulse_rate > 130) {
                $flags[] = "Marked Tachycardia ({$this->pulse_rate} bpm)";
            } elseif ($this->pulse_rate > 100) {
                $flags[] = "Elevated Pulse Rate (Tachycardia: {$this->pulse_rate} bpm)";
            } elseif ($this->pulse_rate < 50) {
                $flags[] = "Marked Bradycardia ({$this->pulse_rate} bpm)";
            } elseif ($this->pulse_rate < 60) {
                $flags[] = "Low Pulse Rate (Bradycardia: {$this->pulse_rate} bpm)";
            }
        }

        // 4. Respiratory Rate (breaths/min)
        if ($this->respiratory_rate !== null) {
            if ($this->respiratory_rate > 24) {
                $flags[] = "Elevated Respiratory Rate (Tachypnea: {$this->respiratory_rate} /min)";
            } elseif ($this->respiratory_rate < 10) {
                $flags[] = "Depressed Respiratory Rate (Bradypnea: {$this->respiratory_rate} /min)";
            }
        }

        // 5. Oxygen Saturation (SpO2 %)
        if ($this->spo2 !== null) {
            $val = (float) $this->spo2;
            if ($val < 90.0) {
                $flags[] = "Critical Hypoxia (SpO2: {$this->spo2}%)";
            } elseif ($val < 95.0) {
                $flags[] = "Reduced Oxygen Saturation (Hypoxia: {$this->spo2}%)";
            }
        }

        return $flags;
    }

    public function isFever(): bool
    {
        if ($this->temperature === null) {
            return false;
        }
        $tempF = (float) $this->temperature;
        if ($this->temperature_unit === '°C' || $this->temperature_unit === 'C') {
            $tempF = ($tempF * 9 / 5) + 32;
        }

        return $tempF >= 100.4;
    }

    public function isHypoxia(): bool
    {
        return $this->spo2 !== null && (float) $this->spo2 < 95.0;
    }

    public function isCriticalHypoxia(): bool
    {
        return $this->spo2 !== null && (float) $this->spo2 < 90.0;
    }

    public function isTachycardia(): bool
    {
        return $this->pulse_rate !== null && $this->pulse_rate > 100;
    }

    public function isBradycardia(): bool
    {
        return $this->pulse_rate !== null && $this->pulse_rate < 60;
    }

    public function hasAbnormalVitals(): bool
    {
        return $this->is_flagged_abnormal || !empty($this->evaluateClinicalFlags());
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Formatters (with Units)
    |--------------------------------------------------------------------------
    */

    public function getTemperatureDisplayAttribute(): string
    {
        return $this->temperature !== null ? "{$this->temperature} {$this->temperature_unit}" : '—';
    }

    public function getBloodPressureDisplayAttribute(): string
    {
        return $this->blood_pressure ? "{$this->blood_pressure} {$this->blood_pressure_unit}" : '—';
    }

    public function getPulseDisplayAttribute(): string
    {
        return $this->pulse_rate !== null ? "{$this->pulse_rate} {$this->pulse_rate_unit}" : '—';
    }

    public function getRespiratoryRateDisplayAttribute(): string
    {
        return $this->respiratory_rate !== null ? "{$this->respiratory_rate} {$this->respiratory_rate_unit}" : '—';
    }

    public function getSpo2DisplayAttribute(): string
    {
        return $this->spo2 !== null ? "{$this->spo2}{$this->spo2_unit}" : '—';
    }

    public function getWeightDisplayAttribute(): ?string
    {
        return $this->weight !== null ? "{$this->weight} {$this->weight_unit}" : null;
    }

    public function getFormattedVitalsAttribute(): string
    {
        $parts = [];
        if ($this->blood_pressure) {
            $parts[] = "BP: {$this->blood_pressure} {$this->blood_pressure_unit}";
        }
        if ($this->pulse_rate !== null) {
            $parts[] = "HR: {$this->pulse_rate} {$this->pulse_rate_unit}";
        }
        if ($this->temperature !== null) {
            $parts[] = "Temp: {$this->temperature} {$this->temperature_unit}";
        }
        if ($this->spo2 !== null) {
            $parts[] = "SpO2: {$this->spo2}{$this->spo2_unit}";
        }
        if ($this->respiratory_rate !== null) {
            $parts[] = "RR: {$this->respiratory_rate} {$this->respiratory_rate_unit}";
        }
        if ($this->weight !== null) {
            $parts[] = "Wt: {$this->weight} {$this->weight_unit}";
        }

        return !empty($parts) ? implode(' • ', $parts) : 'No vitals recorded';
    }

    public function getShiftLabelAttribute(): string
    {
        return match ($this->shift) {
            'morning' => 'Morning Shift (8 AM – 4 PM)',
            'evening' => 'Evening Shift (4 PM – 12 AM)',
            'night' => 'Night Shift (12 AM – 8 AM)',
            default => ucfirst($this->shift ?? 'Morning'),
        };
    }

    public function getReviewStatusBadgeClassAttribute(): string
    {
        return match ($this->clinical_review_status) {
            'normal' => 'success-subtle text-success border border-success-subtle',
            'flagged_for_review' => 'danger-subtle text-danger border border-danger-subtle',
            'reviewed' => 'info-subtle text-info border border-info-subtle',
            default => 'secondary',
        };
    }

    public function getReviewStatusLabelAttribute(): string
    {
        return match ($this->clinical_review_status) {
            'normal' => 'Normal Observation',
            'flagged_for_review' => 'Flagged for Clinical Review',
            'reviewed' => 'Reviewed by Physician',
            default => ucfirst(str_replace('_', ' ', $this->clinical_review_status ?? 'normal')),
        };
    }
}
