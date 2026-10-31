<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

class AssignDepartmentStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $department = $this->route('department');
        return $this->user()?->can('manageStaff', $department) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'designation' => ['required', 'string', 'max:100'],
            'specialization' => ['nullable', 'string', 'max:191'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'shift' => ['required', 'in:morning,evening,night,rotational'],
            'status' => ['required', 'in:active,on_leave,inactive'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Please select a staff member to assign to this department.',
            'user_id.exists' => 'The selected staff member does not exist in the hospital staff roster.',
            'shift.in' => 'Selected work shift must be morning, evening, night, or rotational.',
        ];
    }
}
