<?php

namespace App\Http\Requests\Nurse;

use Illuminate\Foundation\Http\FormRequest;

class StoreVitalsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->hasRole(['nurse', 'super_admin', 'administrator', 'doctor']) || $user->hasPermission('nursing.record'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shift' => ['required', 'string', 'in:morning,evening,night'],
            'temperature' => ['nullable', 'numeric', 'between:90.0,110.0'],
            'blood_pressure' => ['nullable', 'string', 'max:15', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'pulse_rate' => ['nullable', 'integer', 'between:30,250'],
            'respiratory_rate' => ['nullable', 'integer', 'between:5,60'],
            'spo2' => ['nullable', 'numeric', 'between:50.0,100.0'],
            'observations' => ['required', 'string', 'min:3', 'max:2000'],
            'recorded_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'shift.required' => 'Nursing shift is required (morning, evening, or night).',
            'temperature.between' => 'Body temperature must be between 90.0°F and 110.0°F.',
            'blood_pressure.regex' => 'Blood pressure must be in Systolic/Diastolic format (e.g. 120/80).',
            'pulse_rate.between' => 'Pulse rate must be between 30 and 250 beats per minute.',
            'respiratory_rate.between' => 'Respiratory rate must be between 5 and 60 breaths per minute.',
            'spo2.between' => 'Oxygen saturation (SpO2) must be between 50.0% and 100.0%.',
            'observations.required' => 'Bedside nursing observations and patient condition notes are mandatory.',
        ];
    }
}

