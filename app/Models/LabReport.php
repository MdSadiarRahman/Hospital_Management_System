<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_order_item_id',
        'file_path',
        'findings_summary',
        'parameters',
        'review_status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(LabOrderItem::class, 'lab_order_item_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePendingReview($query)
    {
        return $query->where('review_status', 'pending_review');
    }

    public function scopeReviewed($query)
    {
        return $query->where('review_status', 'reviewed');
    }
}
