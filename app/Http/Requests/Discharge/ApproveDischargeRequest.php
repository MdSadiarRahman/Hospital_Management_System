<?php

namespace App\Http\Requests\Discharge;

use Illuminate\Foundation\Http\FormRequest;

class ApproveDischargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole(['doctor', 'administrator', 'super_admin', 'billing_staff'])
        );
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

