<?php

namespace App\Http\Requests\Discharge;

use Illuminate\Foundation\Http\FormRequest;

class ClinicalClearanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole(['doctor', 'administrator', 'super_admin']) ||
            $this->user()->hasPermission('clinical.clearance')
        );
    }

    public function rules(): array
    {
        return [
            'discharge_diagnosis' => ['required', 'string', 'min:3', 'max:1000'],
            'treatment_given' => ['required', 'string', 'min:3', 'max:5000'],
            'condition_at_discharge' => ['required', 'string', 'in:cured,improved,referred,dor,stable'],
            'discharge_medications' => ['required', 'string', 'min:3', 'max:5000'],
            'follow_up_instructions' => ['nullable', 'string', 'max:2000'],
            'hospital_course' => ['nullable', 'string', 'max:5000'],
            'investigations_summary' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'discharge_diagnosis.required' => 'Final discharge diagnosis is required.',
            'treatment_given.required' => 'Summary of clinical treatment given is required.',
            'condition_at_discharge.required' => 'Patient condition at discharge is required.',
            'discharge_medications.required' => 'Take-home discharge medications are required.',
        ];
    }
}

