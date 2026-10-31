<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    /**
     * Determine whether the user can view any departments.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('departments.view');
    }

    /**
     * Determine whether the user can view the specific department.
     */
    public function view(User $user, Department $department): bool
    {
        return $user->hasPermission('departments.view');
    }

    /**
     * Determine whether the user can create new departments.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('departments.create');
    }

    /**
     * Determine whether the user can update department details and status.
     */
    public function update(User $user, Department $department): bool
    {
        return $user->hasPermission('departments.update');
    }

    /**
     * Determine whether the user can decommission or delete a department.
     */
    public function delete(User $user, Department $department): bool
    {
        return $user->hasPermission('departments.delete');
    }

    /**
     * Determine whether the user can assign or transfer staff in this department.
     */
    public function manageStaff(User $user, Department $department): bool
    {
        return $user->hasPermission('departments.manage_staff');
    }

    /**
     * Determine whether the user can configure beds and tariffs in this department.
     */
    public function configureBeds(User $user, Department $department): bool
    {
        return $user->hasPermission('departments.configure_beds');
    }
}
