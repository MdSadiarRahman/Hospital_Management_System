<x-layouts.patient title="Patient Self-Care Dashboard" activeNav="dashboard">
    <div class="container-fluid p-0">
        <!-- 1. Welcome Section & Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <x-breadcrumb :items="['Patient Portal' => null, 'Health Dashboard' => null]" class="mb-1" />
                <h1 class="h3 fw-bold text-navy mb-0">Welcome back, {{ $user->name }}</h1>
                <p class="text-muted-hms mb-0" style="font-size: 0.875rem;">
                    Meghna Clinic & Nursing Home &bull; Shohid Minar Mor, Dinajpur &bull; {{ now()->format('l, d F Y') }}
                </p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                @if($isLinked)
                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold border border-success-subtle">
                        <i class="bi bi-shield-check-fill me-1"></i> Verified Patient: {{ $patient->patient_code }}
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fw-semibold border border-warning-subtle">
                        <i class="bi bi-shield-exclamation me-1"></i> Pending In-Person Verification
                    </span>
                @endif
                <a href="{{ route('patient.profile') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-person-gear me-1"></i> Profile & Contacts
                </a>
            </div>
        </div>

        <!-- Session Status & Flash Messages -->
        @if(session('status'))
            <x-alert type="success" class="mb-4" dismissible>
                {{ session('status') }}
            </x-alert>
        @endif

        @if(session('error'))
            <x-alert type="danger" class="mb-4" dismissible>
                {{ session('error') }}
            </x-alert>
        @endif

        <!-- Identity Verification Notice (if Unlinked) -->
        @if(!$isLinked)
            <div class="card border-warning-subtle rounded-3 bg-warning-subtle p-4 mb-4 shadow-sm">
                <div class="d-flex align-items-start gap-3">
                    <span class="rounded-circle bg-warning text-white p-2.5 d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                        <i class="bi bi-shield-lock-fill fs-5"></i>
                    </span>
                    <div>
                        <h2 class="h6 fw-bold text-navy mb-1">In-Person Clinical Record Linking Required</h2>
                        <p class="text-muted-hms mb-2" style="font-size: 0.875rem; line-height: 1.6;">
                            Your online patient account is active, but to protect medical privacy, digital accounts are not automatically linked to hospital clinical histories. Please present your phone number (<strong>{{ $user->phone }}</strong>) or National ID at our reception counter at <strong>Shohid Minar Mor, Dinajpur</strong> to link your prescriptions, laboratory diagnostics, and billing history.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('patient.profile') }}" class="btn btn-sm btn-outline-dark">
                                <i class="bi bi-person-lines-fill me-1"></i> Update Contact Info
                            </a>
                            <a href="tel:+8801711000000" class="btn btn-sm btn-dark">
                                <i class="bi bi-telephone-fill me-1"></i> Call Clinic Reception
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- 2. Profile Summary & Key Health Indicators -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card hms-card p-3 h-100 border-0 shadow-sm">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-teal-subtle text-teal p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-person-vcard fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted-hms d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Patient ID</span>
                            <span class="h5 fw-bold text-navy mb-0 d-block">{{ $patient?->patient_code ?? 'Not Linked' }}</span>
                            <small class="text-muted" style="font-size: 0.75rem;">
                                {{ $isLinked ? 'Physically Verified' : 'Awaiting Reception Desk' }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card hms-card p-3 h-100 border-0 shadow-sm">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-droplet-half fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted-hms d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Blood Group & Gender</span>
                            <span class="h5 fw-bold text-navy mb-0 d-block">
                                {{ $patient?->blood_group ?? 'Unrecorded' }} &bull; {{ ucfirst($patient?->gender ?? 'N/A') }}
                            </span>
                            <small class="text-muted" style="font-size: 0.75rem;">
                                DOB: {{ $patient?->date_of_birth ? $patient->date_of_birth->format('d M Y') : 'Unrecorded' }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card hms-card p-3 h-100 border-0 shadow-sm">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-telephone-inbound fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted-hms d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Contact & Emergency</span>
                            <span class="h6 fw-bold text-navy mb-0 d-block text-truncate" style="max-width: 170px;">
                                {{ $user->phone ?? 'No phone' }}
                            </span>
                            <small class="text-muted" style="font-size: 0.75rem;">
                                {{ $patient?->contacts->count() ?? 0 }} Emergency Guardian(s)
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card hms-card p-3 h-100 border-0 shadow-sm">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-hospital fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted-hms d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Inpatient Care Status</span>
                            @if($currentAdmission)
                                <span class="h6 fw-bold text-teal mb-0 d-block">
                                    Bed {{ $currentAdmission->currentBedAssignment?->bed?->bed_number ?? 'Assigned' }}
                                </span>
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    {{ $currentAdmission->currentBedAssignment?->bed?->department?->name ?? 'Ward' }}
                                </small>
                            @else
                                <span class="h6 fw-bold text-navy mb-0 d-block">Outpatient Care</span>
                                <small class="text-muted" style="font-size: 0.75rem;">No active ward stay</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Bento Grid: Upcoming Appointments & Notifications -->
        <div class="row g-4 mb-4">
            <!-- Upcoming Appointments -->
            <div class="col-lg-8" id="appointments-section">
                <x-card title="My Upcoming Doctor Appointments">
                    <x-slot:actions>
                        <a href="tel:+8801711000000" class="btn btn-outline-teal btn-sm">
                            <i class="bi bi-calendar2-plus me-1"></i> Book via Reception
                        </a>
                    </x-slot:actions>

                    @if($upcomingAppointments->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr style="font-size: 0.8rem;" class="text-uppercase text-muted-hms">
                                        <th>Date & Time</th>
                                        <th>Doctor & Department</th>
                                        <th>Serial #</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upcomingAppointments as $appt)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold text-navy">
                                                    {{ $appt->appointment_date->format('d M Y') }}
                                                </div>
                                                <small class="text-muted">{{ $appt->appointment_date->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-navy">{{ $appt->doctor?->name ?? 'Attending Doctor' }}</div>
                                                <small class="text-muted">{{ $appt->department?->name ?? 'General OPD' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-navy text-white px-2 py-1">
                                                    Serial #{{ str_pad($appt->serial_number ?? 1, 2, '0', STR_PAD_LEFT) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    {{ ucfirst(str_replace('_', ' ', $appt->type)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($appt->status === 'confirmed')
                                                    <x-badge status="success">Confirmed</x-badge>
                                                @elseif($appt->status === 'pending')
                                                    <x-badge status="warning">Pending</x-badge>
                                                @else
                                                    <x-badge status="secondary">{{ ucfirst($appt->status) }}</x-badge>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <x-empty-state 
                            icon="calendar2-check" 
                            title="No Scheduled Appointments" 
                            description="You do not have any upcoming doctor appointments scheduled. To book a consultation with our specialist doctors, contact reception at Shohid Minar Mor or call +880 1711-000000."
                            actionText="Call Front Desk Reception"
                            actionUrl="tel:+8801711000000"
                            actionIcon="telephone-fill"
                        />
                    @endif
                </x-card>
            </div>

            <!-- Notifications Card -->
            <div class="col-lg-4" id="notifications-section">
                <x-card title="Hospital Notifications">
                    <x-slot:actions>
                        @if($unreadNotificationsCount > 0)
                            <form action="{{ route('patient.notifications.read-all') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link text-teal p-0 text-decoration-none fw-semibold" style="font-size: 0.75rem;">
                                    Mark All Read ({{ $unreadNotificationsCount }})
                                </button>
                            </form>
                        @endif
                    </x-slot:actions>

                    @if($notifications->isNotEmpty())
                        <div class="d-flex flex-column gap-2">
                            @foreach($notifications as $notif)
                                <div class="p-2.5 rounded-3 border {{ $notif->is_read ? 'bg-light text-muted' : 'bg-white border-teal-subtle shadow-xs' }}">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <div class="d-flex align-items-start gap-2">
                                            @if($notif->type === 'appointment')
                                                <i class="bi bi-calendar-event text-teal mt-0.5"></i>
                                            @elseif($notif->type === 'lab_ready')
                                                <i class="bi bi-file-earmark-medical text-primary mt-0.5"></i>
                                            @elseif($notif->type === 'billing')
                                                <i class="bi bi-receipt text-success mt-0.5"></i>
                                            @else
                                                <i class="bi bi-info-circle text-info mt-0.5"></i>
                                            @endif
                                            <div>
                                                <h6 class="mb-0 fw-semibold text-navy" style="font-size: 0.825rem;">
                                                    {{ $notif->title }}
                                                </h6>
                                                <p class="mb-1 text-muted-hms" style="font-size: 0.775rem; line-height: 1.4;">
                                                    {{ $notif->message }}
                                                </p>
                                                <span class="text-muted" style="font-size: 0.7rem;">
                                                    {{ $notif->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                        </div>
                                        @if(!$notif->is_read)
                                            <form action="{{ route('patient.notifications.read', $notif->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-link text-muted p-0" title="Mark as read">
                                                    <i class="bi bi-check2"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <x-empty-state 
                            icon="bell" 
                            title="No Notifications" 
                            description="You are all caught up! There are no unread clinical or administrative alerts." 
                        />
                    @endif
                </x-card>
            </div>
        </div>

        <!-- 4. Recent Clinical Consultation Visits -->
        <div class="card hms-card p-4 border-0 shadow-sm mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h2 class="h5 fw-bold text-navy mb-0">Recent Consultation Visits</h2>
                    <p class="text-muted-hms mb-0" style="font-size: 0.825rem;">
                        Outpatient triage, medical officer consultations, and emergency room visits.
                    </p>
                </div>
            </div>

            @if($recentVisits->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr style="font-size: 0.8rem;" class="text-uppercase text-muted-hms">
                                <th>Visit Number</th>
                                <th>Date & Time</th>
                                <th>Attending Doctor</th>
                                <th>Department</th>
                                <th>Visit Type</th>
                                <th>Triage</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentVisits as $visit)
                                <tr>
                                    <td class="fw-bold text-navy">{{ $visit->visit_number }}</td>
                                    <td>{{ $visit->visit_date->format('d M Y, h:i A') }}</td>
                                    <td>{{ $visit->consultation?->doctor?->name ?? 'General Medical Officer' }}</td>
                                    <td>{{ $visit->consultation?->doctor?->staffProfile?->department?->name ?? 'Clinical Department' }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ ucfirst($visit->visit_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($visit->triage_level === 'critical')
                                            <span class="badge bg-danger text-white">Critical</span>
                                        @elseif($visit->triage_level === 'priority')
                                            <span class="badge bg-warning text-dark">Priority</span>
                                        @else
                                            <span class="badge bg-secondary text-white">Routine</span>
                                        @endif
                                    </td>
                                    <td>
                                        <x-badge status="success">{{ ucfirst($visit->status) }}</x-badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-empty-state 
                    icon="clock-history" 
                    title="No Recorded Consultation Visits" 
                    description="No hospital visits are currently recorded on your clinical chart. When you visit our OPD or Emergency desk, your intake and triage details will be logged here."
                />
            @endif
        </div>

        <!-- 5. Available Digital Prescriptions -->
        <div class="card hms-card p-4 border-0 shadow-sm mb-4" id="prescriptions-section">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h2 class="h5 fw-bold text-navy mb-0">Available Prescriptions & Clinical Advice</h2>
                    <p class="text-muted-hms mb-0" style="font-size: 0.825rem;">
                        Official digital prescriptions issued by attending doctors at Meghna Clinic.
                    </p>
                </div>
            </div>

            @if($prescriptions->isNotEmpty())
                <div class="row g-3">
                    @foreach($prescriptions as $rx)
                        <div class="col-md-6 col-xl-6">
                            <div class="card border rounded-3 p-3 h-100 bg-white shadow-xs">
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                    <div>
                                        <h3 class="h6 fw-bold text-navy mb-0">
                                            {{ $rx->consultation?->doctor?->name ?? 'Attending Doctor' }}
                                        </h3>
                                        <small class="text-teal fw-semibold">
                                            {{ $rx->consultation?->doctor?->staffProfile?->specialization ?? 'General Healthcare & Surgery' }}
                                        </small>
                                    </div>
                                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle px-2 py-1">
                                        <i class="bi bi-calendar2-check me-1"></i> {{ $rx->created_at->format('d M Y') }}
                                    </span>
                                </div>

                                <!-- Diagnoses tags if available -->
                                @if($rx->consultation?->diagnoses?->isNotEmpty())
                                    <div class="mb-2">
                                        <span class="text-muted-hms fw-semibold" style="font-size: 0.725rem;">CLINICAL IMPRESSION:</span>
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            @foreach($rx->consultation->diagnoses as $diag)
                                                <span class="badge bg-light text-dark border" style="font-size: 0.75rem;">
                                                    {{ $diag->condition_name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Prescribed Medicines list -->
                                <div class="p-2.5 rounded bg-light border mb-2">
                                    <span class="text-muted-hms fw-semibold d-block mb-1" style="font-size: 0.725rem;">PRESCRIBED MEDICATIONS:</span>
                                    <ul class="list-unstyled mb-0" style="font-size: 0.825rem;">
                                        @foreach($rx->items as $item)
                                            <li class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                <div>
                                                    <span class="fw-bold text-navy">{{ $item->medicine_name }}</span>
                                                    <small class="text-muted d-block" style="font-size: 0.725rem;">{{ $item->instructions ?? 'As directed' }}</small>
                                                </div>
                                                <span class="badge bg-teal text-white">{{ $item->frequency }} &bull; {{ $item->duration }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <!-- Advice notes -->
                                @if($rx->advice)
                                    <p class="text-muted-hms mb-2" style="font-size: 0.8rem; line-height: 1.5;">
                                        <strong>Advice:</strong> {{ $rx->advice }}
                                    </p>
                                @endif

                                <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-auto">
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        Follow-up: <strong>{{ $rx->follow_up_date ? \Carbon\Carbon::parse($rx->follow_up_date)->format('d M Y') : 'As needed' }}</strong>
                                    </small>
                                    <a href="{{ route('patient.prescriptions.show', $rx->id) }}" class="btn btn-outline-teal btn-sm" target="_blank">
                                        <i class="bi bi-printer me-1"></i> View & Print Rx
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state 
                    icon="file-earmark-medical" 
                    title="No Active Prescriptions" 
                    description="You do not have any digital prescriptions registered on your health record. Prescriptions issued by our consultants will be accessible here immediately following your visit."
                />
            @endif
        </div>

        <!-- 6. Diagnostic Laboratory Reports -->
        <div class="card hms-card p-4 border-0 shadow-sm mb-4" id="reports-section">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h2 class="h5 fw-bold text-navy mb-0">Diagnostic Laboratory Reports</h2>
                    <p class="text-muted-hms mb-0" style="font-size: 0.825rem;">
                        Verified pathology, hematology, and biochemistry test reports published by our laboratory team.
                    </p>
                </div>
            </div>

            @if($labReports->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr style="font-size: 0.8rem;" class="text-uppercase text-muted-hms">
                                <th>Order #</th>
                                <th>Test Name & Category</th>
                                <th>Date Published</th>
                                <th>Reviewed By</th>
                                <th>Findings Overview</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($labReports as $report)
                                <tr>
                                    <td class="fw-bold text-navy">{{ $report->orderItem?->labOrder?->order_number ?? 'LAB-ORD' }}</td>
                                    <td>
                                        <div class="fw-semibold text-navy">{{ $report->orderItem?->test_name ?? 'Diagnostic Test' }}</div>
                                        <small class="text-muted">{{ $report->orderItem?->labTest?->category ?? 'General Pathology' }}</small>
                                    </td>
                                    <td>
                                        {{ $report->reviewed_at ? $report->reviewed_at->format('d M Y, h:i A') : $report->created_at->format('d M Y') }}
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-navy">{{ $report->reviewedBy?->name ?? 'Medical Technologist' }}</div>
                                        <small class="text-muted">{{ $report->reviewedBy?->staffProfile?->designation ?? 'Consultant' }}</small>
                                    </td>
                                    <td>
                                        <p class="text-muted-hms mb-0 text-truncate" style="max-width: 260px; font-size: 0.8rem;" title="{{ $report->findings_summary }}">
                                            {{ $report->findings_summary ?? 'Parameters within standard physiological reference range.' }}
                                        </p>
                                    </td>
                                    <td>
                                        <a href="{{ route('patient.lab-reports.download', $report->id) }}" class="btn btn-outline-teal btn-sm" target="_blank">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> View / Download
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-empty-state 
                    icon="file-earmark-pdf" 
                    title="No Diagnostic Lab Reports" 
                    description="No laboratory diagnostic investigations have been published for your profile yet. Once test samples are analyzed and authorized by our pathologist, you can view and download reports securely here."
                />
            @endif
        </div>

        <!-- 7. Billing Summary & Invoices -->
        <div class="card hms-card p-4 border-0 shadow-sm mb-4" id="billing-section">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h2 class="h5 fw-bold text-navy mb-0">Billing Summary & Invoices</h2>
                    <p class="text-muted-hms mb-0" style="font-size: 0.825rem;">
                        Summary of hospital consultations, diagnostic tests, bed charges, and cleared payments.
                    </p>
                </div>
            </div>

            <!-- Financial Statistics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="p-3 rounded-3 bg-light border">
                        <span class="text-muted-hms d-block text-uppercase fw-semibold" style="font-size: 0.725rem;">Total Hospital Charges</span>
                        <span class="h4 fw-bold text-navy mb-0">৳ {{ number_format($totalBilled, 2) }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle">
                        <span class="text-success d-block text-uppercase fw-semibold" style="font-size: 0.725rem;">Total Cleared & Paid</span>
                        <span class="h4 fw-bold text-success mb-0">৳ {{ number_format($totalPaid, 2) }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-3 {{ $totalDue > 0 ? 'bg-danger-subtle border border-danger-subtle' : 'bg-light border' }}">
                        <span class="{{ $totalDue > 0 ? 'text-danger' : 'text-muted-hms' }} d-block text-uppercase fw-semibold" style="font-size: 0.725rem;">Current Due Balance</span>
                        <span class="h4 fw-bold {{ $totalDue > 0 ? 'text-danger' : 'text-navy' }} mb-0">৳ {{ number_format($totalDue, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Invoices Table -->
            @if($recentBills->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr style="font-size: 0.8rem;" class="text-uppercase text-muted-hms">
                                <th>Invoice #</th>
                                <th>Billing Date</th>
                                <th>Total Amount</th>
                                <th>Paid Amount</th>
                                <th>Due Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBills as $bill)
                                <tr>
                                    <td class="fw-bold text-navy">{{ $bill->invoice_number }}</td>
                                    <td>{{ $bill->created_at->format('d M Y') }}</td>
                                    <td class="fw-semibold">৳ {{ number_format($bill->total_amount, 2) }}</td>
                                    <td class="text-success fw-semibold">৳ {{ number_format($bill->paid_amount, 2) }}</td>
                                    <td class="{{ $bill->due_amount > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                        ৳ {{ number_format($bill->due_amount, 2) }}
                                    </td>
                                    <td>
                                        @if($bill->status === 'paid')
                                            <x-badge status="success">Paid in Full</x-badge>
                                        @elseif($bill->status === 'partial')
                                            <x-badge status="warning">Partial</x-badge>
                                        @else
                                            <x-badge status="danger">Unpaid</x-badge>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-empty-state 
                    icon="receipt" 
                    title="No Invoices Issued" 
                    description="You have no medical billing invoices on record. All hospital services and diagnostic fees are itemized and billed through the central cashier."
                />
            @endif
        </div>

        <!-- 8. Payment Status & Receipts -->
        <div class="card hms-card p-4 border-0 shadow-sm mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h2 class="h5 fw-bold text-navy mb-0">Payment Transaction Receipts</h2>
                    <p class="text-muted-hms mb-0" style="font-size: 0.825rem;">
                        Verified cashier receipts for cash, card, and mobile banking transactions.
                    </p>
                </div>
            </div>

            @if($recentPayments->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr style="font-size: 0.8rem;" class="text-uppercase text-muted-hms">
                                <th>Receipt #</th>
                                <th>Payment Date</th>
                                <th>Invoice Ref</th>
                                <th>Method</th>
                                <th>Amount Paid</th>
                                <th>Transaction Ref</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPayments as $payment)
                                <tr>
                                    <td class="fw-bold text-navy">{{ $payment->receipt_number }}</td>
                                    <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y, h:i A') : $payment->created_at->format('d M Y') }}</td>
                                    <td>{{ $payment->bill?->invoice_number ?? 'Invoice' }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border text-capitalize">
                                            {{ str_replace('_', ' ', $payment->payment_method) }}
                                        </span>
                                    </td>
                                    <td class="fw-bold text-success">৳ {{ number_format($payment->amount, 2) }}</td>
                                    <td class="text-muted" style="font-size: 0.825rem;">
                                        {{ $payment->transaction_ref ?? 'Counter Cash' }}
                                    </td>
                                    <td>
                                        <x-badge status="success">{{ ucfirst($payment->status) }}</x-badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-empty-state 
                    icon="cash-coin" 
                    title="No Payment Receipts Found" 
                    description="No hospital payment transactions have been logged for your profile yet."
                />
            @endif
        </div>
    </div>
</x-layouts.patient>
