<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Operation extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_id',
        'procedure_name',
        'scheduled_at',
        'started_at',
        'ended_at',
        'primary_surgeon_id',
        'assistant_surgeon_id',
        'anesthetist_name',
        'operation_notes',
        'post_op_notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function primarySurgeon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_surgeon_id');
    }

    public function assistantSurgeon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assistant_surgeon_id');
    }
}
