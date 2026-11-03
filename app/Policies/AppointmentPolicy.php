<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /**
     * Determine whether the user can view any appointments.
     */
    public function viewAny(User $user): bool
    {
        if ($user->user_type === 'patient') {
            return true;
        }

        return $user->hasPermission('appointments.view');
    }

    /**
     * Determine whether the user can view the specific appointment.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient && (int) $user->patient->id === (int) $appointment->patient_id;
        }

        if ($user->hasRole('doctor')) {
            // Doctors can view their own appointments or departmental appointments
            return $appointment->doctor_id === $user->id || $user->hasPermission('appointments.view');
        }

        return $user->hasPermission('appointments.view');
    }

    /**
     * Determine whether the user can create an appointment.
     */
    public function create(User $user): bool
    {
        if ($user->user_type === 'patient') {
            return true;
        }

        return $user->hasPermission('appointments.create');
    }

    /**
     * Determine whether the user can approve/confirm an appointment.
     */
    public function approve(User $user, Appointment $appointment): bool
    {
        if ($user->user_type === 'patient') {
            return false;
        }

        return $user->hasPermission('appointments.approve');
    }

    /**
     * Determine whether the user can reschedule/update an appointment.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient
                && (int) $user->patient->id === (int) $appointment->patient_id
                && $appointment->canBeRescheduled();
        }

        return $user->hasPermission('appointments.update');
    }

    /**
     * Determine whether the user can cancel/delete an appointment.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient
                && (int) $user->patient->id === (int) $appointment->patient_id
                && $appointment->canBeCancelled();
        }

        return $user->hasPermission('appointments.delete');
    }

    /**
     * Determine whether the user can print the appointment slip.
     */
    public function print(User $user, Appointment $appointment): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient && (int) $user->patient->id === (int) $appointment->patient_id;
        }

        return $user->hasPermission('appointments.print') || $user->hasPermission('appointments.view');
    }
}
