<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DischargeSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_id',
        'discharge_diagnosis',
        'treatment_given',
        'hospital_course',
        'investigations_summary',
        'condition_at_discharge',
        'discharge_medications',
        'follow_up_instructions',
        'authorized_by',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }
}
