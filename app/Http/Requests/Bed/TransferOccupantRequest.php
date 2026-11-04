<?php

namespace App\Http\Requests\Bed;

use Illuminate\Foundation\Http\FormRequest;

class TransferOccupantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $bed = $this->route('bed');
        return $bed && $this->user()->can('transfer', $bed);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'target_bed_id' => ['required', 'integer', 'exists:beds,id'],
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'target_bed_id.required' => 'Please select an available target bed for transfer.',
            'target_bed_id.exists' => 'Selected target bed is invalid or does not exist.',
            'reason.required' => 'Please provide clinical or administrative justification for the transfer.',
            'reason.min' => 'Transfer reason must be at least 3 characters.',
        ];
    }
}
