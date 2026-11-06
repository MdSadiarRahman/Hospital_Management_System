<?php

namespace App\Http\Requests\Prescription;

use App\Models\Prescription;
use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Prescription::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'consultation_id' => ['required', 'exists:consultations,id'],
            'advice' => ['nullable', 'string', 'max:2000'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['required', 'in:draft,approved'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_name' => ['required', 'string', 'max:191'],
            'items.*.dosage' => ['required', 'string', 'max:50'],
            'items.*.frequency' => ['required', 'string', 'max:50'],
            'items.*.duration' => ['required', 'string', 'max:50'],
            'items.*.instructions' => ['nullable', 'string', 'max:191'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'consultation_id.required' => 'Prescription must be linked to a valid clinical consultation.',
            'consultation_id.exists' => 'Selected consultation record does not exist.',
            'items.required' => 'At least one medicine item must be prescribed.',
            'items.min' => 'At least one medicine item must be prescribed.',
            'items.*.medicine_name.required' => 'Medicine name is required for each line item.',
            'items.*.dosage.required' => 'Dosage is required for each medicine.',
            'items.*.frequency.required' => 'Frequency is required for each medicine.',
            'items.*.duration.required' => 'Duration is required for each medicine.',
            'follow_up_date.after_or_equal' => 'Follow-up date cannot be in the past.',
        ];
    }
}

