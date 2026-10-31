<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'patient_id',
        'order_number',
        'source',
        'lab_partner_id',
        'status',
        'clinical_indication',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function labPartner(): BelongsTo
    {
        return $this->belongsTo(LabPartner::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LabOrderItem::class);
    }

    public function samples(): HasMany
    {
        return $this->hasMany(LabSample::class);
    }

    public function scopeInternal($query)
    {
        return $query->where('source', 'internal');
    }

    public function scopeExternal($query)
    {
        return $query->where('source', 'external');
    }
}
