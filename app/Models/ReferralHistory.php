<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_id',
        'action',
        'changed_by',
        'previous_status',
        'new_status',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'initiated' => 'Emergency Referral Initiated',
            'dispatched_in_transit' => 'Patient Dispatched (In Transit)',
            'transferred' => 'Tertiary Handover Completed',
            'completed' => 'Referral Closed',
            'cancelled' => 'Referral Cancelled',
            'assessment_updated' => 'Emergency Assessment Updated',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}

