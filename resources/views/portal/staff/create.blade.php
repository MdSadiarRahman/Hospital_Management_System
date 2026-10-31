<x-layouts.portal role="admin" title="Register New Staff Member" activeNav="staff">
    <div class="container-fluid p-0">
        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <x-breadcrumb :items="[
                    'Administration' => url('/previews/admin'),
                    'Staff Directory' => route('portal.staff.index'),
                    'Register Staff Member' => null
                ]" class="mb-1" />
                <h1 class="h3 fw-bold text-navy mb-0">Register New Staff Member</h1>
                <p class="text-muted-hms mb-0" style="font-size: 0.875rem;">
                    Create a personnel record, assign hospital roles, multi-department coverage, and shift parameters.
                </p>
            </div>
            <div>
                <a href="{{ route('portal.staff.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Staff Directory</span>
                </a>
            </div>
        </div>

        <!-- Validation Errors -->
        @if($errors->any())
            <x-alert type="danger" class="mb-4" dismissible>
                <strong class="d-block mb-1">Please correct the following input errors:</strong>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <form action="{{ route('portal.staff.store') }}" method="POST" id="createStaffForm">
            @csrf

            <div class="row g-4">
                <!-- Left Column: Core Account & Clinical Credentials -->
                <div class="col-lg-7">
                    <!-- SECTION 1: User Account Credentials -->
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
                            <span class="rounded bg-navy bg-opacity-10 text-navy p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-person-lock fs-5"></i>
                            </span>
                            <h2 class="h5 fw-bold text-navy mb-0">1. Account Credentials & Login</h2>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-semibold text-navy">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                       placeholder="e.g. Dr. Mohammad Rafiq / Sister Fatema" value="{{ old('name') }}" required maxlength="191">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold text-navy">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                       placeholder="staff@meghnaclinic.com" value="{{ old('email') }}" required maxlength="191">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold text-navy">Mobile Number (BD) <span class="text-danger">*</span></label>
                                <input type="text" name="phone" id="phone" class="form-control font-monospace @error('phone') is-invalid @enderror" 
                                       placeholder="+88017XXXXXXXX" value="{{ old('phone') }}" required maxlength="20">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label fw-semibold text-navy">Initial Password</label>
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                                       placeholder="Leave empty for auto-generated">
                                <div class="form-text">Defaults to <code>Meghna@2026!</code> if left empty.</div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: Professional Credentials & Designation -->
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
                            <span class="rounded bg-primary bg-opacity-10 text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-award fs-5"></i>
                            </span>
                            <h2 class="h5 fw-bold text-navy mb-0">2. Professional Designation & Credentials</h2>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="designation" class="form-label fw-semibold text-navy">Designation / Role Title <span class="text-danger">*</span></label>
                                <input type="text" name="designation" id="designation" class="form-control @error('designation') is-invalid @enderror" 
                                       placeholder="e.g. Senior Consultant, Staff Nurse, Receptionist" value="{{ old('designation') }}" required maxlength="100">
                                @error('designation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="specialization" class="form-label fw-semibold text-navy">Clinical Specialization</label>
                                <input type="text" name="specialization" id="specialization" class="form-control @error('specialization') is-invalid @enderror" 
                                       placeholder="e.g. Obstetrics, Orthopedics, Trauma, Pediatric Nursing" value="{{ old('specialization') }}" maxlength="191">
                            </div>

                            <div class="col-md-6">
                                <label for="license_number" class="form-label fw-semibold text-navy">BMDC / BNMC Registration #</label>
                                <input type="text" name="license_number" id="license_number" class="form-control font-monospace text-uppercase @error('license_number') is-invalid @enderror" 
                                       placeholder="e.g. BMDC-A-41289 or BNMC-RN-18920" value="{{ old('license_number') }}" maxlength="50">
                                <div class="form-text">Required for doctors and licensed nurses.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="qualification" class="form-label fw-semibold text-navy">Educational Qualifications</label>
                                <input type="text" name="qualification" id="qualification" class="form-control" 
                                       placeholder="e.g. MBBS, FCPS, MS (Ortho), B.Sc in Nursing" value="{{ old('qualification') }}" maxlength="191">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: Protected Sensitive Employment Information -->
                    <div class="card border border-warning-subtle shadow-sm rounded-3 bg-white p-4">
                        <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded bg-warning bg-opacity-10 text-warning-emphasis p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="bi bi-shield-lock-fill fs-5"></i>
                                </span>
                                <div>
                                    <h2 class="h5 fw-bold text-navy mb-0">3. Protected Employment Information</h2>
                                    <small class="text-muted">Sensitive data protected by RBAC (Super Admin & Administrator only).</small>
                                </div>
                            </div>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">
                                Restricted Access
                            </span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="salary" class="form-label fw-semibold text-navy">Basic Monthly Salary (BDT)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">৳</span>
                                    <input type="number" step="0.01" min="0" max="9999999.99" name="salary" id="salary" 
                                           class="form-control font-monospace @error('salary') is-invalid @enderror" 
                                           placeholder="e.g. 65000.00" value="{{ old('salary') }}">
                                </div>
                                <div class="form-text">Encrypted and masked from non-administrative staff.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="nid_number" class="form-label fw-semibold text-navy">National ID (NID) Number</label>
                                <input type="text" name="nid_number" id="nid_number" class="form-control font-monospace @error('nid_number') is-invalid @enderror" 
                                       placeholder="e.g. 1982271890001" value="{{ old('nid_number') }}" maxlength="30">
                            </div>

                            <div class="col-md-6">
                                <label for="emergency_contact_name" class="form-label fw-semibold text-navy">Emergency Contact Person</label>
                                <input type="text" name="emergency_contact_name" id="emergency_contact_name" class="form-control" 
                                       placeholder="Spouse, parent, or family guardian" value="{{ old('emergency_contact_name') }}" maxlength="100">
                            </div>

                            <div class="col-md-6">
                                <label for="emergency_contact_phone" class="form-label fw-semibold text-navy">Emergency Contact Mobile</label>
                                <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" class="form-control font-monospace" 
                                       placeholder="+88017XXXXXXXX" value="{{ old('emergency_contact_phone') }}" maxlength="20">
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label fw-semibold text-navy">Residential Address</label>
                                <textarea name="address" id="address" rows="2" class="form-control" 
                                          placeholder="Present/Permanent home address in Dinajpur Sadar...">{{ old('address') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Role Assignment, Department Allocation, Shift Scheduling -->
                <div class="col-lg-5">
                    <!-- SECTION 4: Role Assignment -->
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
                            <span class="rounded bg-teal bg-opacity-10 text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-person-gear fs-5"></i>
                            </span>
                            <h2 class="h5 fw-bold text-navy mb-0">Hospital Roles & Access <span class="text-danger">*</span></h2>
                        </div>

                        <p class="text-muted small mb-3">Select the access roles granted to this personnel account:</p>

                        <div class="d-flex flex-column gap-2 mb-3">
                            @foreach($roles as $role)
                                <div class="form-check p-2.5 rounded bg-light border d-flex align-items-center gap-2">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="role_ids[]" 
                                           value="{{ $role->id }}" id="role_{{ $role->id }}"
                                           {{ in_array($role->id, old('role_ids', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label w-100 cursor-pointer" for="role_{{ $role->id }}">
                                        <div class="fw-bold text-navy" style="font-size: 0.85rem;">{{ $role->title }}</div>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">{{ $role->description }}</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('role_ids') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <!-- SECTION 5: Multi-Department Allocation -->
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
                            <span class="rounded bg-navy bg-opacity-10 text-navy p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-hospital fs-5"></i>
                            </span>
                            <h2 class="h5 fw-bold text-navy mb-0">Department Allocations</h2>
                        </div>

                        <!-- Primary Department -->
                        <div class="mb-3">
                            <label for="department_id" class="form-label fw-semibold text-navy">Primary Department <span class="text-danger">*</span></label>
                            <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                <option value="">-- Select Home Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }} ({{ $dept->code }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Primary reporting station for duty rosters and bed inquiries.</div>
                        </div>

                        <!-- Multi-Department Secondary Cross-Coverage -->
                        <div>
                            <label class="form-label fw-semibold text-navy mb-1">Cross-Department Allocations (Optional)</label>
                            <p class="text-muted small mb-2">Check additional departments where this staff member is authorized to cover duties:</p>

                            <div class="row g-2">
                                @foreach($departments as $dept)
                                    <div class="col-6">
                                        <div class="form-check bg-light p-2 rounded border">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" name="secondary_department_ids[]" 
                                                   value="{{ $dept->id }}" id="sec_dept_{{ $dept->id }}"
                                                   {{ in_array($dept->id, old('secondary_department_ids', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-semibold text-navy cursor-pointer" for="sec_dept_{{ $dept->id }}">
                                                {{ $dept->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 6: Shift Scheduling & Employment Parameters -->
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
                            <span class="rounded bg-info bg-opacity-10 text-info p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-clock-history fs-5"></i>
                            </span>
                            <h2 class="h5 fw-bold text-navy mb-0">Shift & Employment Status</h2>
                        </div>

                        <div class="mb-3">
                            <label for="shift" class="form-label fw-semibold text-navy">Default 8-Hour Shift Schedule <span class="text-danger">*</span></label>
                            <select name="shift" id="shift" class="form-select @error('shift') is-invalid @enderror" required>
                                <option value="morning" {{ old('shift') === 'morning' ? 'selected' : '' }}>Morning Shift (08:00 AM - 04:00 PM)</option>
                                <option value="evening" {{ old('shift') === 'evening' ? 'selected' : '' }}>Evening Shift (04:00 PM - 12:00 AM)</option>
                                <option value="night" {{ old('shift') === 'night' ? 'selected' : '' }}>Night Shift (12:00 AM - 08:00 AM)</option>
                                <option value="rotational" {{ old('shift', 'rotational') === 'rotational' ? 'selected' : '' }}>Rotational / On-Call Duty</option>
                            </select>
                            <div class="form-text">6 nurses rotate through the 3 8h shifts; 2 doctors 24/7 on duty.</div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="employment_type" class="form-label fw-semibold text-navy">Contract Type <span class="text-danger">*</span></label>
                                <select name="employment_type" id="employment_type" class="form-select" required>
                                    <option value="full_time" {{ old('employment_type', 'full_time') === 'full_time' ? 'selected' : '' }}>Full Time</option>
                                    <option value="part_time" {{ old('employment_type') === 'part_time' ? 'selected' : '' }}>Part Time</option>
                                    <option value="visiting_consultant" {{ old('employment_type') === 'visiting_consultant' ? 'selected' : '' }}>Visiting Consultant</option>
                                    <option value="contractual" {{ old('employment_type') === 'contractual' ? 'selected' : '' }}>Contractual</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="joining_date" class="form-label fw-semibold text-navy">Joining Date</label>
                                <input type="date" name="joining_date" id="joining_date" class="form-control" value="{{ old('joining_date', date('Y-m-d')) }}">
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <label for="employee_id" class="form-label fw-semibold text-navy">Employee ID</label>
                                <input type="text" name="employee_id" id="employee_id" class="form-control font-monospace text-uppercase" 
                                       placeholder="Auto-generated if empty" value="{{ old('employee_id') }}" maxlength="32">
                            </div>
                            <div class="col-6">
                                <label for="status" class="form-label fw-semibold text-navy">Employment Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active Duty</option>
                                    <option value="on_leave" {{ old('status') === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive / Resigned</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Submission Actions -->
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('portal.staff.index') }}" class="btn btn-light px-4">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                                <i class="bi bi-person-check-fill"></i>
                                <span>Register Staff Member</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layouts.portal>
