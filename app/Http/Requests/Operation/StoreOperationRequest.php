<?php

namespace App\Http\Requests\Operation;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['doctor', 'administrator', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'admission_id' => ['required', 'exists:admissions,id'],
            'procedure_name' => ['required', 'string', 'max:191'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'ot_room' => ['required', 'string', 'max:100'],
            'urgency' => ['required', 'in:elective,urgent,emergency'],
            'indication' => ['nullable', 'string', 'max:2000'],
            'scheduled_at' => ['required', 'date'],
            'estimated_duration_minutes' => ['required', 'integer', 'min:15', 'max:720'],
            'primary_surgeon_id' => ['required', 'exists:users,id'],
            'assistant_surgeon_id' => ['nullable', 'exists:users,id', 'different:primary_surgeon_id'],
            'anesthetist_name' => ['nullable', 'string', 'max:191'],
            'ot_nurse_name' => ['nullable', 'string', 'max:191'],
            'operation_notes' => ['nullable', 'string'],
            'post_op_notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'admission_id.required' => 'Please select an admitted patient record.',
            'procedure_name.required' => 'The surgical procedure name is mandatory.',
            'ot_room.required' => 'Please select an Operation Theatre (OT Room).',
            'scheduled_at.required' => 'Operation date and time is required.',
            'primary_surgeon_id.required' => 'A primary lead surgeon must be assigned.',
            'assistant_surgeon_id.different' => 'Assistant surgeon must be a different doctor than the primary surgeon.',
        ];
    }
}

