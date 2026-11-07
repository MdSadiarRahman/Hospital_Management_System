<?php

namespace App\Http\Requests\Discharge;

use Illuminate\Foundation\Http\FormRequest;

class FinancialClearanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole(['billing_staff', 'administrator', 'super_admin']) ||
            $this->user()->hasPermission('billing.financial_clearance')
        );
    }

    public function rules(): array
    {
        return [
            'allow_dues_exception' => ['nullable', 'boolean'],
            'exception_reason' => ['required_if:allow_dues_exception,1', 'nullable', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'exception_reason.required_if' => 'An authorized justification reason is mandatory when permitting discharge with outstanding dues.',
            'exception_reason.min' => 'The justification reason must be at least 5 characters.',
        ];
    }
}

