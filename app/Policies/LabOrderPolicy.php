<?php

namespace App\Policies;

use App\Models\LabOrder;
use App\Models\User;

class LabOrderPolicy
{
    /**
     * Determine whether the user can view any lab orders.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(['lab.view', 'laboratory.view']);
    }

    /**
     * Determine whether the user can view the specific lab order.
     */
    public function view(User $user, LabOrder $order): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient && $order->patient_id === $user->patient->id;
        }

        return $user->hasPermission(['lab.view', 'laboratory.view']);
    }

    /**
     * Determine whether the user can order lab tests.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission(['lab.create', 'laboratory.create']);
    }

    /**
     * Determine whether the user can update a lab order.
     */
    public function update(User $user, LabOrder $order): bool
    {
        return $user->hasPermission(['lab.update', 'laboratory.update']);
    }

    /**
     * Determine whether the user can delete a lab order.
     */
    public function delete(User $user, LabOrder $order): bool
    {
        return $user->hasPermission(['lab.delete', 'laboratory.delete']);
    }

    /**
     * Determine whether the user can approve/verify a lab report.
     */
    public function approve(User $user, LabOrder $order): bool
    {
        return $user->hasPermission(['lab.approve', 'laboratory.approve']);
    }

    /**
     * Determine whether the user can print the lab report.
     */
    public function print(User $user, LabOrder $order): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient && $order->patient_id === $user->patient->id;
        }

        return $user->hasPermission(['lab.print', 'laboratory.print']);
    }
}
