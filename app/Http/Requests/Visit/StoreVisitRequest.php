<?php

namespace App\Http\Requests\Visit;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Visit::class);
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
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'visit_date' => ['nullable', 'date'],
            'visit_type' => ['required', 'string', 'in:regular,emergency,follow_up'],
            'triage_level' => ['required', 'string', 'in:normal,priority,critical'],
            'status' => ['required', 'string', 'in:waiting,in_consultation,completed,admitted,referred_out'],
            'chief_complaints' => ['nullable', 'string', 'max:2000'],
            'clinical_notes' => ['nullable', 'string', 'max:3000'],
            'doctor_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * Custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'patient_id' => 'Patient',
            'appointment_id' => 'Appointment Reference',
            'visit_date' => 'Visit Date & Time',
            'visit_type' => 'Visit Type',
            'triage_level' => 'Triage Severity Level',
            'status' => 'Initial Visit Status',
            'chief_complaints' => 'Chief Complaints',
            'clinical_notes' => 'Triage / Clinical Notes',
            'doctor_id' => 'Attending Doctor',
        ];
    }
}
