<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'department_id',
        'designation',
        'specialization',
        'license_number',
        'shift',
        'employment_type',
        'joining_date',
        'qualification',
        'salary',
        'nid_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'address',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'salary' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Primary department assigned to the staff member.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Multi-department allocation where authorized.
     */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'department_staff')
            ->withPivot(['is_primary', 'role_in_department'])
            ->withTimestamps();
    }

    /**
     * Shift schedules and history for this staff member.
     */
    public function shiftSchedules(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class);
    }

    public function upcomingShifts(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class)
            ->where('shift_date', '>=', now()->toDateString())
            ->orderBy('shift_date', 'asc')
            ->orderBy('start_time', 'asc');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isDoctor(): bool
    {
        return $this->user ? $this->user->hasRole('doctor') : false;
    }

    public function isNurse(): bool
    {
        return $this->user ? $this->user->hasRole('nurse') : false;
    }
}
