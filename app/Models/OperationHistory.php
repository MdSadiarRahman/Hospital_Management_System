<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'operation_id',
        'action',
        'changed_by',
        'previous_status',
        'new_status',
        'previous_scheduled_at',
        'new_scheduled_at',
        'reason',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'previous_scheduled_at' => 'datetime',
            'new_scheduled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'requested' => 'Operation Requested',
            'scheduled' => 'Operation Scheduled',
            'rescheduled' => 'Operation Rescheduled',
            'started' => 'Procedure Started',
            'completed' => 'Procedure Completed',
            'cancelled' => 'Operation Cancelled',
            'notes_recorded' => 'Surgical Notes Recorded',
            'observation_recorded' => 'Post-op Observation Added',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}

