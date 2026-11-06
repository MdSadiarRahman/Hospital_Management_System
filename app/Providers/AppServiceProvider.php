<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Explicit Policy Registrations
        Gate::policy(\App\Models\Department::class, \App\Policies\DepartmentPolicy::class);
        Gate::policy(\App\Models\StaffProfile::class, \App\Policies\StaffPolicy::class);
        Gate::policy(\App\Models\Patient::class, \App\Policies\PatientPolicy::class);
        Gate::policy(\App\Models\Visit::class, \App\Policies\VisitPolicy::class);
        Gate::policy(\App\Models\Bed::class, \App\Policies\BedPolicy::class);
        Gate::policy(\App\Models\Admission::class, \App\Policies\AdmissionPolicy::class);
        Gate::policy(\App\Models\Appointment::class, \App\Policies\AppointmentPolicy::class);
        Gate::policy(\App\Models\Consultation::class, \App\Policies\ConsultationPolicy::class);
        Gate::policy(\App\Models\Prescription::class, \App\Policies\PrescriptionPolicy::class);

        // Super Admin universal bypass and granular permission resolution
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }

            if ($user->hasPermission($ability)) {
                return true;
            }

            return null;
        });

        // Blade Directive: @role('doctor') or @role(['doctor', 'nurse'])
        Blade::if('role', function (string|array $roles) {
            return auth()->check() && auth()->user()->hasRole($roles);
        });

        Blade::if('hasrole', function (string|array $roles) {
            return auth()->check() && auth()->user()->hasRole($roles);
        });

        // Blade Directive: @permission('patients.create') or @permission(['patients.view', 'patients.print'])
        Blade::if('permission', function (string|array $permissions) {
            return auth()->check() && auth()->user()->hasPermission($permissions);
        });
    }
}
