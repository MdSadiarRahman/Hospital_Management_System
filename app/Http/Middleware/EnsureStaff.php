<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaff
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->guest(route('portal.login'));
        }

        $user = Auth::user();

        if ($user->user_type !== 'staff') {
            return redirect()->route('patient.dashboard')->with('error', 'Staff portal access restricted to hospital employees.');
        }

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('portal.login')->withErrors([
                'email' => 'Your staff account has been deactivated. Please contact the administrator.',
            ]);
        }

        return $next($request);
    }
}
