<x-layouts.portal role="reception" title="Visit Dossier — {{ $visit->visit_number }}">
    <div class="container-fluid px-0">
        <!-- Breadcrumbs & Action Toolbar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('portal.visits.index') }}" class="text-decoration-none text-muted-hms">Patient Visits</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">{{ $visit->visit_number }}</li>
                    </ol>
                </nav>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">Visit #{{ $visit->visit_number }}</h1>
                    
                    <!-- Triage Badge -->
                    @if($visit->triage_level === 'critical')
                        <span class="badge bg-danger text-white fw-bold px-2.5 py-1">
                            <i class="bi bi-exclamation-octagon-fill me-1"></i> Critical Triage
                        </span>
                    @elseif($visit->triage_level === 'priority')
                        <span class="badge bg-warning text-dark fw-semibold px-2.5 py-1">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Priority Triage
                        </span>
                    @else
                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2.5 py-1">
                            <i class="bi bi-check-circle-fill me-1"></i> Normal Triage
                        </span>
                    @endif

                    <!-- Care Setting Badge -->
                    @if($visit->is_inpatient)
                        <span class="badge bg-primary text-white px-2.5 py-1">
                            <i class="bi bi-hospital me-1"></i> Inpatient (Admitted)
                        </span>
                    @else
                        <span class="badge bg-light text-dark border px-2.5 py-1">
                            <i class="bi bi-person-walking me-1"></i> Outpatient Care
                        </span>
                    @endif

                    <!-- Status Badge -->
                    <span class="badge bg-navy text-white px-2.5 py-1">
                        {{ ucfirst(str_replace('_', ' ', $visit->status)) }}
                    </span>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                @can('print', $visit)
                    <a href="{{ route('portal.visits.print-token', $visit->id) }}" target="_blank" class="btn btn-outline-teal d-inline-flex align-items-center gap-2 shadow-xs">
                        <i class="bi bi-printer"></i>
                        <span>Print Queue Token</span>
                    </a>
                @endcan

                @can('update', $visit)
                    <a href="{{ route('portal.visits.edit', $visit->id) }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 shadow-xs">
                        <i class="bi bi-pencil"></i>
                        <span>Update Status / Triage</span>
                    </a>
                @endcan

                <a href="{{ route('portal.patients.show', $visit->patient_id) }}" class="btn btn-outline-navy d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-person-badge"></i>
                    <span>Patient Profile</span>
                </a>

                <a href="{{ route('portal.visits.index') }}" class="btn btn-light d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Visits
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

        <div class="row g-4">
            <!-- Left Column: Patient Master Details (Strict Entity Separation) -->
            <div class="col-lg-4">
                <!-- Patient Master Card -->
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-navy bg-opacity-10 text-navy p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-person-badge-fill fs-5"></i>
                            </span>
                            <h2 class="h5 fw-bold text-navy mb-0">Patient Master Profile</h2>
                        </div>
                        <span class="badge bg-light text-muted border">Separated Master</span>
                    </div>

                    <div class="text-center pb-3 border-bottom mb-3">
                        <div class="rounded-circle bg-teal bg-opacity-10 text-teal d-inline-flex align-items-center justify-content-center fw-bold mx-auto mb-2" style="width: 56px; height: 56px; font-size: 1.5rem;">
                            {{ strtoupper(substr($visit->patient->name, 0, 1)) }}
                        </div>
                        <h3 class="h6 fw-bold text-navy mb-1">{{ $visit->patient->name }}</h3>
                        <span class="badge bg-navy text-white font-monospace px-2.5 py-1">
                            {{ $visit->patient->patient_code }}
                        </span>
                        @if($visit->patient->blood_group)
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle fw-bold ms-1">
                                {{ $visit->patient->blood_group }}
                            </span>
                        @endif
                    </div>

                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2.5" style="font-size: 0.85rem;">
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">Age & Gender:</span>
                            <span class="fw-semibold text-navy">{{ $visit->patient->formatted_age }}, {{ ucfirst($visit->patient->gender) }}</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">Primary Phone:</span>
                            <span class="fw-semibold font-monospace text-navy">{{ $visit->patient->phone }}</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">NID / Birth Cert:</span>
                            <span class="font-monospace text-navy">{{ $visit->patient->nid_or_birth_cert ?? 'Not Provided' }}</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">Address:</span>
                            <span class="text-navy text-end small">{{ Str::limit($visit->patient->address ?? 'Dinajpur', 35) }}</span>
                        </li>
                        @if($visit->patient->primaryContact)
                            <li class="pt-1">
                                <span class="text-muted-hms d-block mb-1">Emergency Contact:</span>
                                <div class="p-2.5 rounded bg-light border small">
                                    <div class="fw-semibold text-navy">{{ $visit->patient->primaryContact->name }} ({{ $visit->patient->primaryContact->relationship }})</div>
                                    <div class="font-monospace text-danger">{{ $visit->patient->primaryContact->phone }}</div>
                                </div>
                            </li>
                        @endif
                    </ul>

                    <div class="mt-3 pt-3 border-top text-center">
                        <a href="{{ route('portal.patients.show', $visit->patient_id) }}" class="btn btn-sm btn-outline-teal w-100">
                            <i class="bi bi-folder2-open me-1"></i> Open Full Medical Record
                        </a>
                    </div>
                </div>

                <!-- Visit Metadata Box -->
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
                    <h3 class="h6 fw-bold text-navy pb-2 mb-3 border-bottom">Visit Metadata & Traceability</h3>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2" style="font-size: 0.825rem;">
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Unique Visit ID:</span>
                            <span class="font-monospace fw-bold text-navy">{{ $visit->visit_number }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Check-in Timestamp:</span>
                            <span class="fw-semibold text-navy">{{ $visit->visit_date ? $visit->visit_date->format('d M Y, h:i A') : 'N/A' }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Visit Type:</span>
                            <span class="text-capitalize">{{ str_replace('_', ' ', $visit->visit_type) }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Triage Level:</span>
                            <span class="text-capitalize fw-bold">{{ $visit->triage_level }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Current Status:</span>
                            <span class="text-capitalize">{{ str_replace('_', ' ', $visit->status) }}</span>
                        </li>
                        @if($visit->appointment_id)
                            <li class="d-flex justify-content-between">
                                <span class="text-muted">Linked Appointment:</span>
                                <span class="font-monospace">#{{ $visit->appointment_id }}</span>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Right Column: Visit Clinical Records & Lifecycle -->
            <div class="col-lg-8">
                <!-- Clinical Consultation Record -->
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-teal bg-opacity-10 text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-clipboard2-pulse-fill fs-5"></i>
                            </span>
                            <h2 class="h5 fw-bold text-navy mb-0">Doctor Consultation & Clinical Findings</h2>
                        </div>
                        @if($visit->consultation)
                            <span class="badge bg-{{ $visit->consultation->status === 'finalized' ? 'success' : 'warning' }}-subtle text-{{ $visit->consultation->status === 'finalized' ? 'success' : 'warning-emphasis' }} border">
                                {{ ucfirst($visit->consultation->status) }}
                            </span>
                        @endif
                    </div>

                    @if($visit->consultation)
                        <div class="row g-3 mb-3" style="font-size: 0.85rem;">
                            <div class="col-md-6">
                                <span class="text-muted d-block mb-1">Attending Physician:</span>
                                <div class="fw-bold text-navy">
                                    <i class="bi bi-person-fill text-teal"></i> {{ $visit->consultation->doctor->name }}
                                </div>
                                <span class="text-muted small">{{ $visit->consultation->doctor->staffProfile?->department?->name ?? 'Medical Officer' }}</span>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block mb-1">Consultation Recorded:</span>
                                <div class="text-navy">
                                    {{ $visit->consultation->created_at->format('d M Y, h:i A') }}
                                </div>
                            </div>

                            <!-- Chief Complaints -->
                            <div class="col-12">
                                <span class="text-muted d-block mb-1 fw-semibold">Chief Complaints:</span>
                                <div class="p-3 rounded bg-light border text-navy">
                                    {{ $visit->consultation->chief_complaints }}
                                </div>
                            </div>

                            <!-- Clinical Notes -->
                            @if($visit->consultation->clinical_notes)
                                <div class="col-12">
                                    <span class="text-muted d-block mb-1 fw-semibold">Physician Examination & Clinical Notes:</span>
                                    <div class="p-3 rounded bg-light border text-navy">
                                        {{ $visit->consultation->clinical_notes }}
                                    </div>
                                </div>
                            @endif

                            <!-- Diagnoses List -->
                            @if($visit->consultation->diagnoses && $visit->consultation->diagnoses->isNotEmpty())
                                <div class="col-12">
                                    <span class="text-muted d-block mb-1 fw-semibold">Diagnoses:</span>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($visit->consultation->diagnoses as $dx)
                                            <span class="badge bg-navy text-white px-2.5 py-1.5" style="font-size: 0.8rem;">
                                                {{ $dx->condition_name }}
                                                @if($dx->icd_code)
                                                    <span class="opacity-75 font-monospace">({{ $dx->icd_code }})</span>
                                                @endif
                                                <span class="badge bg-light text-dark ms-1">{{ ucfirst($dx->diagnosis_type) }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Prescriptions Summary -->
                            @if($visit->consultation->prescription)
                                <div class="col-12 mt-3 pt-3 border-top">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h4 class="h6 fw-bold text-navy mb-0">Electronic Prescription Items</h4>
                                        @if($visit->consultation->prescription->follow_up_date)
                                            <span class="badge bg-info-subtle text-info-emphasis border">
                                                Follow-up: {{ $visit->consultation->prescription->follow_up_date->format('d M Y') }}
                                            </span>
                                        @endif
                                    </div>

                                    @if($visit->consultation->prescription->items && $visit->consultation->prescription->items->isNotEmpty())
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.8rem;">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Medicine Name</th>
                                                        <th>Dosage</th>
                                                        <th>Frequency</th>
                                                        <th>Duration</th>
                                                        <th>Instructions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($visit->consultation->prescription->items as $item)
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

                                    @if($visit->consultation->prescription->advice)
                                        <div class="mt-2 p-2 bg-light rounded small text-navy">
                                            <strong>Advice:</strong> {{ $visit->consultation->prescription->advice }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-4 text-center text-muted bg-light rounded border">
                            <i class="bi bi-clock-history fs-3 d-block mb-2 text-muted"></i>
                            <h4 class="h6 fw-semibold text-navy mb-1">Awaiting Clinical Consultation</h4>
                            <p class="small mb-0">The patient is currently queued for doctor evaluation.</p>
                        </div>
                    @endif
                </div>

                <!-- Inpatient Ward Admission Card (If Admitted) -->
                @if($visit->admissions && $visit->admissions->isNotEmpty())
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded bg-primary bg-opacity-10 text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="bi bi-hospital fs-5"></i>
                                </span>
                                <h2 class="h5 fw-bold text-navy mb-0">Inpatient Admission Records (10 Beds)</h2>
                            </div>
                            <span class="badge bg-primary text-white">Inpatient Care</span>
                        </div>

                        @foreach($visit->admissions as $admission)
                            <div class="p-3 rounded-3 bg-light border mb-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <div>
                                        <span class="font-monospace fw-bold text-navy fs-6">{{ $admission->admission_number }}</span>
                                        <span class="badge bg-{{ $admission->status === 'admitted' ? 'primary' : 'success' }} ms-1">
                                            {{ ucfirst($admission->status) }}
                                        </span>
                                    </div>
                                    <div class="text-muted small">
                                        Admitted: {{ $admission->admission_date ? $admission->admission_date->format('d M Y, h:i A') : 'N/A' }}
                                        @if($admission->discharge_date)
                                            • Discharged: {{ $admission->discharge_date->format('d M Y, h:i A') }}
                                        @endif
                                    </div>
                                </div>

                                <div class="row g-2 small text-navy mb-2">
                                    <div class="col-md-6">
                                        <strong>Attending Doctor:</strong> {{ $admission->attendingDoctor?->name ?? 'Medical Officer' }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Allocated Bed:</strong> 
                                        @if($admission->currentBedAssignment)
                                            <span class="badge bg-navy text-white">
                                                {{ $admission->currentBedAssignment->bed->bed_number }}
                                                ({{ $admission->currentBedAssignment->bed->department->name ?? 'Ward' }})
                                            </span>
                                        @else
                                            <span class="text-muted">Discharged / No Active Bed</span>
                                        @endif
                                    </div>
                                    <div class="col-12">
                                        <strong>Reason for Admission:</strong> {{ $admission->admission_reason }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Clinical Clearance:</strong>
                                        @if($admission->clinical_clearance)
                                            <span class="badge bg-success-subtle text-success">Cleared</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis">Pending Doctor Sign-off</span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Financial Clearance:</strong>
                                        @if($admission->financial_clearance)
                                            <span class="badge bg-success-subtle text-success">Cleared</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis">Pending Cashier Clearance</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Discharge Summary (if concluded) -->
                                @if($admission->dischargeSummary)
                                    <div class="mt-3 p-3 bg-white rounded border">
                                        <h5 class="h6 fw-bold text-navy mb-1">
                                            <i class="bi bi-file-earmark-medical-fill text-success"></i> Concluded Discharge Summary
                                        </h5>
                                        <div class="small text-navy">
                                            <div><strong>Condition at Discharge:</strong> <span class="badge bg-light text-dark border">{{ ucfirst($admission->dischargeSummary->condition_at_discharge) }}</span></div>
                                            <div><strong>Discharge Diagnosis:</strong> {{ $admission->dischargeSummary->discharge_diagnosis }}</div>
                                            <div><strong>Treatment Given:</strong> {{ $admission->dischargeSummary->treatment_given }}</div>
                                            <div><strong>Discharge Medications:</strong> {{ $admission->dischargeSummary->discharge_medications }}</div>
                                            @if($admission->dischargeSummary->follow_up_instructions)
                                                <div><strong>Follow-up Instructions:</strong> {{ $admission->dischargeSummary->follow_up_instructions }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Visit Audit History -->
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-secondary bg-opacity-10 text-secondary p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-shield-check fs-5"></i>
                            </span>
                            <h2 class="h5 fw-bold text-navy mb-0">Visit Audit Log & Traceability</h2>
                        </div>
                        <span class="badge bg-light text-muted border">Tamper-Evident History</span>
                    </div>

                    @if($visit->auditLogs && $visit->auditLogs->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.8rem;">
                                <thead class="table-light">
                                    <tr class="text-uppercase text-muted-hms" style="font-size: 0.725rem;">
                                        <th>Timestamp</th>
                                        <th>Event</th>
                                        <th>Staff Operator</th>
                                        <th>IP Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($visit->auditLogs as $log)
                                        <tr>
                                            <td class="text-muted">{{ $log->created_at->format('d M Y, h:i:s A') }}</td>
                                            <td>
                                                <span class="badge bg-navy text-white">
                                                    {{ str_replace('_', ' ', ucfirst($log->event)) }}
                                                </span>
                                            </td>
                                            <td class="fw-semibold text-navy">{{ $log->user?->name ?? 'System' }}</td>
                                            <td class="font-monospace text-muted">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-3 text-center text-muted small bg-light rounded border">
                            Check-in initial event recorded at {{ $visit->created_at->format('d M Y, h:i A') }}.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.portal>
