@props([
    'title' => null,
    'metaDescription' => null,
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <title>{{ $title ? $title . ' — ' : '' }}Meghna Clinic and Nursing Home, Dinajpur</title>
    <meta name="description" content="{{ $metaDescription ?? 'Meghna Clinic and Nursing Home in Dinajpur provides 24/7 emergency care, 10 inpatient beds, gynecological and orthopedic surgery, and diagnostic pathology.' }}">
    <meta name="author" content="Meghna Clinic and Nursing Home">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Social Sharing -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Meghna Clinic and Nursing Home">
    <meta property="og:title" content="{{ $title ? $title . ' — ' : '' }}Meghna Clinic and Nursing Home, Dinajpur">
    <meta property="og:description" content="{{ $metaDescription ?? 'Meghna Clinic and Nursing Home in Dinajpur provides 24/7 emergency care, 10 inpatient beds, gynecological and orthopedic surgery, and diagnostic pathology.' }}">
    <meta property="og:url" content="{{ url()->current() }}">

    <!-- Design System Assets via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="antialiased d-flex flex-column min-vh-100 bg-body">
    <!-- Top Contact & Emergency Bar -->
    <div class="public-topbar">
        <div class="container-xl d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-3">
                <span><i class="bi bi-geo-alt-fill text-teal me-1"></i> Shohid Minar Mor, Dinajpur, Bangladesh</span>
                <span class="d-none d-md-inline text-white-50">|</span>
                <span class="d-none d-md-inline"><i class="bi bi-clock-fill text-teal me-1"></i> 24/7 Emergency & Inpatient Care</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="tel:+8801700000000" class="text-white text-decoration-none fw-semibold">
                    <i class="bi bi-telephone-fill text-teal me-1"></i> +880 1700-000000
                </a>
                <span class="badge bg-danger text-white px-2 py-1">Emergency 24/7</span>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="public-navbar">
        <div class="container-xl d-flex align-items-center justify-content-between">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                <span class="rounded bg-primary text-white p-2 d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                    <i class="bi bi-hospital fs-4 text-teal"></i>
                </span>
                <div>
                    <span class="d-block fw-bold text-navy fs-5 lh-1">Meghna Clinic</span>
                    <span class="d-block text-muted-hms" style="font-size: 0.75rem;">& Nursing Home, Dinajpur</span>
                </div>
            </a>

            <!-- Desktop Links -->
            <div class="d-none d-xl-flex align-items-center gap-1">
                <a href="{{ route('home') }}" class="public-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('about') }}" class="public-nav-link {{ request()->routeIs('about') ? 'active' : '' }}">About Clinic</a>
                <a href="{{ route('departments') }}" class="public-nav-link {{ request()->routeIs('departments*') ? 'active' : '' }}">Departments</a>
                <a href="{{ route('doctors') }}" class="public-nav-link {{ request()->routeIs('doctors*') ? 'active' : '' }}">Doctors</a>
                <a href="{{ route('services') }}" class="public-nav-link {{ request()->routeIs('services') ? 'active' : '' }}">Services</a>
                <a href="{{ route('contact') }}" class="public-nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                <a href="{{ route('contact') }}#emergency" class="public-nav-link text-danger fw-semibold">
                    <i class="bi bi-telephone-fill me-1"></i> Emergency
                </a>
            </div>

            <!-- Portal Entry & Appointment Buttons -->
            <div class="d-none d-sm-flex align-items-center gap-2">
                <a href="{{ route('home') }}#appointment" class="btn btn-hms-teal btn-hms-sm d-none d-md-inline-flex">
                    <i class="bi bi-calendar2-check"></i> Book Appointment
                </a>
                <a href="{{ route('patient.login') }}" class="btn btn-hms-outline-teal btn-hms-sm">
                    <i class="bi bi-person"></i> Patient Portal
                </a>
                <a href="{{ route('portal.login') }}" class="btn btn-hms-primary btn-hms-sm">
                    <i class="bi bi-shield-lock"></i> Staff Login
                </a>
            </div>

            <!-- Mobile Hamburger Toggle -->
            <button class="btn btn-hms-icon d-xl-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#publicNavOffcanvas" aria-controls="publicNavOffcanvas" aria-label="Toggle navigation">
                <i class="bi bi-list fs-4"></i>
            </button>
        </div>
    </nav>

    <!-- Mobile Offcanvas Drawer -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="publicNavOffcanvas" aria-labelledby="publicNavOffcanvasLabel">
        <div class="offcanvas-header border-bottom">
            <div class="d-flex align-items-center gap-2">
                <span class="rounded bg-primary text-white p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-hospital fs-5 text-teal"></i>
                </span>
                <h5 class="offcanvas-title text-navy fw-bold mb-0" id="publicNavOffcanvasLabel">Meghna Clinic</h5>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column justify-content-between">
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('home') }}" class="public-nav-link {{ request()->routeIs('home') ? 'active' : '' }}" data-bs-dismiss="offcanvas">Home</a>
                <a href="{{ route('about') }}" class="public-nav-link {{ request()->routeIs('about') ? 'active' : '' }}" data-bs-dismiss="offcanvas">About Clinic</a>
                <a href="{{ route('departments') }}" class="public-nav-link {{ request()->routeIs('departments*') ? 'active' : '' }}" data-bs-dismiss="offcanvas">Departments</a>
                <a href="{{ route('doctors') }}" class="public-nav-link {{ request()->routeIs('doctors*') ? 'active' : '' }}" data-bs-dismiss="offcanvas">Doctors</a>
                <a href="{{ route('services') }}" class="public-nav-link {{ request()->routeIs('services') ? 'active' : '' }}" data-bs-dismiss="offcanvas">Healthcare Services</a>
                <a href="{{ route('contact') }}" class="public-nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" data-bs-dismiss="offcanvas">Contact & Location</a>
                <a href="{{ route('contact') }}#emergency" class="public-nav-link text-danger fw-semibold" data-bs-dismiss="offcanvas">
                    <i class="bi bi-telephone-fill me-1"></i> Emergency 24/7
                </a>
                <a href="{{ route('home') }}#appointment" class="public-nav-link text-teal fw-semibold" data-bs-dismiss="offcanvas">
                    <i class="bi bi-calendar2-check me-1"></i> Book Appointment
                </a>
                <a href="{{ route('privacy') }}" class="public-nav-link {{ request()->routeIs('privacy') ? 'active' : '' }}" data-bs-dismiss="offcanvas">Privacy Policy</a>
            </div>
            <div class="d-flex flex-column gap-2 pt-4 border-top">
                <a href="{{ route('home') }}#appointment" class="btn btn-hms-teal w-100" data-bs-dismiss="offcanvas">
                    <i class="bi bi-calendar2-check"></i> Book Appointment
                </a>
                <a href="{{ route('patient.login') }}" class="btn btn-hms-outline-teal w-100">
                    <i class="bi bi-person"></i> Patient Portal
                </a>
                <a href="{{ route('portal.login') }}" class="btn btn-hms-primary w-100">
                    <i class="bi bi-shield-lock"></i> Staff Login
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <main class="flex-grow-1">
        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
    </main>

    <!-- Public Website Footer -->
    <footer class="public-footer">
        <div class="container-xl">
            <div class="row g-4 justify-content-between">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="rounded bg-teal text-white p-2 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="bi bi-hospital fs-5"></i>
                        </span>
                        <h4 class="text-white h5 mb-0 fw-bold">Meghna Clinic & Nursing Home</h4>
                    </div>
                    <p class="text-white-50 mb-3" style="font-size: 0.9rem; line-height: 1.6;">
                        A trusted healthcare institution providing 24/7 emergency medical care, specialized orthopedic and gynecological surgery, 10-bed inpatient ward observation, and diagnostic laboratory services in Dinajpur.
                    </p>
                    <div class="text-white-50" style="font-size: 0.85rem;">
                        <i class="bi bi-geo-alt text-teal me-1"></i> Shohid Minar Mor, Dinajpur, Bangladesh<br>
                        <i class="bi bi-telephone text-teal me-1"></i> Hotline: +880 1700-000000<br>
                        <i class="bi bi-envelope text-teal me-1"></i> info@meghnaclinic.com
                    </div>
                </div>

                <div class="col-6 col-lg-2">
                    <h5 class="text-white h6 fw-bold mb-3">Clinical Specialties</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2" style="font-size: 0.875rem;">
                        <li><a href="{{ route('departments') }}">Gynecology & Obstetrics</a></li>
                        <li><a href="{{ route('departments') }}">Orthopedic Trauma</a></li>
                        <li><a href="{{ route('departments') }}">General Surgery</a></li>
                        <li><a href="{{ route('departments') }}">24/7 Emergency Ward</a></li>
                        <li><a href="{{ route('services') }}">Inpatient Care (10 Beds)</a></li>
                    </ul>
                </div>

                <div class="col-6 col-lg-2">
                    <h5 class="text-white h6 fw-bold mb-3">Patient Resources</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2" style="font-size: 0.875rem;">
                        <li><a href="{{ route('patient.login') }}">Patient Self-Care Portal</a></li>
                        <li><a href="{{ route('home') }}#appointment">Book Doctor Appointment</a></li>
                        <li><a href="{{ route('services') }}">Bed Tariffs & Availability</a></li>
                        <li><a href="{{ route('about') }}">About Our Clinic</a></li>
                        <li><a href="{{ route('privacy') }}">Privacy & Security Policy</a></li>
                    </ul>
                </div>

                <div class="col-lg-3">
                    <h5 class="text-white h6 fw-bold mb-3">Emergency Care 24/7</h5>
                    <div class="p-3 rounded-2 border border-white-subtle mb-3" style="background-color: rgba(255, 255, 255, 0.05);">
                        <span class="badge bg-danger mb-1">On-Duty Emergency Medical Officer</span>
                        <p class="text-white-50 mb-0" style="font-size: 0.8rem;">
                            Walk-in acute trauma, emergency delivery, and 10 observation beds ready with 24/7 oxygen support.
                        </p>
                    </div>
                    <span class="badge bg-teal text-white">Govt. Reg. Clinic</span>
                </div>
            </div>

            <hr class="public-footer-divider">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 text-white-50" style="font-size: 0.8rem;">
                <span>&copy; {{ date('Y') }} Meghna Clinic and Nursing Home. All rights reserved.</span>
                <div class="d-flex gap-3">
                    <a href="{{ route('privacy') }}" class="text-white-50 text-decoration-none hover-teal">Privacy Policy</a>
                    <a href="{{ route('contact') }}" class="text-white-50 text-decoration-none hover-teal">Contact Us</a>
                </div>
                <span>Shohid Minar Mor, Dinajpur | Hospital Management System</span>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
