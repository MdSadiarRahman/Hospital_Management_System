<?php

namespace App\Http\Requests\Operation;

use Illuminate\Foundation\Http\FormRequest;

class StorePostOpObservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['doctor', 'nurse', 'administrator', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'observed_at' => ['nullable', 'date'],
            'blood_pressure_systolic' => ['nullable', 'integer', 'between:40,260'],
            'blood_pressure_diastolic' => ['nullable', 'integer', 'between:30,160'],
            'heart_rate' => ['nullable', 'integer', 'between:30,220'],
            'respiratory_rate' => ['nullable', 'integer', 'between:8,60'],
            'temperature_celsius' => ['nullable', 'numeric', 'between:30.0,45.0'],
            'spo2_percent' => ['nullable', 'integer', 'between:50,100'],
            'consciousness_level' => ['required', 'in:alert,drowsy,stupor,unconscious'],
            'pain_score' => ['nullable', 'integer', 'between:0,10'],
            'surgical_site_condition' => ['nullable', 'string', 'max:255'],
            'drain_output_ml' => ['nullable', 'integer', 'min:0'],
            'urine_output_ml' => ['nullable', 'integer', 'min:0'],
            'observation_notes' => ['required', 'string', 'min:5'],
            'discharge_criteria_met' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'observation_notes.required' => 'Clinical recovery observation notes are required.',
            'consciousness_level.required' => 'Patient consciousness status is mandatory.',
        ];
    }
}

