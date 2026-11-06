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
        'prescription_item_id',
        'medicine_name',
        'dose_given',
        'nurse_id',
        'status',
        'remarks',
        'administered_at',
    ];

    protected function casts(): array
    {
        return [
            'administered_at' => 'datetime',
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

    public function prescriptionItem(): BelongsTo
    {
        return $this->belongsTo(PrescriptionItem::class);
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
        return $query->orderBy('administered_at', 'desc');
    }

    public function scopeAdministered(Builder $query): Builder
    {
        return $query->where('status', 'administered');
    }

    public function scopeOmitted(Builder $query): Builder
    {
        return $query->where('status', 'omitted');
    }

    public function scopeRefused(Builder $query): Builder
    {
        return $query->where('status', 'refused');
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
            'omitted' => 'warning text-dark',
            'refused' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'administered' => 'Administered',
            'omitted' => 'Omitted (Skipped)',
            'refused' => 'Refused by Patient',
            default => ucfirst($this->status ?? 'Administered'),
        };
    }
}

