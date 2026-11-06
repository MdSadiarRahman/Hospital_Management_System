<x-layouts.portal title="Operation Dossier {{ $operation->operation_number }} — Meghna Clinic">
    <div class="container-fluid py-4">

        {{-- Flash Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <strong>Action blocked:</strong>
                </div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Header & Action Bar --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none">Portal</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('portal.operations.index') }}" class="text-decoration-none">Operation Theatre</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $operation->operation_number }}</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">{{ $operation->procedure_name }}</h1>
                    {!! $operation->status_badge !!}
                    {!! $operation->urgency_badge !!}
                </div>
                <div class="text-muted small mt-1 font-monospace">
                    ID: {{ $operation->operation_number }} | OT Suite: <strong class="text-dark">{{ $operation->ot_room }}</strong>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('portal.operations.print', $operation) }}" target="_blank" class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i> Print Slip
                </a>

                @if(auth()->user()->hasAnyRole(['doctor', 'nurse', 'administrator', 'super_admin']))
                    <button type="button" class="btn btn-outline-teal" data-bs-toggle="modal" data-bs-target="#postOpObservationModal">
                        <i class="bi bi-heart-pulse me-1"></i> Record Post-Op Observation
                    </button>
                @endif

                @if(auth()->user()->hasAnyRole(['doctor', 'administrator', 'super_admin']))
                    @if($operation->canBeStarted())
                        <form action="{{ route('portal.operations.start', $operation) }}" method="POST" class="d-inline" onsubmit="return confirm('Commence surgical procedure in {{ $operation->ot_room }}?');">
                            @csrf
                            <button type="submit" class="btn btn-warning text-dark">
                                <i class="bi bi-play-circle-fill me-1"></i> Start Procedure
                            </button>
                        </form>
                    @endif

                    @if($operation->status === 'in_progress')
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#completeOperationModal">
                            <i class="bi bi-check2-circle me-1"></i> Complete Procedure
                        </button>
                    @endif

                    @if($operation->canBeRescheduled())
                        <button type="button" class="btn btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                            <i class="bi bi-calendar2-range me-1"></i> Reschedule
                        </button>
                    @endif

                    @if($operation->canBeCancelled())
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </button>
                    @endif
                @endif
            </div>
        </div>

        {{-- Cancellation Banner if Cancelled --}}
        @if($operation->status === 'cancelled')
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                <div class="d-flex align-items-start">
                    <i class="bi bi-x-octagon-fill fs-4 me-3 text-danger"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Operation Cancelled</h6>
                        <p class="mb-1"><strong>Reason:</strong> {{ $operation->cancellation_reason }}</p>
                        <span class="small text-muted">
                            Cancelled by {{ $operation->cancelledBy?->name ?? 'Staff' }} on {{ $operation->cancelled_at?->format('d M Y, h:i A') }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Reschedule Notice if Rescheduled --}}
        @if($operation->status === 'rescheduled')
            <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4">
                <div class="d-flex align-items-start">
                    <i class="bi bi-calendar2-range-fill fs-4 me-3 text-warning"></i>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Operation Rescheduled</h6>
                        <p class="mb-1 text-dark"><strong>Reason:</strong> {{ $operation->rescheduled_reason }}</p>
                        <span class="small text-muted">
                            Rescheduled by {{ $operation->rescheduledBy?->name ?? 'Staff' }} on {{ $operation->rescheduled_at?->format('d M Y, h:i A') }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Bento Grid Main Layout --}}
        <div class="row g-4 mb-4">
            {{-- Patient Demographics --}}
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-navy"><i class="bi bi-person-heart text-teal me-2"></i>Patient Profile</span>
                        <span class="badge bg-light text-dark border">{{ $operation->patient?->patient_code }}</span>
                    </div>
                    <div class="card-body p-3">
                        <h5 class="fw-bold text-dark mb-1">{{ $operation->patient?->name }}</h5>
                        <div class="text-muted small mb-3">
                            {{ $operation->patient?->formatted_age }}, {{ ucfirst($operation->patient?->gender ?? '') }} | 
                            Blood Group: <strong class="text-danger">{{ $operation->patient?->blood_group ?? 'Unknown' }}</strong>
                        </div>
                        <ul class="list-unstyled small mb-0">
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Admission:</span>
                                <strong class="text-navy">{{ $operation->admission?->admission_number }}</strong>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Bed / Ward:</span>
                                <span>
                                    @if($operation->admission?->currentBedAssignment?->bed)
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-hospital me-1"></i>{{ $operation->admission->currentBedAssignment->bed->bed_number }}
                                        </span>
                                    @else
                                        <span class="text-muted">Not allocated</span>
                                    @endif
                                </span>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Admitted On:</span>
                                <span>{{ $operation->admission?->admission_date?->format('d M Y') }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-1">
                                <span class="text-muted">Contact Phone:</span>
                                <span>{{ $operation->patient?->phone ?? 'N/A' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- OT Suite & Time Window --}}
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-navy"><i class="bi bi-clock-history text-teal me-2"></i>OT Suite & Schedule</span>
                        <span class="badge bg-light text-teal border">{{ $operation->department?->name ?? 'General Surgery' }}</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="rounded-circle bg-teal bg-opacity-10 text-teal p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-door-closed fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $operation->ot_room }}</h6>
                                <span class="text-muted small">Operating Theatre Suite</span>
                            </div>
                        </div>
                        <ul class="list-unstyled small mb-0 mt-3">
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Scheduled Slot:</span>
                                <strong class="text-dark">{{ $operation->scheduled_at?->format('d M Y, h:i A') }}</strong>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Duration:</span>
                                <span>{{ $operation->estimated_duration_minutes }} minutes</span>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Started At:</span>
                                <span>{{ $operation->started_at ? $operation->started_at->format('d M Y, h:i A') : 'Not started' }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-1">
                                <span class="text-muted">Ended At:</span>
                                <span>{{ $operation->ended_at ? $operation->ended_at->format('d M Y, h:i A') : 'Pending completion' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Surgical Team --}}
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-navy"><i class="bi bi-people-fill text-teal me-2"></i>Surgical Team</span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border">Clinical Care</span>
                    </div>
                    <div class="card-body p-3">
                        <ul class="list-unstyled small mb-0">
                            <li class="py-2 border-bottom">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">PRIMARY SURGEON</span>
                                <strong class="text-navy fs-6">Dr. {{ $operation->primarySurgeon?->name }}</strong>
                            </li>
                            <li class="py-2 border-bottom">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">ASSISTANT SURGEON</span>
                                <span class="text-dark">{{ $operation->assistantSurgeon ? 'Dr. ' . $operation->assistantSurgeon->name : 'None / Solo Procedure' }}</span>
                            </li>
                            <li class="py-2 border-bottom">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">ANESTHETIST</span>
                                <span class="text-dark">{{ $operation->anesthetist_name ?: 'Pending Assignment' }}</span>
                            </li>
                            <li class="py-2">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">OT / SCRUB NURSE</span>
                                <span class="text-dark">{{ $operation->ot_nurse_name ?: 'Pending Assignment' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Clinical Indication & Operation Notes --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-navy"><i class="bi bi-file-medical text-teal me-2"></i>Clinical Indication & Pre-Op Notes</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <h6 class="fw-bold text-muted small text-uppercase">Pre-Operative Indication / Diagnosis</h6>
                            <p class="text-dark mb-0">{{ $operation->indication ?: 'No explicit pre-operative diagnosis recorded.' }}</p>
                        </div>
                        <hr>
                        <div>
                            <h6 class="fw-bold text-muted small text-uppercase">Pre-Op Orders & Preparation Plan</h6>
                            <p class="text-dark mb-0">{!! nl2br(e($operation->operation_notes ?: 'No preliminary orders entered.')) !!}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-navy"><i class="bi bi-journal-text text-teal me-2"></i>Operative Findings & Post-Op Instructions</span>
                        @if(auth()->user()->hasAnyRole(['doctor', 'administrator', 'super_admin']))
                            <button type="button" class="btn btn-outline-teal btn-sm" data-bs-toggle="modal" data-bs-target="#notesModal">
                                <i class="bi bi-pencil-square me-1"></i> Edit Notes
                            </button>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <h6 class="fw-bold text-muted small text-uppercase">Operative Findings & Procedure Summary</h6>
                            <p class="text-dark mb-0">{!! nl2br(e($operation->operation_notes ?: 'Surgical procedure notes will be recorded upon completion.')) !!}</p>
                        </div>
                        <hr>
                        <div>
                            <h6 class="fw-bold text-muted small text-uppercase">Immediate Post-Operative Instructions</h6>
                            <p class="text-dark mb-0">{!! nl2br(e($operation->post_op_notes ?: 'Recovery instructions pending procedure completion.')) !!}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Post-Operative Bedside Recovery Observations --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h5 class="card-title fw-bold text-navy mb-0">
                        <i class="bi bi-heart-pulse-fill text-danger me-2"></i>Post-Operative Recovery & Bedside Observations
                    </h5>
                    <span class="text-muted small">Sequential clinical recovery monitoring, vital signs, pain assessment, and wound monitoring.</span>
                </div>
                @if(auth()->user()->hasAnyRole(['doctor', 'nurse', 'administrator', 'super_admin']))
                    <button type="button" class="btn btn-teal text-white btn-sm" data-bs-toggle="modal" data-bs-target="#postOpObservationModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Observation
                    </button>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th scope="col" style="min-width: 150px;">Observed At</th>
                            <th scope="col" style="min-width: 180px;">Vital Signs</th>
                            <th scope="col" style="min-width: 140px;">Consciousness & Pain</th>
                            <th scope="col" style="min-width: 160px;">Site & Drains</th>
                            <th scope="col" style="min-width: 250px;">Clinical Notes</th>
                            <th scope="col" style="min-width: 140px;">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($operation->postOpObservations as $obs)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $obs->observed_at->format('d M Y') }}</div>
                                    <div class="text-muted small">{{ $obs->observed_at->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div class="small">
                                        @if($obs->blood_pressure)
                                            <span class="d-block">BP: <strong class="text-dark">{{ $obs->blood_pressure }}</strong></span>
                                        @endif
                                        @if($obs->heart_rate)
                                            <span class="d-inline-block me-2">Pulse: <strong class="text-dark">{{ $obs->heart_rate }} bpm</strong></span>
                                        @endif
                                        @if($obs->spo2_percent)
                                            <span class="d-inline-block">SpO2: <strong class="text-dark">{{ $obs->spo2_percent }}%</strong></span>
                                        @endif
                                        @if($obs->temperature_celsius)
                                            <span class="d-block">Temp: <strong class="text-dark">{{ $obs->temperature_celsius }} °C</strong></span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $obs->consciousness_level === 'alert' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ ucfirst($obs->consciousness_level) }}
                                    </span>
                                    @if(!is_null($obs->pain_score))
                                        <div class="small mt-1">Pain Score: <strong>{{ $obs->pain_score }}/10</strong></div>
                                    @endif
                                </td>
                                <td>
                                    <div class="small text-muted">
                                        @if($obs->surgical_site_condition)
                                            <div>Site: {{ $obs->surgical_site_condition }}</div>
                                        @endif
                                        @if($obs->drain_output_ml)
                                            <div>Drain: {{ $obs->drain_output_ml }} mL</div>
                                        @endif
                                        @if($obs->urine_output_ml)
                                            <div>Urine: {{ $obs->urine_output_ml }} mL</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <p class="mb-0 small text-dark">{!! nl2br(e($obs->observation_notes)) !!}</p>
                                    @if($obs->discharge_criteria_met)
                                        <span class="badge bg-success bg-opacity-10 text-success border mt-1">
                                            <i class="bi bi-check-circle me-1"></i>Recovery Criteria Met
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark">{{ $obs->recordedBy?->name }}</div>
                                    <span class="text-muted" style="font-size: 0.75rem;">{{ $obs->created_at->diffForHumans() }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted small">
                                    <i class="bi bi-clipboard2-pulse fs-3 d-block mb-1"></i>
                                    No post-operative bedside observations recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Complete Operation Audit History --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title fw-bold text-navy mb-0">
                    <i class="bi bi-clock-history text-teal me-2"></i>Operation History & Audit Trail
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="timeline">
                    @forelse($operation->histories as $hist)
                        <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                            <div class="rounded-circle bg-light border p-2 text-navy d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-check2"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="fw-bold mb-0 text-dark">{{ $hist->action_label }}</h6>
                                    <span class="text-muted small">{{ $hist->created_at->format('d M Y, h:i A') }}</span>
                                </div>
                                <div class="text-muted small mt-1">
                                    Action by <strong>{{ $hist->changedBy?->name }}</strong>
                                    @if($hist->previous_status || $hist->new_status)
                                        | Status: <span class="badge bg-light text-dark border">{{ $hist->previous_status ?: 'Initial' }} &rarr; {{ $hist->new_status }}</span>
                                    @endif
                                </div>
                                @if($hist->reason)
                                    <div class="small text-danger mt-1"><strong>Reason:</strong> {{ $hist->reason }}</div>
                                @endif
                                @if($hist->notes)
                                    <div class="small text-muted mt-1">{{ $hist->notes }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">No history events logged.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    {{-- MODAL 1: Reschedule Operation --}}
    <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('portal.operations.reschedule', $operation) }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-warning bg-opacity-10">
                        <h5 class="modal-title fw-bold text-dark" id="rescheduleModalLabel">
                            <i class="bi bi-calendar2-range me-2 text-warning"></i>Reschedule Operation
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="reschedule_scheduled_at" class="form-label fw-semibold">New Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="scheduled_at" id="reschedule_scheduled_at" class="form-control" 
                                value="{{ $operation->scheduled_at?->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="reschedule_duration" class="form-label fw-semibold">Duration (Minutes) <span class="text-danger">*</span></label>
                            <input type="number" name="estimated_duration_minutes" id="reschedule_duration" class="form-control" 
                                value="{{ $operation->estimated_duration_minutes }}" min="15" max="720" required>
                        </div>
                        <div class="mb-3">
                            <label for="reschedule_ot_room" class="form-label fw-semibold">OT Room</label>
                            <select name="ot_room" id="reschedule_ot_room" class="form-select">
                                @foreach($otRooms as $room)
                                    <option value="{{ $room }}" {{ $operation->ot_room === $room ? 'selected' : '' }}>{{ $room }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label for="rescheduled_reason" class="form-label fw-semibold">Reason for Rescheduling <span class="text-danger">*</span></label>
                            <textarea name="rescheduled_reason" id="rescheduled_reason" rows="3" class="form-control" 
                                placeholder="Explain reason for moving the operation (patient fever, emergency surgery priority, surgeon delay)..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-warning text-dark fw-bold">Confirm Reschedule</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: Cancel Operation --}}
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('portal.operations.cancel', $operation) }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger bg-opacity-10">
                        <h5 class="modal-title fw-bold text-danger" id="cancelModalLabel">
                            <i class="bi bi-x-circle me-2"></i>Cancel Operation
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-warning small mb-3">
                            Cancelling this operation will free up {{ $operation->ot_room }} and the surgeon's scheduled slot. This action will be logged in the permanent audit trail.
                        </div>
                        <div class="mb-0">
                            <label for="cancellation_reason" class="form-label fw-semibold">Cancellation Reason <span class="text-danger">*</span></label>
                            <textarea name="cancellation_reason" id="cancellation_reason" rows="3" class="form-control" 
                                placeholder="Explain why the surgery is being cancelled (patient refused, medically unfit, transferred to tertiary hospital)..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Keep Operation</button>
                        <button type="submit" class="btn btn-danger fw-bold">Confirm Cancellation</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 3: Complete Operation --}}
    <div class="modal fade" id="completeOperationModal" tabindex="-1" aria-labelledby="completeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('portal.operations.complete', $operation) }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-success bg-opacity-10">
                        <h5 class="modal-title fw-bold text-success" id="completeModalLabel">
                            <i class="bi bi-check2-circle me-2"></i>Complete Surgical Procedure
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="anesthetist_name" class="form-label fw-semibold">Anesthetist Name</label>
                                <input type="text" name="anesthetist_name" id="anesthetist_name" class="form-control" 
                                    value="{{ $operation->anesthetist_name }}" placeholder="Dr. Anesthesiologist">
                            </div>
                            <div class="col-md-6">
                                <label for="ot_nurse_name" class="form-label fw-semibold">OT / Scrub Nurse</label>
                                <input type="text" name="ot_nurse_name" id="ot_nurse_name" class="form-control" 
                                    value="{{ $operation->ot_nurse_name }}" placeholder="Sister in charge">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="complete_operation_notes" class="form-label fw-semibold">Operative Findings & Surgical Summary <span class="text-danger">*</span></label>
                            <textarea name="operation_notes" id="complete_operation_notes" rows="4" class="form-control" 
                                placeholder="Detailed findings, incision type, surgical technique, specimen collected, blood loss..." required>{{ $operation->operation_notes }}</textarea>
                        </div>

                        <div class="mb-0">
                            <label for="complete_post_op_notes" class="form-label fw-semibold">Immediate Post-Operative Instructions</label>
                            <textarea name="post_op_notes" id="complete_post_op_notes" rows="3" class="form-control" 
                                placeholder="Recovery ward orders, drain care, IV fluids, analgesia, vitals monitoring frequency...">{{ $operation->post_op_notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success fw-bold">Finish & Record Completion</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 4: Edit Surgical Notes --}}
    <div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('portal.operations.notes', $operation) }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-white border-bottom">
                        <h5 class="modal-title fw-bold text-navy" id="notesModalLabel">
                            <i class="bi bi-pencil-square text-teal me-2"></i>Update Operative Notes
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="edit_operation_notes" class="form-label fw-semibold">Surgical Procedure & Findings <span class="text-danger">*</span></label>
                            <textarea name="operation_notes" id="edit_operation_notes" rows="5" class="form-control" required>{{ $operation->operation_notes }}</textarea>
                        </div>
                        <div class="mb-0">
                            <label for="edit_post_op_notes" class="form-label fw-semibold">Post-Operative Recovery Instructions</label>
                            <textarea name="post_op_notes" id="edit_post_op_notes" rows="4" class="form-control">{{ $operation->post_op_notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-teal text-white fw-bold">Save Notes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 5: Post-Operative Bedside Observation --}}
    <div class="modal fade" id="postOpObservationModal" tabindex="-1" aria-labelledby="observationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('portal.operations.observations.store', $operation) }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-teal text-white">
                        <h5 class="modal-title fw-bold" id="observationModalLabel">
                            <i class="bi bi-heart-pulse me-2"></i>Record Post-Operative Bedside Observation
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="observed_at" class="form-label fw-semibold">Observation Timestamp</label>
                                <input type="datetime-local" name="observed_at" id="observed_at" class="form-control" 
                                    value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="consciousness_level" class="form-label fw-semibold">Consciousness Level <span class="text-danger">*</span></label>
                                <select name="consciousness_level" id="consciousness_level" class="form-select" required>
                                    <option value="alert" selected>Alert & Responsive</option>
                                    <option value="drowsy">Drowsy but rousable</option>
                                    <option value="stupor">Stupor</option>
                                    <option value="unconscious">Unconscious</option>
                                </select>
                            </div>
                        </div>

                        {{-- Vital Signs Grid --}}
                        <h6 class="fw-bold text-navy small text-uppercase mb-2">Vital Signs</h6>
                        <div class="row g-2 mb-3 p-3 bg-light rounded-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label small text-muted mb-1">BP Systolic</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="blood_pressure_systolic" class="form-control" placeholder="120">
                                    <span class="input-group-text">mmHg</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small text-muted mb-1">BP Diastolic</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="blood_pressure_diastolic" class="form-control" placeholder="80">
                                    <span class="input-group-text">mmHg</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small text-muted mb-1">Heart Rate</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="heart_rate" class="form-control" placeholder="72">
                                    <span class="input-group-text">bpm</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small text-muted mb-1">SpO2 Oxygen</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="spo2_percent" class="form-control" placeholder="98" min="50" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 mt-2">
                                <label class="form-label small text-muted mb-1">Temperature</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.1" name="temperature_celsius" class="form-control" placeholder="37.0">
                                    <span class="input-group-text">°C</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 mt-2">
                                <label class="form-label small text-muted mb-1">Respiratory Rate</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="respiratory_rate" class="form-control" placeholder="18">
                                    <span class="input-group-text">/min</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 mt-2">
                                <label class="form-label small text-muted mb-1">Pain Score (0 to 10)</label>
                                <input type="number" name="pain_score" class="form-control form-control-sm" placeholder="0 = None, 10 = Worst" min="0" max="10">
                            </div>
                        </div>

                        {{-- Surgical Site & Outputs --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="surgical_site_condition" class="form-label fw-semibold">Surgical Site & Dressing</label>
                                <input type="text" name="surgical_site_condition" id="surgical_site_condition" class="form-control" 
                                    placeholder="e.g. Dressing clean, dry, and intact. No active bleeding.">
                            </div>
                            <div class="col-md-3">
                                <label for="drain_output_ml" class="form-label fw-semibold">Drain Output</label>
                                <div class="input-group">
                                    <input type="number" name="drain_output_ml" id="drain_output_ml" class="form-control" placeholder="0">
                                    <span class="input-group-text">mL</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="urine_output_ml" class="form-label fw-semibold">Urine Output</label>
                                <div class="input-group">
                                    <input type="number" name="urine_output_ml" id="urine_output_ml" class="form-control" placeholder="0">
                                    <span class="input-group-text">mL</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="observation_notes" class="form-label fw-semibold">Recovery Observations & Nursing Notes <span class="text-danger">*</span></label>
                            <textarea name="observation_notes" id="observation_notes" rows="3" class="form-control" 
                                placeholder="Patient recovery status, vomiting/nausea, bowel sounds, IV fluid infusion, mobilization..." required></textarea>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="discharge_criteria_met" id="discharge_criteria_met" value="1">
                            <label class="form-check-label small" for="discharge_criteria_met">
                                Post-Anesthesia Recovery Room (PACU) discharge criteria met (ready for ward transfer).
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-teal text-white fw-bold">Record Observation</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</x-layouts.portal>

