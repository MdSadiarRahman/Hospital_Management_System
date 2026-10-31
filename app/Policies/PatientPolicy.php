<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**
     * Determine whether the user can view any patient models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('patients.view');
    }

    /**
     * Determine whether the user can view the specific patient.
     */
    public function view(User $user, Patient $patient): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient && $user->patient->id === $patient->id;
        }

        return $user->hasPermission('patients.view');
    }

    /**
     * Determine whether the user can register a new patient.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('patients.create');
    }

    /**
     * Determine whether the user can update the patient record.
     */
    public function update(User $user, Patient $patient): bool
    {
        return $user->hasPermission('patients.update');
    }

    /**
     * Determine whether the user can delete a patient record.
     */
    public function delete(User $user, Patient $patient): bool
    {
        return $user->hasPermission('patients.delete');
    }

    /**
     * Determine whether the user can print patient cards or records.
     */
    public function print(User $user, Patient $patient): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient && $user->patient->id === $patient->id;
        }

        return $user->hasPermission('patients.print');
    }

    /**
     * Determine whether the user can physically verify and link a patient web account.
     */
    public function linkAccount(User $user, Patient $patient): bool
    {
        return $user->hasPermission('patients.link_account');
    }
}
