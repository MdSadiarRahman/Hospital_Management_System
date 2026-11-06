<?php

namespace App\Http\Requests\Referral;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['doctor', 'nurse', 'administrator', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'emergency_assessment' => ['required', 'string', 'min:5'],
            'interventions_given' => ['nullable', 'string'],
            'referral_notes' => ['nullable', 'string'],
            'blood_pressure_systolic' => ['nullable', 'integer', 'between:40,260'],
            'blood_pressure_diastolic' => ['nullable', 'integer', 'between:30,160'],
            'heart_rate' => ['nullable', 'integer', 'between:30,220'],
            'respiratory_rate' => ['nullable', 'integer', 'between:8,60'],
            'temperature_celsius' => ['nullable', 'numeric', 'between:30.0,45.0'],
            'spo2_percent' => ['nullable', 'integer', 'between:50,100'],
            'consciousness_level' => ['nullable', 'in:alert,voice,pain,unresponsive,drowsy,stupor,unconscious'],
        ];
    }

    public function messages(): array
    {
        return [
            'emergency_assessment.required' => 'Emergency clinical assessment findings are mandatory.',
        ];
    }
}

