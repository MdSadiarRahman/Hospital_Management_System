<?php

namespace App\Http\Requests\Nurse;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicationAdministrationRequest extends FormRequest
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
            'prescription_item_id' => ['nullable', 'integer', 'exists:prescription_items,id'],
            'medicine_name' => ['required', 'string', 'max:191'],
            'dose_given' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:administered,omitted,refused'],
            'remarks' => ['nullable', 'string', 'max:1000', 'required_if:status,omitted,refused'],
            'administered_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'medicine_name.required' => 'Medicine name is required.',
            'dose_given.required' => 'Dose administered is required (e.g. 500mg, 1 tablet, 100ml IV).',
            'status.required' => 'Medication administration status (administered, omitted, refused) is required.',
            'remarks.required_if' => 'Clinical reason or patient reaction is mandatory when a dose is omitted or refused.',
        ];
    }
}

