<?php

namespace App\Http\Requests\Lab;

use Illuminate\Foundation\Http\FormRequest;

class ReviewLabReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->hasRole(['super_admin', 'administrator', 'doctor']) || $user->hasPermission('lab.approve'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'doctor_notes' => ['nullable', 'string', 'max:2000'],
            'clinical_notes' => ['nullable', 'string', 'max:2000'],
            'findings_summary' => ['nullable', 'string', 'max:5000'],
            'is_critical' => ['nullable', 'boolean'],
        ];
    }
}

