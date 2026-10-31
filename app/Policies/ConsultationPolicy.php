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
        return $user->hasPermission('consultations.view');
    }

    /**
     * Determine whether the user can view the specific consultation.
     */
    public function view(User $user, Consultation $consultation): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient && $consultation->patient_id === $user->patient->id;
        }

        return $user->hasPermission('consultations.view');
    }

    /**
     * Determine whether the doctor can create a consultation.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('consultations.create');
    }

    /**
     * Determine whether the doctor can update a consultation.
     */
    public function update(User $user, Consultation $consultation): bool
    {
        return $user->hasPermission('consultations.update');
    }

    /**
     * Determine whether the user can delete a consultation.
     */
    public function delete(User $user, Consultation $consultation): bool
    {
        return $user->hasPermission('consultations.delete');
    }

    /**
     * Determine whether the user can finalize/approve a consultation.
     */
    public function approve(User $user, Consultation $consultation): bool
    {
        return $user->hasPermission('consultations.approve');
    }

    /**
     * Determine whether the user can print the consultation notes.
     */
    public function print(User $user, Consultation $consultation): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient && $consultation->patient_id === $user->patient->id;
        }

        return $user->hasPermission('consultations.print');
    }
}
