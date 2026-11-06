<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class ApplyDiscountRequest extends FormRequest
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
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'discount_reason' => ['required', 'string', 'max:191'],
        ];
    }

    public function messages(): array
    {
        return [
            'discount_amount.required' => 'Please provide the concession or discount amount.',
            'discount_amount.min' => 'Discount amount cannot be negative.',
            'discount_reason.required' => 'A valid justification reason is required for approving a discount.',
        ];
    }
}

