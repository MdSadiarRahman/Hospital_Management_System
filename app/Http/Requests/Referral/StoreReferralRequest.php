<?php

namespace App\Http\Requests\Referral;

use Illuminate\Foundation\Http\FormRequest;

class StoreReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['doctor', 'administrator', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['nullable', 'exists:patients,id'],
            'admission_id' => ['nullable', 'exists:admissions,id'],
            'visit_id' => ['nullable', 'exists:visits,id'],
            'facility_name' => ['required', 'string', 'max:191'],
            'destination_department' => ['nullable', 'string', 'max:100'],
            'referral_type' => ['required', 'in:icu_ccu_emergency,specialist,tertiary_transfer'],
            'is_critical' => ['nullable', 'boolean'],
            'requires_icu' => ['nullable', 'boolean'],
            'clinical_reason' => ['required', 'string', 'min:5'],
            'emergency_assessment' => ['nullable', 'string'],
            'interventions_given' => ['nullable', 'string'],
            'receiving_doctor_contact' => ['nullable', 'string', 'max:191'],
            'blood_pressure_systolic' => ['nullable', 'integer', 'between:40,260'],
            'blood_pressure_diastolic' => ['nullable', 'integer', 'between:30,160'],
            'heart_rate' => ['nullable', 'integer', 'between:30,220'],
            'respiratory_rate' => ['nullable', 'integer', 'between:8,60'],
            'temperature_celsius' => ['nullable', 'numeric', 'between:30.0,45.0'],
            'spo2_percent' => ['nullable', 'integer', 'between:50,100'],
            'consciousness_level' => ['nullable', 'in:alert,voice,pain,unresponsive,drowsy,stupor,unconscious'],
            'gcs_score' => ['nullable', 'integer', 'between:3,15'],
            'airway_status' => ['nullable', 'in:patent,compromised,intubated,oxygen_needed'],
            'transport_arranged' => ['nullable', 'boolean'],
            'transport_type' => ['nullable', 'string', 'max:100'],
            'ambulance_driver_contact' => ['nullable', 'string', 'max:100'],
            'accompanying_staff' => ['nullable', 'string', 'max:191'],
            'oxygen_support_transit' => ['nullable', 'boolean'],
            'referral_notes' => ['nullable', 'string'],
            'referred_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'facility_name.required' => 'Destination tertiary hospital name is mandatory.',
            'clinical_reason.required' => 'Clinical reason for external referral / ICU transfer is required.',
            'clinical_reason.min' => 'Clinical reason must be at least 5 characters.',
            'referral_type.required' => 'Please select the referral classification type.',
        ];
    }
}

