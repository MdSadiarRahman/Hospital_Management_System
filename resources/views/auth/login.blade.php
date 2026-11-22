<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ !empty($roleData['name']) ? $roleData['name'] . ' Login' : 'Staff Portal Login' }} — Meghna Clinic and Nursing Home</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-icon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-body d-flex flex-column min-vh-100 justify-content-between">
    <!-- Top Minimal Navigation -->
    <header class="py-3 px-4 border-bottom bg-white">
        <div class="container-xl d-flex justify-content-between align-items-center">
            <a href="{{ route('home') }}" class="text-decoration-none">
                <x-brand-logo size="sm" theme="light" />
            </a>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted-hms d-none d-sm-inline" style="font-size: 0.85rem;">Are you a patient?</span>
                <a href="{{ route('patient.login') }}" class="btn btn-hms-outline-teal btn-hms-sm">
                    <i class="bi bi-person me-1"></i> Patient Portal
                </a>
            </div>
        </div>
    </header>

    <!-- Main Login Area -->
    <main class="container-xl py-5 my-auto">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5 col-xl-4">
                <!-- Login Card -->
                <div class="card border-0 shadow-sm hms-card p-3 p-sm-4 bg-white">
                    <div class="text-center mb-4">
                        @if(!empty($roleData))
                            <div class="d-inline-flex p-3 rounded-circle {{ $roleData['badge_class'] ?? 'bg-teal-subtle text-teal' }} mb-2">
                                <i class="bi {{ $roleData['icon'] ?? 'bi-shield-lock-fill' }} fs-3"></i>
                            </div>
                            <div class="mb-2">
                                <span class="badge {{ $roleData['badge_class'] ?? 'bg-secondary-subtle text-dark' }} px-3 py-1.5 fw-semibold" style="font-size: 0.8rem;">
                                    {{ $roleData['role_title'] ?? 'Staff Portal' }}
                                </span>
                            </div>
                            <h1 class="h4 fw-bold text-navy mb-1">{{ $roleData['name'] ?? 'Workstation' }} Login</h1>
                            <p class="text-muted-hms" style="font-size: 0.85rem; line-height: 1.45;">
                                {{ $roleData['subtitle'] ?? 'Please sign in with your authorized credentials to access your clinical workstation.' }}
                            </p>
                        @else
                            <div class="d-inline-flex p-3 rounded-circle bg-teal-subtle text-teal mb-3">
                                <i class="bi bi-shield-lock-fill fs-3"></i>
                            </div>
                            <h1 class="h4 fw-bold text-navy mb-1">Staff Portal Login</h1>
                            <p class="text-muted-hms" style="font-size: 0.875rem;">
                                Authorized personnel only &bull; Doctors, Nurses, Cashier, Reception & Administration.
                            </p>
                        @endif
                    </div>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 py-2" role="alert" style="font-size: 0.85rem;">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>{{ session('status') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger py-2 d-flex align-items-center gap-2 mb-3" style="font-size: 0.85rem;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div>{{ $errors->first() }}</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('portal.login.submit') }}" class="d-flex flex-column gap-3">
                        @csrf
                        @if(!empty($activeRole))
                            <input type="hidden" name="intended_role" value="{{ $activeRole }}">
                        @endif

                        <!-- Email Address -->
                        <div>
                            <label for="email" class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">
                                {{ !empty($roleData['role_title']) ? $roleData['role_title'] . ' Email Address' : 'Staff Email Address' }}
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted-hms"><i class="bi bi-envelope"></i></span>
                                <input 
                                    type="email" 
                                    name="email" 
                                    id="email" 
                                    class="form-control @error('email') is-invalid @enderror" 
                                    value="{{ old('email', !empty($roleData['sample_email']) ? $roleData['sample_email'] : '') }}" 
                                    placeholder="{{ $roleData['sample_email'] ?? 'e.g. staff@meghnaclinic.com' }}" 
                                    required
                                    autofocus
                                >
                            </div>
                            @error('email')
                                <div class="text-danger mt-1" style="font-size: 0.75rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="password" class="form-label text-navy fw-semibold mb-0" style="font-size: 0.85rem;">
                                    Password
                                </label>
                                <a href="{{ route('password.request') }}" class="text-teal text-decoration-none" style="font-size: 0.775rem;">
                                    Forgot password?
                                </a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted-hms"><i class="bi bi-key"></i></span>
                                <input 
                                    type="password" 
                                    name="password" 
                                    id="password" 
                                    class="form-control @error('password') is-invalid @enderror" 
                                    placeholder="••••••••••••"
                                    required
                                >
                            </div>
                            @error('password')
                                <div class="text-danger mt-1" style="font-size: 0.75rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                            <label class="form-check-label text-muted-hms" for="remember" style="font-size: 0.825rem;">
                                Keep me signed in on this workstation
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <x-button type="submit" variant="primary" size="md" class="w-100 mt-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to {{ !empty($roleData['name']) ? $roleData['name'] : 'Portal' }}
                        </x-button>

                        @if(!empty($roleData))
                            <!-- Role Credentials Test Hint -->
                            <div class="mt-2 p-2.5 rounded-3 bg-light border text-muted-hms" style="font-size: 0.78rem;">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="fw-semibold text-navy"><i class="bi bi-info-circle me-1 text-teal"></i> Workstation Demo Access</span>
                                    <span class="badge bg-white border text-secondary" style="font-size: 0.7rem;">Default Credential</span>
                                </div>
                                <div class="font-monospace text-navy" style="font-size: 0.75rem;">
                                    <div><strong>Email:</strong> {{ $roleData['sample_email'] }}</div>
                                    <div><strong>Pass:</strong> {{ $roleData['default_password'] ?? 'Meghna@2026!' }}</div>
                                </div>
                            </div>

                            <div class="text-center pt-2 border-top mt-1">
                                <a href="{{ route('portal.login') }}" class="text-decoration-none text-muted-hms" style="font-size: 0.8rem;">
                                    <i class="bi bi-arrow-left me-1"></i> Switch to Unified Staff Portal
                                </a>
                            </div>
                        @else
                            <!-- Dedicated Workstation Login Selectors -->
                            <div class="pt-3 border-top mt-1">
                                <div class="text-muted-hms mb-2 text-center" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">
                                    Dedicated Workstations
                                </div>
                                <div class="d-flex flex-wrap gap-1.5 justify-content-center">
                                    <a href="{{ route('portal.login.role', 'doctor') }}" class="badge text-decoration-none bg-info-subtle text-teal py-1.5 px-2">Doctor</a>
                                    <a href="{{ route('portal.login.role', 'nurse') }}" class="badge text-decoration-none bg-success-subtle text-success py-1.5 px-2">Nurse</a>
                                    <a href="{{ route('portal.login.role', 'reception') }}" class="badge text-decoration-none bg-warning-subtle text-dark py-1.5 px-2">Reception</a>
                                    <a href="{{ route('portal.login.role', 'billing') }}" class="badge text-decoration-none bg-danger-subtle text-danger py-1.5 px-2">Billing</a>
                                    <a href="{{ route('portal.login.role', 'lab') }}" class="badge text-decoration-none bg-info-subtle text-info py-1.5 px-2">Laboratory</a>
                                    <a href="{{ route('portal.login.role', 'admin') }}" class="badge text-decoration-none bg-secondary-subtle text-navy py-1.5 px-2">Admin</a>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>

                <!-- Security Note -->
                <div class="text-center mt-3 text-muted-hms" style="font-size: 0.75rem;">
                    <i class="bi bi-shield-check text-teal me-1"></i> Protected by Rate-limiting &amp; Session Invalidation Protocols
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-3 text-center border-top text-muted-hms bg-white" style="font-size: 0.8rem;">
        &copy; {{ date('Y') }} Meghna Clinic & Nursing Home &bull; Shohid Minar Mor, Dinajpur, Bangladesh
    </footer>
</body>
</html>
