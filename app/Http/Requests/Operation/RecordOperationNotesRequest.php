<?php

namespace App\Http\Requests\Operation;

use Illuminate\Foundation\Http\FormRequest;

class RecordOperationNotesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['doctor', 'administrator', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'operation_notes' => ['required', 'string', 'min:10'],
            'post_op_notes' => ['nullable', 'string'],
            'anesthetist_name' => ['nullable', 'string', 'max:191'],
            'ot_nurse_name' => ['nullable', 'string', 'max:191'],
        ];
    }

    public function messages(): array
    {
        return [
            'operation_notes.required' => 'Detailed surgical procedure notes and findings are required.',
            'operation_notes.min' => 'Operation notes must be at least 10 characters.',
        ];
    }
}

