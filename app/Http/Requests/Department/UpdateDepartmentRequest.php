<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $department = $this->route('department');
        return $this->user()?->can('update', $department) ?? false;
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim($this->code)),
            ]);
        }

        $this->merge([
            'is_clinical' => $this->boolean('is_clinical'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $departmentId = $this->route('department') instanceof \App\Models\Department
            ? $this->route('department')->id
            : $this->route('department');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('departments', 'name')->ignore($departmentId),
            ],
            'code' => [
                'required',
                'string',
                'max:10',
                'alpha_num',
                Rule::unique('departments', 'code')->ignore($departmentId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_clinical' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom error messages for validator.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Another department is already registered with this name.',
            'code.unique' => 'Another department is already registered with this code.',
            'code.alpha_num' => 'The department code may only contain alphanumeric characters.',
        ];
    }
}
