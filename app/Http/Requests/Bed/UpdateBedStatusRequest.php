<?php

namespace App\Http\Requests\Bed;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBedStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $bed = $this->route('bed');
        return $this->user()->can('updateStatus', $bed);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(['available', 'occupied', 'reserved', 'cleaning', 'maintenance'])],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

