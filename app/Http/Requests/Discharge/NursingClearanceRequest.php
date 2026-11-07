<?php

namespace App\Http\Requests\Discharge;

use Illuminate\Foundation\Http\FormRequest;

class NursingClearanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole(['nurse', 'doctor', 'administrator', 'super_admin']) ||
            $this->user()->hasPermission('nursing.record')
        );
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

