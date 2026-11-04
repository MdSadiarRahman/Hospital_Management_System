<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class BedAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_id',
        'bed_id',
        'assigned_at',
        'released_at',
        'transfer_reason',
        'assigned_by',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function patient(): HasOneThrough
    {
        return $this->hasOneThrough(
            Patient::class,
            Admission::class,
            'id',
            'id',
            'admission_id',
            'patient_id'
        );
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('released_at');
    }
}
