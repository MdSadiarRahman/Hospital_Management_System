<?php

namespace App\Policies;

use App\Models\Consultation;
use App\Models\User;

class ConsultationPolicy
{
    /**
     * Determine whether the user can view any consultations.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('consultations.view') || $user->hasRole(['doctor', 'super_admin', 'administrator', 'nurse']);
    }

    /**
     * Determine whether the user can view the specific consultation.
     */
    public function view(User $user, Consultation $consultation): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient && $consultation->visit?->patient_id === $user->patient->id;
        }

        return $user->hasPermission('consultations.view') || $user->hasRole(['doctor', 'super_admin', 'administrator', 'nurse']);
    }

    /**
     * Determine whether the doctor can create a consultation.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('consultations.create') || $user->hasRole(['doctor', 'super_admin', 'administrator']);
    }

    /**
     * Determine whether the doctor can update a consultation.
     * Protects clinical records: only the authoring doctor or administrator can modify.
     */
    public function update(User $user, Consultation $consultation): bool
    {
        if ($user->hasRole(['super_admin', 'administrator'])) {
            return true;
        }

        if ($consultation->doctor_id === $user->id) {
            return $user->hasPermission('consultations.update') || $user->hasRole('doctor');
        }

        return false;
    }

    /**
     * Determine whether the user can delete a consultation.
     * Legal healthcare standard: Finalized consultations are permanent legal records and cannot be deleted.
     */
    public function delete(User $user, Consultation $consultation): bool
    {
        if ($consultation->status === 'finalized') {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $consultation->doctor_id === $user->id && $user->hasRole('doctor');
    }

    /**
     * Determine whether the user can finalize/approve a consultation.
     */
    public function approve(User $user, Consultation $consultation): bool
    {
        if ($user->hasRole(['super_admin', 'administrator'])) {
            return true;
        }

        return $consultation->doctor_id === $user->id && $user->hasRole('doctor');
    }

    /**
     * Determine whether the user can print the consultation notes.
     */
    public function print(User $user, Consultation $consultation): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient && $consultation->visit?->patient_id === $user->patient->id;
        }

        return $user->hasPermission('consultations.print') || $user->hasRole(['doctor', 'super_admin', 'administrator', 'nurse', 'receptionist']);
    }
}
