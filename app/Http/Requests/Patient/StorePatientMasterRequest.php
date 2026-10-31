<?php

namespace App\Http\Requests\Patient;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;

class StorePatientMasterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Patient::class) ?? false;
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone') && $this->phone) {
            $this->merge([
                'phone' => trim($this->phone),
            ]);
        }

        if ($this->has('email') && $this->email) {
            $this->merge([
                'email' => strtolower(trim($this->email)),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Patient Demographics
            'patient_code' => ['nullable', 'string', 'max:32', 'unique:patients,patient_code'],
            'name' => ['required', 'string', 'max:191'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', 'in:male,female,other'],
            'blood_group' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:191'],
            'nid_or_birth_cert' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'registration_date' => ['nullable', 'date', 'before_or_equal:today'],

            // Emergency Contact (Next of Kin)
            'emergency_contact_name' => ['required', 'string', 'max:191'],
            'emergency_contact_relationship' => ['required', 'string', 'max:50'],
            'emergency_contact_phone' => ['required', 'string', 'max:20'],

            // Duplicate acknowledgment override
            'confirm_not_duplicate' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'patient_code' => 'patient ID',
            'name' => 'patient full name',
            'date_of_birth' => 'date of birth',
            'phone' => 'primary mobile number',
            'nid_or_birth_cert' => 'NID or birth certificate',
            'emergency_contact_name' => 'emergency contact name',
            'emergency_contact_relationship' => 'emergency contact relationship',
            'emergency_contact_phone' => 'emergency contact phone',
        ];
    }
}
