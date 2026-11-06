<x-layouts.portal role="nurse" title="Inpatient eMAR Flowsheet — {{ $patient->name }}" activeNav="mar">
    <!-- Breadcrumb & Top Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 58px; height: 58px; font-size: 1.6rem;">
                        <i class="bi bi-calendar2-range-fill"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="fw-bold text-navy mb-0">Daily Inpatient eMAR Flowsheet</h4>
                            <span class="badge bg-teal px-2 py-1">{{ $admission->currentBed?->bed_number ?? 'Inpatient' }}</span>
                        </div>
                        <p class="text-muted-hms small mb-0 mt-1">
                            8-Hour shift dose tracking, authorized prescription verification, withheld reasoning, and bedside administration ledger
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <!-- Date Selector -->
                    <form method="GET" action="{{ route('portal.nurse.admissions.mar-flowsheet', $admission->id) }}" class="d-flex align-items-center gap-2">
                        <label class="form-label small text-muted mb-0 fw-semibold">Date:</label>
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ $targetDate->toDateString() }}" onchange="this.form.submit()">
                    </form>

                    <a href="{{ route('portal.nurse.admissions.dossier', $admission->id) }}" class="btn btn-sm btn-outline-teal">
                        <i class="bi bi-folder2-open me-1"></i> Patient Dossier
                    </a>
                    <a href="{{ route('portal.nurse.admissions.trends', $admission->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-graph-up me-1"></i> Vitals Trends
                    </a>
                    <a href="{{ route('portal.nurse.mar') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> eMAR Ledger
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages & Errors -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Medication Administration Error:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Inpatient Clinical Identity Banner -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-3 border-end-md">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-circle rounded-circle bg-light-primary text-primary fw-bold fs-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                            {{ strtoupper(substr($patient->name, 0, 1)) }}
                        </div>
                        <div>
                            <h5 class="fw-bold text-navy mb-0">{{ $patient->name }}</h5>
                            <span class="badge bg-light text-secondary border mt-1">{{ $patient->patient_code }}</span>
                            <div class="small text-muted mt-1">{{ $patient->formatted_age }} &bull; {{ ucfirst($patient->gender) }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 border-end-md">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Ward & Bed Location</div>
                    <div class="fw-bold text-teal fs-5 mb-0">
                        <i class="bi bi-door-open me-1"></i>{{ $admission->currentBed?->bed_number ?? 'No Bed Assigned' }}
                    </div>
                    <div class="small text-muted">{{ $admission->department?->name ?? 'General Ward' }} &bull; Room {{ $admission->currentBed?->room_number ?? '—' }}</div>
                </div>

                <div class="col-md-3 border-end-md">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Attending Physician</div>
                    <div class="fw-bold text-navy mb-0">
                        <i class="bi bi-person-badge me-1"></i>{{ $admission->attendingDoctor?->name ?? 'Unassigned Doctor' }}
                    </div>
                    <div class="small text-muted">Admission #{{ $admission->admission_number }}</div>
                </div>

                <div class="col-md-3">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Flowsheet Date</div>
                    <div class="fw-bold text-navy fs-6 mb-0">
                        <i class="bi bi-calendar-event me-1"></i>{{ $targetDate->format('l, d F Y') }}
                    </div>
                    @if($targetDate->isToday())
                        <span class="badge bg-success-subtle text-success border border-success-subtle mt-1">
                            <i class="bi bi-broadcast me-1"></i>Today's Live Flowsheet
                        </span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border mt-1">
                            Historical Archive
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Daily MAR Statistics KPI Bento -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-2">
            <x-stat-card 
                title="Scheduled Doses" 
                value="{{ $stats['total_scheduled'] }}" 
                icon="calendar-check" 
                color="primary"
                subtitle="Prescription orders"
            />
        </div>
        <div class="col-sm-6 col-xl-2">
            <x-stat-card 
                title="Administered" 
                value="{{ $stats['administered'] }}" 
                icon="check2-all" 
                color="success"
                subtitle="Signed & given"
            />
        </div>
        <div class="col-sm-6 col-xl-2">
            <x-stat-card 
                title="Withheld (Hold)" 
                value="{{ $stats['withheld'] }}" 
                icon="shield-slash" 
                color="warning"
                subtitle="Clinical hold reason"
            />
        </div>
        <div class="col-sm-6 col-xl-2">
            <x-stat-card 
                title="Missed Doses" 
                value="{{ $stats['missed'] }}" 
                icon="clock-history" 
                color="danger"
                subtitle="Interrupted / off ward"
            />
        </div>
        <div class="col-sm-6 col-xl-2">
            <x-stat-card 
                title="Patient Refusals" 
                value="{{ $stats['refused'] }}" 
                icon="slash-circle" 
                color="secondary"
                subtitle="Refused by patient"
            />
        </div>
        <div class="col-sm-6 col-xl-2">
            <x-stat-card 
                title="Pending / Due" 
                value="{{ $stats['pending'] }}" 
                icon="hourglass-split" 
                color="info"
                subtitle="Awaiting shift dose"
            />
        </div>
    </div>

    <!-- 1. Authorized Doctor Prescriptions (Strictly Read-Only) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded bg-teal-subtle text-teal p-2 d-flex align-items-center justify-content-center">
                    <i class="bi bi-file-earmark-medical-fill fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-navy mb-0">Authorized Doctor Prescriptions</h5>
                    <span class="text-muted small">Doctor clinical regimen is strictly read-only for nursing staff</span>
                </div>
            </div>
            <span class="badge bg-light text-navy border px-3 py-2">
                <i class="bi bi-lock-fill text-warning me-1"></i> Read-Only Clinical Orders
            </span>
        </div>

        <div class="card-body p-4">
            @forelse($approvedPrescriptions as $rx)
                <div class="border rounded-3 p-3 mb-3 bg-light-subtle">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-2 mb-3">
                        <div>
                            <span class="badge bg-success px-2 py-1 me-2">
                                <i class="bi bi-patch-check-fill me-1"></i>Approved Doctor Order
                            </span>
                            <span class="fw-bold text-navy">Prescription #{{ $rx->prescription_number }}</span>
                            <span class="text-muted small ms-2">Signed on {{ $rx->approved_at?->format('d M Y, h:i A') ?? $rx->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="small text-muted mt-2 mt-md-0">
                            <strong>Prescribing Physician:</strong> {{ $rx->consultation?->doctor?->name ?? 'Attending Doctor' }}
                        </div>
                    </div>

                    @if($rx->consultation && $rx->consultation->diagnoses->isNotEmpty())
                        <div class="mb-2 small">
                            <strong class="text-navy">Clinical Diagnoses:</strong>
                            @foreach($rx->consultation->diagnoses as $diag)
                                <span class="badge bg-light text-dark border me-1">{{ $diag->diagnosis_name }} ({{ $diag->icd10_code ?? 'Clinical' }})</span>
                            @endforeach
                        </div>
                    @endif

                    @if($rx->advice)
                        <div class="p-2 rounded bg-white border small text-muted mb-3">
                            <strong>Doctor's Advice:</strong> {{ $rx->advice }}
                        </div>
                    @endif

                    <!-- Prescribed Medications Table -->
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white align-middle mb-0">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th>Medicine Name</th>
                                    <th>Dosage</th>
                                    <th>Frequency</th>
                                    <th>Duration</th>
                                    <th>Doctor Instructions</th>
                                    <th>Assigned Shifts</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rx->items as $item)
                                    <tr>
                                        <td class="fw-bold text-teal">{{ $item->medicine_name }}</td>
                                        <td>{{ $item->dosage }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $item->frequency }}</span></td>
                                        <td>{{ $item->duration }}</td>
                                        <td class="small text-muted">{{ $item->instructions ?? 'Standard protocol' }}</td>
                                        <td>
                                            @php
                                                $shifts = app(\App\Services\NurseDashboardService::class)->parseFrequencyToShifts($item->frequency);
                                            @endphp
                                            @foreach($shifts as $s)
                                                <span class="badge bg-teal-subtle text-teal me-1 text-capitalize">{{ $s }}</span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-file-earmark-medical fs-1 d-block mb-2 text-secondary"></i>
                    <p class="mb-0 fw-semibold">No authorized doctor prescriptions found for this inpatient stay.</p>
                    <small>Medications must be prescribed and approved by the attending physician before administration.</small>
                </div>
            @endforelse
        </div>
    </div>

    <!-- 2. 8-Hour Shift Scheduled eMAR Grid -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded bg-teal text-white p-2 d-flex align-items-center justify-content-center">
                    <i class="bi bi-grid-3x3-gap-fill fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-navy mb-0">8-Hour Shift Medication Schedule</h5>
                    <span class="text-muted small">Tracking for {{ $targetDate->format('d M Y') }} across Morning, Evening, and Night shifts</span>
                </div>
            </div>
            <span class="badge bg-teal-subtle text-teal border px-3 py-2">
                <i class="bi bi-clock-history me-1"></i> Real-Time Nursing Administration
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th style="width: 25%;" class="ps-3">Prescribed Medicine & Order</th>
                        <th style="width: 25%;">
                            <div class="fw-bold text-navy">Morning Shift</div>
                            <small class="text-muted">08:00 AM – 04:00 PM</small>
                        </th>
                        <th style="width: 25%;">
                            <div class="fw-bold text-navy">Evening Shift</div>
                            <small class="text-muted">04:00 PM – 12:00 AM</small>
                        </th>
                        <th style="width: 25%;" class="pe-3">
                            <div class="fw-bold text-navy">Night Shift</div>
                            <small class="text-muted">12:00 AM – 08:00 AM</small>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($scheduledItems as $sched)
                        <tr>
                            <!-- Medicine & Order -->
                            <td class="ps-3 bg-light-subtle">
                                <div class="fw-bold text-teal fs-6">{{ $sched['item']->medicine_name }}</div>
                                <div class="small text-navy fw-semibold">
                                    {{ $sched['item']->dosage }} &bull; Freq: {{ $sched['item']->frequency }}
                                </div>
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-info-circle me-1"></i>{{ $sched['item']->instructions ?? 'Standard protocol' }}
                                </div>
                                <div class="small text-secondary mt-1">
                                    Rx #{{ $sched['prescription']->prescription_number }}
                                </div>
                            </td>

                            <!-- Shifts: Morning, Evening, Night -->
                            @foreach(['morning', 'evening', 'night'] as $shiftKey)
                                @php
                                    $slot = $sched['slots'][$shiftKey];
                                    $record = $slot['record'];
                                    $status = $slot['status'];
                                @endphp
                                <td>
                                    @if(!$slot['is_scheduled'])
                                        <div class="text-center py-3 text-muted">
                                            <span class="text-secondary opacity-50">—</span>
                                            <div class="small text-muted" style="font-size: 0.75rem;">Not scheduled</div>
                                        </div>
                                    @elseif($record)
                                        <!-- Dose Record Exists for this slot -->
                                        <div class="p-2 rounded border bg-white shadow-sm">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="badge bg-{{ $record->status_badge_class }} px-2 py-1">
                                                    {{ $record->status_label }}
                                                </span>
                                                <span class="small text-muted fw-bold">{{ $record->administered_at?->format('h:i A') }}</span>
                                            </div>

                                            <div class="small text-navy fw-semibold">
                                                Given: {{ $record->dose_given }} ({{ $record->route ?? 'Oral' }})
                                            </div>

                                            @if($record->status === 'withheld' && $record->withheld_reason)
                                                <div class="p-1 px-2 mt-1 rounded bg-warning-subtle text-dark small border border-warning-subtle" style="font-size: 0.75rem;">
                                                    <strong>Hold Reason:</strong> {{ $record->withheld_reason }}
                                                </div>
                                            @endif

                                            @if($record->remarks)
                                                <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                                    {{ $record->remarks }}
                                                </div>
                                            @endif

                                            <div class="d-flex align-items-center justify-content-between mt-2 pt-1 border-top" style="font-size: 0.75rem;">
                                                <span class="text-muted">
                                                    <i class="bi bi-person me-1"></i>{{ $record->nurse?->name ?? 'Nurse' }}
                                                </span>

                                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1" title="Amend Record" data-bs-toggle="modal" data-bs-target="#amendMarModal-{{ $record->id }}">
                                                    <i class="bi bi-pencil" style="font-size: 0.7rem;"></i> Amend
                                                </button>
                                            </div>

                                            @if($record->is_corrected)
                                                <div class="mt-1">
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 0.7rem;" title="Amended by {{ $record->correctedBy?->name }}: {{ $record->correction_reason }}">
                                                        <i class="bi bi-pencil-square me-1"></i>Amended
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <!-- Scheduled but not yet recorded (DUE) -->
                                        <div class="p-3 text-center rounded border border-warning-subtle bg-warning-subtle bg-opacity-25">
                                            <span class="badge bg-warning text-dark mb-2">
                                                <i class="bi bi-hourglass-split me-1"></i>Due / Pending
                                            </span>
                                            <div class="small text-muted mb-2">Prescribed: {{ $sched['item']->dosage }}</div>
                                            
                                            <button type="button" class="btn btn-sm btn-teal w-100 shadow-sm" data-bs-toggle="modal" data-bs-target="#recordSlotModal-{{ $sched['item']->id }}-{{ $shiftKey }}">
                                                <i class="bi bi-check2-circle me-1"></i>Administer / Hold
                                            </button>
                                        </div>

                                        <!-- Modal for this specific slot -->
                                        <div class="modal fade" id="recordSlotModal-{{ $sched['item']->id }}-{{ $shiftKey }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered text-start">
                                                <div class="modal-content border-0 shadow">
                                                    <form method="POST" action="{{ route('portal.nurse.medications.store', $admission->id) }}">
                                                        @csrf
                                                        <input type="hidden" name="prescription_id" value="{{ $sched['prescription']->id }}">
                                                        <input type="hidden" name="prescription_item_id" value="{{ $sched['item']->id }}">
                                                        <input type="hidden" name="medicine_name" value="{{ $sched['item']->medicine_name }}">
                                                        <input type="hidden" name="scheduled_shift" value="{{ $shiftKey }}">

                                                        <div class="modal-header bg-teal text-white">
                                                            <h5 class="modal-title fw-bold">
                                                                <i class="bi bi-capsule me-2"></i>Record Dose: {{ $sched['item']->medicine_name }}
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>

                                                        <div class="modal-body p-4">
                                                            <div class="alert alert-light border py-2 mb-3 small">
                                                                <strong>Order:</strong> {{ $sched['item']->medicine_name }} &bull; {{ $sched['item']->dosage }} &bull; {{ $sched['item']->frequency }}<br>
                                                                <strong>Shift:</strong> {{ ucfirst($shiftKey) }} Shift &bull; <strong>Date:</strong> {{ $targetDate->format('d M Y') }}
                                                            </div>

                                                            <div class="row g-2 mb-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label small fw-semibold text-navy">Dose Administered <span class="text-danger">*</span></label>
                                                                    <input type="text" name="dose_given" class="form-control" value="{{ $sched['item']->dosage }}" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label small fw-semibold text-navy">Route</label>
                                                                    <select name="route" class="form-select">
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

                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold text-navy">Administration Status <span class="text-danger">*</span></label>
                                                                <select name="status" class="form-select" id="slotStatusSelect-{{ $sched['item']->id }}-{{ $shiftKey }}" onchange="toggleSlotWithheldReason('{{ $sched['item']->id }}', '{{ $shiftKey }}')">
                                                                    <option value="administered" selected>Administered (Given)</option>
                                                                    <option value="withheld">Withheld (Clinical Hold)</option>
                                                                    <option value="missed">Missed Dose</option>
                                                                    <option value="refused">Refused by Patient</option>
                                                                    <option value="omitted">Omitted</option>
                                                                </select>
                                                            </div>

                                                            <div class="mb-3 d-none" id="slotWithheldReasonGroup-{{ $sched['item']->id }}-{{ $shiftKey }}">
                                                                <label class="form-label small fw-semibold text-navy">Withheld Reason <span class="text-danger">*</span></label>
                                                                <input type="text" name="withheld_reason" id="slotWithheldInput-{{ $sched['item']->id }}-{{ $shiftKey }}" class="form-control" placeholder="Clinical justification (e.g. Hypotension BP 85/50, NPO for surgery)...">
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold text-navy">Bedside Clinical Remarks</label>
                                                                <textarea name="remarks" class="form-control" rows="2" placeholder="Bedside notes or patient tolerance..."></textarea>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold text-navy">Administration Timestamp</label>
                                                                <input type="datetime-local" name="administered_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                                                            </div>
                                                        </div>

                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-sm btn-teal">
                                                                <i class="bi bi-check-circle me-1"></i>Save Record
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-capsule fs-1 d-block mb-2 text-secondary"></i>
                                <p class="mb-0 fw-bold">No Scheduled Medications Found for this Patient</p>
                                <small>Prescribe medication through the Doctor Consultation module to populate the eMAR flowsheet.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Complete Patient eMAR History & Audit Trail -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
            <div>
                <h5 class="fw-bold text-navy mb-0">Complete Inpatient eMAR Administration History</h5>
                <span class="text-muted small">All doses logged during admission #{{ $admission->admission_number }} with correction audit trail</span>
            </div>
            <span class="badge bg-light text-navy border px-3 py-2">
                <i class="bi bi-shield-check text-teal me-1"></i> Permanent Clinical Ledger
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Date & Time</th>
                        <th>Shift</th>
                        <th>Medication & Dose Given</th>
                        <th>Route</th>
                        <th>Status</th>
                        <th>Clinical Reason / Remarks</th>
                        <th>Responsible Nurse</th>
                        <th>Correction History</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $hist)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-navy">{{ $hist->administered_at?->format('d M Y, h:i A') }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-capitalize">
                                    {{ $hist->scheduled_shift ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-teal">{{ $hist->medicine_name }}</div>
                                <div class="small text-navy">Dose: {{ $hist->dose_given }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">{{ $hist->route ?? 'Oral' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $hist->status_badge_class }} px-2 py-1">
                                    {{ $hist->status_label }}
                                </span>
                            </td>
                            <td>
                                @if($hist->status === 'withheld' && $hist->withheld_reason)
                                    <div class="small p-1 px-2 rounded bg-warning-subtle text-dark border border-warning-subtle mb-1">
                                        <strong>Hold:</strong> {{ $hist->withheld_reason }}
                                    </div>
                                @endif
                                @if($hist->remarks)
                                    <div class="small text-muted">{{ $hist->remarks }}</div>
                                @endif
                                @if(!$hist->withheld_reason && !$hist->remarks)
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $hist->nurse?->name ?? 'Staff' }}</div>
                                <div class="small text-muted">{{ $hist->nurse?->staffProfile?->employee_id ?? 'Nurse' }}</div>
                            </td>
                            <td>
                                @if($hist->is_corrected)
                                    <div class="small text-info fw-semibold">
                                        <i class="bi bi-pencil-square me-1"></i>Amended by {{ $hist->correctedBy?->name ?? 'Staff' }}
                                    </div>
                                    <div class="small text-muted" style="font-size: 0.75rem;">
                                        Reason: {{ $hist->correction_reason }}
                                    </div>
                                    <div class="small text-muted" style="font-size: 0.7rem;">
                                        On: {{ $hist->corrected_at?->format('d M Y, h:i A') }}
                                    </div>
                                @else
                                    <span class="badge bg-light text-secondary border">Original Record</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No historical medication administration events recorded yet for this admission.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleSlotWithheldReason(itemId, shift) {
            const select = document.getElementById('slotStatusSelect-' + itemId + '-' + shift);
            const group = document.getElementById('slotWithheldReasonGroup-' + itemId + '-' + shift);
            const input = document.getElementById('slotWithheldInput-' + itemId + '-' + shift);
            if (select.value === 'withheld') {
                group.classList.remove('d-none');
                input.required = true;
            } else {
                group.classList.add('d-none');
                input.required = false;
            }
        }
    </script>
    @endpush
</x-layouts.portal>

