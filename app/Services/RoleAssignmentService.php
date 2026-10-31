<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class RoleAssignmentService
{
    /**
     * Administrative roles that have high-privilege access.
     */
    public const ADMINISTRATIVE_ROLES = ['super_admin', 'administrator'];

    /**
     * Assign a role to a target user with strict anti-self-escalation guards.
     *
     * @throws AuthorizationException
     */
    public function assignRole(User $actor, User $target, Role|string $role): User
    {
        $roleModel = is_string($role) ? Role::where('name', $role)->firstOrFail() : $role;
        $roleName = $roleModel->name;

        $this->validateRoleAssignment($actor, $target, $roleName);

        DB::transaction(function () use ($actor, $target, $roleModel, $roleName) {
            $oldRoles = $target->roles->pluck('name')->toArray();

            $target->roles()->syncWithoutDetaching([$roleModel->id]);
            $target->load('roles');

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'role.assigned',
                'auditable_type' => User::class,
                'auditable_id' => $target->id,
                'old_values' => ['roles' => $oldRoles],
                'new_values' => ['role_assigned' => $roleName, 'current_roles' => $target->roles->pluck('name')->toArray()],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System/Service',
                'created_at' => now(),
            ]);
        });

        return $target;
    }

    /**
     * Remove a role from a target user with privilege hierarchy checks.
     *
     * @throws AuthorizationException
     */
    public function removeRole(User $actor, User $target, Role|string $role): User
    {
        $roleModel = is_string($role) ? Role::where('name', $role)->firstOrFail() : $role;
        $roleName = $roleModel->name;

        $this->validateRoleRevocation($actor, $target, $roleName);

        DB::transaction(function () use ($actor, $target, $roleModel, $roleName) {
            $oldRoles = $target->roles->pluck('name')->toArray();

            $target->roles()->detach($roleModel->id);
            $target->load('roles');

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'role.revoked',
                'auditable_type' => User::class,
                'auditable_id' => $target->id,
                'old_values' => ['roles' => $oldRoles],
                'new_values' => ['role_revoked' => $roleName, 'current_roles' => $target->roles->pluck('name')->toArray()],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System/Service',
                'created_at' => now(),
            ]);
        });

        return $target;
    }

    /**
     * Sync roles for a target user with full anti-self-escalation validation.
     *
     * @throws AuthorizationException
     */
    public function syncRoles(User $actor, User $target, array $roles): User
    {
        // Resolve role models/names
        $roleModels = Role::whereIn('name', $roles)
            ->orWhereIn('id', array_filter($roles, 'is_numeric'))
            ->get();

        $targetRoleNames = $roleModels->pluck('name')->toArray();

        foreach ($targetRoleNames as $roleName) {
            $this->validateRoleAssignment($actor, $target, $roleName);
        }

        DB::transaction(function () use ($actor, $target, $roleModels, $targetRoleNames) {
            $oldRoles = $target->roles->pluck('name')->toArray();

            $target->roles()->sync($roleModels->pluck('id'));
            $target->load('roles');

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'roles.synced',
                'auditable_type' => User::class,
                'auditable_id' => $target->id,
                'old_values' => ['roles' => $oldRoles],
                'new_values' => ['roles' => $targetRoleNames],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System/Service',
                'created_at' => now(),
            ]);
        });

        return $target;
    }

    /**
     * Sync permissions assigned to a role.
     *
     * @throws AuthorizationException
     */
    public function syncRolePermissions(User $actor, Role $role, array $permissionNames): Role
    {
        if (!$actor->hasRole('super_admin') && !$actor->hasPermission('roles.manage')) {
            throw new AuthorizationException('Unauthorized: You do not possess the authority to manage role permissions.');
        }

        // Prevent modification of the primary super_admin role permissions
        if ($role->name === 'super_admin' && !$actor->hasRole('super_admin')) {
            throw new AuthorizationException('Unauthorized: Super Admin permissions cannot be modified.');
        }

        $permissions = Permission::whereIn('name', $permissionNames)->get();

        DB::transaction(function () use ($actor, $role, $permissions, $permissionNames) {
            $oldPerms = $role->permissions->pluck('name')->toArray();

            $role->permissions()->sync($permissions->pluck('id'));
            $role->load('permissions');

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'role_permissions.synced',
                'auditable_type' => Role::class,
                'auditable_id' => $role->id,
                'old_values' => ['permissions' => $oldPerms],
                'new_values' => ['permissions' => $permissionNames],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System/Service',
                'created_at' => now(),
            ]);
        });

        return $role;
    }

    /**
     * Validate role assignment constraints and anti-self-escalation policy.
     *
     * @throws AuthorizationException
     */
    protected function validateRoleAssignment(User $actor, User $target, string $roleName): void
    {
        // 1. Strict Anti-Self-Escalation Rule: Users CANNOT assign themselves administrative roles
        if ($actor->id === $target->id && in_array($roleName, self::ADMINISTRATIVE_ROLES, true)) {
            throw new AuthorizationException('Security Violation: Users are strictly forbidden from assigning themselves administrative roles.');
        }

        // 2. Check basic authorization to assign roles
        if (!$actor->hasRole('super_admin') && !$actor->hasPermission('users.assign_role')) {
            throw new AuthorizationException('Unauthorized: You lack the permission to assign user roles.');
        }

        // 3. Escalation Protection: Only a Super Admin can assign the super_admin or administrator roles
        if (in_array($roleName, self::ADMINISTRATIVE_ROLES, true) && !$actor->hasRole('super_admin')) {
            throw new AuthorizationException('Security Violation: Only a Super Admin may grant administrative roles (' . $roleName . ').');
        }

        // 4. Role Hierarchy: Non-super-admins cannot manage users with administrative roles
        if ($target->hasRole(self::ADMINISTRATIVE_ROLES) && !$actor->hasRole('super_admin') && $actor->id !== $target->id) {
            throw new AuthorizationException('Security Violation: You do not possess higher authorization than the target administrative account.');
        }
    }

    /**
     * Validate role revocation constraints.
     *
     * @throws AuthorizationException
     */
    protected function validateRoleRevocation(User $actor, User $target, string $roleName): void
    {
        if (!$actor->hasRole('super_admin') && !$actor->hasPermission('users.assign_role')) {
            throw new AuthorizationException('Unauthorized: You lack the permission to revoke user roles.');
        }

        // Prevent self-demotion if it would leave the system without a Super Admin
        if ($actor->id === $target->id && $roleName === 'super_admin') {
            $superAdminCount = DB::table('role_user')
                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->where('roles.name', 'super_admin')
                ->count();

            if ($superAdminCount <= 1) {
                throw new AuthorizationException('Security Guard: Cannot revoke the sole remaining Super Admin account.');
            }
        }

        // Non-super-admins cannot revoke administrative roles
        if (in_array($roleName, self::ADMINISTRATIVE_ROLES, true) && !$actor->hasRole('super_admin')) {
            throw new AuthorizationException('Security Violation: Only a Super Admin may revoke administrative roles.');
        }
    }
}
