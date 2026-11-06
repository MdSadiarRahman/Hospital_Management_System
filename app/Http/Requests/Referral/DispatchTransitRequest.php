<?php

namespace App\Http\Requests\Referral;

use Illuminate\Foundation\Http\FormRequest;

class DispatchTransitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['doctor', 'nurse', 'administrator', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'transport_type' => ['required', 'string', 'max:100'],
            'ambulance_driver_contact' => ['nullable', 'string', 'max:100'],
            'accompanying_staff' => ['nullable', 'string', 'max:191'],
            'oxygen_support_transit' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'transport_type.required' => 'Please specify the ambulance or transport vehicle type.',
        ];
    }
}

