<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Patient Registration — Meghna Clinic and Nursing Home</title>
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
                    <span class="d-block text-muted-hms" style="font-size: 0.7rem;">Patient Self-Care Registration</span>
                </div>
            </a>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted-hms d-none d-sm-inline" style="font-size: 0.85rem;">Already registered?</span>
                <a href="{{ route('patient.login') }}" class="btn btn-hms-outline-teal btn-hms-sm">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                </a>
            </div>
        </div>
    </header>

    <!-- Main Registration Area -->
    <main class="container-xl py-5 my-auto">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-6 col-xl-5">
                <!-- Registration Card -->
                <div class="card border-0 shadow-sm hms-card p-3 p-sm-4 bg-white">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex p-3 rounded-circle bg-teal-subtle text-teal mb-3">
                            <i class="bi bi-person-plus-fill fs-3"></i>
                        </div>
                        <h1 class="h4 fw-bold text-navy mb-1">Create Patient Account</h1>
                        <p class="text-muted-hms" style="font-size: 0.875rem;">
                            Register for online appointments and digital prescription access.
                        </p>
                    </div>

                    <!-- Medical Privacy Rule Notice -->
                    <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-start gap-2" style="font-size: 0.8rem; background-color: rgba(8, 127, 140, 0.08); border-color: rgba(8, 127, 140, 0.2);">
                        <i class="bi bi-shield-lock-fill text-teal fs-5 mt-0.5"></i>
                        <div>
                            <strong class="text-navy d-block">Confidential Health Privacy Protocol:</strong>
                            Self-registration creates an unlinked portal account. To safeguard patient confidentiality, past hospital charts and diagnostic reports will only link after physical identity verification at Meghna Clinic reception.
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger py-2 d-flex align-items-center gap-2 mb-3" style="font-size: 0.85rem;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div>{{ $errors->first() }}</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('patient.register.submit') }}" class="d-flex flex-column gap-3">
                        @csrf

                        <!-- Full Name -->
                        <div>
                            <label for="name" class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">
                                Patient Full Name <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="name" 
                                id="name" 
                                class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name') }}" 
                                placeholder="e.g., Tanvir Hasan" 
                                required 
                                autofocus
                            >
                            @error('name')
                                <div class="text-danger mt-1" style="font-size: 0.75rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label for="phone" class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">
                                Contact Phone Number <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted-hms">+880</span>
                                <input 
                                    type="text" 
                                    name="phone" 
                                    id="phone" 
                                    class="form-control @error('phone') is-invalid @enderror" 
                                    value="{{ old('phone') }}" 
                                    placeholder="17XXXXXXXX" 
                                    required
                                >
                            </div>
                            @error('phone')
                                <div class="text-danger mt-1" style="font-size: 0.75rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email Address -->
                        <div>
                            <label for="email" class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                class="form-control @error('email') is-invalid @enderror" 
                                value="{{ old('email') }}" 
                                placeholder="name@example.com" 
                                required
                            >
                            @error('email')
                                <div class="text-danger mt-1" style="font-size: 0.75rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">
                                Password (Minimum 8 Characters) <span class="text-danger">*</span>
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
                                Confirm Password <span class="text-danger">*</span>
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

                        <!-- Terms & Conditions Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="terms" id="terms" value="1" {{ old('terms') ? 'checked' : '' }} required>
                            <label class="form-check-label text-muted-hms" for="terms" style="font-size: 0.8rem;">
                                I agree to the <a href="#" class="text-teal">Terms of Care</a> and consent to clinic privacy verification policies.
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <x-button type="submit" variant="teal" size="md" class="w-100 mt-2">
                            <i class="bi bi-check2-circle me-1"></i> Register Account
                        </x-button>
                    </form>

                    <div class="mt-4 pt-3 border-top text-center text-muted-hms" style="font-size: 0.85rem;">
                        Already have an account? <a href="{{ route('patient.login') }}" class="text-teal fw-semibold">Sign In</a>
                    </div>
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
