<?php

namespace App\Policies;

use App\Models\Bed;
use App\Models\User;

class BedPolicy
{
    /**
     * Determine whether the user can view any beds.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('beds.view');
    }

    /**
     * Determine whether the user can view the specific bed.
     */
    public function view(User $user, Bed $bed): bool
    {
        return $user->hasPermission('beds.view');
    }

    /**
     * Determine whether the user can create new beds.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('beds.create');
    }

    /**
     * Determine whether the user can update bed details.
     */
    public function update(User $user, Bed $bed): bool
    {
        return $user->hasPermission('beds.update');
    }

    /**
     * Determine whether the user can delete a bed.
     */
    public function delete(User $user, Bed $bed): bool
    {
        return $user->hasPermission('beds.delete');
    }

    /**
     * Determine whether the user can assign/transfer a patient to this bed.
     */
    public function assign(User $user, Bed $bed): bool
    {
        return $user->hasPermission('beds.assign');
    }

    /**
     * Determine whether the user can update bed status (e.g., mark as cleaning/available).
     */
    public function statusUpdate(User $user, Bed $bed): bool
    {
        return $user->hasPermission('beds.status_update');
    }
}
