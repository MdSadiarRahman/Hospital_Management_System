<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePatient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->guest(route('patient.login'));
        }

        $user = Auth::user();

        if ($user->user_type !== 'patient') {
            return redirect()->route('portal.dashboard')->with('error', 'Patient portal is reserved for registered patients.');
        }

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('patient.login')->withErrors([
                'email' => 'Your patient account has been deactivated. Please contact reception.',
            ]);
        }

        return $next($request);
    }
}
