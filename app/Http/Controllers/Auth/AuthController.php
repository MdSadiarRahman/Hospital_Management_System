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
     * Display the staff portal login view.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectForRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming staff authentication request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return $this->redirectForRole($request->user());
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
