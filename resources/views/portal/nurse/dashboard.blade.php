<x-layouts.portal role="nurse" title="Nurse Ward Station & Inpatient Care — Meghna Clinic & Nursing Home" activeNav="dashboard">
    <!-- 1. Nurse Profile & Duty Shift Header -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 58px; height: 58px; font-size: 1.6rem;">
                        <i class="bi bi-heart-pulse-fill"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <h4 class="fw-bold text-navy mb-0">{{ $nurse->name }}</h4>
                            <span class="badge bg-teal text-white px-2 py-1 font-monospace" style="font-size: 0.75rem;">
                                {{ $nurse->staffProfile?->employee_id ?? 'MCNH-NUR-2026' }}
                            </span>
                            <span class="badge bg-navy text-white px-2 py-1">
                                {{ $shiftInfo['shift_label'] }}
                            </span>
                            @if($nurse->staffProfile?->department)
                                <span class="badge bg-secondary text-white px-2 py-1">
                                    {{ $nurse->staffProfile->department->name }}
                                </span>
                            @endif
                        </div>
                        <p class="text-muted-hms small mb-0 mt-1">
                            <i class="bi bi-clock-history me-1 text-teal"></i> Current Shift: <span class="fw-bold text-dark">{{ $shiftInfo['hours'] }}</span> &bull; 
                            <i class="bi bi-calendar3 me-1 text-teal"></i> {{ $shiftInfo['date_formatted'] }} &bull;
                            <i class="bi bi-person-badge me-1 text-teal"></i> On-Duty Doctor: <span class="fw-semibold text-navy">{{ $shiftInfo['on_duty_doctor'] }}</span>
                        </p>
                    </div>
                </div>

                <!-- Filters & Action Toolbar -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <form method="GET" action="{{ route('portal.nurse') }}" class="d-flex flex-wrap align-items-center gap-2">
                        @if(auth()->user()->hasRole(['super_admin', 'administrator']) && $allNurses->count() > 1)
                            <select name="nurse_id" class="form-select form-select-sm border-teal-subtle" onchange="this.form.submit()" title="Switch Attending Nurse">
                                @foreach($allNurses as $n)
                                    <option value="{{ $n->id }}" @selected($n->id == $nurse->id)>
                                        {{ $n->name }} ({{ $n->staffProfile?->shift ?? 'Nurse' }})
                                    </option>
                                @endforeach
                            </select>
                        @endif

                        <select name="department_id" class="form-select form-select-sm border-teal-subtle" onchange="this.form.submit()" title="Filter by Department">
                            <option value="all">All Ward Departments</option>
                            @foreach($clinicalDepartments as $dept)
                                <option value="{{ $dept->id }}" @selected($selectedDeptId == $dept->id)>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>

                        <select name="shift" class="form-select form-select-sm border-teal-subtle" onchange="this.form.submit()" title="Filter by Shift">
                            <option value="">All Shifts</option>
                            <option value="morning" @selected($selectedShift === 'morning')>Morning (8 AM - 4 PM)</option>
                            <option value="evening" @selected($selectedShift === 'evening')>Evening (4 PM - 12 AM)</option>
                            <option value="night" @selected($selectedShift === 'night')>Night (12 AM - 8 AM)</option>
                        </select>

                        @if($selectedDeptId || $selectedShift || $searchTerm)
                            <a href="{{ route('portal.nurse') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        @endif
                    </form>

                    <button type="button" class="btn btn-sm btn-teal d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#recordVitalsModal">
                        <i class="bi bi-heart-pulse"></i>
                        <span>Record Vitals</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#recordMarModal">
                        <i class="bi bi-capsule"></i>
                        <span>Record MAR</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please correct the following errors:</div>
            <ul class="mb-0 small ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 3. Clear Alerts for Pending Tasks (Prompt Requirement) -->
    <div id="alerts-section" class="mb-4">
        <!-- Critical Abnormal Vitals Alert -->
        @if(!empty($pendingTasks['abnormal_vitals']))
            <div class="alert alert-danger border-0 shadow-sm mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="fw-bold fs-6 text-danger">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>
                        CRITICAL VITALS ALERT: {{ count($pendingTasks['abnormal_vitals']) }} Inpatient(s) Require Immediate Clinical Attention
                    </div>
                    <span class="badge bg-danger text-white">Escalate to On-Duty Doctor</span>
                </div>
                <div class="row g-2">
                    @foreach($pendingTasks['abnormal_vitals'] as $abAlert)
                        <div class="col-md-6 col-xl-4">
                            <div class="bg-white p-2 rounded border border-danger-subtle d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-danger-subtle text-danger fw-bold me-1">
                                        {{ $abAlert['bed']?->bed_number ?? 'Bed ?' }}
                                    </span>
                                    <span class="fw-bold text-dark">{{ $abAlert['patient']?->name }}</span>
                                    <div class="small text-danger mt-1">
                                        @if($abAlert['is_fever']) <span class="badge bg-warning text-dark me-1">Fever: {{ $abAlert['vital']?->temperature }}°F</span> @endif
                                        @if($abAlert['is_hypoxia']) <span class="badge bg-danger text-white me-1">Low SpO2: {{ $abAlert['vital']?->spo2 }}%</span> @endif
                                        @if($abAlert['is_tachycardia']) <span class="badge bg-danger-subtle text-danger me-1">HR: {{ $abAlert['vital']?->pulse_rate }} bpm</span> @endif
                                        @if($abAlert['is_bradycardia']) <span class="badge bg-secondary text-white me-1">Low HR: {{ $abAlert['vital']?->pulse_rate }} bpm</span> @endif
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="openVitalsModal({{ $abAlert['admission']->id }}, '{{ $abAlert['patient']?->name }}', '{{ $abAlert['bed']?->bed_number }}')">
                                    Re-check
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Pending Vitals Due Alert -->
        @if(!empty($pendingTasks['vitals_due']))
            <div class="alert alert-warning border-0 shadow-sm mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="fw-bold text-dark">
                        <i class="bi bi-clock-history me-2 text-warning"></i>
                        PENDING VITALS: {{ count($pendingTasks['vitals_due']) }} Inpatient(s) Overdue for Bedside Vitals Check (> 8h Shift Cycle)
                    </div>
                    <span class="badge bg-warning text-dark">{{ count($pendingTasks['vitals_due']) }} Patients Due</span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($pendingTasks['vitals_due'] as $due)
                        <span class="badge bg-white text-dark border p-2 d-inline-flex align-items-center gap-2">
                            <span class="fw-bold text-navy">{{ $due['bed']?->bed_number ?? 'Unassigned' }}</span>: {{ $due['patient']?->name }}
                            <small class="text-muted">({{ $due['last_checked'] }})</small>
                            <button type="button" class="btn btn-xs btn-teal py-0 px-1 text-white" 
                                onclick="openVitalsModal({{ $due['admission']->id }}, '{{ $due['patient']?->name }}', '{{ $due['bed']?->bed_number }}')">
                                Record
                            </button>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Beds Requiring Cleaning Alert -->
        @if($pendingTasks['cleaning_beds']->isNotEmpty())
            <div class="alert alert-info border-0 shadow-sm mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div class="fw-bold text-navy">
                        <i class="bi bi-stars me-2 text-teal"></i>
                        BED SANITIZATION REQUIRED: {{ $pendingTasks['cleaning_beds']->count() }} Bed(s) Awaiting Cleaning Sign-off Before Patient Allocation
                    </div>
                    <span class="badge bg-teal text-white">Housekeeping & Ward Check</span>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    @foreach($pendingTasks['cleaning_beds'] as $cBed)
                        <div class="badge bg-white text-dark border p-2 d-inline-flex align-items-center gap-2">
                            <span class="fw-bold text-teal">{{ $cBed->bed_number }}</span>
                            <span>({{ $cBed->department?->name ?? 'Ward' }})</span>
                            <form method="POST" action="{{ route('portal.nurse.beds.update-status', $cBed) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="available">
                                <input type="hidden" name="notes" value="Disinfected & sanitized by shift sister">
                                <button type="submit" class="btn btn-xs btn-success py-0 px-2 text-white" onclick="return confirm('Confirm Bed {{ $cBed->bed_number }} is thoroughly sanitized and ready for patient admission?')">
                                    <i class="bi bi-check2"></i> Mark Ready
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- 4. Bento Grid Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Active Inpatients" 
                value="{{ $metrics['total_inpatients'] }} Patients" 
                icon="people-fill" 
                color="primary"
                trend="Admitted Inpatients"
                trendUp="true"
                subtitle="Currently under ward care"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Ward Bed Occupancy" 
                value="{{ $metrics['occupied_beds'] }} / {{ $metrics['total_beds'] }} Beds" 
                icon="door-open" 
                color="teal"
                trend="{{ $metrics['occupancy_percentage'] }}% Occupancy"
                trendUp="true"
                subtitle="{{ $metrics['available_beds'] }} available &bull; {{ $metrics['cleaning_beds'] }} cleaning"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Vitals Checks Due" 
                value="{{ $metrics['vitals_due_count'] }} Due" 
                icon="heart-pulse" 
                color="warning"
                trend="{{ $metrics['critical_alerts_count'] }} critical alerts"
                trendUp="false"
                subtitle="8-hour vital monitoring cycle"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Today's MAR Administrations" 
                value="{{ $metrics['today_mar_doses'] }} Doses Given" 
                icon="capsule" 
                color="success"
                trend="{{ $shiftInfo['shift_type'] }} shift running"
                trendUp="true"
                subtitle="Doctor-approved prescriptions"
            />
        </div>
    </div>

    <!-- 5. 10-Bed Inpatient Ward Bedside Monitor (Prompt Requirement) -->
    <div id="ward-beds-section" class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-header bg-white py-3 border-0">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold text-navy mb-0">
                        <i class="bi bi-hospital text-teal me-2"></i>10-Bed Inpatient Ward — Live Bedside Monitor
                    </h5>
                    <small class="text-muted-hms">Real-time bed occupancy, patient assignments, and bedside clinical vitals</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        <i class="bi bi-circle-fill small me-1"></i> Available ({{ $metrics['available_beds'] }})
                    </span>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                        <i class="bi bi-circle-fill small me-1"></i> Occupied ({{ $metrics['occupied_beds'] }})
                    </span>
                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                        <i class="bi bi-circle-fill small me-1"></i> Cleaning ({{ $metrics['cleaning_beds'] }})
                    </span>
                    <a href="{{ route('portal.beds.occupancy') }}" class="btn btn-sm btn-outline-teal">
                        <i class="bi bi-arrows-fullscreen me-1"></i> Full Bed Grid
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-4 pt-0">
            <div class="row g-3">
                @foreach($wardBeds as $bed)
                    @php
                        $isOccupied = $bed->status === 'occupied' && $bed->currentAssignment;
                        $assignment = $bed->currentAssignment;
                        $admission = $assignment?->admission;
                        $patient = $admission?->patient;
                        $latestVital = $admission?->nursingNotes?->first();
                    @endphp
                    <div class="col-md-6 col-xl-4 col-xxl-3">
                        <div class="h-100 p-3 rounded-3 border {{ $isOccupied ? 'bg-white shadow-sm border-teal-subtle' : ($bed->status === 'cleaning' ? 'bg-info-subtle border-info' : 'bg-light border-dashed') }}">
                            <!-- Bed Card Top Bar -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge {{ $isOccupied ? 'bg-navy' : ($bed->status === 'cleaning' ? 'bg-info text-white' : 'bg-secondary') }} px-2 py-1 font-monospace">
                                        {{ $bed->bed_number }}
                                    </span>
                                    <span class="fw-bold text-navy small">
                                        {{ $bed->department?->name ?? 'General Ward' }}
                                    </span>
                                </div>
                                <span class="badge bg-{{ $bed->status_badge_class }}">
                                    {{ ucfirst($bed->status) }}
                                </span>
                            </div>

                            <!-- Bed Card Body -->
                            @if($isOccupied && $patient && $admission)
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="fw-bold text-navy mb-0 text-truncate" title="{{ $patient->name }}">
                                                {{ $patient->name }}
                                            </p>
                                            <small class="text-muted-hms font-monospace" style="font-size: 0.75rem;">
                                                {{ $patient->patient_code }} &bull; {{ $patient->formatted_age }}, {{ ucfirst($patient->gender) }}
                                            </small>
                                        </div>
                                        <span class="badge bg-secondary-subtle text-dark" style="font-size: 0.7rem;">
                                            {{ $admission->stay_duration }}
                                        </span>
                                    </div>
                                    <p class="small text-muted-hms mb-1 mt-1 text-truncate" title="{{ $admission->admission_reason ?? 'General Inpatient Care' }}">
                                        <i class="bi bi-activity text-teal me-1"></i>{{ $admission->admission_reason ?? 'Inpatient Care' }}
                                    </p>
                                    <p class="small text-muted-hms mb-2" style="font-size: 0.75rem;">
                                        <i class="bi bi-person-badge text-navy me-1"></i>Dr. {{ $admission->attendingDoctor?->name ?? 'Assigned Doctor' }}
                                    </p>

                                    <!-- Vitals Snapshot -->
                                    <div class="p-2 rounded bg-light mb-2" style="font-size: 0.75rem;">
                                        @if($latestVital)
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted-hms">Latest Vitals:</span>
                                                <span class="text-muted-hms">{{ $latestVital->recorded_at->diffForHumans() }}</span>
                                            </div>
                                            <div class="fw-semibold {{ $latestVital->hasAbnormalVitals() ? 'text-danger' : 'text-teal' }} mt-1">
                                                {{ $latestVital->formatted_vitals }}
                                            </div>
                                        @else
                                            <span class="text-warning fw-semibold">
                                                <i class="bi bi-clock me-1"></i> Vitals Check Due
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-teal flex-grow-1 py-1" style="font-size: 0.75rem;"
                                            onclick="openVitalsModal({{ $admission->id }}, '{{ $patient->name }}', '{{ $bed->bed_number }}')">
                                            <i class="bi bi-heart-pulse"></i> Vitals
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary flex-grow-1 py-1" style="font-size: 0.75rem;"
                                            onclick="openMarModal({{ $admission->id }}, '{{ $patient->name }}', '{{ $bed->bed_number }}')">
                                            <i class="bi bi-capsule"></i> MAR
                                        </button>
                                        <a href="{{ route('portal.nurse.admissions.dossier', $admission) }}" class="btn btn-sm btn-outline-secondary py-1" style="font-size: 0.75rem;" title="View Complete Nursing Dossier">
                                            <i class="bi bi-journal-text"></i>
                                        </a>
                                    </div>
                                </div>
                            @elseif($bed->status === 'cleaning')
                                <div class="py-3 text-center">
                                    <i class="bi bi-stars text-info fs-3 d-block mb-1"></i>
                                    <p class="small text-navy fw-semibold mb-2">Sanitization in Progress</p>
                                    <form method="POST" action="{{ route('portal.nurse.beds.update-status', $bed) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="available">
                                        <input type="hidden" name="notes" value="Disinfected and verified ready by Sister {{ $nurse->name }}">
                                        <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Confirm Bed {{ $bed->bed_number }} is completely sanitized and ready?')">
                                            <i class="bi bi-check2-circle me-1"></i> Mark Available
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="py-3 text-center">
                                    <i class="bi bi-door-open text-success fs-3 d-block mb-1"></i>
                                    <p class="small text-muted-hms mb-2">{{ $bed->category_label }} &bull; Ready for Patient</p>
                                    <form method="POST" action="{{ route('portal.nurse.beds.update-status', $bed) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="cleaning">
                                        <input type="hidden" name="notes" value="Scheduled for routine sanitization">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary w-100 py-1" style="font-size: 0.75rem;">
                                            <i class="bi bi-brush me-1"></i> Request Cleaning
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 6. Assigned Patients & Active Admissions Registry -->
    <div id="inpatients-section" class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-header bg-white py-3 border-0">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div>
                    <h5 class="fw-bold text-navy mb-0">
                        <i class="bi bi-people-fill text-teal me-2"></i>Assigned Inpatients & Active Admissions ({{ $activeAdmissions->count() }})
                    </h5>
                    <small class="text-muted-hms">Ward inpatients currently admitted under clinical and nursing care</small>
                </div>
                <!-- Search Box -->
                <div style="min-width: 280px;">
                    <form method="GET" action="{{ route('portal.nurse') }}">
                        @if($selectedDeptId) <input type="hidden" name="department_id" value="{{ $selectedDeptId }}"> @endif
                        @if($selectedShift) <input type="hidden" name="shift" value="{{ $selectedShift }}"> @endif
                        <div class="input-group input-group-sm">
                            <input type="text" name="q" class="form-control" placeholder="Search patient, code, bed #..." value="{{ $searchTerm }}">
                            <button class="btn btn-teal text-white" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                            @if($searchTerm)
                                <a href="{{ route('portal.nurse', ['department_id' => $selectedDeptId, 'shift' => $selectedShift]) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-navy small">
                        <tr>
                            <th class="ps-4">Admission #</th>
                            <th>Inpatient Name</th>
                            <th>Bed & Dept</th>
                            <th>Attending Doctor</th>
                            <th>Latest Vitals Snapshot</th>
                            <th>Medication (MAR)</th>
                            <th class="text-end pe-4">Clinical Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeAdmissions as $adm)
                            @php
                                $pt = $adm->patient;
                                $bed = $adm->currentBed;
                                $latestVital = $adm->nursingNotes->first();
                                $hasAbnormal = $latestVital?->hasAbnormalVitals();
                                $marCount = $adm->medicationAdministrations->count();
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold font-monospace text-navy" style="font-size: 0.85rem;">
                                        {{ $adm->admission_number }}
                                    </span>
                                    <div class="small text-muted-hms">{{ $adm->admission_date?->format('d M Y, h:i A') }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-navy">{{ $pt?->name }}</div>
                                    <small class="text-muted-hms font-monospace">
                                        {{ $pt?->patient_code }} &bull; {{ $pt?->formatted_age }}, {{ ucfirst($pt?->gender ?? '') }}
                                        @if($pt?->blood_group) <span class="badge bg-secondary-subtle text-dark ms-1">{{ $pt->blood_group }}</span> @endif
                                    </small>
                                </td>
                                <td>
                                    @if($bed)
                                        <span class="badge bg-navy px-2 py-1 font-monospace">
                                            {{ $bed->bed_number }}
                                        </span>
                                        <div class="small text-muted-hms">{{ $adm->department?->name ?? 'Ward' }}</div>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending Bed</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">Dr. {{ $adm->attendingDoctor?->name ?? 'Unassigned' }}</div>
                                    <small class="text-muted-hms text-truncate d-inline-block" style="max-width: 180px;" title="{{ $adm->admission_reason }}">
                                        {{ $adm->admission_reason ?? 'Inpatient care' }}
                                    </small>
                                </td>
                                <td>
                                    @if($latestVital)
                                        <div class="small {{ $hasAbnormal ? 'text-danger fw-bold' : 'text-teal fw-medium' }}">
                                            @if($hasAbnormal) <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> @endif
                                            {{ $latestVital->formatted_vitals }}
                                        </div>
                                        <small class="text-muted-hms">
                                            By Sister {{ $latestVital->nurse?->name }} &bull; {{ $latestVital->recorded_at->diffForHumans() }}
                                        </small>
                                    @else
                                        <span class="badge bg-warning-subtle text-dark">
                                            <i class="bi bi-clock me-1"></i> Due for Check
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $rx = $adm->visit?->consultation?->prescription;
                                    @endphp
                                    @if($rx && $rx->status === 'approved')
                                        <span class="badge bg-teal-subtle text-teal border border-teal-subtle">
                                            {{ $rx->items->count() }} Prescribed Meds
                                        </span>
                                        <div class="small text-muted-hms mt-1">{{ $marCount }} doses administered</div>
                                    @else
                                        <span class="text-muted small">No active Rx</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-teal" 
                                            onclick="openVitalsModal({{ $adm->id }}, '{{ $pt?->name }}', '{{ $bed?->bed_number }}')"
                                            title="Record Bedside Vitals">
                                            <i class="bi bi-heart-pulse"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" 
                                            onclick="openMarModal({{ $adm->id }}, '{{ $pt?->name }}', '{{ $bed?->bed_number }}')"
                                            title="Record Medication Administration">
                                            <i class="bi bi-capsule"></i>
                                        </button>
                                        <a href="{{ route('portal.nurse.admissions.trends', $adm) }}" class="btn btn-outline-teal" title="View Vital Trends">
                                            <i class="bi bi-graph-up"></i>
                                        </a>
                                        <a href="{{ route('portal.nurse.admissions.dossier', $adm) }}" class="btn btn-outline-secondary" title="View Patient Nursing Dossier">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-1 text-muted"></i>
                                    No active inpatients found matching the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 7. Two Column Bento: Bedside Vitals Ledger & MAR Timeline -->
    <div class="row g-4 mb-4">
        <!-- Vitals & Observations Ledger -->
        <div class="col-lg-6" id="vitals-section">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold text-navy mb-0">
                            <i class="bi bi-heart-pulse text-teal me-2"></i>Bedside Vitals & Nursing Observations
                        </h6>
                        <small class="text-muted-hms">Recent vital checks recorded across ward stations</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-teal" data-bs-toggle="modal" data-bs-target="#recordVitalsModal">
                        <i class="bi bi-plus-circle me-1"></i> New
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light text-navy">
                                <tr>
                                    <th class="ps-3">Time & Shift</th>
                                    <th>Patient & Bed</th>
                                    <th>Parameters</th>
                                    <th>Observations</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentVitals as $vt)
                                    @php
                                        $abnormal = $vt->hasAbnormalVitals();
                                    @endphp
                                    <tr class="{{ $abnormal ? 'table-danger-subtle' : '' }}">
                                        <td class="ps-3">
                                            <span class="fw-bold">{{ $vt->recorded_at->format('h:i A') }}</span>
                                            <div class="text-muted-hms small">{{ ucfirst($vt->shift) }} &bull; {{ $vt->recorded_at->format('d M') }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-navy">{{ $vt->admission?->patient?->name }}</div>
                                            <span class="badge bg-navy px-1 py-0 font-monospace">
                                                {{ $vt->admission?->currentBed?->bed_number ?? 'Bed' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="{{ $abnormal ? 'text-danger fw-bold' : 'text-teal' }}">
                                                {{ $vt->formatted_vitals }}
                                            </div>
                                            @if($abnormal)
                                                <span class="badge bg-danger text-white mt-1" style="font-size: 0.65rem;">
                                                    Abnormal Reading
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-dark small text-truncate" style="max-width: 180px;" title="{{ $vt->observations }}">
                                                {{ $vt->observations }}
                                            </div>
                                            <small class="text-muted">By Sister {{ $vt->nurse?->name }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No bedside vitals recorded for this shift yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAR Medication Administration Ledger -->
        <div class="col-lg-6" id="mar-section">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold text-navy mb-0">
                            <i class="bi bi-capsule text-primary me-2"></i>Medication Administration Records (eMAR)
                        </h6>
                        <small class="text-muted-hms">Doctor-prescribed dose delivery and omission log</small>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <a href="{{ route('portal.nurse.mar') }}" class="btn btn-sm btn-outline-secondary" title="View Full eMAR Ledger">
                            <i class="bi bi-list-ul me-1"></i> Ledger
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#recordMarModal">
                            <i class="bi bi-plus-circle me-1"></i> New Dose
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light text-navy">
                                <tr>
                                    <th class="ps-3">Delivered Time</th>
                                    <th>Patient & Bed</th>
                                    <th>Medication & Dose</th>
                                    <th>Status & Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentMedicationAdministrations as $mar)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="fw-bold">{{ $mar->administered_at->format('h:i A') }}</span>
                                            <div class="text-muted-hms small">{{ $mar->administered_at->format('d M Y') }}</div>
                                            <span class="badge bg-light text-secondary border mt-1" style="font-size: 0.65rem;">
                                                {{ ucfirst($mar->scheduled_shift ?? 'Shift') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-navy">{{ $mar->admission?->patient?->name }}</div>
                                            <span class="badge bg-navy px-1 py-0 font-monospace">
                                                {{ $mar->admission?->currentBed?->bed_number ?? 'Bed' }}
                                            </span>
                                            @if($mar->admission_id)
                                                <a href="{{ route('portal.nurse.admissions.mar-flowsheet', $mar->admission_id) }}" class="d-block small text-teal text-decoration-none mt-1" style="font-size: 0.7rem;">
                                                    <i class="bi bi-calendar2-range me-1"></i>Flowsheet
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $mar->medicine_name }}</div>
                                            <span class="badge bg-secondary-subtle text-dark">{{ $mar->dose_given }}</span>
                                            <span class="badge bg-light text-dark border" style="font-size: 0.65rem;">{{ $mar->route ?? 'Oral' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $mar->status_badge_class }}">
                                                {{ $mar->status_label }}
                                            </span>
                                            @if($mar->status === 'withheld' && $mar->withheld_reason)
                                                <div class="text-warning small text-truncate mt-1" style="max-width: 160px;" title="Hold: {{ $mar->withheld_reason }}">
                                                    <strong>Hold:</strong> {{ $mar->withheld_reason }}
                                                </div>
                                            @endif
                                            @if($mar->remarks)
                                                <div class="text-muted small text-truncate mt-1" style="max-width: 160px;" title="{{ $mar->remarks }}">
                                                    {{ $mar->remarks }}
                                                </div>
                                            @endif
                                            <div class="text-muted" style="font-size: 0.7rem;">Sister {{ $mar->nurse?->name }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No medication administration records entered today.
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

    <!-- 8. Shift Information & Duty Team Roster (Prompt Requirement) -->
    <div id="shift-section" class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold text-navy mb-0">
                <i class="bi bi-journal-medical text-teal me-2"></i>Nursing Shift Handover & Clinical Duty Roster
            </h6>
            <small class="text-muted-hms">8-hour shift allocation, peer team members, and emergency medical escalation</small>
        </div>
        <div class="card-body p-4 pt-0">
            <div class="row g-3">
                <!-- Shift Information Widget -->
                <div class="col-md-4">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="rounded-circle bg-teal text-white p-2">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-navy mb-0">Active Shift Duty</h6>
                                <small class="text-teal fw-semibold">{{ $shiftInfo['shift_label'] }}</small>
                            </div>
                        </div>
                        <ul class="list-unstyled small mb-0 mt-3 space-y-1">
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Shift Window:</span>
                                <span class="fw-bold text-dark">{{ $shiftInfo['hours'] }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Date:</span>
                                <span class="fw-medium text-dark">{{ $shiftInfo['date_formatted'] }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Attending Sister:</span>
                                <span class="fw-bold text-teal">{{ $nurse->name }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-1">
                                <span class="text-muted">Assigned Department:</span>
                                <span class="fw-medium text-navy">{{ $nurse->staffProfile?->department?->name ?? '10-Bed Inpatient Ward' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- On-Duty Emergency Team Widget -->
                <div class="col-md-4">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="rounded-circle bg-danger text-white p-2">
                                <i class="bi bi-telephone-plus-fill"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-navy mb-0">Emergency Medical Escalation</h6>
                                <small class="text-danger fw-semibold">24/7 Rapid Response</small>
                            </div>
                        </div>
                        <p class="small text-muted-hms mb-2 mt-3">
                            In case of sudden clinical deterioration (hypoxia, cardiac arrest, post-op hemorrhage):
                        </p>
                        <div class="p-2 rounded bg-white border border-danger-subtle mb-2">
                            <div class="fw-bold text-navy small">Emergency Attending Doctor:</div>
                            <div class="text-danger fw-semibold">{{ $shiftInfo['on_duty_doctor'] }}</div>
                            <small class="text-muted">Duty Room: Emergency Triage / Consultation Chamber 1</small>
                        </div>
                        <div class="small text-muted">
                            <i class="bi bi-shield-check text-success me-1"></i> Direct clinical escalation enabled.
                        </div>
                    </div>
                </div>

                <!-- Peer Nursing Staff on Shift -->
                <div class="col-md-4">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="rounded-circle bg-navy text-white p-2">
                                <i class="bi bi-people"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-navy mb-0">Peer Nursing Team</h6>
                                <small class="text-muted-hms">Shift Handover Colleagues</small>
                            </div>
                        </div>
                        <ul class="list-unstyled small mb-0 mt-3">
                            @forelse($shiftInfo['peer_nurses'] as $peer)
                                <li class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                    <span class="fw-medium text-dark">Sister {{ $peer->name }}</span>
                                    <span class="badge bg-secondary-subtle text-dark font-monospace" style="font-size: 0.7rem;">
                                        {{ ucfirst($peer->staffProfile?->shift ?? 'rotational') }}
                                    </span>
                                </li>
                            @empty
                                <li class="text-muted py-2">No other shift peers registered for today.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 1: Record Bedside Vital Signs -->
    <div class="modal fade" id="recordVitalsModal" tabindex="-1" aria-labelledby="recordVitalsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <form id="vitalsForm" method="POST" action="">
                    @csrf
                    <div class="modal-header bg-teal text-white">
                        <h5 class="modal-title fw-bold" id="recordVitalsModalLabel">
                            <i class="bi bi-heart-pulse-fill me-2"></i>Record Bedside Patient Vital Signs & Observations
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Patient Selection Alert -->
                        <div class="mb-3">
                            <label for="vitalsAdmissionSelect" class="form-label fw-bold text-navy">Select Admitted Patient / Bed <span class="text-danger">*</span></label>
                            <select id="vitalsAdmissionSelect" class="form-select border-teal-subtle" required onchange="updateVitalsFormAction(this.value)">
                                <option value="">-- Choose active inpatient --</option>
                                @foreach($activeAdmissions as $adm)
                                    <option value="{{ $adm->id }}">
                                        {{ $adm->currentBed?->bed_number ?? 'Bed ?' }} — {{ $adm->patient?->name }} ({{ $adm->admission_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="vitalsShift" class="form-label fw-semibold text-dark">Nursing Shift <span class="text-danger">*</span></label>
                                <select name="shift" id="vitalsShift" class="form-select" required>
                                    <option value="morning" @selected($shiftInfo['shift_type'] === 'morning')>Morning Shift (8 AM – 4 PM)</option>
                                    <option value="evening" @selected($shiftInfo['shift_type'] === 'evening')>Evening Shift (4 PM – 12 AM)</option>
                                    <option value="night" @selected($shiftInfo['shift_type'] === 'night')>Night Shift (12 AM – 8 AM)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="vitalsTemp" class="form-label fw-semibold text-dark">Temperature (°F)</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" name="temperature" id="vitalsTemp" class="form-control" placeholder="e.g. 98.4" min="90.0" max="110.0">
                                    <span class="input-group-text">°F</span>
                                </div>
                                <small class="text-muted">Normal: 97.0°F - 99.0°F (≥ 100.4 Fever)</small>
                            </div>
                            <div class="col-md-4">
                                <label for="vitalsBP" class="form-label fw-semibold text-dark">Blood Pressure (mmHg)</label>
                                <input type="text" name="blood_pressure" id="vitalsBP" class="form-control" placeholder="e.g. 120/80" pattern="\d{2,3}/\d{2,3}">
                                <small class="text-muted">Format: Systolic/Diastolic</small>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="vitalsPulse" class="form-label fw-semibold text-dark">Pulse Rate (Heart Rate)</label>
                                <div class="input-group">
                                    <input type="number" name="pulse_rate" id="vitalsPulse" class="form-control" placeholder="e.g. 78" min="30" max="250">
                                    <span class="input-group-text">BPM</span>
                                </div>
                                <small class="text-muted">Normal: 60 - 100 BPM</small>
                            </div>
                            <div class="col-md-4">
                                <label for="vitalsSpo2" class="form-label fw-semibold text-dark">SpO2 (Oxygen Saturation)</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" name="spo2" id="vitalsSpo2" class="form-control" placeholder="e.g. 98.5" min="50.0" max="100.0">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Normal: 95% - 100% (&lt; 95% Hypoxia)</small>
                            </div>
                            <div class="col-md-4">
                                <label for="vitalsRR" class="form-label fw-semibold text-dark">Respiratory Rate</label>
                                <div class="input-group">
                                    <input type="number" name="respiratory_rate" id="vitalsRR" class="form-control" placeholder="e.g. 18" min="5" max="60">
                                    <span class="input-group-text">/min</span>
                                </div>
                                <small class="text-muted">Normal: 12 - 20 /min</small>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="vitalsWeight" class="form-label fw-semibold text-dark">Patient Body Weight (Where appropriate)</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" name="weight" id="vitalsWeight" class="form-control" placeholder="e.g. 68.5" min="1.0" max="300.0">
                                    <select name="weight_unit" class="form-select" style="max-width: 80px;">
                                        <option value="kg">kg</option>
                                        <option value="lbs">lbs</option>
                                    </select>
                                </div>
                                <small class="text-muted">Clinical tracking / fluid balance</small>
                            </div>
                            <div class="col-md-6">
                                <label for="vitalsTime" class="form-label fw-semibold text-dark">Observation Date & Time</label>
                                <input type="datetime-local" name="recorded_at" id="vitalsTime" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                                <small class="text-muted">Observation timestamp</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="vitalsObs" class="form-label fw-bold text-navy">Bedside Clinical Observations & Condition Notes <span class="text-danger">*</span></label>
                            <textarea name="observations" id="vitalsObs" rows="3" class="form-control" required placeholder="Patient general appearance, pain level, consciousness, surgical wound site condition, fluid intake/output..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-teal text-white">
                            <i class="bi bi-save me-1"></i> Save Bedside Vitals
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Record Medication Administration (MAR) -->
    <div class="modal fade" id="recordMarModal" tabindex="-1" aria-labelledby="recordMarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="marForm" method="POST" action="">
                    @csrf
                    <div class="modal-header bg-navy text-white">
                        <h5 class="modal-title fw-bold" id="recordMarModalLabel">
                            <i class="bi bi-capsule me-2"></i>Record Medication Administration (MAR)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="marAdmissionSelect" class="form-label fw-bold text-navy">Select Admitted Inpatient <span class="text-danger">*</span></label>
                            <select id="marAdmissionSelect" class="form-select border-teal-subtle" required onchange="updateMarFormAction(this.value)">
                                <option value="">-- Choose active inpatient --</option>
                                @foreach($activeAdmissions as $adm)
                                    <option value="{{ $adm->id }}">
                                        {{ $adm->currentBed?->bed_number ?? 'Bed ?' }} — {{ $adm->patient?->name }} ({{ $adm->admission_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="marMedicineName" class="form-label fw-semibold text-dark">Medicine Name <span class="text-danger">*</span></label>
                            <input type="text" name="medicine_name" id="marMedicineName" class="form-control" placeholder="e.g. Injection Ceftriaxone, Tab Paracetamol" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="marDose" class="form-label fw-semibold text-dark">Dose Administered <span class="text-danger">*</span></label>
                                <input type="text" name="dose_given" id="marDose" class="form-control" placeholder="e.g. 1g IV, 500mg PO" required>
                            </div>
                            <div class="col-6">
                                <label for="marRoute" class="form-label fw-semibold text-dark">Route</label>
                                <select name="route" id="marRoute" class="form-select">
                                    <option value="Oral" selected>Oral</option>
                                    <option value="IV Push">IV Push</option>
                                    <option value="IV Infusion">IV Infusion</option>
                                    <option value="IM">IM</option>
                                    <option value="SC">SC</option>
                                    <option value="Inhalation">Inhalation (Nebulizer)</option>
                                    <option value="Topical">Topical</option>
                                    <option value="Sublingual">Sublingual</option>
                                    <option value="Rectal">Rectal</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="marShift" class="form-label fw-semibold text-dark">Shift</label>
                                <select name="scheduled_shift" id="marShift" class="form-select">
                                    <option value="morning">Morning (8 AM – 4 PM)</option>
                                    <option value="evening">Evening (4 PM – 12 AM)</option>
                                    <option value="night">Night (12 AM – 8 AM)</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="marStatus" class="form-label fw-semibold text-dark">Administration Status <span class="text-danger">*</span></label>
                                <select name="status" id="marStatus" class="form-select" required onchange="toggleMarRemarks(this.value)">
                                    <option value="administered" selected>Administered (Given)</option>
                                    <option value="withheld">Withheld (Clinical Hold)</option>
                                    <option value="missed">Missed Dose</option>
                                    <option value="refused">Refused by Patient</option>
                                    <option value="omitted">Omitted (Skipped)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3 d-none" id="marWithheldContainer">
                            <label for="marWithheldReason" class="form-label fw-semibold text-dark">Withheld Reason <span class="text-danger">*</span></label>
                            <input type="text" name="withheld_reason" id="marWithheldReason" class="form-control" placeholder="Required: Clinical reason for hold (e.g. Hypotension, NPO)...">
                        </div>

                        <div class="mb-3" id="marRemarksContainer">
                            <label for="marRemarks" class="form-label fw-semibold text-dark">Remarks / Bedside Notes</label>
                            <textarea name="remarks" id="marRemarks" rows="2" class="form-control" placeholder="Optional for given meds. Mandatory if missed, omitted, or refused..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle me-1"></i> Confirm MAR Entry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Handlers for Modal Form Targets -->
    <script>
        function openVitalsModal(admissionId, patientName, bedNumber) {
            const select = document.getElementById('vitalsAdmissionSelect');
            if (select) {
                select.value = admissionId;
            }
            updateVitalsFormAction(admissionId);
            const modal = new bootstrap.Modal(document.getElementById('recordVitalsModal'));
            modal.show();
        }

        function updateVitalsFormAction(admissionId) {
            const form = document.getElementById('vitalsForm');
            if (form && admissionId) {
                form.action = `/portal/nurse/admissions/${admissionId}/vitals`;
            }
        }

        function openMarModal(admissionId, patientName, bedNumber) {
            const select = document.getElementById('marAdmissionSelect');
            if (select) {
                select.value = admissionId;
            }
            updateMarFormAction(admissionId);
            const modal = new bootstrap.Modal(document.getElementById('recordMarModal'));
            modal.show();
        }

        function updateMarFormAction(admissionId) {
            const form = document.getElementById('marForm');
            if (form && admissionId) {
                form.action = `/portal/nurse/admissions/${admissionId}/medications`;
            }
        }

        function toggleMarRemarks(status) {
            const remarks = document.getElementById('marRemarks');
            const withheldContainer = document.getElementById('marWithheldContainer');
            const withheldInput = document.getElementById('marWithheldReason');

            if (status === 'withheld') {
                withheldContainer.classList.remove('d-none');
                withheldInput.required = true;
            } else {
                withheldContainer.classList.add('d-none');
                withheldInput.required = false;
            }

            if (status === 'missed' || status === 'omitted' || status === 'refused') {
                remarks.required = true;
                remarks.placeholder = "Required: Clinical justification or patient reason for missed/omitted/refused dose...";
            } else {
                remarks.required = false;
                remarks.placeholder = "Optional observations, patient tolerance, or IV line status...";
            }
        }
    </script>
</x-layouts.portal>

