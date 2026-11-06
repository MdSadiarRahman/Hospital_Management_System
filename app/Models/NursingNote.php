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
        'blood_pressure',
        'pulse_rate',
        'respiratory_rate',
        'spo2',
        'observations',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:1',
            'spo2' => 'decimal:1',
            'recorded_at' => 'datetime',
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

    public function scopeForShift(Builder $query, string $shift): Builder
    {
        return $query->where('shift', $shift);
    }

    /*
    |--------------------------------------------------------------------------
    | Clinical Alerts & Helper Accessors
    |--------------------------------------------------------------------------
    */

    public function isFever(): bool
    {
        return $this->temperature !== null && (float) $this->temperature >= 100.4;
    }

    public function isHypoxia(): bool
    {
        return $this->spo2 !== null && (float) $this->spo2 < 95.0;
    }

    public function isCriticalHypoxia(): bool
    {
        return $this->spo2 !== null && (float) $this->spo2 < 92.0;
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
        return $this->isFever() || $this->isHypoxia() || $this->isTachycardia() || $this->isBradycardia();
    }

    public function getFormattedVitalsAttribute(): string
    {
        $parts = [];
        if ($this->blood_pressure) {
            $parts[] = "BP: {$this->blood_pressure} mmHg";
        }
        if ($this->pulse_rate) {
            $parts[] = "HR: {$this->pulse_rate} bpm";
        }
        if ($this->temperature) {
            $parts[] = "Temp: {$this->temperature}°F";
        }
        if ($this->spo2) {
            $parts[] = "SpO2: {$this->spo2}%";
        }
        if ($this->respiratory_rate) {
            $parts[] = "RR: {$this->respiratory_rate}/min";
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
}

