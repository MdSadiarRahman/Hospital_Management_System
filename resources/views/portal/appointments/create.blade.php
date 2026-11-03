@extends('layouts.portal', [
    'title' => 'Book Doctor Appointment',
    'activeNav' => 'appointments'
])

@section('content')
<div class="container-fluid py-4 px-md-4">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted" style="font-size: 0.85rem;">
                    <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted">Portal</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('portal.appointments.index') }}" class="text-decoration-none text-muted">Appointments</a></li>
                    <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Book Appointment</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-navy mb-0">Book Doctor Appointment</h1>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                Schedule an outpatient consultation with clinical department, doctor availability, and time slot validation.
            </p>
        </div>
        <div>
            <a href="{{ route('portal.appointments.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Appointments</span>
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <div class="fw-bold d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-exclamation-triangle-fill text-danger"></i> Please check the required fields:
            </div>
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('portal.appointments.store') }}" method="POST" id="appointmentBookingForm">
        @csrf

        <div class="row g-4">
            <!-- Left Column: Patient Selection & Contact -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title fw-bold text-navy mb-0 fs-6">
                            <i class="bi bi-person-bounding-box text-teal me-2"></i> 1. Patient Selection
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Patient Mode Toggle -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-navy text-uppercase" style="letter-spacing: 0.5px;">Booking Category</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="patient_type" id="type_registered" value="registered" {{ old('patient_type', $selectedPatient ? 'registered' : 'registered') === 'registered' ? 'checked' : '' }} onchange="togglePatientMode()">
                                    <label class="form-check-label fw-semibold text-navy" for="type_registered">
                                        Registered Patient (PID)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="patient_type" id="type_guest" value="guest" {{ old('patient_type') === 'guest' ? 'checked' : '' }} onchange="togglePatientMode()">
                                    <label class="form-check-label fw-semibold text-navy" for="type_guest">
                                        Walk-in / New Guest
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Registered Patient Selector -->
                        <div id="registered_patient_section" class="mb-4">
                            <label for="patient_id" class="form-label small fw-semibold text-navy">
                                Select Patient Record <span class="text-danger">*</span>
                            </label>
                            <select name="patient_id" id="patient_id" class="form-select form-select-sm">
                                <option value="">-- Choose Registered Patient --</option>
                                @if($selectedPatient)
                                    <option value="{{ $selectedPatient->id }}" selected>
                                        {{ $selectedPatient->name }} ({{ $selectedPatient->patient_code }} | {{ $selectedPatient->phone }})
                                    </option>
                                @endif
                                @foreach(App\Models\Patient::latest()->take(30)->get() as $p)
                                    @if(!$selectedPatient || $selectedPatient->id !== $p->id)
                                    <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }} ({{ $p->patient_code }} | {{ $p->phone }})
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="form-text small text-muted">
                                Can't find patient? <a href="{{ route('portal.patients.create') }}" target="_blank" class="text-teal text-decoration-none">Register New Patient</a>
                            </div>
                        </div>

                        <!-- Guest / Walk-in Details -->
                        <div id="guest_patient_section" class="d-none">
                            <div class="alert alert-light border small text-muted mb-3">
                                <i class="bi bi-info-circle me-1"></i> Patient is not registered in the master database yet. Contact details will be stored for serial and intake verification.
                            </div>
                            <div class="mb-3">
                                <label for="guest_name" class="form-label small fw-semibold text-navy">Patient Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="guest_name" id="guest_name" class="form-control form-control-sm" value="{{ old('guest_name') }}" placeholder="e.g. Md. Anisur Rahman">
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label for="guest_phone" class="form-label small fw-semibold text-navy">Mobile Phone <span class="text-danger">*</span></label>
                                    <input type="tel" name="guest_phone" id="guest_phone" class="form-control form-control-sm" value="{{ old('guest_phone') }}" placeholder="017XXXXXXXX">
                                </div>
                                <div class="col-md-6">
                                    <label for="guest_email" class="form-label small fw-semibold text-navy">Email Address</label>
                                    <input type="email" name="guest_email" id="guest_email" class="form-control form-control-sm" value="{{ old('guest_email') }}" placeholder="patient@example.com">
                                </div>
                            </div>
                        </div>

                        <!-- Intake Type & Origin -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label for="type" class="form-label small fw-semibold text-navy">Consultation Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select form-select-sm" required>
                                    <option value="walk_in" {{ old('type') === 'walk_in' ? 'selected' : '' }}>Walk-in (Reception Desk)</option>
                                    <option value="online" {{ old('type') === 'online' ? 'selected' : '' }}>Online / Phone Booking</option>
                                    <option value="follow_up" {{ old('type') === 'follow_up' ? 'selected' : '' }}>Follow-up Review</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-navy">Approval Action</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" name="auto_confirm" id="auto_confirm" value="1" {{ old('auto_confirm', '1') === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-semibold text-success" for="auto_confirm">
                                        Confirm Immediately
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Symptoms / Chief Complaints -->
                        <div>
                            <label for="reason" class="form-label small fw-semibold text-navy">Chief Medical Complaint / Reason</label>
                            <textarea name="reason" id="reason" class="form-control form-control-sm" rows="3" placeholder="Symptoms, purpose of visit, previous medication notes...">{{ old('reason') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Clinical Dept, Doctor & Live Time Slots -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title fw-bold text-navy mb-0 fs-6">
                            <i class="bi bi-calendar2-week text-teal me-2"></i> 2. Doctor & Schedule Selection
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Department Selection -->
                        <div class="mb-3">
                            <label for="department_id" class="form-label small fw-semibold text-navy">
                                Clinical Department <span class="text-danger">*</span>
                            </label>
                            <select name="department_id" id="department_id" class="form-select form-select-sm" required onchange="onDepartmentChanged()">
                                <option value="">-- Choose Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }} ({{ $dept->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Doctor Selection -->
                        <div class="mb-3">
                            <label for="doctor_id" class="form-label small fw-semibold text-navy">
                                Consulting Doctor <span class="text-danger">*</span>
                            </label>
                            <select name="doctor_id" id="doctor_id" class="form-select form-select-sm" required onchange="onDoctorOrDateChanged()">
                                <option value="">-- Choose Doctor --</option>
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}" data-dept="{{ $doc->staffProfile?->department_id }}" {{ old('doctor_id') == $doc->id ? 'selected' : '' }}>
                                        {{ $doc->name }} — {{ $doc->staffProfile?->designation ?? 'Consultant' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Preferred Date -->
                        <div class="mb-4">
                            <label for="appointment_date" class="form-label small fw-semibold text-navy">
                                Preferred Consultation Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="appointment_date" id="appointment_date" class="form-control form-control-sm" min="{{ date('Y-m-d') }}" value="{{ old('appointment_date', date('Y-m-d')) }}" required onchange="onDoctorOrDateChanged()">
                        </div>

                        <!-- Time Slots Section (Interactive Bento Chips) -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label small fw-semibold text-navy mb-0">
                                    Available Time Slots <span class="text-danger">*</span>
                                </label>
                                <span id="slot_loading_indicator" class="spinner-border spinner-border-sm text-teal d-none" role="status"></span>
                            </div>

                            <input type="hidden" name="appointment_time" id="appointment_time" value="{{ old('appointment_time') }}" required>

                            <div id="slots_container" class="border rounded-3 p-3 bg-light min-h-100">
                                <p class="text-muted small text-center mb-0 py-3" id="slots_placeholder">
                                    <i class="bi bi-calendar3 fs-4 d-block mb-1 text-muted"></i>
                                    Select a doctor and date above to load available time slots.
                                </p>
                                <div id="slots_grid" class="d-flex flex-wrap gap-2 d-none">
                                    <!-- Dynamic Slots Rendered Here -->
                                </div>
                            </div>
                            <div id="slots_status_msg" class="form-text small mt-1"></div>
                        </div>

                        <div class="pt-3 border-top d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('portal.appointments.index') }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-hms-teal px-4 fw-semibold" id="submitBtn">
                                <i class="bi bi-calendar2-check me-1"></i> Confirm Booking
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function togglePatientMode() {
    const isRegistered = document.getElementById('type_registered').checked;
    const regSection = document.getElementById('registered_patient_section');
    const guestSection = document.getElementById('guest_patient_section');

    if (isRegistered) {
        regSection.classList.remove('d-none');
        guestSection.classList.add('d-none');
        document.getElementById('patient_id').required = true;
        document.getElementById('guest_name').required = false;
        document.getElementById('guest_phone').required = false;
    } else {
        regSection.classList.add('d-none');
        guestSection.classList.remove('d-none');
        document.getElementById('patient_id').required = false;
        document.getElementById('guest_name').required = true;
        document.getElementById('guest_phone').required = true;
    }
}

function onDepartmentChanged() {
    const deptId = document.getElementById('department_id').value;
    const doctorSelect = document.getElementById('doctor_id');

    if (!deptId) return;

    fetch(`{{ route('api.appointments.doctors') }}?department_id=${deptId}`)
        .then(res => res.json())
        .then(data => {
            doctorSelect.innerHTML = '<option value="">-- Choose Doctor --</option>';
            data.doctors.forEach(doc => {
                const opt = document.createElement('option');
                opt.value = doc.id;
                opt.textContent = `${doc.name} — ${doc.designation}`;
                doctorSelect.appendChild(opt);
            });
            onDoctorOrDateChanged();
        })
        .catch(() => {});
}

function onDoctorOrDateChanged() {
    const doctorId = document.getElementById('doctor_id').value;
    const date = document.getElementById('appointment_date').value;
    const slotsGrid = document.getElementById('slots_grid');
    const slotsPlaceholder = document.getElementById('slots_placeholder');
    const statusMsg = document.getElementById('slots_status_msg');
    const spinner = document.getElementById('slot_loading_indicator');
    const hiddenTime = document.getElementById('appointment_time');

    if (!doctorId || !date) {
        slotsGrid.classList.add('d-none');
        slotsPlaceholder.classList.remove('d-none');
        statusMsg.textContent = '';
        return;
    }

    spinner.classList.remove('d-none');
    slotsGrid.innerHTML = '';
    slotsGrid.classList.add('d-none');
    slotsPlaceholder.classList.remove('d-none');
    slotsPlaceholder.textContent = 'Loading doctor slots...';
    statusMsg.textContent = '';

    fetch(`{{ route('api.appointments.slots') }}?doctor_id=${doctorId}&date=${date}`)
        .then(res => res.json())
        .then(data => {
            spinner.classList.add('d-none');

            if (!data.is_scheduled || !data.slots || data.slots.length === 0) {
                slotsPlaceholder.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle me-1"></i> ${data.message || 'No available consultation slots on this date.'}</span>`;
                slotsPlaceholder.classList.remove('d-none');
                slotsGrid.classList.add('d-none');
                return;
            }

            slotsPlaceholder.classList.add('d-none');
            slotsGrid.classList.remove('d-none');
            slotsGrid.innerHTML = '';

            let availableSlots = 0;
            data.slots.forEach(slot => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm text-center py-2 px-3 rounded-2 transition-all position-relative ';

                if (slot.is_available) {
                    availableSlots++;
                    btn.className += (hiddenTime.value === slot.start) ? 'btn-hms-teal shadow-sm' : 'btn-outline-secondary';
                    btn.innerHTML = `<span class="d-block fw-semibold" style="font-size: 0.85rem;">${slot.formatted}</span><small class="text-teal-emphasis" style="font-size: 0.7rem;">Available</small>`;
                    btn.onclick = function() {
                        hiddenTime.value = slot.start;
                        document.querySelectorAll('#slots_grid button').forEach(b => {
                            if (b.classList.contains('btn-hms-teal')) {
                                b.classList.remove('btn-hms-teal', 'shadow-sm');
                                b.classList.add('btn-outline-secondary');
                            }
                        });
                        btn.classList.remove('btn-outline-secondary');
                        btn.classList.add('btn-hms-teal', 'shadow-sm');
                    };
                } else {
                    btn.className += 'btn-light text-muted border border-dashed opacity-50';
                    btn.disabled = true;
                    btn.innerHTML = `<span class="d-block text-decoration-line-through small">${slot.formatted}</span><small style="font-size: 0.65rem;">${slot.reason}</small>`;
                }

                slotsGrid.appendChild(btn);
            });

            statusMsg.textContent = `${availableSlots} available slot(s) for ${data.day_of_week}. Consultation: ${data.slots[0]?.room_number || 'OPD'}.`;
        })
        .catch(() => {
            spinner.classList.add('d-none');
            slotsPlaceholder.innerHTML = '<span class="text-danger">Failed to load doctor slots. Please retry.</span>';
        });
}

document.addEventListener('DOMContentLoaded', function() {
    togglePatientMode();
    if (document.getElementById('doctor_id').value && document.getElementById('appointment_date').value) {
        onDoctorOrDateChanged();
    }
});
</script>
@endsection
