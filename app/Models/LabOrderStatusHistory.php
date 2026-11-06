<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabOrderStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'lab_order_status_histories';

    protected $fillable = [
        'lab_order_id',
        'from_status',
        'to_status',
        'changed_by',
        'notes',
    ];

    public function labOrder(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getFromStatusLabelAttribute(): string
    {
        return $this->from_status ? ucwords(str_replace('_', ' ', $this->from_status)) : 'Order Created';
    }

    public function getToStatusLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->to_status));
    }
}

