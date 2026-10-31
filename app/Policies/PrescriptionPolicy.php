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
        return $user->hasPermission('prescriptions.view');
    }

    /**
     * Determine whether the user can view the specific prescription.
     */
    public function view(User $user, Prescription $prescription): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient && $prescription->patient_id === $user->patient->id;
        }

        return $user->hasPermission('prescriptions.view');
    }

    /**
     * Determine whether the doctor can create a prescription.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('prescriptions.create');
    }

    /**
     * Determine whether the doctor can update a prescription.
     */
    public function update(User $user, Prescription $prescription): bool
    {
        return $user->hasPermission('prescriptions.update');
    }

    /**
     * Determine whether the user can delete a prescription.
     */
    public function delete(User $user, Prescription $prescription): bool
    {
        return $user->hasPermission('prescriptions.delete');
    }

    /**
     * Determine whether the doctor can sign/approve a prescription.
     */
    public function approve(User $user, Prescription $prescription): bool
    {
        return $user->hasPermission('prescriptions.approve');
    }

    /**
     * Determine whether the user can print the prescription.
     */
    public function print(User $user, Prescription $prescription): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient && $prescription->patient_id === $user->patient->id;
        }

        return $user->hasPermission('prescriptions.print');
    }
}
