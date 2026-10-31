<?php

namespace App\Http\Requests\Staff;

use App\Models\StaffProfile;
use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', StaffProfile::class) ?? false;
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('license_number') && $this->license_number) {
            $this->merge([
                'license_number' => strtoupper(trim($this->license_number)),
            ]);
        }

        if ($this->has('employee_id') && $this->employee_id) {
            $this->merge([
                'employee_id' => strtoupper(trim($this->employee_id)),
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
            // User Credentials & Basic Info
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['exists:roles,id'],

            // Staff Profile & Department Allocation
            'employee_id' => ['nullable', 'string', 'max:32', 'unique:staff_profiles,employee_id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'secondary_department_ids' => ['nullable', 'array'],
            'secondary_department_ids.*' => ['exists:departments,id'],
            'designation' => ['required', 'string', 'max:100'],
            'specialization' => ['nullable', 'string', 'max:191'],
            'license_number' => ['nullable', 'string', 'max:50', 'unique:staff_profiles,license_number'],
            'shift' => ['required', 'in:morning,evening,night,rotational'],
            'status' => ['required', 'in:active,on_leave,inactive'],

            // Employment & Sensitive Information
            'employment_type' => ['required', 'in:full_time,part_time,visiting_consultant,contractual'],
            'joining_date' => ['nullable', 'date'],
            'qualification' => ['nullable', 'string', 'max:191'],
            'salary' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'nid_number' => ['nullable', 'string', 'max:30'],
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'role_ids.required' => 'At least one hospital role must be assigned to this staff member.',
            'employee_id.unique' => 'This Employee ID is already registered to another staff member.',
            'license_number.unique' => 'This BMDC or Nursing License number is already registered in the system.',
            'email.unique' => 'This email address is already bound to an existing user account.',
            'phone.unique' => 'This mobile number is already bound to an existing user account.',
        ];
    }
}
