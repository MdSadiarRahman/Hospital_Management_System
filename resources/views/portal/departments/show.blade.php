<x-layouts.portal role="admin" title="{{ $department->name }} ({{ $department->code }}) - Department Details" activeNav="departments">
    <div class="container-fluid p-0">
        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <x-breadcrumb :items="[
                    'Administration' => url('/previews/admin'),
                    'Departments & Wards' => route('portal.departments.index'),
                    $department->name => null
                ]" class="mb-1" />
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">{{ $department->name }}</h1>
                    <span class="badge bg-navy px-2.5 py-1 font-monospace fw-bold text-white rounded">
                        {{ $department->code }}
                    </span>
                    @if($department->is_clinical)
                        <span class="badge bg-teal-subtle text-teal border border-teal-subtle rounded-pill px-2.5 py-1 font-sans">
                            <i class="bi bi-heart-pulse me-1"></i> Clinical Specialty
                        </span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 font-sans">
                            <i class="bi bi-building me-1"></i> Support Unit
                        </span>
                    @endif
                    @if($department->is_active)
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                            <i class="bi bi-check-circle-fill me-1"></i> Active
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">
                            <i class="bi bi-dash-circle-fill me-1"></i> Inactive
                        </span>
                    @endif
                </div>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-2">
                @can('update', $department)
                    <!-- Toggle Status Form -->
                    <form action="{{ route('portal.departments.toggle-status', $department) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $department->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                onclick="return confirm('Toggle status for department {{ $department->name }}?')">
                            <i class="bi {{ $department->is_active ? 'bi-pause-circle' : 'bi-play-circle' }} me-1"></i>
                            <span>{{ $department->is_active ? 'Deactivate' : 'Activate' }}</span>
                        </button>
                    </form>

                    <a href="{{ route('portal.departments.edit', $department) }}" class="btn btn-sm btn-navy d-inline-flex align-items-center gap-1">
                        <i class="bi bi-pencil-square"></i>
                        <span>Edit Details</span>
                    </a>
                @endcan

                <a href="{{ route('portal.departments.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> All Departments
                </a>
            </div>
        </div>

        <!-- Session Status & Flash Alerts -->
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

        <!-- Department Operational KPI Bento -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 text-center">
                    <span class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Configured Beds</span>
                    <h3 class="fw-bold text-navy mb-0 mt-1">{{ $totalBeds }}</h3>
                    <small class="text-muted" style="font-size: 0.72rem;">Department Total</small>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 text-center border-bottom border-3 border-success">
                    <span class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Available Beds</span>
                    <h3 class="fw-bold text-success mb-0 mt-1">{{ $availableBeds }}</h3>
                    <small class="text-success fw-semibold" style="font-size: 0.72rem;">Ready for Intake</small>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 text-center border-bottom border-3 border-danger">
                    <span class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Occupied Beds</span>
                    <h3 class="fw-bold text-danger mb-0 mt-1">{{ $occupiedBeds }}</h3>
                    <small class="text-muted" style="font-size: 0.72rem;">Admitted Patients</small>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 text-center border-bottom border-3 border-warning">
                    <span class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Maintenance/Cleaning</span>
                    <h3 class="fw-bold text-warning mb-0 mt-1">{{ $maintenanceBeds }}</h3>
                    <small class="text-muted" style="font-size: 0.72rem;">Turnaround</small>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 text-center border-bottom border-3 border-primary">
                    <span class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Assigned Doctors</span>
                    <h3 class="fw-bold text-primary mb-0 mt-1">{{ $doctors->count() }}</h3>
                    <small class="text-muted" style="font-size: 0.72rem;">Consultants/MOs</small>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 text-center border-bottom border-3 border-teal">
                    <span class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Nursing & Staff</span>
                    <h3 class="fw-bold text-teal mb-0 mt-1">{{ $otherStaff->count() }}</h3>
                    <small class="text-muted" style="font-size: 0.72rem;">Sisters & Wards</small>
                </div>
            </div>
        </div>

        <!-- Scope & Description Card -->
        <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-info-circle text-teal fs-5"></i>
                <h2 class="h6 fw-bold text-navy mb-0">Department Scope & Clinical Function</h2>
            </div>
            <p class="text-muted mb-0" style="font-size: 0.9rem; line-height: 1.55;">
                {{ $department->description ?: 'No specific operational description or triage notes have been entered for this department. Use the "Edit Details" button above to maintain clinical guidelines.' }}
            </p>
        </div>

        <!-- SECTION 1: Department-wise Doctor Listing -->
        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4 overflow-hidden">
            <div class="card-header bg-white border-bottom p-3.5 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded bg-primary bg-opacity-10 text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="bi bi-person-badge fs-5"></i>
                    </span>
                    <div>
                        <h2 class="h5 fw-bold text-navy mb-0">Assigned Medical Doctors & Specialists</h2>
                        <span class="text-muted" style="font-size: 0.8rem;">Consultants, Resident Surgeons, and Medical Officers attached to {{ $department->name }}.</span>
                    </div>
                </div>

                @can('manageStaff', $department)
                    <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#assignStaffModal">
                        <i class="bi bi-person-plus"></i>
                        <span>Assign Doctor / Staff</span>
                    </button>
                @endcan
            </div>

            <div class="card-body p-0">
                @if($doctors->isEmpty())
                    <div class="p-4 text-center">
                        <div class="mb-2 text-muted">
                            <i class="bi bi-person-slash fs-2 opacity-50"></i>
                        </div>
                        <h3 class="h6 fw-bold text-navy mb-1">No Doctors Assigned</h3>
                        <p class="text-muted small mb-3">There are currently no doctors assigned to this department roster.</p>
                        @can('manageStaff', $department)
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignStaffModal">
                                <i class="bi bi-plus-circle me-1"></i> Assign Doctor Now
                            </button>
                        @endcan
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                <tr>
                                    <th class="ps-4">Doctor Name</th>
                                    <th>Designation & Specialization</th>
                                    <th>BMDC Reg. No</th>
                                    <th>Shift</th>
                                    <th>Status</th>
                                    <th>Contact</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y" style="font-size: 0.875rem;">
                                @foreach($doctors as $profile)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-navy text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                                    {{ strtoupper(substr($profile->user?->name ?? 'D', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold text-navy d-block">{{ $profile->user?->name ?? 'Unknown Doctor' }}</span>
                                                    <small class="text-muted">{{ $profile->user?->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-navy d-block">{{ $profile->designation }}</span>
                                            <small class="text-muted">{{ $profile->specialization ?: 'General Practice' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-navy border font-monospace">
                                                {{ $profile->license_number ?: 'Pending' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1 text-capitalize">
                                                {{ $profile->shift }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($profile->status === 'active')
                                                <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1">Active</span>
                                            @elseif($profile->status === 'on_leave')
                                                <span class="badge bg-warning-subtle text-warning rounded-pill px-2.5 py-1">On Leave</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1 text-capitalize">{{ $profile->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $profile->user?->phone ?: 'N/A' }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            @can('manageStaff', $department)
                                                <form action="{{ route('portal.departments.staff.remove', [$department, $profile->id]) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Remove doctor {{ $profile->user?->name }} from {{ $department->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Unassign Doctor">
                                                        <i class="bi bi-person-dash"></i> Unassign
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

        <!-- SECTION 2: Department Nursing & Clinical Support Staff -->
        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4 overflow-hidden">
            <div class="card-header bg-white border-bottom p-3.5 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded bg-teal bg-opacity-10 text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="bi bi-people fs-5"></i>
                    </span>
                    <div>
                        <h2 class="h5 fw-bold text-navy mb-0">Nursing & Ward Support Personnel</h2>
                        <span class="text-muted" style="font-size: 0.8rem;">Staff nurses, sisters, and ward attendants assigned to this department.</span>
                    </div>
                </div>

                @can('manageStaff', $department)
                    <button type="button" class="btn btn-sm btn-outline-teal d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#assignStaffModal">
                        <i class="bi bi-plus-lg"></i>
                        <span>Assign Staff Member</span>
                    </button>
                @endcan
            </div>

            <div class="card-body p-0">
                @if($otherStaff->isEmpty())
                    <div class="p-4 text-center">
                        <div class="mb-2 text-muted">
                            <i class="bi bi-people fs-2 opacity-50"></i>
                        </div>
                        <h3 class="h6 fw-bold text-navy mb-1">No Support Staff Assigned</h3>
                        <p class="text-muted small mb-0">No nurses or attendants are currently attached to this unit.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                <tr>
                                    <th class="ps-4">Staff Member</th>
                                    <th>Role / Group</th>
                                    <th>Designation</th>
                                    <th>Shift Schedule</th>
                                    <th>Status</th>
                                    <th>Phone</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y" style="font-size: 0.875rem;">
                                @foreach($otherStaff as $profile)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 0.8rem;">
                                                    {{ strtoupper(substr($profile->user?->name ?? 'S', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold text-navy d-block">{{ $profile->user?->name ?? 'Staff Member' }}</span>
                                                    <small class="text-muted">{{ $profile->user?->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @foreach($profile->user?->roles ?? [] as $role)
                                                <span class="badge bg-navy bg-opacity-10 text-navy rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-navy">{{ $profile->designation }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1 text-capitalize">
                                                {{ $profile->shift }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($profile->status === 'active')
                                                <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1">Active</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1 text-capitalize">{{ $profile->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $profile->user?->phone ?: 'N/A' }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            @can('manageStaff', $department)
                                                <form action="{{ route('portal.departments.staff.remove', [$department, $profile->id]) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Remove {{ $profile->user?->name }} from {{ $department->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Unassign Staff Member">
                                                        <i class="bi bi-person-dash"></i> Unassign
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

        <!-- SECTION 3: Department-wise Bed Configuration -->
        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4 overflow-hidden">
            <div class="card-header bg-white border-bottom p-3.5 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded bg-info bg-opacity-10 text-info p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="bi bi-door-open fs-5"></i>
                    </span>
                    <div>
                        <h2 class="h5 fw-bold text-navy mb-0">Department Inpatient Bed Configuration</h2>
                        <span class="text-muted" style="font-size: 0.8rem;">Manage beds, categories, daily room tariffs, and live occupancy in {{ $department->name }}.</span>
                    </div>
                </div>

                @can('configureBeds', $department)
                    <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addBedModal">
                        <i class="bi bi-plus-circle"></i>
                        <span>Configure New Bed</span>
                    </button>
                @endcan
            </div>

            <div class="card-body p-0">
                @if($department->beds->isEmpty())
                    <div class="p-4 text-center">
                        <div class="mb-2 text-muted">
                            <i class="bi bi-door-closed fs-2 opacity-50"></i>
                        </div>
                        <h3 class="h6 fw-bold text-navy mb-1">No Beds Configured</h3>
                        <p class="text-muted small mb-3">This department has no assigned inpatient beds configured.</p>
                        @can('configureBeds', $department)
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addBedModal">
                                <i class="bi bi-plus-circle me-1"></i> Add Bed to Department
                            </button>
                        @endcan
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                <tr>
                                    <th class="ps-4">Bed Number</th>
                                    <th>Category</th>
                                    <th>Daily Tariff (BDT)</th>
                                    <th>Current Status</th>
                                    <th>Current Admitted Inpatient</th>
                                    <th class="text-end pe-4">Bed Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y" style="font-size: 0.875rem;">
                                @foreach($department->beds as $bed)
                                    @php
                                        $currentAssignment = $bed->currentAssignment;
                                        $patient = $currentAssignment?->patient;
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-navy px-2.5 py-1.5 font-monospace fw-bold text-white rounded">
                                                    {{ $bed->bed_number }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-navy border text-capitalize px-2.5 py-1">
                                                {{ str_replace('_', ' ', $bed->category) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-navy font-monospace">
                                                ৳{{ number_format($bed->daily_charge, 2) }}
                                            </span>
                                            <small class="text-muted d-block" style="font-size: 0.72rem;">per 24h stay</small>
                                        </td>
                                        <td>
                                            @if($bed->status === 'available')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Available
                                                </span>
                                            @elseif($bed->status === 'occupied')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">
                                                    <i class="bi bi-person-fill me-1"></i> Occupied
                                                </span>
                                            @elseif($bed->status === 'reserved')
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1">
                                                    <i class="bi bi-clock-fill me-1"></i> Reserved
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 text-capitalize">
                                                    <i class="bi bi-tools me-1"></i> {{ $bed->status }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($patient)
                                                <div>
                                                    <span class="fw-bold text-navy d-block">{{ $patient->name }}</span>
                                                    <small class="text-muted font-monospace">{{ $patient->patient_code }}</small>
                                                    @if($currentAssignment->assigned_at)
                                                        <small class="text-muted d-block" style="font-size: 0.72rem;">
                                                            Admitted: {{ \Carbon\Carbon::parse($currentAssignment->assigned_at)->format('d M Y, h:i A') }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted fst-italic">None (Vacant)</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            @can('configureBeds', $department)
                                                <div class="d-flex align-items-center justify-content-end gap-1">
                                                    <!-- Edit Bed Button -->
                                                    <button type="button" class="btn btn-sm btn-light text-navy" 
                                                            data-bs-toggle="modal" data-bs-target="#editBedModal{{ $bed->id }}" 
                                                            title="Edit Bed Configuration">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>

                                                    <!-- Delete Bed Button -->
                                                    @if($bed->status !== 'occupied')
                                                        <form action="{{ route('portal.departments.beds.destroy', [$department, $bed]) }}" method="POST" class="d-inline"
                                                              onsubmit="return confirm('Decommission Bed {{ $bed->bed_number }}?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Decommission Bed">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button class="btn btn-sm btn-light text-muted" disabled title="Cannot decommission occupied bed">
                                                            <i class="bi bi-lock-fill"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            @endcan
                                        </td>
                                    </tr>

                                    <!-- Edit Bed Modal for this Bed -->
                                    @can('configureBeds', $department)
                                        <div class="modal fade" id="editBedModal{{ $bed->id }}" tabindex="-1" aria-labelledby="editBedModalLabel{{ $bed->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header bg-navy text-white">
                                                        <h5 class="modal-title fs-6 fw-bold" id="editBedModalLabel{{ $bed->id }}">
                                                            <i class="bi bi-pencil-square text-teal me-1"></i> Edit Bed {{ $bed->bed_number }}
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('portal.departments.beds.update', [$department, $bed]) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body p-4 text-start">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold text-navy">Bed Identifier <span class="text-danger">*</span></label>
                                                                <input type="text" name="bed_number" class="form-control font-monospace text-uppercase" value="{{ old('bed_number', $bed->bed_number) }}" required maxlength="32">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold text-navy">Bed Category <span class="text-danger">*</span></label>
                                                                <select name="category" class="form-select" required>
                                                                    <option value="general" {{ old('category', $bed->category) === 'general' ? 'selected' : '' }}>General Ward Bed</option>
                                                                    <option value="department" {{ old('category', $bed->category) === 'department' ? 'selected' : '' }}>Departmental Care Bed</option>
                                                                    <option value="cabin" {{ old('category', $bed->category) === 'cabin' ? 'selected' : '' }}>Private Cabin</option>
                                                                    <option value="post_op" {{ old('category', $bed->category) === 'post_op' ? 'selected' : '' }}>Post-Op / Recovery Bed</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold text-navy">Daily Tariff (BDT) <span class="text-danger">*</span></label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light">৳</span>
                                                                    <input type="number" step="0.01" min="0" max="999999" name="daily_charge" class="form-control font-monospace" value="{{ old('daily_charge', $bed->daily_charge) }}" required>
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold text-navy">Operational Status <span class="text-danger">*</span></label>
                                                                <select name="status" class="form-select" required>
                                                                    <option value="available" {{ old('status', $bed->status) === 'available' ? 'selected' : '' }}>Available (Vacant)</option>
                                                                    <option value="occupied" {{ old('status', $bed->status) === 'occupied' ? 'selected' : '' }}>Occupied</option>
                                                                    <option value="reserved" {{ old('status', $bed->status) === 'reserved' ? 'selected' : '' }}>Reserved</option>
                                                                    <option value="cleaning" {{ old('status', $bed->status) === 'cleaning' ? 'selected' : '' }}>Cleaning / Sanitizing</option>
                                                                    <option value="maintenance" {{ old('status', $bed->status) === 'maintenance' ? 'selected' : '' }}>Maintenance / Repair</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Update Bed</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endcan
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- MODAL 1: Assign Staff Member / Doctor -->
    @can('manageStaff', $department)
        <div class="modal fade" id="assignStaffModal" tabindex="-1" aria-labelledby="assignStaffModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-navy text-white">
                        <h5 class="modal-title fs-6 fw-bold" id="assignStaffModalLabel">
                            <i class="bi bi-person-plus text-teal me-1"></i> Assign Personnel to {{ $department->name }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('portal.departments.staff.assign', $department) }}" method="POST">
                        @csrf
                        <div class="modal-body p-4 text-start">
                            <div class="mb-3">
                                <label for="assign_user_id" class="form-label fw-semibold text-navy">Staff Member / Doctor <span class="text-danger">*</span></label>
                                <select name="user_id" id="assign_user_id" class="form-select" required>
                                    <option value="">-- Select Active Personnel --</option>
                                    @foreach($assignableStaff as $person)
                                        @php
                                            $isDoctor = $person->hasRole('doctor');
                                            $currentDept = $person->staffProfile?->department?->name;
                                        @endphp
                                        <option value="{{ $person->id }}">
                                            {{ $person->name }} ({{ $isDoctor ? 'Doctor' : $person->roles->pluck('name')->join(', ') }})
                                            @if($currentDept)
                                                - Currently in {{ $currentDept }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="assign_designation" class="form-label fw-semibold text-navy">Designation / Hospital Role <span class="text-danger">*</span></label>
                                <input type="text" name="designation" id="assign_designation" class="form-control" 
                                       placeholder="e.g. Senior Consultant, Assistant Surgeon, Duty Doctor, Staff Nurse" 
                                       required maxlength="100">
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label for="assign_specialization" class="form-label fw-semibold text-navy">Specialization</label>
                                    <input type="text" name="specialization" id="assign_specialization" class="form-control" 
                                           placeholder="e.g. Orthopedics, Obstetrics, General Surgery" 
                                           maxlength="100">
                                </div>
                                <div class="col-md-6">
                                    <label for="assign_license_number" class="form-label fw-semibold text-navy">BMDC / License #</label>
                                    <input type="text" name="license_number" id="assign_license_number" class="form-control font-monospace" 
                                           placeholder="e.g. A-48291" 
                                           maxlength="50">
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label for="assign_shift" class="form-label fw-semibold text-navy">Shift Assignment <span class="text-danger">*</span></label>
                                    <select name="shift" id="assign_shift" class="form-select" required>
                                        <option value="morning">Morning (8 AM - 4 PM)</option>
                                        <option value="evening">Evening (4 PM - 12 AM)</option>
                                        <option value="night">Night (12 AM - 8 AM)</option>
                                        <option value="rotational" selected>Rotational / On-Call</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="assign_status" class="form-label fw-semibold text-navy">Staff Status <span class="text-danger">*</span></label>
                                    <select name="status" id="assign_status" class="form-select" required>
                                        <option value="active" selected>Active Duty</option>
                                        <option value="on_leave">On Leave</option>
                                        <option value="inactive">Inactive / Transferred</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2 me-1"></i> Save Assignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    <!-- MODAL 2: Add Bed to Department -->
    @can('configureBeds', $department)
        <div class="modal fade" id="addBedModal" tabindex="-1" aria-labelledby="addBedModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-navy text-white">
                        <h5 class="modal-title fs-6 fw-bold" id="addBedModalLabel">
                            <i class="bi bi-plus-circle text-teal me-1"></i> Add Bed to {{ $department->name }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('portal.departments.beds.store', $department) }}" method="POST">
                        @csrf
                        <div class="modal-body p-4 text-start">
                            <div class="mb-3">
                                <label for="bed_number" class="form-label fw-semibold text-navy">Bed Identifier <span class="text-danger">*</span></label>
                                <input type="text" name="bed_number" id="bed_number" 
                                       class="form-control font-monospace text-uppercase" 
                                       placeholder="e.g. {{ $department->code }}-01 or BED-05" 
                                       required maxlength="32">
                                <div class="form-text">Must be a unique bed number across the hospital.</div>
                            </div>

                            <div class="mb-3">
                                <label for="bed_category" class="form-label fw-semibold text-navy">Bed Category <span class="text-danger">*</span></label>
                                <select name="category" id="bed_category" class="form-select" required>
                                    <option value="department" selected>Departmental Care Bed</option>
                                    <option value="general">General Ward Bed</option>
                                    <option value="cabin">Private Cabin Bed</option>
                                    <option value="post_op">Post-Op / Surgical Recovery</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="bed_daily_charge" class="form-label fw-semibold text-navy">Daily Stay Tariff (BDT) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">৳</span>
                                    <input type="number" step="0.01" min="0" max="999999" name="daily_charge" id="bed_daily_charge" 
                                           class="form-control font-monospace" 
                                           placeholder="e.g. 500.00" 
                                           value="500.00" 
                                           required>
                                </div>
                                <div class="form-text">Charged per midnight / 24-hour cycle.</div>
                            </div>

                            <div class="mb-3">
                                <label for="bed_status" class="form-label fw-semibold text-navy">Initial Status <span class="text-danger">*</span></label>
                                <select name="status" id="bed_status" class="form-select" required>
                                    <option value="available" selected>Available (Vacant & Ready)</option>
                                    <option value="reserved">Reserved for Admission</option>
                                    <option value="cleaning">Cleaning / Sanitization</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-door-open me-1"></i> Configure Bed
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
</x-layouts.portal>
