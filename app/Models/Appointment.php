<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_RESCHEDULED = 'rescheduled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NO_SHOW = 'no_show';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_CONFIRMED => 'Confirmed',
        self::STATUS_RESCHEDULED => 'Rescheduled',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_NO_SHOW => 'No Show',
    ];

    protected $fillable = [
        'patient_id',
        'department_id',
        'doctor_id',
        'guest_name',
        'guest_phone',
        'guest_email',
        'appointment_date',
        'appointment_time',
        'serial_number',
        'type',
        'status',
        'reason',
        'confirmed_by',
        'confirmed_at',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'rescheduled_from_id',
        'slot_duration_minutes',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'serial_number' => 'integer',
            'slot_duration_minutes' => 'integer',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function rescheduledFrom(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'rescheduled_from_id');
    }

    public function visit(): HasOne
    {
        return $this->hasOne(Visit::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(AppointmentHistory::class)->orderBy('created_at', 'desc');
    }

    // Scopes
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeRescheduled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RESCHEDULED);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeNoShow(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_NO_SHOW);
    }

    public function scopeActiveBookings(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_RESCHEDULED]);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('appointment_date', $date);
    }

    public function scopeForDoctor(Builder $query, int $doctorId): Builder
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForPatient(Builder $query, int $patientId): Builder
    {
        return $query->where('patient_id', $patientId);
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isRescheduled(): bool
    {
        return $this->status === self::STATUS_RESCHEDULED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isNoShow(): bool
    {
        return $this->status === self::STATUS_NO_SHOW;
    }

    public function canBeConfirmed(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_RESCHEDULED], true);
    }

    public function canBeRescheduled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_RESCHEDULED], true);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_RESCHEDULED], true);
    }

    public function canBeMarkedCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_RESCHEDULED], true);
    }

    public function canBeMarkedNoShow(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_RESCHEDULED], true);
    }

    public function getPatientDisplayNameAttribute(): string
    {
        return $this->patient?->full_name ?? $this->guest_name ?? 'Walk-in Guest';
    }

    public function getPatientDisplayPhoneAttribute(): string
    {
        return $this->patient?->phone ?? $this->guest_phone ?? 'N/A';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
            self::STATUS_CONFIRMED => 'bg-success-subtle text-success border border-success-subtle',
            self::STATUS_RESCHEDULED => 'bg-info-subtle text-info border border-info-subtle',
            self::STATUS_COMPLETED => 'bg-primary-subtle text-primary border border-primary-subtle',
            self::STATUS_CANCELLED => 'bg-danger-subtle text-danger border border-danger-subtle',
            self::STATUS_NO_SHOW => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
            default => 'bg-light text-dark',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }
}
