<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Staff Portal Login — Meghna Clinic and Nursing Home</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-body d-flex flex-column min-vh-100 justify-content-between">
    <!-- Top Minimal Navigation -->
    <header class="py-3 px-4 border-bottom bg-white">
        <div class="container-xl d-flex justify-content-between align-items-center">
            <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                <span class="rounded bg-primary text-white p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-hospital fs-5 text-teal"></i>
                </span>
                <div>
                    <span class="d-block fw-bold text-navy fs-6 lh-1">Meghna Clinic</span>
                    <span class="d-block text-muted-hms" style="font-size: 0.7rem;">& Nursing Home, Dinajpur</span>
                </div>
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
                        <div class="d-inline-flex p-3 rounded-circle bg-teal-subtle text-teal mb-3">
                            <i class="bi bi-shield-lock-fill fs-3"></i>
                        </div>
                        <h1 class="h4 fw-bold text-navy mb-1">Staff Portal Login</h1>
                        <p class="text-muted-hms" style="font-size: 0.875rem;">
                            Authorized personnel only &bull; Doctors, Nurses, Cashier, Reception & Administration.
                        </p>
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

                        <!-- Email Address -->
                        <div>
                            <label for="email" class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">
                                Staff Email Address <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted-hms"><i class="bi bi-envelope"></i></span>
                                <input 
                                    type="email" 
                                    name="email" 
                                    id="email" 
                                    class="form-control @error('email') is-invalid @enderror" 
                                    value="{{ old('email') }}" 
                                    placeholder="doctor@meghnaclinic.com" 
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
                                    Password <span class="text-danger">*</span>
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
                            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Portal
                        </x-button>
                    </form>

                    <!-- Quick Testing Credentials Badge Drawer -->
                    <div class="mt-4 pt-3 border-top">
                        <span class="d-block text-muted-hms fw-semibold mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            Quick Demo Credentials (Password: <code>Meghna@2026!</code>)
                        </span>
                        <div class="d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 0.7rem;" onclick="document.getElementById('email').value='admin@meghnaclinic.com'; document.getElementById('password').value='Meghna@2026!';">
                                Admin
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 0.7rem;" onclick="document.getElementById('email').value='doctor@meghnaclinic.com'; document.getElementById('password').value='Meghna@2026!';">
                                Doctor
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 0.7rem;" onclick="document.getElementById('email').value='nurse@meghnaclinic.com'; document.getElementById('password').value='Meghna@2026!';">
                                Nurse
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 0.7rem;" onclick="document.getElementById('email').value='reception@meghnaclinic.com'; document.getElementById('password').value='Meghna@2026!';">
                                Reception
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 0.7rem;" onclick="document.getElementById('email').value='billing@meghnaclinic.com'; document.getElementById('password').value='Meghna@2026!';">
                                Billing
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 0.7rem;" onclick="document.getElementById('email').value='lab@meghnaclinic.com'; document.getElementById('password').value='Meghna@2026!';">
                                Lab
                            </button>
                        </div>
                    </div>
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
