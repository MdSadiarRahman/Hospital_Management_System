<?php

namespace App\Http\Requests\Lab;

use Illuminate\Foundation\Http\FormRequest;

class StoreLabOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->hasRole(['super_admin', 'administrator', 'doctor', 'lab_staff']) || $user->hasPermission('lab.create'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'consultation_id' => ['required', 'integer', 'exists:consultations,id'],
            'source' => ['required', 'string', 'in:internal,external'],
            'lab_partner_id' => ['nullable', 'required_if:source,external', 'integer', 'exists:lab_partners,id'],
            'priority' => ['required', 'string', 'in:routine,urgent,stat'],
            'clinical_indication' => ['nullable', 'string', 'max:1000'],
            'tests' => ['required', 'array', 'min:1'],
            'tests.*.lab_test_id' => ['required', 'integer', 'exists:lab_test_catalog,id'],
            'tests.*.instructions' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'patient_id.required' => 'Please select an enrolled patient.',
            'consultation_id.required' => 'Test orders must be linked to a consultation.',
            'source.required' => 'Order source (internal or external) is required.',
            'lab_partner_id.required_if' => 'Please select an external reference laboratory partner for outsourced tests.',
            'priority.required' => 'Clinical priority (routine, urgent, stat) is required.',
            'tests.required' => 'At least one diagnostic test must be included in the order.',
            'tests.min' => 'Please select at least one test from the laboratory catalog.',
            'tests.*.lab_test_id.required' => 'Test selection from catalog is required.',
            'tests.*.lab_test_id.exists' => 'Selected test does not exist in the active catalog.',
        ];
    }
}

