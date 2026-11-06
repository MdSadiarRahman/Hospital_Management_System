<x-layouts.portal title="Schedule Operation — Meghna Clinic">
    <div class="container-fluid py-4">

        {{-- Breadcrumb & Header --}}
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none">Portal</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('portal.operations.index') }}" class="text-decoration-none">Operation Theatre (OT)</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Schedule Operation</li>
                </ol>
            </nav>
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h1 class="h3 fw-bold text-navy mb-0">Schedule Surgical Operation</h1>
                    <p class="text-muted small mb-0">Book an Operating Theatre slot, assign the surgical team, and prevent conflicting schedules.</p>
                </div>
                <a href="{{ route('portal.operations.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Roster
                </a>
            </div>
        </div>

        {{-- Conflict & Double Booking Protection Notice --}}
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4 rounded-3" role="alert">
            <i class="bi bi-shield-check fs-4 me-3 text-info"></i>
            <div>
                <strong class="d-block text-navy">Pessimistic Concurrency & Double-Booking Protection Active</strong>
                <span class="small text-muted">The system automatically prevents conflicting schedules for the selected Operating Theatre room, the assigned Primary Surgeon, and the patient within the specified time window.</span>
            </div>
        </div>

        {{-- Errors alert --}}
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3" role="alert">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-exclamation-octagon-fill fs-5 me-2"></i>
                    <strong>Cannot Schedule Operation:</strong>
                </div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('portal.operations.store') }}" method="POST" id="scheduleOperationForm">
            @csrf

            <div class="row g-4">
                {{-- Left Column: Patient & Clinical Information --}}
                <div class="col-12 col-lg-7">
                    {{-- Patient & Admission Linkage --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title fw-bold text-navy mb-0">
                                <i class="bi bi-person-heart text-teal me-2"></i>1. Patient & Inpatient Admission
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="admission_id" class="form-label fw-semibold text-dark">
                                    Admitted Patient <span class="text-danger">*</span>
                                </label>
                                <select name="admission_id" id="admission_id" class="form-select @error('admission_id') is-invalid @enderror" required>
                                    <option value="">-- Select Inpatient Admission --</option>
                                    @foreach($admissions as $adm)
                                        <option value="{{ $adm->id }}" 
                                            data-patient-name="{{ $adm->patient?->name }}"
                                            data-patient-code="{{ $adm->patient?->patient_code }}"
                                            data-patient-age="{{ $adm->patient?->formatted_age }}"
                                            data-patient-gender="{{ ucfirst($adm->patient?->gender ?? '') }}"
                                            data-patient-blood="{{ $adm->patient?->blood_group ?? 'Unknown' }}"
                                            data-bed-number="{{ $adm->currentBedAssignment?->bed?->bed_number ?? 'No Bed' }}"
                                            data-dept-id="{{ $adm->department_id }}"
                                            data-doctor-id="{{ $adm->attending_doctor_id }}"
                                            {{ (old('admission_id', $selectedAdmission?->id) == $adm->id) ? 'selected' : '' }}>
                                            {{ $adm->patient?->name }} ({{ $adm->patient?->patient_code }}) — Bed: {{ $adm->currentBedAssignment?->bed?->bed_number ?? 'N/A' }} (Adm: {{ $adm->admission_number }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('admission_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Operations must be linked to an active inpatient admission record.</div>
                            </div>

                            {{-- Dynamic Patient Dossier Card --}}
                            <div id="patientInfoCard" class="p-3 rounded-3 bg-light border d-none">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-teal text-white" id="patientInfoCode">MCNH-...</span>
                                    <span class="badge bg-light text-dark border" id="patientInfoBed">Bed: --</span>
                                </div>
                                <h6 class="fw-bold text-navy mb-1" id="patientInfoName">Patient Name</h6>
                                <div class="small text-muted">
                                    <span id="patientInfoAge">-- yrs</span>, 
                                    <span id="patientInfoGender">--</span> | 
                                    Blood Group: <strong class="text-danger" id="patientInfoBlood">--</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Surgical Procedure & Indication --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title fw-bold text-navy mb-0">
                                <i class="bi bi-scissors text-teal me-2"></i>2. Procedure & Clinical Details
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="procedure_name" class="form-label fw-semibold text-dark">
                                    Surgical Procedure Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="procedure_name" id="procedure_name" 
                                    class="form-control @error('procedure_name') is-invalid @enderror" 
                                    placeholder="e.g. Appendectomy, Cesarean Section, Cholecystectomy, ORIF" 
                                    value="{{ old('procedure_name') }}" required>
                                @error('procedure_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label for="department_id" class="form-label fw-semibold text-dark">
                                        Clinical Department
                                    </label>
                                    <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                        <option value="">-- Auto-inherit from Admission --</option>
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

                                <div class="col-12 col-md-6">
                                    <label for="urgency" class="form-label fw-semibold text-dark">
                                        Clinical Urgency <span class="text-danger">*</span>
                                    </label>
                                    <select name="urgency" id="urgency" class="form-select @error('urgency') is-invalid @enderror" required>
                                        <option value="elective" {{ old('urgency', 'elective') === 'elective' ? 'selected' : '' }}>Elective (Routine)</option>
                                        <option value="urgent" {{ old('urgency') === 'urgent' ? 'selected' : '' }}>Urgent (Within 24-48h)</option>
                                        <option value="emergency" {{ old('urgency') === 'emergency' ? 'selected' : '' }}>Emergency (Immediate)</option>
                                    </select>
                                    @error('urgency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="indication" class="form-label fw-semibold text-dark">
                                    Pre-Operative Diagnosis / Clinical Indication
                                </label>
                                <textarea name="indication" id="indication" rows="2" 
                                    class="form-control @error('indication') is-invalid @enderror" 
                                    placeholder="e.g. Acute appendicitis with localized peritonitis, Obstructed labor...">{{ old('indication') }}</textarea>
                                @error('indication')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="operation_notes" class="form-label fw-semibold text-dark">
                                    Pre-Operative Instructions & Plan (Optional)
                                </label>
                                <textarea name="operation_notes" id="operation_notes" rows="3" 
                                    class="form-control @error('operation_notes') is-invalid @enderror" 
                                    placeholder="NPO status orders, cross-matched blood units, antibiotic prophylaxis...">{{ old('operation_notes') }}</textarea>
                                @error('operation_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Schedule & Surgical Team --}}
                <div class="col-12 col-lg-5">
                    {{-- Schedule & Room Allocation --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title fw-bold text-navy mb-0">
                                <i class="bi bi-clock-history text-teal me-2"></i>3. OT Suite & Time Window
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="ot_room" class="form-label fw-semibold text-dark">
                                    Operating Theatre (OT Room) <span class="text-danger">*</span>
                                </label>
                                <select name="ot_room" id="ot_room" class="form-select @error('ot_room') is-invalid @enderror" required>
                                    @foreach($otRooms as $room)
                                        <option value="{{ $room }}" {{ old('ot_room', 'OT-1 (Main Theatre)') === $room ? 'selected' : '' }}>
                                            {{ $room }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ot_room')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="scheduled_at" class="form-label fw-semibold text-dark">
                                    Scheduled Date & Time <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" name="scheduled_at" id="scheduled_at" 
                                    class="form-control @error('scheduled_at') is-invalid @enderror" 
                                    value="{{ old('scheduled_at', now()->addHours(2)->format('Y-m-d\TH:i')) }}" required>
                                @error('scheduled_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="estimated_duration_minutes" class="form-label fw-semibold text-dark">
                                    Estimated Duration (Minutes) <span class="text-danger">*</span>
                                </label>
                                <select name="estimated_duration_minutes" id="estimated_duration_minutes" 
                                    class="form-select @error('estimated_duration_minutes') is-invalid @enderror" required>
                                    <option value="30" {{ old('estimated_duration_minutes') == 30 ? 'selected' : '' }}>30 Minutes (Minor procedure)</option>
                                    <option value="45" {{ old('estimated_duration_minutes') == 45 ? 'selected' : '' }}>45 Minutes</option>
                                    <option value="60" {{ old('estimated_duration_minutes', 60) == 60 ? 'selected' : '' }}>60 Minutes (1 Hour)</option>
                                    <option value="90" {{ old('estimated_duration_minutes') == 90 ? 'selected' : '' }}>90 Minutes (1.5 Hours)</option>
                                    <option value="120" {{ old('estimated_duration_minutes') == 120 ? 'selected' : '' }}>120 Minutes (2 Hours)</option>
                                    <option value="180" {{ old('estimated_duration_minutes') == 180 ? 'selected' : '' }}>180 Minutes (3 Hours)</option>
                                    <option value="240" {{ old('estimated_duration_minutes') == 240 ? 'selected' : '' }}>240 Minutes (4 Hours)</option>
                                </select>
                                @error('estimated_duration_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Used for automatic collision checking with other operations.</div>
                            </div>
                        </div>
                    </div>

                    {{-- Surgical Team Allocation --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title fw-bold text-navy mb-0">
                                <i class="bi bi-people-fill text-teal me-2"></i>4. Surgical Team Assignment
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="primary_surgeon_id" class="form-label fw-semibold text-dark">
                                    Lead / Primary Surgeon <span class="text-danger">*</span>
                                </label>
                                <select name="primary_surgeon_id" id="primary_surgeon_id" class="form-select @error('primary_surgeon_id') is-invalid @enderror" required>
                                    <option value="">-- Select Primary Surgeon --</option>
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}" {{ old('primary_surgeon_id', auth()->user()->hasRole('doctor') ? auth()->id() : '') == $doc->id ? 'selected' : '' }}>
                                            Dr. {{ $doc->name }} ({{ $doc->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('primary_surgeon_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Conflict check prevents the lead surgeon from being double-booked.</div>
                            </div>

                            <div class="mb-3">
                                <label for="assistant_surgeon_id" class="form-label fw-semibold text-dark">
                                    Assistant Surgeon (Optional)
                                </label>
                                <select name="assistant_surgeon_id" id="assistant_surgeon_id" class="form-select @error('assistant_surgeon_id') is-invalid @enderror">
                                    <option value="">-- None / Solo Surgery --</option>
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}" {{ old('assistant_surgeon_id') == $doc->id ? 'selected' : '' }}>
                                            Dr. {{ $doc->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assistant_surgeon_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="anesthetist_name" class="form-label fw-semibold text-dark">
                                    Anesthetist Name (Optional)
                                </label>
                                <input type="text" name="anesthetist_name" id="anesthetist_name" 
                                    class="form-control @error('anesthetist_name') is-invalid @enderror" 
                                    placeholder="e.g. Dr. M. Rahman (Consultant Anesthesiologist)" 
                                    value="{{ old('anesthetist_name') }}">
                                @error('anesthetist_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="ot_nurse_name" class="form-label fw-semibold text-dark">
                                    Assigned OT / Scrub Nurse (Optional)
                                </label>
                                <input type="text" name="ot_nurse_name" id="ot_nurse_name" 
                                    class="form-control @error('ot_nurse_name') is-invalid @enderror" 
                                    placeholder="e.g. Sister Halima Begum" 
                                    value="{{ old('ot_nurse_name') }}">
                                @error('ot_nurse_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-teal text-white py-3 fw-bold fs-6">
                            <i class="bi bi-calendar2-check-fill me-2"></i>Confirm & Schedule Operation
                        </button>
                        <a href="{{ route('portal.operations.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>

    </div>

    {{-- Script for Patient Info card update --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const admissionSelect = document.getElementById('admission_id');
            const patientCard = document.getElementById('patientInfoCard');
            const nameEl = document.getElementById('patientInfoName');
            const codeEl = document.getElementById('patientInfoCode');
            const bedEl = document.getElementById('patientInfoBed');
            const ageEl = document.getElementById('patientInfoAge');
            const genderEl = document.getElementById('patientInfoGender');
            const bloodEl = document.getElementById('patientInfoBlood');

            function updateCard() {
                const selected = admissionSelect.options[admissionSelect.selectedIndex];
                if (selected && selected.value) {
                    nameEl.textContent = selected.getAttribute('data-patient-name') || 'N/A';
                    codeEl.textContent = selected.getAttribute('data-patient-code') || 'MCNH-...';
                    bedEl.textContent = 'Bed: ' + (selected.getAttribute('data-bed-number') || 'N/A');
                    ageEl.textContent = selected.getAttribute('data-patient-age') || '--';
                    genderEl.textContent = selected.getAttribute('data-patient-gender') || '--';
                    bloodEl.textContent = selected.getAttribute('data-patient-blood') || '--';
                    patientCard.classList.remove('d-none');
                } else {
                    patientCard.classList.add('d-none');
                }
            }

            admissionSelect.addEventListener('change', updateCard);
            updateCard();
        });
    </script>
</x-layouts.portal>

