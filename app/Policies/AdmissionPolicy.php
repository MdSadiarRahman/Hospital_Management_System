<?php

namespace App\Policies;

use App\Models\Admission;
use App\Models\User;

class AdmissionPolicy
{
    /**
     * Determine whether the user can view any admissions.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->user_type !== 'staff') {
            return false;
        }

        return $user->hasAnyRole(['administrator', 'receptionist', 'doctor', 'nurse', 'billing_staff', 'ot_ward_staff'])
            || $user->hasPermission('admissions.view');
    }

    /**
     * Determine whether the user can view the specific admission.
     */
    public function view(User $user, Admission $admission): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Staff policy
        if ($user->user_type === 'staff') {
            return $user->hasAnyRole(['administrator', 'receptionist', 'doctor', 'nurse', 'billing_staff', 'ot_ward_staff'])
                || $user->hasPermission('admissions.view');
        }

        // Patient access - can only view their own verified admission
        if ($user->user_type === 'patient') {
            return $user->patient && $user->patient->id === $admission->patient_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create an inpatient admission.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->user_type !== 'staff') {
            return false;
        }

        return $user->hasAnyRole(['administrator', 'receptionist', 'doctor'])
            || $user->hasPermission('admissions.create');
    }

    /**
     * Determine whether the user can update the admission.
     */
    public function update(User $user, Admission $admission): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->user_type !== 'staff') {
            return false;
        }

        return $user->hasAnyRole(['administrator', 'receptionist', 'doctor'])
            || $user->hasPermission('admissions.update');
    }

    /**
     * Determine whether the user can transfer the patient between beds.
     */
    public function transferBed(User $user, Admission $admission): bool
    {
        if ($admission->status !== 'admitted') {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->user_type !== 'staff') {
            return false;
        }

        return $user->hasAnyRole(['administrator', 'receptionist', 'doctor', 'nurse'])
            || $user->hasPermission('beds.assign');
    }

    /**
     * Determine whether the user can cancel an admission.
     */
    public function cancel(User $user, Admission $admission): bool
    {
        if ($admission->status !== 'admitted') {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->user_type !== 'staff') {
            return false;
        }

        return $user->hasAnyRole(['administrator', 'doctor'])
            || $user->hasPermission('admissions.delete');
    }

    /**
     * Determine whether the user can delete an admission record.
     */
    public function delete(User $user, Admission $admission): bool
    {
        return $this->cancel($user, $admission);
    }
}

