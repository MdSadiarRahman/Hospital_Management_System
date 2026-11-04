<?php

namespace App\Http\Requests\Admission;

use App\Models\Admission;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Admission::class);
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
            'visit_id' => ['nullable', 'integer', 'exists:visits,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'bed_id' => ['required', 'integer', 'exists:beds,id'],
            'attending_doctor_id' => ['required', 'integer', 'exists:users,id'],
            'admission_date' => ['nullable', 'date'],
            'admission_reason' => ['required', 'string', 'min:3', 'max:1500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'patient_id.required' => 'Please select a registered patient for admission.',
            'patient_id.exists' => 'The selected patient record was not found.',
            'bed_id.required' => 'Please allocate an available bed for the inpatient.',
            'bed_id.exists' => 'The selected bed does not exist in hospital inventory.',
            'attending_doctor_id.required' => 'Please assign an in-charge attending doctor.',
            'admission_reason.required' => 'Please provide clinical reasons and indications for inpatient admission.',
        ];
    }
}

