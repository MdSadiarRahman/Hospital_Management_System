<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'action_url',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes & Actions
    |--------------------------------------------------------------------------
    */

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function markAsRead(): bool
    {
        return $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'appointment' => 'info',
            'admission' => 'primary',
            'bed_alert' => 'warning text-dark',
            'lab_ready' => 'teal',
            'billing' => 'success',
            'discharge' => 'danger',
            default => 'secondary',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'appointment' => 'calendar-check',
            'admission' => 'hospital',
            'bed_alert' => 'door-open',
            'lab_ready' => 'clipboard2-pulse',
            'billing' => 'cash-stack',
            'discharge' => 'box-arrow-right',
            default => 'bell',
        };
    }
}
