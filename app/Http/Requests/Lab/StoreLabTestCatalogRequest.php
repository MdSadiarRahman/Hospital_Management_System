<?php

namespace App\Http\Requests\Lab;

use Illuminate\Foundation\Http\FormRequest;

class StoreLabTestCatalogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->hasRole(['super_admin', 'administrator', 'lab_staff']) || $user->hasPermission('lab.create'));
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim($this->input('code'))),
            ]);
        }
        if ($this->has('price') && !$this->has('standard_charge')) {
            $this->merge([
                'standard_charge' => $this->input('price'),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'code' => ['required', 'string', 'max:20', 'unique:lab_test_catalog,code', 'regex:/^[A-Za-z0-9_-]+$/'],
            'category' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'standard_charge' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'sample_type' => ['nullable', 'string', 'max:100'],
            'turnaround_time' => ['nullable', 'string', 'max:50'],
            'default_source' => ['required', 'string', 'in:internal,external,both'],
            'lab_partner_id' => ['nullable', 'integer', 'exists:lab_partners,id'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The diagnostic test name is required.',
            'code.required' => 'A unique test code is required.',
            'code.unique' => 'This test code already exists in the catalog. Please choose a unique code.',
            'code.regex' => 'Test code may only contain letters, numbers, hyphens, and underscores.',
            'category.required' => 'Please select or specify a laboratory department/category.',
            'standard_charge.required' => 'The price / standard charge is required.',
            'standard_charge.min' => 'Price cannot be negative.',
            'default_source.in' => 'Availability must be internal, external, or both.',
        ];
    }
}

