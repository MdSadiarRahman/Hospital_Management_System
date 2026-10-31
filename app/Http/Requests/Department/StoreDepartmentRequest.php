<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Department::class) ?? false;
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
            'is_active' => $this->boolean('is_active', true),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:departments,name'],
            'code' => ['required', 'string', 'max:10', 'alpha_num', 'unique:departments,code'],
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
            'name.unique' => 'A hospital department with this name already exists.',
            'code.unique' => 'A department with this uppercase code already exists.',
            'code.alpha_num' => 'The department code may only contain alphanumeric characters.',
        ];
    }
}
