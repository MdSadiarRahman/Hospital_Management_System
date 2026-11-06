<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostOpObservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'operation_id',
        'recorded_by',
        'observed_at',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'heart_rate',
        'respiratory_rate',
        'temperature_celsius',
        'spo2_percent',
        'consciousness_level',
        'pain_score',
        'surgical_site_condition',
        'drain_output_ml',
        'urine_output_ml',
        'observation_notes',
        'discharge_criteria_met',
    ];

    protected function casts(): array
    {
        return [
            'observed_at' => 'datetime',
            'temperature_celsius' => 'decimal:1',
            'discharge_criteria_met' => 'boolean',
            'pain_score' => 'integer',
            'blood_pressure_systolic' => 'integer',
            'blood_pressure_diastolic' => 'integer',
            'heart_rate' => 'integer',
            'respiratory_rate' => 'integer',
            'spo2_percent' => 'integer',
            'drain_output_ml' => 'integer',
            'urine_output_ml' => 'integer',
        ];
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getBloodPressureAttribute(): ?string
    {
        if ($this->blood_pressure_systolic && $this->blood_pressure_diastolic) {
            return "{$this->blood_pressure_systolic}/{$this->blood_pressure_diastolic} mmHg";
        }
        return null;
    }
}

