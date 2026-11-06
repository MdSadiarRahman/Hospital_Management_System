<?php

namespace App\Http\Requests\Operation;

use Illuminate\Foundation\Http\FormRequest;

class CancelOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['doctor', 'administrator', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'cancellation_reason.required' => 'A valid reason is required to cancel an operation.',
            'cancellation_reason.min' => 'Cancellation reason must be at least 5 characters.',
        ];
    }
}

