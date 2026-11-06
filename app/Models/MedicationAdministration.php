<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MedicationAdministration extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_id',
        'prescription_id',
        'prescription_item_id',
        'medicine_name',
        'dose_given',
        'route',
        'scheduled_time',
        'scheduled_shift',
        'nurse_id',
        'status',
        'withheld_reason',
        'remarks',
        'is_corrected',
        'correction_reason',
        'corrected_by',
        'corrected_at',
        'administered_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_time' => 'datetime',
            'administered_at' => 'datetime',
            'corrected_at' => 'datetime',
            'is_corrected' => 'boolean',
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

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function prescriptionItem(): BelongsTo
    {
        return $this->belongsTo(PrescriptionItem::class);
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    public function correctedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
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
        return $query->orderBy('administered_at', 'desc');
    }

    public function scopeAdministered(Builder $query): Builder
    {
        return $query->where('status', 'administered');
    }

    public function scopeMissed(Builder $query): Builder
    {
        return $query->where('status', 'missed');
    }

    public function scopeWithheld(Builder $query): Builder
    {
        return $query->where('status', 'withheld');
    }

    public function scopeRefused(Builder $query): Builder
    {
        return $query->where('status', 'refused');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(!empty($filters['admission_id']), fn($q) => $q->where('admission_id', $filters['admission_id']))
            ->when(!empty($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when(!empty($filters['shift']), fn($q) => $q->where('scheduled_shift', $filters['shift']))
            ->when(!empty($filters['nurse_id']), fn($q) => $q->where('nurse_id', $filters['nurse_id']))
            ->when(!empty($filters['date_from']), fn($q) => $q->whereDate('administered_at', '>=', $filters['date_from']))
            ->when(!empty($filters['date_to']), fn($q) => $q->whereDate('administered_at', '<=', $filters['date_to']));
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers & Accessors
    |--------------------------------------------------------------------------
    */

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'administered' => 'success',
            'missed' => 'danger',
            'withheld' => 'warning text-dark',
            'refused' => 'danger-subtle text-danger border border-danger-subtle',
            'omitted' => 'secondary',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'administered' => 'Administered (Given)',
            'missed' => 'Missed Dose',
            'withheld' => 'Withheld (Clinical Hold)',
            'refused' => 'Refused by Patient',
            'omitted' => 'Omitted (Skipped)',
            default => ucfirst($this->status ?? 'Administered'),
        };
    }

    public function isGiven(): bool
    {
        return $this->status === 'administered';
    }

    public function isWithheldOrMissed(): bool
    {
        return in_array($this->status, ['withheld', 'missed', 'refused', 'omitted']);
    }
}
