<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabReportHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_report_id',
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

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function labReport(): BelongsTo
    {
        return $this->belongsTo(LabReport::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers & Accessors
    |--------------------------------------------------------------------------
    */

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'result_entered' => 'Results Entered',
            'report_uploaded' => 'Report File Uploaded',
            'file_replaced' => 'Report File Replaced',
            'doctor_reviewed' => 'Doctor Medical Review',
            'amended' => 'Report Amended',
            default => ucwords(str_replace('_', ' ', $this->action)),
        };
    }

    public function getActionBadgeClassAttribute(): string
    {
        return match ($this->action) {
            'result_entered' => 'bg-teal text-white',
            'report_uploaded' => 'bg-info text-white',
            'file_replaced' => 'bg-warning text-dark',
            'doctor_reviewed' => 'bg-success text-white',
            'amended' => 'bg-danger text-white',
            default => 'bg-secondary text-white',
        };
    }
}

