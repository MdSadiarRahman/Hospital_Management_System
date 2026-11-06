<?php

namespace App\Http\Requests\Lab;

use Illuminate\Foundation\Http\FormRequest;

class CollectSampleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->hasRole(['super_admin', 'administrator', 'lab_staff', 'nurse']) || $user->hasPermission('lab.update'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sample_type' => ['required', 'string', 'max:50'],
            'barcode' => ['nullable', 'string', 'max:50', 'unique:lab_samples,barcode'],
            'collected_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'item_ids' => ['nullable', 'array'],
            'item_ids.*' => ['integer', 'exists:lab_order_items,id'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'sample_type.required' => 'Specimen / sample type (e.g. Whole Blood, Serum, Urine) is required.',
            'barcode.unique' => 'This sample identifier / barcode is already in use.',
        ];
    }
}

