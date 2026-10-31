<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_clinical',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_clinical' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function staffProfiles(): HasMany
    {
        return $this->hasMany(StaffProfile::class);
    }

    /**
     * Multi-department staff allocation.
     */
    public function assignedStaff(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(StaffProfile::class, 'department_staff')
            ->withPivot(['is_primary', 'role_in_department'])
            ->withTimestamps();
    }

    /**
     * Shift schedules assigned in this department.
     */
    public function shiftSchedules(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class);
    }

    public function beds(): HasMany
    {
        return $this->hasMany(Bed::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeClinical($query)
    {
        return $query->where('is_clinical', true);
    }
}
