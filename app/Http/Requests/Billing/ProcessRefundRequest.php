<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class ProcessRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole(['super_admin', 'administrator']) ||
            $this->user()->hasPermission('billing.approve')
        );
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'refund_method' => ['nullable', 'string', 'in:cash,card,mobile_banking'],
            'transaction_ref' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Refund amount is required.',
            'amount.min' => 'Refund amount must be greater than zero.',
            'reason.required' => 'A detailed clinical or administrative justification reason is required to process a refund.',
            'reason.min' => 'Reason must be at least 5 characters long.',
        ];
    }
}

