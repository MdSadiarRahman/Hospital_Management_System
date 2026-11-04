<?php

namespace App\Http\Requests\Bed;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Bed::class);
    }

    public function rules(): array
    {
        return [
            'bed_number' => ['required', 'string', 'max:32', 'unique:beds,bed_number'],
            'room_number' => ['nullable', 'string', 'max:32'],
            'category' => ['required', 'string', Rule::in(['general', 'department', 'cabin', 'post_op', 'emergency'])],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'daily_charge' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(['available', 'occupied', 'reserved', 'cleaning', 'maintenance'])],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'bed_number.unique' => 'A bed with this number already exists in hospital inventory.',
            'category.in' => 'Selected bed category is invalid.',
            'department_id.exists' => 'Selected clinical department does not exist.',
        ];
    }
}

