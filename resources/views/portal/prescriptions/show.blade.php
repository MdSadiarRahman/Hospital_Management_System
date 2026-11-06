<x-layouts.portal role="doctor" title="Prescription {{ $prescription->prescription_number }} — {{ $patient->name }} — Meghna Clinic & Nursing Home" activeNav="prescriptions">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('portal.doctor') }}" class="text-teal text-decoration-none">Doctor Workstation</a></li>
            <li class="breadcrumb-item"><a href="{{ route('portal.prescriptions.index') }}" class="text-teal text-decoration-none">Prescriptions</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $prescription->prescription_number }}</li>
        </ol>
    </nav>

    <!-- Header Dossier Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 54px; height: 54px; font-size: 1.4rem;">
                        <i class="bi bi-capsule"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h4 class="fw-bold text-navy mb-0">{{ $patient->name }}</h4>
                            <span class="badge bg-teal font-monospace">{{ $patient->patient_code }}</span>
                            <span class="badge bg-{{ $prescription->status_badge_class }} text-uppercase px-2 py-1">
                                {{ $prescription->status }}
                            </span>
                            <span class="badge bg-light text-navy border font-monospace">
                                {{ $prescription->prescription_number }}
                            </span>
                        </div>
                        <div class="text-muted-hms small d-flex flex-wrap gap-3">
                            <span><i class="bi bi-person-badge text-teal me-1"></i> Prescribing Doctor: <strong class="text-dark">Dr. {{ $doctor?->name ?? 'Clinical Staff' }}</strong></span>
                            @if($prescription->is_approved)
                                <span><i class="bi bi-shield-check text-success me-1"></i> Approved by: <strong class="text-dark">Dr. {{ $prescription->approvedBy?->name ?? $doctor?->name }}</strong> ({{ $prescription->approved_at?->format('d M Y, h:i A') }})</span>
                            @else
                                <span class="text-warning"><i class="bi bi-hourglass-split me-1"></i> Status: Unapproved Draft</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Controls -->
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('portal.prescriptions.print', $prescription) }}" target="_blank" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-printer"></i>
                        <span>Print Prescription Slip</span>
                    </a>

                    @if(!$prescription->is_approved)
                        <form method="POST" action="{{ route('portal.prescriptions.approve', $prescription) }}" class="d-inline" onsubmit="return confirm('Officially approve this prescription? Once approved, it will be visible to the patient.');">
                            @csrf
                            <button type="submit" class="btn btn-teal btn-sm d-flex align-items-center gap-1 shadow-sm">
                                <i class="bi bi-check2-circle"></i>
                                <span>Approve Prescription</span>
                            </button>
                        </form>
                        <a href="{{ route('portal.prescriptions.edit', $prescription) }}" class="btn btn-navy btn-sm d-flex align-items-center gap-1">
                            <i class="bi bi-pencil"></i>
                            <span>Edit Draft</span>
                        </a>
                    @else
                        @can('update', $prescription)
                            <a href="{{ route('portal.prescriptions.edit', $prescription) }}" class="btn btn-warning btn-sm d-flex align-items-center gap-1 shadow-sm text-dark">
                                <i class="bi bi-shield-exclamation"></i>
                                <span>Amend / Correct Rx</span>
                            </a>
                        @endcan
                    @endif
                </div>
            </div>
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
        <!-- Main Prescription Content (8 cols) -->
        <div class="col-12 col-lg-8">
            <!-- Pharmacological Regimen Table -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-4 text-teal fw-bold" style="font-family: Georgia, serif;">℞</span>
                        <h5 class="fw-bold text-navy mb-0">Pharmacological Regimen & Drugs</h5>
                    </div>
                    <span class="badge bg-teal font-monospace">{{ $items->count() }} Medications</span>
                </div>
                <div class="card-body p-4">
                    @if($items->isNotEmpty())
                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small text-uppercase text-muted-hms">
                                    <tr>
                                        <th>#</th>
                                        <th>Medicine Name</th>
                                        <th>Dosage</th>
                                        <th>Frequency</th>
                                        <th>Duration</th>
                                        <th>Instructions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $idx => $item)
                                        <tr>
                                            <td class="font-monospace text-muted">{{ $idx + 1 }}</td>
                                            <td class="fw-bold text-navy">{{ $item->medicine_name }}</td>
                                            <td class="font-monospace">{{ $item->dosage }}</td>
                                            <td class="font-monospace"><span class="badge bg-teal-subtle text-teal border">{{ $item->frequency }}</span></td>
                                            <td class="font-monospace">{{ $item->duration }}</td>
                                            <td class="small text-muted">{{ $item->instructions ?? '&mdash;' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted small">
                            No medications added to this prescription.
                        </div>
                    @endif

                    @if($prescription->advice)
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="small fw-bold text-navy mb-1"><i class="bi bi-lightbulb text-teal me-1"></i> Clinical Advice & Diet Directions:</div>
                            <p class="mb-0 text-dark small" style="white-space: pre-line;">{{ $prescription->advice }}</p>
                        </div>
                    @endif

                    @if($prescription->follow_up_date)
                        <div class="p-3 bg-teal-subtle text-teal rounded-3 border border-teal-subtle d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-bold"><i class="bi bi-calendar-event me-1"></i> Follow-up Consultation:</span>
                                <span class="font-monospace fw-bold ms-1">{{ $prescription->follow_up_date->format('l, d F Y') }}</span>
                            </div>
                            <span class="badge bg-teal text-white">{{ $prescription->follow_up_date->diffForHumans() }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Audit Trail & Correction History -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-navy mb-0">
                        <i class="bi bi-shield-check text-teal me-2"></i> Audit Trail & Prescription Revisions
                    </h5>
                    <span class="badge bg-light text-muted border font-monospace">{{ $auditLogs->count() }} Records</span>
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
                                                'approved' => 'bg-success',
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
                                            <strong>Correction Reason:</strong> {{ $log->new_values['correction_reason'] }}
                                        </div>
                                    @endif

                                    <div class="mt-2 text-muted-hms font-monospace small" style="font-size: 0.75rem;">
                                        IP: {{ $log->ip_address }} &bull; Agent: {{ Str::limit($log->user_agent, 40) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3 text-muted small">No audit history recorded.</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar (4 cols) -->
        <div class="col-12 col-lg-4">
            <!-- Linked Consultation Info -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-navy mb-0">
                        <i class="bi bi-link-45deg text-teal me-1"></i> Linked Clinical Consultation
                    </h6>
                </div>
                <div class="card-body p-3 small">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Consultation Ref:</span>
                        <a href="{{ route('portal.consultations.show', $consultation) }}" class="fw-bold text-teal text-decoration-none">
                            #CNS-{{ str_pad($consultation->id, 5, '0', STR_PAD_LEFT) }}
                        </a>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Visit Number:</span>
                        <span class="font-monospace fw-bold text-dark">{{ $consultation->visit?->visit_number }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Consultation Status:</span>
                        <span class="badge bg-{{ $consultation->status_badge_class }}">{{ ucfirst($consultation->status) }}</span>
                    </div>

                    @if($diagnoses->isNotEmpty())
                        <div class="mt-2 pt-2">
                            <span class="text-muted d-block mb-1">Diagnoses:</span>
                            @foreach($diagnoses as $d)
                                <span class="badge bg-light text-navy border mb-1">{{ $d->condition_name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Patient Demographics -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-navy mb-0">
                        <i class="bi bi-person-lines-fill text-teal me-1"></i> Patient Dossier
                    </h6>
                </div>
                <div class="card-body p-3 small">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Name:</span>
                        <strong class="text-dark">{{ $patient->name }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Patient Code:</span>
                        <span class="font-monospace text-teal fw-bold">{{ $patient->patient_code }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Age / Sex:</span>
                        <span>{{ $patient->formatted_age }} &bull; {{ ucfirst($patient->gender) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Phone:</span>
                        <span class="font-monospace">{{ $patient->phone }}</span>
                    </div>
                    <div class="mt-3 text-center">
                        <a href="{{ route('portal.prescriptions.patient-history', $patient) }}" class="btn btn-sm btn-outline-teal w-100">
                            <i class="bi bi-clock-history me-1"></i> View All Prescriptions for Patient
                        </a>
                    </div>
                </div>
            </div>

            <!-- Patient Historical Prescriptions -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-navy mb-0">
                        <i class="bi bi-clock-history text-teal me-1"></i> Prior Prescriptions
                    </h6>
                </div>
                <div class="card-body p-3">
                    @if($patientHistory->isNotEmpty())
                        <div class="d-flex flex-column gap-2">
                            @foreach($patientHistory as $prior)
                                <a href="{{ route('portal.prescriptions.show', $prior) }}" class="p-2 border rounded text-decoration-none text-dark bg-light d-block hover-shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-navy small">{{ $prior->prescription_number }}</span>
                                        <span class="badge bg-{{ $prior->status_badge_class }}" style="font-size: 0.65rem;">{{ $prior->status }}</span>
                                    </div>
                                    <div class="text-muted font-monospace small" style="font-size: 0.7rem;">{{ $prior->created_at->format('d M Y') }} &bull; {{ $prior->items->count() }} drugs</div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3 text-muted small">No other prescriptions on record.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.portal>

