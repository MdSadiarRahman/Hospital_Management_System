<x-layouts.portal role="reception" title="Register New Patient">
    <div class="container-fluid px-0">
        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('portal.patients.index') }}" class="text-decoration-none text-muted-hms">Patient Registry</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Intake & Registration</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold text-navy mb-0">Register New Hospital Patient</h1>
                <p class="text-muted-hms mb-0" style="font-size: 0.875rem;">
                    Meghna Clinic and Nursing Home master clinical registry intake.
                </p>
            </div>

            <a href="{{ route('portal.patients.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Back to Patient Directory
            </a>
        </div>

        <!-- DUPLICATE WARNING MODAL / BANNER (If DuplicatePatientException was thrown on submit) -->
        @if(session('duplicate_warning'))
            <div class="alert alert-warning border border-warning shadow-sm rounded-3 p-4 mb-4" role="alert">
                <div class="d-flex align-items-start gap-3">
                    <span class="rounded bg-warning bg-opacity-25 text-warning-emphasis p-2 d-inline-flex">
                        <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                    </span>
                    <div class="flex-grow-1">
                        <h4 class="alert-heading fw-bold text-warning-emphasis h5 mb-1">
                            Potential Duplicate Patient Records Detected!
                        </h4>
                        <p class="mb-3" style="font-size: 0.875rem;">
                            {{ session('duplicate_warning') }} Duplicate records are <strong>never automatically merged</strong> to protect patient privacy and clinical record integrity.
                        </p>

                        <!-- Existing Matching Patients Cards -->
                        @if(session('duplicate_patients') && count(session('duplicate_patients')) > 0)
                            <div class="row g-2 mb-3">
                                @foreach(session('duplicate_patients') as $dup)
                                    <div class="col-md-6">
                                        <div class="p-3 rounded border bg-white shadow-xs">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="badge bg-navy text-white font-monospace">{{ $dup->patient_code }}</span>
                                                <span class="text-muted small">Registered: {{ $dup->registration_date ? $dup->registration_date->format('d M Y') : $dup->created_at->format('d M Y') }}</span>
                                            </div>
                                            <div class="fw-bold text-navy">{{ $dup->name }}</div>
                                            <div class="small text-muted mb-2">
                                                <span>{{ $dup->formatted_age }}</span> &bull; 
                                                <span class="text-capitalize">{{ $dup->gender }}</span> &bull;
                                                <i class="bi bi-telephone ms-1"></i> {{ $dup->phone }}
                                                @if($dup->blood_group) &bull; <span class="badge bg-danger bg-opacity-10 text-danger">{{ $dup->blood_group }}</span>@endif
                                            </div>
                                            <a href="{{ route('portal.patients.show', $dup->id) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0.5" style="font-size: 0.775rem;">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> View Existing Profile
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="form-check bg-white p-3 rounded border border-warning">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="confirm_not_duplicate" value="1" id="confirm_duplicate_override" form="patientRegisterForm">
                            <label class="form-check-label fw-bold text-navy" for="confirm_duplicate_override">
                                I confirm: This is a separate and distinct patient (e.g. family member sharing phone, or namesake). Proceed with registration.
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Real-Time Duplicate Warning Alert (Triggered via AJAX) -->
        <div id="liveDuplicateAlert" class="alert alert-info border border-info shadow-sm rounded-3 p-3 mb-4 d-none" role="alert">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill text-info fs-5"></i>
                    <div>
                        <strong class="text-navy">Clinic Database Notice:</strong>
                        <span id="liveDuplicateMessage" class="text-navy">Matching records found with this phone number or name.</span>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-info" id="viewLiveDuplicatesBtn">
                    View Matches (<span id="liveDuplicateCount">0</span>)
                </button>
            </div>
            <div id="liveDuplicatesContainer" class="mt-3 pt-3 border-top border-info-subtle d-none">
                <div class="row g-2" id="liveDuplicatesList"></div>
            </div>
        </div>

        <!-- Registration Form -->
        <form action="{{ route('portal.patients.store') }}" method="POST" id="patientRegisterForm">
            @csrf

            <div class="row g-4">
                <!-- Main Form (Left 8 Columns) -->
                <div class="col-lg-8">
                    <!-- Section 1: Patient Master Demographics -->
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
                            <span class="rounded bg-primary bg-opacity-10 text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-person-vcard fs-5"></i>
                            </span>
                            <h2 class="h5 fw-bold text-navy mb-0">Patient Demographic Master Record</h2>
                        </div>

                        <!-- Patient Code & Full Name -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="patient_code" class="form-label fw-semibold text-navy">
                                    Patient ID <span class="text-muted fw-normal">(Unique Code)</span>
                                </label>
                                <input type="text" name="patient_code" id="patient_code" 
                                       class="form-control font-monospace text-navy fw-bold bg-light" 
                                       value="{{ old('patient_code', $suggestedPatientId) }}" 
                                       placeholder="MCNH-2026-0001" readonly required>
                                <div class="form-text">System auto-generated format: <code>MCNH-YYYY-XXXX</code></div>
                            </div>

                            <div class="col-md-8">
                                <label for="name" class="form-label fw-semibold text-navy">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" placeholder="e.g. Most. Rokeya Akhter" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- DOB, Age Preview, Gender, Blood Group -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="date_of_birth" class="form-label fw-semibold text-navy">
                                    Date of Birth <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="date_of_birth" id="date_of_birth" 
                                       class="form-control @error('date_of_birth') is-invalid @enderror" 
                                       value="{{ old('date_of_birth') }}" 
                                       max="{{ date('Y-m-d') }}" required>
                                <div class="form-text text-teal fw-semibold mt-1" id="agePreview">
                                    Age: Please select date
                                </div>
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-navy">
                                    Gender <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex gap-2 pt-1">
                                    <div class="form-check form-check-inline flex-fill border rounded p-2 text-center bg-light">
                                        <input class="form-check-input" type="radio" name="gender" id="gender_female" value="female" 
                                               @checked(old('gender', 'female') === 'female') required>
                                        <label class="form-check-label fw-semibold text-navy" for="gender_female">Female</label>
                                    </div>
                                    <div class="form-check form-check-inline flex-fill border rounded p-2 text-center bg-light">
                                        <input class="form-check-input" type="radio" name="gender" id="gender_male" value="male" 
                                               @checked(old('gender') === 'male') required>
                                        <label class="form-check-label fw-semibold text-navy" for="gender_male">Male</label>
                                    </div>
                                    <div class="form-check form-check-inline border rounded p-2 text-center bg-light">
                                        <input class="form-check-input" type="radio" name="gender" id="gender_other" value="other" 
                                               @checked(old('gender') === 'other') required>
                                        <label class="form-check-label fw-semibold text-navy" for="gender_other">Other</label>
                                    </div>
                                </div>
                                @error('gender')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="blood_group" class="form-label fw-semibold text-navy">
                                    Blood Group <span class="text-muted fw-normal">(Optional)</span>
                                </label>
                                <select name="blood_group" id="blood_group" class="form-select @error('blood_group') is-invalid @enderror">
                                    <option value="">Unknown / Not Tested</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                        <option value="{{ $bg }}" @selected(old('blood_group') === $bg)>{{ $bg }}</option>
                                    @endforeach
                                </select>
                                @error('blood_group')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Contact & Address Information -->
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
                            <span class="rounded bg-teal bg-opacity-10 text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-geo-alt-fill fs-5"></i>
                            </span>
                            <h2 class="h5 fw-bold text-navy mb-0">Contact & Address Details</h2>
                        </div>

                        <!-- Phone & Email -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold text-navy">
                                    Primary Phone Number <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">
                                        <i class="bi bi-telephone-fill"></i>
                                    </span>
                                    <input type="text" name="phone" id="phone" 
                                           class="form-control font-monospace @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone') }}" placeholder="017XXXXXXXX" required maxlength="20">
                                </div>
                                <div class="form-text">Primary contact for appointment reminders and test reports.</div>
                                @error('phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold text-navy">
                                    Email Address <span class="text-muted fw-normal">(Optional)</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">
                                        <i class="bi bi-envelope-fill"></i>
                                    </span>
                                    <input type="email" name="email" id="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email') }}" placeholder="patient@example.com" maxlength="191">
                                </div>
                                <div class="form-text">Used for PDF invoices & online patient portal linking.</div>
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- NID & Registration Date -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="nid_or_birth_cert" class="form-label fw-semibold text-navy">
                                    NID or Birth Certificate <span class="text-muted fw-normal">(Optional)</span>
                                </label>
                                <input type="text" name="nid_or_birth_cert" id="nid_or_birth_cert" 
                                       class="form-control font-monospace @error('nid_or_birth_cert') is-invalid @enderror" 
                                       value="{{ old('nid_or_birth_cert') }}" placeholder="National ID / Smart NID / Birth Reg No" maxlength="30">
                                @error('nid_or_birth_cert')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="registration_date" class="form-label fw-semibold text-navy">
                                    Registration Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="registration_date" id="registration_date" 
                                       class="form-control @error('registration_date') is-invalid @enderror" 
                                       value="{{ old('registration_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                                @error('registration_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="mb-0">
                            <label for="address" class="form-label fw-semibold text-navy">
                                Residential Address <span class="text-muted fw-normal">(Village/Ward, Thana, District)</span>
                            </label>
                            <textarea name="address" id="address" rows="2" 
                                      class="form-control @error('address') is-invalid @enderror" 
                                      placeholder="e.g. Shohid Minar Mor, Dinajpur Sadar, Dinajpur">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Right Side (Col-4): Emergency Contact & Actions -->
                <div class="col-lg-4">
                    <!-- Section 3: Emergency Contact (Next of Kin) -->
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
                            <span class="rounded bg-danger bg-opacity-10 text-danger p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-telephone-plus-fill fs-5"></i>
                            </span>
                            <h2 class="h5 fw-bold text-navy mb-0">Emergency Contact</h2>
                        </div>
                        <p class="text-muted-hms mb-3" style="font-size: 0.8rem;">
                            Primary next of kin or guardian required for clinical consent and emergency hospital notification.
                        </p>

                        <!-- Contact Name -->
                        <div class="mb-3">
                            <label for="emergency_contact_name" class="form-label fw-semibold text-navy">
                                Guardian / Kin Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="emergency_contact_name" id="emergency_contact_name" 
                                   class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                   value="{{ old('emergency_contact_name') }}" placeholder="e.g. Md. Abdul Karim" required>
                            @error('emergency_contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Relationship -->
                        <div class="mb-3">
                            <label for="emergency_contact_relationship" class="form-label fw-semibold text-navy">
                                Relationship to Patient <span class="text-danger">*</span>
                            </label>
                            <select name="emergency_contact_relationship" id="emergency_contact_relationship" 
                                    class="form-select @error('emergency_contact_relationship') is-invalid @enderror" required>
                                <option value="">Select Relationship</option>
                                @foreach(['Father', 'Mother', 'Spouse', 'Child', 'Sibling', 'Guardian', 'Relative', 'Friend', 'Other'] as $rel)
                                    <option value="{{ $rel }}" @selected(old('emergency_contact_relationship') === $rel)>{{ $rel }}</option>
                                @endforeach
                            </select>
                            @error('emergency_contact_relationship')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Emergency Phone -->
                        <div class="mb-0">
                            <label for="emergency_contact_phone" class="form-label fw-semibold text-navy">
                                Emergency Contact Phone <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">
                                    <i class="bi bi-phone"></i>
                                </span>
                                <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" 
                                       class="form-control font-monospace @error('emergency_contact_phone') is-invalid @enderror" 
                                       value="{{ old('emergency_contact_phone') }}" placeholder="017XXXXXXXX" required maxlength="20">
                            </div>
                            @error('emergency_contact_phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Actions & Submit Card -->
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
                        <div class="d-flex flex-column gap-2">
                            <button type="submit" class="btn btn-primary btn-lg w-100 d-inline-flex align-items-center justify-content-center gap-2 shadow-sm" id="submitBtn">
                                <i class="bi bi-person-check-fill"></i>
                                <span>Register Patient Master Record</span>
                            </button>
                            <a href="{{ route('portal.patients.index') }}" class="btn btn-light w-100">
                                Cancel
                            </a>
                        </div>
                        <div class="mt-3 text-muted text-center" style="font-size: 0.75rem;">
                            <i class="bi bi-shield-check text-success"></i> Changes are recorded in hospital security audit history.
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dobInput = document.getElementById('date_of_birth');
            const agePreview = document.getElementById('agePreview');
            const phoneInput = document.getElementById('phone');
            const nameInput = document.getElementById('name');
            const liveAlert = document.getElementById('liveDuplicateAlert');
            const liveMsg = document.getElementById('liveDuplicateMessage');
            const liveCount = document.getElementById('liveDuplicateCount');
            const viewLiveBtn = document.getElementById('viewLiveDuplicatesBtn');
            const liveContainer = document.getElementById('liveDuplicatesContainer');
            const liveList = document.getElementById('liveDuplicatesList');

            // 1. Calculate Age Preview dynamically
            function updateAgePreview() {
                if (!dobInput.value) {
                    agePreview.textContent = 'Age: Please select date';
                    return;
                }
                const dob = new Date(dobInput.value);
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const m = today.getMonth() - dob.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                    age--;
                }
                if (age >= 1) {
                    agePreview.textContent = `Calculated Age: ${age} years old`;
                } else {
                    const months = (today.getFullYear() - dob.getFullYear()) * 12 + (today.getMonth() - dob.getMonth());
                    agePreview.textContent = `Calculated Age: ${months > 0 ? months + ' months' : 'Newborn'}`;
                }
            }
            dobInput.addEventListener('change', updateAgePreview);
            if (dobInput.value) updateAgePreview();

            // 2. Real-time Duplicate Check via Debounce
            let debounceTimer = null;
            function triggerDuplicateCheck() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    const phone = phoneInput.value.trim();
                    const name = nameInput.value.trim();
                    const dob = dobInput.value;

                    if (phone.length < 5 && (name.length < 3 || !dob)) {
                        liveAlert.classList.add('d-none');
                        return;
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    fetch("{{ route('portal.patients.check-duplicates') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ phone: phone, name: name, date_of_birth: dob })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.has_duplicates) {
                            liveAlert.classList.remove('d-none');
                            liveCount.textContent = data.count;
                            liveMsg.textContent = `${data.count} existing patient record(s) share this phone number or identity details.`;
                            
                            // Populate live list
                            liveList.innerHTML = '';
                            data.duplicates.forEach(dup => {
                                const col = document.createElement('div');
                                col.className = 'col-md-6';
                                col.innerHTML = `
                                    <div class="p-2.5 rounded border bg-white small">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong class="text-navy">${dup.name}</strong>
                                            <span class="badge bg-navy text-white font-monospace">${dup.patient_code}</span>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            ${dup.age} &bull; ${dup.gender} &bull; Tel: ${dup.phone}
                                        </div>
                                        <a href="${dup.url}" target="_blank" class="small text-primary text-decoration-none d-inline-block mt-1">
                                            Open Existing Record &rarr;
                                        </a>
                                    </div>
                                `;
                                liveList.appendChild(col);
                            });
                        } else {
                            liveAlert.classList.add('d-none');
                        }
                    })
                    .catch(err => console.error('Duplicate check error:', err));
                }, 500);
            }

            phoneInput.addEventListener('input', triggerDuplicateCheck);
            nameInput.addEventListener('input', triggerDuplicateCheck);
            dobInput.addEventListener('change', triggerDuplicateCheck);

            viewLiveBtn.addEventListener('click', function () {
                liveContainer.classList.toggle('d-none');
            });
        });
    </script>
    @endpush
</x-layouts.portal>
