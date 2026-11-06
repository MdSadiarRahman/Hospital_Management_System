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
            'prescription_id' => ['nullable', 'integer', 'exists:prescriptions,id'],
            'prescription_item_id' => ['nullable', 'integer', 'exists:prescription_items,id'],
            'medicine_name' => ['required', 'string', 'max:191'],
            'dose_given' => ['required', 'string', 'max:50'],
            'route' => ['nullable', 'string', 'in:Oral,IV Push,IV Infusion,IM,SC,Inhalation,Topical,Sublingual,Rectal,Other'],
            'scheduled_shift' => ['nullable', 'string', 'in:morning,evening,night'],
            'scheduled_time' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:administered,missed,withheld,refused,omitted'],
            'withheld_reason' => ['nullable', 'string', 'max:1000', 'required_if:status,withheld'],
            'remarks' => ['nullable', 'string', 'max:1000', 'required_if:status,missed,refused,omitted'],
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
            'status.required' => 'Medication administration status (administered, missed, withheld, refused, omitted) is required.',
            'status.in' => 'Status must be one of: administered, missed, withheld, refused, omitted.',
            'withheld_reason.required_if' => 'A clinical reason is mandatory when withholding a doctor-prescribed medication.',
            'remarks.required_if' => 'Clinical reason or patient reaction is mandatory when a dose is missed, refused, or omitted.',
        ];
    }
}
