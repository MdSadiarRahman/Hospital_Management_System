<?php

namespace App\Policies;

use App\Models\StaffProfile;
use App\Models\User;

class StaffPolicy
{
    /**
     * Determine whether the user can view the staff directory.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('staff.view') || $user->hasPermission('users.view');
    }

    /**
     * Determine whether the user can view the specific staff profile.
     */
    public function view(User $user, StaffProfile $profile): bool
    {
        // Staff members can always view their own profile
        if ($user->id === $profile->user_id) {
            return true;
        }

        // Internal hospital staff can view colleague professional profiles (sensitive details like salary/nid remain masked by viewSensitive)
        if ($user->isStaff() || $user->hasPermission('staff.view') || $user->hasPermission('users.view')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can register new staff.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('staff.create') || $user->hasPermission('users.create');
    }

    /**
     * Determine whether the user can update staff information.
     */
    public function update(User $user, StaffProfile $profile): bool
    {
        return $user->hasPermission('staff.update') || $user->hasPermission('users.update');
    }

    /**
     * Determine whether the user can deactivate or delete a staff member.
     */
    public function delete(User $user, StaffProfile $profile): bool
    {
        // Prevent deleting oneself
        if ($user->id === $profile->user_id) {
            return false;
        }

        return $user->hasPermission('staff.delete') || $user->hasPermission('users.delete');
    }

    /**
     * Determine whether the user can view sensitive employment data (salary, NID).
     */
    public function viewSensitive(User $user, StaffProfile $profile): bool
    {
        // Own profile owner can view their own financial & NID data
        if ($user->id === $profile->user_id) {
            return true;
        }

        return $user->hasPermission('staff.view_sensitive');
    }

    /**
     * Determine whether the user can schedule or manage 8h nursing / doctor shifts.
     */
    public function scheduleShifts(User $user): bool
    {
        return $user->hasPermission('staff.schedule_shifts');
    }
}
