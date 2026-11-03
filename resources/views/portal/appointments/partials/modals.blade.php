<!-- Confirm Modal -->
@can('approve', $appointment)
@if($appointment->canBeConfirmed())
<div class="modal fade" id="confirmModal{{ $appointment->id }}" tabindex="-1" aria-labelledby="confirmModalLabel{{ $appointment->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('portal.appointments.confirm', $appointment) }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fs-6 fw-bold" id="confirmModalLabel{{ $appointment->id }}">
                        <i class="bi bi-check2-circle me-1"></i> Confirm Appointment #{{ $appointment->id }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <p class="mb-3">
                        Are you sure you want to approve and confirm the consultation for 
                        <strong>{{ $appointment->patient_display_name }}</strong>?
                    </p>
                    <div class="bg-light p-3 rounded-3 mb-3 small">
                        <div><strong>Doctor:</strong> {{ $appointment->doctor?->name }}</div>
                        <div><strong>Department:</strong> {{ $appointment->department?->name }}</div>
                        <div><strong>Date:</strong> {{ $appointment->appointment_date->format('d M Y') }} at {{ $appointment->appointment_time ? date('h:i A', strtotime($appointment->appointment_time)) : $appointment->appointment_date->format('h:i A') }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-navy">Internal Approval Notes (Optional)</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="e.g. Phone confirmed with patient guardian..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success fw-semibold">
                        <i class="bi bi-check2"></i> Confirm Appointment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endcan

<!-- Reschedule Modal -->
@can('update', $appointment)
@if($appointment->canBeRescheduled())
<div class="modal fade" id="rescheduleModal{{ $appointment->id }}" tabindex="-1" aria-labelledby="rescheduleModalLabel{{ $appointment->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('portal.appointments.reschedule', $appointment) }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-navy text-white" style="background-color: #123B5D;">
                    <h5 class="modal-title fs-6 fw-bold" id="rescheduleModalLabel{{ $appointment->id }}">
                        <i class="bi bi-arrow-repeat me-1"></i> Reschedule Appointment #{{ $appointment->id }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <div class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-info-circle-fill"></i>
                        <div>Current Schedule: <strong>{{ $appointment->appointment_date->format('d M Y') }} at {{ $appointment->appointment_time ? date('h:i A', strtotime($appointment->appointment_time)) : $appointment->appointment_date->format('h:i A') }}</strong> with {{ $appointment->doctor?->name }}</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-navy">Select New Date <span class="text-danger">*</span></label>
                            <input type="date" name="new_date" id="reschedule_date_{{ $appointment->id }}" class="form-control form-control-sm" min="{{ date('Y-m-d') }}" value="{{ $appointment->appointment_date->format('Y-m-d') }}" required onchange="loadRescheduleSlots({{ $appointment->id }}, {{ $appointment->doctor_id }})">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-navy">Select Available Time Slot <span class="text-danger">*</span></label>
                            <select name="new_time" id="reschedule_time_{{ $appointment->id }}" class="form-select form-select-sm" required>
                                <option value="">Select date first to view slots</option>
                            </select>
                            <div id="reschedule_slots_status_{{ $appointment->id }}" class="form-text small text-muted"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-navy">Reason for Rescheduling</label>
                            <textarea name="reschedule_reason" class="form-control form-control-sm" rows="2" placeholder="e.g. Patient requested morning shift, doctor unavailable on previous date..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-hms-primary fw-semibold">
                        <i class="bi bi-check2-circle"></i> Save Rescheduled Appointment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endcan

<!-- Cancel Modal -->
@can('delete', $appointment)
@if($appointment->canBeCancelled())
<div class="modal fade" id="cancelModal{{ $appointment->id }}" tabindex="-1" aria-labelledby="cancelModalLabel{{ $appointment->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('portal.appointments.cancel', $appointment) }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fs-6 fw-bold" id="cancelModalLabel{{ $appointment->id }}">
                        <i class="bi bi-x-circle me-1"></i> Cancel Appointment #{{ $appointment->id }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <p class="text-danger fw-semibold mb-2">
                        Are you sure you want to cancel this appointment?
                    </p>
                    <p class="small text-muted mb-3">
                        Patient: <strong>{{ $appointment->patient_display_name }}</strong><br>
                        Scheduled: {{ $appointment->appointment_date->format('d M Y h:i A') }}
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-navy">Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" class="form-control form-control-sm" rows="3" placeholder="Provide reason for record and patient notification..." required minlength="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Keep Appointment</button>
                    <button type="submit" class="btn btn-sm btn-danger fw-semibold">
                        <i class="bi bi-x-octagon"></i> Confirm Cancellation
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endcan

<script>
function loadRescheduleSlots(appointmentId, doctorId) {
    const dateInput = document.getElementById('reschedule_date_' + appointmentId);
    const timeSelect = document.getElementById('reschedule_time_' + appointmentId);
    const statusDiv = document.getElementById('reschedule_slots_status_' + appointmentId);

    if (!dateInput || !timeSelect || !dateInput.value) return;

    timeSelect.innerHTML = '<option value="">Loading available slots...</option>';
    timeSelect.disabled = true;
    if (statusDiv) statusDiv.textContent = 'Checking doctor schedule...';

    fetch(`{{ route('api.appointments.slots') }}?doctor_id=${doctorId}&date=${dateInput.value}&ignore_appointment_id=${appointmentId}`)
        .then(response => response.json())
        .then(data => {
            timeSelect.innerHTML = '';
            timeSelect.disabled = false;

            if (!data.is_scheduled || !data.slots || data.slots.length === 0) {
                timeSelect.innerHTML = '<option value="">No available consultation slots on this date</option>';
                if (statusDiv) statusDiv.textContent = data.message || 'Doctor not available on this date.';
                return;
            }

            let availableCount = 0;
            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = '-- Select Time Slot --';
            timeSelect.appendChild(defaultOpt);

            data.slots.forEach(slot => {
                const opt = document.createElement('option');
                opt.value = slot.start;
                opt.textContent = `${slot.formatted} ${slot.is_available ? '(Available)' : '— ' + slot.reason}`;
                if (!slot.is_available) {
                    opt.disabled = true;
                } else {
                    availableCount++;
                }
                timeSelect.appendChild(opt);
            });

            if (statusDiv) {
                statusDiv.textContent = `${availableCount} slots available for ${data.day_of_week}.`;
            }
        })
        .catch(err => {
            timeSelect.innerHTML = '<option value="">Error loading slots</option>';
            if (statusDiv) statusDiv.textContent = 'Could not fetch slots.';
        });
}

// Trigger initial load on modal open
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('rescheduleModal{{ $appointment->id }}');
    if (modalEl) {
        modalEl.addEventListener('shown.bs.modal', function() {
            loadRescheduleSlots({{ $appointment->id }}, {{ $appointment->doctor_id }});
        });
    }
});
</script>
