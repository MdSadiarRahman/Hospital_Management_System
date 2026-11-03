<x-layouts.public 
    :title="$doctor->name . ' — Doctor Profile'"
    :metaDescription="'Consult with ' . $doctor->name . ' (' . ($doctor->staffProfile->designation ?? 'Doctor') . ') at Meghna Clinic and Nursing Home, Dinajpur. OPD and inpatient surgical care.'"
>
    <!-- Page Header & Breadcrumb -->
    <div class="bg-navy text-white py-5">
        <div class="container-xl">
            <x-breadcrumb :items="['Doctors' => route('doctors'), $doctor->name => null]" class="mb-3 text-white-50" />
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-teal text-white px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.85rem;">
                            {{ $doctor->staffProfile->department->name ?? 'Clinical Department' }}
                        </span>
                        <span class="badge bg-success text-white">
                            <i class="bi bi-patch-check-fill me-1"></i> BMDC Registered
                        </span>
                    </div>
                    <h1 class="display-5 fw-bold mb-2 text-white">{{ $doctor->name }}</h1>
                    <p class="lead text-white-50 mb-0" style="max-width: 720px; line-height: 1.6;">
                        {{ $doctor->staffProfile->designation ?? 'Medical Officer / Attending Doctor' }} &bull; Meghna Clinic & Nursing Home, Dinajpur
                    </p>
                </div>
                <div class="col-lg-4 mt-4 mt-lg-0 text-lg-end">
                    <a href="{{ route('home') }}#appointment" class="btn btn-hms-teal btn-hms-lg">
                        <i class="bi bi-calendar2-check"></i> Book Consultation
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content & Sidebar -->
    <section class="py-5 bg-body">
        <div class="container-xl">
            <div class="row g-4">
                <!-- Left: Profile Details & Schedule -->
                <div class="col-lg-4">
                    <!-- Profile Card -->
                    <div class="card border shadow-sm p-4 rounded-3 bg-white text-center mb-4">
                        <div class="rounded-circle bg-navy text-white d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 96px; height: 96px;">
                            <i class="bi bi-person-fill display-4 text-teal"></i>
                        </div>
                        <h2 class="h5 fw-bold text-navy mb-1">{{ $doctor->name }}</h2>
                        <span class="badge bg-teal-subtle text-teal mb-2">
                            {{ $doctor->staffProfile->designation ?? 'Medical Officer' }}
                        </span>
                        <p class="text-muted-hms mb-3" style="font-size: 0.85rem;">
                            {{ $doctor->staffProfile->department->name ?? 'Clinical Department' }}
                        </p>

                        <div class="border-top pt-3 text-start" style="font-size: 0.85rem;">
                            <div class="d-flex justify-content-between py-1.5 border-bottom">
                                <span class="text-muted-hms">Registration:</span>
                                <span class="fw-semibold text-navy">BMDC Verified</span>
                            </div>
                            <div class="d-flex justify-content-between py-1.5 border-bottom">
                                <span class="text-muted-hms">Department:</span>
                                <span class="fw-semibold text-navy">{{ $doctor->staffProfile->department->name ?? 'Clinical' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1.5 border-bottom">
                                <span class="text-muted-hms">Shift Pattern:</span>
                                <span class="fw-semibold text-navy text-capitalize">{{ $doctor->staffProfile->shift ?? 'Rotational' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1.5">
                                <span class="text-muted-hms">Chamber Token Desk:</span>
                                <span class="fw-semibold text-teal">+880 1711-000004</span>
                            </div>
                        </div>
                    </div>

                    <!-- Consultation Schedule Card -->
                    <div class="card border shadow-sm p-4 rounded-3 bg-white mb-4">
                        <h3 class="h6 fw-bold text-navy mb-3">
                            <i class="bi bi-clock-fill text-teal me-2"></i> Weekly Consultation Hours
                        </h3>
                        <div class="d-flex flex-column gap-2" style="font-size: 0.85rem;">
                            <div class="p-2.5 rounded bg-body border">
                                <span class="d-block text-muted-hms" style="font-size: 0.75rem;">Morning Shift:</span>
                                <strong class="text-navy">9:00 AM – 1:00 PM</strong>
                                <span class="d-block text-muted-hms" style="font-size: 0.75rem;">Saturday through Thursday</span>
                            </div>
                            <div class="p-2.5 rounded bg-body border">
                                <span class="d-block text-muted-hms" style="font-size: 0.75rem;">Evening Shift:</span>
                                <strong class="text-navy">5:00 PM – 9:00 PM</strong>
                                <span class="d-block text-muted-hms" style="font-size: 0.75rem;">Saturday through Thursday</span>
                            </div>
                            <div class="p-2.5 rounded bg-body border">
                                <span class="d-block text-muted-hms" style="font-size: 0.75rem;">Inpatient Ward Rounds:</span>
                                <strong class="text-teal">Daily Morning & Evening Rounds</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Emergency Rota Notice -->
                    <div class="p-3 rounded-3 bg-danger-subtle border border-danger-subtle">
                        <span class="d-block fw-bold text-danger mb-1" style="font-size: 0.85rem;">
                            <i class="bi bi-exclamation-octagon-fill me-1"></i> Acute Emergency Cases
                        </span>
                        <p class="text-muted-hms mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            Outside regular OPD chamber hours, walk-in emergency deliveries and acute trauma are admitted 24/7 by our resident On-Duty Emergency Medical Officer.
                        </p>
                    </div>
                </div>

                <!-- Right: Bio & Appointment Booking -->
                <div class="col-lg-8">
                    <!-- Professional Focus & Specializations -->
                    <div class="card border shadow-sm p-4 rounded-3 bg-white mb-4">
                        <h2 class="h4 fw-bold text-navy mb-3">
                            <i class="bi bi-file-medical text-teal me-2"></i> Clinical Specialization & Experience
                        </h2>
                        <p class="text-muted-hms mb-3" style="line-height: 1.7;">
                            {{ $doctor->staffProfile->specialization ?? 'Dedicated clinical practitioner providing outpatient diagnosis, prenatal checkups, surgical evaluations, and supervised inpatient care at Meghna Clinic and Nursing Home.' }}
                        </p>
                        <p class="text-muted-hms mb-4" style="line-height: 1.7;">
                            With a strong commitment to ethical medicine and compassionate patient communication, {{ $doctor->name }} coordinates closely with our 6 shift nurses and attending surgical team to ensure thorough post-operative recovery for all admitted patients.
                        </p>

                        <h3 class="h6 fw-bold text-navy mb-3">Key Medical Procedures & Scope:</h3>
                        <div class="row g-2 text-muted-hms" style="font-size: 0.875rem;">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle text-teal"></i>
                                    <span>Outpatient Clinical Diagnosis & Prescriptions</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle text-teal"></i>
                                    <span>Inpatient Ward Care & Daily Clinical Reviews</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle text-teal"></i>
                                    <span>Sterile Minor Surgical Procedures & Dressings</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle text-teal"></i>
                                    <span>Diagnostic Test Evaluation & Referral Review</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Appointment Booking Card -->
                    <div class="card border shadow-sm p-4 rounded-3 bg-white mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h3 class="h5 fw-bold text-navy mb-0">
                                <i class="bi bi-calendar-plus text-teal me-2"></i> Request Consultation Token
                            </h3>
                            <span class="badge bg-teal-subtle text-teal">OPD Token</span>
                        </div>
                        <p class="text-muted-hms mb-4" style="font-size: 0.875rem;">
                            Fill in patient details below to book an appointment with <strong>{{ $doctor->name }}</strong>. Tokens are confirmed by our reception desk upon arrival.
                        </p>

                        <!-- Success Alert with Serial -->
                        @if(session('appointment_success'))
                            <div class="alert alert-success border-0 shadow-sm rounded-3 p-3 text-center mb-4">
                                <i class="bi bi-check-circle-fill text-success fs-2 d-block mb-1"></i>
                                <h5 class="fw-bold text-navy mb-1">Appointment Request Submitted!</h5>
                                <p class="text-muted small mb-2">{{ session('status') }}</p>
                                <div class="bg-white p-2 rounded-2 border d-inline-block text-start small mb-2">
                                    <div><strong>Consulting Doctor:</strong> Dr. {{ session('appointment_doctor') }}</div>
                                    <div><strong>Date & Time:</strong> {{ session('appointment_date') }}</div>
                                    @if(session('appointment_serial'))
                                    <div><strong>Daily Serial:</strong> <span class="badge bg-navy text-white">#{{ session('appointment_serial') }}</span></div>
                                    @endif
                                </div>
                                <div class="small text-muted">
                                    Reference ID: <strong>MCNH-APT-{{ str_pad(session('appointment_id'), 5, '0', STR_PAD_LEFT) }}</strong>
                                </div>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 p-3">
                                <strong class="small d-block mb-1"><i class="bi bi-exclamation-circle me-1"></i> Please check errors:</strong>
                                <ul class="small mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('appointments.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="doctor_id" id="doc_profile_doctor_id" value="{{ $doctor->id }}">
                            <input type="hidden" name="department_id" value="{{ $doctor->staffProfile?->department_id ?? 1 }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">Patient Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="guest_name" class="form-control" placeholder="e.g. Mohammad Rahim" value="{{ old('guest_name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">Contact Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" name="guest_phone" class="form-control" placeholder="017XXXXXXXX" value="{{ old('guest_phone') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">Email Address (Optional)</label>
                                    <input type="email" name="guest_email" class="form-control" placeholder="name@example.com" value="{{ old('guest_email') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">Preferred Date <span class="text-danger">*</span></label>
                                    <input type="date" id="doc_profile_apt_date" name="appointment_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ old('appointment_date', date('Y-m-d')) }}" required onchange="onDocProfileDateChange()">
                                </div>

                                <!-- Time Slot Chips Selection -->
                                <div class="col-12">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label fw-semibold text-navy mb-0" style="font-size: 0.85rem;">
                                            Available Consultation Slot <span class="text-danger">*</span>
                                        </label>
                                        <span id="doc_profile_slot_spinner" class="spinner-border spinner-border-sm text-teal d-none"></span>
                                    </div>

                                    <input type="hidden" name="appointment_time" id="doc_profile_apt_time" value="{{ old('appointment_time') }}" required>

                                    <div id="doc_profile_slots_box" class="border rounded-2 p-3 bg-light">
                                        <p class="text-muted small text-center mb-0" id="doc_profile_slots_empty">
                                            <i class="bi bi-clock me-1"></i> Checking consultation slots for selected date...
                                        </p>
                                        <div id="doc_profile_slots_grid" class="d-flex flex-wrap gap-2 d-none">
                                            <!-- Injected Slots -->
                                        </div>
                                    </div>
                                    <div id="doc_profile_slots_note" class="form-text small"></div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">Brief Medical Concern (Optional)</label>
                                    <textarea name="reason" class="form-control" rows="2" placeholder="Symptoms or note for the doctor...">{{ old('reason') }}</textarea>
                                </div>

                                <div class="col-12 mt-3">
                                    <button type="submit" class="btn btn-hms-teal w-100 shadow-sm">
                                        <i class="bi bi-calendar2-check"></i> Submit Appointment Request
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <script>
                    function onDocProfileDateChange() {
                        const docId = document.getElementById('doc_profile_doctor_id').value;
                        const date = document.getElementById('doc_profile_apt_date').value;
                        const grid = document.getElementById('doc_profile_slots_grid');
                        const emptyMsg = document.getElementById('doc_profile_slots_empty');
                        const note = document.getElementById('doc_profile_slots_note');
                        const spinner = document.getElementById('doc_profile_slot_spinner');
                        const timeInput = document.getElementById('doc_profile_apt_time');

                        if (!docId || !date) return;

                        spinner.classList.remove('d-none');
                        grid.innerHTML = '';
                        grid.classList.add('d-none');
                        emptyMsg.classList.remove('d-none');
                        emptyMsg.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Loading slots...';
                        note.textContent = '';
                        timeInput.value = '';

                        fetch(`{{ route('api.appointments.slots') }}?doctor_id=${docId}&date=${date}`)
                            .then(res => res.json())
                            .then(data => {
                                spinner.classList.add('d-none');
                                if (!data.scheduled) {
                                    emptyMsg.innerHTML = `<i class="bi bi-info-circle text-warning me-1"></i> ${data.message}`;
                                    note.textContent = 'Please choose another day from the doctor\'s weekly consultation schedule.';
                                    note.className = 'form-text small text-danger';
                                    return;
                                }

                                if (!data.slots || data.slots.length === 0) {
                                    emptyMsg.innerHTML = '<i class="bi bi-x-circle text-danger me-1"></i> No consultation slots configured for this date.';
                                    return;
                                }

                                emptyMsg.classList.add('d-none');
                                grid.classList.remove('d-none');

                                let selectableCount = 0;
                                data.slots.forEach(slot => {
                                    const btn = document.createElement('button');
                                    btn.type = 'button';
                                    btn.className = `btn btn-sm ${slot.available ? 'btn-outline-primary' : 'btn-light text-muted border'}`;
                                    btn.textContent = slot.formatted;
                                    btn.disabled = !slot.available;
                                    if (!slot.available) {
                                        btn.title = 'Slot Already Booked / Past';
                                    } else {
                                        selectableCount++;
                                        btn.onclick = function() {
                                            grid.querySelectorAll('button').forEach(b => {
                                                if (!b.disabled) b.className = 'btn btn-sm btn-outline-primary';
                                            });
                                            btn.className = 'btn btn-sm btn-hms-teal text-white fw-bold shadow-sm';
                                            timeInput.value = slot.time;
                                            note.textContent = `Selected consultation slot: ${slot.formatted}`;
                                            note.className = 'form-text small text-success fw-semibold';
                                        };
                                    }
                                    grid.appendChild(btn);
                                });

                                if (selectableCount === 0) {
                                    note.textContent = 'All consultation slots are fully booked or past for this date.';
                                    note.className = 'form-text small text-danger';
                                } else {
                                    note.textContent = `${selectableCount} available consultation slot(s). Click a time slot to select.`;
                                    note.className = 'form-text small text-muted';
                                }
                            })
                            .catch(err => {
                                spinner.classList.add('d-none');
                                emptyMsg.innerHTML = '<i class="bi bi-exclamation-triangle text-danger me-1"></i> Failed to load available slots. Please try again.';
                            });
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        onDocProfileDateChange();
                    });
                    </script>

                    <!-- Other Doctors On Duty -->
                    @if($otherDoctors->isNotEmpty())
                        <div class="card border shadow-sm p-4 rounded-3 bg-white mb-4">
                            <h3 class="h6 fw-bold text-navy mb-3">Other Doctors on Medical Staff</h3>
                            <div class="row g-3">
                                @foreach($otherDoctors as $other)
                                    <div class="col-sm-6">
                                        <div class="p-3 rounded border bg-body d-flex justify-content-between align-items-center">
                                            <div>
                                                <h4 class="h6 fw-bold text-navy mb-0">{{ $other->name }}</h4>
                                                <span class="text-muted-hms" style="font-size: 0.75rem;">
                                                    {{ $other->staffProfile->designation ?? 'Consultant' }}
                                                </span>
                                            </div>
                                            <a href="{{ route('doctors.show', $other->id) }}" class="btn btn-hms-outline-primary btn-hms-sm">
                                                Profile
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Administrator Control Notice -->
                    <div class="p-3 rounded bg-warning-subtle border border-warning-subtle d-flex align-items-center gap-2">
                        <i class="bi bi-gear-fill text-warning fs-5"></i>
                        <span class="text-muted-hms" style="font-size: 0.85rem;">
                            <strong>Admin Notice:</strong> Doctor chamber fees, leave dates, and daily token limits are maintained through the Staff Portal by the Hospital Administrator.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
