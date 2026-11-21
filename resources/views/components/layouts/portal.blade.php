@props([
    'title' => 'Portal Dashboard',
    'role' => 'admin',
    'userName' => null,
    'userRoleTitle' => null,
    'activeNav' => 'dashboard',
    'breadcrumbs' => [],
])

@php
    $portalUser = Auth::user();

    // Adapt role dynamically if authenticated
    if ($portalUser) {
        if ($portalUser->user_type === 'patient') {
            $role = 'patient';
        } elseif ($role === 'admin') {
            if ($portalUser->hasRole(['super_admin', 'administrator'])) {
                $role = 'admin';
            } elseif ($portalUser->hasRole('doctor')) {
                $role = 'doctor';
            } elseif ($portalUser->hasRole('nurse')) {
                $role = 'nurse';
            } elseif ($portalUser->hasRole('receptionist')) {
                $role = 'reception';
            } elseif ($portalUser->hasRole('billing_staff')) {
                $role = 'billing';
            } elseif ($portalUser->hasRole('lab_staff')) {
                $role = 'lab';
            } elseif ($portalUser->hasRole('ot_ward_staff')) {
                $role = 'ot_ward';
            }
        }
    }

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
        'ot_ward' => [
            'title' => 'OT & Inpatient Ward Station',
            'badge' => 'OT/Ward Staff',
            'badgeStatus' => 'primary',
            'avatarClass' => 'nurse',
            'defaultName' => 'Md. Rafiqul Islam',
            'roleDisplay' => 'OT & Ward Coordinator',
        ],
    ];

    $currentConfig = $roleConfigs[$role] ?? $roleConfigs['admin'];

    // Dynamic resolution of User Name, Designation, and Badges
    if ($portalUser) {
        $displayName = $userName ?? $portalUser->name;

        if ($portalUser->user_type === 'patient') {
            $patientRecord = $portalUser->patient;
            if ($patientRecord && $patientRecord->is_verified) {
                $displayRole = $userRoleTitle ?? ('Patient (ID: ' . $patientRecord->patient_code . ')');
                $badgeText = 'Verified Patient';
                $badgeStatus = 'teal';
            } elseif ($patientRecord) {
                $displayRole = $userRoleTitle ?? ('Patient (ID: ' . $patientRecord->patient_code . ' - Pending)');
                $badgeText = 'Pending Verification';
                $badgeStatus = 'warning';
            } else {
                $displayRole = $userRoleTitle ?? 'Patient (Pending In-Person Verification)';
                $badgeText = 'Pending Verification';
                $badgeStatus = 'warning';
            }
            $avatarClass = 'patient';
        } else {
            // Staff user
            $staffProfile = $portalUser->staffProfile;
            if ($userRoleTitle) {
                $displayRole = $userRoleTitle;
            } elseif ($staffProfile?->designation) {
                $displayRole = $staffProfile->designation;
            } else {
                $firstRole = $portalUser->roles->first();
                $displayRole = $firstRole?->title ?? $currentConfig['roleDisplay'];
            }
            $badgeText = $currentConfig['badge'];
            $badgeStatus = $currentConfig['badgeStatus'];
            $avatarClass = $currentConfig['avatarClass'];
        }
    } else {
        $displayName = $userName ?? $currentConfig['defaultName'];
        $displayRole = $userRoleTitle ?? $currentConfig['roleDisplay'];
        $badgeText = $currentConfig['badge'];
        $badgeStatus = $currentConfig['badgeStatus'];
        $avatarClass = $currentConfig['avatarClass'];
    }

    // Role-specific navigation items (Strict Role-Based Access Control)
    $navMenus = match($role) {
        'admin' => [
            ['title' => 'Overview', 'icon' => 'speedometer2', 'slug' => 'dashboard', 'url' => route('portal.admin')],
            ['title' => 'Reports & Analytics', 'icon' => 'bar-chart-line', 'slug' => 'reports', 'url' => route('portal.reports.index')],
            ['title' => 'Patient Registry', 'icon' => 'person-vcard', 'slug' => 'patients', 'url' => route('portal.patients.index')],
            ['title' => 'Staff Management', 'icon' => 'people', 'slug' => 'staff', 'url' => route('portal.staff.index')],
            ['title' => 'Staff Shifts & Roster', 'icon' => 'calendar-range', 'slug' => 'shifts', 'url' => route('portal.staff.shifts')],
            ['title' => 'Departments & Wards', 'icon' => 'diagram-3', 'slug' => 'departments', 'url' => route('portal.departments.index')],
            ['title' => '10 Beds & Tariffs', 'icon' => 'door-open', 'slug' => 'beds', 'url' => route('portal.beds.index')],
            ['title' => 'Bed Occupancy Board', 'icon' => 'grid-3x3-gap', 'slug' => 'occupancy', 'url' => route('portal.beds.occupancy')],
            ['title' => 'Diagnostic Lab Orders', 'icon' => 'clipboard2-pulse', 'slug' => 'lab-orders', 'url' => route('portal.lab-orders.index')],
            ['title' => 'Diagnostic Test Reports', 'icon' => 'file-earmark-medical', 'slug' => 'lab-reports', 'url' => route('portal.lab-reports.index')],
            ['title' => 'Operation Theatre (OT)', 'icon' => 'scissors', 'slug' => 'operations', 'url' => route('portal.operations.index')],
            ['title' => 'Emergency Referrals', 'icon' => 'box-arrow-up-right', 'slug' => 'referrals', 'url' => route('portal.referrals.index')],
            ['title' => 'Lab Test Catalog', 'icon' => 'card-checklist', 'slug' => 'lab-catalog', 'url' => route('portal.lab-catalog.index')],
            ['title' => 'Discharge Clearances', 'icon' => 'box-arrow-right', 'slug' => 'discharges', 'url' => route('portal.discharges.index')],
            ['title' => 'Billing & Revenue', 'icon' => 'cash-stack', 'slug' => 'financials', 'url' => route('portal.billing.index')],
            ['title' => 'Payments & Receipts', 'icon' => 'cash-coin', 'slug' => 'payments', 'url' => route('portal.payments.index')],
            ['title' => 'Service Catalog', 'icon' => 'tags', 'slug' => 'services', 'url' => route('portal.services.index')],
            ['title' => 'Audit & Security Logs', 'icon' => 'shield-check', 'slug' => 'audit', 'url' => route('portal.audit-logs.index')],
        ],
        'doctor' => [
            ['title' => 'Clinical Dashboard', 'icon' => 'speedometer2', 'slug' => 'dashboard', 'url' => route('portal.doctor')],
            ['title' => 'OPD Patient Queue', 'icon' => 'person-lines-fill', 'slug' => 'queue', 'url' => route('portal.doctor') . '#consultation-queue'],
            ['title' => 'Consultations History', 'icon' => 'journal-medical', 'slug' => 'consultations', 'url' => route('portal.consultations.index')],
            ['title' => 'Digital Prescriptions', 'icon' => 'capsule', 'slug' => 'prescriptions', 'url' => route('portal.prescriptions.index')],
            ['title' => 'Patient Registry', 'icon' => 'person-vcard', 'slug' => 'patients', 'url' => route('portal.patients.index')],
            ['title' => 'Inpatient Care (10 Beds)', 'icon' => 'hospital', 'slug' => 'inpatients', 'url' => route('portal.beds.occupancy')],
            ['title' => 'Doctor Appointments', 'icon' => 'calendar2-check', 'slug' => 'appointments', 'url' => route('portal.appointments.index')],
            ['title' => 'Bed Transfers & Ledger', 'icon' => 'arrow-left-right', 'slug' => 'transfers', 'url' => route('portal.beds.transfers')],
            ['title' => 'Diagnostic Lab Orders', 'icon' => 'clipboard2-pulse', 'slug' => 'lab-orders', 'url' => route('portal.lab-orders.index')],
            ['title' => 'Diagnostic Test Reports', 'icon' => 'file-earmark-medical', 'slug' => 'lab-reports', 'url' => route('portal.lab-reports.index')],
            ['title' => 'Operation Theatre (OT)', 'icon' => 'scissors', 'slug' => 'operations', 'url' => route('portal.operations.index')],
            ['title' => 'Emergency Referrals', 'icon' => 'box-arrow-up-right', 'slug' => 'referrals', 'url' => route('portal.referrals.index')],
            ['title' => 'Discharge Clearances', 'icon' => 'box-arrow-right', 'slug' => 'discharges', 'url' => route('portal.discharges.index')],
            ['title' => 'Reports & Analytics', 'icon' => 'bar-chart-line', 'slug' => 'reports', 'url' => route('portal.reports.index')],
        ],
        'nurse' => [
            ['title' => 'Nursing Station', 'icon' => 'speedometer2', 'slug' => 'dashboard', 'url' => route('portal.nurse')],
            ['title' => '10-Bed Ward Monitor', 'icon' => 'door-open', 'slug' => 'beds', 'url' => route('portal.nurse') . '#ward-beds-section'],
            ['title' => 'Assigned Inpatients', 'icon' => 'people-fill', 'slug' => 'patients', 'url' => route('portal.nurse') . '#inpatients-section'],
            ['title' => 'Nursing Observations', 'icon' => 'clipboard2-pulse', 'slug' => 'observations', 'url' => route('portal.nurse.observations')],
            ['title' => 'Bedside Vitals Ledger', 'icon' => 'heart-pulse', 'slug' => 'vitals', 'url' => route('portal.nurse') . '#vitals-section'],
            ['title' => 'Medication Records (MAR)', 'icon' => 'capsule', 'slug' => 'mar', 'url' => route('portal.nurse.mar')],
            ['title' => 'Inpatient Admissions', 'icon' => 'box-arrow-in-right', 'slug' => 'admissions', 'url' => route('portal.admissions.index')],
            ['title' => 'Bed Transfers & Ledger', 'icon' => 'arrow-left-right', 'slug' => 'transfers', 'url' => route('portal.beds.transfers')],
            ['title' => 'Operation Theatre (OT)', 'icon' => 'scissors', 'slug' => 'operations', 'url' => route('portal.operations.index')],
            ['title' => 'Emergency Referrals', 'icon' => 'box-arrow-up-right', 'slug' => 'referrals', 'url' => route('portal.referrals.index')],
            ['title' => 'Discharge Station', 'icon' => 'box-arrow-right', 'slug' => 'discharges', 'url' => route('portal.discharges.index')],
            ['title' => 'Shift & Duty Handover', 'icon' => 'journal-medical', 'slug' => 'shift', 'url' => route('portal.nurse') . '#shift-section'],
        ],
        'reception' => [
            ['title' => 'Reception Desk', 'icon' => 'speedometer2', 'slug' => 'dashboard', 'url' => route('portal.reception')],
            ['title' => 'Patient Registration', 'icon' => 'person-plus', 'slug' => 'register', 'url' => route('portal.patients.create')],
            ['title' => 'Patient Registry', 'icon' => 'person-vcard', 'slug' => 'patients', 'url' => route('portal.patients.index')],
            ['title' => 'Doctor Appointments', 'icon' => 'calendar2-check', 'slug' => 'appointments', 'url' => route('portal.appointments.index')],
            ['title' => 'Visit & Triage Intake', 'icon' => 'ticket-perforated', 'slug' => 'visits', 'url' => route('portal.visits.create')],
            ['title' => 'Patient Visits List', 'icon' => 'card-list', 'slug' => 'visits-list', 'url' => route('portal.visits.index')],
            ['title' => 'Inpatient Admission', 'icon' => 'box-arrow-in-right', 'slug' => 'admissions', 'url' => route('portal.admissions.create')],
            ['title' => 'Admissions List', 'icon' => 'hospital', 'slug' => 'admissions-list', 'url' => route('portal.admissions.index')],
            ['title' => '10-Bed Occupancy Board', 'icon' => 'door-open', 'slug' => 'beds', 'url' => route('portal.beds.occupancy')],
            ['title' => 'Verify & Link Accounts', 'icon' => 'person-check', 'slug' => 'linking', 'url' => route('portal.patient-linking.index')],
        ],
        'billing' => [
            ['title' => 'Cashier Desk', 'icon' => 'speedometer2', 'slug' => 'dashboard', 'url' => route('portal.billing.index')],
            ['title' => 'Create Invoices', 'icon' => 'receipt', 'slug' => 'invoices', 'url' => route('portal.billing.create')],
            ['title' => 'Payments & Receipts', 'icon' => 'cash-coin', 'slug' => 'payments', 'url' => route('portal.payments.index')],
            ['title' => 'Refund Vouchers', 'icon' => 'arrow-counterclockwise', 'slug' => 'refunds', 'url' => route('portal.refunds.index')],
            ['title' => 'Service Catalog', 'icon' => 'tags', 'slug' => 'services', 'url' => route('portal.services.index')],
            ['title' => 'Unpaid Invoices', 'icon' => 'exclamation-circle', 'slug' => 'dues', 'url' => route('portal.billing.index') . '?status=unpaid'],
            ['title' => 'Paid Invoices', 'icon' => 'check2-all', 'slug' => 'paid', 'url' => route('portal.billing.index') . '?status=paid'],
            ['title' => 'Financial Clearances', 'icon' => 'check2-square', 'slug' => 'discharges', 'url' => route('portal.discharges.index')],
            ['title' => 'Financial Reports', 'icon' => 'bar-chart-line', 'slug' => 'reports', 'url' => route('portal.reports.index')],
        ],
        'lab' => [
            ['title' => 'Laboratory Worklist', 'icon' => 'speedometer2', 'slug' => 'dashboard', 'url' => route('portal.lab-orders.index')],
            ['title' => 'Diagnostic Orders', 'icon' => 'clipboard2-pulse', 'slug' => 'lab-orders', 'url' => route('portal.lab-orders.index')],
            ['title' => 'Diagnostic Test Reports', 'icon' => 'file-earmark-medical', 'slug' => 'lab-reports', 'url' => route('portal.lab-reports.index')],
            ['title' => 'Sample Collection Queue', 'icon' => 'upc-scan', 'slug' => 'samples', 'url' => route('portal.lab-orders.index') . '?status=pending_sample'],
            ['title' => 'Partner Laboratories', 'icon' => 'buildings', 'slug' => 'partners', 'url' => route('portal.lab-orders.index') . '?type=external'],
            ['title' => 'Lab Test Catalog', 'icon' => 'card-checklist', 'slug' => 'lab-catalog', 'url' => route('portal.lab-catalog.index')],
        ],
        'patient' => [
            ['title' => 'Health Overview', 'icon' => 'speedometer2', 'slug' => 'dashboard', 'url' => route('patient.dashboard')],
            ['title' => 'My Doctor Appointments', 'icon' => 'calendar-date', 'slug' => 'appointments', 'url' => route('patient.appointments.index')],
            ['title' => 'Prescriptions & Advice', 'icon' => 'file-earmark-medical', 'slug' => 'prescriptions', 'url' => route('patient.prescriptions')],
            ['title' => 'Diagnostic Test Reports', 'icon' => 'file-earmark-pdf', 'slug' => 'reports', 'url' => route('patient.lab-reports')],
            ['title' => 'Inpatient Bed Stays', 'icon' => 'hospital', 'slug' => 'admissions', 'url' => route('patient.inpatient-stays')],
            ['title' => 'Invoices & Receipts', 'icon' => 'receipt', 'slug' => 'billing', 'url' => route('patient.bills')],
            ['title' => 'My Account & Profile', 'icon' => 'person-gear', 'slug' => 'profile', 'url' => route('patient.profile')],
        ],
        'ot_ward' => [
            ['title' => 'OT & Ward Operations', 'icon' => 'speedometer2', 'slug' => 'dashboard', 'url' => route('portal.operations.index')],
            ['title' => '10-Bed Inpatient Status', 'icon' => 'door-open', 'slug' => 'beds', 'url' => route('portal.beds.index')],
            ['title' => 'Bed Occupancy Board', 'icon' => 'grid-3x3-gap', 'slug' => 'occupancy', 'url' => route('portal.beds.occupancy')],
            ['title' => 'Bed Transfers & Ledger', 'icon' => 'arrow-left-right', 'slug' => 'transfers', 'url' => route('portal.beds.transfers')],
            ['title' => 'Emergency Referrals', 'icon' => 'box-arrow-up-right', 'slug' => 'referrals', 'url' => route('portal.referrals.index')],
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

    <!-- Brand Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-icon.svg') }}">

    <!-- Design System Assets via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="antialiased">
    <div class="hms-layout-wrapper">
        <!-- 1. Desktop Responsive Sidebar -->
        <aside class="hms-sidebar d-none d-lg-flex" aria-label="Portal Navigation Sidebar">
            <!-- Sidebar Brand Header -->
            <div class="hms-sidebar-brand py-3 px-3">
                <a href="{{ route('home') }}" class="text-decoration-none d-block">
                    <x-brand-logo size="md" theme="dark" />
                </a>
            </div>

            <!-- Role Badge Banner -->
            <div class="px-3 pt-3 pb-1">
                <div class="p-2 rounded bg-white bg-opacity-10 d-flex align-items-center justify-content-between">
                    <span class="text-white fw-semibold" style="font-size: 0.8rem;">
                        <i class="bi bi-shield-lock-fill text-teal me-1"></i> {{ $currentConfig['title'] }}
                    </span>
                    <x-badge :status="$badgeStatus" :dot="false">
                        {{ $badgeText }}
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
            </ul>

            <!-- Sidebar Bottom Info -->
            @php
                try {
                    $sidebarOccupiedBeds = \App\Models\Bed::where('status', 'occupied')->count();
                    $sidebarTotalBeds = \App\Models\Bed::count() ?: 10;
                } catch (\Throwable $e) {
                    $sidebarOccupiedBeds = 4;
                    $sidebarTotalBeds = 10;
                }
            @endphp
            <div class="p-3 border-top border-white border-opacity-10 text-white-50" style="font-size: 0.75rem;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span>Inpatient Beds</span>
                    <span class="text-white fw-bold tabular-nums">{{ $sidebarOccupiedBeds }} / {{ $sidebarTotalBeds }} Occupied</span>
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
                <x-brand-logo size="sm" theme="dark" />
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
                    @php
                        $isPatient = $portalUser && $portalUser->user_type === 'patient';
                        try {
                            $portalUnreadCount = $portalUser ? \App\Models\SystemNotification::where('user_id', $portalUser->id)->where('is_read', false)->count() : 0;
                            $portalRecentNotifications = $portalUser ? \App\Models\SystemNotification::where('user_id', $portalUser->id)->latest('created_at')->limit(5)->get() : collect();
                        } catch (\Throwable $e) {
                            $portalUnreadCount = 0;
                            $portalRecentNotifications = collect();
                        }
                    @endphp
                    <!-- Notification Bell Dropdown -->
                    <div class="dropdown">
                        <button class="hms-notification-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Clinical notifications">
                            <i class="bi bi-bell"></i>
                            @if($portalUnreadCount > 0)
                                <span class="hms-notification-badge">{{ $portalUnreadCount > 99 ? '99+' : $portalUnreadCount }}</span>
                            @endif
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown shadow-sm" style="min-width: 330px; max-width: 380px;">
                            <div class="notification-header d-flex align-items-center justify-content-between p-3 border-bottom">
                                <span class="fw-bold text-navy" style="font-size: 0.85rem;">{{ $isPatient ? 'Patient Notifications' : 'System & Clinical Alerts' }}</span>
                                <div class="d-flex align-items-center gap-2">
                                    @if($portalUnreadCount > 0)
                                        <span class="badge bg-danger-subtle text-danger" style="font-size: 0.7rem;">{{ $portalUnreadCount }} Unread</span>
                                        <form method="POST" action="{{ $isPatient ? route('patient.notifications.read-all') : route('portal.notifications.mark-all-read') }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-link p-0 text-muted small text-decoration-none" style="font-size: 0.7rem;" title="Mark all as read">
                                                <i class="bi bi-check-all"></i> Read all
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge bg-success-subtle text-success" style="font-size: 0.7rem;">All read</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex flex-column" style="max-height: 320px; overflow-y: auto;">
                                @forelse($portalRecentNotifications as $notif)
                                    <div class="notification-item {{ $notif->is_read ? '' : 'unread' }} p-2.5 border-bottom d-flex align-items-start gap-2">
                                        <div class="rounded-circle bg-{{ $notif->type_badge_class }}-subtle text-{{ $notif->type_badge_class }} p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                                            <i class="bi bi-{{ $notif->type_icon }}"></i>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden" style="font-size: 0.8rem;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="fw-semibold text-navy text-truncate d-block">{{ $notif->title }}</span>
                                                <span class="text-muted-hms flex-shrink-0 ms-1" style="font-size: 0.65rem;">{{ $notif->created_at->diffForHumans(null, true) }}</span>
                                            </div>
                                            <p class="text-muted-hms mb-1 text-truncate" style="font-size: 0.75rem;">{{ $notif->message }}</p>
                                            <div class="d-flex align-items-center gap-2">
                                                @if($notif->action_url)
                                                    <a href="{{ $notif->action_url }}" class="text-teal small fw-semibold text-decoration-none" style="font-size: 0.7rem;">View Record &rarr;</a>
                                                @endif
                                                @if(!$notif->is_read)
                                                    <form method="POST" action="{{ $isPatient ? route('patient.notifications.read', $notif->id) : route('portal.notifications.read', $notif->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-link p-0 text-muted" style="font-size: 0.7rem;">Mark read</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-muted" style="font-size: 0.8rem;">
                                        <i class="bi bi-bell-slash fs-4 d-block mb-1 text-muted-hms"></i>
                                        No alerts or notifications.
                                    </div>
                                @endforelse
                            </div>
                            <div class="p-2 border-top text-center bg-light">
                                @if($isPatient)
                                    <a href="{{ route('patient.profile') }}" class="text-teal fw-semibold text-decoration-none" style="font-size: 0.75rem;">Account &amp; Emergency Contacts &rarr;</a>
                                @else
                                    <a href="{{ route('portal.notifications.index') }}" class="text-teal fw-semibold text-decoration-none" style="font-size: 0.75rem;">Notification Center &rarr;</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown">
                        <button class="hms-user-badge dropdown-toggle border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="hms-avatar {{ $avatarClass }}">
                                {{ strtoupper(mb_substr($displayName, 0, 1)) }}
                            </span>
                            <div class="d-none d-sm-block text-start lh-1">
                                <span class="d-block fw-semibold text-navy text-truncate" style="font-size: 0.825rem; max-width: 170px;">{{ $displayName }}</span>
                                <span class="text-muted-hms text-truncate d-block" style="font-size: 0.7rem; max-width: 170px;">{{ $displayRole }}</span>
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 230px;">
                            <li class="px-3 py-2 border-bottom bg-light">
                                <span class="d-block fw-bold text-navy text-truncate" style="font-size: 0.85rem;">{{ $displayName }}</span>
                                <span class="d-block text-muted-hms text-truncate" style="font-size: 0.75rem;">{{ $displayRole }}</span>
                                <x-badge :status="$badgeStatus" :dot="false" class="mt-1">
                                    {{ $badgeText }}
                                </x-badge>
                            </li>
                            @if($portalUser && $portalUser->user_type === 'patient')
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('patient.profile') }}">
                                        <i class="bi bi-person-circle me-2 text-teal"></i> My Profile &amp; Account
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('patient.appointments.index') }}">
                                        <i class="bi bi-calendar2-check me-2 text-teal"></i> My Doctor Appointments
                                    </a>
                                </li>
                                @if($portalUser->patient?->is_verified)
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('patient.prescriptions') }}">
                                            <i class="bi bi-file-earmark-medical me-2 text-teal"></i> My Prescriptions &amp; Rx
                                        </a>
                                    </li>
                                @endif
                            @else
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('portal.staff.shifts') }}">
                                        <i class="bi bi-person-badge me-2 text-teal"></i> My Profile &amp; Shift Roster
                                    </a>
                                </li>
                                @if($portalUser && $portalUser->hasRole(['super_admin', 'administrator']))
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('portal.audit-logs.index') }}">
                                            <i class="bi bi-shield-lock me-2 text-teal"></i> Security &amp; Audit Logs
                                        </a>
                                    </li>
                                @else
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('portal.notifications.index') }}">
                                            <i class="bi bi-bell me-2 text-teal"></i> Notifications Center
                                        </a>
                                    </li>
                                @endif
                            @endif
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
