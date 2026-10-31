<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabSample extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_order_id',
        'sample_type',
        'barcode',
        'collected_at',
        'collected_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'collected_at' => 'datetime',
        ];
    }

    public function labOrder(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class);
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
