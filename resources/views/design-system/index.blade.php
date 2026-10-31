<x-layouts.app title="Global UI/UX Design System">
    <!-- Top Navigation Header -->
    <header class="hms-topbar">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="rounded bg-primary-subtle p-2 text-navy d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-hospital fs-5 text-teal"></i>
                </span>
                <div>
                    <h1 class="h6 mb-0 fw-bold text-navy">Meghna Clinic & Nursing Home</h1>
                    <span class="text-muted-hms" style="font-size: 0.75rem;">Shohid Minar Mor, Dinajpur — HMS Design System v1.0</span>
                </div>
            </div>
            <span class="badge bg-light text-secondary border ms-2 d-none d-md-inline-block">Phase 03</span>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="dropdown">
                <button class="btn btn-hms-ghost btn-hms-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-translate text-teal me-1"></i> English / বাংলা
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item active" href="#">English (Default)</a></li>
                    <li><a class="dropdown-item font-bn" href="#">বাংলা (Bangla)</a></li>
                </ul>
            </div>
            <a href="#components-section" class="btn btn-hms-teal btn-hms-sm">
                <i class="bi bi-grid-1x2"></i> Component Specs
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container-fluid px-3 px-lg-5 py-4">
        <!-- Hero Banner: Minimalist Corporate Healthcare Header -->
        <div class="hms-card p-4 mb-4 border-0" style="background: linear-gradient(135deg, #123B5D 0%, #1A4B75 100%); color: #FFFFFF;">
            <div class="row align-items-center gy-3">
                <div class="col-lg-8">
                    <span class="badge mb-2 px-2.5 py-1 text-uppercase tracking-wider" style="background-color: rgba(8, 127, 140, 0.3); color: #A5E8EE; font-size: 0.7rem; font-weight: 700;">
                        Enterprise Healthcare UI Framework
                    </span>
                    <h2 class="display-6 fw-bold text-white mb-2">Meghna HMS Design System</h2>
                    <p class="text-white-50 mb-0" style="font-size: 0.95rem; max-width: 680px;">
                        A production-grade, accessible, data-dense design system engineered for clinical reliability, rapid triage scanning, and bilingual operations in Dinajpur, Bangladesh.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-inline-flex flex-column align-items-lg-end gap-1">
                        <span class="badge text-white border border-light-subtle px-3 py-1.5" style="background: rgba(255, 255, 255, 0.1);">
                            <i class="bi bi-check2-circle text-success me-1"></i> WCAG AA Accessible Contrast
                        </span>
                        <span class="badge text-white border border-light-subtle px-3 py-1.5" style="background: rgba(255, 255, 255, 0.1);">
                            <i class="bi bi-phone me-1"></i> Mobile-First Bootstrap 5 & Bento Grid
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 1: Color Tokens & Accessibility Contrast -->
        <section class="mb-5">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="h5 fw-bold text-navy mb-0">1. Color Palette Tokens & Contrast Standards</h3>
                    <p class="text-muted-hms mb-0" style="font-size: 0.825rem;">Standardized tokens adhering strictly to high-contrast clinical readability standards.</p>
                </div>
            </div>

            <div class="row g-3">
                <!-- Primary Navy -->
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="hms-card p-3 h-100 text-center">
                        <div class="rounded-3 mb-2" style="height: 52px; background-color: var(--hms-primary);"></div>
                        <div class="fw-bold text-navy text-truncate" style="font-size: 0.85rem;">Primary Navy</div>
                        <div class="font-monospace text-muted-hms" style="font-size: 0.75rem;">#123B5D</div>
                        <div class="badge bg-light text-dark border mt-1" style="font-size: 0.7rem;">Dominant Brand</div>
                    </div>
                </div>

                <!-- Medical Teal -->
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="hms-card p-3 h-100 text-center">
                        <div class="rounded-3 mb-2" style="height: 52px; background-color: var(--hms-teal);"></div>
                        <div class="fw-bold text-teal text-truncate" style="font-size: 0.85rem;">Medical Teal</div>
                        <div class="font-monospace text-muted-hms" style="font-size: 0.75rem;">#087F8C</div>
                        <div class="badge bg-light text-dark border mt-1" style="font-size: 0.7rem;">Action / Secondary</div>
                    </div>
                </div>

                <!-- Soft Background -->
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="hms-card p-3 h-100 text-center">
                        <div class="rounded-3 mb-2 border" style="height: 52px; background-color: var(--hms-bg-body);"></div>
                        <div class="fw-bold text-main text-truncate" style="font-size: 0.85rem;">Soft Canvas</div>
                        <div class="font-monospace text-muted-hms" style="font-size: 0.75rem;">#F4F7FA</div>
                        <div class="badge bg-light text-dark border mt-1" style="font-size: 0.7rem;">Body Canvas</div>
                    </div>
                </div>

                <!-- Dark Text -->
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="hms-card p-3 h-100 text-center">
                        <div class="rounded-3 mb-2" style="height: 52px; background-color: var(--hms-text-main);"></div>
                        <div class="fw-bold text-main text-truncate" style="font-size: 0.85rem;">Dark Neutral Text</div>
                        <div class="font-monospace text-muted-hms" style="font-size: 0.75rem;">#263445</div>
                        <div class="badge bg-light text-dark border mt-1" style="font-size: 0.7rem;">High Contrast Body</div>
                    </div>
                </div>

                <!-- Clinical Success -->
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="hms-card p-3 h-100 text-center">
                        <div class="rounded-3 mb-2" style="height: 52px; background-color: var(--hms-success);"></div>
                        <div class="fw-bold text-success text-truncate" style="font-size: 0.85rem;">Normal / Paid</div>
                        <div class="font-monospace text-muted-hms" style="font-size: 0.75rem;">#10B981</div>
                        <div class="badge bg-success-subtle text-success-emphasis border mt-1" style="font-size: 0.7rem;">Normal Condition</div>
                    </div>
                </div>

                <!-- Clinical Danger / Emergency -->
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="hms-card p-3 h-100 text-center">
                        <div class="rounded-3 mb-2" style="height: 52px; background-color: var(--hms-danger);"></div>
                        <div class="fw-bold text-danger text-truncate" style="font-size: 0.85rem;">Critical / Due</div>
                        <div class="font-monospace text-muted-hms" style="font-size: 0.75rem;">#EF4444</div>
                        <div class="badge bg-danger-subtle text-danger-emphasis border mt-1" style="font-size: 0.7rem;">Emergency / Alert</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 2: Bilingual Typography System -->
        <section class="mb-5">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="h5 fw-bold text-navy mb-0">2. Bilingual Typography (Inter & Hind Siliguri)</h3>
                    <p class="text-muted-hms mb-0" style="font-size: 0.825rem;">Clean Latin numeric alignment paired with native Hind Siliguri for patients and clinical receipts.</p>
                </div>
            </div>

            <div class="row g-3">
                <!-- English Hierarchy -->
                <div class="col-lg-6">
                    <div class="hms-card p-4 h-100">
                        <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                            <span class="fw-bold text-navy"><i class="bi bi-type me-1 text-teal"></i> English Clinical Typeface (Inter)</span>
                            <span class="badge bg-light text-secondary border">Default</span>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted-hms d-block" style="font-size: 0.7rem; font-weight: 600;">H1 HEADER (24px, Bold, -0.015em)</span>
                            <h1 class="h3 mb-1">Inpatient Clinical Rounds & Triage</h1>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted-hms d-block" style="font-size: 0.7rem; font-weight: 600;">BODY TEXT (14px, Line Height 1.5)</span>
                            <p class="text-main mb-1">
                                Patient admitted under Gynecology department for routine postpartum observation. Vital signs stable, initial lab panel reviewed.
                            </p>
                        </div>
                        <div>
                            <span class="text-muted-hms d-block" style="font-size: 0.7rem; font-weight: 600;">TABULAR NUMERICS (tnum, monospace alignment)</span>
                            <div class="d-flex flex-wrap gap-2 pt-1">
                                <span class="code-badge">ID: MCNH-2026-0042</span>
                                <span class="code-badge">BP: 120/80 mmHg</span>
                                <span class="code-badge">SpO2: 99.0%</span>
                                <span class="code-badge">TOTAL: ৳ 4,500.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bengali Hierarchy -->
                <div class="col-lg-6">
                    <div class="hms-card p-4 h-100 font-bn">
                        <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                            <span class="fw-bold text-navy"><i class="bi bi-fonts me-1 text-teal"></i> বাংলা টাইপোগ্রাফি (হিন্দ শিলিগুড়ি)</span>
                            <span class="badge bg-light text-secondary border">Bangla</span>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted-hms d-block" style="font-size: 0.7rem; font-weight: 600;">শিরোনাম (২৪ পিক্সেল, বোল্ড)</span>
                            <h2 class="h3 mb-1">মেঘনা ক্লিনিক অ্যান্ড নার্সিং হোম</h2>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted-hms d-block" style="font-size: 0.7rem; font-weight: 600;">বর্ণনা ও ব্যবস্থাপত্র (১৪ পিক্সেল, লাইন হাইট ১.৬)</span>
                            <p class="text-main mb-1">
                                শহীদ মিনার মোড়, দিনাজপুর। ২৪ ঘণ্টা জরুরি সেবা, অভিজ্ঞ গাইনি ও অর্থোপেডিক বিশেষজ্ঞ চিকিৎসক এবং সার্বক্ষণিক নার্সিং পর্যবেক্ষণ।
                            </p>
                        </div>
                        <div>
                            <span class="text-muted-hms d-block" style="font-size: 0.7rem; font-weight: 600;">বাংলা রসিদ ও ক্লিনিক্যাল টার্মস</span>
                            <div class="d-flex flex-wrap gap-2 pt-1">
                                <span class="hms-badge hms-badge-navy">রোগীর কোড: MCNH-২০২৬-০০৪২</span>
                                <span class="hms-badge hms-badge-teal">কেবিন নম্বর: GYN-০১</span>
                                <span class="hms-badge hms-badge-success">পরিশোধিত: ৳ ৪,৫০০.০০</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 3: Bento Grid Layout System (Modern Healthcare SaaS Dashboard) -->
        <section class="mb-5">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="h5 fw-bold text-navy mb-0">3. Bento Grid Healthcare Dashboard Modules</h3>
                    <p class="text-muted-hms mb-0" style="font-size: 0.825rem;">Modular widgets designed for immediate operational oversight without cognitive overload.</p>
                </div>
            </div>

            <div class="bento-grid">
                <!-- KPI 1: Bed Occupancy -->
                <x-bento-card
                    span="3"
                    variant="warning"
                    kpiLabel="Bed Occupancy"
                    kpiValue="8 / 10"
                    kpiIcon="hospital"
                    kpiMeta="80% Total Clinic Capacity"
                >
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 80%" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </x-bento-card>

                <!-- KPI 2: Emergency Intake -->
                <x-bento-card
                    span="3"
                    variant="danger"
                    kpiLabel="Emergency Intake"
                    kpiValue="03 Cases"
                    kpiIcon="heart-pulse"
                    kpiMeta="2 Critical, 1 In Consultation"
                />

                <!-- KPI 3: Daily Revenue Collection -->
                <x-bento-card
                    span="3"
                    variant="teal"
                    kpiLabel="Today's Collections"
                    kpiValue="৳ 42,500"
                    kpiIcon="cash-coin"
                    kpiMeta="18 Invoices Settled (100%)"
                />

                <!-- KPI 4: Pending Lab Tests -->
                <x-bento-card
                    span="3"
                    variant="kpi"
                    kpiLabel="Lab Reports Pending"
                    kpiValue="04 Reports"
                    kpiIcon="virus"
                    kpiMeta="2 Outsourced, 2 Internal CBC"
                />

                <!-- Large Bento Card: Live Bed Status Map (Meghna Clinic 10-Bed Profile) -->
                <div class="bento-col-8">
                    <x-card title="Live Inpatient Bed Allocation" subtitle="Real-time occupancy across 4 clinical wards (10 Beds Total)" icon="door-open">
                        <x-slot:action>
                            <x-button variant="outline-teal" size="sm" icon="arrow-clockwise">
                                Refresh Status
                            </x-button>
                        </x-slot:action>

                        <div class="row g-2">
                            <!-- Emergency Beds (2) -->
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-2 text-center bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">EMERGENCY</span>
                                        <span class="hms-badge-dot bg-danger"></span>
                                    </div>
                                    <div class="fw-bold text-navy">EM-01</div>
                                    <small class="text-danger fw-semibold d-block">Occupied</small>
                                    <small class="text-muted-hms" style="font-size: 0.7rem;">MCNH-0012</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-2 text-center bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">EMERGENCY</span>
                                        <span class="hms-badge-dot bg-success"></span>
                                    </div>
                                    <div class="fw-bold text-navy">EM-02</div>
                                    <small class="text-success fw-semibold d-block">Available</small>
                                    <small class="text-muted-hms" style="font-size: 0.7rem;">Ready</small>
                                </div>
                            </div>

                            <!-- Gynecology Beds (2) -->
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-2 text-center bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">GYN / OBS</span>
                                        <span class="hms-badge-dot bg-danger"></span>
                                    </div>
                                    <div class="fw-bold text-navy">GYN-01</div>
                                    <small class="text-danger fw-semibold d-block">Occupied</small>
                                    <small class="text-muted-hms" style="font-size: 0.7rem;">MCNH-0021</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-2 text-center bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">GYN / OBS</span>
                                        <span class="hms-badge-dot bg-danger"></span>
                                    </div>
                                    <div class="fw-bold text-navy">GYN-02</div>
                                    <small class="text-danger fw-semibold d-block">Occupied</small>
                                    <small class="text-muted-hms" style="font-size: 0.7rem;">MCNH-0024</small>
                                </div>
                            </div>

                            <!-- Surgery Beds (2) -->
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-2 text-center bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">SURGERY</span>
                                        <span class="hms-badge-dot bg-danger"></span>
                                    </div>
                                    <div class="fw-bold text-navy">SUR-01</div>
                                    <small class="text-danger fw-semibold d-block">Occupied</small>
                                    <small class="text-muted-hms" style="font-size: 0.7rem;">MCNH-0033</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-2 text-center bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">SURGERY</span>
                                        <span class="hms-badge-dot bg-danger"></span>
                                    </div>
                                    <div class="fw-bold text-navy">SUR-02</div>
                                    <small class="text-danger fw-semibold d-block">Occupied</small>
                                    <small class="text-muted-hms" style="font-size: 0.7rem;">MCNH-0035</small>
                                </div>
                            </div>

                            <!-- General Ward Beds (4) -->
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-2 text-center bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">GEN WARD</span>
                                        <span class="hms-badge-dot bg-danger"></span>
                                    </div>
                                    <div class="fw-bold text-navy">GEN-01</div>
                                    <small class="text-danger fw-semibold d-block">Occupied</small>
                                    <small class="text-muted-hms" style="font-size: 0.7rem;">MCNH-0038</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-2 text-center bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">GEN WARD</span>
                                        <span class="hms-badge-dot bg-success"></span>
                                    </div>
                                    <div class="fw-bold text-navy">GEN-02</div>
                                    <small class="text-success fw-semibold d-block">Available</small>
                                    <small class="text-muted-hms" style="font-size: 0.7rem;">Ready</small>
                                </div>
                            </div>
                        </div>

                        <x-slot:footer>
                            <span class="text-muted-hms" style="font-size: 0.8rem;">
                                <i class="bi bi-info-circle me-1 text-teal"></i> Double-booking prevention enforced via atomic DB row locks.
                            </span>
                            <span class="badge bg-primary-subtle text-navy border">Total: 10 Beds Configured</span>
                        </x-slot:footer>
                    </x-card>
                </div>

                <!-- Side Bento Card: Quick Actions -->
                <div class="bento-col-4">
                    <x-card title="Quick Clinical Actions" subtitle="One-click role workflows" icon="lightning-charge">
                        <div class="d-flex flex-column gap-2">
                            <x-button variant="primary" icon="person-plus" class="w-100 justify-content-start">
                                Intake New Patient
                            </x-button>
                            <x-button variant="teal" icon="clipboard-plus" class="w-100 justify-content-start">
                                Record Triage Vitals
                            </x-button>
                            <x-button variant="outline-primary" icon="receipt" class="w-100 justify-content-start">
                                Generate Bill & Invoice
                            </x-button>
                            <x-button variant="outline-teal" icon="file-earmark-medical" class="w-100 justify-content-start">
                                Order Laboratory Test
                            </x-button>
                        </div>
                    </x-card>
                </div>
            </div>
        </section>

        <!-- Section 4: Interactive Component Suite -->
        <section id="components-section" class="mb-5">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="h5 fw-bold text-navy mb-0">4. Reusable Component Suite</h3>
                    <p class="text-muted-hms mb-0" style="font-size: 0.825rem;">Standardized Bootstrap 5 Blade components ready for portal assembly.</p>
                </div>
            </div>

            <!-- Row: Buttons & Badges -->
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <x-card title="Button System" subtitle="Variants, focus rings and accessibility targets">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <x-button variant="primary" icon="check-lg">Primary Navy</x-button>
                            <x-button variant="teal" icon="activity">Medical Teal</x-button>
                            <x-button variant="outline-primary">Outline Navy</x-button>
                            <x-button variant="outline-teal">Outline Teal</x-button>
                            <x-button variant="ghost">Ghost Button</x-button>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <x-button variant="teal" size="sm" icon="save">Small Button</x-button>
                            <x-button variant="primary" size="md">Regular (40px)</x-button>
                            <x-button variant="primary" disabled="true" icon="slash-circle">Disabled State</x-button>
                        </div>
                    </x-card>
                </div>

                <div class="col-lg-6">
                    <x-card title="Clinical Status Pills" subtitle="Color-coded condition badges with pulse dots">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <x-badge status="success">Discharged / Normal</x-badge>
                            <x-badge status="warning">Pending Clearance</x-badge>
                            <x-badge status="danger">Critical / Emergency</x-badge>
                            <x-badge status="info">Under Consultation</x-badge>
                            <x-badge status="teal">Sample Collected</x-badge>
                            <x-badge status="navy">Admitted Inpatient</x-badge>
                            <x-badge status="neutral">Draft Record</x-badge>
                        </div>
                        <div class="p-3 bg-light rounded-2 border">
                            <span class="text-muted-hms d-block mb-1" style="font-size: 0.75rem;">Interactive Modal Dialog Demo:</span>
                            <x-button variant="teal" size="sm" icon="window-stack" data-bs-toggle="modal" data-bs-target="#demoModal">
                                Launch Test Modal
                            </x-button>
                        </div>
                    </x-card>
                </div>
            </div>

            <!-- Row: Form Controls -->
            <div class="row g-4 mb-4">
                <div class="col-lg-12">
                    <x-card title="Healthcare Form Elements" subtitle="Crisp inputs with accessible labels, hint helpers and validation states">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <x-form-input
                                    name="patient_name"
                                    label="Full Patient Name"
                                    placeholder="e.g. Most. Fatema Begum"
                                    required="true"
                                    hint="Enter exact name as per NID or Birth Certificate"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-form-input
                                    name="phone_number"
                                    label="Mobile Number (Primary)"
                                    placeholder="+88017XXXXXXXX"
                                    required="true"
                                    hint="11-digit Bangladeshi mobile number"
                                />
                            </div>
                            <div class="col-md-4">
                                <label class="hms-form-label required">Department / Ward</label>
                                <select class="hms-form-select">
                                    <option value="">Select Clinical Department...</option>
                                    <option value="EMERG">Emergency (24/7)</option>
                                    <option value="GYN">Gynecology & Obstetrics</option>
                                    <option value="ORTHO">Orthopedic Surgery</option>
                                    <option value="SURG">General Surgery</option>
                                </select>
                                <div class="hms-form-hint">Ward determines primary bed allocation</div>
                            </div>
                            <div class="col-md-6">
                                <x-form-input
                                    name="valid_field"
                                    label="Provisional Clinical Diagnosis"
                                    value="Acute Appendicitis - Inpatient Observation"
                                />
                            </div>
                            <div class="col-md-6">
                                <x-form-input
                                    name="invalid_demo"
                                    label="Validation Error State Demonstration"
                                    value="Invalid Patient Code Format"
                                    error="Patient code must follow approved format (MCNH-YYYY-XXXX)"
                                />
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>

            <!-- Row: Alerts & Callouts -->
            <div class="row g-4 mb-4">
                <div class="col-lg-12">
                    <x-card title="Accessible System Alerts" subtitle="Dismissible feedback banners for clinical and billing operations">
                        <x-alert type="success" dismissible="true">
                            <strong>Dual Discharge Complete:</strong> Patient MCNH-2026-0038 has received both Clinical Clearance by Dr. Surgeon and Financial Settlement by Cashier.
                        </x-alert>
                        <x-alert type="warning" dismissible="true">
                            <strong>Bed Capacity Threshold Alert:</strong> Ward occupancy is at 80% (8 of 10 beds active). Emergency bed EM-02 must be reserved for acute walk-in admissions.
                        </x-alert>
                        <x-alert type="danger" dismissible="true">
                            <strong>Severe Drug Allergy Detected:</strong> Patient has documented anaphylaxis reaction to Penicillin. System will block matching prescription items.
                        </x-alert>
                    </x-card>
                </div>
            </div>

            <!-- Row: Data Tables (Clinical Queue Simulation) -->
            <div class="row g-4 mb-4">
                <div class="col-lg-12">
                    <x-card title="Enterprise Clinical Intake Queue" subtitle="Dense tabular data view with high contrast and tabular numeric values">
                        <x-slot:action>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-secondary border">Queue: 4 Patients Waiting</span>
                            </div>
                        </x-slot:action>

                        <x-table :headers="['Patient Code', 'Patient Name', 'Age / Gender', 'Department', 'Attending Doctor', 'Triage Status', 'Actions']">
                            <tr>
                                <td><span class="code-badge">MCNH-2026-0042</span></td>
                                <td class="fw-semibold text-navy">Most. Rokeya Akhter</td>
                                <td>28y / Female</td>
                                <td><span class="badge bg-light text-secondary border">Gynecology</span></td>
                                <td>Dr. Nasreen Sultana</td>
                                <td><x-badge status="warning">Priority (Active)</x-badge></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-hms-teal btn-hms-sm" title="Open Consultation">
                                            <i class="bi bi-stethoscope"></i> Consult
                                        </button>
                                        <button class="btn btn-hms-ghost btn-hms-sm" title="View Patient Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="code-badge">MCNH-2026-0043</span></td>
                                <td class="fw-semibold text-navy">Md. Rafiqul Islam</td>
                                <td>45y / Male</td>
                                <td><span class="badge bg-light text-secondary border">Orthopedic</span></td>
                                <td>Dr. M. A. Karim</td>
                                <td><x-badge status="danger">Critical Emergency</x-badge></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-hms-teal btn-hms-sm" title="Open Consultation">
                                            <i class="bi bi-stethoscope"></i> Consult
                                        </button>
                                        <button class="btn btn-hms-ghost btn-hms-sm" title="View Patient Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="code-badge">MCNH-2026-0044</span></td>
                                <td class="fw-semibold text-navy">Anowar Hossain</td>
                                <td>34y / Male</td>
                                <td><span class="badge bg-light text-secondary border">General Surgery</span></td>
                                <td>Dr. S. K. Roy</td>
                                <td><x-badge status="info">In Consultation</x-badge></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-hms-teal btn-hms-sm" title="Open Consultation">
                                            <i class="bi bi-stethoscope"></i> Consult
                                        </button>
                                        <button class="btn btn-hms-ghost btn-hms-sm" title="View Patient Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="code-badge">MCNH-2026-0045</span></td>
                                <td class="fw-semibold text-navy">Shirin Akter</td>
                                <td>22y / Female</td>
                                <td><span class="badge bg-light text-secondary border">Emergency</span></td>
                                <td>Emergency Duty Doctor</td>
                                <td><x-badge status="success">Normal (Walk-in)</x-badge></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-hms-teal btn-hms-sm" title="Open Consultation">
                                            <i class="bi bi-stethoscope"></i> Consult
                                        </button>
                                        <button class="btn btn-hms-ghost btn-hms-sm" title="View Patient Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </x-table>
                    </x-card>
                </div>
            </div>
        </section>

        <!-- Footer Note -->
        <footer class="pt-4 pb-5 border-top text-center text-muted-hms" style="font-size: 0.825rem;">
            <p class="mb-1"><strong>Meghna Clinic and Nursing Home HMS</strong> — Production UI Design System</p>
            <p class="mb-0">Shohid Minar Mor, Dinajpur, Bangladesh | Built with Laravel 12, Bootstrap 5 & Bento Grid Architecture</p>
        </footer>
    </main>

    <!-- Demo Modal Component -->
    <x-modal id="demoModal" title="Patient Bed Reassignment Confirmation" icon="arrow-left-right">
        <p class="mb-2 text-main">
            Are you sure you want to reassign patient <strong>Most. Rokeya Akhter (MCNH-2026-0042)</strong> from bed <strong>EM-01</strong> to <strong>GYN-01</strong>?
        </p>
        <div class="p-2.5 bg-light rounded border mb-2" style="font-size: 0.8rem;">
            <div class="d-flex justify-content-between mb-1">
                <span class="text-muted-hms">Daily Bed Rate:</span>
                <span class="fw-bold tabular-nums">৳ 1,200.00 / day</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-muted-hms">Clinical Clearance Required:</span>
                <span class="text-success fw-bold">Verified by Nurse</span>
            </div>
        </div>
        <p class="text-muted-hms mb-0" style="font-size: 0.75rem;">
            This action will update the active bed assignment with timestamped audit logging.
        </p>

        <x-slot:footer>
            <x-button variant="ghost" size="sm" data-bs-dismiss="modal">
                Cancel
            </x-button>
            <x-button variant="teal" size="sm" icon="check-circle" data-bs-dismiss="modal">
                Confirm Transfer
            </x-button>
        </x-slot:footer>
    </x-modal>
</x-layouts.app>
