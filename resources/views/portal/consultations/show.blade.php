<x-layouts.portal role="doctor" title="Consultation #CNS-{{ str_pad($consultation->id, 5, '0', STR_PAD_LEFT) }} — {{ $patient->name }} — Meghna Clinic & Nursing Home" activeNav="consultations">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('portal.doctor') }}" class="text-teal text-decoration-none">Doctor Workstation</a></li>
            <li class="breadcrumb-item"><a href="{{ route('portal.consultations.index') }}" class="text-teal text-decoration-none">Consultations</a></li>
            <li class="breadcrumb-item active" aria-current="page">#CNS-{{ str_pad($consultation->id, 5, '0', STR_PAD_LEFT) }}</li>
        </ol>
    </nav>

    <!-- Header Dossier Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 54px; height: 54px; font-size: 1.4rem;">
                        <i class="bi bi-file-earmark-medical"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h4 class="fw-bold text-navy mb-0">{{ $patient->name }}</h4>
                            <span class="badge bg-teal font-monospace">{{ $patient->patient_code }}</span>
                            <span class="badge bg-{{ $consultation->status_badge_class }} text-uppercase px-2 py-1" style="font-size: 0.75rem;">
                                {{ $consultation->status }}
                            </span>
                            @if($consultation->is_finalized)
                                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.7rem;">
                                    <i class="bi bi-lock-fill text-success me-1"></i> Finalized {{ $consultation->finalized_at?->format('d M Y, h:i A') }}
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace" style="font-size: 0.7rem;">
                                    <i class="bi bi-pencil-fill me-1"></i> Draft Mode (Unfinalized)
                                </span>
                            @endif
                        </div>
                        <div class="text-muted-hms small d-flex flex-wrap gap-3">
                            <span><i class="bi bi-person-badge text-teal me-1"></i> Attending Doctor: <strong class="text-dark">Dr. {{ $doctor->name }}</strong></span>
                            <span><i class="bi bi-ticket-detailed text-teal me-1"></i> Visit: <strong class="text-dark font-monospace">{{ $visit->visit_number }}</strong> ({{ ucfirst($visit->visit_type) }})</span>
                            <span><i class="bi bi-clock-history text-teal me-1"></i> Authored: <strong class="text-dark">{{ $consultation->created_at->format('d M Y, h:i A') }}</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Action Controls -->
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('portal.consultations.print', $consultation) }}" target="_blank" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-printer"></i>
                        <span>Print Prescription Slip</span>
                    </a>

                    @if(!$consultation->is_finalized)
                        <form method="POST" action="{{ route('portal.consultations.finalize', $consultation) }}" class="d-inline" onsubmit="return confirm('Permanently finalize this consultation record? Once finalized, edits will be recorded in the audit correction ledger.');">
                            @csrf
                            <button type="submit" class="btn btn-teal btn-sm d-flex align-items-center gap-1 shadow-sm">
                                <i class="bi bi-check2-circle"></i>
                                <span>Finalize Consultation</span>
                            </button>
                        </form>
                        <a href="{{ route('portal.consultations.edit', $consultation) }}" class="btn btn-navy btn-sm d-flex align-items-center gap-1">
                            <i class="bi bi-pencil"></i>
                            <span>Edit Draft</span>
                        </a>
                    @else
                        @can('update', $consultation)
                            <a href="{{ route('portal.consultations.edit', $consultation) }}" class="btn btn-warning btn-sm d-flex align-items-center gap-1 shadow-sm text-dark">
                                <i class="bi bi-shield-exclamation"></i>
                                <span>Correct / Amend Record</span>
                            </a>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Alert / Notice Banner: Medical Responsibility Disclaimer -->
    <div class="alert alert-info border-info-subtle shadow-sm mb-4 rounded-3 d-flex align-items-center gap-3">
        <i class="bi bi-shield-check fs-3 text-info flex-shrink-0"></i>
        <div class="small">
            <strong class="text-navy">Clinical Governance & Legal Disclaimer:</strong>
            This software strictly records decisions made by authorized healthcare professionals. No automated medical diagnosis is performed. All treatments and recommendations were reviewed by Dr. {{ $doctor->name }} (BMDC Reg: {{ $doctor->staffProfile?->license_number ?? 'Verified' }}).
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4 rounded-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show shadow-sm mb-4 rounded-3" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i> {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4 rounded-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Main Clinical Record (8 cols) -->
        <div class="col-12 col-lg-8">
            <!-- 1. Chief Complaints -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold text-navy mb-0">
                        <i class="bi bi-chat-left-dots text-teal me-2"></i> Chief Complaints
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="mb-0 text-dark fs-6" style="white-space: pre-line;">{{ $consultation->chief_complaints }}</p>
                </div>
            </div>

            <!-- 2. Clinical Notes & Physical Examination Findings -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold text-navy mb-0">
                        <i class="bi bi-clipboard-pulse text-teal me-2"></i> Clinical Notes & Examination Findings
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if($consultation->clinical_notes)
                        <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $consultation->clinical_notes }}</p>
                    @else
                        <span class="text-muted fst-italic">No additional physical examination notes recorded.</span>
                    @endif
                </div>
            </div>

            <!-- 3. Diagnoses -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-navy mb-0">
                            <i class="bi bi-journal-medical text-teal me-2"></i> Documented Diagnoses
                        </h5>
                        <span class="badge bg-teal">{{ $diagnoses->count() }} Recorded</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($diagnoses->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small text-uppercase text-muted-hms">
                                    <tr>
                                        <th>#</th>
                                        <th>Clinical Condition / Disease</th>
                                        <th>ICD-10 Code</th>
                                        <th>Classification</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($diagnoses as $idx => $diag)
                                        <tr>
                                            <td class="font-monospace text-muted">{{ $idx + 1 }}</td>
                                            <td class="fw-bold text-navy">{{ $diag->condition_name }}</td>
                                            <td>
                                                @if($diag->icd_code)
                                                    <span class="badge bg-light text-secondary border font-monospace">{{ $diag->icd_code }}</span>
                                                @else
                                                    <span class="text-muted small">&mdash;</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $diag->diagnosis_type === 'final' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                    {{ ucfirst($diag->diagnosis_type) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3 text-muted fst-italic">
                            No diagnoses recorded yet. (Mandatory before finalizing record).
                        </div>
                    @endif
                </div>
            </div>

            <!-- 4. Prescription & Medication Regimen (Rx) -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-navy mb-0">
                            <span class="text-teal font-monospace me-2" style="font-size: 1.3rem;">&#8478;</span> Prescription & Medication Regimen
                        </h5>
                        @if($medications->isNotEmpty())
                            <span class="badge bg-teal">{{ $medications->count() }} Medications</span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($medications->isNotEmpty())
                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small text-uppercase text-muted-hms">
                                    <tr>
                                        <th>Medicine Name</th>
                                        <th>Dosage</th>
                                        <th>Frequency</th>
                                        <th>Duration</th>
                                        <th>Special Instructions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($medications as $med)
                                        <tr>
                                            <td class="fw-semibold text-dark">{{ $med->medicine_name }}</td>
                                            <td class="font-monospace">{{ $med->dosage }}</td>
                                            <td class="font-monospace">{{ $med->frequency }}</td>
                                            <td class="font-monospace">{{ $med->duration }}</td>
                                            <td class="small text-muted">{{ $med->instructions ?? '&mdash;' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-3 bg-light rounded-3 text-center text-muted small mb-4">
                            No pharmacological medications prescribed in this consultation.
                        </div>
                    @endif

                    @if($prescription && $prescription->advice)
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="small fw-bold text-navy mb-1"><i class="bi bi-lightbulb text-teal me-1"></i> Non-Pharmacological Advice & Instructions:</div>
                            <p class="mb-0 text-dark small" style="white-space: pre-line;">{{ $prescription->advice }}</p>
                        </div>
                    @endif

                    @if($prescription && $prescription->follow_up_date)
                        <div class="mt-3 p-3 bg-teal-subtle text-teal rounded-3 border border-teal-subtle d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-bold"><i class="bi bi-calendar-event me-1"></i> Recommended Review Date:</span>
                                <span class="font-monospace fw-bold ms-1">{{ $prescription->follow_up_date->format('l, d F Y') }}</span>
                            </div>
                            <span class="badge bg-teal text-white">{{ $prescription->follow_up_date->diffForHumans() }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 5. Audit Trail & Legal Medical Correction Ledger -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-navy mb-0">
                            <i class="bi bi-shield-check text-teal me-2"></i> Audit Trail & Record Correction History
                        </h5>
                        <span class="badge bg-light text-muted border font-monospace">{{ $auditLogs->count() }} Entries</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($auditLogs->isNotEmpty())
                        <div class="timeline d-flex flex-column gap-3">
                            @foreach($auditLogs as $log)
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge {{ match($log->event) {
                                                'created' => 'bg-teal',
                                                'finalized' => 'bg-success',
                                                'corrected' => 'bg-warning text-dark',
                                                default => 'bg-secondary'
                                            } }} text-uppercase font-monospace" style="font-size: 0.7rem;">
                                                {{ $log->event }}
                                            </span>
                                            <span class="fw-semibold text-dark small">by {{ $log->user?->name ?? 'System' }}</span>
                                        </div>
                                        <span class="small text-muted font-monospace">{{ $log->created_at->format('d M Y, h:i:s A') }}</span>
                                    </div>

                                    @if(!empty($log->new_values['correction_reason']))
                                        <div class="mt-2 p-2 bg-warning-subtle text-warning-emphasis rounded border border-warning-subtle small">
                                            <strong>Correction Reason Documented:</strong> {{ $log->new_values['correction_reason'] }}
                                        </div>
                                    @endif

                                    <div class="mt-2 text-muted-hms font-monospace small" style="font-size: 0.75rem;">
                                        IP: {{ $log->ip_address }} &bull; Agent: {{ Str::limit($log->user_agent, 40) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3 text-muted small">
                            No audit log modifications recorded yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar (4 cols) -->
        <div class="col-12 col-lg-4">
            <!-- Patient Demographics Summary -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-navy mb-0">
                        <i class="bi bi-person-lines-fill text-teal me-1"></i> Patient Dossier
                    </h6>
                </div>
                <div class="card-body p-3 small">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Full Name:</span>
                        <strong class="text-dark">{{ $patient->name }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Patient Code:</span>
                        <span class="font-monospace text-teal fw-bold">{{ $patient->patient_code }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Age / Gender:</span>
                        <span>{{ $patient->formatted_age }} &bull; {{ ucfirst($patient->gender) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Blood Group:</span>
                        <span class="badge bg-danger-subtle text-danger">{{ $patient->blood_group ?? 'Unknown' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Primary Phone:</span>
                        <span class="font-monospace">{{ $patient->phone }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Address:</span>
                        <span class="text-end">{{ Str::limit($patient->address, 30) }}</span>
                    </div>
                    <div class="mt-3 pt-2 text-center border-top">
                        <a href="{{ route('portal.consultations.patient-history', $patient) }}" class="btn btn-outline-teal btn-sm w-100">
                            <i class="bi bi-clock-history me-1"></i> View Full Consultation History
                        </a>
                    </div>
                </div>
            </div>

            <!-- Inpatient Bed Status if admitted -->
            @if($visit->admission)
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-navy mb-0">
                            <i class="bi bi-hospital text-teal me-1"></i> Inpatient Bed Stay
                        </h6>
                    </div>
                    <div class="card-body p-3 small">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Admission #:</span>
                            <span class="font-monospace fw-bold">{{ $visit->admission->admission_number }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Bed Allocated:</span>
                            <span class="fw-bold text-teal">{{ $visit->admission->currentBedAssignment?->bed?->bed_number ?? 'Bed Active' }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Ward / Dept:</span>
                            <span>{{ $visit->admission->department?->name ?? 'Ward' }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">Admission Date:</span>
                            <span>{{ $visit->admission->admission_date->format('d M Y, h:i A') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Past Consultations Timeline -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-navy mb-0">
                        <i class="bi bi-clock-history text-teal me-1"></i> Previous Consultations
                    </h6>
                </div>
                <div class="card-body p-3">
                    @if($pastConsultations->isNotEmpty())
                        <div class="d-flex flex-column gap-2">
                            @foreach($pastConsultations as $past)
                                <a href="{{ route('portal.consultations.show', $past) }}" class="p-2 border rounded text-decoration-none text-dark bg-light d-block hover-shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-navy small">#CNS-{{ str_pad($past->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        <span class="badge bg-{{ $past->status_badge_class }}" style="font-size: 0.65rem;">{{ $past->status }}</span>
                                    </div>
                                    <div class="text-muted font-monospace small" style="font-size: 0.7rem;">{{ $past->created_at->format('d M Y') }} &bull; Dr. {{ $past->doctor->name }}</div>
                                    <div class="small text-truncate text-muted mt-1">{{ $past->chief_complaints }}</div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3 text-muted small">
                            No other consultations on record.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.portal>

