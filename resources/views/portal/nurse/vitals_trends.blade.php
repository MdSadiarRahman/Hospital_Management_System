<x-layouts.portal role="nurse" title="Patient Vital Trends & Trajectory — {{ $patient->name }}" activeNav="observations">
    <!-- Header & Navigation Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('portal.nurse.observations') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>All Observations</span>
            </a>
            <a href="{{ route('portal.nurse') }}" class="btn btn-sm btn-outline-teal d-inline-flex align-items-center gap-1">
                <i class="bi bi-hospital"></i>
                <span>Ward Station</span>
            </a>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('portal.nurse.admissions.flowsheet', $admission) }}" target="_blank" class="btn btn-sm btn-outline-teal d-inline-flex align-items-center gap-1">
                <i class="bi bi-printer"></i>
                <span>Print Official Flowsheet</span>
            </a>
            <button type="button" class="btn btn-sm btn-teal d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#recordVitalsModal">
                <i class="bi bi-heart-pulse"></i>
                <span>Record New Vitals</span>
            </button>
        </div>
    </div>

    <!-- Patient Header Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px; font-size: 1.6rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <h4 class="fw-bold text-navy mb-0">{{ $patient->name }}</h4>
                            <span class="badge bg-navy text-white px-2 py-1 font-monospace">{{ $patient->patient_code }}</span>
                            @if($admission->currentBed)
                                <span class="badge bg-teal text-white px-2 py-1 font-monospace">BED: {{ $admission->currentBed->bed_number }}</span>
                            @endif
                            <span class="badge bg-{{ $admission->status_badge_class }}">{{ $admission->status_label }}</span>
                        </div>
                        <p class="text-muted-hms small mb-0 mt-1">
                            <i class="bi bi-calendar3 me-1 text-teal"></i> Age: <span class="fw-semibold text-dark">{{ $patient->formatted_age }}</span> ({{ ucfirst($patient->gender) }}) &bull;
                            <i class="bi bi-droplet me-1 text-danger"></i> Blood Group: <span class="fw-bold text-dark">{{ $patient->blood_group ?? 'Unknown' }}</span> &bull;
                            <i class="bi bi-clock-history me-1 text-teal"></i> Stay Duration: <span class="fw-semibold text-dark">{{ $admission->stay_duration }}</span> (Admitted: {{ $admission->admission_date?->format('d M Y, h:i A') }})
                        </p>
                    </div>
                </div>
                <div class="text-lg-end">
                    <div class="small text-muted-hms">Attending Physician:</div>
                    <div class="fw-bold text-navy fs-6">Dr. {{ $admission->attendingDoctor?->name ?? 'Unassigned' }}</div>
                    <small class="badge bg-secondary-subtle text-dark">{{ $admission->department?->name ?? 'Ward' }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Vital Signs Statistical Trend Cards (Prompt Requirement: Store units & Vital trends) -->
    <div class="row g-3 mb-4">
        <!-- Body Temperature Trend -->
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="text-muted-hms small fw-semibold text-uppercase">Body Temperature</span>
                            <h3 class="fw-bold text-navy mb-0 mt-1">
                                {{ $stats['temperature']['current'] ?? '—' }} <small class="fs-6 text-muted">{{ $stats['temperature']['unit'] }}</small>
                            </h3>
                        </div>
                        <div class="rounded-circle bg-danger-subtle text-danger p-2">
                            <i class="bi bi-thermometer-half fs-4"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between text-muted-hms small border-top pt-2">
                        <span>Min: <strong class="text-dark">{{ $stats['temperature']['min'] ?? '—' }}</strong></span>
                        <span>Avg: <strong class="text-dark">{{ $stats['temperature']['avg'] ?? '—' }}</strong></span>
                        <span>Max: <strong class="text-dark">{{ $stats['temperature']['max'] ?? '—' }}</strong></span>
                    </div>
                    <div class="small text-muted mt-1">Safe zone: 97.0°F – 99.0°F (Fever ≥ 100.4°F)</div>
                </div>
            </div>
        </div>

        <!-- Blood Pressure Trend -->
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="text-muted-hms small fw-semibold text-uppercase">Blood Pressure</span>
                            <h3 class="fw-bold text-navy mb-0 mt-1">
                                {{ $stats['blood_pressure']['current'] ?? '—' }} <small class="fs-6 text-muted">{{ $stats['blood_pressure']['unit'] }}</small>
                            </h3>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary p-2">
                            <i class="bi bi-activity fs-4"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between text-muted-hms small border-top pt-2">
                        <span>Sys Range: <strong class="text-dark">{{ $stats['blood_pressure']['sys_min'] ?? '—' }} – {{ $stats['blood_pressure']['sys_max'] ?? '—' }}</strong></span>
                        <span>Dia Range: <strong class="text-dark">{{ $stats['blood_pressure']['dia_min'] ?? '—' }} – {{ $stats['blood_pressure']['dia_max'] ?? '—' }}</strong></span>
                    </div>
                    <div class="small text-muted mt-1">Normal target: 90/60 – 120/80 mmHg</div>
                </div>
            </div>
        </div>

        <!-- Pulse Rate (Heart Rate) Trend -->
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="text-muted-hms small fw-semibold text-uppercase">Pulse (Heart Rate)</span>
                            <h3 class="fw-bold text-navy mb-0 mt-1">
                                {{ $stats['pulse']['current'] ?? '—' }} <small class="fs-6 text-muted">{{ $stats['pulse']['unit'] }}</small>
                            </h3>
                        </div>
                        <div class="rounded-circle bg-teal-subtle text-teal p-2">
                            <i class="bi bi-heart-pulse-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between text-muted-hms small border-top pt-2">
                        <span>Min: <strong class="text-dark">{{ $stats['pulse']['min'] ?? '—' }}</strong></span>
                        <span>Avg: <strong class="text-dark">{{ $stats['pulse']['avg'] ?? '—' }}</strong></span>
                        <span>Max: <strong class="text-dark">{{ $stats['pulse']['max'] ?? '—' }}</strong></span>
                    </div>
                    <div class="small text-muted mt-1">Normal range: 60 – 100 bpm</div>
                </div>
            </div>
        </div>

        <!-- Oxygen Saturation (SpO2) Trend -->
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="text-muted-hms small fw-semibold text-uppercase">Oxygen Saturation (SpO2)</span>
                            <h3 class="fw-bold text-navy mb-0 mt-1">
                                {{ $stats['spo2']['current'] ?? '—' }} <small class="fs-6 text-muted">{{ $stats['spo2']['unit'] }}</small>
                            </h3>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success p-2">
                            <i class="bi bi-lungs-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between text-muted-hms small border-top pt-2">
                        <span>Min: <strong class="text-dark">{{ $stats['spo2']['min'] ?? '—' }}%</strong></span>
                        <span>Avg: <strong class="text-dark">{{ $stats['spo2']['avg'] ?? '—' }}%</strong></span>
                        <span>Max: <strong class="text-dark">{{ $stats['spo2']['max'] ?? '—' }}%</strong></span>
                    </div>
                    <div class="small text-muted mt-1">Target: 95% – 100% (&lt; 95% Hypoxia Alert)</div>
                </div>
            </div>
        </div>

        <!-- Respiratory Rate Trend -->
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="text-muted-hms small fw-semibold text-uppercase">Respiratory Rate</span>
                            <h3 class="fw-bold text-navy mb-0 mt-1">
                                {{ $stats['respiratory']['current'] ?? '—' }} <small class="fs-6 text-muted">{{ $stats['respiratory']['unit'] }}</small>
                            </h3>
                        </div>
                        <div class="rounded-circle bg-info-subtle text-info p-2">
                            <i class="bi bi-wind fs-4"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between text-muted-hms small border-top pt-2">
                        <span>Min: <strong class="text-dark">{{ $stats['respiratory']['min'] ?? '—' }}</strong></span>
                        <span>Avg: <strong class="text-dark">{{ $stats['respiratory']['avg'] ?? '—' }}</strong></span>
                        <span>Max: <strong class="text-dark">{{ $stats['respiratory']['max'] ?? '—' }}</strong></span>
                    </div>
                    <div class="small text-muted mt-1">Normal: 12 – 20 breaths/min</div>
                </div>
            </div>
        </div>

        <!-- Body Weight Trend -->
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="text-muted-hms small fw-semibold text-uppercase">Body Weight (Fluid Retention Check)</span>
                            <h3 class="fw-bold text-navy mb-0 mt-1">
                                {{ $stats['weight']['current'] ?? '—' }} <small class="fs-6 text-muted">{{ $stats['weight']['unit'] }}</small>
                            </h3>
                        </div>
                        <div class="rounded-circle bg-secondary-subtle text-dark p-2">
                            <i class="bi bi-speedometer fs-4"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between text-muted-hms small border-top pt-2">
                        <span>Min: <strong class="text-dark">{{ $stats['weight']['min'] ?? '—' }}</strong></span>
                        <span>Max: <strong class="text-dark">{{ $stats['weight']['max'] ?? '—' }}</strong></span>
                    </div>
                    <div class="small text-muted mt-1">Recorded where clinically appropriate</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visual Trajectory Trend Flowsheet (Responsive Chart & Points) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold text-navy mb-0">
                    <i class="bi bi-graph-up-arrow text-teal me-2"></i>Vital Signs Sequential Progression
                </h6>
                <small class="text-muted-hms">Chronological trajectory across recorded nursing shifts</small>
            </div>
            <span class="badge bg-navy text-white">{{ $timelineNotes->count() }} Checkpoints</span>
        </div>
        <div class="card-body p-4 pt-0">
            @if($timelineNotes->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle text-center mb-0" style="font-size: 0.8rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start ps-3" style="min-width: 130px;">Parameter</th>
                                @foreach($timelineNotes as $note)
                                    <th style="min-width: 95px;">
                                        <div>{{ $note->recorded_at->format('d M') }}</div>
                                        <small class="text-muted font-monospace">{{ $note->recorded_at->format('h:i A') }}</small>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Temp row -->
                            <tr>
                                <td class="text-start ps-3 fw-bold text-navy">
                                    <i class="bi bi-thermometer-half text-danger me-1"></i> Temperature (°F)
                                </td>
                                @foreach($timelineNotes as $note)
                                    <td class="{{ $note->isFever() ? 'bg-danger-subtle text-danger fw-bold' : '' }}">
                                        {{ $note->temperature ?? '—' }}
                                    </td>
                                @endforeach
                            </tr>
                            <!-- BP row -->
                            <tr>
                                <td class="text-start ps-3 fw-bold text-navy">
                                    <i class="bi bi-activity text-primary me-1"></i> Blood Pressure
                                </td>
                                @foreach($timelineNotes as $note)
                                    <td>{{ $note->blood_pressure ?? '—' }}</td>
                                @endforeach
                            </tr>
                            <!-- Pulse row -->
                            <tr>
                                <td class="text-start ps-3 fw-bold text-navy">
                                    <i class="bi bi-heart-pulse-fill text-teal me-1"></i> Pulse (bpm)
                                </td>
                                @foreach($timelineNotes as $note)
                                    <td class="{{ $note->isTachycardia() || $note->isBradycardia() ? 'bg-danger-subtle text-danger fw-bold' : '' }}">
                                        {{ $note->pulse_rate ?? '—' }}
                                    </td>
                                @endforeach
                            </tr>
                            <!-- SpO2 row -->
                            <tr>
                                <td class="text-start ps-3 fw-bold text-navy">
                                    <i class="bi bi-lungs-fill text-success me-1"></i> SpO2 (%)
                                </td>
                                @foreach($timelineNotes as $note)
                                    <td class="{{ $note->isHypoxia() ? 'bg-danger-subtle text-danger fw-bold' : 'text-success' }}">
                                        {{ $note->spo2 ? $note->spo2 . '%' : '—' }}
                                    </td>
                                @endforeach
                            </tr>
                            <!-- Resp row -->
                            <tr>
                                <td class="text-start ps-3 fw-bold text-navy">
                                    <i class="bi bi-wind text-info me-1"></i> Resp Rate (/min)
                                </td>
                                @foreach($timelineNotes as $note)
                                    <td>{{ $note->respiratory_rate ?? '—' }}</td>
                                @endforeach
                            </tr>
                            <!-- Weight row -->
                            <tr>
                                <td class="text-start ps-3 fw-bold text-navy">
                                    <i class="bi bi-speedometer text-secondary me-1"></i> Weight (kg)
                                </td>
                                @foreach($timelineNotes as $note)
                                    <td>{{ $note->weight ?? '—' }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    No vital trajectory recorded for this admission yet.
                </div>
            @endif
        </div>
    </div>

    <!-- Complete Chronological Observation History Table -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold text-navy mb-0">
                    <i class="bi bi-clock-history text-teal me-2"></i>Complete Bedside Observation History
                </h6>
                <small class="text-muted-hms">Audited clinical notes, responsible sisters, and physician review signatures</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-teal" data-bs-toggle="modal" data-bs-target="#recordVitalsModal">
                <i class="bi bi-plus-circle me-1"></i> Add Observation
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light text-navy small">
                        <tr>
                            <th class="ps-4">Recorded At & Shift</th>
                            <th>Responsible Nurse</th>
                            <th>Vitals Snapshot (Units)</th>
                            <th>Clinical Observations & Notes</th>
                            <th>Threshold Flags</th>
                            <th class="pe-4">Physician Review</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notes as $note)
                            <tr class="{{ $note->is_flagged_abnormal ? 'table-danger-subtle' : '' }}">
                                <td class="ps-4">
                                    <div class="fw-bold text-navy">{{ $note->recorded_at->format('d M Y') }}</div>
                                    <small class="text-muted-hms">{{ $note->recorded_at->format('h:i A') }} &bull; <span class="badge bg-secondary-subtle text-dark">{{ ucfirst($note->shift) }}</span></small>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">Sister {{ $note->nurse?->name }}</div>
                                    <small class="text-muted-hms">{{ $note->nurse?->staffProfile?->designation ?? 'Staff Nurse' }}</small>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2 font-monospace">
                                        @if($note->blood_pressure)
                                            <span class="badge bg-light text-dark border">BP: {{ $note->blood_pressure_display }}</span>
                                        @endif
                                        @if($note->pulse_rate)
                                            <span class="badge bg-light text-dark border">HR: {{ $note->pulse_display }}</span>
                                        @endif
                                        @if($note->temperature)
                                            <span class="badge {{ $note->isFever() ? 'bg-danger text-white' : 'bg-light text-dark border' }}">
                                                Temp: {{ $note->temperature_display }}
                                            </span>
                                        @endif
                                        @if($note->spo2)
                                            <span class="badge {{ $note->isHypoxia() ? 'bg-danger text-white' : 'bg-light text-success border' }}">
                                                SpO2: {{ $note->spo2_display }}
                                            </span>
                                        @endif
                                        @if($note->weight)
                                            <span class="badge bg-light text-dark border">Wt: {{ $note->weight_display }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="text-dark">{{ $note->observations }}</div>
                                </td>
                                <td>
                                    @if($note->is_flagged_abnormal && !empty($note->clinical_flags))
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($note->clinical_flags as $flag)
                                                <span class="badge bg-danger text-white py-0 px-1" style="font-size: 0.65rem;">
                                                    {{ $flag }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Normal Range</span>
                                    @endif
                                </td>
                                <td class="pe-4">
                                    <span class="badge {{ $note->review_status_badge_class }}">
                                        {{ $note->review_status_label }}
                                    </span>
                                    @if($note->reviewedBy)
                                        <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                            Dr. {{ $note->reviewedBy->name }} &bull; {{ $note->reviewed_at?->format('d M, h:i A') }}
                                        </div>
                                        @if($note->review_notes)
                                            <small class="text-muted d-block fst-italic">"{{ $note->review_notes }}"</small>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No observations recorded for this patient.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Record Vitals Modal -->
    <div class="modal fade" id="recordVitalsModal" tabindex="-1" aria-labelledby="recordVitalsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('portal.nurse.vitals.store', $admission) }}">
                    @csrf
                    <div class="modal-header bg-teal text-white">
                        <h5 class="modal-title fw-bold" id="recordVitalsModalLabel">
                            <i class="bi bi-heart-pulse-fill me-2"></i>Record Clinical Observation for {{ $patient->name }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="vitalsShift" class="form-label fw-semibold text-dark">Nursing Shift <span class="text-danger">*</span></label>
                                <select name="shift" id="vitalsShift" class="form-select" required>
                                    <option value="morning">Morning Shift (8 AM – 4 PM)</option>
                                    <option value="evening">Evening Shift (4 PM – 12 AM)</option>
                                    <option value="night">Night Shift (12 AM – 8 AM)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="vitalsTemp" class="form-label fw-semibold text-dark">Body Temperature</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" name="temperature" id="vitalsTemp" class="form-control" placeholder="e.g. 98.4" min="90.0" max="110.0">
                                    <select name="temperature_unit" class="form-select" style="max-width: 80px;">
                                        <option value="°F">°F</option>
                                        <option value="°C">°C</option>
                                    </select>
                                </div>
                                <small class="text-muted">Normal: 97.0°F - 99.0°F</small>
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
                                    <span class="input-group-text">bpm</span>
                                </div>
                                <small class="text-muted">Normal: 60 - 100 bpm</small>
                            </div>
                            <div class="col-md-4">
                                <label for="vitalsSpo2" class="form-label fw-semibold text-dark">SpO2 (Oxygen Saturation)</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" name="spo2" id="vitalsSpo2" class="form-control" placeholder="e.g. 98.5" min="50.0" max="100.0">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Normal: 95% - 100%</small>
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
                                <label for="vitalsWeight" class="form-label fw-semibold text-dark">Body Weight (Where appropriate)</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" name="weight" id="vitalsWeight" class="form-control" placeholder="e.g. 68.5" min="1.0" max="300.0">
                                    <span class="input-group-text">kg</span>
                                </div>
                                <small class="text-muted">Recorded for fluid balance / clinical tracking</small>
                            </div>
                            <div class="col-md-6">
                                <label for="vitalsTime" class="form-label fw-semibold text-dark">Observation Date & Time</label>
                                <input type="datetime-local" name="recorded_at" id="vitalsTime" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="vitalsObs" class="form-label fw-bold text-navy">Bedside Clinical Observations & Notes <span class="text-danger">*</span></label>
                            <textarea name="observations" id="vitalsObs" rows="3" class="form-control" required placeholder="Patient orientation, pain scale, wound assessment, IV fluid status, ambulation, clinical complaints..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-teal text-white">
                            <i class="bi bi-save me-1"></i> Save Clinical Observation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.portal>

