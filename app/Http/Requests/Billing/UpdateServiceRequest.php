<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole(['super_admin', 'administrator']) ||
            $this->user()->hasPermission('billing.update') ||
            $this->user()->hasPermission('settings.manage')
        );
    }

    public function rules(): array
    {
        $serviceId = $this->route('service') instanceof \App\Models\Service
            ? $this->route('service')->id
            : $this->route('service');

        return [
            'category_id' => ['sometimes', 'required', 'integer', 'exists:service_categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:191'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

