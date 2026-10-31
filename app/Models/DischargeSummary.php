<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DischargeSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_id',
        'discharge_diagnosis',
        'treatment_given',
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
}
