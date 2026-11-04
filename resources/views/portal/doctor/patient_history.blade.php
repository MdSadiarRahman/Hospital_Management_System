<x-layouts.portal role="doctor" title="Patient Clinical Dossier — {{ $patient->name }}" activeNav="dashboard">
    <!-- Breadcrumb & Back Action -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <a href="{{ route('portal.doctor') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i>
            <span>Back to Clinical Workstation</span>
        </a>
        <span class="badge bg-light text-navy border font-monospace">
            Medical Record #{{ $patient->patient_code }}
        </span>
    </div>

    <!-- Patient Clinical Header Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-navy text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 58px; height: 58px; font-size: 1.6rem;">
                        <i class="bi bi-person-heart"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <h4 class="fw-bold text-navy mb-0">{{ $patient->name }}</h4>
                            <span class="badge bg-teal text-white font-monospace">{{ $patient->patient_code }}</span>
                            @if($patient->blood_group)
                                <span class="badge bg-danger text-white">Blood: {{ $patient->blood_group }}</span>
                            @endif
                        </div>
                        <p class="text-muted-hms small mb-0 mt-1">
                            <i class="bi bi-calendar-event me-1 text-teal"></i> Age: <span class="fw-semibold text-dark">{{ $patient->formatted_age }}</span> &bull; 
                            <i class="bi bi-gender-ambiguous me-1 text-teal"></i> Gender: <span class="fw-semibold text-dark">{{ ucfirst($patient->gender) }}</span> &bull; 
                            <i class="bi bi-telephone me-1 text-teal"></i> Phone: <span class="fw-semibold text-dark font-monospace">{{ $patient->phone }}</span>
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if($patient->allergies)
                        <div class="p-2 rounded bg-warning bg-opacity-10 border border-warning-subtle text-warning-emphasis small">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Allergies: <strong>{{ $patient->allergies }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Clinical History Nav Tabs -->
    <ul class="nav nav-pills gap-2 mb-4 p-2 bg-white rounded-3 shadow-sm" id="patientHistoryTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold text-navy d-flex align-items-center gap-2" id="consultations-tab" data-bs-toggle="pill" data-bs-target="#consultationsHistoryPane" type="button" role="tab">
                <i class="bi bi-journal-medical text-teal"></i>
                <span>Consultations & Prescriptions</span>
                <span class="badge bg-teal text-white">{{ $visits->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-navy d-flex align-items-center gap-2" id="admissions-tab" data-bs-toggle="pill" data-bs-target="#admissionsHistoryPane" type="button" role="tab">
                <i class="bi bi-hospital text-teal"></i>
                <span>Inpatient Bed Stays</span>
                <span class="badge bg-primary text-white">{{ $admissions->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-navy d-flex align-items-center gap-2" id="labs-tab" data-bs-toggle="pill" data-bs-target="#labsHistoryPane" type="button" role="tab">
                <i class="bi bi-virus text-teal"></i>
                <span>Diagnostic Test Reports</span>
                <span class="badge bg-danger text-white">{{ $labOrders->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-navy d-flex align-items-center gap-2" id="vitals-tab" data-bs-toggle="pill" data-bs-target="#vitalsHistoryPane" type="button" role="tab">
                <i class="bi bi-heart-pulse text-teal"></i>
                <span>Vitals Log</span>
                <span class="badge bg-info text-dark">{{ $vitalsHistory->count() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="patientHistoryTabsContent">
        <!-- 1. CONSULTATIONS & PRESCRIPTIONS -->
        <div class="tab-pane fade show active" id="consultationsHistoryPane" role="tabpanel">
            <div class="d-flex flex-column gap-3">
                @forelse($visits as $v)
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <span class="badge bg-teal text-white font-monospace me-2">{{ $v->visit_number }}</span>
                                <span class="fw-bold text-navy">{{ $v->visit_date->format('d M, Y — h:i A') }}</span>
                                <span class="badge bg-light text-dark border ms-2">{{ ucfirst($v->visit_type) }}</span>
                            </div>
                            <span class="small text-muted-hms">
                                Attending: <strong>Dr. {{ $v->consultation?->doctor?->name ?? 'Medical Officer' }}</strong>
                            </span>
                        </div>
                        <div class="card-body pt-0">
                            @if($v->consultation)
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 h-100">
                                            <div class="fw-semibold text-navy small mb-1">Chief Complaints:</div>
                                            <p class="small text-dark mb-2">{{ $v->consultation->chief_complaints }}</p>

                                            @if($v->consultation->clinical_notes)
                                                <div class="fw-semibold text-navy small mb-1">Clinical Notes / Examination:</div>
                                                <p class="small text-muted-hms mb-2">{{ $v->consultation->clinical_notes }}</p>
                                            @endif

                                            <div class="fw-semibold text-navy small mb-1">Provisional / Final Diagnoses:</div>
                                            <div class="d-flex flex-wrap gap-1">
                                                @forelse($v->consultation->diagnoses as $d)
                                                    <span class="badge bg-white text-navy border">
                                                        {{ $d->condition_name }} @if($d->icd_code)({{ $d->icd_code }})@endif
                                                    </span>
                                                @empty
                                                    <span class="small text-muted">No diagnoses recorded</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 h-100">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="fw-semibold text-navy small">Prescribed Medications:</span>
                                                @if($v->consultation->prescription?->follow_up_date)
                                                    <span class="badge bg-info text-dark font-monospace" style="font-size: 0.7rem;">
                                                        Follow-up: {{ $v->consultation->prescription->follow_up_date->format('d M, Y') }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if($v->consultation->prescription?->items?->isNotEmpty())
                                                <ul class="list-group list-group-flush small bg-transparent">
                                                    @foreach($v->consultation->prescription->items as $item)
                                                        <li class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong class="text-teal">{{ $item->medicine_name }}</strong>
                                                                <span class="text-muted">({{ $item->dosage }} &bull; {{ $item->frequency }})</span>
                                                            </div>
                                                            <span class="badge bg-light text-dark border">{{ $item->duration }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                @if($v->consultation->prescription->advice)
                                                    <div class="mt-2 pt-2 border-top small text-muted-hms">
                                                        <strong>Advice:</strong> {{ $v->consultation->prescription->advice }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="small text-muted">No medicines prescribed for this visit.</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-muted-hms small p-3 bg-light rounded-3">
                                    No consultation authoring completed for this visit yet. Status: <strong>{{ ucfirst($v->status) }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="card border-0 shadow-sm rounded-3 p-5 text-center text-muted-hms">
                        <i class="bi bi-journal-x fs-2 text-muted d-block mb-2"></i>
                        No recorded consultations for this patient.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 2. INPATIENT ADMISSIONS & BED STAYS -->
        <div class="tab-pane fade" id="admissionsHistoryPane" role="tabpanel">
            <div class="d-flex flex-column gap-3">
                @forelse($admissions as $adm)
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <span class="badge bg-navy text-white font-monospace me-2">{{ $adm->admission_number }}</span>
                                <span class="fw-bold text-navy">{{ $adm->admission_date?->format('d M, Y') }}</span>
                                <span class="badge bg-teal text-white ms-2">{{ ucfirst($adm->status) }}</span>
                            </div>
                            <span class="small text-muted-hms">
                                Attending: <strong>Dr. {{ $adm->attendingDoctor?->name ?? 'Medical Officer' }}</strong>
                            </span>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 h-100">
                                        <div class="fw-semibold text-navy small mb-1">Ward & Bed Allocation:</div>
                                        <p class="small text-dark mb-2">
                                            {{ $adm->department?->name }} &bull; Bed: 
                                            <strong>{{ $adm->currentBedAssignment?->bed?->bed_number ?? 'Bed Unassigned' }}</strong>
                                        </p>

                                        <div class="fw-semibold text-navy small mb-1">Admission Reason:</div>
                                        <p class="small text-muted-hms mb-0">{{ $adm->admission_reason }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 h-100">
                                        <div class="fw-semibold text-navy small mb-1">Stay Duration & Discharge:</div>
                                        <p class="small text-dark mb-2">
                                            Admitted: {{ $adm->admission_date?->format('d M Y, h:i A') }}<br>
                                            Discharged: {{ $adm->discharge_date ? $adm->discharge_date->format('d M Y, h:i A') : 'Currently Admitted' }}
                                        </p>
                                        @if($adm->dischargeSummary)
                                            <div class="mt-2 pt-2 border-top small">
                                                <strong class="text-success">Discharge Summary:</strong>
                                                <div class="text-muted-hms">{{ $adm->dischargeSummary->condition_at_discharge }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card border-0 shadow-sm rounded-3 p-5 text-center text-muted-hms">
                        <i class="bi bi-hospital fs-2 text-muted d-block mb-2"></i>
                        No inpatient admissions on record for this patient.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 3. DIAGNOSTIC TEST REPORTS -->
        <div class="tab-pane fade" id="labsHistoryPane" role="tabpanel">
            <div class="d-flex flex-column gap-3">
                @forelse($labOrders as $order)
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <span class="badge bg-danger text-white font-monospace me-2">{{ $order->order_number }}</span>
                                <span class="fw-bold text-navy">{{ $order->created_at->format('d M, Y') }}</span>
                                <span class="badge bg-light text-dark border ms-2">Source: {{ ucfirst($order->source) }}</span>
                            </div>
                            <span class="small text-muted-hms">Status: <strong>{{ ucfirst($order->status) }}</strong></span>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="text-muted-hms small">
                                        <tr>
                                            <th>Test Name</th>
                                            <th>Findings Summary</th>
                                            <th>Review Status</th>
                                            <th>Reviewed By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                            <tr>
                                                <td class="fw-semibold text-navy">{{ $item->test_name }}</td>
                                                <td>
                                                    <div class="small" style="max-width: 350px;">
                                                        {{ $item->report?->findings_summary ?? 'Report pending upload' }}
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($item->report?->review_status === 'reviewed')
                                                        <span class="badge bg-success text-white"><i class="bi bi-check2"></i> Reviewed</span>
                                                    @elseif($item->report)
                                                        <span class="badge bg-warning text-dark">Pending Review</span>
                                                    @else
                                                        <span class="badge bg-secondary text-white">Pending</span>
                                                    @endif
                                                </td>
                                                <td class="small text-muted-hms">
                                                    {{ $item->report?->reviewedBy?->name ? 'Dr. ' . $item->report->reviewedBy->name : '--' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card border-0 shadow-sm rounded-3 p-5 text-center text-muted-hms">
                        <i class="bi bi-virus fs-2 text-muted d-block mb-2"></i>
                        No diagnostic laboratory orders found for this patient.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 4. VITALS & NURSING OBSERVATIONS -->
        <div class="tab-pane fade" id="vitalsHistoryPane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted-hms small text-uppercase">
                                <tr>
                                    <th>Date & Shift</th>
                                    <th>Blood Pressure</th>
                                    <th>Pulse</th>
                                    <th>Temperature</th>
                                    <th>SpO2</th>
                                    <th>Logged Observations</th>
                                    <th>Nurse</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vitalsHistory as $vital)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-navy">{{ $vital->recorded_at->format('d M Y') }}</div>
                                            <div class="small text-muted-hms">{{ ucfirst($vital->shift) }} shift</div>
                                        </td>
                                        <td class="fw-bold text-danger font-monospace">
                                            {{ $vital->blood_pressure ?? '--' }}
                                        </td>
                                        <td class="font-monospace text-primary">
                                            {{ $vital->pulse_rate ? $vital->pulse_rate . ' bpm' : '--' }}
                                        </td>
                                        <td class="font-monospace">
                                            {{ $vital->temperature ? $vital->temperature . ' °F' : '--' }}
                                        </td>
                                        <td class="font-monospace text-teal fw-semibold">
                                            {{ $vital->spo2 ? $vital->spo2 . '%' : '--' }}
                                        </td>
                                        <td>
                                            <div class="small text-truncate" style="max-width: 250px;">
                                                {{ $vital->observations }}
                                            </div>
                                        </td>
                                        <td class="small text-muted-hms">
                                            {{ $vital->nurse?->name ?? 'Sister on Duty' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted-hms">
                                            <i class="bi bi-heart-pulse fs-2 text-muted d-block mb-2"></i>
                                            No vital signs or nursing observations recorded.
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
</x-layouts.portal>
