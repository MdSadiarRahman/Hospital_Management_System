<x-layouts.portal title="Emergency Referral {{ $referral->referral_number }} — Meghna Clinic">
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
                        <li class="breadcrumb-item"><a href="{{ route('portal.referrals.index') }}" class="text-decoration-none">Emergency Referrals</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $referral->referral_number }}</li>
                    </ol>
                </nav>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">{{ $referral->facility_name }}</h1>
                    {!! $referral->status_badge !!}
                    {!! $referral->type_badge !!}
                    {!! $referral->critical_badge !!}
                </div>
                <div class="text-muted small mt-1 font-monospace">
                    Referral ID: {{ $referral->referral_number }} | Initiated: {{ $referral->referred_at?->format('d M Y, h:i A') }} by Dr. {{ $referral->authorizedBy?->name }}
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('portal.referrals.print', $referral) }}" target="_blank" class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i> Print Handover Slip
                </a>

                @if(auth()->user()->hasAnyRole(['doctor', 'nurse', 'administrator', 'super_admin']))
                    @if($referral->canBeDispatched())
                        <button type="button" class="btn btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#dispatchModal">
                            <i class="bi bi-truck me-1"></i> Dispatch (In Transit)
                        </button>
                    @endif

                    @if($referral->canBeTransferred())
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#completeTransferModal">
                            <i class="bi bi-check2-all me-1"></i> Confirm Handover
                        </button>
                    @endif

                    <button type="button" class="btn btn-outline-teal" data-bs-toggle="modal" data-bs-target="#updateAssessmentModal">
                        <i class="bi bi-clipboard2-pulse me-1"></i> Update Assessment
                    </button>
                @endif

                @if(auth()->user()->hasAnyRole(['doctor', 'administrator', 'super_admin']) && $referral->canBeCancelled())
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelReferralModal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                @endif
            </div>
        </div>

        {{-- Cancellation Banner if Cancelled --}}
        @if($referral->status === 'cancelled')
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                <div class="d-flex align-items-start">
                    <i class="bi bi-x-octagon-fill fs-4 me-3 text-danger"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Referral Cancelled</h6>
                        <p class="mb-1"><strong>Reason:</strong> {{ $referral->cancellation_reason }}</p>
                        <span class="small text-muted">
                            Cancelled by {{ $referral->cancelledBy?->name ?? 'Staff' }} on {{ $referral->cancelled_at?->format('d M Y, h:i A') }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Bento Grid Main Details --}}
        <div class="row g-4 mb-4">
            {{-- Patient Demographics --}}
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-navy"><i class="bi bi-person-heart text-danger me-2"></i>Patient Identity</span>
                        <span class="badge bg-light text-dark border">{{ $referral->patient?->patient_code }}</span>
                    </div>
                    <div class="card-body p-3">
                        <h5 class="fw-bold text-dark mb-1">{{ $referral->patient?->name }}</h5>
                        <div class="text-muted small mb-3">
                            {{ $referral->patient?->formatted_age }}, {{ ucfirst($referral->patient?->gender ?? '') }} | 
                            Blood: <strong class="text-danger">{{ $referral->patient?->blood_group ?? 'N/A' }}</strong>
                        </div>
                        <ul class="list-unstyled small mb-0">
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Originating Case:</span>
                                <span>
                                    @if($referral->admission)
                                        <span class="badge bg-light text-dark border">Inpatient Adm #{{ $referral->admission->admission_number }}</span>
                                    @elseif($referral->visit)
                                        <span class="badge bg-light text-dark border">Emergency Visit #{{ $referral->visit->visit_number }}</span>
                                    @else
                                        <span class="text-muted">Emergency OPD</span>
                                    @endif
                                </span>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Allocated Bed:</span>
                                <span>
                                    @if($referral->admission?->currentBedAssignment?->bed)
                                        <span class="badge bg-light text-dark border">{{ $referral->admission->currentBedAssignment->bed->bed_number }}</span>
                                    @else
                                        <span class="text-muted">None / Emergency Bay</span>
                                    @endif
                                </span>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Primary Phone:</span>
                                <strong>{{ $referral->patient?->phone ?? 'N/A' }}</strong>
                            </li>
                            <li class="d-flex justify-content-between py-1">
                                <span class="text-muted">Authorizing Doctor:</span>
                                <strong class="text-navy">Dr. {{ $referral->authorizedBy?->name }}</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Destination & Department --}}
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-navy"><i class="bi bi-hospital text-danger me-2"></i>Destination Center</span>
                        {!! $referral->type_badge !!}
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-buildings fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $referral->facility_name }}</h6>
                                <span class="text-muted small">{{ $referral->destination_department ?? 'Emergency / ICU' }}</span>
                            </div>
                        </div>
                        <ul class="list-unstyled small mb-0 mt-3">
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Requires ICU/CCU:</span>
                                <span>
                                    @if($referral->requires_icu)
                                        <span class="badge bg-danger">Yes (Intensive Care)</span>
                                    @else
                                        <span class="badge bg-light text-dark border">General Tertiary</span>
                                    @endif
                                </span>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Receiving Contact:</span>
                                <span>{{ $referral->receiving_doctor_contact ?: 'General Triage / RMO Desk' }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Referred At:</span>
                                <span>{{ $referral->referred_at?->format('d M Y, h:i A') }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-1">
                                <span class="text-muted">Transferred At:</span>
                                <span>{{ $referral->transferred_at ? $referral->transferred_at->format('d M Y, h:i A') : 'Pending Handover' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Transport & Ambulance --}}
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-navy"><i class="bi bi-truck text-danger me-2"></i>Transport & Transit Logistics</span>
                        <span class="badge {{ $referral->transport_arranged ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $referral->transport_arranged ? 'Arranged' : 'Pending' }}
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <ul class="list-unstyled small mb-0">
                            <li class="py-1 border-bottom">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">VEHICLE TYPE</span>
                                <strong class="text-dark">{{ $referral->transport_type ?: 'Not specified' }}</strong>
                            </li>
                            <li class="py-1 border-bottom">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">OXYGEN EN-ROUTE</span>
                                <span>
                                    @if($referral->oxygen_support_transit)
                                        <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Continuous O2 Cylinder Equipped</span>
                                    @else
                                        <span class="text-muted">Standard transit</span>
                                    @endif
                                </span>
                            </li>
                            <li class="py-1 border-bottom">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">ACCOMPANYING CLINICAL STAFF</span>
                                <span class="text-dark">{{ $referral->accompanying_staff ?: 'None / Family accompanied' }}</span>
                            </li>
                            <li class="py-1">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">DRIVER CONTACT</span>
                                <strong>{{ $referral->ambulance_driver_contact ?: 'N/A' }}</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Clinical Assessment, Interventions, & Referral Reason --}}
        <div class="row g-4 mb-4">
            {{-- Bedside Assessment & Vitals Snapshot --}}
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-navy"><i class="bi bi-heart-pulse text-danger me-2"></i>Emergency Assessment & Vitals Snapshot</span>
                        @if($referral->is_critical)
                            <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Critical</span>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        {{-- Vitals grid --}}
                        @php $v = $referral->vitals_snapshot ?? []; @endphp
                        <div class="row g-2 mb-3 p-3 bg-light rounded-3 text-center">
                            <div class="col-4">
                                <span class="text-muted small d-block">Blood Pressure</span>
                                <strong class="text-dark fs-6">{{ ($v['bp_systolic'] ?? '--') . '/' . ($v['bp_diastolic'] ?? '--') }}</strong>
                                <span class="text-muted small" style="font-size: 0.7rem;">mmHg</span>
                            </div>
                            <div class="col-4">
                                <span class="text-muted small d-block">Heart Rate</span>
                                <strong class="text-dark fs-6">{{ $v['heart_rate'] ?? '--' }}</strong>
                                <span class="text-muted small" style="font-size: 0.7rem;">bpm</span>
                            </div>
                            <div class="col-4">
                                <span class="text-muted small d-block">SpO2 (Oxygen)</span>
                                <strong class="{{ (isset($v['spo2']) && $v['spo2'] < 92) ? 'text-danger' : 'text-dark' }} fs-6">
                                    {{ isset($v['spo2']) ? $v['spo2'].'%' : '--' }}
                                </strong>
                            </div>
                            <div class="col-4 mt-2">
                                <span class="text-muted small d-block">Temperature</span>
                                <strong class="text-dark">{{ isset($v['temperature']) ? $v['temperature'].' °C' : '--' }}</strong>
                            </div>
                            <div class="col-4 mt-2">
                                <span class="text-muted small d-block">Resp. Rate</span>
                                <strong class="text-dark">{{ isset($v['respiratory_rate']) ? $v['respiratory_rate'].'/min' : '--' }}</strong>
                            </div>
                            <div class="col-4 mt-2">
                                <span class="text-muted small d-block">Consciousness</span>
                                <span class="badge bg-secondary">{{ ucfirst($v['consciousness_level'] ?? 'Alert') }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="fw-bold text-muted small text-uppercase">Emergency Assessment Narrative</h6>
                            <p class="text-dark mb-0">{!! nl2br(e($referral->emergency_assessment ?: 'No preliminary narrative entered.')) !!}</p>
                        </div>
                        <hr>
                        <div>
                            <h6 class="fw-bold text-muted small text-uppercase">Interventions Administered at Meghna Clinic</h6>
                            <p class="text-dark mb-0">{!! nl2br(e($referral->interventions_given ?: 'Standard bedside stabilization.')) !!}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Clinical Reason & Handover Notes --}}
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <span class="fw-bold text-navy"><i class="bi bi-journal-medical text-danger me-2"></i>Clinical Reason for External Transfer</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="p-3 bg-danger bg-opacity-10 border border-danger rounded-3 mb-3">
                            <h6 class="fw-bold text-danger mb-1">
                                <i class="bi bi-shield-exclamation me-1"></i>Tertiary Escalation Justification
                            </h6>
                            <p class="text-dark mb-0">{!! nl2br(e($referral->clinical_reason)) !!}</p>
                        </div>

                        <div class="mb-0">
                            <h6 class="fw-bold text-muted small text-uppercase">Clinical Handover & Transit Advice</h6>
                            <p class="text-dark mb-0">{!! nl2br(e($referral->referral_notes ?: 'Continuously monitor vitals and pulse oximetry during transit.')) !!}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Complete Referral Audit History --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title fw-bold text-navy mb-0">
                    <i class="bi bi-clock-history text-danger me-2"></i>Referral Tracking & Audit Trail
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="timeline">
                    @forelse($referral->histories as $hist)
                        <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                            <div class="rounded-circle bg-light border p-2 text-danger d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
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
                                @if($hist->notes)
                                    <div class="small text-muted mt-1">{{ $hist->notes }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">No audit events logged.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    {{-- MODAL 1: Dispatch in Transit --}}
    <div class="modal fade" id="dispatchModal" tabindex="-1" aria-labelledby="dispatchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('portal.referrals.dispatch', $referral) }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-warning bg-opacity-10">
                        <h5 class="modal-title fw-bold text-dark" id="dispatchModalLabel">
                            <i class="bi bi-truck me-2 text-warning"></i>Dispatch Patient in Transit
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="dispatch_transport_type" class="form-label fw-semibold">Ambulance / Transport Type <span class="text-danger">*</span></label>
                            <input type="text" name="transport_type" id="dispatch_transport_type" class="form-control" 
                                value="{{ $referral->transport_type ?: 'Clinic Ambulance' }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="dispatch_ambulance_driver" class="form-label fw-semibold">Driver Contact Number</label>
                            <input type="text" name="ambulance_driver_contact" id="dispatch_ambulance_driver" class="form-control" 
                                value="{{ $referral->ambulance_driver_contact }}" placeholder="017xxxxxxxx">
                        </div>
                        <div class="mb-3">
                            <label for="dispatch_accompanying_staff" class="form-label fw-semibold">Accompanying Clinical Staff</label>
                            <input type="text" name="accompanying_staff" id="dispatch_accompanying_staff" class="form-control" 
                                value="{{ $referral->accompanying_staff ?: 'Duty Nurse + Ward Boy' }}" placeholder="Staff names">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="oxygen_support_transit" id="dispatch_oxygen" value="1" {{ $referral->oxygen_support_transit ? 'checked' : '' }}>
                            <label class="form-check-label small" for="dispatch_oxygen">
                                Confirm Continuous Oxygen Cylinder Support is Active in Ambulance
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-warning text-dark fw-bold">Confirm Departure</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: Complete Transfer Handover --}}
    <div class="modal fade" id="completeTransferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('portal.referrals.complete', $referral) }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-success bg-opacity-10">
                        <h5 class="modal-title fw-bold text-success" id="transferModalLabel">
                            <i class="bi bi-check2-all me-2"></i>Confirm Tertiary Clinical Handover
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-info small mb-3">
                            Confirming this handover marks the patient successfully received at {{ $referral->facility_name }}. If the patient held an inpatient bed at Meghna Clinic, it will be automatically released and marked for cleaning.
                        </div>
                        <div class="mb-3">
                            <label for="transfer_time" class="form-label fw-semibold">Handover Timestamp</label>
                            <input type="datetime-local" name="transferred_at" id="transfer_time" class="form-control" 
                                value="{{ now()->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="mb-0">
                            <label for="handover_notes" class="form-label fw-semibold">Handover / Admission Confirmation Notes</label>
                            <textarea name="handover_notes" id="handover_notes" rows="3" class="form-control" 
                                placeholder="e.g. Received by Dr. ... at ICU Bed #03. Vitals stable on arrival. Clinical handover documents delivered."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success fw-bold">Complete Handover</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 3: Cancel Referral --}}
    <div class="modal fade" id="cancelReferralModal" tabindex="-1" aria-labelledby="cancelRefModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('portal.referrals.cancel', $referral) }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger bg-opacity-10">
                        <h5 class="modal-title fw-bold text-danger" id="cancelRefModalLabel">
                            <i class="bi bi-x-circle me-2"></i>Cancel Emergency Referral
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-0">
                            <label for="cancellation_reason" class="form-label fw-semibold">Cancellation Reason <span class="text-danger">*</span></label>
                            <textarea name="cancellation_reason" id="cancellation_reason" rows="3" class="form-control" 
                                placeholder="Explain reason for cancellation (patient condition stabilized locally, relative refused transfer, alternative management)..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Keep Active</button>
                        <button type="submit" class="btn btn-danger fw-bold">Confirm Cancellation</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 4: Update Assessment --}}
    <div class="modal fade" id="updateAssessmentModal" tabindex="-1" aria-labelledby="assessmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('portal.referrals.assessment.update', $referral) }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-white border-bottom">
                        <h5 class="modal-title fw-bold text-navy" id="assessmentModalLabel">
                            <i class="bi bi-clipboard2-pulse text-danger me-2"></i>Update Emergency Clinical Assessment
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        @php $v = $referral->vitals_snapshot ?? []; @endphp
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="form-label small text-muted">BP Systolic</label>
                                <input type="number" name="blood_pressure_systolic" class="form-control form-control-sm" value="{{ $v['bp_systolic'] ?? '' }}">
                            </div>
                            <div class="col-4">
                                <label class="form-label small text-muted">BP Diastolic</label>
                                <input type="number" name="blood_pressure_diastolic" class="form-control form-control-sm" value="{{ $v['bp_diastolic'] ?? '' }}">
                            </div>
                            <div class="col-4">
                                <label class="form-label small text-muted">Heart Rate (bpm)</label>
                                <input type="number" name="heart_rate" class="form-control form-control-sm" value="{{ $v['heart_rate'] ?? '' }}">
                            </div>
                            <div class="col-4 mt-2">
                                <label class="form-label small text-muted">SpO2 (%)</label>
                                <input type="number" name="spo2_percent" class="form-control form-control-sm" value="{{ $v['spo2'] ?? '' }}">
                            </div>
                            <div class="col-4 mt-2">
                                <label class="form-label small text-muted">Temp (°C)</label>
                                <input type="number" step="0.1" name="temperature_celsius" class="form-control form-control-sm" value="{{ $v['temperature'] ?? '' }}">
                            </div>
                            <div class="col-4 mt-2">
                                <label class="form-label small text-muted">Resp Rate (/min)</label>
                                <input type="number" name="respiratory_rate" class="form-control form-control-sm" value="{{ $v['respiratory_rate'] ?? '' }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="update_assessment" class="form-label fw-semibold">Assessment Findings <span class="text-danger">*</span></label>
                            <textarea name="emergency_assessment" id="update_assessment" rows="3" class="form-control" required>{{ $referral->emergency_assessment }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="update_interventions" class="form-label fw-semibold">Interventions Given</label>
                            <textarea name="interventions_given" id="update_interventions" rows="2" class="form-control">{{ $referral->interventions_given }}</textarea>
                        </div>
                        <div class="mb-0">
                            <label for="update_notes" class="form-label fw-semibold">Referral & Handover Notes</label>
                            <textarea name="referral_notes" id="update_notes" rows="2" class="form-control">{{ $referral->referral_notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger text-white fw-bold">Save Assessment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</x-layouts.portal>

