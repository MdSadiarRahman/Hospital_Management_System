<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    /**
     * Determine whether the user can view any visits.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('visits.view');
    }

    /**
     * Determine whether the user can view the specific visit.
     */
    public function view(User $user, Visit $visit): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient && (int)$user->patient->id === (int)$visit->patient_id;
        }

        return $user->hasPermission('visits.view');
    }

    /**
     * Determine whether the user can create a visit.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('visits.create');
    }

    /**
     * Determine whether the user can update the visit.
     */
    public function update(User $user, Visit $visit): bool
    {
        return $user->hasPermission('visits.update');
    }

    /**
     * Determine whether the user can cancel/delete a visit.
     */
    public function delete(User $user, Visit $visit): bool
    {
        return $user->hasPermission('visits.delete');
    }

    /**
     * Determine whether the user can print the visit token / summary.
     */
    public function print(User $user, Visit $visit): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient && (int)$user->patient->id === (int)$visit->patient_id;
        }

        return $user->hasPermission('visits.print');
    }
}
