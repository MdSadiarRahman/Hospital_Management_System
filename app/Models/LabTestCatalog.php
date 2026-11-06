<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LabTestCatalog extends Model
{
    use HasFactory;

    protected $table = 'lab_test_catalog';

    protected $fillable = [
        'name',
        'code',
        'category',
        'description',
        'standard_charge',
        'sample_type',
        'turnaround_time',
        'instructions',
        'default_source',
        'lab_partner_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'standard_charge' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function orderItems(): HasMany
    {
        return $this->hasMany(LabOrderItem::class, 'lab_test_id');
    }

    public function labPartner(): BelongsTo
    {
        return $this->belongsTo(LabPartner::class);
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        return $category ? $query->where('category', $category) : $query;
    }

    public function scopeSource(Builder $query, ?string $source): Builder
    {
        return $source ? $query->where('default_source', $source) : $query;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('code', 'like', "%{$term}%")
              ->orWhere('category', 'like', "%{$term}%")
              ->orWhere('sample_type', 'like', "%{$term}%");
        });
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(!empty($filters['q']), fn($q) => $q->search($filters['q']))
            ->when(!empty($filters['category']) && $filters['category'] !== 'all', fn($q) => $q->where('category', $filters['category']))
            ->when(!empty($filters['source']) && $filters['source'] !== 'all', fn($q) => $q->where('default_source', $filters['source']))
            ->when(isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== 'all', function ($q) use ($filters) {
                $q->where('is_active', (bool) $filters['status']);
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Alias for standard_charge to support both 'price' and 'standard_charge'.
     */
    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->standard_charge,
            set: fn($value) => ['standard_charge' => $value],
        );
    }

    public function getFormattedPriceAttribute(): string
    {
        return '৳ ' . number_format((float) $this->standard_charge, 2);
    }

    public function getAvailabilityLabelAttribute(): string
    {
        return match ($this->default_source) {
            'internal' => 'Internal Lab',
            'external' => 'Outsourced / External',
            'both' => 'Internal & External',
            default => ucfirst($this->default_source ?? 'Internal'),
        };
    }

    public function getAvailabilityBadgeClassAttribute(): string
    {
        return match ($this->default_source) {
            'internal' => 'teal',
            'external' => 'warning text-dark',
            'both' => 'info text-dark',
            default => 'secondary',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }
}
