<?php

namespace App\Policies;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\NursingNote;
use App\Models\User;

class NursingPolicy
{
    /**
     * Determine whether the user can view the nursing dashboard and records.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['nurse', 'doctor', 'administrator', 'super_admin'])
            || $user->hasPermission('nursing.view');
    }

    /**
     * Determine whether the user can view a specific nursing note.
     */
    public function view(User $user, NursingNote $note): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can record vital signs and observations.
     */
    public function recordVitals(User $user, ?Admission $admission = null): bool
    {
        if ($admission && $admission->status !== 'admitted') {
            return false;
        }

        return $user->hasRole(['nurse', 'super_admin', 'administrator', 'doctor'])
            || $user->hasPermission('nursing.record');
    }

    /**
     * Determine whether the user can record medication administration (MAR).
     */
    public function recordMar(User $user, ?Admission $admission = null): bool
    {
        if ($admission && $admission->status !== 'admitted') {
            return false;
        }

        return $user->hasRole(['nurse', 'super_admin', 'administrator', 'doctor'])
            || $user->hasPermission('nursing.record');
    }

    /**
     * Determine whether the user can update bed cleaning and readiness status.
     */
    public function updateBedReadiness(User $user, Bed $bed): bool
    {
        return $user->hasRole(['nurse', 'ot_ward_staff', 'administrator', 'super_admin'])
            || $user->hasPermission('beds.status_update');
    }
}

