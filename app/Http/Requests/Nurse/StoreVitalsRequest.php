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
            'temperature' => ['nullable', 'numeric', function ($attribute, $value, $fail) {
                $unit = request('temperature_unit', '°F');
                if ($unit === '°C' || $unit === 'C') {
                    if ($value < 32.0 || $value > 43.3) {
                        $fail('Body temperature in Celsius must be between 32.0°C and 43.3°C.');
                    }
                } else {
                    if ($value < 90.0 || $value > 110.0) {
                        $fail('Body temperature in Fahrenheit must be between 90.0°F and 110.0°F.');
                    }
                }
            }],
            'temperature_unit' => ['nullable', 'string', 'in:°F,°C,F,C'],
            'blood_pressure' => ['nullable', 'string', 'max:15', 'regex:/^\d{2,3}\/\d{2,3}$/', function ($attribute, $value, $fail) {
                if ($value && str_contains($value, '/')) {
                    $parts = explode('/', $value);
                    $sys = (int) trim($parts[0]);
                    $dia = (int) trim($parts[1]);

                    if ($sys < 50 || $sys > 260) {
                        $fail('Systolic blood pressure must be between 50 and 260 mmHg.');
                    }
                    if ($dia < 30 || $dia > 160) {
                        $fail('Diastolic blood pressure must be between 30 and 160 mmHg.');
                    }
                    if ($sys <= $dia) {
                        $fail('Systolic blood pressure must be higher than diastolic blood pressure.');
                    }
                }
            }],
            'pulse_rate' => ['nullable', 'integer', 'between:30,250'],
            'respiratory_rate' => ['nullable', 'integer', 'between:5,60'],
            'spo2' => ['nullable', 'numeric', 'between:50.0,100.0'],
            'weight' => ['nullable', 'numeric', 'between:1.0,300.0'],
            'weight_unit' => ['nullable', 'string', 'in:kg,lbs'],
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
            'blood_pressure.regex' => 'Blood pressure must be in format Systolic/Diastolic (e.g. 120/80).',
            'pulse_rate.between' => 'Pulse rate must be between 30 and 250 beats per minute.',
            'respiratory_rate.between' => 'Respiratory rate must be between 5 and 60 breaths per minute.',
            'spo2.between' => 'Oxygen saturation (SpO2) must be between 50.0% and 100.0%.',
            'weight.between' => 'Patient weight must be between 1.0 kg and 300.0 kg.',
            'observations.required' => 'Bedside nursing observations and condition notes are mandatory.',
        ];
    }
}
