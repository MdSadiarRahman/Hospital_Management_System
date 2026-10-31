<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\User;

class BillPolicy
{
    /**
     * Determine whether the user can view any bills.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('billing.view');
    }

    /**
     * Determine whether the user can view the specific bill.
     */
    public function view(User $user, Bill $bill): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient && $bill->patient_id === $user->patient->id;
        }

        return $user->hasPermission('billing.view');
    }

    /**
     * Determine whether the cashier can create a bill.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('billing.create');
    }

    /**
     * Determine whether the user can update the bill.
     */
    public function update(User $user, Bill $bill): bool
    {
        return $user->hasPermission('billing.update');
    }

    /**
     * Determine whether the user can delete/void a bill.
     */
    public function delete(User $user, Bill $bill): bool
    {
        return $user->hasPermission('billing.delete');
    }

    /**
     * Determine whether the user can approve discounts/waivers or financial clearance.
     */
    public function approve(User $user, Bill $bill): bool
    {
        return $user->hasPermission('billing.approve');
    }

    /**
     * Determine whether the user can print the money receipt / invoice.
     */
    public function print(User $user, Bill $bill): bool
    {
        if ($user->user_type === 'patient') {
            return $user->patient && $bill->patient_id === $user->patient->id;
        }

        return $user->hasPermission('billing.print');
    }
}
