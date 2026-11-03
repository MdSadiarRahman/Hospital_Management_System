<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'action',
        'previous_status',
        'new_status',
        'previous_date',
        'new_date',
        'note',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'previous_date' => 'datetime',
            'new_date' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
