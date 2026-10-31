<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePatientPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->user_type === 'patient';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ];
    }

    /**
     * Custom messages.
     */
    public function messages(): array
    {
        return [
            'current_password.current_password' => 'The provided current password does not match our records.',
            'password.confirmed' => 'New password confirmation does not match.',
            'password.min' => 'The new password must be at least 8 characters with numbers and mixed case letters.',
        ];
    }
}
