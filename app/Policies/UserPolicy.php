<?php

namespace App\Policies;

use App\Models\User;
use App\Services\RoleAssignmentService;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->hasPermission('users.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('users.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->hasPermission('users.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Users cannot delete their own account
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasPermission('users.delete');
    }

    /**
     * Determine whether the user can assign a role to the target user.
     * Enforces the critical rule: Users cannot assign themselves administrative roles.
     */
    public function assignRole(User $user, User $target, string $role): bool
    {
        $isAdminRole = in_array($role, RoleAssignmentService::ADMINISTRATIVE_ROLES, true);

        // Anti-self-escalation: Under NO circumstances can a user grant themselves an administrative role
        if ($user->id === $target->id && $isAdminRole) {
            return false;
        }

        // Only a Super Admin can grant administrative roles
        if ($isAdminRole) {
            return $user->hasRole('super_admin');
        }

        // For other roles, requires users.assign_role permission
        return $user->hasPermission('users.assign_role');
    }
}
