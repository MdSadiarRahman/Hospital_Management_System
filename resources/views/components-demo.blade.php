<x-layouts.app title="Frontend Components Showcase">
    <!-- Top Navigation Header -->
    <header class="hms-topbar">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="rounded bg-primary-subtle p-2 text-navy d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-hospital fs-5 text-teal"></i>
                </span>
                <div>
                    <h1 class="h6 mb-0 fw-bold text-navy">Meghna Clinic & Nursing Home</h1>
                    <span class="text-muted-hms" style="font-size: 0.75rem;">Shohid Minar Mor, Dinajpur — 21 Reusable UI Components</span>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('design-system') }}" class="btn btn-hms-outline-teal btn-hms-sm">
                <i class="bi bi-palette"></i> Design Tokens
            </a>
            <span class="badge bg-success-subtle text-success-emphasis border">Phase 03 — 21 Components Ready</span>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container-fluid px-3 px-lg-5 py-4">
        <!-- 19. Page Header Component -->
        <x-page-header
            title="Reusable Blade Components Catalog"
            subtitle="Accessible, enterprise-grade healthcare interface components for Meghna Clinic HMS"
            icon="boxes"
            :breadcrumbs="['Clinical Portal' => '#', 'Design System' => route('design-system'), 'Components' => null]"
        >
            <x-slot:actions>
                <x-button variant="outline-primary" size="sm" icon="code-slash">
                    Export Schema
                </x-button>
                <x-button variant="teal" size="sm" icon="check2-all">
                    All 21 Verified
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <!-- Section 1: Buttons Suite (Primary, Secondary, Icon Buttons) -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-navy mb-3">
                <span class="badge bg-primary text-white me-2">1, 2, 3</span> Button Components
            </h2>
            <div class="row g-4">
                <div class="col-lg-6">
                    <x-card title="Primary & Secondary Buttons" subtitle="Accessible actions with semantic variants">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <!-- 1. Primary Button -->
                            <x-button variant="primary" icon="person-plus">
                                Primary Action (Navy)
                            </x-button>

                            <!-- 2. Secondary Button -->
                            <x-button variant="secondary" icon="heart-pulse">
                                Secondary Action (Teal)
                            </x-button>

                            <x-button variant="outline-primary">
                                Outline Navy
                            </x-button>

                            <x-button variant="outline-teal">
                                Outline Teal
                            </x-button>

                            <x-button variant="ghost">
                                Ghost
                            </x-button>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <x-button variant="primary" size="sm" icon="save">Small Button</x-button>
                            <x-button variant="secondary" size="md">Regular (40px)</x-button>
                            <x-button variant="primary" disabled="true" icon="slash-circle">Disabled State</x-button>
                        </div>
                    </x-card>
                </div>

                <div class="col-lg-6">
                    <x-card title="3. Icon Buttons" subtitle="Compact 36px touch targets for toolbars and table rows">
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div>
                                <span class="text-muted-hms d-block mb-1" style="font-size: 0.75rem;">Default Ghost:</span>
                                <div class="d-flex gap-1">
                                    <x-icon-button icon="eye" title="View Patient File" />
                                    <x-icon-button icon="pencil" title="Edit Prescription" />
                                    <x-icon-button icon="printer" title="Print Invoice" />
                                    <x-icon-button icon="three-dots-vertical" title="More Options" />
                                </div>
                            </div>
                            <div>
                                <span class="text-muted-hms d-block mb-1" style="font-size: 0.75rem;">Primary Tinted:</span>
                                <div class="d-flex gap-1">
                                    <x-icon-button icon="person-badge" variant="primary" title="Physician Profile" />
                                    <x-icon-button icon="clipboard-data" variant="primary" title="Clinical Vitals" />
                                </div>
                            </div>
                            <div>
                                <span class="text-muted-hms d-block mb-1" style="font-size: 0.75rem;">Medical Teal:</span>
                                <div class="d-flex gap-1">
                                    <x-icon-button icon="check-lg" variant="teal" title="Approve Report" />
                                    <x-icon-button icon="arrow-clockwise" variant="teal" title="Refresh Beds" />
                                </div>
                            </div>
                            <div>
                                <span class="text-muted-hms d-block mb-1" style="font-size: 0.75rem;">Danger Alert:</span>
                                <div class="d-flex gap-1">
                                    <x-icon-button icon="trash" variant="danger" title="Cancel Appointment" />
                                </div>
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>
        </section>

        <!-- Section 2: Form Controls (Input, Select, Textarea, Validation, Search) -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-navy mb-3">
                <span class="badge bg-primary text-white me-2">4, 5, 6, 7, 13</span> Form & Input Components
            </h2>
            <div class="row g-4">
                <div class="col-lg-12">
                    <x-card title="Healthcare Data Entry Forms" subtitle="Standardized inputs, selectors, textarea and validation messages">
                        <div class="row g-3">
                            <!-- 13. Search Field -->
                            <div class="col-md-12 mb-2">
                                <label class="hms-form-label">Search Patient Database (Component 13)</label>
                                <x-search-input
                                    name="patient_search"
                                    placeholder="Search by Patient Name, Phone (+88017...), or Code (MCNH-2026-0042)..."
                                />
                            </div>

                            <!-- 4. Input Field -->
                            <div class="col-md-4">
                                <x-form-input
                                    name="patient_name"
                                    label="Full Patient Name (Component 4)"
                                    placeholder="e.g. Most. Fatema Begum"
                                    required="true"
                                    hint="Enter exact name as per NID / Birth Certificate"
                                />
                            </div>

                            <!-- 5. Select Field -->
                            <div class="col-md-4">
                                <x-form-select
                                    name="department_id"
                                    label="Clinical Department (Component 5)"
                                    required="true"
                                    hint="Allocates primary ward and doctor roster"
                                    :options="[
                                        'EMERG' => 'Emergency (24/7 Intake)',
                                        'GYN' => 'Gynecology & Obstetrics',
                                        'ORTHO' => 'Orthopedic Surgery',
                                        'SURG' => 'General Surgery'
                                    ]"
                                />
                            </div>

                            <div class="col-md-4">
                                <x-form-input
                                    name="blood_pressure"
                                    label="Blood Pressure (Tabular)"
                                    placeholder="120/80"
                                    hint="Systolic / Diastolic mmHg"
                                />
                            </div>

                            <!-- 6. Textarea -->
                            <div class="col-md-8">
                                <x-form-textarea
                                    name="clinical_notes"
                                    label="Chief Complaints & History (Component 6)"
                                    placeholder="Describe presenting symptoms, onset duration, and prior drug history..."
                                    rows="3"
                                    hint="Entered by attending Medical Officer or Nurse"
                                />
                            </div>

                            <!-- 7. Form Validation Message -->
                            <div class="col-md-4">
                                <label class="hms-form-label">Validation Error State (Component 7)</label>
                                <input type="text" class="hms-form-control is-invalid" value="12345 (Invalid Format)">
                                <x-form-error message="Patient ID must match pattern MCNH-YYYY-XXXX" />
                                <div class="mt-3">
                                    <span class="text-muted-hms d-block" style="font-size: 0.75rem;">Standalone Field Error:</span>
                                    <x-form-error message="Clinical clearance is mandatory prior to discharge billing." />
                                </div>
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>
        </section>

        <!-- Section 3: Cards & Statistics (Info Card, Statistics Card, Bento Grid) -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-navy mb-3">
                <span class="badge bg-primary text-white me-2">9, 10</span> Information & Statistics Cards
            </h2>
            <div class="row g-4">
                <!-- 10. Statistics Cards -->
                <div class="col-md-3">
                    <x-stat-card
                        label="Daily Inpatients"
                        value="08 / 10 Beds"
                        icon="hospital"
                        variant="warning"
                        trend="up"
                        trendValue="+2 Today"
                        meta="80% Total Capacity"
                    />
                </div>
                <div class="col-md-3">
                    <x-stat-card
                        label="Emergency Walk-Ins"
                        value="05 Patients"
                        icon="heart-pulse"
                        variant="danger"
                        trend="up"
                        trendValue="+3 Critical"
                        meta="Shohid Minar Mor 24/7"
                    />
                </div>
                <div class="col-md-3">
                    <x-stat-card
                        label="Settled Invoices"
                        value="৳ 42,500.00"
                        icon="cash-coin"
                        variant="teal"
                        trend="up"
                        trendValue="18 Bills"
                        meta="100% Cash/MFS Cleared"
                    />
                </div>
                <div class="col-md-3">
                    <x-stat-card
                        label="Diagnostic Reports"
                        value="04 Pending"
                        icon="virus"
                        variant="primary"
                        trend="neutral"
                        trendValue="2 Outsourced"
                        meta="Lab Verification Pending"
                    />
                </div>

                <!-- 9. Information Card -->
                <div class="col-md-12">
                    <x-card title="Clinic Operational Profile (Component 9)" subtitle="Permanent baseline constraints and doctor-nurse coverage" icon="info-circle">
                        <x-slot:action>
                            <span class="badge bg-primary-subtle text-navy border">Profile Validated</span>
                        </x-slot:action>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="border rounded p-2.5 bg-light">
                                    <span class="text-muted-hms d-block" style="font-size: 0.75rem;">Total Inpatient Capacity</span>
                                    <span class="fw-bold text-navy fs-5 tabular-nums">10 Beds</span>
                                    <span class="d-block text-secondary" style="font-size: 0.75rem;">General & Departmental Wards</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2.5 bg-light">
                                    <span class="text-muted-hms d-block" style="font-size: 0.75rem;">Active Staff Roster</span>
                                    <span class="fw-bold text-navy fs-5 tabular-nums">~14 Staff</span>
                                    <span class="d-block text-secondary" style="font-size: 0.75rem;">Doctors, Nurses, Reception, Billing</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2.5 bg-light">
                                    <span class="text-muted-hms d-block" style="font-size: 0.75rem;">Doctor Coverage</span>
                                    <span class="fw-bold text-teal fs-5">2 Doctors (24/7)</span>
                                    <span class="d-block text-secondary" style="font-size: 0.75rem;">Emergency Medical Officer on duty</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2.5 bg-light">
                                    <span class="text-muted-hms d-block" style="font-size: 0.75rem;">Nurse Rotations</span>
                                    <span class="fw-bold text-navy fs-5 tabular-nums">6 Nurses</span>
                                    <span class="d-block text-secondary" style="font-size: 0.75rem;">8-Hour Shifts (Morning/Evening/Night)</span>
                                </div>
                            </div>
                        </div>

                        <x-slot:footer>
                            <span class="text-muted-hms" style="font-size: 0.8rem;">
                                Pharmacy retail sales deferred to future phase. Ward MAR and clinical prescriptions are in active scope.
                            </span>
                        </x-slot:footer>
                    </x-card>
                </div>
            </div>
        </section>

        <!-- Section 4: Data Tables, Filter Panel & Pagination -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-navy mb-3">
                <span class="badge bg-primary text-white me-2">11, 12, 14</span> Filter Panel, Data Table & Pagination
            </h2>

            <!-- 14. Filter Panel -->
            <x-filter-panel title="Filter Inpatient Roster & Clinical Queues">
                <div class="col-md-3">
                    <label class="hms-form-label">Ward / Department</label>
                    <select class="hms-form-select">
                        <option value="">All Departments</option>
                        <option value="EMERG">Emergency</option>
                        <option value="GYN">Gynecology</option>
                        <option value="ORTHO">Orthopedic</option>
                        <option value="SURG">Surgery</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="hms-form-label">Triage Priority</label>
                    <select class="hms-form-select">
                        <option value="">All Priorities</option>
                        <option value="normal">Normal (Routine)</option>
                        <option value="priority">Priority (Observation)</option>
                        <option value="critical">Critical (Emergency)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="hms-form-label">Admission Date</label>
                    <input type="date" class="hms-form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="hms-form-label">Payment Status</label>
                    <select class="hms-form-select">
                        <option value="">All Statuses</option>
                        <option value="paid">Fully Paid</option>
                        <option value="partial">Partial</option>
                        <option value="due">Due / Unpaid</option>
                    </select>
                </div>
            </x-filter-panel>

            <!-- 11. Data Table -->
            <x-table :headers="['Patient Code', 'Patient Name', 'Admission Date', 'Assigned Bed', 'Daily Rate', 'Clearance State', 'Actions']">
                <tr>
                    <td><span class="code-badge">MCNH-2026-0042</span></td>
                    <td class="fw-semibold text-navy">Most. Rokeya Akhter</td>
                    <td>2026-10-30 08:30</td>
                    <td><span class="badge bg-light text-secondary border">GYN-01</span></td>
                    <td class="tabular-nums">৳ 1,200.00</td>
                    <td><x-badge status="warning">Clinical Cleared (Bill Pending)</x-badge></td>
                    <td>
                        <div class="d-flex gap-1">
                            <x-icon-button icon="stethoscope" variant="teal" title="Consult" />
                            <x-icon-button icon="receipt" variant="primary" title="Invoicing" />
                            <x-icon-button icon="arrow-left-right" title="Transfer Bed" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><span class="code-badge">MCNH-2026-0043</span></td>
                    <td class="fw-semibold text-navy">Md. Rafiqul Islam</td>
                    <td>2026-10-30 11:15</td>
                    <td><span class="badge bg-light text-secondary border">EM-01</span></td>
                    <td class="tabular-nums">৳ 1,500.00</td>
                    <td><x-badge status="danger">Critical (Inpatient)</x-badge></td>
                    <td>
                        <div class="d-flex gap-1">
                            <x-icon-button icon="stethoscope" variant="teal" title="Consult" />
                            <x-icon-button icon="receipt" variant="primary" title="Invoicing" />
                            <x-icon-button icon="arrow-left-right" title="Transfer Bed" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><span class="code-badge">MCNH-2026-0044</span></td>
                    <td class="fw-semibold text-navy">Anowar Hossain</td>
                    <td>2026-10-29 16:00</td>
                    <td><span class="badge bg-light text-secondary border">SUR-01</span></td>
                    <td class="tabular-nums">৳ 1,200.00</td>
                    <td><x-badge status="success">Dual Cleared (Discharged)</x-badge></td>
                    <td>
                        <div class="d-flex gap-1">
                            <x-icon-button icon="file-earmark-medical" variant="primary" title="Summary" />
                            <x-icon-button icon="printer" title="Print Discharge" />
                        </div>
                    </td>
                </tr>
            </x-table>

            <!-- 12. Pagination -->
            <x-pagination :from="1" :to="3" :total="42" />
        </section>

        <!-- Section 5: Feedback & Dialogs (Status Badges, Alerts, Modals, Confirm Dialog) -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-navy mb-3">
                <span class="badge bg-primary text-white me-2">8, 15, 16, 17</span> Status Badges, Alerts & Dialogs
            </h2>
            <div class="row g-4">
                <!-- 8. Status Badges -->
                <div class="col-lg-6">
                    <x-card title="8. Clinical Status Badges" subtitle="Pill chips with color-coded pulse dots">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <x-badge status="success">Normal / Cleared</x-badge>
                            <x-badge status="warning">Priority / Observation</x-badge>
                            <x-badge status="danger">Critical / Emergency</x-badge>
                            <x-badge status="info">Under Consultation</x-badge>
                            <x-badge status="teal">Sample Collected</x-badge>
                            <x-badge status="navy">Inpatient Admitted</x-badge>
                            <x-badge status="neutral">Archived / Draft</x-badge>
                        </div>
                        <div class="d-flex gap-2">
                            <!-- 15. Modal Trigger -->
                            <x-button variant="teal" size="sm" icon="window-stack" data-bs-toggle="modal" data-bs-target="#standardModal">
                                Launch Standard Modal (15)
                            </x-button>

                            <!-- 16. Confirmation Dialog Trigger -->
                            <x-button variant="primary" size="sm" icon="exclamation-diamond" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal">
                                Confirmation Dialog (16)
                            </x-button>
                        </div>
                    </x-card>
                </div>

                <!-- 17. Alert Messages -->
                <div class="col-lg-6">
                    <x-card title="17. Alert Messages" subtitle="Dismissible feedback callouts for patient safety">
                        <x-alert type="success" dismissible="true">
                            <strong>Payment Received:</strong> Bill BILL-2026-0042 settled via Cash (৳ 1,200.00). Financial clearance granted.
                        </x-alert>
                        <x-alert type="warning" dismissible="true">
                            <strong>Emergency Bed Reservation:</strong> Ward occupancy is at 80%. Bed EM-02 must be reserved for walk-ins.
                        </x-alert>
                        <x-alert type="danger" dismissible="true">
                            <strong>Anaphylaxis Warning:</strong> Documented Penicillin allergy. Prescription validation will reject penicillin derivatives.
                        </x-alert>
                    </x-card>
                </div>
            </div>
        </section>

        <!-- Section 6: States (Empty State, Loading State) -->
        <section class="mb-5">
            <h2 class="h5 fw-bold text-navy mb-3">
                <span class="badge bg-primary text-white me-2">20, 21</span> Empty & Loading States
            </h2>
            <div class="row g-4">
                <!-- 20. Empty State -->
                <div class="col-lg-6">
                    <x-card title="20. Empty State Component" subtitle="Engaging placeholder when 0 records match criteria">
                        <x-empty-state
                            icon="folder-x"
                            title="No Pending Diagnostic Reports"
                            description="All ordered laboratory tests for today have been reviewed, verified and published to patient files."
                            actionText="Order New Lab Test"
                            actionUrl="#"
                            actionIcon="plus-lg"
                        />
                    </x-card>
                </div>

                <!-- 21. Loading State -->
                <div class="col-lg-6">
                    <x-card title="21. Loading State Component" subtitle="Skeleton pulse loaders and circular async spinner">
                        <div class="row g-3">
                            <div class="col-6">
                                <span class="text-muted-hms d-block mb-1" style="font-size: 0.75rem;">Skeleton Loader (Table Rows):</span>
                                <x-loading-state type="skeleton-table" rows="3" />
                            </div>
                            <div class="col-6">
                                <span class="text-muted-hms d-block mb-1" style="font-size: 0.75rem;">Circular Spinner:</span>
                                <x-loading-state type="spinner" message="Syncing bed occupancy..." />
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="pt-4 pb-5 border-top text-center text-muted-hms" style="font-size: 0.825rem;">
            <p class="mb-1"><strong>Meghna Clinic and Nursing Home HMS</strong> — 21 Reusable Frontend Components</p>
            <p class="mb-0">Shohid Minar Mor, Dinajpur, Bangladesh | Pure Bootstrap 5 & Blade Architecture</p>
        </footer>
    </main>

    <!-- 15. Standard Modal Component -->
    <x-modal id="standardModal" title="Patient Bed Reassignment" icon="arrow-left-right" size="md">
        <p class="text-main mb-2">
            Select target department and available bed for patient <strong>Most. Rokeya Akhter (MCNH-2026-0042)</strong>:
        </p>
        <x-form-select
            name="target_bed"
            label="Available Bed"
            required="true"
            :options="[
                'GYN-02' => 'GYN-02 (Gynecology Ward — Available)',
                'SUR-02' => 'SUR-02 (Surgery Ward — Available)',
                'GEN-02' => 'GEN-02 (General Ward — Available)'
            ]"
        />
        <x-form-input
            name="transfer_reason"
            label="Reason for Transfer"
            placeholder="e.g. Post-operative stepdown from Emergency"
        />

        <x-slot:footer>
            <x-button variant="ghost" size="sm" data-bs-dismiss="modal">Cancel</x-button>
            <x-button variant="teal" size="sm" icon="check-lg" data-bs-dismiss="modal">Reassign Bed</x-button>
        </x-slot:footer>
    </x-modal>

    <!-- 16. Confirmation Dialog Component -->
    <x-confirm-dialog
        id="deleteConfirmModal"
        title="Revoke Bed Assignment?"
        message="Are you sure you want to release bed EM-01? This will mark the bed as Available and close the active occupancy record."
        confirmText="Release Bed"
        confirmVariant="danger"
        icon="exclamation-octagon"
    />
</x-layouts.app>
