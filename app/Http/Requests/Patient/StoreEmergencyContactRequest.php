<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmergencyContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->user_type === 'patient';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'relationship' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:20'],
            'is_primary' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Emergency contact name is required.',
            'relationship.required' => 'Please specify the relationship (e.g. Spouse, Parent, Sibling).',
            'phone.required' => 'Emergency contact phone number is required.',
        ];
    }
}
