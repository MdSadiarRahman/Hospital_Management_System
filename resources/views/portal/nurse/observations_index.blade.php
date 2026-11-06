<x-layouts.portal role="nurse" title="Clinical Nursing Observations & Vitals Ledger — Meghna Clinic" activeNav="observations">
    <!-- Header -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 58px; height: 58px; font-size: 1.6rem;">
                        <i class="bi bi-clipboard2-pulse-fill"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-navy mb-0">Clinical Nursing Observations & Vitals Ledger</h4>
                        <p class="text-muted-hms small mb-0 mt-1">
                            Chronological bedside clinical observations, physiological parameters, stored units, and clinical review flags
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-teal d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#recordVitalsModal">
                        <i class="bi bi-plus-circle"></i>
                        <span>Record Observation</span>
                    </button>
                    <a href="{{ route('portal.nurse') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Nursing Station
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Metric KPI Bento -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <x-stat-card 
                title="Total Observations Logged" 
                value="{{ $totalObservations }} Notes" 
                icon="journal-text" 
                color="primary"
                trend="All Inpatients"
                trendUp="true"
                subtitle="Complete observation history"
            />
        </div>
        <div class="col-sm-6 col-xl-4">
            <x-stat-card 
                title="Flagged Abnormal Readings" 
                value="{{ $flaggedCount }} Flagged" 
                icon="exclamation-octagon" 
                color="danger"
                trend="{{ $pendingReviewCount }} pending physician review"
                trendUp="false"
                subtitle="Physiological thresholds flagged"
            />
        </div>
        <div class="col-sm-6 col-xl-4">
            <x-stat-card 
                title="Attending Clinical Review" 
                value="{{ $totalObservations - $pendingReviewCount }} Verified" 
                icon="shield-check" 
                color="success"
                trend="Doctor oversight maintained"
                trendUp="true"
                subtitle="No automated medical diagnosis"
            />
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('portal.nurse.observations') }}" class="row g-2 align-items-center">
                <!-- Inpatient Dropdown -->
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Inpatient / Bed</label>
                    <select name="admission_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Inpatients</option>
                        @foreach($activeAdmissions as $adm)
                            <option value="{{ $adm->id }}" @selected(($filters['admission_id'] ?? '') == $adm->id)>
                                {{ $adm->currentBed?->bed_number ?? 'Bed ?' }} — {{ $adm->patient?->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Shift Filter -->
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Shift</label>
                    <select name="shift" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Shifts</option>
                        <option value="morning" @selected(($filters['shift'] ?? '') === 'morning')>Morning (8 AM – 4 PM)</option>
                        <option value="evening" @selected(($filters['shift'] ?? '') === 'evening')>Evening (4 PM – 12 AM)</option>
                        <option value="night" @selected(($filters['shift'] ?? '') === 'night')>Night (12 AM – 8 AM)</option>
                    </select>
                </div>

                <!-- Flag Status -->
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Observation Status</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Observations</option>
                        <option value="flagged" @selected(($filters['status'] ?? '') === 'flagged')>Flagged Abnormal Only</option>
                        <option value="normal" @selected(($filters['status'] ?? '') === 'normal')>Normal Values Only</option>
                        <option value="reviewed" @selected(($filters['status'] ?? '') === 'reviewed')>Doctor Reviewed</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] ?? '' }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] ?? '' }}" onchange="this.form.submit()">
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <a href="{{ route('portal.nurse.observations') }}" class="btn btn-sm btn-outline-secondary w-100" title="Reset Filters">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Master Observations Table -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-navy mb-0">
                <i class="bi bi-table text-teal me-2"></i>Bedside Observations & Measured Values (With Stored Units)
            </h6>
            <span class="text-muted-hms small">Showing {{ $observations->firstItem() ?? 0 }} - {{ $observations->lastItem() ?? 0 }} of {{ $observations->total() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light text-navy small">
                        <tr>
                            <th class="ps-3">Date / Time & Shift</th>
                            <th>Inpatient & Bed</th>
                            <th>Responsible Nurse</th>
                            <th>Blood Pressure</th>
                            <th>Pulse Rate</th>
                            <th>Temperature</th>
                            <th>SpO2</th>
                            <th>Resp Rate</th>
                            <th>Weight</th>
                            <th>Clinical Notes & Flags</th>
                            <th>Review Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($observations as $obs)
                            @php
                                $adm = $obs->admission;
                                $pt = $adm?->patient;
                                $bed = $adm?->currentBed;
                                $isFlagged = $obs->is_flagged_abnormal;
                            @endphp
                            <tr class="{{ $isFlagged ? 'table-danger-subtle' : '' }}">
                                <td class="ps-3">
                                    <div class="fw-bold text-navy">{{ $obs->recorded_at->format('d M Y') }}</div>
                                    <div class="small text-muted-hms">
                                        {{ $obs->recorded_at->format('h:i A') }} &bull; <span class="badge bg-secondary-subtle text-dark">{{ ucfirst($obs->shift) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-navy">{{ $pt?->name }}</div>
                                    <small class="text-muted-hms font-monospace">
                                        {{ $pt?->patient_code }} &bull; 
                                        @if($bed)
                                            <span class="badge bg-navy px-1 py-0">{{ $bed->bed_number }}</span>
                                        @else
                                            <span class="text-muted">No Bed</span>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">Sister {{ $obs->nurse?->name }}</div>
                                    <small class="text-muted-hms">{{ $obs->nurse?->staffProfile?->designation ?? 'Staff Nurse' }}</small>
                                </td>
                                <td>
                                    <span class="fw-bold font-monospace text-dark">{{ $obs->blood_pressure_display }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold font-monospace {{ $obs->isTachycardia() || $obs->isBradycardia() ? 'text-danger' : 'text-dark' }}">
                                        {{ $obs->pulse_display }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold font-monospace {{ $obs->isFever() ? 'text-danger' : 'text-dark' }}">
                                        {{ $obs->temperature_display }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold font-monospace {{ $obs->isHypoxia() ? 'text-danger' : 'text-success' }}">
                                        {{ $obs->spo2_display }}
                                    </span>
                                </td>
                                <td>
                                    <span class="font-monospace text-dark">{{ $obs->respiratory_rate_display }}</span>
                                </td>
                                <td>
                                    <span class="font-monospace text-dark">{{ $obs->weight_display ?? '—' }}</span>
                                </td>
                                <td>
                                    <div class="text-dark text-truncate" style="max-width: 220px;" title="{{ $obs->observations }}">
                                        {{ $obs->observations }}
                                    </div>
                                    @if($isFlagged && !empty($obs->clinical_flags))
                                        <div class="mt-1 d-flex flex-wrap gap-1">
                                            @foreach($obs->clinical_flags as $flag)
                                                <span class="badge bg-danger text-white py-0 px-1" style="font-size: 0.65rem;">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $flag }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $obs->review_status_badge_class }}">
                                        {{ $obs->review_status_label }}
                                    </span>
                                    @if($obs->reviewedBy)
                                        <div class="text-muted small mt-1" style="font-size: 0.7rem;">
                                            By Dr. {{ $obs->reviewedBy->name }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        @if($adm)
                                            <a href="{{ route('portal.nurse.admissions.trends', $adm) }}" class="btn btn-outline-teal" title="View Patient Vital Trends">
                                                <i class="bi bi-graph-up"></i>
                                            </a>
                                        @endif
                                        @if($isFlagged && $obs->clinical_review_status === 'flagged_for_review' && auth()->user()->hasRole(['doctor', 'super_admin', 'administrator']))
                                            <button type="button" class="btn btn-outline-danger" 
                                                onclick="openReviewModal({{ $obs->id }}, '{{ $pt?->name }}', '{{ addslashes(implode(', ', $obs->clinical_flags ?? [])) }}')"
                                                title="Physician Review Flagged Reading">
                                                <i class="bi bi-check2-circle"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-4 text-muted">
                                    <i class="bi bi-clipboard-x fs-2 d-block mb-1"></i>
                                    No nursing observations match the selected criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($observations->hasPages())
            <div class="card-footer bg-white py-2 border-0">
                {{ $observations->links() }}
            </div>
        @endif
    </div>

    <!-- Doctor Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="reviewForm" method="POST" action="">
                    @csrf
                    <div class="modal-header bg-navy text-white">
                        <h5 class="modal-title fw-bold" id="reviewModalLabel">
                            <i class="bi bi-shield-check me-2"></i>Physician Clinical Verification of Flagged Observation
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-warning mb-3">
                            <div class="fw-bold" id="reviewPatientName">Patient: </div>
                            <div class="small" id="reviewFlags">Clinical Flags: </div>
                        </div>
                        <div class="mb-3">
                            <label for="reviewNotesInput" class="form-label fw-semibold text-dark">Physician Clinical Remarks & Directives</label>
                            <textarea name="review_notes" id="reviewNotesInput" rows="3" class="form-control" placeholder="Clinical evaluation: adjust IV rate, administer antipyretic, monitor SpO2 every 2 hours..."></textarea>
                        </div>
                        <small class="text-muted d-block">
                            <i class="bi bi-info-circle me-1"></i> Verification logs your physician signature into the clinical audit trail.
                        </small>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check2-circle me-1"></i> Verify Flagged Observation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openReviewModal(noteId, patientName, flags) {
            const form = document.getElementById('reviewForm');
            if (form) {
                form.action = `/portal/nursing-observations/${noteId}/review`;
            }
            document.getElementById('reviewPatientName').textContent = 'Patient: ' + patientName;
            document.getElementById('reviewFlags').textContent = 'Abnormal Criteria: ' + flags;
            const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
            modal.show();
        }
    </script>
</x-layouts.portal>

