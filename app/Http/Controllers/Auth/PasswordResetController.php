<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Display the forgot password request view.
     */
    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send or generate a password reset token.
     */
    public function sendResetLink(ForgotPasswordRequest $request): RedirectResponse
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Avoid account enumeration in production, but provide clear message
            return back()->with('status', 'If an account exists with that email, a password reset link has been dispatched.');
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'auth.password_reset_requested',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => null,
            'new_values' => ['email' => $email],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $email]);

        return back()->with([
            'status' => 'Password reset token generated successfully. In development/local testing, you may use the link below.',
            'reset_url' => $resetUrl,
        ]);
    }

    /**
     * Display the reset password form with the token.
     */
    public function showResetPasswordForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->input('email'),
        ]);
    }

    /**
     * Reset the user password.
     */
    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $email = $request->input('email');
        $token = $request->input('token');
        $password = $request->input('password');

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record || !Hash::check($token, $record->token)) {
            return back()->withErrors(['email' => 'This password reset token is invalid or has expired.']);
        }

        // Check if token is older than 60 minutes
        if (now()->subMinutes(60)->greaterThan($record->created_at)) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return back()->withErrors(['email' => 'This password reset token has expired. Please request a new one.']);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We could not locate a user with this email address.']);
        }

        $user->forceFill([
            'password' => Hash::make($password),
            'remember_token' => Str::random(60),
        ])->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'auth.password_reset_completed',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => null,
            'new_values' => ['email' => $email],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        $redirectRoute = $user->user_type === 'patient' ? 'patient.login' : 'portal.login';

        return redirect()->route($redirectRoute)->with('status', 'Your password has been securely reset. Please log in with your new password.');
    }
}
