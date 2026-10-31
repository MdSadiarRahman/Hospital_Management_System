<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePatientLinked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || $user->user_type !== 'patient') {
            abort(403, 'Unauthorized access.');
        }

        // Check if user has an associated clinical patient record
        if (!$user->patient) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your patient account is pending identity verification at reception.',
                    'linked' => false,
                ], 403);
            }

            return redirect()->route('patient.unlinked');
        }

        return $next($request);
    }
}
