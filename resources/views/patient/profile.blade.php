<x-layouts.patient title="My Account & Guardians" activeNav="profile">
    <div class="container-fluid p-0">
        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <x-breadcrumb :items="['Dashboard' => route('patient.dashboard'), 'My Account & Guardians' => null]" class="mb-1" />
                <h1 class="h3 fw-bold text-navy mb-0">My Account & Guardians</h1>
                <p class="text-muted-hms mb-0" style="font-size: 0.875rem;">
                    Manage your personal demographics, emergency contacts, and portal security.
                </p>
            </div>
            <div>
                @if($isLinked)
                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold border border-success-subtle">
                        <i class="bi bi-shield-check-fill me-1"></i> Verified Patient: {{ $patient->patient_code }}
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fw-semibold border border-warning-subtle">
                        <i class="bi bi-shield-exclamation me-1"></i> Pending In-Person Verification
                    </span>
                @endif
            </div>
        </div>

        <!-- Session Status & Errors -->
        @if(session('status'))
            <x-alert type="success" class="mb-4" dismissible>
                {{ session('status') }}
            </x-alert>
        @endif

        @if(session('error'))
            <x-alert type="danger" class="mb-4" dismissible>
                {{ session('error') }}
            </x-alert>
        @endif

        @if($errors->any())
            <x-alert type="danger" class="mb-4" dismissible>
                <strong class="d-block mb-1">Please correct the following errors:</strong>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <!-- Identity Verification Status Notice -->
        @if(!$isLinked)
            <div class="card border border-warning-subtle rounded-3 bg-warning-subtle p-4 mb-4">
                <div class="d-flex align-items-start gap-3">
                    <span class="rounded-circle bg-warning text-white p-2.5 d-inline-flex align-items-center justify-content-center shrink-0" style="width: 48px; height: 48px;">
                        <i class="bi bi-shield-lock-fill fs-4"></i>
                    </span>
                    <div>
                        <h2 class="h6 fw-bold text-navy mb-1">In-Person Identity Verification Required for Medical Records</h2>
                        <p class="text-muted-hms mb-2" style="font-size: 0.875rem; line-height: 1.6;">
                            In accordance with patient confidentiality regulations, online self-registered accounts are not automatically linked to hospital clinical histories. To view past prescriptions, laboratory reports, and inpatient bed records, please complete a one-time physical identity verification at our reception desk.
                        </p>
                        <div class="d-flex flex-wrap gap-3 align-items-center text-navy fw-semibold" style="font-size: 0.8rem;">
                            <span><i class="bi bi-geo-alt-fill text-teal me-1"></i> Meghna Clinic Reception, Shohid Minar Mor, Dinajpur</span>
                            <span><i class="bi bi-telephone-fill text-teal me-1"></i> Verification Desk: +880 1711-000004</span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card border border-success-subtle rounded-3 bg-success-subtle p-3 mb-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2" style="font-size: 0.85rem;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-patch-check-fill text-success fs-5"></i>
                        <span class="fw-bold text-navy">Verified Clinical Chart: {{ $patient->patient_code }}</span>
                        <span class="text-muted-hms">&bull; Verified on {{ $patient->verified_at ? $patient->verified_at->format('d M Y, h:i A') : 'N/A' }}</span>
                    </div>
                    @if($patient->verifiedBy)
                        <span class="text-muted-hms">Verified by: <strong class="text-navy">{{ $patient->verifiedBy->name }}</strong></span>
                    @endif
                </div>
            </div>
        @endif

        <div class="row g-4">
            <!-- Left Column: Personal Demographics & Profile Form -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm p-4 rounded-3 bg-white mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                        <div>
                            <h2 class="h5 fw-bold text-navy mb-1">
                                <i class="bi bi-person-lines-fill text-teal me-2"></i> Personal Demographics
                            </h2>
                            <p class="text-muted-hms mb-0" style="font-size: 0.8rem;">
                                Your official patient details used for OPD tokens, invoices, and clinical identification.
                            </p>
                        </div>
                    </div>

                    <form action="{{ route('patient.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="profile_name" class="form-label fw-semibold text-navy" style="font-size: 0.85rem;">Full Legal Name <span class="text-danger">*</span></label>
                                <input type="text" id="profile_name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="profile_phone" class="form-label fw-semibold text-navy" style="font-size: 0.85rem;">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" id="profile_phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required>
                                <div class="form-text" style="font-size: 0.75rem;">Used for SMS token updates and emergency contact.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="profile_email" class="form-label fw-semibold text-navy" style="font-size: 0.85rem;">Email Address <span class="text-danger">*</span></label>
                                <input type="email" id="profile_email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="profile_blood_group" class="form-label fw-semibold text-navy" style="font-size: 0.85rem;">Blood Group</label>
                                <select id="profile_blood_group" name="blood_group" class="form-select">
                                    <option value="">Select Blood Group</option>
                                    @php
                                        $currentBlood = old('blood_group', $patient?->blood_group);
                                    @endphp
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                        <option value="{{ $bg }}" {{ $currentBlood === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="profile_dob" class="form-label fw-semibold text-navy" style="font-size: 0.85rem;">Date of Birth</label>
                                <input type="date" id="profile_dob" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $patient?->date_of_birth?->format('Y-m-d')) }}">
                            </div>

                            <div class="col-md-6">
                                <label for="profile_gender" class="form-label fw-semibold text-navy" style="font-size: 0.85rem;">Gender</label>
                                @php
                                    $currentGender = old('gender', $patient?->gender);
                                @endphp
                                <select id="profile_gender" name="gender" class="form-select">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ $currentGender === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ $currentGender === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ $currentGender === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label for="profile_nid" class="form-label fw-semibold text-navy" style="font-size: 0.85rem;">National ID / Birth Registration Certificate</label>
                                <input type="text" id="profile_nid" name="nid_or_birth_cert" class="form-control" value="{{ old('nid_or_birth_cert', $patient?->nid_or_birth_cert) }}" placeholder="10, 13, or 17 digit NID / Birth Cert Number">
                            </div>

                            <div class="col-12">
                                <label for="profile_address" class="form-label fw-semibold text-navy" style="font-size: 0.85rem;">Residential Address</label>
                                <textarea id="profile_address" name="address" rows="2" class="form-control" placeholder="Village / Road, Post Office, Thana, District">{{ old('address', $patient?->address) }}</textarea>
                            </div>

                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-hms-teal">
                                    <i class="bi bi-check2-circle me-1"></i> Save Profile Details
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Emergency Contacts & Guardian Management -->
                <div class="card border-0 shadow-sm p-4 rounded-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div>
                            <h2 class="h5 fw-bold text-navy mb-1">
                                <i class="bi bi-people-fill text-teal me-2"></i> Emergency Contacts & Guardians
                            </h2>
                            <p class="text-muted-hms mb-0" style="font-size: 0.8rem;">
                                Authorized family members or attendants to reach during inpatient observation or acute trauma care.
                            </p>
                        </div>
                    </div>

                    @if($isLinked)
                        <!-- Contacts List Table -->
                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Relationship</th>
                                        <th>Phone Number</th>
                                        <th>Type</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($patient->contacts as $contact)
                                        <tr>
                                            <td class="fw-bold text-navy">{{ $contact->name }}</td>
                                            <td>{{ $contact->relationship }}</td>
                                            <td class="font-monospace">{{ $contact->phone }}</td>
                                            <td>
                                                @if($contact->is_primary)
                                                    <span class="badge bg-teal-subtle text-teal">Primary Attendant</span>
                                                @else
                                                    <span class="badge bg-light text-muted-hms border">Secondary</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <form action="{{ route('patient.emergency-contacts.destroy', $contact->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this emergency contact?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete Contact">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted-hms py-3">
                                                No emergency contacts registered yet. Please add a primary guardian below.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Add Emergency Contact Form -->
                        <div class="p-3 bg-body rounded-3 border">
                            <h3 class="h6 fw-bold text-navy mb-3">Add Emergency Contact</h3>
                            <form action="{{ route('patient.emergency-contacts.store') }}" method="POST">
                                @csrf
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Contact Full Name" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="relationship" class="form-control form-control-sm" placeholder="Relationship (e.g. Spouse)" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="tel" name="phone" class="form-control form-control-sm" placeholder="Phone (017XXXXXXXX)" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-hms-teal btn-sm w-100">
                                            <i class="bi bi-plus-lg"></i> Add
                                        </button>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <div class="form-check" style="font-size: 0.8rem;">
                                            <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="is_primary_check">
                                            <label class="form-check-label text-muted-hms" for="is_primary_check">
                                                Set as primary attendant for hospital contact
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="p-4 bg-light rounded text-center">
                            <i class="bi bi-lock-fill text-muted-hms fs-3 mb-2 d-block"></i>
                            <h3 class="h6 fw-bold text-navy mb-1">Guardian Records Locked</h3>
                            <p class="text-muted-hms mb-0" style="font-size: 0.85rem;">
                                Emergency contact management will be activated as soon as your account is verified in person at the Meghna Clinic reception desk.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Security, Password Change & Privacy Safeguards -->
            <div class="col-lg-5">
                <!-- Change Password Card -->
                <div class="card border-0 shadow-sm p-4 rounded-3 bg-white mb-4">
                    <h2 class="h5 fw-bold text-navy mb-1">
                        <i class="bi bi-key-fill text-teal me-2"></i> Change Password
                    </h2>
                    <p class="text-muted-hms mb-3" style="font-size: 0.8rem;">
                        Keep your Patient Portal account protected with a strong, distinct password.
                    </p>

                    <form action="{{ route('patient.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold text-navy" style="font-size: 0.85rem;">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required autocomplete="current-password">
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-semibold text-navy" style="font-size: 0.85rem;">New Password</label>
                            <input type="password" id="new_password" name="password" class="form-control" required autocomplete="new-password">
                            <div class="form-text" style="font-size: 0.75rem;">Minimum 8 characters with numbers and mixed letters.</div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label fw-semibold text-navy" style="font-size: 0.85rem;">Confirm New Password</label>
                            <input type="password" id="new_password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn btn-hms-outline-primary w-100">
                            <i class="bi bi-shield-check me-1"></i> Update Password
                        </button>
                    </form>
                </div>

                <!-- Account Security & Access Audit Box -->
                <div class="card border-0 shadow-sm p-4 rounded-3 bg-white">
                    <h3 class="h6 fw-bold text-navy mb-2">
                        <i class="bi bi-shield-shaded text-teal me-1"></i> Your Healthcare Data Rights
                    </h3>
                    <ul class="text-muted-hms d-flex flex-column gap-2 mb-3 ps-3" style="font-size: 0.8rem; line-height: 1.6;">
                        <li><strong>Single Identity Access:</strong> You can only access clinical charts linked to your authenticated identity.</li>
                        <li><strong>Audit Logging:</strong> Every prescription view, lab download, and profile edit creates an immutable security log.</li>
                        <li><strong>Unlinking Safeguard:</strong> In-person verification ensures no other individual can claim your medical history.</li>
                    </ul>
                    <div class="p-2.5 rounded bg-body border text-muted-hms" style="font-size: 0.75rem;">
                        For privacy concerns or to update your primary NID record, contact the Hospital Administrator at <code>info@meghnaclinic.com</code>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.patient>
