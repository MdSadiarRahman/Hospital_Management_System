@extends('layouts.portal', [
    'title' => 'Request Doctor Appointment',
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
                    <li class="breadcrumb-item"><a href="{{ route('patient.appointments.index') }}" class="text-decoration-none text-muted">Appointments</a></li>
                    <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Request Consultation</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-navy mb-0">Request Doctor Consultation</h1>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                Select your preferred doctor, date, and consultation time slot.
            </p>
        </div>
        <div>
            <a href="{{ route('patient.appointments.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1.5 shadow-sm">
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

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 p-4 bg-white">
                <form action="{{ route('patient.appointments.store') }}" method="POST">
                    @csrf

                    <!-- Department Selection -->
                    <div class="mb-3">
                        <label for="p_dept" class="form-label small fw-semibold text-navy">
                            Clinical Department <span class="text-danger">*</span>
                        </label>
                        <select name="department_id" id="p_dept" class="form-select" required onchange="onPatientDeptChange()">
                            <option value="">-- Choose Department --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Doctor Selection -->
                    <div class="mb-3">
                        <label for="p_doctor" class="form-label small fw-semibold text-navy">
                            Consulting Doctor <span class="text-danger">*</span>
                        </label>
                        <select name="doctor_id" id="p_doctor" class="form-select" required onchange="onPatientDocOrDateChange()">
                            <option value="">-- Choose Doctor --</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ old('doctor_id') == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }} — {{ $doc->staffProfile?->designation ?? 'Consultant' }} ({{ $doc->staffProfile?->department?->name ?? 'OPD' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Preferred Date -->
                    <div class="mb-3">
                        <label for="p_date" class="form-label small fw-semibold text-navy">
                            Preferred Consultation Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="appointment_date" id="p_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ old('appointment_date', date('Y-m-d')) }}" required onchange="onPatientDocOrDateChange()">
                    </div>

                    <!-- Live Time Slots Selection -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label small fw-semibold text-navy mb-0">
                                Select Available Time Slot <span class="text-danger">*</span>
                            </label>
                            <span id="p_slot_spinner" class="spinner-border spinner-border-sm text-teal d-none"></span>
                        </div>

                        <input type="hidden" name="appointment_time" id="p_time" value="{{ old('appointment_time') }}" required>

                        <div id="p_slots_box" class="border rounded-3 p-3 bg-light">
                            <p class="text-muted small text-center mb-0 py-3" id="p_slots_empty">
                                <i class="bi bi-clock-history fs-4 d-block mb-1 text-muted"></i>
                                Select a doctor and date to view consultation hours and slots.
                            </p>
                            <div id="p_slots_grid" class="d-flex flex-wrap gap-2 d-none">
                                <!-- Dynamic Slot Buttons -->
                            </div>
                        </div>
                        <div id="p_slots_note" class="form-text small mt-1"></div>
                    </div>

                    <!-- Reason / Symptoms -->
                    <div class="mb-4">
                        <label for="p_reason" class="form-label small fw-semibold text-navy">
                            Describe Your Symptoms or Reasons for Consultation
                        </label>
                        <textarea name="reason" id="p_reason" class="form-control" rows="3" placeholder="Briefly explain your health concern, symptoms, or why you need this consultation...">{{ old('reason') }}</textarea>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('patient.appointments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-hms-teal px-4 fw-semibold shadow-sm">
                            <i class="bi bi-calendar2-check me-1"></i> Submit Consultation Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function onPatientDeptChange() {
    const deptId = document.getElementById('p_dept').value;
    const doctorSelect = document.getElementById('p_doctor');

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
            onPatientDocOrDateChange();
        });
}

function onPatientDocOrDateChange() {
    const docId = document.getElementById('p_doctor').value;
    const date = document.getElementById('p_date').value;
    const grid = document.getElementById('p_slots_grid');
    const emptyMsg = document.getElementById('p_slots_empty');
    const note = document.getElementById('p_slots_note');
    const spinner = document.getElementById('p_slot_spinner');
    const hiddenTime = document.getElementById('p_time');

    if (!docId || !date) {
        grid.classList.add('d-none');
        emptyMsg.classList.remove('d-none');
        note.textContent = '';
        return;
    }

    spinner.classList.remove('d-none');
    emptyMsg.textContent = 'Checking doctor consultation slots...';
    emptyMsg.classList.remove('d-none');
    grid.classList.add('d-none');

    fetch(`{{ route('api.appointments.slots') }}?doctor_id=${docId}&date=${date}`)
        .then(res => res.json())
        .then(data => {
            spinner.classList.add('d-none');

            if (!data.is_scheduled || !data.slots || data.slots.length === 0) {
                emptyMsg.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle me-1"></i> ${data.message || 'No consultation slots on this date.'}</span>`;
                emptyMsg.classList.remove('d-none');
                grid.classList.add('d-none');
                note.textContent = '';
                return;
            }

            emptyMsg.classList.add('d-none');
            grid.classList.remove('d-none');
            grid.innerHTML = '';

            let available = 0;
            data.slots.forEach(slot => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm py-2 px-3 rounded-2 text-center ';

                if (slot.is_available) {
                    available++;
                    btn.className += (hiddenTime.value === slot.start) ? 'btn-hms-teal shadow-sm' : 'btn-outline-secondary';
                    btn.innerHTML = `<span class="d-block fw-semibold" style="font-size: 0.85rem;">${slot.formatted}</span><small style="font-size: 0.65rem;">Available</small>`;
                    btn.onclick = function() {
                        hiddenTime.value = slot.start;
                        document.querySelectorAll('#p_slots_grid button').forEach(b => {
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
                    btn.innerHTML = `<span class="d-block text-decoration-line-through small">${slot.formatted}</span><small style="font-size: 0.65rem;">Booked</small>`;
                }

                grid.appendChild(btn);
            });

            note.textContent = `${available} available slots found for ${data.day_of_week}.`;
        })
        .catch(() => {
            spinner.classList.add('d-none');
            emptyMsg.innerHTML = '<span class="text-danger">Failed to load doctor slots. Please retry.</span>';
        });
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('p_doctor').value && document.getElementById('p_date').value) {
        onPatientDocOrDateChange();
    }
});
</script>
@endsection
