<?php

namespace App\Http\Requests\Auth;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PatientLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     * Temporary development default: auto-fill default patient credentials if omitted.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->filled('email') ? $this->input('email') : 'patient@meghnaclinic.com',
            'password' => $this->filled('password') ? $this->input('password') : 'Meghna@2026!',
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Attempt to authenticate patient credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $input = $this->input('email');
        $password = $this->input('password');
        $remember = $this->boolean('remember');

        // Allow login using either Email or Phone number
        $user = User::where(function ($query) use ($input) {
            $query->where('email', $input)
                  ->orWhere('phone', $input);
        })->first();

        if ($user && $user->user_type !== 'patient') {
            RateLimiter::hit($this->throttleKey());

            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'auth.unauthorized_patient_access',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'old_values' => null,
                'new_values' => ['input' => $input, 'reason' => 'Staff attempted patient portal login'],
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'created_at' => now(),
            ]);

            throw ValidationException::withMessages([
                'email' => __('This portal is for patients only. Hospital staff members must log in through the Staff Management Portal.'),
            ]);
        }

        if ($user && !$user->is_active) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('Your patient portal account has been deactivated. Please contact clinic reception.'),
            ]);
        }

        $credentials = [
            'email' => $user ? $user->email : $input,
            'password' => $password,
        ];

        if (!Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($this->throttleKey());

            AuditLog::create([
                'user_id' => $user?->id,
                'event' => 'auth.failed_patient_login',
                'auditable_type' => User::class,
                'auditable_id' => $user?->id,
                'old_values' => null,
                'new_values' => ['input' => $input, 'ip' => $this->ip()],
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'created_at' => now(),
            ]);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        // Audit Log Successful Patient Login
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'auth.patient_login',
            'auditable_type' => User::class,
            'auditable_id' => Auth::id(),
            'old_values' => null,
            'new_values' => ['portal' => 'patient', 'name' => Auth::user()->name],
            'ip_address' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
