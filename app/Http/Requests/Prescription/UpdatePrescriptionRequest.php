<?php

namespace App\Http\Requests\Prescription;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrescriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $prescription = $this->route('prescription');
        return $this->user()->can('update', $prescription);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $prescription = $this->route('prescription');
        $isAlreadyApproved = $prescription && $prescription->status === 'approved';

        return [
            'advice' => ['nullable', 'string', 'max:2000'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['required', 'in:draft,approved'],
            'correction_reason' => [
                $isAlreadyApproved ? 'required' : 'nullable',
                'string',
                'min:5',
                'max:1000',
            ],

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
            'correction_reason.required' => 'A documented clinical correction reason is mandatory to amend an approved prescription.',
            'correction_reason.min' => 'Correction reason must be at least 5 characters.',
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

