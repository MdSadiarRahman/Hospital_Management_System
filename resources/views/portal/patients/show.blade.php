<x-layouts.portal role="reception" title="Patient Profile & Medical Record — {{ $patient->patient_code }}">
    <div class="container-fluid px-0">
        <!-- Page Header & Action Toolbar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('portal.patients.index') }}" class="text-decoration-none text-muted-hms">Patient Registry</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">{{ $patient->patient_code }}</li>
                    </ol>
                </nav>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">{{ $patient->name }}</h1>
                    <span class="badge bg-navy text-white font-monospace px-2.5 py-1.5" style="font-size: 0.85rem;">
                        {{ $patient->patient_code }}
                    </span>
                    @if($patient->blood_group)
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle fw-bold px-2 py-1">
                            <i class="bi bi-droplet-fill me-0.5"></i> {{ $patient->blood_group }}
                        </span>
                    @endif
                    @if($patient->is_verified)
                        <span class="badge bg-teal-subtle text-teal border border-teal-subtle rounded-pill">
                            <i class="bi bi-check2-all"></i> Verified Online Account
                        </span>
                    @else
                        <span class="badge bg-light text-muted border">
                            Unlinked / In-Person Only
                        </span>
                    @endif
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                @can('create', App\Models\Visit::class)
                    <a href="{{ route('portal.visits.create', ['patient_id' => $patient->id]) }}" class="btn btn-teal d-inline-flex align-items-center gap-2 shadow-xs">
                        <i class="bi bi-ticket-perforated-fill"></i>
                        <span>New Visit Check-in</span>
                    </a>
                @endcan

                <a href="{{ route('portal.patients.medical-record', $patient->id) }}" target="_blank" class="btn btn-outline-navy d-inline-flex align-items-center gap-2 shadow-xs" title="Print Comprehensive Medical Chart">
                    <i class="bi bi-file-earmark-medical"></i>
                    <span>Medical Chart</span>
                </a>

                @can('print', $patient)
                    <a href="{{ route('portal.patients.print-card', $patient->id) }}" target="_blank" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 shadow-xs">
                        <i class="bi bi-printer"></i>
                        <span>Registration Slip</span>
                    </a>
                @endcan

                @can('update', $patient)
                    <a href="{{ route('portal.patients.edit', $patient->id) }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 shadow-xs">
                        <i class="bi bi-pencil"></i>
                        <span>Edit Demographics</span>
                    </a>
                @endcan

                <a href="{{ route('portal.patients.index') }}" class="btn btn-light d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Registry
                </a>
            </div>
        </div>

        <!-- Alert Notification -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Bento KPI Metrics Bar: Patient Medical Overview -->
        <div class="row g-3 mb-4">
            <div class="col-xl col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Total Visits</span>
                        <span class="rounded bg-primary bg-opacity-10 text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-ticket-detailed-fill"></i>
                        </span>
                    </div>
                    <div class="h3 fw-bold text-navy mb-0">{{ number_format($clinicalMetrics['total_visits']) }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">
                        {{ $clinicalMetrics['emergency_visits'] }} Emergency • {{ $clinicalMetrics['regular_visits'] }} OPD
                    </span>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Inpatient Admissions</span>
                        <span class="rounded bg-teal bg-opacity-10 text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-hospital-fill"></i>
                        </span>
                    </div>
                    <div class="h3 fw-bold text-teal mb-0">{{ number_format($clinicalMetrics['admissions_count']) }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">
                        @if($clinicalMetrics['active_admission'])
                            <span class="text-primary fw-semibold">Active Bed: {{ $clinicalMetrics['active_admission']->currentBedAssignment?->bed?->bed_number ?? 'Assigned' }}</span>
                        @else
                            No Active Inpatient Stay
                        @endif
                    </span>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Consultations</span>
                        <span class="rounded bg-info bg-opacity-10 text-info p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-clipboard2-pulse-fill"></i>
                        </span>
                    </div>
                    <div class="h3 fw-bold text-navy mb-0">{{ number_format($clinicalMetrics['consultations_count']) }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">Physician clinical evaluations</span>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Prescriptions</span>
                        <span class="rounded bg-warning bg-opacity-10 text-warning p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-file-earmark-medical-fill"></i>
                        </span>
                    </div>
                    <div class="h3 fw-bold text-warning-emphasis mb-0">{{ number_format($clinicalMetrics['prescriptions_count']) }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">Electronic medication regimens</span>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Lab Tests & Discharges</span>
                        <span class="rounded bg-success bg-opacity-10 text-success p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-flask-fill"></i>
                        </span>
                    </div>
                    <div class="h3 fw-bold text-success mb-0">{{ number_format($clinicalMetrics['lab_orders_count']) }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">
                        {{ $clinicalMetrics['discharges_count'] }} Concluded Discharges
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Left Column: Patient Master Demographics & Contacts (Strict Separation) -->
            <div class="col-lg-4">
                <!-- Demographic Master Card -->
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-navy bg-opacity-10 text-navy p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-person-badge-fill fs-5"></i>
                            </span>
                            <h2 class="h5 fw-bold text-navy mb-0">Demographic Profile</h2>
                        </div>
                        <span class="badge bg-light text-muted border">Master Record</span>
                    </div>

                    <ul class="list-unstyled mb-0 d-flex flex-column gap-3" style="font-size: 0.85rem;">
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">Patient ID:</span>
                            <span class="fw-bold font-monospace text-navy">{{ $patient->patient_code }}</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">Full Legal Name:</span>
                            <span class="fw-bold text-navy">{{ $patient->name }}</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">Age & Date of Birth:</span>
                            <span class="fw-semibold text-navy">
                                {{ $patient->formatted_age }} ({{ $patient->date_of_birth ? $patient->date_of_birth->format('d M Y') : 'N/A' }})
                            </span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">Biological Gender:</span>
                            <span class="fw-semibold text-capitalize text-navy">{{ $patient->gender }}</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">Blood Group:</span>
                            <span class="fw-bold text-danger">{{ $patient->blood_group ?? 'Not Recorded' }}</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">Primary Phone:</span>
                            <span class="fw-semibold font-monospace text-navy">{{ $patient->phone }}</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">Email Address:</span>
                            <span class="text-navy">{{ $patient->email ?? 'None' }}</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">NID / Birth Cert:</span>
                            <span class="font-monospace text-navy">{{ $patient->nid_or_birth_cert ?? 'Not Provided' }}</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">Registration Date:</span>
                            <span class="text-navy">{{ $patient->registration_date ? $patient->registration_date->format('d M Y') : $patient->created_at->format('d M Y') }}</span>
                        </li>
                        <li>
                            <span class="text-muted-hms d-block mb-1">Residential Address:</span>
                            <p class="text-navy mb-0 bg-light p-2 rounded border" style="font-size: 0.825rem;">
                                {{ $patient->address ?? 'Shohid Minar Mor, Dinajpur Sadar, Dinajpur' }}
                            </p>
                        </li>
                    </ul>
                </div>

                <!-- Emergency Contacts Card -->
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-danger bg-opacity-10 text-danger p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-shield-fill-exclamation fs-5"></i>
                            </span>
                            <h2 class="h5 fw-bold text-navy mb-0">Emergency Contact</h2>
                        </div>
                    </div>

                    @if($patient->primaryContact)
                        <div class="p-3 rounded-3 bg-light border mb-2">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold text-navy" style="font-size: 0.95rem;">
                                    {{ $patient->primaryContact->name }}
                                </span>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">
                                    Primary Kin
                                </span>
                            </div>
                            <div class="text-muted small mb-2">
                                <span class="badge bg-white text-dark border">{{ $patient->primaryContact->relationship }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-1.5 font-monospace text-navy" style="font-size: 0.875rem;">
                                <i class="bi bi-telephone-fill text-danger"></i>
                                <a href="tel:{{ $patient->primaryContact->phone }}" class="text-navy text-decoration-none fw-semibold">
                                    {{ $patient->primaryContact->phone }}
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="p-3 text-center text-muted small bg-light rounded border">
                            No primary emergency contact on file.
                        </div>
                    @endif

                    @if($patient->contacts->count() > 1)
                        <div class="mt-3">
                            <span class="text-muted-hms fw-semibold small d-block mb-2">Secondary Contacts:</span>
                            @foreach($patient->contacts->where('is_primary', false) as $sec)
                                <div class="p-2 rounded bg-light border mb-1 small d-flex justify-content-between">
                                    <span>{{ $sec->name }} ({{ $sec->relationship }})</span>
                                    <span class="font-monospace text-muted">{{ $sec->phone }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Medical Record Overview & Historical Records Tabs -->
            <div class="col-lg-8">
                <!-- Navigation Tabs Bar -->
                <div class="card border-0 shadow-sm rounded-3 bg-white p-3 mb-4">
                    <ul class="nav nav-pills nav-fill gap-1" id="medicalRecordTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if($activeTab === 'overview') active @endif py-2 px-3 fw-semibold small" 
                                    id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                                <i class="bi bi-speedometer2 me-1"></i> Overview
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if($activeTab === 'visits') active @endif py-2 px-3 fw-semibold small" 
                                    id="visits-tab" data-bs-toggle="tab" data-bs-target="#visits-pane" type="button" role="tab">
                                <i class="bi bi-ticket-detailed me-1"></i> Visits ({{ $visits->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if($activeTab === 'consultations') active @endif py-2 px-3 fw-semibold small" 
                                    id="consultations-tab" data-bs-toggle="tab" data-bs-target="#consultations-pane" type="button" role="tab">
                                <i class="bi bi-clipboard2-pulse me-1"></i> Consultations ({{ $consultations->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if($activeTab === 'admissions') active @endif py-2 px-3 fw-semibold small" 
                                    id="admissions-tab" data-bs-toggle="tab" data-bs-target="#admissions-pane" type="button" role="tab">
                                <i class="bi bi-hospital me-1"></i> Admissions ({{ $admissions->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if($activeTab === 'prescriptions') active @endif py-2 px-3 fw-semibold small" 
                                    id="prescriptions-tab" data-bs-toggle="tab" data-bs-target="#prescriptions-pane" type="button" role="tab">
                                <i class="bi bi-file-earmark-medical me-1"></i> Prescriptions ({{ $prescriptions->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if($activeTab === 'lab') active @endif py-2 px-3 fw-semibold small" 
                                    id="lab-tab" data-bs-toggle="tab" data-bs-target="#lab-pane" type="button" role="tab">
                                <i class="bi bi-flask me-1"></i> Lab History ({{ $labOrders->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if($activeTab === 'discharges') active @endif py-2 px-3 fw-semibold small" 
                                    id="discharges-tab" data-bs-toggle="tab" data-bs-target="#discharges-pane" type="button" role="tab">
                                <i class="bi bi-file-earmark-check me-1"></i> Discharges ({{ $dischargeSummaries->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if($activeTab === 'audit') active @endif py-2 px-3 fw-semibold small" 
                                    id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit-pane" type="button" role="tab">
                                <i class="bi bi-shield-check me-1"></i> Audit Trail
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Tab Contents Container -->
                <div class="tab-content" id="medicalRecordTabsContent">
                    <!-- 1. Overview Tab -->
                    <div class="tab-pane fade @if($activeTab === 'overview') show active @endif" id="overview" role="tabpanel">
                        <!-- Recent Hospital Activity Feed -->
                        <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                            <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                                <h3 class="h5 fw-bold text-navy mb-0">Clinical Summary & Recent Activity</h3>
                                @can('create', App\Models\Visit::class)
                                    <a href="{{ route('portal.visits.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-teal">
                                        <i class="bi bi-plus-circle me-1"></i> Check-in Patient
                                    </a>
                                @endcan
                            </div>

                            <!-- Recent Visits Quick View -->
                            <h4 class="h6 fw-bold text-navy mb-2">Recent Visits</h4>
                            @if($visits->isNotEmpty())
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.825rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Visit Number</th>
                                                <th>Check-in Date</th>
                                                <th>Type</th>
                                                <th>Triage</th>
                                                <th>Care Mode</th>
                                                <th>Status</th>
                                                <th class="text-end pe-2">Details</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($visits->take(5) as $v)
                                                <tr>
                                                    <td class="font-monospace fw-bold text-navy">{{ $v->visit_number }}</td>
                                                    <td>{{ $v->visit_date ? $v->visit_date->format('d M Y, h:i A') : 'N/A' }}</td>
                                                    <td><span class="badge bg-light text-dark border">{{ ucfirst($v->visit_type) }}</span></td>
                                                    <td>
                                                        <span class="badge bg-{{ $v->triage_level === 'critical' ? 'danger' : ($v->triage_level === 'priority' ? 'warning text-dark' : 'success') }}-subtle text-{{ $v->triage_level === 'critical' ? 'danger' : ($v->triage_level === 'priority' ? 'warning-emphasis' : 'success') }}">
                                                            {{ ucfirst($v->triage_level) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $v->is_inpatient ? 'primary' : 'light text-dark border' }}">
                                                            {{ $v->is_inpatient ? 'Inpatient' : 'Outpatient' }}
                                                        </span>
                                                    </td>
                                                    <td><span class="badge bg-navy text-white">{{ ucfirst(str_replace('_', ' ', $v->status)) }}</span></td>
                                                    <td class="text-end pe-2">
                                                        <a href="{{ route('portal.visits.show', $v->id) }}" class="btn btn-xs btn-outline-navy">
                                                            View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="p-3 text-center text-muted small bg-light rounded border mb-4">
                                    No hospital visits registered yet.
                                </div>
                            @endif

                            <!-- Active Inpatient Stay Alert (if admitted) -->
                            @if($clinicalMetrics['active_admission'])
                                <div class="alert alert-primary border-0 p-3 mb-0">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <div>
                                            <div class="fw-bold fs-6">
                                                <i class="bi bi-hospital-fill me-1"></i> Patient Currently Admitted (Inpatient)
                                            </div>
                                            <div class="small">
                                                Admission No: <span class="font-monospace fw-semibold">{{ $clinicalMetrics['active_admission']->admission_number }}</span>
                                                • Bed: <span class="badge bg-navy text-white">{{ $clinicalMetrics['active_admission']->currentBedAssignment?->bed?->bed_number ?? 'Bed Allocated' }}</span>
                                                • Admitted on: {{ $clinicalMetrics['active_admission']->admission_date?->format('d M Y, h:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 2. Visits History Tab -->
                    <div class="tab-pane fade @if($activeTab === 'visits') show active @endif" id="visits-pane" role="tabpanel">
                        <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                            <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom flex-wrap gap-2">
                                <div>
                                    <h3 class="h5 fw-bold text-navy mb-0">All Patient Visits</h3>
                                    <p class="text-muted small mb-0">Complete historical check-in record for this patient</p>
                                </div>
                                @can('create', App\Models\Visit::class)
                                    <a href="{{ route('portal.visits.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-teal">
                                        <i class="bi bi-plus-circle me-1"></i> Check-in New Visit
                                    </a>
                                @endcan
                            </div>

                            @if($visits->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                        <thead class="table-light">
                                            <tr class="text-uppercase text-muted-hms" style="font-size: 0.75rem;">
                                                <th>Visit Number</th>
                                                <th>Check-in Date</th>
                                                <th>Classification</th>
                                                <th>Triage</th>
                                                <th>Care Mode</th>
                                                <th>Lifecycle Status</th>
                                                <th class="text-end pe-2">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($visits as $v)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('portal.visits.show', $v->id) }}" class="font-monospace fw-bold text-navy text-decoration-none hover-underline">
                                                            {{ $v->visit_number }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="fw-semibold text-navy">{{ $v->visit_date ? $v->visit_date->format('d M Y') : 'N/A' }}</span>
                                                        <span class="text-muted d-block small">{{ $v->visit_date ? $v->visit_date->format('h:i A') : '' }}</span>
                                                    </td>
                                                    <td><span class="badge bg-light text-dark border">{{ ucfirst($v->visit_type) }}</span></td>
                                                    <td>
                                                        <span class="badge bg-{{ $v->triage_level === 'critical' ? 'danger' : ($v->triage_level === 'priority' ? 'warning text-dark' : 'success') }}-subtle text-{{ $v->triage_level === 'critical' ? 'danger' : ($v->triage_level === 'priority' ? 'warning-emphasis' : 'success') }}">
                                                            {{ ucfirst($v->triage_level) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($v->is_inpatient)
                                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle">
                                                                Inpatient (Admitted)
                                                            </span>
                                                        @else
                                                            <span class="badge bg-light text-muted border">Outpatient</span>
                                                        @endif
                                                    </td>
                                                    <td><span class="badge bg-navy text-white">{{ ucfirst(str_replace('_', ' ', $v->status)) }}</span></td>
                                                    <td class="text-end pe-2">
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('portal.visits.show', $v->id) }}" class="btn btn-outline-navy" title="View Visit Dossier">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            @can('print', $v)
                                                                <a href="{{ route('portal.visits.print-token', $v->id) }}" target="_blank" class="btn btn-outline-teal" title="Print Token">
                                                                    <i class="bi bi-printer"></i>
                                                                </a>
                                                            @endcan
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="p-4 text-center text-muted bg-light rounded border">
                                    No visits recorded yet for this patient.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 3. Previous Consultations Tab -->
                    <div class="tab-pane fade @if($activeTab === 'consultations') show active @endif" id="consultations-pane" role="tabpanel">
                        <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                            <div class="pb-3 mb-3 border-bottom">
                                <h3 class="h5 fw-bold text-navy mb-0">Previous Clinical Consultations</h3>
                                <p class="text-muted small mb-0">Doctor evaluations, chief complaints, clinical notes, and diagnoses</p>
                            </div>

                            @if($consultations->isNotEmpty())
                                <div class="d-flex flex-column gap-3">
                                    @foreach($consultations as $cons)
                                        <div class="p-3.5 rounded-3 bg-light border">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pb-2 mb-2 border-bottom">
                                                <div>
                                                    <span class="fw-bold text-navy">
                                                        <i class="bi bi-person-fill text-teal"></i> Dr. {{ $cons->doctor->name }}
                                                    </span>
                                                    <span class="text-muted small">({{ $cons->doctor->staffProfile?->department?->name ?? 'Medical Officer' }})</span>
                                                    <span class="text-muted small ms-2">• Visit #{{ $cons->visit->visit_number }}</span>
                                                </div>
                                                <div class="text-muted small">
                                                    {{ $cons->created_at->format('d M Y, h:i A') }}
                                                    <span class="badge bg-{{ $cons->status === 'finalized' ? 'success' : 'warning' }}-subtle text-{{ $cons->status === 'finalized' ? 'success' : 'warning-emphasis' }} ms-1">
                                                        {{ ucfirst($cons->status) }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="mb-2" style="font-size: 0.85rem;">
                                                <strong>Chief Complaints:</strong>
                                                <p class="text-navy mb-1 bg-white p-2 rounded border">{{ $cons->chief_complaints }}</p>
                                            </div>

                                            @if($cons->clinical_notes)
                                                <div class="mb-2" style="font-size: 0.85rem;">
                                                    <strong>Clinical Notes:</strong>
                                                    <p class="text-navy mb-1 bg-white p-2 rounded border">{{ $cons->clinical_notes }}</p>
                                                </div>
                                            @endif

                                            @if($cons->diagnoses && $cons->diagnoses->isNotEmpty())
                                                <div class="mb-2" style="font-size: 0.825rem;">
                                                    <strong>Diagnoses:</strong>
                                                    <div class="d-flex flex-wrap gap-1.5 mt-1">
                                                        @foreach($cons->diagnoses as $diag)
                                                            <span class="badge bg-navy text-white px-2 py-1">
                                                                {{ $diag->condition_name }}
                                                                @if($diag->icd_code) ({{ $diag->icd_code }}) @endif
                                                                <span class="badge bg-light text-dark ms-0.5">{{ ucfirst($diag->diagnosis_type) }}</span>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if($cons->prescription)
                                                <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                                    <span class="text-muted small">Prescription attached ({{ $cons->prescription->items->count() }} items)</span>
                                                    <a href="{{ route('portal.visits.show', $cons->visit_id) }}" class="btn btn-xs btn-outline-teal">
                                                        View Full Consultation
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-4 text-center text-muted bg-light rounded border">
                                    No consultation records on file.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 4. Previous Admissions Tab -->
                    <div class="tab-pane fade @if($activeTab === 'admissions') show active @endif" id="admissions-pane" role="tabpanel">
                        <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                            <div class="pb-3 mb-3 border-bottom">
                                <h3 class="h5 fw-bold text-navy mb-0">Previous Inpatient Admissions (10 Beds)</h3>
                                <p class="text-muted small mb-0">Inpatient stay history, allocated beds, clearances, and outcomes</p>
                            </div>

                            @if($admissions->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                        <thead class="table-light">
                                            <tr class="text-uppercase text-muted-hms" style="font-size: 0.75rem;">
                                                <th>Admission No</th>
                                                <th>Admission Date</th>
                                                <th>Discharge Date</th>
                                                <th>Ward / Bed</th>
                                                <th>Reason / Indication</th>
                                                <th>Attending Doctor</th>
                                                <th>Clearances</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($admissions as $adm)
                                                <tr>
                                                    <td class="font-monospace fw-bold text-navy">{{ $adm->admission_number }}</td>
                                                    <td>{{ $adm->admission_date ? $adm->admission_date->format('d M Y') : 'N/A' }}</td>
                                                    <td>{{ $adm->discharge_date ? $adm->discharge_date->format('d M Y') : '—' }}</td>
                                                    <td>
                                                        <span class="badge bg-navy text-white">
                                                            {{ $adm->currentBedAssignment?->bed?->bed_number ?? 'Bed Assigned' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-wrap" style="max-width: 220px;">
                                                        <span class="fw-medium text-navy">{{ $adm->admission_reason ?? 'General Admission' }}</span>
                                                    </td>
                                                    <td>{{ $adm->attendingDoctor?->name ?? 'Medical Officer' }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $adm->clinical_clearance ? 'success' : 'secondary' }}-subtle text-{{ $adm->clinical_clearance ? 'success' : 'secondary' }}">
                                                            Clinical: {{ $adm->clinical_clearance ? 'Yes' : 'No' }}
                                                        </span>
                                                        <span class="badge bg-{{ $adm->financial_clearance ? 'success' : 'secondary' }}-subtle text-{{ $adm->financial_clearance ? 'success' : 'secondary' }}">
                                                            Billing: {{ $adm->financial_clearance ? 'Yes' : 'No' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $adm->status === 'admitted' ? 'primary' : 'success' }}">
                                                            {{ ucfirst($adm->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="p-4 text-center text-muted bg-light rounded border">
                                    No inpatient bed admissions on record.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 5. Prescriptions Tab -->
                    <div class="tab-pane fade @if($activeTab === 'prescriptions') show active @endif" id="prescriptions-pane" role="tabpanel">
                        <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                            <div class="pb-3 mb-3 border-bottom">
                                <h3 class="h5 fw-bold text-navy mb-0">Prescription History</h3>
                                <p class="text-muted small mb-0">Electronic medication records authored during clinical visits</p>
                            </div>

                            @if($prescriptions->isNotEmpty())
                                <div class="d-flex flex-column gap-3">
                                    @foreach($prescriptions as $rx)
                                        <div class="p-3 rounded-3 bg-light border">
                                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                                <div>
                                                    <span class="fw-bold text-navy">Dr. {{ $rx->consultation?->doctor?->name ?? 'Medical Officer' }}</span>
                                                    <span class="text-muted small ms-2">Date: {{ $rx->created_at->format('d M Y') }}</span>
                                                    @if($rx->consultation && $rx->consultation->visit)
                                                        <span class="text-muted small ms-2">• Visit #{{ $rx->consultation->visit->visit_number }}</span>
                                                    @endif
                                                </div>
                                                @if($rx->follow_up_date)
                                                    <span class="badge bg-info-subtle text-info-emphasis border">
                                                        Follow-up: {{ $rx->follow_up_date->format('d M Y') }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if($rx->items && $rx->items->isNotEmpty())
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered bg-white align-middle mb-0" style="font-size: 0.8rem;">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Medicine</th>
                                                                <th>Dosage</th>
                                                                <th>Frequency</th>
                                                                <th>Duration</th>
                                                                <th>Instructions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($rx->items as $item)
                                                                <tr>
                                                                    <td class="fw-bold text-navy">{{ $item->medicine_name }}</td>
                                                                    <td>{{ $item->dosage }}</td>
                                                                    <td>{{ $item->frequency }}</td>
                                                                    <td>{{ $item->duration }}</td>
                                                                    <td class="text-muted">{{ $item->instructions ?? '—' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif

                                            @if($rx->advice)
                                                <div class="mt-2 text-muted small">
                                                    <strong>Advice:</strong> {{ $rx->advice }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-4 text-center text-muted bg-light rounded border">
                                    No prescriptions recorded.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 6. Laboratory History Tab -->
                    <div class="tab-pane fade @if($activeTab === 'lab') show active @endif" id="lab-pane" role="tabpanel">
                        <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                            <div class="pb-3 mb-3 border-bottom">
                                <h3 class="h5 fw-bold text-navy mb-0">Laboratory & Diagnostic History</h3>
                                <p class="text-muted small mb-0">Internal lab investigations and external diagnostic center partner orders</p>
                            </div>

                            @if($labOrders->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                        <thead class="table-light">
                                            <tr class="text-uppercase text-muted-hms" style="font-size: 0.75rem;">
                                                <th>Order Number</th>
                                                <th>Order Date</th>
                                                <th>Source</th>
                                                <th>Tests Ordered</th>
                                                <th>Status</th>
                                                <th>Doctor Review</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($labOrders as $order)
                                                <tr>
                                                    <td class="font-monospace fw-bold text-navy">{{ $order->order_number }}</td>
                                                    <td>{{ $order->created_at->format('d M Y') }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $order->source === 'internal' ? 'teal' : 'primary' }}-subtle text-{{ $order->source === 'internal' ? 'teal' : 'primary' }} border">
                                                            {{ ucfirst($order->source) }}
                                                            @if($order->labPartner) ({{ $order->labPartner->code }}) @endif
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($order->items && $order->items->isNotEmpty())
                                                            {{ $order->items->pluck('test_name')->join(', ') }}
                                                        @else
                                                            <span class="text-muted">General Panel</span>
                                                        @endif
                                                    </td>
                                                    <td><span class="badge bg-light text-dark border">{{ ucfirst($order->status) }}</span></td>
                                                    <td>
                                                        @php
                                                            $allReviewed = $order->items->every(fn($i) => $i->report && $i->report->review_status === 'reviewed');
                                                        @endphp
                                                        @if($allReviewed && $order->items->isNotEmpty())
                                                            <span class="badge bg-success-subtle text-success">
                                                                <i class="bi bi-check-circle"></i> Reviewed
                                                            </span>
                                                        @else
                                                            <span class="badge bg-warning-subtle text-warning-emphasis">
                                                                Pending Review
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="p-4 text-center text-muted bg-light rounded border">
                                    No laboratory investigations recorded.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 7. Discharge History Tab -->
                    <div class="tab-pane fade @if($activeTab === 'discharges') show active @endif" id="discharges-pane" role="tabpanel">
                        <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                            <div class="pb-3 mb-3 border-bottom">
                                <h3 class="h5 fw-bold text-navy mb-0">Inpatient Discharge History</h3>
                                <p class="text-muted small mb-0">Concluded hospital discharge summaries and follow-up guidance</p>
                            </div>

                            @if($dischargeSummaries->isNotEmpty())
                                <div class="d-flex flex-column gap-3">
                                    @foreach($dischargeSummaries as $dc)
                                        <div class="p-3 rounded-3 bg-light border">
                                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                                <div>
                                                    <span class="font-monospace fw-bold text-navy fs-6">Admission #{{ $dc->admission->admission_number }}</span>
                                                    <span class="badge bg-success text-white ms-2">Discharged</span>
                                                </div>
                                                <div class="text-muted small">
                                                    Condition: <span class="badge bg-light text-dark border">{{ ucfirst($dc->condition_at_discharge) }}</span>
                                                    • Date: {{ $dc->created_at->format('d M Y') }}
                                                </div>
                                            </div>

                                            <div class="small text-navy mb-2">
                                                <div><strong>Discharge Diagnosis:</strong> {{ $dc->discharge_diagnosis }}</div>
                                                <div><strong>Treatment Given:</strong> {{ $dc->treatment_given }}</div>
                                                <div><strong>Discharge Medications:</strong> {{ $dc->discharge_medications }}</div>
                                                @if($dc->follow_up_instructions)
                                                    <div><strong>Follow-up Instructions:</strong> {{ $dc->follow_up_instructions }}</div>
                                                @endif
                                            </div>

                                            <div class="text-muted small border-top pt-1 text-end">
                                                Authorized by: <strong>Dr. {{ $dc->authorizedBy?->name ?? 'Attending Doctor' }}</strong>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-4 text-center text-muted bg-light rounded border">
                                    No concluded discharge summaries on record.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 8. Audit History Tab -->
                    <div class="tab-pane fade @if($activeTab === 'audit') show active @endif" id="audit-pane" role="tabpanel">
                        <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
                            <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded bg-secondary bg-opacity-10 text-secondary p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-shield-check fs-5"></i>
                                    </span>
                                    <h3 class="h5 fw-bold text-navy mb-0">Security Audit Trail & Master History</h3>
                                </div>
                                <span class="badge bg-light text-muted border">Tamper-Evident System Log</span>
                            </div>

                            @if($patient->auditLogs && $patient->auditLogs->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.8rem;">
                                        <thead class="table-light">
                                            <tr class="text-uppercase text-muted-hms" style="font-size: 0.725rem;">
                                                <th>Timestamp</th>
                                                <th>Action Event</th>
                                                <th>Staff User</th>
                                                <th>IP Address</th>
                                                <th>Details</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($patient->auditLogs as $log)
                                                <tr>
                                                    <td class="text-muted">{{ $log->created_at->format('d M Y, h:i:s A') }}</td>
                                                    <td>
                                                        <span class="badge bg-navy text-white">
                                                            {{ ucwords(str_replace('_', ' ', $log->event)) }}
                                                        </span>
                                                    </td>
                                                    <td class="fw-semibold text-navy">{{ $log->user?->name ?? 'System' }}</td>
                                                    <td class="font-monospace text-muted" style="font-size: 0.75rem;">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                                                    <td>
                                                        @if($log->event === 'patient_registered' && !empty($log->new_values['duplicate_warning_acknowledged']))
                                                            <span class="text-warning-emphasis small"><i class="bi bi-exclamation-triangle"></i> Override Confirmed</span>
                                                        @else
                                                            <span class="text-muted small">Logged</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="p-3 text-center text-muted small bg-light rounded border">
                                    No audit entries recorded.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.portal>
