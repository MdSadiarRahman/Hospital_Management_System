<?php

namespace App\Http\Requests\Lab;

use Illuminate\Foundation\Http\FormRequest;

class UploadExternalReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->hasRole(['super_admin', 'administrator', 'doctor', 'lab_staff']) || $user->hasPermission('lab.create'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // Max 10MB
            'lab_partner_id' => ['nullable', 'integer', 'exists:lab_partners,id'],
            'external_reference_number' => ['nullable', 'string', 'max:100'],
            'findings_summary' => ['nullable', 'string', 'max:5000'],
            'is_critical' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please select a diagnostic report file to upload.',
            'file.file' => 'The uploaded file is invalid or corrupt.',
            'file.mimes' => 'Only PDF documents and medical image scans (PDF, JPG, JPEG, PNG) are permitted.',
            'file.max' => 'The report file size cannot exceed 10 MB.',
            'lab_partner_id.exists' => 'The selected external reference laboratory does not exist.',
        ];
    }
}

