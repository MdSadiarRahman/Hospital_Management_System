<?php

namespace App\Http\Requests\Referral;

use Illuminate\Foundation\Http\FormRequest;

class CompleteTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['doctor', 'nurse', 'administrator', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'transferred_at' => ['nullable', 'date'],
            'handover_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

