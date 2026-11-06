<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole(['super_admin', 'administrator', 'billing_staff', 'receptionist']) ||
            $this->user()->hasPermission('billing.collect')
        );
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'in:cash,card,mobile_banking'],
            'transaction_ref' => ['nullable', 'string', 'max:100'],
            'payment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Payment collection amount is required.',
            'amount.min' => 'Payment amount must be greater than zero.',
            'payment_method.in' => 'Accepted payment methods are Cash, Card, or Mobile Banking (bKash/Nagad/Rocket).',
        ];
    }
}

