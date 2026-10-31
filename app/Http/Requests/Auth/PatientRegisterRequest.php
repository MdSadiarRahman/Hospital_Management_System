<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class PatientRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'terms' => ['accepted'],
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'terms.accepted' => 'You must accept the Clinic Privacy Policy and Terms of Care to register.',
            'password.confirmed' => 'The password confirmation does not match.',
            'email.unique' => 'An account is already registered with this email address.',
            'phone.unique' => 'An account is already registered with this phone number.',
        ];
    }
}
