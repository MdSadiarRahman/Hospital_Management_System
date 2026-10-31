<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'appointment_id',
        'visit_number',
        'visit_date',
        'visit_type',
        'triage_level',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function consultation(): HasOne
    {
        return $this->hasOne(Consultation::class);
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class);
    }

    public function admission(): HasOne
    {
        return $this->hasOne(Admission::class)->latestOfMany();
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function referral(): HasOne
    {
        return $this->hasOne(Referral::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }

    public function getIsOutpatientAttribute(): bool
    {
        return $this->admissions()->doesntExist();
    }

    public function getIsInpatientAttribute(): bool
    {
        return $this->admissions()->exists();
    }

    public function scopeEmergency($query)
    {
        return $query->where('visit_type', 'emergency');
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('visit_number', 'like', "%{$term}%")
              ->orWhereHas('patient', function ($pq) use ($term) {
                  $pq->where('name', 'like', "%{$term}%")
                     ->orWhere('patient_code', 'like', "%{$term}%")
                     ->orWhere('phone', 'like', "%{$term}%");
              });
        });
    }

    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['visit_type'])) {
            $query->where('visit_type', $filters['visit_type']);
        }

        if (!empty($filters['triage_level'])) {
            $query->where('triage_level', $filters['triage_level']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('visit_date', $filters['date']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('visit_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('visit_date', '<=', $filters['date_to']);
        }

        return $query;
    }
}
