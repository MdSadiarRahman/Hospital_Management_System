<?php

namespace App\Http\Requests\Consultation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $consultation = $this->route('consultation');
        return $this->user()->can('update', $consultation);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $consultation = $this->route('consultation');
        $isAlreadyFinalized = $consultation && $consultation->status === 'finalized';

        return [
            'chief_complaints' => ['required', 'string', 'min:3'],
            'clinical_notes' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,finalized'],
            'correction_reason' => [
                $isAlreadyFinalized ? 'required' : 'nullable',
                'string',
                'min:5',
                'max:1000'
            ],

            // Diagnoses
            'diagnoses' => ['nullable', 'array'],
            'diagnoses.*.id' => ['nullable', 'integer'],
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
            'chief_complaints.required' => 'Chief complaint is mandatory to document the presenting patient problem.',
            'chief_complaints.min' => 'Chief complaint must be at least 3 characters.',
            'correction_reason.required' => 'An explicit clinical correction reason is required when modifying a finalized medical record.',
            'correction_reason.min' => 'Correction reason must be at least 5 characters.',
            'diagnoses.*.condition_name.required_with' => 'Condition name is required for each diagnosis entered.',
            'diagnoses.*.diagnosis_type.in' => 'Diagnosis type must be provisional or final.',
            'follow_up_date.after_or_equal' => 'Follow-up date cannot be in the past.',
            'medications.*.medicine_name.required_with' => 'Medicine name is required for each prescription entry.',
        ];
    }
}

