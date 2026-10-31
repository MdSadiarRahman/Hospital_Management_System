<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NursingNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_id',
        'nurse_id',
        'shift',
        'temperature',
        'blood_pressure',
        'pulse_rate',
        'respiratory_rate',
        'spo2',
        'observations',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:1',
            'spo2' => 'decimal:1',
            'recorded_at' => 'datetime',
        ];
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }
}
