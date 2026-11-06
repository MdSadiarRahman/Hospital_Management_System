<?php

namespace App\Policies;

use App\Models\Prescription;
use App\Models\User;

class PrescriptionPolicy
{
    /**
     * Determine whether the user can view any prescriptions.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('prescriptions.view') || $user->hasRole(['doctor', 'super_admin', 'administrator', 'nurse']);
    }

    /**
     * Determine whether the user can view the specific prescription.
     * Rule: Patient can view only authorized (approved) prescriptions belonging to them.
     */
    public function view(User $user, Prescription $prescription): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient &&
                   $prescription->patient_id === $user->patient->id &&
                   $prescription->is_approved;
        }

        return $user->hasPermission('prescriptions.view') || $user->hasRole(['doctor', 'super_admin', 'administrator', 'nurse']);
    }

    /**
     * Determine whether the doctor can create a prescription.
     * Rule: Only authorized doctors or administrators can create.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('prescriptions.create') || $user->hasRole(['doctor', 'super_admin', 'administrator']);
    }

    /**
     * Determine whether the doctor can update a prescription.
     */
    public function update(User $user, Prescription $prescription): bool
    {
        if ($user->hasRole(['super_admin', 'administrator'])) {
            return true;
        }

        if ($prescription->consultation?->doctor_id === $user->id) {
            return $user->hasPermission('prescriptions.update') || $user->hasRole('doctor');
        }

        return false;
    }

    /**
     * Determine whether the doctor can sign/approve a prescription.
     * Rule: Only authorized doctors can approve prescriptions.
     */
    public function approve(User $user, Prescription $prescription): bool
    {
        if ($user->hasRole(['super_admin', 'administrator'])) {
            return true;
        }

        return $prescription->consultation?->doctor_id === $user->id && $user->hasRole('doctor');
    }

    /**
     * Determine whether the user can print the prescription.
     */
    public function print(User $user, Prescription $prescription): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient &&
                   $prescription->patient_id === $user->patient->id &&
                   $prescription->is_approved;
        }

        return $user->hasPermission('prescriptions.print') || $user->hasRole(['doctor', 'super_admin', 'administrator', 'nurse', 'receptionist']);
    }

    /**
     * Determine whether the user can delete a prescription.
     * Legal requirement: Approved prescriptions cannot be deleted.
     */
    public function delete(User $user, Prescription $prescription): bool
    {
        if ($prescription->is_approved) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $prescription->consultation?->doctor_id === $user->id && $user->hasRole('doctor');
    }
}
