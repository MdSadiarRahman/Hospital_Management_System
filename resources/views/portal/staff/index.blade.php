<x-layouts.portal role="admin" title="Staff Management & Roster" activeNav="staff">
    <div class="container-fluid p-0">
        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <x-breadcrumb :items="['Administration' => url('/previews/admin'), 'Staff Directory & Roster' => null]" class="mb-1" />
                <h1 class="h3 fw-bold text-navy mb-0">Hospital Staff Management</h1>
                <p class="text-muted-hms mb-0" style="font-size: 0.875rem;">
                    Manage healthcare personnel profiles, multi-department allocations, designations, and 8-hour shift rosters.
                </p>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <a href="{{ route('portal.staff.shifts') }}" class="btn btn-outline-teal d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-calendar3-week"></i>
                    <span>24/7 Shift Roster Board</span>
                </a>

                @can('create', App\Models\StaffProfile::class)
                    <a href="{{ route('portal.staff.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>Register Staff Member</span>
                    </a>
                @endcan
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

        <!-- Summary KPI Bento Grid -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 border-navy">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Staff</span>
                            <h3 class="fw-bold text-navy mb-0 mt-1">{{ $totalStaff }}</h3>
                            <span class="text-muted" style="font-size: 0.78rem;">Active Personnel (~14 Clinic Team)</span>
                        </div>
                        <div class="rounded-circle bg-navy bg-opacity-10 text-navy p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 border-primary">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Active Doctors</span>
                            <h3 class="fw-bold text-navy mb-0 mt-1">{{ $totalDoctors }}</h3>
                            <span class="text-muted" style="font-size: 0.78rem;">24/7 Coverage (Min. 2 Doctors)</span>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-person-badge fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 border-teal">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Nursing Team</span>
                            <h3 class="fw-bold text-navy mb-0 mt-1">{{ $totalNurses }}</h3>
                            <span class="text-muted" style="font-size: 0.78rem;">6 Sisters (8-Hour Shifts)</span>
                        </div>
                        <div class="rounded-circle bg-teal bg-opacity-10 text-teal p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-heart-pulse fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 border-warning">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">On Leave</span>
                            <h3 class="fw-bold text-navy mb-0 mt-1">{{ $onLeave }}</h3>
                            <span class="text-muted" style="font-size: 0.78rem;">Temporary Absent</span>
                        </div>
                        <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3 mb-4">
            <form action="{{ route('portal.staff.index') }}" method="GET" class="row g-2 align-items-center">
                <!-- Search Input -->
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" 
                               placeholder="Search staff name, employee ID, license, designation..." 
                               value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Department Filter -->
                <div class="col-sm-6 col-md-2">
                    <select name="department_id" class="form-select bg-light">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Role Filter -->
                <div class="col-sm-6 col-md-2">
                    <select name="role" class="form-select bg-light">
                        <option value="">All Staff Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                                {{ $role->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Shift Filter -->
                <div class="col-sm-6 col-md-2">
                    <select name="shift" class="form-select bg-light">
                        <option value="">All Shifts</option>
                        <option value="morning" {{ request('shift') === 'morning' ? 'selected' : '' }}>Morning (8 AM - 4 PM)</option>
                        <option value="evening" {{ request('shift') === 'evening' ? 'selected' : '' }}>Evening (4 PM - 12 AM)</option>
                        <option value="night" {{ request('shift') === 'night' ? 'selected' : '' }}>Night (12 AM - 8 AM)</option>
                        <option value="rotational" {{ request('shift') === 'rotational' ? 'selected' : '' }}>Rotational / On-Call</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-sm-6 col-md-1">
                    <select name="status" class="form-select bg-light">
                        <option value="">Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="on_leave" {{ request('status') === 'on_leave' ? 'selected' : '' }}>Leave</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-navy w-100" title="Apply Filter">
                        <i class="bi bi-funnel"></i>
                    </button>
                    @if(request()->anyFilled(['search', 'department_id', 'role', 'shift', 'status']))
                        <a href="{{ route('portal.staff.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Staff Data Table -->
        <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden mb-4">
            <div class="card-body p-0">
                @if($staffList->isEmpty())
                    <div class="p-5 text-center">
                        <div class="mb-3 text-muted">
                            <i class="bi bi-person-x display-4 opacity-50"></i>
                        </div>
                        <h4 class="fw-bold text-navy">No Staff Members Found</h4>
                        <p class="text-muted mb-4" style="max-width: 450px; margin: 0 auto;">
                            No personnel matched your search or filter parameters. Try adjusting the filter criteria or register a new staff member.
                        </p>
                        @can('create', App\Models\StaffProfile::class)
                            <a href="{{ route('portal.staff.create') }}" class="btn btn-primary">
                                <i class="bi bi-person-plus me-1"></i> Register New Staff
                            </a>
                        @endcan
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                <tr>
                                    <th class="ps-4">Staff Member & ID</th>
                                    <th>Role & Designation</th>
                                    <th>Department Allocation</th>
                                    <th>Shift Schedule</th>
                                    <th>Status</th>
                                    <th>Contact</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y" style="font-size: 0.875rem;">
                                @foreach($staffList as $profile)
                                    @php
                                        $user = $profile->user;
                                        $primaryDept = $profile->department;
                                        $secondaryDepts = $profile->departments->reject(fn($d) => $d->id === $profile->department_id);
                                    @endphp
                                    <tr>
                                        <!-- Staff Member -->
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2.5">
                                                <div class="rounded-circle bg-navy text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                                    {{ strtoupper(substr($user?->name ?? 'S', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <a href="{{ route('portal.staff.show', $profile) }}" class="fw-bold text-navy text-decoration-none hover-teal d-block">
                                                        {{ $user?->name ?? 'Staff User' }}
                                                    </a>
                                                    <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.72rem;">
                                                        {{ $profile->employee_id ?: 'MCNH-STF-' . $profile->id }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Role & Designation -->
                                        <td>
                                            <div class="d-flex flex-wrap gap-1 mb-1">
                                                @foreach($user?->roles ?? [] as $role)
                                                    <span class="badge bg-navy bg-opacity-10 text-navy rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 0.7rem;">
                                                        {{ $role->title }}
                                                    </span>
                                                @endforeach
                                            </div>
                                            <span class="fw-semibold text-navy d-block" style="font-size: 0.85rem;">{{ $profile->designation }}</span>
                                            @if($profile->license_number)
                                                <small class="text-muted font-monospace" style="font-size: 0.75rem;">
                                                    <i class="bi bi-award me-0.5"></i> {{ $profile->license_number }}
                                                </small>
                                            @endif
                                        </td>

                                        <!-- Department Allocation -->
                                        <td>
                                            @if($primaryDept)
                                                <span class="badge bg-teal-subtle text-teal border border-teal-subtle rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1 mb-1" style="font-size: 0.75rem;">
                                                    <i class="bi bi-hospital"></i> {{ $primaryDept->name }}
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5 mb-1" style="font-size: 0.72rem;">General Facility</span>
                                            @endif

                                            <!-- Cross-Department allocations -->
                                            @if($secondaryDepts->isNotEmpty())
                                                <div class="d-flex flex-wrap gap-1 mt-1">
                                                    @foreach($secondaryDepts as $sDept)
                                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-0.5" style="font-size: 0.68rem;" title="Secondary Cross-Department Coverage">
                                                            + {{ $sDept->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Shift Schedule -->
                                        <td>
                                            @if($profile->shift === 'morning')
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">
                                                    <i class="bi bi-sun me-1"></i> Morning (8h)
                                                </span>
                                            @elseif($profile->shift === 'evening')
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">
                                                    <i class="bi bi-sunset me-1"></i> Evening (8h)
                                                </span>
                                            @elseif($profile->shift === 'night')
                                                <span class="badge bg-dark-subtle text-dark border border-dark-subtle rounded-pill px-2.5 py-1">
                                                    <i class="bi bi-moon-stars me-1"></i> Night (8h)
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1">
                                                    <i class="bi bi-arrow-repeat me-1"></i> Rotational
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Status -->
                                        <td>
                                            @if($profile->status === 'active')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Active
                                                </span>
                                            @elseif($profile->status === 'on_leave')
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1">
                                                    <i class="bi bi-clock-history me-1"></i> On Leave
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">
                                                    <i class="bi bi-dash-circle me-1"></i> Inactive
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Contact -->
                                        <td>
                                            <span class="text-navy font-monospace d-block" style="font-size: 0.8rem;">{{ $user?->phone }}</span>
                                            <small class="text-muted" style="font-size: 0.72rem;">{{ $user?->email }}</small>
                                        </td>

                                        <!-- Actions -->
                                        <td class="text-end pe-4">
                                            <div class="d-flex align-items-center justify-content-end gap-1">
                                                <a href="{{ route('portal.staff.show', $profile) }}" class="btn btn-sm btn-outline-primary" title="View Full Profile">
                                                    <i class="bi bi-eye"></i> View
                                                </a>

                                                @can('update', $profile)
                                                    <a href="{{ route('portal.staff.edit', $profile) }}" class="btn btn-sm btn-light text-navy" title="Edit Profile">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>

                                                    <!-- Toggle Status -->
                                                    <form action="{{ route('portal.staff.toggle-status', $profile) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-light {{ $profile->status === 'active' ? 'text-warning' : 'text-success' }}"
                                                                title="{{ $profile->status === 'active' ? 'Mark Inactive' : 'Activate Staff' }}"
                                                                onclick="return confirm('Change status for staff member {{ $user?->name }}?')">
                                                            <i class="bi {{ $profile->status === 'active' ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($staffList->hasPages())
                        <div class="p-3 border-top">
                            {{ $staffList->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-layouts.portal>
