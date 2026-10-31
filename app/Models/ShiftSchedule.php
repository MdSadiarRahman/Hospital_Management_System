<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_profile_id',
        'department_id',
        'shift_date',
        'shift_type',
        'start_time',
        'end_time',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'shift_date' => 'date',
        ];
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->where('shift_date', $date);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('shift_date', '>=', now()->toDateString())
            ->orderBy('shift_date', 'asc')
            ->orderBy('start_time', 'asc');
    }

    public function scopeForDepartment(Builder $query, int $departmentId): Builder
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeForStaff(Builder $query, int $staffProfileId): Builder
    {
        return $query->where('staff_profile_id', $staffProfileId);
    }

    public function getFormattedTimeRangeAttribute(): string
    {
        $start = Carbon::parse($this->start_time)->format('h:i A');
        $end = Carbon::parse($this->end_time)->format('h:i A');

        return "{$start} - {$end}";
    }

    public function getShiftLabelAttribute(): string
    {
        return match ($this->shift_type) {
            'morning' => 'Morning Shift (8 AM - 4 PM)',
            'evening' => 'Evening Shift (4 PM - 12 AM)',
            'night' => 'Night Shift (12 AM - 8 AM)',
            default => ucfirst($this->shift_type),
        };
    }
}
