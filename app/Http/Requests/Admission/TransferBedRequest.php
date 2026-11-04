<?php

namespace App\Http\Requests\Admission;

use Illuminate\Foundation\Http\FormRequest;

class TransferBedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $admission = $this->route('admission');
        return $admission && $this->user()->can('transferBed', $admission);
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
            'target_bed_id.exists' => 'The selected target bed was not found.',
            'reason.required' => 'Please provide clinical or administrative justification for bed transfer.',
        ];
    }
}

