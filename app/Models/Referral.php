<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'admission_id',
        'referral_type',
        'facility_name',
        'clinical_reason',
        'transport_arranged',
        'status',
        'authorized_by',
        'referred_at',
    ];

    protected function casts(): array
    {
        return [
            'transport_arranged' => 'boolean',
            'referred_at' => 'datetime',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function scopeEmergencyIcu($query)
    {
        return $query->where('referral_type', 'icu_ccu_emergency');
    }
}
