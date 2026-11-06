<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class StoreBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole(['super_admin', 'administrator', 'billing_staff', 'receptionist']) ||
            $this->user()->hasPermission('billing.create')
        );
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'admission_id' => ['nullable', 'integer', 'exists:admissions,id'],
            'visit_id' => ['nullable', 'integer', 'exists:visits,id'],
            'bill_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_id' => ['nullable', 'integer', 'exists:services,id'],
            'items.*.item_type' => ['nullable', 'string', 'in:bed,consultation,lab,surgery,service'],
            'items.*.item_name' => ['required_without:items.*.service_id', 'nullable', 'string', 'max:191'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_reason' => ['nullable', 'string', 'max:191'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Please select a valid registered patient.',
            'items.required' => 'At least one billable service or item must be added to the invoice.',
            'items.min' => 'At least one billable service or item must be added to the invoice.',
            'items.*.quantity.min' => 'Each bill item quantity must be at least 1.',
        ];
    }
}

