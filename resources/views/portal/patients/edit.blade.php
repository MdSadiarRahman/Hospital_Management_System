<x-layouts.portal role="reception" title="Edit Patient — {{ $patient->patient_code }}">
    <div class="container-fluid px-0">
        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('portal.patients.index') }}" class="text-decoration-none text-muted-hms">Patient Registry</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('portal.patients.show', $patient->id) }}" class="text-decoration-none text-muted-hms">{{ $patient->patient_code }}</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Edit</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold text-navy mb-0">Edit Patient Master Record</h1>
                <p class="text-muted-hms mb-0" style="font-size: 0.875rem;">
                    Update demographic details, primary contact number, and emergency guardian for <strong class="text-navy font-monospace">{{ $patient->patient_code }}</strong>.
                </p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portal.patients.show', $patient->id) }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </div>

        <form action="{{ route('portal.patients.update', $patient->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <!-- Main Form (Left 8 Columns) -->
                <div class="col-lg-8">
                    <!-- Section 1: Demographics -->
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
                            <span class="rounded bg-primary bg-opacity-10 text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-person-vcard fs-5"></i>
                            </span>
                            <h2 class="h5 fw-bold text-navy mb-0">Demographic Information</h2>
                        </div>

                        <!-- Patient Code & Full Name -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-navy">Patient ID</label>
                                <input type="text" class="form-control font-monospace bg-light fw-bold text-navy" 
                                       value="{{ $patient->patient_code }}" readonly disabled>
                                <div class="form-text">System ID cannot be changed.</div>
                            </div>

                            <div class="col-md-8">
                                <label for="name" class="form-label fw-semibold text-navy">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $patient->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- DOB, Gender, Blood Group -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="date_of_birth" class="form-label fw-semibold text-navy">
                                    Date of Birth <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="date_of_birth" id="date_of_birth" 
                                       class="form-control @error('date_of_birth') is-invalid @enderror" 
                                       value="{{ old('date_of_birth', $patient->date_of_birth?->format('Y-m-d')) }}" 
                                       max="{{ date('Y-m-d') }}" required>
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
                                               @checked(old('gender', $patient->gender) === 'female') required>
                                        <label class="form-check-label fw-semibold text-navy" for="gender_female">Female</label>
                                    </div>
                                    <div class="form-check form-check-inline flex-fill border rounded p-2 text-center bg-light">
                                        <input class="form-check-input" type="radio" name="gender" id="gender_male" value="male" 
                                               @checked(old('gender', $patient->gender) === 'male') required>
                                        <label class="form-check-label fw-semibold text-navy" for="gender_male">Male</label>
                                    </div>
                                    <div class="form-check form-check-inline border rounded p-2 text-center bg-light">
                                        <input class="form-check-input" type="radio" name="gender" id="gender_other" value="other" 
                                               @checked(old('gender', $patient->gender) === 'other') required>
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
                                        <option value="{{ $bg }}" @selected(old('blood_group', $patient->blood_group) === $bg)>{{ $bg }}</option>
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
                                <input type="text" name="phone" id="phone" 
                                       class="form-control font-monospace @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone', $patient->phone) }}" required maxlength="20">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold text-navy">
                                    Email Address <span class="text-muted fw-normal">(Optional)</span>
                                </label>
                                <input type="email" name="email" id="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $patient->email) }}" maxlength="191">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
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
                                       value="{{ old('nid_or_birth_cert', $patient->nid_or_birth_cert) }}" maxlength="30">
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
                                       value="{{ old('registration_date', $patient->registration_date?->format('Y-m-d') ?? $patient->created_at->format('Y-m-d')) }}" 
                                       max="{{ date('Y-m-d') }}" required>
                                @error('registration_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="mb-0">
                            <label for="address" class="form-label fw-semibold text-navy">
                                Residential Address
                            </label>
                            <textarea name="address" id="address" rows="2" 
                                      class="form-control @error('address') is-invalid @enderror">{{ old('address', $patient->address) }}</textarea>
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

                        <!-- Contact Name -->
                        <div class="mb-3">
                            <label for="emergency_contact_name" class="form-label fw-semibold text-navy">
                                Kin Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="emergency_contact_name" id="emergency_contact_name" 
                                   class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                   value="{{ old('emergency_contact_name', $patient->primaryContact?->name) }}" required>
                            @error('emergency_contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Relationship -->
                        <div class="mb-3">
                            <label for="emergency_contact_relationship" class="form-label fw-semibold text-navy">
                                Relationship <span class="text-danger">*</span>
                            </label>
                            <select name="emergency_contact_relationship" id="emergency_contact_relationship" 
                                    class="form-select @error('emergency_contact_relationship') is-invalid @enderror" required>
                                <option value="">Select Relationship</option>
                                @foreach(['Father', 'Mother', 'Spouse', 'Child', 'Sibling', 'Guardian', 'Relative', 'Friend', 'Other'] as $rel)
                                    <option value="{{ $rel }}" @selected(old('emergency_contact_relationship', $patient->primaryContact?->relationship) === $rel)>{{ $rel }}</option>
                                @endforeach
                            </select>
                            @error('emergency_contact_relationship')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Emergency Phone -->
                        <div class="mb-0">
                            <label for="emergency_contact_phone" class="form-label fw-semibold text-navy">
                                Emergency Phone <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" 
                                   class="form-control font-monospace @error('emergency_contact_phone') is-invalid @enderror" 
                                   value="{{ old('emergency_contact_phone', $patient->primaryContact?->phone) }}" required maxlength="20">
                            @error('emergency_contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
                        <div class="d-flex flex-column gap-2">
                            <button type="submit" class="btn btn-primary btn-lg w-100 d-inline-flex align-items-center justify-content-center gap-2 shadow-sm">
                                <i class="bi bi-save2-fill"></i>
                                <span>Save Changes</span>
                            </button>
                            <a href="{{ route('portal.patients.show', $patient->id) }}" class="btn btn-light w-100">
                                Discard & Back
                            </a>
                        </div>
                        <div class="mt-3 text-muted text-center" style="font-size: 0.75rem;">
                            <i class="bi bi-shield-check text-success"></i> Changes are recorded in security audit logs.
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layouts.portal>
