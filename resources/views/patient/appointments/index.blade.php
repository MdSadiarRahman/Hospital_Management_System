@extends('layouts.portal', [
    'title' => 'My Doctor Appointments',
    'activeNav' => 'appointments'
])

@section('content')
<div class="container-fluid py-4 px-md-4">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted" style="font-size: 0.85rem;">
                    <li class="breadcrumb-item"><a href="{{ route('patient.dashboard') }}" class="text-decoration-none text-muted">My Health Portal</a></li>
                    <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Appointments</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-navy mb-0">My Doctor Appointments</h1>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                View your upcoming doctor consultations, booking status, and reschedule or cancel requests.
            </p>
        </div>
        <div>
            <a href="{{ route('patient.appointments.create') }}" class="btn btn-hms-teal d-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-calendar-plus"></i>
                <span class="fw-semibold">Request New Appointment</span>
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
            <div>{{ session('status') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Status Tabs -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('patient.appointments.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-hms-primary fw-semibold' : 'btn-outline-secondary' }}">
            All Consultations
        </a>
        <a href="{{ route('patient.appointments.index', ['status' => 'pending']) }}" class="btn btn-sm {{ request('status') === 'pending' ? 'btn-warning fw-semibold' : 'btn-outline-secondary' }}">
            Pending Requests
        </a>
        <a href="{{ route('patient.appointments.index', ['status' => 'confirmed']) }}" class="btn btn-sm {{ request('status') === 'confirmed' ? 'btn-success fw-semibold' : 'btn-outline-secondary' }}">
            Confirmed
        </a>
        <a href="{{ route('patient.appointments.index', ['status' => 'rescheduled']) }}" class="btn btn-sm {{ request('status') === 'rescheduled' ? 'btn-info fw-semibold' : 'btn-outline-secondary' }}">
            Rescheduled
        </a>
        <a href="{{ route('patient.appointments.index', ['status' => 'completed']) }}" class="btn btn-sm {{ request('status') === 'completed' ? 'btn-primary fw-semibold' : 'btn-outline-secondary' }}">
            Completed
        </a>
        <a href="{{ route('patient.appointments.index', ['status' => 'cancelled']) }}" class="btn btn-sm {{ request('status') === 'cancelled' ? 'btn-danger fw-semibold' : 'btn-outline-secondary' }}">
            Cancelled
        </a>
    </div>

    <!-- Appointments Bento Cards List -->
    <div class="row g-3">
        @forelse($appointments as $appointment)
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 p-4 bg-white h-100 transition-hover">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <span class="badge rounded-pill px-2.5 py-1 mb-2 {{ $appointment->status_badge_class }}">
                            {{ $appointment->status_label }}
                        </span>
                        <h5 class="fw-bold text-navy mb-0">
                            Dr. {{ $appointment->doctor?->name }}
                        </h5>
                        <p class="text-muted small mb-0">{{ $appointment->doctor?->staffProfile?->designation ?? 'Consultant' }} &bull; <strong class="text-teal">{{ $appointment->department?->name }}</strong></p>
                    </div>
                    @if($appointment->serial_number)
                    <div class="text-end">
                        <span class="d-block small text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Serial</span>
                        <span class="badge bg-navy text-white fs-6 px-2.5 py-1">
                            #{{ $appointment->serial_number }}
                        </span>
                    </div>
                    @endif
                </div>

                <!-- Schedule Box -->
                <div class="bg-light p-3 rounded-3 d-flex align-items-center justify-content-between mb-3 small">
                    <div>
                        <div class="text-muted small">Scheduled Date & Time</div>
                        <div class="fw-bold text-navy fs-6">
                            <i class="bi bi-calendar-event text-teal me-1"></i>
                            {{ $appointment->appointment_date->format('d M Y') }} at {{ $appointment->appointment_time ? date('h:i A', strtotime($appointment->appointment_time)) : $appointment->appointment_date->format('h:i A') }}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small">Chamber</div>
                        <div class="fw-semibold text-navy">{{ $appointment->doctor?->staffProfile?->room_number ?? 'OPD Room 101' }}</div>
                    </div>
                </div>

                @if($appointment->reason)
                <div class="mb-3">
                    <small class="text-muted d-block">Reason / Symptoms:</small>
                    <span class="small text-navy">{{ Str::limit($appointment->reason, 120) }}</span>
                </div>
                @endif

                @if($appointment->cancellation_reason)
                <div class="alert alert-danger py-2 px-3 small mb-3">
                    <strong>Cancellation Note:</strong> {{ $appointment->cancellation_reason }}
                </div>
                @endif

                <!-- Action Footer -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-3 border-top mt-auto">
                    <small class="text-muted">Ref: MCNH-APT-{{ str_pad($appointment->id, 5, '0', STR_PAD_LEFT) }}</small>
                    <div class="d-flex gap-2">
                        @if($appointment->isConfirmed() || $appointment->isRescheduled())
                        <a href="{{ route('patient.appointments.print-slip', $appointment) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-printer me-1"></i> Print Slip
                        </a>
                        @endif

                        @can('update', $appointment)
                            @if($appointment->canBeRescheduled())
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#patientRescheduleModal{{ $appointment->id }}">
                                <i class="bi bi-arrow-repeat me-1"></i> Reschedule
                            </button>
                            @endif
                        @endcan

                        @can('delete', $appointment)
                            @if($appointment->canBeCancelled())
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#patientCancelModal{{ $appointment->id }}">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </button>
                            @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Patient Reschedule Modal -->
        @can('update', $appointment)
        @if($appointment->canBeRescheduled())
        <div class="modal fade" id="patientRescheduleModal{{ $appointment->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('patient.appointments.reschedule', $appointment) }}" method="POST">
                    @csrf
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-navy text-white" style="background-color: #123B5D;">
                            <h5 class="modal-title fs-6 fw-bold">Reschedule Appointment</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4 text-start">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-navy">Select New Date <span class="text-danger">*</span></label>
                                <input type="date" name="new_date" id="p_reschedule_date_{{ $appointment->id }}" class="form-control form-control-sm" min="{{ date('Y-m-d') }}" value="{{ $appointment->appointment_date->format('Y-m-d') }}" required onchange="loadPatientSlots({{ $appointment->id }}, {{ $appointment->doctor_id }})">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-navy">Select Time Slot <span class="text-danger">*</span></label>
                                <select name="new_time" id="p_reschedule_time_{{ $appointment->id }}" class="form-select form-select-sm" required>
                                    <option value="">Select date first</option>
                                </select>
                                <div id="p_slots_status_{{ $appointment->id }}" class="form-text small text-muted"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-navy">Reason for Rescheduling</label>
                                <textarea name="reschedule_reason" class="form-control form-control-sm" rows="2" placeholder="Brief note for doctor / reception..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-hms-primary fw-semibold">Save New Schedule</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif
        @endcan

        <!-- Patient Cancel Modal -->
        @can('delete', $appointment)
        @if($appointment->canBeCancelled())
        <div class="modal fade" id="patientCancelModal{{ $appointment->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('patient.appointments.cancel', $appointment) }}" method="POST">
                    @csrf
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title fs-6 fw-bold">Cancel Appointment</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4 text-start">
                            <p class="text-danger fw-semibold mb-2">Are you sure you want to cancel this consultation?</p>
                            <p class="small text-muted mb-3">Scheduled: <strong>{{ $appointment->appointment_date->format('d M Y h:i A') }}</strong> with Dr. {{ $appointment->doctor?->name }}</p>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-navy">Reason for Cancellation <span class="text-danger">*</span></label>
                                <textarea name="cancellation_reason" class="form-control form-control-sm" rows="3" placeholder="Please tell us why you are cancelling..." required minlength="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Keep Appointment</button>
                            <button type="submit" class="btn btn-sm btn-danger fw-semibold">Confirm Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif
        @endcan

        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 p-5 text-center bg-white">
                <i class="bi bi-calendar-check fs-1 text-teal d-block mb-3"></i>
                <h4 class="fw-bold text-navy mb-1">No Consultations Scheduled</h4>
                <p class="text-muted small mb-4">You do not have any appointments matching this category.</p>
                <div>
                    <a href="{{ route('patient.appointments.create') }}" class="btn btn-hms-teal px-4 fw-semibold shadow-sm">
                        <i class="bi bi-calendar-plus me-1"></i> Request Doctor Consultation
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    @if($appointments->hasPages())
    <div class="mt-4">
        {{ $appointments->links() }}
    </div>
    @endif
</div>

<script>
function loadPatientSlots(appointmentId, doctorId) {
    const dateInput = document.getElementById('p_reschedule_date_' + appointmentId);
    const timeSelect = document.getElementById('p_reschedule_time_' + appointmentId);
    const statusDiv = document.getElementById('p_slots_status_' + appointmentId);

    if (!dateInput || !timeSelect || !dateInput.value) return;

    timeSelect.innerHTML = '<option value="">Loading slots...</option>';
    timeSelect.disabled = true;

    fetch(`{{ route('api.appointments.slots') }}?doctor_id=${doctorId}&date=${dateInput.value}&ignore_appointment_id=${appointmentId}`)
        .then(res => res.json())
        .then(data => {
            timeSelect.innerHTML = '';
            timeSelect.disabled = false;

            if (!data.is_scheduled || !data.slots || data.slots.length === 0) {
                timeSelect.innerHTML = '<option value="">No available consultation slots</option>';
                if (statusDiv) statusDiv.textContent = data.message || 'Doctor not available.';
                return;
            }

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = '-- Select Slot --';
            timeSelect.appendChild(defaultOpt);

            data.slots.forEach(slot => {
                const opt = document.createElement('option');
                opt.value = slot.start;
                opt.textContent = `${slot.formatted} ${slot.is_available ? '' : '(' + slot.reason + ')'}`;
                if (!slot.is_available) opt.disabled = true;
                timeSelect.appendChild(opt);
            });

            if (statusDiv) statusDiv.textContent = 'Slots loaded for ' + data.day_of_week;
        });
}
</script>
@endsection
