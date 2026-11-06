<?php

namespace App\Http\Requests\Nurse;

use Illuminate\Foundation\Http\FormRequest;

class CorrectMedicationAdministrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->hasRole(['nurse', 'super_admin', 'administrator', 'doctor']) || $user->hasPermission('nursing.record'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dose_given' => ['required', 'string', 'max:50'],
            'route' => ['nullable', 'string', 'in:Oral,IV Push,IV Infusion,IM,SC,Inhalation,Topical,Sublingual,Rectal,Other'],
            'status' => ['required', 'string', 'in:administered,missed,withheld,refused,omitted'],
            'withheld_reason' => ['nullable', 'string', 'max:1000', 'required_if:status,withheld'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'correction_reason' => ['required', 'string', 'min:5', 'max:1000'],
            'administered_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'dose_given.required' => 'Dose administered is required.',
            'status.required' => 'Administration status is required.',
            'status.in' => 'Status must be one of: administered, missed, withheld, refused, omitted.',
            'withheld_reason.required_if' => 'Clinical reason is required when a medication is marked as withheld.',
            'correction_reason.required' => 'A detailed reason for correcting this medication record is required for clinical audit compliance.',
            'correction_reason.min' => 'Please provide at least 5 characters explaining the correction reason.',
        ];
    }
}

