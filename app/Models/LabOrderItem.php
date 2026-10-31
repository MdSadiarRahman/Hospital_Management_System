<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LabOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_order_id',
        'lab_test_id',
        'test_name',
        'test_charge',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'test_charge' => 'decimal:2',
        ];
    }

    public function labOrder(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class);
    }

    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTestCatalog::class, 'lab_test_id');
    }

    public function report(): HasOne
    {
        return $this->hasOne(LabReport::class);
    }
}
