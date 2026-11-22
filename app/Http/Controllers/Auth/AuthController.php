<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Role login configurations and workstation metadata.
     */
    public static function getRoleConfigs(): array
    {
        return [
            'doctor' => [
                'name' => 'Doctor Clinical Workstation',
                'role_title' => 'Medical Officer / Consultant',
                'subtitle' => 'OPD consultation queue, digital prescriptions, diagnostic orders, and 10-bed ward rounds.',
                'icon' => 'bi-heart-pulse-fill',
                'icon_color' => 'text-teal',
                'badge_class' => 'bg-info-subtle text-teal',
                'role_key' => 'doctor',
                'sample_email' => 'dr.nasreen@gmail.com',
                'default_password' => 'Doctor@123',
                'target_dashboard' => 'portal.doctor',
            ],
            'nurse' => [
                'name' => 'Nurse Ward Station',
                'role_title' => 'Staff Nurse on Duty',
                'subtitle' => '10 inpatient beds monitor, bedside vitals logging, medication administration (MAR) & shift handover.',
                'icon' => 'bi-clipboard2-pulse',
                'icon_color' => 'text-success',
                'badge_class' => 'bg-success-subtle text-success',
                'role_key' => 'nurse',
                'sample_email' => 'nurse@meghnaclinic.com',
                'default_password' => 'Meghna@2026!',
                'target_dashboard' => 'portal.nurse',
            ],
            'reception' => [
                'name' => 'Reception & Patient Intake Desk',
                'role_title' => 'Front Desk Receptionist',
                'subtitle' => 'Patient master registration, duplicate check (NID/Phone), visit tokens, and emergency admissions.',
                'icon' => 'bi-person-vcard',
                'icon_color' => 'text-warning',
                'badge_class' => 'bg-warning-subtle text-dark',
                'role_key' => 'receptionist',
                'sample_email' => 'reception@meghnaclinic.com',
                'default_password' => 'Meghna@2026!',
                'target_dashboard' => 'portal.reception',
            ],
            'billing' => [
                'name' => 'Billing & Cashier Desk',
                'role_title' => 'Cashier / Billing Officer',
                'subtitle' => 'Itemized patient invoicing, daily money receipts, payment collection, and discharge financial clearance.',
                'icon' => 'bi-cash-stack',
                'icon_color' => 'text-danger',
                'badge_class' => 'bg-danger-subtle text-danger',
                'role_key' => 'billing_staff',
                'sample_email' => 'billing@meghnaclinic.com',
                'default_password' => 'Meghna@2026!',
                'target_dashboard' => 'portal.billing',
            ],
            'lab' => [
                'name' => 'Diagnostic Laboratory Desk',
                'role_title' => 'Medical Technologist / Pathologist',
                'subtitle' => 'Pathology orders queue, sample tracking, test results authoring, and external partner lab dispatch.',
                'icon' => 'bi-virus',
                'icon_color' => 'text-info',
                'badge_class' => 'bg-info-subtle text-info',
                'role_key' => 'lab_staff',
                'sample_email' => 'lab@meghnaclinic.com',
                'default_password' => 'Meghna@2026!',
                'target_dashboard' => 'portal.lab',
            ],
            'admin' => [
                'name' => 'Hospital Administration Console',
                'role_title' => 'Hospital Administrator',
                'subtitle' => 'Full administrative oversight, 14 staff roster, 10 beds tariff, revenue reporting & security audit ledger.',
                'icon' => 'bi-shield-check',
                'icon_color' => 'text-navy',
                'badge_class' => 'bg-primary-subtle text-primary',
                'role_key' => 'administrator',
                'sample_email' => 'admin@meghnaclinic.com',
                'default_password' => 'Meghna@2026!',
                'target_dashboard' => 'portal.admin',
            ],
        ];
    }

    /**
     * Display the staff portal login view.
     */
    public function showLoginForm(Request $request): View|RedirectResponse
    {
        $role = $request->query('role');
        if ($role) {
            return $this->showRoleLoginForm($role);
        }

        if (Auth::check()) {
            return $this->redirectForRole(Auth::user());
        }

        return view('auth.login', [
            'roleData' => null,
            'activeRole' => null,
            'allRoles' => self::getRoleConfigs(),
        ]);
    }

    /**
     * Display a dedicated role-specific login view.
     */
    public function showRoleLoginForm(string $role): View|RedirectResponse
    {
        $role = strtolower(trim($role));
        $roleConfigs = self::getRoleConfigs();

        if (!isset($roleConfigs[$role])) {
            return redirect()->route('portal.login');
        }

        $roleData = $roleConfigs[$role];

        if (Auth::check()) {
            /** @var User $currentUser */
            $currentUser = Auth::user();
            if ($this->userHasTargetRole($currentUser, $role)) {
                return redirect()->intended(route($roleData['target_dashboard']));
            }
            return $this->redirectForRole($currentUser);
        }

        return view('auth.login', [
            'roleData' => $roleData,
            'activeRole' => $role,
            'allRoles' => $roleConfigs,
        ]);
    }

    /**
     * Handle an incoming staff authentication request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        $intendedRole = $request->input('intended_role');

        if ($intendedRole) {
            $roleConfigs = self::getRoleConfigs();
            if (isset($roleConfigs[$intendedRole]) && $this->userHasTargetRole($user, $intendedRole)) {
                return redirect()->intended(route($roleConfigs[$intendedRole]['target_dashboard']));
            }
        }

        return $this->redirectForRole($user);
    }

    /**
     * Check if a staff user is authorized for a specific workstation role.
     */
    public function userHasTargetRole(User $user, string $role): bool
    {
        if ($user->hasRole(['super_admin', 'administrator'])) {
            return true;
        }

        return match ($role) {
            'doctor' => $user->hasRole('doctor'),
            'nurse' => $user->hasRole('nurse'),
            'reception', 'receptionist' => $user->hasRole('receptionist'),
            'billing', 'billing_staff' => $user->hasRole('billing_staff'),
            'lab', 'lab_staff' => $user->hasRole('lab_staff'),
            'admin', 'administrator' => $user->hasRole(['administrator', 'super_admin']),
            default => false,
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'auth.logout',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'old_values' => null,
                'new_values' => ['portal' => 'staff'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login')->with('status', 'You have been safely signed out of the hospital portal.');
    }

    /**
     * Redirect authenticated user to their role-authorized dashboard.
     */
    public function redirectForRole(User $user): RedirectResponse
    {
        if ($user->user_type === 'patient') {
            return redirect()->intended(route('patient.dashboard'));
        }

        if ($user->hasRole(['super_admin', 'administrator'])) {
            return redirect()->intended(route('portal.admin'));
        }

        if ($user->hasRole('doctor')) {
            return redirect()->intended(route('portal.doctor'));
        }

        if ($user->hasRole('nurse')) {
            return redirect()->intended(route('portal.nurse'));
        }

        if ($user->hasRole('receptionist')) {
            return redirect()->intended(route('portal.reception'));
        }

        if ($user->hasRole('billing_staff')) {
            return redirect()->intended(route('portal.billing'));
        }

        if ($user->hasRole('lab_staff')) {
            return redirect()->intended(route('portal.lab'));
        }

        if ($user->hasRole('ot_ward_staff')) {
            return redirect()->intended(route('portal.ot-ward'));
        }

        return redirect()->intended(route('portal.dashboard'));
    }

    /**
     * Generic Staff Portal Dashboard Dispatcher.
     */
    public function dashboard(): View|RedirectResponse
    {
        return $this->redirectForRole(Auth::user());
    }
}
