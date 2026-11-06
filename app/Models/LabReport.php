<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LabReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_order_item_id',
        'source',
        'report_status',
        'lab_partner_id',
        'external_reference_number',
        'uploaded_by',
        'uploaded_at',
        'file_path',
        'original_file_name',
        'file_type',
        'file_size',
        'findings_summary',
        'parameters',
        'doctor_notes',
        'is_critical',
        'review_status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'uploaded_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'is_critical' => 'boolean',
            'file_size' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(LabOrderItem::class, 'lab_order_item_id');
    }

    public function labPartner(): BelongsTo
    {
        return $this->belongsTo(LabPartner::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(LabReportHistory::class)->latest('created_at');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('review_status', 'pending_review');
    }

    public function scopeReviewed(Builder $query): Builder
    {
        return $query->where('review_status', 'reviewed');
    }

    public function scopeInternal(Builder $query): Builder
    {
        return $query->where('source', 'internal');
    }

    public function scopeExternal(Builder $query): Builder
    {
        return $query->where('source', 'external');
    }

    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('is_critical', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */

    public function getPatientAttribute(): ?Patient
    {
        return $this->orderItem?->labOrder?->patient;
    }

    public function getDoctorAttribute(): ?User
    {
        return $this->orderItem?->labOrder?->doctor;
    }

    public function getTestNameAttribute(): string
    {
        return $this->orderItem?->test_name ?? 'Diagnostic Test';
    }

    public function isReviewed(): bool
    {
        return $this->review_status === 'reviewed';
    }

    public function hasFile(): bool
    {
        return !empty($this->file_path);
    }

    public function isPdf(): bool
    {
        return $this->file_type === 'application/pdf' || str_ends_with(strtolower($this->file_path ?? ''), '.pdf');
    }

    public function isImage(): bool
    {
        return in_array($this->file_type, ['image/jpeg', 'image/png', 'image/webp'])
            || preg_match('/\.(jpg|jpeg|png|webp)$/i', $this->file_path ?? '') === 1;
    }

    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        if ($this->file_size < 1024) {
            return $this->file_size . ' B';
        } elseif ($this->file_size < 1048576) {
            return round($this->file_size / 1024, 1) . ' KB';
        } else {
            return round($this->file_size / 1048576, 2) . ' MB';
        }
    }

    public function getSourceBadgeClassAttribute(): string
    {
        return match ($this->source) {
            'internal' => 'bg-teal text-white',
            'external' => 'bg-warning text-dark',
            default => 'bg-secondary text-white',
        };
    }

    public function getReportStatusBadgeClassAttribute(): string
    {
        return match ($this->report_status) {
            'received' => 'bg-info text-white',
            'reviewed' => 'bg-success text-white',
            default => 'bg-secondary text-white',
        };
    }

    public function getReviewStatusBadgeClassAttribute(): string
    {
        return match ($this->review_status) {
            'pending_review' => 'bg-warning text-dark',
            'reviewed' => 'bg-success text-white',
            default => 'bg-secondary text-white',
        };
    }

    public function getReportStatusLabelAttribute(): string
    {
        return match ($this->report_status) {
            'received' => 'Report Received',
            'reviewed' => 'Report Reviewed',
            default => ucfirst($this->report_status ?? 'Received'),
        };
    }

    public function getReviewStatusLabelAttribute(): string
    {
        return match ($this->review_status) {
            'pending_review' => 'Pending Medical Review',
            'reviewed' => 'Medically Verified',
            default => ucwords(str_replace('_', ' ', $this->review_status ?? 'Pending Review')),
        };
    }
}
