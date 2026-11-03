<?php

namespace App\Http\Requests\Appointment;

use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;

class BookAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Public visitors, patients, receptionists, and staff are authorized
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $isStaff = $user && $user->user_type === 'staff';
        $isPatient = $user && $user->user_type === 'patient';

        $rules = [
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'doctor_id' => ['required', 'integer', 'exists:users,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'string'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'type' => ['nullable', 'string', 'in:online,walk_in,follow_up'],
        ];

        if ($isStaff) {
            // Staff can either select an existing patient or provide guest details
            $rules['patient_id'] = ['nullable', 'integer', 'exists:patients,id'];
            $rules['guest_name'] = ['required_without:patient_id', 'nullable', 'string', 'max:191'];
            $rules['guest_phone'] = ['required_without:patient_id', 'nullable', 'string', 'max:20'];
            $rules['guest_email'] = ['nullable', 'email', 'max:191'];
            $rules['auto_confirm'] = ['nullable', 'boolean'];
        } elseif ($isPatient) {
            // Logged-in patients don't need guest fields
            $rules['patient_id'] = ['nullable', 'integer', 'exists:patients,id'];
        } else {
            // Public visitors MUST provide guest contact details
            $rules['guest_name'] = ['required', 'string', 'max:191'];
            $rules['guest_phone'] = ['required', 'string', 'max:20'];
            $rules['guest_email'] = ['nullable', 'email', 'max:191'];
        }

        return $rules;
    }

    /**
     * Custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'department_id' => 'clinical department',
            'doctor_id' => 'consulting doctor',
            'appointment_date' => 'preferred date',
            'appointment_time' => 'time slot',
            'guest_name' => 'patient full name',
            'guest_phone' => 'phone number',
            'guest_email' => 'email address',
        ];
    }
}
