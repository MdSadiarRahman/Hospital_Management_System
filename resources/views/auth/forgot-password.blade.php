<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Forgot Password — Meghna Clinic and Nursing Home</title>
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
                    <span class="d-block text-muted-hms" style="font-size: 0.7rem;">Account Security & Recovery</span>
                </div>
            </a>
            <a href="{{ route('portal.login') }}" class="btn btn-hms-outline-teal btn-hms-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Login
            </a>
        </div>
    </header>

    <!-- Main Recovery Area -->
    <main class="container-xl py-5 my-auto">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5 col-xl-4">
                <div class="card border-0 shadow-sm hms-card p-3 p-sm-4 bg-white">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex p-3 rounded-circle bg-teal-subtle text-teal mb-3">
                            <i class="bi bi-key-fill fs-3"></i>
                        </div>
                        <h1 class="h4 fw-bold text-navy mb-1">Reset Password</h1>
                        <p class="text-muted-hms" style="font-size: 0.875rem;">
                            Enter your registered email address to receive a secure password reset token.
                        </p>
                    </div>

                    @if(session('status'))
                        <div class="alert alert-success d-flex flex-column gap-2 py-2" role="alert" style="font-size: 0.85rem;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>{{ session('status') }}</span>
                            </div>
                            @if(session('reset_url'))
                                <div class="mt-2 p-2 bg-white rounded border">
                                    <span class="d-block fw-bold text-navy" style="font-size: 0.75rem;">Testing Reset Link:</span>
                                    <a href="{{ session('reset_url') }}" class="text-teal text-break" style="font-size: 0.75rem;">
                                        {{ session('reset_url') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger py-2 d-flex align-items-center gap-2 mb-3" style="font-size: 0.85rem;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div>{{ $errors->first() }}</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="d-flex flex-column gap-3">
                        @csrf

                        <div>
                            <label for="email" class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">
                                Registered Email Address <span class="text-danger">*</span>
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

                        <x-button type="submit" variant="primary" size="md" class="w-100 mt-2">
                            <i class="bi bi-send me-1"></i> Send Password Reset Link
                        </x-button>
                    </form>

                    <div class="mt-4 pt-3 border-top text-center text-muted-hms" style="font-size: 0.85rem;">
                        Remember your credentials? <a href="{{ route('portal.login') }}" class="text-teal fw-semibold">Sign In</a>
                    </div>
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
