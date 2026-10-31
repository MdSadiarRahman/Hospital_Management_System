<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfigureDepartmentBedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $department = $this->route('department');
        return $this->user()?->can('configureBeds', $department) ?? false;
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('bed_number')) {
            $this->merge([
                'bed_number' => strtoupper(trim($this->bed_number)),
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
        $bedId = $this->route('bed') instanceof \App\Models\Bed
            ? $this->route('bed')->id
            : $this->route('bed');

        return [
            'bed_number' => [
                'required',
                'string',
                'max:32',
                Rule::unique('beds', 'bed_number')->ignore($bedId),
            ],
            'category' => ['required', 'in:general,department,cabin,post_op'],
            'daily_charge' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,occupied,reserved,cleaning,maintenance'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'bed_number.required' => 'A unique bed number (e.g. GYN-03, SUR-05) is required.',
            'bed_number.unique' => 'A bed with this number is already configured in the hospital.',
            'category.in' => 'Bed category must be general, department, cabin, or post_op.',
            'daily_charge.min' => 'The daily bed tariff cannot be negative.',
        ];
    }
}
