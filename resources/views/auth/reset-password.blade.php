<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Set New Password — Meghna Clinic and Nursing Home</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-body d-flex flex-column min-vh-100 justify-content-between">
    <!-- Top Minimal Navigation -->
    <header class="py-3 px-4 border-bottom bg-white">
        <div class="container-xl d-flex justify-content-between align-items-center">
            <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                <span class="rounded bg-teal text-white p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-hospital fs-5"></i>
                </span>
                <div>
                    <span class="d-block fw-bold text-navy fs-6 lh-1">Meghna Clinic</span>
                    <span class="d-block text-muted-hms" style="font-size: 0.7rem;">Set New Password</span>
                </div>
            </a>
            <a href="{{ route('portal.login') }}" class="btn btn-hms-outline-teal btn-hms-sm">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </a>
        </div>
    </header>

    <!-- Main Reset Form Area -->
    <main class="container-xl py-5 my-auto">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5 col-xl-4">
                <div class="card border-0 shadow-sm hms-card p-3 p-sm-4 bg-white">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex p-3 rounded-circle bg-teal-subtle text-teal mb-3">
                            <i class="bi bi-shield-lock-fill fs-3"></i>
                        </div>
                        <h1 class="h4 fw-bold text-navy mb-1">Set New Password</h1>
                        <p class="text-muted-hms" style="font-size: 0.875rem;">
                            Choose a strong, secure password for your account.
                        </p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger py-2 d-flex align-items-center gap-2 mb-3" style="font-size: 0.85rem;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div>{{ $errors->first() }}</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}" class="d-flex flex-column gap-3">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <!-- Email -->
                        <div>
                            <label for="email" class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">
                                Registered Email <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                class="form-control @error('email') is-invalid @enderror" 
                                value="{{ old('email', $email) }}" 
                                required 
                                autofocus
                            >
                            @error('email')
                                <div class="text-danger mt-1" style="font-size: 0.75rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div>
                            <label for="password" class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">
                                New Password (Min. 8 characters) <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                placeholder="••••••••••••" 
                                required
                            >
                            @error('password')
                                <div class="text-danger mt-1" style="font-size: 0.75rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">
                                Confirm New Password <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="password" 
                                name="password_confirmation" 
                                id="password_confirmation" 
                                class="form-control" 
                                placeholder="••••••••••••" 
                                required
                            >
                        </div>

                        <x-button type="submit" variant="primary" size="md" class="w-100 mt-2">
                            <i class="bi bi-check2-circle me-1"></i> Update &amp; Reset Password
                        </x-button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-3 text-center border-top text-muted-hms bg-white" style="font-size: 0.8rem;">
        &copy; {{ date('Y') }} Meghna Clinic & Nursing Home &bull; Dinajpur, Bangladesh
    </footer>
</body>
</html>
