<?php

namespace App\Http\Requests\Consultation;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Consultation::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'visit_id' => ['required', 'exists:visits,id'],
            'chief_complaints' => ['required', 'string', 'min:3'],
            'clinical_notes' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,finalized'],

            // Diagnoses
            'diagnoses' => ['nullable', 'array'],
            'diagnoses.*.condition_name' => ['required_with:diagnoses', 'string', 'max:255'],
            'diagnoses.*.icd_code' => ['nullable', 'string', 'max:50'],
            'diagnoses.*.diagnosis_type' => ['required_with:diagnoses', 'in:provisional,final'],

            // Advice / non-pharmacological treatment
            'advice' => ['nullable', 'string'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
            'follow_up_instructions' => ['nullable', 'string'],
            'inpatient_treatment_plan' => ['nullable', 'string'],

            // Prescriptions / medications
            'medications' => ['nullable', 'array'],
            'medications.*.medicine_name' => ['required_with:medications', 'string', 'max:255'],
            'medications.*.dosage' => ['required_with:medications', 'string', 'max:100'],
            'medications.*.frequency' => ['required_with:medications', 'string', 'max:100'],
            'medications.*.duration' => ['required_with:medications', 'string', 'max:100'],
            'medications.*.instructions' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('status') === 'finalized') {
                $diagnoses = $this->input('diagnoses', []);
                if (empty($diagnoses) || !is_array($diagnoses)) {
                    $validator->errors()->add('diagnoses', 'At least one diagnosis is mandatory before finalizing a consultation.');
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'visit_id.required' => 'Consultation must be linked to a valid patient visit.',
            'visit_id.exists' => 'Selected patient visit record was not found.',
            'chief_complaints.required' => 'Chief complaint is mandatory to document the presenting patient problem.',
            'chief_complaints.min' => 'Chief complaint must be at least 3 characters.',
            'diagnoses.*.condition_name.required_with' => 'Condition name is required for each diagnosis entered.',
            'diagnoses.*.diagnosis_type.in' => 'Diagnosis type must be provisional or final.',
            'follow_up_date.after_or_equal' => 'Follow-up date cannot be in the past.',
            'medications.*.medicine_name.required_with' => 'Medicine name is required for each prescription entry.',
        ];
    }
}

