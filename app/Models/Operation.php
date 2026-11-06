<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operation extends Model
{
    use HasFactory;

    protected $fillable = [
        'operation_number',
        'patient_id',
        'admission_id',
        'procedure_name',
        'department_id',
        'ot_room',
        'urgency',
        'indication',
        'scheduled_at',
        'estimated_duration_minutes',
        'scheduled_end_at',
        'started_at',
        'ended_at',
        'primary_surgeon_id',
        'assistant_surgeon_id',
        'requested_by',
        'anesthetist_name',
        'ot_nurse_name',
        'operation_notes',
        'post_op_notes',
        'rescheduled_reason',
        'rescheduled_at',
        'rescheduled_by',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'scheduled_end_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'rescheduled_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'estimated_duration_minutes' => 'integer',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function primarySurgeon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_surgeon_id');
    }

    public function assistantSurgeon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assistant_surgeon_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function rescheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rescheduled_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OperationHistory::class)->orderBy('created_at', 'desc');
    }

    public function postOpObservations(): HasMany
    {
        return $this->hasMany(PostOpObservation::class)->orderBy('observed_at', 'desc');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['cancelled']);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->whereIn('status', ['scheduled', 'rescheduled']);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeForSurgeon(Builder $query, int $surgeonId): Builder
    {
        return $query->where('primary_surgeon_id', $surgeonId);
    }

    public function scopeForRoom(Builder $query, string $room): Builder
    {
        return $query->where('ot_room', $room);
    }

    public function scopeForDepartment(Builder $query, int $departmentId): Builder
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeOnDate(Builder $query, string|Carbon $date): Builder
    {
        $d = $date instanceof Carbon ? $date->toDateString() : $date;
        return $query->whereDate('scheduled_at', $d);
    }

    // Helpers & Accessors
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => '<span class="badge bg-primary"><i class="bi bi-calendar-event me-1"></i>Scheduled</span>',
            'rescheduled' => '<span class="badge bg-warning text-dark"><i class="bi bi-calendar-range me-1"></i>Rescheduled</span>',
            'in_progress' => '<span class="badge bg-info text-dark"><i class="bi bi-heart-pulse me-1"></i>In Progress</span>',
            'completed' => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Completed</span>',
            'cancelled' => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Cancelled</span>',
            'requested' => '<span class="badge bg-secondary"><i class="bi bi-clock me-1"></i>Requested</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }

    public function getUrgencyBadgeAttribute(): string
    {
        return match ($this->urgency) {
            'emergency' => '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Emergency</span>',
            'urgent' => '<span class="badge bg-warning text-dark"><i class="bi bi-lightning-charge me-1"></i>Urgent</span>',
            default => '<span class="badge bg-light text-dark border">Elective</span>',
        };
    }

    public function getFormattedTimeWindowAttribute(): string
    {
        if (!$this->scheduled_at) {
            return 'Not scheduled';
        }

        $end = $this->scheduled_end_at ?? $this->scheduled_at->copy()->addMinutes($this->estimated_duration_minutes ?: 60);
        return $this->scheduled_at->format('d M Y, h:i A') . ' - ' . $end->format('h:i A') . ' (' . ($this->estimated_duration_minutes ?: 60) . ' mins)';
    }

    public function canBeRescheduled(): bool
    {
        return in_array($this->status, ['scheduled', 'rescheduled', 'requested']);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['scheduled', 'rescheduled', 'requested']);
    }

    public function canBeStarted(): bool
    {
        return in_array($this->status, ['scheduled', 'rescheduled']);
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'in_progress';
    }
}
