<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HMS Layouts Directory — Meghna Clinic & Nursing Home</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-body py-5">
    <div class="container-xl">
        <!-- Header -->
        <div class="text-center mb-5">
            <span class="badge bg-teal-subtle text-teal px-3 py-2 rounded-pill fw-semibold mb-2">
                <i class="bi bi-layers-fill me-1"></i> Phase 03 UI/UX Layout Architecture
            </span>
            <h1 class="display-5 fw-bold text-navy mb-2">Meghna Clinic & Nursing Home</h1>
            <p class="text-muted-hms fs-6 max-w-700 mx-auto">
                Responsive, Role-Aware Application Layouts with Permission-Aware Navigation, Topbar, Responsive Sidebars, Breadcrumbs, and Consistent Footers.
            </p>
        </div>

        <!-- 8 Layouts Grid -->
        <div class="row g-4">
            <!-- 1. Public Website Layout -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm hms-card hover-lift">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="rounded bg-teal-subtle text-teal p-3 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-globe fs-4"></i>
                            </span>
                            <span class="badge bg-success-subtle text-success">Public Subsystem</span>
                        </div>
                        <h4 class="card-title text-navy h5 fw-bold mb-2">1. Public Website Layout</h4>
                        <p class="text-muted-hms mb-4 flex-grow-1" style="font-size: 0.875rem;">
                            External portal for patients and visitors with emergency hotline topbar, brand navbar, specialties, doctor roster, and 24/7 care notice.
                        </p>
                        <a href="{{ url('/previews/public') }}" class="btn btn-hms-primary w-100">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Launch Public Preview
                        </a>
                    </div>
                </div>
            </div>

            <!-- 2. Admin Dashboard Layout -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm hms-card hover-lift">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="rounded bg-primary text-white p-3 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-shield-shaded fs-4"></i>
                            </span>
                            <span class="badge bg-primary text-white">Role: Administrator</span>
                        </div>
                        <h4 class="card-title text-navy h5 fw-bold mb-2">2. Admin Dashboard Layout</h4>
                        <p class="text-muted-hms mb-4 flex-grow-1" style="font-size: 0.875rem;">
                            Full hospital operations control: 14 staff management, 10 bed tariffs, revenue metrics, audit logs, and security controls.
                        </p>
                        <a href="{{ url('/previews/admin') }}" class="btn btn-hms-primary w-100">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Launch Admin Preview
                        </a>
                    </div>
                </div>
            </div>

            <!-- 3. Doctor Dashboard Layout -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm hms-card hover-lift">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="rounded bg-teal text-white p-3 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-heart-pulse-fill fs-4"></i>
                            </span>
                            <span class="badge bg-teal text-white">Role: Doctor (24/7)</span>
                        </div>
                        <h4 class="card-title text-navy h5 fw-bold mb-2">3. Doctor Dashboard Layout</h4>
                        <p class="text-muted-hms mb-4 flex-grow-1" style="font-size: 0.875rem;">
                            Clinical consultation portal: OPD queue, inpatient rounds on 10 beds, prescriptions, lab orders, OT schedules, and discharge clearance.
                        </p>
                        <a href="{{ url('/previews/doctor') }}" class="btn btn-hms-teal w-100">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Launch Doctor Preview
                        </a>
                    </div>
                </div>
            </div>

            <!-- 4. Nurse Dashboard Layout -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm hms-card hover-lift">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="rounded bg-success text-white p-3 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-clipboard2-pulse-fill fs-4"></i>
                            </span>
                            <span class="badge bg-success text-white">Role: Nurse (8h Shifts)</span>
                        </div>
                        <h4 class="card-title text-navy h5 fw-bold mb-2">4. Nurse Ward Layout</h4>
                        <p class="text-muted-hms mb-4 flex-grow-1" style="font-size: 0.875rem;">
                            10-bed inpatient ward station: real-time bed occupancy, vital signs monitoring, medication administration records (MAR), and shift handover.
                        </p>
                        <a href="{{ url('/previews/nurse') }}" class="btn btn-hms-primary w-100">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Launch Nurse Preview
                        </a>
                    </div>
                </div>
            </div>

            <!-- 5. Reception Dashboard Layout -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm hms-card hover-lift">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="rounded bg-info text-white p-3 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-person-lines-fill fs-4"></i>
                            </span>
                            <span class="badge bg-info text-white">Role: Receptionist</span>
                        </div>
                        <h4 class="card-title text-navy h5 fw-bold mb-2">5. Reception Desk Layout</h4>
                        <p class="text-muted-hms mb-4 flex-grow-1" style="font-size: 0.875rem;">
                            Front desk patient intake: patient registration, doctor appointments, triage visits, bed admissions, and patient account verification.
                        </p>
                        <a href="{{ url('/previews/reception') }}" class="btn btn-hms-primary w-100">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Launch Reception Preview
                        </a>
                    </div>
                </div>
            </div>

            <!-- 6. Billing Dashboard Layout -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm hms-card hover-lift">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="rounded bg-warning text-dark p-3 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-cash-stack fs-4"></i>
                            </span>
                            <span class="badge bg-warning text-dark">Role: Cashier</span>
                        </div>
                        <h4 class="card-title text-navy h5 fw-bold mb-2">6. Billing & Cashier Layout</h4>
                        <p class="text-muted-hms mb-4 flex-grow-1" style="font-size: 0.875rem;">
                            Financial desk: invoice creation, payment collection (Cash/bKash/Nagad), due management, refund vouchers, and discharge financial clearance.
                        </p>
                        <a href="{{ url('/previews/billing') }}" class="btn btn-hms-primary w-100">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Launch Billing Preview
                        </a>
                    </div>
                </div>
            </div>

            <!-- 7. Laboratory Dashboard Layout -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm hms-card hover-lift">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="rounded bg-info text-white p-3 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-virus fs-4"></i>
                            </span>
                            <span class="badge bg-info text-white">Role: Lab Technologist</span>
                        </div>
                        <h4 class="card-title text-navy h5 fw-bold mb-2">7. Laboratory Layout</h4>
                        <p class="text-muted-hms mb-4 flex-grow-1" style="font-size: 0.875rem;">
                            Diagnostic workflow: internal/external test orders, barcode sample collection, result entry, test catalog, and secure PDF report generation.
                        </p>
                        <a href="{{ url('/previews/lab') }}" class="btn btn-hms-primary w-100">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Launch Lab Preview
                        </a>
                    </div>
                </div>
            </div>

            <!-- 8. Patient Portal Layout -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm hms-card hover-lift">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="rounded bg-teal text-white p-3 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-person-heart fs-4"></i>
                            </span>
                            <span class="badge bg-teal text-white">Role: Verified Patient</span>
                        </div>
                        <h4 class="card-title text-navy h5 fw-bold mb-2">8. Patient Portal Layout</h4>
                        <p class="text-muted-hms mb-4 flex-grow-1" style="font-size: 0.875rem;">
                            Patient self-care portal: view appointments, prescriptions, download verified lab reports, review inpatient bed stays, and payment receipts.
                        </p>
                        <a href="{{ url('/previews/patient') }}" class="btn btn-hms-teal w-100">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Launch Patient Preview
                        </a>
                    </div>
                </div>
            </div>

            <!-- Design System Components Hub Link -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm hms-card border-start border-4 border-teal bg-white">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <span class="badge bg-teal-subtle text-teal mb-2">Design Tokens & Library</span>
                            <h4 class="card-title text-navy h5 fw-bold mb-2">Design System Components</h4>
                            <p class="text-muted-hms" style="font-size: 0.875rem;">
                                Complete catalog of 21 reusable Blade UI components (buttons, badges, inputs, stat-cards, modals, alert banners, and tables).
                            </p>
                        </div>
                        <a href="{{ route('design-system') }}" class="btn btn-hms-outline-teal w-100">
                            <i class="bi bi-palette-fill me-1"></i> View Design System
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="text-center text-muted-hms mt-5 pt-3 border-top" style="font-size: 0.85rem;">
            Meghna Clinic & Nursing Home &bull; Shohid Minar Mor, Dinajpur, Bangladesh &bull; Production Monolith
        </div>
    </div>
</body>
</html>
