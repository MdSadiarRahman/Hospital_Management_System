@props([
    'title' => 'Portal Dashboard',
    'role' => 'admin',
    'userName' => null,
    'userRoleTitle' => null,
    'activeNav' => 'dashboard',
    'breadcrumbs' => [],
])

@php
    // Role metadata & styling presets
    $roleConfigs = [
        'admin' => [
            'title' => 'Administrator Portal',
            'badge' => 'Admin Authority',
            'badgeStatus' => 'navy',
            'avatarClass' => 'bg-primary',
            'defaultName' => 'Md. Sadiar Rahman (Admin)',
            'roleDisplay' => 'Hospital Administrator',
        ],
        'doctor' => [
            'title' => 'Doctor Clinical Portal',
            'badge' => 'Medical Officer',
            'badgeStatus' => 'teal',
            'avatarClass' => 'doctor',
            'defaultName' => 'Dr. Nasreen Sultana, FCPS',
            'roleDisplay' => 'Gynecologist & Surgeon',
        ],
        'nurse' => [
            'title' => 'Nurse Ward Station',
            'badge' => 'Inpatient Sister',
            'badgeStatus' => 'success',
            'avatarClass' => 'nurse',
            'defaultName' => 'Sister Shima Begum',
            'roleDisplay' => 'Senior Staff Nurse (Morning Shift)',
        ],
        'reception' => [
            'title' => 'Reception & Intake Desk',
            'badge' => 'Intake Desk',
            'badgeStatus' => 'info',
            'avatarClass' => 'bg-secondary',
            'defaultName' => 'Kowshik Roy (Receptionist)',
            'roleDisplay' => 'Front Desk Receptionist',
        ],
        'billing' => [
            'title' => 'Billing & Cashier Portal',
            'badge' => 'Financial Desk',
            'badgeStatus' => 'warning',
            'avatarClass' => 'billing',
            'defaultName' => 'Aftab Ahmed (Cashier)',
            'roleDisplay' => 'Billing & Settlement Officer',
        ],
        'lab' => [
            'title' => 'Diagnostic Laboratory Portal',
            'badge' => 'Lab Diagnostics',
            'badgeStatus' => 'info',
            'avatarClass' => 'lab',
            'defaultName' => 'Md. Faruk Hossain',
            'roleDisplay' => 'Medical Technologist (Lab)',
        ],
        'patient' => [
            'title' => 'Patient Self-Care Portal',
            'badge' => 'Verified Patient',
            'badgeStatus' => 'teal',
            'avatarClass' => 'patient',
            'defaultName' => 'Most. Rokeya Akhter',
            'roleDisplay' => 'Patient (ID: MCNH-2026-0042)',
        ],
    ];

    $currentConfig = $roleConfigs[$role] ?? $roleConfigs['admin'];
    $displayName = $userName ?? $currentConfig['defaultName'];
    $displayRole = $userRoleTitle ?? $currentConfig['roleDisplay'];

    // Role-specific navigation items (Strict Role-Based Access Control)
    $navMenus = match($role) {
        'admin' => [
            ['title' => 'Overview', 'icon' => 'speedometer2', 'slug' => 'dashboard', 'url' => url('/previews/admin')],
            ['title' => 'Staff Management', 'icon' => 'people', 'slug' => 'staff', 'url' => '#'],
            ['title' => 'Departments & Wards', 'icon' => 'diagram-3', 'slug' => 'departments', 'url' => '#'],
            ['title' => '10 Beds & Tariffs', 'icon' => 'door-open', 'slug' => 'beds', 'url' => '#'],
            ['title' => 'Diagnostic Lab Orders', 'icon' => 'clipboard2-pulse', 'slug' => 'lab-orders', 'url' => route('portal.lab-orders.index')],
            ['title' => 'Diagnostic Test Reports', 'icon' => 'file-earmark-medical', 'slug' => 'lab-reports', 'url' => route('portal.lab-reports.index')],
            ['title' => 'Operation Theatre (OT)', 'icon' => 'scissors', 'slug' => 'operations', 'url' => route('portal.operations.index')],
            ['title' => 'Emergency Referrals', 'icon' => 'box-arrow-up-right', 'slug' => 'referrals', 'url' => route('portal.referrals.index')],
            ['title' => 'Lab Test Catalog', 'icon' => 'card-checklist', 'slug' => 'lab-catalog', 'url' => route('portal.lab-catalog.index')],
            ['title' => 'Billing & Revenue', 'icon' => 'cash-stack', 'slug' => 'financials', 'url' => route('portal.billing.index')],
            ['title' => 'Service Catalog', 'icon' => 'tags', 'slug' => 'services', 'url' => route('portal.services.index')],
            ['title' => 'Audit & Security Logs', 'icon' => 'shield-check', 'slug' => 'audit', 'url' => '#'],
            ['title' => 'System Settings', 'icon' => 'gear', 'slug' => 'settings', 'url' => '#'],
        ],
        'doctor' => [
            ['title' => 'Clinical Dashboard', 'icon' => 'speedometer2', 'slug' => 'dashboard', 'url' => route('portal.doctor')],
            ['title' => 'OPD Patient Queue', 'icon' => 'person-lines-fill', 'slug' => 'queue', 'url' => route('portal.doctor') . '#consultation-queue'],
            ['title' => 'Consultations History', 'icon' => 'journal-medical', 'slug' => 'consultations', 'url' => route('portal.consultations.index')],
            ['title' => 'Digital Prescriptions', 'icon' => 'capsule', 'slug' => 'prescriptions', 'url' => route('portal.prescriptions.index')],
            ['title' => 'Inpatient Care (10 Beds)', 'icon' => 'hospital', 'slug' => 'inpatients', 'url' => route('portal.beds.occupancy')],
            ['title' => 'Doctor Appointments', 'icon' => 'calendar2-check', 'slug' => 'appointments', 'url' => route('portal.appointments.index')],
            ['title' => 'Bed Transfers & Ledger', 'icon' => 'arrow-left-right', 'slug' => 'transfers', 'url' => route('portal.beds.transfers')],
            ['title' => 'Diagnostic Lab Orders', 'icon' => 'clipboard2-pulse', 'slug' => 'lab-orders', 'url' => route('portal.lab-orders.index')],
            ['title' => 'Operation Theatre (OT)', 'icon' => 'scissors', 'slug' => 'operations', 'url' => route('portal.operations.index')],
            ['title' => 'Emergency Referrals', 'icon' => 'box-arrow-up-right', 'slug' => 'referrals', 'url' => route('portal.referrals.index')],
        ],
        'nurse' => [
            ['title' => 'Nursing Station', 'icon' => 'speedometer2', 'slug' => 'dashboard', 'url' => route('portal.nurse')],
            ['title' => '10-Bed Ward Monitor', 'icon' => 'door-open', 'slug' => 'beds', 'url' => route('portal.nurse') . '#ward-beds-section'],
            ['title' => 'Assigned Inpatients', 'icon' => 'people-fill', 'slug' => 'patients', 'url' => route('portal.nurse') . '#inpatients-section'],
            ['title' => 'Nursing Observations', 'icon' => 'clipboard2-pulse', 'slug' => 'observations', 'url' => route('portal.nurse.observations')],
            ['title' => 'Bedside Vitals Ledger', 'icon' => 'heart-pulse', 'slug' => 'vitals', 'url' => route('portal.nurse') . '#vitals-section'],
            ['title' => 'Medication Records (MAR)', 'icon' => 'capsule', 'slug' => 'mar', 'url' => route('portal.nurse.mar')],
            ['title' => 'Operation Theatre (OT)', 'icon' => 'scissors', 'slug' => 'operations', 'url' => route('portal.operations.index')],
            ['title' => 'Emergency Referrals', 'icon' => 'box-arrow-up-right', 'slug' => 'referrals', 'url' => route('portal.referrals.index')],
            ['title' => 'Shift & Duty Handover', 'icon' => 'journal-medical', 'slug' => 'shift', 'url' => route('portal.nurse') . '#shift-section'],
            ['title' => 'Bed Transfers & Ledger', 'icon' => 'arrow-left-right', 'slug' => 'transfers', 'url' => route('portal.beds.transfers')],
        ],
        'reception' => [
            ['title' => 'Reception Desk', 'icon' => 'speedometer2', 'slug' => 'dashboard', 'url' => url('/previews/reception')],
            ['title' => 'Patient Registration', 'icon' => 'person-plus', 'slug' => 'register', 'url' => '#'],
            ['title' => 'Doctor Appointments', 'icon' => 'calendar2-check', 'slug' => 'appointments', 'url' => '#'],
            ['title' => 'Visit & Triage Intake', 'icon' => 'ticket-perforated', 'slug' => 'visits', 'url' => '#'],
            ['title' => 'Inpatient Admission', 'icon' => 'box-arrow-in-right', 'slug' => 'admissions', 'url' => '#'],
            ['title' => 'Verify & Link Accounts', 'icon' => 'person-check', 'slug' => 'linking', 'url' => '#'],
        ],
        'billing' => [
            ['title' => 'Cashier Desk', 'icon' => 'speedometer2', 'slug' => 'dashboard', 'url' => route('portal.billing.index')],
            ['title' => 'Create Invoices', 'icon' => 'receipt', 'slug' => 'invoices', 'url' => route('portal.billing.create')],
            ['title' => 'Service Catalog', 'icon' => 'tags', 'slug' => 'services', 'url' => route('portal.services.index')],
            ['title' => 'Unpaid Invoices', 'icon' => 'cash-coin', 'slug' => 'payments', 'url' => route('portal.billing.index') . '?status=unpaid'],
            ['title' => 'Partial Dues', 'icon' => 'exclamation-circle', 'slug' => 'dues', 'url' => route('portal.billing.index') . '?status=partial'],
            ['title' => 'Paid Invoices', 'icon' => 'check2-all', 'slug' => 'paid', 'url' => route('portal.billing.index') . '?status=paid'],
        ],
        'lab' => [
            ['title' => 'Laboratory Worklist', 'icon' => 'speedometer2', 'slug' => 'dashboard', 'url' => url('/previews/lab')],
            ['title' => 'Diagnostic Orders', 'icon' => 'clipboard2-pulse', 'slug' => 'lab-orders', 'url' => route('portal.lab-orders.index')],
            ['title' => 'Diagnostic Test Reports', 'icon' => 'file-earmark-medical', 'slug' => 'lab-reports', 'url' => route('portal.lab-reports.index')],
            ['title' => 'Barcode Sample Collection', 'icon' => 'upc-scan', 'slug' => 'samples', 'url' => '#'],
            ['title' => 'Partner Laboratories', 'icon' => 'buildings', 'slug' => 'partners', 'url' => '#'],
            ['title' => 'Lab Test Catalog', 'icon' => 'card-checklist', 'slug' => 'lab-catalog', 'url' => route('portal.lab-catalog.index')],
        ],
        'patient' => [
            ['title' => 'Health Overview', 'icon' => 'speedometer2', 'slug' => 'dashboard', 'url' => url('/previews/patient')],
            ['title' => 'My Doctor Appointments', 'icon' => 'calendar-date', 'slug' => 'appointments', 'url' => '#'],
            ['title' => 'Prescriptions & Advice', 'icon' => 'file-earmark-medical', 'slug' => 'prescriptions', 'url' => '#'],
            ['title' => 'Diagnostic Test Reports', 'icon' => 'file-earmark-pdf', 'slug' => 'reports', 'url' => '#'],
            ['title' => 'Inpatient Bed Stays', 'icon' => 'hospital', 'slug' => 'admissions', 'url' => '#'],
            ['title' => 'Invoices & Receipts', 'icon' => 'receipt', 'slug' => 'billing', 'url' => '#'],
            ['title' => 'My Account & Guardians', 'icon' => 'person-gear', 'slug' => 'profile', 'url' => '#'],
        ],
        default => [],
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — Meghna Clinic & Nursing Home</title>

    <!-- Design System Assets via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="antialiased">
    <div class="hms-layout-wrapper">
        <!-- 1. Desktop Responsive Sidebar -->
        <aside class="hms-sidebar d-none d-lg-flex" aria-label="Portal Navigation Sidebar">
            <!-- Sidebar Brand Header -->
            <div class="hms-sidebar-brand">
                <span class="rounded bg-teal text-white p-2 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="bi bi-hospital fs-5"></i>
                </span>
                <div class="overflow-hidden">
                    <span class="d-block fw-bold text-white text-truncate" style="font-size: 0.95rem;">Meghna Clinic</span>
                    <span class="d-block text-white-50 text-truncate" style="font-size: 0.7rem;">Shohid Minar Mor, Dinajpur</span>
                </div>
            </div>

            <!-- Role Badge Banner -->
            <div class="px-3 pt-3 pb-1">
                <div class="p-2 rounded bg-white bg-opacity-10 d-flex align-items-center justify-content-between">
                    <span class="text-white fw-semibold" style="font-size: 0.8rem;">
                        <i class="bi bi-shield-lock-fill text-teal me-1"></i> {{ $currentConfig['title'] }}
                    </span>
                    <x-badge :status="$currentConfig['badgeStatus']" :dot="false">
                        {{ $currentConfig['badge'] }}
                    </x-badge>
                </div>
            </div>

            <!-- Navigation Menu Items -->
            <ul class="hms-sidebar-nav">
                <li class="hms-nav-section-title">Main Navigation</li>
                @foreach($navMenus as $menu)
                    <li class="hms-nav-item">
                        <a href="{{ $menu['url'] }}" class="hms-nav-link {{ $activeNav === $menu['slug'] ? 'active' : '' }}">
                            <i class="bi bi-{{ $menu['icon'] }}" aria-hidden="true"></i>
                            <span>{{ $menu['title'] }}</span>
                        </a>
                    </li>
                @endforeach

                <li class="hms-nav-section-title mt-3">Subsystems Switcher</li>
                <li class="hms-nav-item">
                    <a href="{{ url('/previews') }}" class="hms-nav-link">
                        <i class="bi bi-grid-fill" aria-hidden="true"></i>
                        <span>All 8 Layout Previews</span>
                    </a>
                </li>
                <li class="hms-nav-item">
                    <a href="{{ url('/previews/public') }}" class="hms-nav-link" target="_blank">
                        <i class="bi bi-globe" aria-hidden="true"></i>
                        <span>Public Website</span>
                    </a>
                </li>
            </ul>

            <!-- Sidebar Bottom Info -->
            <div class="p-3 border-top border-white border-opacity-10 text-white-50" style="font-size: 0.75rem;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span>Inpatient Beds</span>
                    <span class="text-white fw-bold tabular-nums">8 / 10 Occupied</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span>Emergency Doctor</span>
                    <span class="text-success fw-bold">24/7 On-Duty</span>
                </div>
            </div>
        </aside>

        <!-- 2. Mobile Offcanvas Sidebar Drawer -->
        <div class="offcanvas offcanvas-start bg-primary text-white" tabindex="-1" id="portalMobileSidebar" aria-labelledby="portalMobileSidebarLabel" style="width: 280px; background-color: var(--hms-primary) !important;">
            <div class="offcanvas-header border-bottom border-white border-opacity-10">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded bg-teal text-white p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-hospital fs-5"></i>
                    </span>
                    <h5 class="offcanvas-title text-white fw-bold mb-0" id="portalMobileSidebarLabel">Meghna Clinic</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0 d-flex flex-column justify-content-between">
                <ul class="hms-sidebar-nav px-3 py-2">
                    <li class="hms-nav-section-title">{{ $currentConfig['title'] }}</li>
                    @foreach($navMenus as $menu)
                        <li class="hms-nav-item">
                            <a href="{{ $menu['url'] }}" class="hms-nav-link {{ $activeNav === $menu['slug'] ? 'active' : '' }}">
                                <i class="bi bi-{{ $menu['icon'] }}" aria-hidden="true"></i>
                                <span>{{ $menu['title'] }}</span>
                            </a>
                        </li>
                    @endforeach

                    <li class="hms-nav-section-title mt-3">Switch Layouts</li>
                    <li class="hms-nav-item">
                        <a href="{{ url('/previews') }}" class="hms-nav-link">
                            <i class="bi bi-grid-fill"></i>
                            <span>All 8 Layouts Index</span>
                        </a>
                    </li>
                </ul>
                <div class="p-3 border-top border-white border-opacity-10 text-white-50" style="font-size: 0.75rem;">
                    Shohid Minar Mor, Dinajpur | HMS Portal v1.0
                </div>
            </div>
        </div>

        <!-- 3. Main Application Content Area -->
        <div class="hms-main-wrapper">
            <!-- Top Navigation Bar -->
            <header class="hms-topbar">
                <div class="d-flex align-items-center gap-3">
                    <!-- Mobile Hamburger Button -->
                    <button class="btn btn-hms-icon d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#portalMobileSidebar" aria-controls="portalMobileSidebar" aria-label="Open mobile menu">
                        <i class="bi bi-list fs-4"></i>
                    </button>

                    <!-- Breadcrumbs Area -->
                    <div class="d-none d-md-block">
                        @if(!empty($breadcrumbs))
                            <x-breadcrumb :items="$breadcrumbs" />
                        @else
                            <x-breadcrumb :items="[$currentConfig['title'] => '#', $title => null]" />
                        @endif
                    </div>
                </div>

                <!-- Right Action Bar: Notifications & User Profile -->
                <div class="d-flex align-items-center gap-2">
                    <!-- Notification Bell Dropdown -->
                    <div class="dropdown">
                        <button class="hms-notification-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Clinical notifications">
                            <i class="bi bi-bell"></i>
                            <span class="hms-notification-badge"></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                            <div class="notification-header">
                                <span class="fw-bold text-navy" style="font-size: 0.85rem;">Clinical Notifications</span>
                                <span class="badge bg-danger-subtle text-danger" style="font-size: 0.7rem;">3 Unread</span>
                            </div>
                            <div class="d-flex flex-column">
                                <a href="#" class="notification-item unread">
                                    <div class="rounded-circle bg-danger-subtle text-danger p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="bi bi-heart-pulse"></i>
                                    </div>
                                    <div style="font-size: 0.8rem;">
                                        <span class="fw-semibold d-block text-main">New Emergency Admission</span>
                                        <span class="text-muted-hms d-block">Bed EM-01 occupied (MCNH-2026-0043)</span>
                                        <span class="text-muted-hms" style="font-size: 0.7rem;">10 mins ago</span>
                                    </div>
                                </a>
                                <a href="#" class="notification-item unread">
                                    <div class="rounded-circle bg-teal-subtle text-teal p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="bi bi-virus"></i>
                                    </div>
                                    <div style="font-size: 0.8rem;">
                                        <span class="fw-semibold d-block text-main">Lab Report Ready</span>
                                        <span class="text-muted-hms d-block">CBC test published for verification</span>
                                        <span class="text-muted-hms" style="font-size: 0.7rem;">25 mins ago</span>
                                    </div>
                                </a>
                                <a href="#" class="notification-item">
                                    <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="bi bi-check2-circle"></i>
                                    </div>
                                    <div style="font-size: 0.8rem;">
                                        <span class="fw-semibold d-block text-main">Payment Settled</span>
                                        <span class="text-muted-hms d-block">Bill #BILL-0042 cleared (৳ 1,200.00)</span>
                                        <span class="text-muted-hms" style="font-size: 0.7rem;">1 hour ago</span>
                                    </div>
                                </a>
                            </div>
                            <div class="p-2 border-top text-center bg-light">
                                <a href="#" class="text-teal fw-semibold text-decoration-none" style="font-size: 0.75rem;">View All Notifications</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown">
                        <button class="hms-user-badge dropdown-toggle border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="hms-avatar {{ $currentConfig['avatarClass'] }}">
                                {{ substr($displayName, 0, 1) }}
                            </span>
                            <div class="d-none d-sm-block text-start lh-1">
                                <span class="d-block fw-semibold text-navy" style="font-size: 0.825rem;">{{ $displayName }}</span>
                                <span class="text-muted-hms" style="font-size: 0.7rem;">{{ $displayRole }}</span>
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 220px;">
                            <li class="px-3 py-2 border-bottom bg-light">
                                <span class="d-block fw-bold text-navy" style="font-size: 0.85rem;">{{ $displayName }}</span>
                                <span class="d-block text-muted-hms" style="font-size: 0.75rem;">{{ $displayRole }}</span>
                                <x-badge :status="$currentConfig['badgeStatus']" :dot="false" class="mt-1">
                                    {{ $currentConfig['badge'] }}
                                </x-badge>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="#">
                                    <i class="bi bi-person me-2 text-teal"></i> My Profile & Shift
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="#">
                                    <i class="bi bi-key me-2 text-teal"></i> Change Password
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="#">
                                    <i class="bi bi-shield-lock me-2 text-teal"></i> Active Permissions
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger border-0 bg-transparent w-100 text-start">
                                        <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Page Content Body Area -->
            <div class="hms-content-area">
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </div>

            <!-- Portal Footer -->
            <footer class="hms-portal-footer">
                <div>
                    <strong>Meghna Clinic & Nursing Home</strong> — Shohid Minar Mor, Dinajpur, Bangladesh
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="d-inline-flex align-items-center gap-1.5 text-success">
                        <span class="hms-badge-dot bg-success"></span> System Operational
                    </span>
                    <span>HMS v1.0 Production Monolith</span>
                </div>
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
