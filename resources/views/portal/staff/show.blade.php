<x-layouts.portal role="admin" title="Staff Profile: {{ $staffProfile->user->name }}" activeNav="staff">
    <div class="container-fluid p-0">
        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <x-breadcrumb :items="[
                    'Administration' => url('/previews/admin'),
                    'Staff Directory' => route('portal.staff.index'),
                    $staffProfile->user->name => null
                ]" class="mb-1" />
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">{{ $staffProfile->user->name }}</h1>
                    <span class="badge bg-navy px-2.5 py-1 font-monospace fw-bold text-white rounded">
                        {{ $staffProfile->employee_id ?: 'MCNH-STF-' . $staffProfile->id }}
                    </span>
                    @if($staffProfile->status === 'active')
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                            <i class="bi bi-check-circle-fill me-1"></i> Active Duty
                        </span>
                    @elseif($staffProfile->status === 'on_leave')
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1">
                            <i class="bi bi-clock-history me-1"></i> On Leave
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">
                            <i class="bi bi-dash-circle me-1"></i> Inactive
                        </span>
                    @endif
                </div>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-2">
                @can('scheduleShifts', App\Models\StaffProfile::class)
                    <button type="button" class="btn btn-sm btn-teal d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#scheduleShiftModal">
                        <i class="bi bi-calendar-plus"></i>
                        <span>Schedule Shift</span>
                    </button>
                @endcan

                @can('update', $staffProfile)
                    <!-- Toggle Status -->
                    <form action="{{ route('portal.staff.toggle-status', $staffProfile) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $staffProfile->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                onclick="return confirm('Change status for {{ $staffProfile->user->name }}?')">
                            <i class="bi {{ $staffProfile->status === 'active' ? 'bi-pause-circle' : 'bi-play-circle' }} me-1"></i>
                            <span>{{ $staffProfile->status === 'active' ? 'Mark Inactive' : 'Activate' }}</span>
                        </button>
                    </form>

                    <a href="{{ route('portal.staff.edit', $staffProfile) }}" class="btn btn-sm btn-navy d-inline-flex align-items-center gap-1">
                        <i class="bi bi-pencil-square"></i>
                        <span>Edit Profile</span>
                    </a>
                @endcan

                <a href="{{ route('portal.staff.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Directory
                </a>
            </div>
        </div>

        <!-- Flash Alerts -->
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

        <!-- Summary KPI Bento Grid -->
        <div class="row g-3 mb-4">
            <!-- Designation & Roles -->
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                    <span class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Hospital Designation</span>
                    <h5 class="fw-bold text-navy mb-1 mt-1">{{ $staffProfile->designation }}</h5>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @foreach($staffProfile->user->roles as $role)
                            <span class="badge bg-navy bg-opacity-10 text-navy rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                                {{ $role->title }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Department Allocation -->
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                    <span class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Primary Department</span>
                    <h5 class="fw-bold text-teal mb-1 mt-1">
                        {{ $staffProfile->department?->name ?? 'General Clinic Facility' }}
                    </h5>
                    @php
                        $secondaryDepts = $staffProfile->departments->reject(fn($d) => $d->id === $staffProfile->department_id);
                    @endphp
                    @if($secondaryDepts->isNotEmpty())
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @foreach($secondaryDepts as $sDept)
                                <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">
                                    + {{ $sDept->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <small class="text-muted" style="font-size: 0.72rem;">Single Department Duty</small>
                    @endif
                </div>
            </div>

            <!-- Shift Schedule -->
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                    <span class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Shift Assignment</span>
                    <h5 class="fw-bold text-navy mb-1 mt-1 text-capitalize">
                        {{ $staffProfile->shift }} Shift
                    </h5>
                    <small class="text-muted" style="font-size: 0.75rem;">
                        @if($staffProfile->shift === 'morning') 08:00 AM - 04:00 PM (8h)
                        @elseif($staffProfile->shift === 'evening') 04:00 PM - 12:00 AM (8h)
                        @elseif($staffProfile->shift === 'night') 12:00 AM - 08:00 AM (8h)
                        @else Rotational 24/7 Clinical Duty
                        @endif
                    </small>
                </div>
            </div>

            <!-- Employment Type -->
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                    <span class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Employment Contract</span>
                    <h5 class="fw-bold text-navy mb-1 mt-1 text-capitalize">
                        {{ str_replace('_', ' ', $staffProfile->employment_type) }}
                    </h5>
                    <small class="text-muted" style="font-size: 0.72rem;">
                        Joined: {{ $staffProfile->joining_date ? $staffProfile->joining_date->format('d M Y') : 'N/A' }}
                    </small>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Left Column: Professional & Protected Info -->
            <div class="col-lg-5">
                <!-- Professional Qualifications Card -->
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                    <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
                        <i class="bi bi-mortarboard text-teal fs-5"></i>
                        <h2 class="h6 fw-bold text-navy mb-0">Professional Credentials</h2>
                    </div>

                    <dl class="row mb-0" style="font-size: 0.875rem;">
                        <dt class="col-sm-5 text-muted fw-normal">Registration / License:</dt>
                        <dd class="col-sm-7 fw-bold font-monospace text-navy">
                            {{ $staffProfile->license_number ?: 'Not Registered' }}
                        </dd>

                        <dt class="col-sm-5 text-muted fw-normal">Specialization:</dt>
                        <dd class="col-sm-7 text-navy">
                            {{ $staffProfile->specialization ?: 'General Healthcare' }}
                        </dd>

                        <dt class="col-sm-5 text-muted fw-normal">Qualifications:</dt>
                        <dd class="col-sm-7 text-navy">
                            {{ $staffProfile->qualification ?: 'On File with HR' }}
                        </dd>

                        <dt class="col-sm-5 text-muted fw-normal">Work Email:</dt>
                        <dd class="col-sm-7 text-navy">{{ $staffProfile->user->email }}</dd>

                        <dt class="col-sm-5 text-muted fw-normal">Official Phone:</dt>
                        <dd class="col-sm-7 font-monospace text-navy">{{ $staffProfile->user->phone }}</dd>
                    </dl>
                </div>

                <!-- Protected Sensitive Employment Information Card -->
                <div class="card border {{ $canViewSensitive ? 'border-primary-subtle' : 'border-warning-subtle' }} shadow-sm rounded-3 bg-white p-4">
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-lock-fill {{ $canViewSensitive ? 'text-primary' : 'text-warning' }} fs-5"></i>
                            <h2 class="h6 fw-bold text-navy mb-0">Sensitive Employment Details</h2>
                        </div>
                        @if($canViewSensitive)
                            <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">
                                Authorized View
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">
                                Masked / Protected
                            </span>
                        @endif
                    </div>

                    @if($canViewSensitive)
                        <dl class="row mb-0" style="font-size: 0.875rem;">
                            <dt class="col-sm-5 text-muted fw-normal">Monthly Basic Salary:</dt>
                            <dd class="col-sm-7 fw-bold font-monospace text-navy">
                                @if($staffProfile->salary)
                                    ৳{{ number_format($staffProfile->salary, 2) }} BDT
                                @else
                                    <span class="text-muted">Not Recorded</span>
                                @endif
                            </dd>

                            <dt class="col-sm-5 text-muted fw-normal">National ID (NID):</dt>
                            <dd class="col-sm-7 font-monospace text-navy">
                                {{ $staffProfile->nid_number ?: 'Not Recorded' }}
                            </dd>

                            <dt class="col-sm-5 text-muted fw-normal">Emergency Contact:</dt>
                            <dd class="col-sm-7 text-navy">
                                {{ $staffProfile->emergency_contact_name ?: 'None' }}
                                @if($staffProfile->emergency_contact_phone)
                                    <span class="d-block font-monospace text-muted small">({{ $staffProfile->emergency_contact_phone }})</span>
                                @endif
                            </dd>

                            <dt class="col-sm-5 text-muted fw-normal">Home Address:</dt>
                            <dd class="col-sm-7 text-muted">
                                {{ $staffProfile->address ?: 'Shohid Minar Mor, Dinajpur Sadar' }}
                            </dd>
                        </dl>
                    @else
                        <!-- Masked Sensitive Info for Regular Staff -->
                        <div class="p-3 bg-light rounded text-center">
                            <i class="bi bi-eye-slash text-muted fs-3 mb-2 d-block"></i>
                            <h3 class="h6 fw-bold text-navy mb-1">Confidential Information Masked</h3>
                            <p class="text-muted small mb-0">
                                Remuneration, national identity, and personal emergency contacts are restricted to hospital administration and HR officers.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: 8-Hour Shift Schedule & History -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden mb-4">
                    <div class="card-header bg-white border-bottom p-3.5 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-teal bg-opacity-10 text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                <i class="bi bi-calendar3-event fs-5"></i>
                            </span>
                            <div>
                                <h2 class="h5 fw-bold text-navy mb-0">Upcoming 8-Hour Shift Schedule</h2>
                                <span class="text-muted" style="font-size: 0.8rem;">Scheduled duty windows in designated wards or clinical units.</span>
                            </div>
                        </div>

                        @can('scheduleShifts', App\Models\StaffProfile::class)
                            <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#scheduleShiftModal">
                                <i class="bi bi-plus-circle"></i>
                                <span>Schedule Duty</span>
                            </button>
                        @endcan
                    </div>

                    <div class="card-body p-0">
                        @if($upcomingShifts->isEmpty())
                            <div class="p-4 text-center">
                                <i class="bi bi-calendar2-x text-muted fs-3 opacity-50 mb-2 d-block"></i>
                                <h3 class="h6 fw-bold text-navy mb-1">No Upcoming Scheduled Shifts</h3>
                                <p class="text-muted small mb-3">No future 8-hour duties are currently scheduled for this staff member.</p>
                                @can('scheduleShifts', App\Models\StaffProfile::class)
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#scheduleShiftModal">
                                        <i class="bi bi-calendar-plus me-1"></i> Add Shift Assignment
                                    </button>
                                @endcan
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                        <tr>
                                            <th class="ps-4">Date & Time</th>
                                            <th>Shift Type</th>
                                            <th>Duty Station / Ward</th>
                                            <th>Status</th>
                                            <th class="text-end pe-4">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y" style="font-size: 0.875rem;">
                                        @foreach($upcomingShifts as $shift)
                                            <tr>
                                                <td class="ps-4">
                                                    <span class="fw-bold text-navy d-block">{{ $shift->shift_date->format('D, d M Y') }}</span>
                                                    <small class="text-muted font-monospace">{{ $shift->formatted_time_range }}</small>
                                                </td>
                                                <td>
                                                    @if($shift->shift_type === 'morning')
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">Morning (8h)</span>
                                                    @elseif($shift->shift_type === 'evening')
                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">Evening (8h)</span>
                                                    @else
                                                        <span class="badge bg-dark-subtle text-dark border border-dark-subtle rounded-pill px-2.5 py-1">Night (8h)</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-navy">{{ $shift->department->name }}</span>
                                                    @if($shift->notes)
                                                        <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $shift->notes }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5 text-capitalize">
                                                        {{ $shift->status }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    @can('scheduleShifts', App\Models\StaffProfile::class)
                                                        <form action="{{ route('portal.staff.delete-shift', $shift) }}" method="POST" class="d-inline"
                                                              onsubmit="return confirm('Cancel this scheduled shift?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancel Shift">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Shift History -->
                @if($recentShifts->isNotEmpty())
                    <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden">
                        <div class="card-header bg-white border-bottom p-3">
                            <h2 class="h6 fw-bold text-navy mb-0">
                                <i class="bi bi-clock-history text-muted me-1"></i> Recent Shift Attendance History
                            </h2>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.825rem;">
                                    <thead class="table-light text-muted text-uppercase" style="font-size: 0.7rem;">
                                        <tr>
                                            <th class="ps-3">Date</th>
                                            <th>Shift</th>
                                            <th>Station</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentShifts as $rShift)
                                            <tr>
                                                <td class="ps-3 fw-semibold text-navy">{{ $rShift->shift_date->format('d M Y') }}</td>
                                                <td class="text-capitalize">{{ $rShift->shift_type }}</td>
                                                <td>{{ $rShift->department->name }}</td>
                                                <td>
                                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-0.5 text-capitalize">
                                                        {{ $rShift->status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- MODAL: Schedule Duty Shift -->
    @can('scheduleShifts', App\Models\StaffProfile::class)
        <div class="modal fade" id="scheduleShiftModal" tabindex="-1" aria-labelledby="scheduleShiftModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-navy text-white">
                        <h5 class="modal-title fs-6 fw-bold" id="scheduleShiftModalLabel">
                            <i class="bi bi-calendar-plus text-teal me-1"></i> Schedule Duty Shift: {{ $staffProfile->user->name }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('portal.staff.schedule-shift') }}" method="POST">
                        @csrf
                        <input type="hidden" name="staff_profile_id" value="{{ $staffProfile->id }}">

                        <div class="modal-body p-4 text-start">
                            <!-- Department Selection -->
                            <div class="mb-3">
                                <label for="shift_department_id" class="form-label fw-semibold text-navy">Duty Ward / Department <span class="text-danger">*</span></label>
                                <select name="department_id" id="shift_department_id" class="form-select" required>
                                    <option value="{{ $staffProfile->department_id }}">
                                        {{ $staffProfile->department?->name ?? 'Primary Station' }} (Primary)
                                    </option>
                                    @foreach($staffProfile->departments->reject(fn($d) => $d->id === $staffProfile->department_id) as $sDept)
                                        <option value="{{ $sDept->id }}">
                                            {{ $sDept->name }} (Cross-Department Coverage)
                                        </option>
                                    @endforeach
                                    @foreach($departments->reject(fn($d) => $d->id === $staffProfile->department_id || $staffProfile->departments->contains($d->id)) as $oDept)
                                        <option value="{{ $oDept->id }}">
                                            {{ $oDept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Shift Date -->
                            <div class="mb-3">
                                <label for="shift_date" class="form-label fw-semibold text-navy">Shift Date <span class="text-danger">*</span></label>
                                <input type="date" name="shift_date" id="shift_date" class="form-control" 
                                       value="{{ date('Y-m-d') }}" required>
                            </div>

                            <!-- Shift Type -->
                            <div class="mb-3">
                                <label for="shift_type" class="form-label fw-semibold text-navy">8-Hour Shift Window <span class="text-danger">*</span></label>
                                <select name="shift_type" id="shift_type" class="form-select" required>
                                    <option value="morning">Morning Shift (08:00 AM - 04:00 PM)</option>
                                    <option value="evening">Evening Shift (04:00 PM - 12:00 AM)</option>
                                    <option value="night">Night Shift (12:00 AM - 08:00 AM)</option>
                                </select>
                                <div class="form-text">System prevents overlapping incompatible shifts for this staff member.</div>
                            </div>

                            <!-- Notes -->
                            <div class="mb-3">
                                <label for="shift_notes" class="form-label fw-semibold text-navy">Duty Notes / Handover Instructions</label>
                                <input type="text" name="notes" id="shift_notes" class="form-control" 
                                       placeholder="e.g. Primary Emergency Triage / Inpatient Ward Rounds">
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2 me-1"></i> Confirm Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
</x-layouts.portal>
