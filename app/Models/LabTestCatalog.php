<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabTestCatalog extends Model
{
    use HasFactory;

    protected $table = 'lab_test_catalog';

    protected $fillable = [
        'name',
        'code',
        'category',
        'standard_charge',
        'default_source',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'standard_charge' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(LabOrderItem::class, 'lab_test_id');
    }
}
