<?php

namespace App\Http\Requests\Staff;

use App\Models\StaffProfile;
use Illuminate\Foundation\Http\FormRequest;

class ScheduleShiftRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('scheduleShifts', StaffProfile::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'staff_profile_id' => ['required', 'exists:staff_profiles,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'shift_date' => ['required', 'date'],
            'shift_type' => ['required', 'in:morning,evening,night'],
            'status' => ['nullable', 'in:scheduled,completed,swapped,absent,cancelled'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'staff_profile_id.required' => 'Please select a valid staff member for this shift.',
            'department_id.required' => 'Please specify the ward or department for this duty assignment.',
            'shift_date.required' => 'The shift schedule date is required.',
            'shift_type.required' => 'Please select an 8-hour shift type (morning, evening, or night).',
        ];
    }
}
