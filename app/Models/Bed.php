<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bed extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'bed_number',
        'room_number',
        'category',
        'daily_charge',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'daily_charge' => 'decimal:2',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(BedAssignment::class);
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(BedAssignment::class)->whereNull('released_at');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(BedStatusHistory::class)->latest('created_at');
    }

    /* ---------------- Scopes ---------------- */

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    public function scopeReserved($query)
    {
        return $query->where('status', 'reserved');
    }

    public function scopeCleaning($query)
    {
        return $query->where('status', 'cleaning');
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    public function scopeGeneral($query)
    {
        return $query->where(function ($q) {
            $q->where('category', 'general')
              ->orWhereNull('department_id');
        });
    }

    public function scopeDepartmentWise($query, $departmentId = null)
    {
        if ($departmentId) {
            return $query->where('department_id', $departmentId);
        }
        return $query->whereNotNull('department_id');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('bed_number', 'like', "%{$term}%")
              ->orWhere('room_number', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhereHas('department', function ($dq) use ($term) {
                  $dq->where('name', 'like', "%{$term}%")
                     ->orWhere('code', 'like', "%{$term}%");
              });
        });
    }

    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['department_id'])) {
            if ($filters['department_id'] === 'general') {
                $query->general();
            } else {
                $query->where('department_id', $filters['department_id']);
            }
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    /* ---------------- Helpers & Accessors ---------------- */

    public function getIsGeneralAttribute(): bool
    {
        return $this->category === 'general' || is_null($this->department_id);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'available' => 'success',
            'occupied' => 'danger',
            'reserved' => 'warning text-dark',
            'cleaning' => 'info text-white',
            'maintenance' => 'secondary',
            default => 'light text-dark',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'general' => 'General Bed',
            'department' => 'Department Bed',
            'cabin' => 'Private Cabin',
            'post_op' => 'Post-Operative Recovery',
            'emergency' => 'Emergency Observation',
            default => ucfirst(str_replace('_', ' ', $this->category)),
        };
    }
}
