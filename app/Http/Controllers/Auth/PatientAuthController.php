<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PatientLoginRequest;
use App\Http\Requests\Auth\PatientRegisterRequest;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PatientAuthController extends Controller
{
    /**
     * Display the patient portal login view.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            if (Auth::user()->user_type === 'patient') {
                return redirect()->route('patient.dashboard');
            }
            return redirect()->route('portal.dashboard');
        }

        return view('auth.patient-login');
    }

    /**
     * Handle an incoming patient authentication request.
     */
    public function login(PatientLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('patient.dashboard'));
    }

    /**
     * Display the patient self-registration form.
     */
    public function showRegisterForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('patient.dashboard');
        }

        return view('auth.patient-register');
    }

    /**
     * Handle patient self-registration.
     * Enforces the Privacy Rule: Online accounts are never automatically linked to existing medical charts.
     */
    public function register(PatientRegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'patient',
            'is_active' => true,
        ]);

        // Attach patient role
        $patientRole = Role::where('name', 'patient')->first();
        if ($patientRole) {
            $user->roles()->attach($patientRole->id);
        }

        // Audit Log registration
        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'patient.registered_unlinked',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => null,
            'new_values' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'linked_patient_record' => null,
                'status' => 'Pending In-Person Physical Verification at Reception',
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('patient.dashboard')->with('status', 'Your patient portal account has been registered. Please present your phone or ID at Meghna Clinic reception on your next visit to link your past clinical charts.');
    }

    /**
     * Destroy a patient authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'auth.patient_logout',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'old_values' => null,
                'new_values' => ['portal' => 'patient'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('patient.login')->with('status', 'You have been safely signed out of your Patient Portal.');
    }

    /**
     * Display the unlinked patient verification notice.
     */
    public function unlinkedNotice(): View
    {
        return view('patient.unlinked');
    }
}
