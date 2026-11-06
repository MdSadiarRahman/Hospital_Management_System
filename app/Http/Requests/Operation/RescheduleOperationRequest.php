<?php

namespace App\Http\Requests\Operation;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['doctor', 'administrator', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'scheduled_at' => ['required', 'date'],
            'estimated_duration_minutes' => ['required', 'integer', 'min:15', 'max:720'],
            'ot_room' => ['nullable', 'string', 'max:100'],
            'primary_surgeon_id' => ['nullable', 'exists:users,id'],
            'assistant_surgeon_id' => ['nullable', 'exists:users,id'],
            'rescheduled_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'scheduled_at.required' => 'The new scheduled date and time is required.',
            'rescheduled_reason.required' => 'Please provide a clinical or operational reason for rescheduling.',
            'rescheduled_reason.min' => 'Rescheduling reason must be at least 5 characters.',
        ];
    }
}

