<x-layouts.portal role="admin" title="Admit Inpatient & Bed Allocation">
    <div class="container-fluid px-0">
        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('portal.admissions.index') }}" class="text-decoration-none text-muted-hms">Admissions</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Admit Inpatient</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold text-navy mb-0">Inpatient Admission & Bed Allocation</h1>
                <p class="text-muted-hms small mb-0 mt-1">
                    Register a hospital inpatient stay, link the originating visit, and allocate an available General or Department bed.
                </p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portal.admissions.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Admissions</span>
                </a>
                <a href="{{ route('portal.beds.index') }}" class="btn btn-outline-navy d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-door-open"></i>
                    <span>View 10-Bed Live Station</span>
                </a>
            </div>
        </div>

        <!-- Alert Notifications -->
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please resolve the following errors:</div>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- Form Column -->
            <div class="col-12 col-lg-8">
                <form action="{{ route('portal.admissions.store') }}" method="POST" id="admissionForm">
                    @csrf

                    <!-- 1. Patient Selection & Visit Linking -->
                    <div class="card border-0 shadow-xs mb-4">
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded bg-teal bg-opacity-10 text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                    <i class="bi bi-person-vcard"></i>
                                </span>
                                <div>
                                    <h5 class="fw-bold text-navy mb-0">1. Patient Identification & Clinical Visit</h5>
                                    <span class="text-muted-hms small">Admission must belong to a verified registered patient</span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4 pt-1">
                            <div class="row g-3">
                                <!-- Registered Patient Select -->
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-navy">
                                        Registered Patient <span class="text-danger">*</span>
                                    </label>
                                    <select name="patient_id" id="patientSelect" class="form-select @error('patient_id') is-invalid @enderror" required>
                                        <option value="">-- Choose Registered Patient (Search by Name or Code) --</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" 
                                                data-code="{{ $patient->patient_code }}"
                                                data-phone="{{ $patient->phone }}"
                                                data-gender="{{ $patient->gender }}"
                                                {{ (old('patient_id', $selectedPatientId) == $patient->id) ? 'selected' : '' }}>
                                                {{ $patient->name }} ({{ $patient->patient_code }}) · {{ $patient->phone }} · {{ ucfirst($patient->gender) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text small">
                                        Patient not registered yet? <a href="{{ route('portal.patients.create') }}" target="_blank" class="text-teal text-decoration-none fw-semibold">Register new patient here</a>.
                                    </div>
                                </div>

                                <!-- Visit Linking -->
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-navy">
                                        Clinical Visit Linking <span class="text-muted-hms fw-normal">(Optional — links to existing OPD/Emergency visit)</span>
                                    </label>
                                    <select name="visit_id" id="visitSelect" class="form-select @error('visit_id') is-invalid @enderror">
                                        <option value="">-- Auto-create new Inpatient Intake Visit (Default) --</option>
                                        @if(isset($patientVisits))
                                            @foreach($patientVisits as $pv)
                                                <option value="{{ $pv->id }}" {{ (old('visit_id', $selectedVisitId) == $pv->id) ? 'selected' : '' }}>
                                                    {{ $pv->visit_number }} ({{ $pv->visit_date ? $pv->visit_date->format('d M Y') : 'Date N/A' }} - {{ ucfirst($pv->visit_type) }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('visit_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text small">
                                        If left blank, an inpatient intake visit will be generated automatically.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Admission Timing & Clinical Responsibility -->
                    <div class="card border-0 shadow-xs mb-4">
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded bg-primary bg-opacity-10 text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                    <i class="bi bi-clock-history"></i>
                                </span>
                                <div>
                                    <h5 class="fw-bold text-navy mb-0">2. Admission Schedule & Medical Responsibility</h5>
                                    <span class="text-muted-hms small">Intake timestamp, target department, and in-charge physician</span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4 pt-1">
                            <div class="row g-3">
                                <!-- Admission Date & Time -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold text-navy">
                                        Admission Date & Time <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local" name="admission_date" class="form-control @error('admission_date') is-invalid @enderror" 
                                           value="{{ old('admission_date', now()->format('Y-m-d\TH:i')) }}" required>
                                    @error('admission_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Department Assignment -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold text-navy">
                                        Clinical Department
                                    </label>
                                    <select name="department_id" id="departmentSelect" class="form-select @error('department_id') is-invalid @enderror">
                                        <option value="">-- General Ward (Non-departmental) --</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                                {{ $dept->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Attending Doctor -->
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-navy">
                                        Attending / In-Charge Doctor <span class="text-danger">*</span>
                                    </label>
                                    <select name="attending_doctor_id" class="form-select @error('attending_doctor_id') is-invalid @enderror" required>
                                        <option value="">-- Choose Attending Doctor --</option>
                                        @foreach($doctors as $doc)
                                            <option value="{{ $doc->id }}" {{ old('attending_doctor_id') == $doc->id ? 'selected' : '' }}>
                                                {{ $doc->name }} ({{ $doc->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('attending_doctor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Admission Reason -->
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-navy">
                                        Admission Reason & Clinical Indications <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="admission_reason" rows="3" class="form-control @error('admission_reason') is-invalid @enderror" 
                                              placeholder="E.g., Acute abdominal pain, post-cesarean observation, orthopedic trauma reduction..." required>{{ old('admission_reason') }}</textarea>
                                    @error('admission_reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Initial Notes -->
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-navy">
                                        Nursing / Special Intake Notes <span class="text-muted-hms fw-normal">(Optional)</span>
                                    </label>
                                    <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror" 
                                              placeholder="Dietary instructions, allergy cautions, oxygen support requirements...">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Bed Allocation (General & Department-wise) -->
                    <div class="card border-0 shadow-xs mb-4">
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded bg-teal bg-opacity-10 text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                        <i class="bi bi-door-open"></i>
                                    </span>
                                    <div>
                                        <h5 class="fw-bold text-navy mb-0">3. Inpatient Bed Allocation</h5>
                                        <span class="text-muted-hms small">Select an available bed (General Ward or Department-specific)</span>
                                    </div>
                                </div>
                                <span class="badge bg-teal text-white font-monospace" id="availableBedCount">
                                    {{ $availableBeds->count() }} Available Beds
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-4 pt-1">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-navy">
                                    Select Available Bed <span class="text-danger">*</span>
                                </label>
                                <select name="bed_id" id="bedSelect" class="form-select @error('bed_id') is-invalid @enderror" required>
                                    <option value="">-- Choose an Available Bed --</option>
                                    @foreach($availableBeds as $bed)
                                        <option value="{{ $bed->id }}" 
                                                data-dept="{{ $bed->department_id }}"
                                                data-category="{{ $bed->category }}"
                                                {{ old('bed_id') == $bed->id ? 'selected' : '' }}>
                                            Bed {{ $bed->bed_number }} ({{ $bed->room_number ?? 'Room N/A' }}) — {{ $bed->department?->name ?? 'General Ward' }} · ৳{{ number_format((float)$bed->daily_charge, 2) }}/day [{{ $bed->category_label }}]
                                        </option>
                                    @endforeach
                                </select>
                                @error('bed_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">
                                    Both General Ward beds and Department-specialized beds are supported. Only beds currently in <strong>Available</strong> status can be allocated.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submission Button -->
                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-teal btn-lg px-4 shadow-xs d-inline-flex align-items-center gap-2">
                            <i class="bi bi-check2-circle fs-5"></i>
                            <span>Confirm & Allocate Inpatient Bed</span>
                        </button>
                        <a href="{{ route('portal.admissions.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Side Helper Information Column -->
            <div class="col-12 col-lg-4">
                <!-- Inpatient Hospital Rules Card -->
                <div class="card border-0 shadow-xs mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold text-navy mb-0">
                            <i class="bi bi-shield-check text-teal me-1"></i> Admission Business Rules
                        </h6>
                    </div>
                    <div class="card-body p-3 pt-0">
                        <ul class="list-unstyled mb-0" style="font-size: 0.85rem;">
                            <li class="mb-2 d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-teal mt-1"></i>
                                <span><strong>Single Active Stay:</strong> A patient cannot have multiple simultaneous active admissions.</span>
                            </li>
                            <li class="mb-2 d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-teal mt-1"></i>
                                <span><strong>Concurrency Safe:</strong> Bed allocation uses atomic database row locking to prevent double bookings.</span>
                            </li>
                            <li class="mb-2 d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-teal mt-1"></i>
                                <span><strong>Transferable:</strong> Inpatients can later be transferred between beds with full audit tracking.</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-teal mt-1"></i>
                                <span><strong>Dual Clearance:</strong> Discharge requires clinical clearance from doctor and financial clearance from cashier.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- 10 Beds Quick Status -->
                <div class="card border-0 shadow-xs">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold text-navy mb-0">
                            <i class="bi bi-door-open text-primary me-1"></i> Meghna Clinic 10 Beds Station
                        </h6>
                    </div>
                    <div class="card-body p-3 pt-0">
                        <p class="text-muted-hms small mb-3">
                            Current capacity breakdown across departments:
                        </p>
                        <div class="d-grid gap-2">
                            <a href="{{ route('portal.beds.index') }}" class="btn btn-outline-navy btn-sm d-flex align-items-center justify-content-between">
                                <span>Open Live Bed Map</span>
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const patientSelect = document.getElementById('patientSelect');
            const visitSelect = document.getElementById('visitSelect');
            const deptSelect = document.getElementById('departmentSelect');
            const bedSelect = document.getElementById('bedSelect');

            // 1. Dynamic Patient Visits loader
            if (patientSelect) {
                patientSelect.addEventListener('change', function () {
                    const patientId = this.value;
                    if (!patientId) {
                        visitSelect.innerHTML = '<option value="">-- Auto-create new Inpatient Intake Visit (Default) --</option>';
                        return;
                    }

                    fetch(`{{ url('/portal/api/admissions/patient') }}/${patientId}/visits`)
                        .then(res => res.json())
                        .then(data => {
                            let options = '<option value="">-- Auto-create new Inpatient Intake Visit (Default) --</option>';
                            if (data.visits && data.visits.length > 0) {
                                data.visits.forEach(v => {
                                    const dateStr = v.visit_date ? new Date(v.visit_date).toLocaleDateString() : '';
                                    options += `<option value="${v.id}">Visit #${v.visit_number} (${dateStr} - ${v.visit_type})</option>`;
                                });
                            }
                            visitSelect.innerHTML = options;
                        })
                        .catch(err => console.error('Failed to fetch patient visits:', err));
                });
            }

            // 2. Dynamic Bed filter by department
            if (deptSelect) {
                deptSelect.addEventListener('change', function () {
                    const deptId = this.value;
                    const url = `{{ route('portal.api.admissions.available-beds') }}` + (deptId ? `?department_id=${deptId}` : '');

                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            let bedOptions = '<option value="">-- Choose an Available Bed --</option>';
                            if (data.beds && data.beds.length > 0) {
                                data.beds.forEach(b => {
                                    bedOptions += `<option value="${b.id}">Bed ${b.bed_number} (${b.room_number}) — ${b.department_name} · ৳${b.daily_charge}/day [${b.category_label}]</option>`;
                                });
                                document.getElementById('availableBedCount').textContent = `${data.beds.length} Available Beds`;
                            } else {
                                bedOptions = '<option value="">-- No available beds in this department --</option>';
                                document.getElementById('availableBedCount').textContent = '0 Beds Available';
                            }
                            bedSelect.innerHTML = bedOptions;
                        })
                        .catch(err => console.error('Failed to fetch beds:', err));
                });
            }
        });
    </script>
    @endpush
</x-layouts.portal>

