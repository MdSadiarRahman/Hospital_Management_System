<?php

namespace App\Http\Requests\Bed;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBedRequest extends FormRequest
{
    public function authorize(): bool
    {
        $bed = $this->route('bed');
        return $this->user()->can('update', $bed);
    }

    public function rules(): array
    {
        $bedId = $this->route('bed')?->id ?? $this->route('bed');

        return [
            'bed_number' => ['required', 'string', 'max:32', Rule::unique('beds', 'bed_number')->ignore($bedId)],
            'room_number' => ['nullable', 'string', 'max:32'],
            'category' => ['required', 'string', Rule::in(['general', 'department', 'cabin', 'post_op', 'emergency'])],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'daily_charge' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(['available', 'occupied', 'reserved', 'cleaning', 'maintenance'])],
            'description' => ['nullable', 'string', 'max:1000'],
            'status_reason' => ['nullable', 'string', 'max:255'],
            'status_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

