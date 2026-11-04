<x-layouts.portal role="doctor" title="Doctor Clinical Workstation — Meghna Clinic & Nursing Home" activeNav="dashboard">
    <!-- 1. Doctor Profile & Duty Header -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 58px; height: 58px; font-size: 1.6rem;">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <h4 class="fw-bold text-navy mb-0">Dr. {{ $doctor->name }}</h4>
                            <span class="badge bg-teal text-white px-2 py-1 font-monospace" style="font-size: 0.75rem;">
                                {{ $doctor->staffProfile?->license_number ?? 'BMDC Registered' }}
                            </span>
                            @if($doctor->staffProfile?->department)
                                <span class="badge bg-navy text-white px-2 py-1">
                                    {{ $doctor->staffProfile->department->name }}
                                </span>
                            @endif
                        </div>
                        <p class="text-muted-hms small mb-0 mt-1">
                            <i class="bi bi-briefcase me-1 text-teal"></i> {{ $doctor->staffProfile?->designation ?? 'Consultant Physician & Surgeon' }} &bull; 
                            <i class="bi bi-mortarboard me-1 text-teal"></i> {{ $doctor->staffProfile?->qualification ?? 'MBBS, Specialized Clinical Care' }} &bull;
                            <i class="bi bi-clock-history me-1 text-teal"></i> Duty Shift: <span class="fw-semibold text-dark">{{ ucfirst($doctor->staffProfile?->shift ?? 'Rotational') }}</span>
                        </p>
                    </div>
                </div>

                <!-- Date & Doctor Switcher Toolbar -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <form method="GET" action="{{ route('portal.doctor') }}" class="d-flex align-items-center gap-2">
                        @if(auth()->user()->hasRole(['super_admin', 'administrator']) && $allDoctors->count() > 1)
                            <select name="doctor_id" class="form-select form-select-sm border-teal-subtle" onchange="this.form.submit()" title="Filter by Attending Doctor">
                                @foreach($allDoctors as $doc)
                                    <option value="{{ $doc->id }}" @selected($doc->id == $doctor->id)>
                                        Dr. {{ $doc->name }} ({{ $doc->staffProfile?->department?->code ?? 'DOC' }})
                                    </option>
                                @endforeach
                            </select>
                        @endif

                        <input type="date" name="date" class="form-control form-control-sm font-monospace" value="{{ $selectedDate }}" onchange="this.form.submit()" title="Select Clinical Roster Date">
                        @if($searchTerm || $selectedDate !== today()->toDateString())
                            <a href="{{ route('portal.doctor') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        @endif
                    </form>

                    <button type="button" class="btn btn-sm btn-outline-teal d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#quickPatientLookupModal">
                        <i class="bi bi-search"></i>
                        <span>Patient Dossier</span>
                    </button>
                    <a href="{{ route('portal.beds.occupancy') }}" class="btn btn-sm btn-teal d-flex align-items-center gap-1 shadow-sm">
                        <i class="bi bi-hospital"></i>
                        <span>10-Bed Inpatients</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Quick Clinical Search Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('portal.doctor') }}" class="row g-2 align-items-center">
                @if(request('doctor_id'))
                    <input type="hidden" name="doctor_id" value="{{ request('doctor_id') }}">
                @endif
                @if(request('date'))
                    <input type="hidden" name="date" value="{{ request('date') }}">
                @endif
                <div class="col-md-9 col-lg-10">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" value="{{ $searchTerm }}" class="form-control border-0 bg-light" placeholder="Quick search patient by Name, Patient Code (e.g. MCNH-2026-XXXX), Phone, or Token..." aria-label="Quick Patient Search">
                    </div>
                </div>
                <div class="col-md-3 col-lg-2 d-grid">
                    <button type="submit" class="btn btn-teal">
                        <i class="bi bi-filter me-1"></i> Filter Records
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 3. Clinical KPI Bento Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- Today's OPD Appointments -->
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Today's Appointments</span>
                        <span class="p-2 rounded bg-teal bg-opacity-10 text-teal">
                            <i class="bi bi-calendar2-check-fill fs-5"></i>
                        </span>
                    </div>
                    <h3 class="fw-bold text-navy mb-1">{{ $metrics['today_appointments'] }}</h3>
                    <div class="small text-muted-hms" style="font-size: 0.75rem;">
                        <span class="text-success fw-semibold">{{ $metrics['today_confirmed'] }} Confirmed</span> &bull; 
                        <span>{{ $metrics['today_completed'] }} Completed</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Consultation Queue -->
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Consultation Queue</span>
                        <span class="p-2 rounded bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-person-lines-fill fs-5"></i>
                        </span>
                    </div>
                    <h3 class="fw-bold text-navy mb-1">{{ $metrics['waiting_queue'] }}</h3>
                    <div class="small text-muted-hms" style="font-size: 0.75rem;">
                        @if($metrics['emergency_cases'] > 0)
                            <span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> {{ $metrics['emergency_cases'] }} Emergency</span>
                        @else
                            <span class="text-success"><i class="bi bi-check2"></i> Normal Flow</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Inpatient Ward Care (10 Beds) -->
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Active Inpatients</span>
                        <span class="p-2 rounded bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-hospital fs-5"></i>
                        </span>
                    </div>
                    <h3 class="fw-bold text-navy mb-1">{{ $metrics['active_admissions'] }}</h3>
                    <div class="small text-muted-hms" style="font-size: 0.75rem;">
                        <span>10-Bed Inpatient Ward</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Lab Reports for Review -->
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Pending Lab Reviews</span>
                        <span class="p-2 rounded bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-file-earmark-medical fs-5"></i>
                        </span>
                    </div>
                    <h3 class="fw-bold text-navy mb-1">{{ $metrics['pending_lab_reviews'] }}</h3>
                    <div class="small text-muted-hms" style="font-size: 0.75rem;">
                        <span>Awaiting doctor validation</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Treatment Follow-ups -->
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Follow-ups Due</span>
                        <span class="p-2 rounded bg-info bg-opacity-10 text-info">
                            <i class="bi bi-clock-history fs-5"></i>
                        </span>
                    </div>
                    <h3 class="fw-bold text-navy mb-1">{{ $metrics['follow_ups_due'] }}</h3>
                    <div class="small text-muted-hms" style="font-size: 0.75rem;">
                        <span>Next 7 days & overdue</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Clinical Navigation Tabs & Workspaces -->
    <ul class="nav nav-pills gap-2 mb-4 p-2 bg-white rounded-3 shadow-sm" id="doctorDashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold text-navy d-flex align-items-center gap-2" id="queue-tab" data-bs-toggle="pill" data-bs-target="#queuePane" type="button" role="tab">
                <i class="bi bi-people-fill text-teal"></i>
                <span>Consultation Queue</span>
                <span class="badge bg-teal text-white">{{ $consultationQueue->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-navy d-flex align-items-center gap-2" id="appointments-tab" data-bs-toggle="pill" data-bs-target="#appointmentsPane" type="button" role="tab">
                <i class="bi bi-calendar-event text-teal"></i>
                <span>Today's Appointments</span>
                <span class="badge bg-secondary text-white">{{ $todayAppointments->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-navy d-flex align-items-center gap-2" id="inpatients-tab" data-bs-toggle="pill" data-bs-target="#inpatientsPane" type="button" role="tab">
                <i class="bi bi-hospital text-teal"></i>
                <span>Active Admissions (10 Beds)</span>
                <span class="badge bg-primary text-white">{{ $activeAdmissions->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-navy d-flex align-items-center gap-2" id="labs-tab" data-bs-toggle="pill" data-bs-target="#labsPane" type="button" role="tab">
                <i class="bi bi-virus text-teal"></i>
                <span>Pending Lab Reports</span>
                @if($pendingLabReports->count() > 0)
                    <span class="badge bg-danger text-white">{{ $pendingLabReports->count() }}</span>
                @else
                    <span class="badge bg-secondary text-white">0</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-navy d-flex align-items-center gap-2" id="followups-tab" data-bs-toggle="pill" data-bs-target="#followupsPane" type="button" role="tab">
                <i class="bi bi-arrow-repeat text-teal"></i>
                <span>Treatment Follow-ups</span>
                <span class="badge bg-info text-dark">{{ $treatmentFollowUps->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-navy d-flex align-items-center gap-2" id="recent-tab" data-bs-toggle="pill" data-bs-target="#recentPane" type="button" role="tab">
                <i class="bi bi-journal-medical text-teal"></i>
                <span>Recent Consultations</span>
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="doctorDashboardTabsContent">
        <!-- 1. CONSULTATION QUEUE PANE -->
        <div class="tab-pane fade show active" id="queuePane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h5 class="fw-bold text-navy mb-0">Today's Live Consultation Queue</h5>
                        <span class="text-muted-hms small">Prioritized by triage severity and patient arrival timestamp</span>
                    </div>
                    <span class="badge bg-light text-navy border font-monospace">
                        Date: {{ \Carbon\Carbon::parse($selectedDate)->format('d M, Y') }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted-hms small text-uppercase">
                                <tr>
                                    <th>Token / Arrival</th>
                                    <th>Patient Details</th>
                                    <th>Triage Priority</th>
                                    <th>Department</th>
                                    <th>Visit Reason / Complaints</th>
                                    <th>Status</th>
                                    <th class="text-end">Clinical Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($consultationQueue as $visit)
                                    <tr>
                                        <td>
                                            <div class="fw-bold font-monospace text-teal">{{ $visit->visit_number }}</div>
                                            <div class="small text-muted-hms">{{ $visit->visit_date->format('h:i A') }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-navy">{{ $visit->patient?->name }}</div>
                                            <div class="small text-muted-hms font-monospace">
                                                {{ $visit->patient?->patient_code }} &bull; {{ $visit->patient?->formatted_age }}, {{ ucfirst($visit->patient?->gender ?? '') }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($visit->triage_level === 'critical' || $visit->triage_level === 'emergency')
                                                <span class="badge bg-danger text-white"><i class="bi bi-lightning-fill"></i> Emergency</span>
                                            @elseif($visit->triage_level === 'priority')
                                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle-fill"></i> Priority</span>
                                            @else
                                                <span class="badge bg-light text-dark border">Normal</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="small fw-semibold">{{ $visit->appointment?->department?->name ?? 'General OPD' }}</span>
                                        </td>
                                        <td>
                                            <div class="text-truncate small" style="max-width: 240px;">
                                                {{ $visit->appointment?->reason ?? ($visit->consultation?->chief_complaints ?? 'Routine physician consultation') }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($visit->status === 'in_consultation')
                                                <span class="badge bg-info text-white">In Consultation</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Waiting</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('portal.doctor.patient-history', $visit->patient_id) }}" class="btn btn-outline-secondary" title="View Patient Clinical Dossier">
                                                    <i class="bi bi-clock-history"></i> Dossier
                                                </a>
                                                <a href="{{ route('portal.visits.show', $visit) }}" class="btn btn-teal fw-semibold">
                                                    <i class="bi bi-pencil-square"></i> Attend
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted-hms">
                                            <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                                            No patients waiting in queue for Dr. {{ $doctor->name }} on this date.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. TODAY'S APPOINTMENTS PANE -->
        <div class="tab-pane fade" id="appointmentsPane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h5 class="fw-bold text-navy mb-0">Doctor Chamber Appointments</h5>
                        <span class="text-muted-hms small">Registered appointment slots booked with Dr. {{ $doctor->name }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted-hms small text-uppercase">
                                <tr>
                                    <th>Serial</th>
                                    <th>Time Slot</th>
                                    <th>Patient</th>
                                    <th>Type</th>
                                    <th>Purpose</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todayAppointments as $apt)
                                    <tr>
                                        <td class="fw-bold font-monospace text-teal">
                                            #{{ $apt->serial_number }}
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-navy">{{ $apt->appointment_time }}</div>
                                            <span class="text-muted-hms small" style="font-size: 0.72rem;">Duration: {{ $apt->slot_duration_minutes ?? 15 }}m</span>
                                        </td>
                                        <td>
                                            @if($apt->patient)
                                                <div class="fw-semibold text-navy">{{ $apt->patient->name }}</div>
                                                <div class="small text-muted-hms font-monospace">{{ $apt->patient->patient_code }} &bull; {{ $apt->patient->phone }}</div>
                                            @else
                                                <div class="fw-semibold text-navy">{{ $apt->guest_name }} (Unregistered)</div>
                                                <div class="small text-muted-hms">{{ $apt->guest_phone }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ ucfirst($apt->type ?? 'new') }}</span>
                                        </td>
                                        <td>
                                            <div class="small text-truncate" style="max-width: 220px;">
                                                {{ $apt->reason ?? 'Consultation & checkup' }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($apt->status === 'confirmed')
                                                <span class="badge bg-success text-white">Confirmed</span>
                                            @elseif($apt->status === 'completed')
                                                <span class="badge bg-teal text-white">Completed</span>
                                            @elseif($apt->status === 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @else
                                                <span class="badge bg-secondary text-white">{{ ucfirst($apt->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($apt->patient_id)
                                                <a href="{{ route('portal.doctor.patient-history', $apt->patient_id) }}" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-clock-history"></i> Dossier
                                                </a>
                                            @endif
                                            <a href="{{ route('portal.appointments.show', $apt) }}" class="btn btn-sm btn-outline-teal">
                                                <i class="bi bi-eye"></i> Details
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted-hms">
                                            <i class="bi bi-calendar-x fs-2 text-muted d-block mb-2"></i>
                                            No appointments scheduled for Dr. {{ $doctor->name }} on this date.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. INPATIENT WARD CARE PANE (10-BEDS) -->
        <div class="tab-pane fade" id="inpatientsPane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h5 class="fw-bold text-navy mb-0">Inpatient Ward Rounds (10 Clinical Beds)</h5>
                        <span class="text-muted-hms small">Patients currently admitted under the clinical oversight of Dr. {{ $doctor->name }}</span>
                    </div>
                    <a href="{{ route('portal.beds.occupancy') }}" class="btn btn-sm btn-outline-teal">
                        <i class="bi bi-grid-3x3-gap-fill me-1"></i> Visual Ward Map
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted-hms small text-uppercase">
                                <tr>
                                    <th>Bed Location</th>
                                    <th>Admission No.</th>
                                    <th>Patient</th>
                                    <th>Admitted Date & Duration</th>
                                    <th>Admission Reason</th>
                                    <th>Latest Vitals</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeAdmissions as $adm)
                                    <tr>
                                        <td>
                                            <span class="badge bg-teal text-white font-monospace fs-6">
                                                Bed {{ $adm->currentBed?->bed_number ?? 'N/A' }}
                                            </span>
                                            <div class="small text-muted-hms mt-1">
                                                {{ $adm->currentBed?->room_number ?? 'Ward Room' }} &bull; {{ $adm->department?->name }}
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('portal.admissions.show', $adm) }}" class="fw-bold font-monospace text-navy text-decoration-none">
                                                {{ $adm->admission_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-navy">{{ $adm->patient?->name }}</div>
                                            <div class="small text-muted-hms font-monospace">
                                                {{ $adm->patient?->patient_code }} &bull; {{ $adm->patient?->formatted_age }}, {{ ucfirst($adm->patient?->gender ?? '') }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold text-dark">{{ $adm->admission_date?->format('d M Y, h:i A') }}</div>
                                            <span class="badge bg-light text-dark border">{{ $adm->stay_duration }}</span>
                                        </td>
                                        <td>
                                            <div class="small text-truncate" style="max-width: 200px;">
                                                {{ $adm->admission_reason }}
                                            </div>
                                        </td>
                                        <td>
                                            @php $vitals = $adm->nursingNotes->first(); @endphp
                                            @if($vitals)
                                                <div class="small font-monospace">
                                                    <span class="text-danger fw-semibold">BP:</span> {{ $vitals->blood_pressure ?? '--' }} | 
                                                    <span class="text-primary fw-semibold">Pulse:</span> {{ $vitals->pulse_rate ?? '--' }} | 
                                                    <span class="text-teal fw-semibold">SpO2:</span> {{ $vitals->spo2 ? $vitals->spo2 . '%' : '--' }}
                                                </div>
                                                <span class="text-muted-hms small" style="font-size: 0.7rem;">Obs: {{ $vitals->recorded_at?->diffForHumans() }}</span>
                                            @else
                                                <span class="text-muted-hms small">No vitals logged today</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('portal.doctor.patient-history', $adm->patient_id) }}" class="btn btn-outline-secondary" title="View Patient Clinical Dossier">
                                                    <i class="bi bi-clock-history"></i> Dossier
                                                </a>
                                                <a href="{{ route('portal.admissions.show', $adm) }}" class="btn btn-outline-teal" title="View Inpatient Admission Chart">
                                                    <i class="bi bi-file-medical"></i> Chart
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted-hms">
                                            <i class="bi bi-hospital fs-2 text-muted d-block mb-2"></i>
                                            No admitted inpatients currently assigned to Dr. {{ $doctor->name }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. PENDING LAB REPORTS FOR REVIEW PANE -->
        <div class="tab-pane fade" id="labsPane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h5 class="fw-bold text-navy mb-0">Diagnostic Reports Awaiting Doctor Validation</h5>
                        <span class="text-muted-hms small">Per clinical protocol, test reports must be signed off by the ordering physician before unlocking for patient discharge or portal view</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted-hms small text-uppercase">
                                <tr>
                                    <th>Order #</th>
                                    <th>Patient</th>
                                    <th>Investigation Test</th>
                                    <th>Lab Source</th>
                                    <th>Findings Summary</th>
                                    <th>Status</th>
                                    <th class="text-end">Validation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingLabReports as $report)
                                    <tr>
                                        <td class="fw-bold font-monospace text-teal">
                                            {{ $report->orderItem?->labOrder?->order_number ?? 'ORD-N/A' }}
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-navy">{{ $report->orderItem?->labOrder?->patient?->name }}</div>
                                            <div class="small text-muted-hms font-monospace">
                                                {{ $report->orderItem?->labOrder?->patient?->patient_code }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $report->orderItem?->test_name }}</div>
                                            <span class="small text-muted-hms">{{ $report->orderItem?->labTest?->category ?? 'Pathology' }}</span>
                                        </td>
                                        <td>
                                            @if(($report->orderItem?->labOrder?->source ?? 'internal') === 'internal')
                                                <span class="badge bg-teal text-white">Internal Lab</span>
                                            @else
                                                <span class="badge bg-warning text-dark">External Partner</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small text-truncate" style="max-width: 250px;">
                                                {{ $report->findings_summary ?? 'Report uploaded; findings pending doctor sign-off.' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger text-white">Pending Review</span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-teal" onclick="openLabReviewModal('{{ $report->id }}', '{{ addslashes($report->orderItem?->test_name ?? 'Test') }}', '{{ addslashes($report->orderItem?->labOrder?->patient?->name ?? 'Patient') }}', '{{ addslashes($report->findings_summary ?? '') }}')">
                                                <i class="bi bi-clipboard-check me-1"></i> Review & Verify
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted-hms">
                                            <i class="bi bi-shield-check fs-2 text-success d-block mb-2"></i>
                                            All diagnostic test reports have been verified and signed off.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. TREATMENT FOLLOW-UPS PANE -->
        <div class="tab-pane fade" id="followupsPane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h5 class="fw-bold text-navy mb-0">Treatment Follow-up Schedule</h5>
                        <span class="text-muted-hms small">Patients advised to return for clinical follow-up evaluations and medication monitoring</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted-hms small text-uppercase">
                                <tr>
                                    <th>Target Follow-up Date</th>
                                    <th>Status</th>
                                    <th>Patient</th>
                                    <th>Primary Diagnosis</th>
                                    <th>Prescription Advice</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($treatmentFollowUps as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-bold font-monospace text-navy">
                                                {{ $item['follow_up_date']->format('d M, Y') }}
                                            </div>
                                            <div class="small text-muted-hms">{{ $item['follow_up_date']->format('l') }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $item['status_badge'] }} text-white">
                                                {{ $item['status_label'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-navy">{{ $item['patient']?->name }}</div>
                                            <div class="small text-muted-hms font-monospace">
                                                {{ $item['patient']?->patient_code }} &bull; {{ $item['patient']?->phone }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($item['diagnoses']->isNotEmpty())
                                                <span class="badge bg-light text-dark border">
                                                    {{ $item['diagnoses']->first()->condition_name }}
                                                </span>
                                            @else
                                                <span class="text-muted-hms small">Clinical review</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small text-truncate" style="max-width: 240px;">
                                                {{ $item['advice'] ?? 'Follow medication schedule and check vitals' }}
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            @if($item['patient'])
                                                <a href="{{ route('portal.doctor.patient-history', $item['patient']) }}" class="btn btn-sm btn-outline-teal">
                                                    <i class="bi bi-clock-history"></i> Dossier
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted-hms">
                                            <i class="bi bi-calendar-check fs-2 text-muted d-block mb-2"></i>
                                            No upcoming treatment follow-ups scheduled in the immediate timeframe.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. RECENT CONSULTATIONS PANE -->
        <div class="tab-pane fade" id="recentPane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h5 class="fw-bold text-navy mb-0">Recent Consultations Authored</h5>
                        <span class="text-muted-hms small">Recent electronic prescriptions and diagnoses finalized by Dr. {{ $doctor->name }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted-hms small text-uppercase">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Patient</th>
                                    <th>Chief Complaints</th>
                                    <th>Diagnoses</th>
                                    <th>Prescription Items</th>
                                    <th>Status</th>
                                    <th class="text-end">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentConsultations as $c)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-navy">{{ $c->created_at->format('d M Y') }}</div>
                                            <div class="small text-muted-hms">{{ $c->created_at->format('h:i A') }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-navy">{{ $c->visit?->patient?->name }}</div>
                                            <div class="small text-muted-hms font-monospace">
                                                {{ $c->visit?->patient?->patient_code }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small text-truncate" style="max-width: 220px;">
                                                {{ $c->chief_complaints }}
                                            </div>
                                        </td>
                                        <td>
                                            @foreach($c->diagnoses as $diag)
                                                <span class="badge bg-light text-dark border me-1">{{ $diag->condition_name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <span class="small fw-semibold">{{ $c->prescription?->items?->count() ?? 0 }} medications</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success text-white">{{ ucfirst($c->status) }}</span>
                                        </td>
                                        <td class="text-end">
                                            @if($c->visit?->patient_id)
                                                <a href="{{ route('portal.doctor.patient-history', $c->visit->patient_id) }}" class="btn btn-sm btn-outline-teal">
                                                    <i class="bi bi-clock-history"></i> Dossier
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted-hms">
                                            <i class="bi bi-journal fs-2 text-muted d-block mb-2"></i>
                                            No recent clinical consultations on record.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Assigned Active Patients Summary Section -->
    <div class="card border-0 shadow-sm rounded-3 mt-4">
        <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="fw-bold text-navy mb-0">Assigned Inpatients & OPD Cohort</h5>
                <span class="text-muted-hms small">Registered patients currently or recently receiving clinical care from Dr. {{ $doctor->name }}</span>
            </div>
            <span class="badge bg-teal text-white">{{ $assignedPatients->total() }} Patients Total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted-hms small text-uppercase">
                        <tr>
                            <th>Patient Code</th>
                            <th>Full Name</th>
                            <th>Age & Gender</th>
                            <th>Phone Contact</th>
                            <th>Current Clinical Status</th>
                            <th>Latest Visit</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignedPatients as $patient)
                            <tr>
                                <td class="fw-bold font-monospace text-teal">
                                    {{ $patient->patient_code }}
                                </td>
                                <td>
                                    <div class="fw-semibold text-navy">{{ $patient->name }}</div>
                                </td>
                                <td>
                                    {{ $patient->formatted_age }}, {{ ucfirst($patient->gender) }}
                                </td>
                                <td class="font-monospace">
                                    {{ $patient->phone }}
                                </td>
                                <td>
                                    @php $activeAdm = $patient->admissions->first(); @endphp
                                    @if($activeAdm)
                                        <span class="badge bg-teal text-white font-monospace">
                                            Inpatient: Bed {{ $activeAdm->currentBedAssignment?->bed?->bed_number ?? 'Ward' }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark border">Outpatient</span>
                                    @endif
                                </td>
                                <td>
                                    @php $lastVisit = $patient->visits->first(); @endphp
                                    @if($lastVisit)
                                        <span class="small font-monospace">{{ $lastVisit->visit_date?->format('d M Y') }}</span>
                                    @else
                                        <span class="text-muted-hms small">N/A</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('portal.doctor.patient-history', $patient) }}" class="btn btn-sm btn-outline-teal">
                                        <i class="bi bi-clock-history me-1"></i> Medical History
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted-hms">
                                    No patients currently assigned to Dr. {{ $doctor->name }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($assignedPatients->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $assignedPatients->links() }}
            </div>
        @endif
    </div>

    <!-- 6. MODAL: Diagnostic Lab Report Review & Verification -->
    <div class="modal fade" id="labReviewModal" tabindex="-1" aria-labelledby="labReviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <form id="labReviewForm" method="POST" action="">
                    @csrf
                    <div class="modal-header bg-navy text-white">
                        <h5 class="modal-title fw-bold" id="labReviewModalLabel">
                            <i class="bi bi-shield-check text-teal me-1"></i> Clinical Review & Report Sign-Off
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="p-3 bg-light rounded-3 mb-3">
                            <div class="small text-muted-hms">Patient & Investigation</div>
                            <div class="fw-bold text-navy fs-6" id="labModalTestDisplay">Test Name</div>
                            <div class="small text-muted font-monospace" id="labModalPatientDisplay">Patient</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-navy small">Report Findings Summary</label>
                            <textarea name="findings_summary" id="labModalFindings" class="form-control font-monospace" rows="3" placeholder="Pathological observations, critical alert values..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-navy small">Doctor's Clinical Notes / Recommendation</label>
                            <textarea name="clinical_notes" class="form-control" rows="3" placeholder="E.g., Findings verified clinically; prescribe Ceftriaxone 1g IV; repeat CBC in 48h..."></textarea>
                        </div>

                        <div class="alert alert-info py-2 px-3 small mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle-fill"></i>
                            <div>Signing off certifies review by <strong>Dr. {{ $doctor->name }}</strong>.</div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-teal fw-semibold">
                            <i class="bi bi-check-lg me-1"></i> Verify & Sign Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 7. MODAL: Quick Patient Dossier Lookup -->
    <div class="modal fade" id="quickPatientLookupModal" tabindex="-1" aria-labelledby="quickPatientLookupLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title fw-bold" id="quickPatientLookupLabel">
                        <i class="bi bi-search text-teal me-1"></i> Patient Clinical Dossier Lookup
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="input-group mb-3">
                        <input type="text" id="patientLiveSearchInput" class="form-control form-control-lg" placeholder="Type patient name, code (MCNH-2026-...), or phone number..." autofocus>
                        <button class="btn btn-teal px-4" type="button" onclick="executePatientSearch()">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                    <div id="patientSearchResultsContainer">
                        <div class="text-center py-4 text-muted-hms small">
                            <i class="bi bi-keyboard fs-3 text-muted d-block mb-2"></i>
                            Start typing above to search the hospital medical records database.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openLabReviewModal(reportId, testName, patientName, findings) {
            const form = document.getElementById('labReviewForm');
            form.action = `{{ url('/portal/doctor/lab-reports') }}/${reportId}/review`;

            document.getElementById('labModalTestDisplay').textContent = testName;
            document.getElementById('labModalPatientDisplay').textContent = `Patient: ${patientName}`;
            document.getElementById('labModalFindings').value = findings;

            new bootstrap.Modal(document.getElementById('labReviewModal')).show();
        }

        let searchDebounceTimeout;
        const liveSearchInput = document.getElementById('patientLiveSearchInput');
        if (liveSearchInput) {
            liveSearchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    executePatientSearch();
                    return;
                }
                clearTimeout(searchDebounceTimeout);
                searchDebounceTimeout = setTimeout(executePatientSearch, 400);
            });
        }

        function executePatientSearch() {
            const query = document.getElementById('patientLiveSearchInput').value.trim();
            const container = document.getElementById('patientSearchResultsContainer');

            if (query.length < 2) {
                container.innerHTML = '<div class="text-center py-4 text-muted-hms small"><i class="bi bi-info-circle me-1"></i> Please enter at least 2 characters.</div>';
                return;
            }

            container.innerHTML = '<div class="text-center py-4 text-muted-hms small"><div class="spinner-border spinner-border-sm text-teal me-2" role="status"></div>Searching clinical records...</div>';

            fetch(`{{ route('portal.doctor.search-patients') }}?q=${encodeURIComponent(query)}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.results || data.results.length === 0) {
                    container.innerHTML = '<div class="text-center py-4 text-muted-hms small"><i class="bi bi-exclamation-circle me-1"></i> No matching patients found.</div>';
                    return;
                }

                let html = '<div class="list-group list-group-flush">';
                data.results.forEach(p => {
                    const bedBadge = p.active_bed ? `<span class="badge bg-teal ms-2">Bed ${p.active_bed}</span>` : '';
                    html += `
                        <a href="${p.history_url}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 rounded mb-2 border">
                            <div>
                                <div class="fw-bold text-navy">${p.name} ${bedBadge}</div>
                                <div class="small text-muted font-monospace">${p.patient_code} &bull; ${p.age}, ${p.gender} &bull; Ph: ${p.phone}</div>
                            </div>
                            <span class="btn btn-sm btn-outline-teal">
                                <i class="bi bi-clock-history me-1"></i> Open Dossier &rarr;
                            </span>
                        </a>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            })
            .catch(err => {
                container.innerHTML = '<div class="text-center py-4 text-danger small"><i class="bi bi-exclamation-triangle me-1"></i> Error searching records. Please try again.</div>';
            });
        }
    </script>
    @endpush
</x-layouts.portal>
