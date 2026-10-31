<?php

namespace App\Http\Requests\Visit;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVisitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('visit'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'visit_date' => ['nullable', 'date'],
            'visit_type' => ['required', 'string', 'in:regular,emergency,follow_up'],
            'triage_level' => ['required', 'string', 'in:normal,priority,critical'],
            'status' => ['required', 'string', 'in:waiting,in_consultation,completed,admitted,referred_out'],
        ];
    }

    /**
     * Custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'visit_date' => 'Visit Date & Time',
            'visit_type' => 'Visit Type',
            'triage_level' => 'Triage Severity Level',
            'status' => 'Visit Status',
        ];
    }
}
