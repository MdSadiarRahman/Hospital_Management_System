<?php

namespace App\Http\Requests\Lab;

use Illuminate\Foundation\Http\FormRequest;

class StoreInternalResultRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->hasRole(['super_admin', 'administrator', 'doctor', 'lab_staff']) || $user->hasPermission('lab.create'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'findings_summary' => ['nullable', 'string', 'max:5000'],
            'parameters' => ['nullable', 'array'],
            'parameters.*.parameter' => ['required_with:parameters', 'string', 'max:150'],
            'parameters.*.value' => ['required_with:parameters', 'string', 'max:100'],
            'parameters.*.unit' => ['nullable', 'string', 'max:50'],
            'parameters.*.reference_range' => ['nullable', 'string', 'max:100'],
            'parameters.*.flag' => ['nullable', 'string', 'in:normal,low,high,abnormal,critical'],
            'is_critical' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'parameters.*.parameter.required_with' => 'Parameter name is required when recording structured results.',
            'parameters.*.value.required_with' => 'Parameter measured value is required.',
        ];
    }
}

