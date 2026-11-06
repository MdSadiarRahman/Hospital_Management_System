<x-layouts.portal role="nurse" title="Inpatient Clinical Dossier — {{ $patient->name }}" activeNav="patients">
    <!-- Breadcrumb & Back Action -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('portal.nurse') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i>
            <span>Back to Nursing Station</span>
        </a>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-teal" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Dossier
            </button>
        </div>
    </div>

    <!-- Patient & Inpatient Admission Header Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-navy text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px; font-size: 1.6rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <h4 class="fw-bold text-navy mb-0">{{ $patient->name }}</h4>
                            <span class="badge bg-navy text-white px-2 py-1 font-monospace">{{ $patient->patient_code }}</span>
                            @if($bed)
                                <span class="badge bg-teal text-white px-2 py-1 font-monospace">BED: {{ $bed->bed_number }}</span>
                            @else
                                <span class="badge bg-warning text-dark px-2 py-1">Pending Bed</span>
                            @endif
                            <span class="badge bg-{{ $admission->status_badge_class }}">{{ $admission->status_label }}</span>
                        </div>
                        <p class="text-muted-hms small mb-0 mt-1">
                            <i class="bi bi-calendar3 me-1 text-teal"></i> Age: <span class="fw-semibold text-dark">{{ $patient->formatted_age }}</span> ({{ ucfirst($patient->gender) }}) &bull;
                            <i class="bi bi-droplet me-1 text-danger"></i> Blood Group: <span class="fw-bold text-dark">{{ $patient->blood_group ?? 'Unknown' }}</span> &bull;
                            <i class="bi bi-telephone me-1 text-teal"></i> {{ $patient->phone }} &bull;
                            <i class="bi bi-clock me-1 text-teal"></i> Admitted: <span class="fw-semibold text-dark">{{ $admission->admission_date?->format('d M Y, h:i A') }}</span> (Stay: {{ $admission->stay_duration }})
                        </p>
                    </div>
                </div>
                <div class="text-lg-end">
                    <div class="small text-muted-hms">Attending Physician:</div>
                    <div class="fw-bold text-navy fs-6">Dr. {{ $admission->attendingDoctor?->name ?? 'Unassigned' }}</div>
                    <small class="badge bg-secondary-subtle text-dark">{{ $admission->department?->name ?? 'Clinical Ward' }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Doctor's Approved Prescriptions (Strictly READ-ONLY for Nurses) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold text-navy mb-0">
                    <i class="bi bi-file-earmark-medical-fill text-teal me-2"></i>Doctor-Approved Prescriptions & Medication Orders
                </h6>
                <small class="text-muted-hms">Authorized clinical orders prescribed by the attending doctor (Read-Only access)</small>
            </div>
            <span class="badge bg-success-subtle text-success border border-success-subtle">
                <i class="bi bi-lock-fill me-1"></i> Clinical Records Protected
            </span>
        </div>
        <div class="card-body p-4 pt-0">
            @forelse($approvedPrescriptions as $rx)
                <div class="p-3 rounded-3 bg-light border mb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-navy px-2 py-1 font-monospace">{{ $rx->prescription_number }}</span>
                            <span class="fw-bold text-dark">Prescribed by Dr. {{ $rx->consultation?->doctor?->name ?? 'Physician' }}</span>
                        </div>
                        <div class="small text-muted-hms">
                            Approved: {{ $rx->approved_at?->format('d M Y, h:i A') ?? $rx->created_at->format('d M Y') }}
                        </div>
                    </div>
                    @if($rx->advice)
                        <div class="p-2 rounded bg-white border small text-muted-hms mb-2">
                            <strong>Doctor's Advice:</strong> {{ $rx->advice }}
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light text-navy">
                                <tr>
                                    <th>Medicine Name</th>
                                    <th>Dosage</th>
                                    <th>Frequency</th>
                                    <th>Duration</th>
                                    <th>Instructions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rx->items as $item)
                                    <tr>
                                        <td class="fw-bold text-dark">{{ $item->medicine_name }}</td>
                                        <td>{{ $item->dosage }}</td>
                                        <td>{{ $item->frequency }}</td>
                                        <td>{{ $item->duration }}</td>
                                        <td class="text-muted">{{ $item->instructions ?? 'As directed' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="text-center py-3 text-muted">
                    <i class="bi bi-file-earmark-x fs-3 d-block mb-1"></i>
                    No doctor-approved prescriptions found for this inpatient.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Bedside Vitals History Timeline -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold text-navy mb-0">
                <i class="bi bi-heart-pulse-fill text-danger me-2"></i>Bedside Vital Signs & Nursing Observations History
            </h6>
            <small class="text-muted-hms">Complete vital monitoring timeline recorded by ward nurses</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-navy small">
                        <tr>
                            <th class="ps-4">Recorded At & Shift</th>
                            <th>Attending Nurse</th>
                            <th>Temperature</th>
                            <th>Blood Pressure</th>
                            <th>Pulse Rate</th>
                            <th>SpO2</th>
                            <th>Respiratory Rate</th>
                            <th class="pe-4">Clinical Observations</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($nursingNotes as $note)
                            <tr class="{{ $note->hasAbnormalVitals() ? 'table-danger-subtle' : '' }}">
                                <td class="ps-4">
                                    <div class="fw-bold">{{ $note->recorded_at->format('d M Y, h:i A') }}</div>
                                    <small class="badge bg-secondary-subtle text-dark">{{ ucfirst($note->shift) }}</small>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">Sister {{ $note->nurse?->name }}</div>
                                </td>
                                <td>
                                    @if($note->temperature)
                                        <span class="{{ $note->isFever() ? 'text-danger fw-bold' : 'text-dark' }}">
                                            {{ $note->temperature }}°F
                                            @if($note->isFever()) <i class="bi bi-exclamation-circle-fill text-danger"></i> @endif
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $note->blood_pressure ?? '—' }}</span>
                                </td>
                                <td>
                                    @if($note->pulse_rate)
                                        <span class="{{ $note->isTachycardia() || $note->isBradycardia() ? 'text-danger fw-bold' : 'text-dark' }}">
                                            {{ $note->pulse_rate }} bpm
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($note->spo2)
                                        <span class="{{ $note->isHypoxia() ? 'text-danger fw-bold' : 'text-success fw-medium' }}">
                                            {{ $note->spo2 }}%
                                            @if($note->isHypoxia()) <i class="bi bi-exclamation-triangle-fill text-danger"></i> @endif
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $note->respiratory_rate ? $note->respiratory_rate . ' /min' : '—' }}</td>
                                <td class="pe-4">
                                    <div class="text-dark small">{{ $note->observations }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No bedside vitals recorded for this admission yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Medication Administration Records (MAR) History -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold text-navy mb-0">
                <i class="bi bi-capsule text-primary me-2"></i>Medication Administration Records (MAR) History
            </h6>
            <small class="text-muted-hms">Audited dose delivery log for this patient</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-navy small">
                        <tr>
                            <th class="ps-4">Administered Time</th>
                            <th>Medicine Name</th>
                            <th>Dose Given</th>
                            <th>Administered By</th>
                            <th>Status</th>
                            <th class="pe-4">Remarks / Patient Reaction</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($medicationAdministrations as $mar)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold">{{ $mar->administered_at->format('d M Y, h:i A') }}</div>
                                    <small class="text-muted-hms">{{ $mar->administered_at->diffForHumans() }}</small>
                                </td>
                                <td class="fw-bold text-navy">{{ $mar->medicine_name }}</td>
                                <td><span class="badge bg-secondary-subtle text-dark">{{ $mar->dose_given }}</span></td>
                                <td>Sister {{ $mar->nurse?->name }}</td>
                                <td>
                                    <span class="badge bg-{{ $mar->status_badge_class }}">
                                        {{ $mar->status_label }}
                                    </span>
                                </td>
                                <td class="pe-4">
                                    <span class="small text-muted">{{ $mar->remarks ?? 'Routine dose delivered' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No medication administrations logged for this patient yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.portal>

