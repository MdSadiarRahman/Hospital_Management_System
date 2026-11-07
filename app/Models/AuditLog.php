<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Immutable model guard & automatic sanitization of sensitive values.
     */
    protected static function booted(): void
    {
        static::creating(function (AuditLog $log) {
            $log->old_values = static::sanitizeValues($log->old_values);
            $log->new_values = static::sanitizeValues($log->new_values);

            if (!$log->created_at) {
                $log->created_at = now();
            }
        });

        static::updating(function () {
            throw new \RuntimeException('Security Violation: Audit logs are strictly immutable and cannot be updated.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Security Violation: Audit logs are permanent records and cannot be deleted.');
        });
    }

    /**
     * Recursively redact passwords, tokens, pins, and credentials from audit payloads.
     */
    public static function sanitizeValues(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $sensitivePatterns = '/^(password|password_confirmation|token|remember_token|secret|access_token|refresh_token|api_key|pin|cvv|card_number|credit_card)$/i';

        $sanitized = [];
        foreach ($values as $key => $val) {
            if (is_string($key) && preg_match($sensitivePatterns, $key)) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($val)) {
                $sanitized[$key] = static::sanitizeValues($val);
            } else {
                $sanitized[$key] = $val;
            }
        }

        return $sanitized;
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

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes & Helpers
    |--------------------------------------------------------------------------
    */

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (!empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['auditable_type'])) {
            $query->where('auditable_type', $filters['auditable_type']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('event', 'like', $search)
                    ->orWhere('ip_address', 'like', $search)
                    ->orWhere('auditable_type', 'like', $search)
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    });
            });
        }

        return $query;
    }

    public function getEventBadgeClassAttribute(): string
    {
        $event = strtolower($this->event);

        if (str_contains($event, 'delete') || str_contains($event, 'cancel') || str_contains($event, 'failed') || str_contains($event, 'refund')) {
            return 'danger';
        }

        if (str_contains($event, 'create') || str_contains($event, 'registered') || str_contains($event, 'login') || str_contains($event, 'approved')) {
            return 'success';
        }

        if (str_contains($event, 'transfer') || str_contains($event, 'update') || str_contains($event, 'price')) {
            return 'warning text-dark';
        }

        if (str_contains($event, 'clearance') || str_contains($event, 'discharge')) {
            return 'info';
        }

        return 'secondary';
    }

    public function getFormattedAuditableNameAttribute(): string
    {
        if (!$this->auditable_type) {
            return 'System';
        }

        $parts = explode('\\', $this->auditable_type);
        $basename = end($parts);

        return $basename . ($this->auditable_id ? " #{$this->auditable_id}" : '');
    }
}
