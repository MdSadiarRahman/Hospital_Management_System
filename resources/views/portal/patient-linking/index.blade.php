<x-layouts.portal role="reception" title="Verify & Link Patient Accounts" activeNav="linking">
    <div class="container-fluid p-0">
        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <x-breadcrumb :items="['Reception Desk' => url('/portal/reception'), 'Verify & Link Accounts' => null]" class="mb-1" />
                <h1 class="h3 fw-bold text-navy mb-0">Verify & Link Patient Accounts</h1>
                <p class="text-muted-hms mb-0" style="font-size: 0.875rem;">
                    Staff identity verification desk for linking online self-registered patient accounts to physical clinical charts.
                </p>
            </div>
            <div>
                <span class="badge bg-navy text-white px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-shield-check text-teal me-1"></i> Reception Station Desk
                </span>
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
                <strong class="d-block mb-1">Validation Errors:</strong>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <!-- Identity Verification Action Card -->
        <div class="card border-0 shadow-sm p-4 rounded-3 bg-white mb-4">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                <span class="rounded bg-teal-subtle text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="bi bi-link-45deg fs-4"></i>
                </span>
                <div>
                    <h2 class="h5 fw-bold text-navy mb-0">Verify & Link Patient Record</h2>
                    <span class="text-muted-hms" style="font-size: 0.8rem;">Select an online account and a clinical chart to perform authorized linkage.</span>
                </div>
            </div>

            <form action="{{ route('portal.patient-linking.store') }}" method="POST" id="patientLinkingForm">
                @csrf
                <div class="row g-3">
                    <!-- User Account Picker -->
                    <div class="col-md-4">
                        <label for="link_user_id" class="form-label fw-semibold text-navy" style="font-size: 0.85rem;">Online Patient Account <span class="text-danger">*</span></label>
                        <select name="user_id" id="link_user_id" class="form-select" required>
                            <option value="">-- Choose Unlinked User Account --</option>
                            @foreach($unlinkedUsers as $u)
                                <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                    #{{ $u->id }} &bull; {{ $u->name }} ({{ $u->phone }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Clinical Patient Chart Picker -->
                    <div class="col-md-4">
                        <label for="link_patient_id" class="form-label fw-semibold text-navy" style="font-size: 0.85rem;">Clinical Patient Record <span class="text-danger">*</span></label>
                        <select name="patient_id" id="link_patient_id" class="form-select" required>
                            <option value="">-- Choose Clinical Chart (MCNH-XXXX) --</option>
                            @foreach($unlinkedPatients as $p)
                                <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->patient_code }} &bull; {{ $p->name }} ({{ $p->phone }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Verification Method -->
                    <div class="col-md-4">
                        <label for="verification_method" class="form-label fw-semibold text-navy" style="font-size: 0.85rem;">Verification Protocol <span class="text-danger">*</span></label>
                        <select name="verification_method" id="verification_method" class="form-select" required>
                            <option value="physical_in_person">Physical In-Person at Reception</option>
                            <option value="nid_matched">National ID / Birth Cert Matched</option>
                            <option value="phone_otp_verified">Phone Registered Number Verified</option>
                            <option value="guardian_verified">Legal Guardian / Attendant Verified</option>
                        </select>
                    </div>

                    <!-- Verification Notes -->
                    <div class="col-md-9">
                        <label for="link_notes" class="form-label fw-semibold text-navy" style="font-size: 0.85rem;">Verification Audit Notes (Optional)</label>
                        <input type="text" name="notes" id="link_notes" class="form-control" placeholder="e.g. Verified via physical NID card # 198527... by receptionist Kowshik Roy">
                    </div>

                    <!-- Submit Button -->
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-hms-teal w-100 py-2">
                            <i class="bi bi-shield-check me-1"></i> Verify & Link Chart
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- 2 Side-by-Side Search Tables -->
        <div class="row g-4 mb-4">
            <!-- Left Table: Unlinked Online Users -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                            <div>
                                <h3 class="h6 fw-bold text-navy mb-0">Unlinked Online Web Accounts</h3>
                                <span class="text-muted-hms" style="font-size: 0.75rem;">Self-registered patients awaiting physical verification</span>
                            </div>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                {{ $unlinkedUsers->total() }} Pending
                            </span>
                        </div>

                        <!-- User Search Form -->
                        <form method="GET" action="{{ route('portal.patient-linking.index') }}" class="mb-3">
                            <input type="hidden" name="patient_search" value="{{ $patientSearch }}">
                            <div class="input-group input-group-sm">
                                <input type="text" name="user_search" class="form-control" placeholder="Search by name, phone, or email..." value="{{ $userSearch }}">
                                <button class="btn btn-hms-outline-primary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                                @if($userSearch)
                                    <a href="{{ route('portal.patient-linking.index', ['patient_search' => $patientSearch]) }}" class="btn btn-outline-secondary">Clear</a>
                                @endif
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.8rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Phone</th>
                                        <th>Registered</th>
                                        <th class="text-end">Select</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($unlinkedUsers as $u)
                                        <tr>
                                            <td>
                                                <strong class="text-navy d-block">{{ $u->name }}</strong>
                                                <span class="text-muted-hms" style="font-size: 0.75rem;">{{ $u->email }}</span>
                                            </td>
                                            <td class="font-monospace">{{ $u->phone }}</td>
                                            <td class="text-muted-hms">{{ $u->created_at->format('d M Y') }}</td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-xs btn-hms-outline-teal select-user-btn" data-user-id="{{ $u->id }}">
                                                    Select
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted-hms py-3">No unlinked user accounts found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3">
                        {{ $unlinkedUsers->appends(['patient_search' => $patientSearch])->links() }}
                    </div>
                </div>
            </div>

            <!-- Right Table: Unlinked Clinical Patient Records -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                            <div>
                                <h3 class="h6 fw-bold text-navy mb-0">Unlinked Hospital Clinical Records</h3>
                                <span class="text-muted-hms" style="font-size: 0.75rem;">Physical charts registered at reception without online login</span>
                            </div>
                            <span class="badge bg-teal-subtle text-teal">
                                {{ $unlinkedPatients->total() }} Records
                            </span>
                        </div>

                        <!-- Patient Search Form -->
                        <form method="GET" action="{{ route('portal.patient-linking.index') }}" class="mb-3">
                            <input type="hidden" name="user_search" value="{{ $userSearch }}">
                            <div class="input-group input-group-sm">
                                <input type="text" name="patient_search" class="form-control" placeholder="Search by patient code, name, phone, NID..." value="{{ $patientSearch }}">
                                <button class="btn btn-hms-outline-primary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                                @if($patientSearch)
                                    <a href="{{ route('portal.patient-linking.index', ['user_search' => $userSearch]) }}" class="btn btn-outline-secondary">Clear</a>
                                @endif
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.8rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Patient Code</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th class="text-end">Select</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($unlinkedPatients as $p)
                                        <tr>
                                            <td class="font-monospace fw-bold text-navy">{{ $p->patient_code }}</td>
                                            <td>
                                                <strong class="text-navy d-block">{{ $p->name }}</strong>
                                                <span class="text-muted-hms" style="font-size: 0.75rem;">{{ ucfirst($p->gender) }} &bull; {{ $p->blood_group ?? 'Unknown' }}</span>
                                            </td>
                                            <td class="font-monospace">{{ $p->phone }}</td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-xs btn-hms-outline-teal select-patient-btn" data-patient-id="{{ $p->id }}">
                                                    Select
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted-hms py-3">No unlinked clinical charts found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3">
                        {{ $unlinkedPatients->appends(['user_search' => $userSearch])->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Recently Verified & Linked Accounts Audit -->
        <div class="card border-0 shadow-sm p-4 rounded-3 bg-white">
            <h3 class="h5 fw-bold text-navy mb-3">
                <i class="bi bi-journal-check text-teal me-2"></i> Recently Verified & Linked Patient Charts
            </h3>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Patient Code</th>
                            <th>Patient Name</th>
                            <th>Linked User Account</th>
                            <th>Verification Date</th>
                            <th>Verified By Staff</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentlyLinked as $linked)
                            <tr>
                                <td class="font-monospace fw-bold text-navy">{{ $linked->patient_code }}</td>
                                <td class="fw-semibold text-navy">{{ $linked->name }}</td>
                                <td>
                                    @if($linked->user)
                                        <span class="d-block fw-semibold text-navy">{{ $linked->user->name }}</span>
                                        <span class="text-muted-hms font-monospace" style="font-size: 0.75rem;">{{ $linked->user->email }}</span>
                                    @else
                                        <span class="text-muted-hms">N/A</span>
                                    @endif
                                </td>
                                <td class="text-muted-hms">
                                    {{ $linked->verified_at ? $linked->verified_at->format('d M Y, h:i A') : 'N/A' }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-navy border">
                                        {{ $linked->verifiedBy->name ?? 'System' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('portal.patient-linking.unlink', $linked->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Unlink this patient account? Patient will lose web access to medical records until re-verified.');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                            <i class="bi bi-link-45deg"></i> Unlink
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted-hms py-3">No verified patient accounts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Quick select user button handler
            document.querySelectorAll('.select-user-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-user-id');
                    const select = document.getElementById('link_user_id');
                    if (select) {
                        select.value = userId;
                        select.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        select.focus();
                    }
                });
            });

            // Quick select patient button handler
            document.querySelectorAll('.select-patient-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const patientId = this.getAttribute('data-patient-id');
                    const select = document.getElementById('link_patient_id');
                    if (select) {
                        select.value = patientId;
                        select.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        select.focus();
                    }
                });
            });
        });
    </script>
    @endpush
</x-layouts.portal>
