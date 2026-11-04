<x-layouts.portal role="admin" title="Inpatient Admissions & Bed Allocation">
    <div class="container-fluid px-0">
        <!-- Page Header & Action Bar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Admissions</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">Inpatient Admissions & Bed Allocation</h1>
                    <span class="badge bg-teal text-white font-monospace">{{ $metrics['active_inpatients'] }} Active Inpatients</span>
                    <span class="badge bg-navy text-white font-monospace">{{ $metrics['occupancy_rate'] }}% Ward Occupancy</span>
                </div>
                <p class="text-muted-hms small mb-0 mt-1">
                    Manage patient hospital stays, assign General and Department-wise beds, and track transfer history.
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                @can('create', App\Models\Admission::class)
                    <a href="{{ route('portal.admissions.create') }}" class="btn btn-teal d-inline-flex align-items-center gap-2 shadow-xs">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Admit New Inpatient</span>
                    </a>
                @endcan

                <a href="{{ route('portal.beds.index') }}" class="btn btn-outline-navy d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-door-open"></i>
                    <span>10-Bed Live Station</span>
                </a>
            </div>
        </div>

        <!-- Alert Notifications -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please check the following issues:</div>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Bento KPI Grid: Admission & Occupancy Overview -->
        <div class="row g-3 mb-4">
            <!-- Active Inpatients -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-xs h-100 p-3 bg-white" style="border-left: 4px solid #10B981 !important;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted-hms text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Active Inpatients</span>
                            <h3 class="fw-bold text-navy mb-0 mt-1">{{ $metrics['active_inpatients'] }}</h3>
                            <span class="text-success small fw-medium">Currently in beds</span>
                        </div>
                        <span class="rounded p-2 bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-person-wheelchair fs-5"></i>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Inpatient Bed Occupancy Rate -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-xs h-100 p-3 bg-white" style="border-left: 4px solid #087F8C !important;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted-hms text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Ward Occupancy</span>
                            <h3 class="fw-bold text-teal mb-0 mt-1">{{ $metrics['occupancy_rate'] }}%</h3>
                            <span class="text-muted-hms small">{{ $metrics['active_inpatients'] }} of {{ $metrics['total_beds'] }} beds used</span>
                        </div>
                        <span class="rounded p-2 bg-teal bg-opacity-10 text-teal d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-hospital fs-5"></i>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Admitted Today -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-xs h-100 p-3 bg-white" style="border-left: 4px solid #3B82F6 !important;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted-hms text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Admitted Today</span>
                            <h3 class="fw-bold text-primary mb-0 mt-1">{{ $metrics['admitted_today'] }}</h3>
                            <span class="text-muted-hms small">New hospital admissions</span>
                        </div>
                        <span class="rounded p-2 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-calendar2-plus fs-5"></i>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Total Historical Stays -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-xs h-100 p-3 bg-white" style="border-left: 4px solid #123B5D !important;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted-hms text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Admissions</span>
                            <h3 class="fw-bold text-navy mb-0 mt-1">{{ $metrics['total_admissions'] }}</h3>
                            <span class="text-muted-hms small">{{ $metrics['discharged_count'] }} discharged · {{ $metrics['cancelled_count'] }} cancelled</span>
                        </div>
                        <span class="rounded p-2 bg-navy bg-opacity-10 text-navy d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-journal-medical fs-5"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <div class="card border-0 shadow-xs mb-4">
            <div class="card-body p-3">
                <form action="{{ route('portal.admissions.index') }}" method="GET" class="row g-2 align-items-end">
                    <!-- Search input -->
                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted-hms fw-semibold mb-1">Search Inpatient / Bed</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control border-start-0" placeholder="Adm #, Patient, Phone, Bed #...">
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted-hms fw-semibold mb-1">Stay Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="admitted" {{ ($filters['status'] ?? '') === 'admitted' ? 'selected' : '' }}>Admitted (Inpatient)</option>
                            <option value="discharged" {{ ($filters['status'] ?? '') === 'discharged' ? 'selected' : '' }}>Discharged</option>
                            <option value="referred_external" {{ ($filters['status'] ?? '') === 'referred_external' ? 'selected' : '' }}>Referred External</option>
                            <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <!-- Department Filter -->
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted-hms fw-semibold mb-1">Department</label>
                        <select name="department_id" class="form-select form-select-sm">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ ($filters['department_id'] ?? '') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Attending Doctor -->
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted-hms fw-semibold mb-1">Attending Doctor</label>
                        <select name="doctor_id" class="form-select form-select-sm">
                            <option value="">All Doctors</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ ($filters['doctor_id'] ?? '') == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date From -->
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted-hms fw-semibold mb-1">Admitted Since</label>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-select form-select-sm">
                    </div>

                    <!-- Actions -->
                    <div class="col-12 col-md-1 d-flex gap-1">
                        <button type="submit" class="btn btn-navy btn-sm flex-grow-1" title="Apply Filters">
                            <i class="bi bi-funnel"></i>
                        </button>
                        @if(!empty(array_filter($filters)))
                            <a href="{{ route('portal.admissions.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Admissions Table -->
        <div class="card border-0 shadow-xs mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold text-navy mb-0">Inpatient Directory</h5>
                    <span class="text-muted-hms small">Showing {{ $admissions->total() }} admissions in hospital registry</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light text-muted-hms text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-3">Admission #</th>
                            <th>Patient</th>
                            <th>Allocated Bed</th>
                            <th>Department & Doctor</th>
                            <th>Admission Date & Stay</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admissions as $admission)
                            <tr>
                                <!-- Admission Number & Visit -->
                                <td class="ps-3">
                                    <a href="{{ route('portal.admissions.show', $admission) }}" class="fw-bold text-navy text-decoration-none font-monospace">
                                        {{ $admission->admission_number }}
                                    </a>
                                    <div class="small text-muted-hms font-monospace">
                                        Visit: <a href="{{ route('portal.visits.show', $admission->visit_id) }}" class="text-decoration-none text-muted-hms">{{ $admission->visit?->visit_number ?? '—' }}</a>
                                    </div>
                                </td>

                                <!-- Patient Master Information -->
                                <td>
                                    <a href="{{ route('portal.patients.show', $admission->patient_id) }}" class="fw-semibold text-navy text-decoration-none d-block">
                                        {{ $admission->patient?->name ?? 'Unknown Patient' }}
                                    </a>
                                    <div class="small text-muted-hms">
                                        <span class="font-monospace text-teal fw-semibold">{{ $admission->patient?->patient_code }}</span>
                                        · {{ $admission->patient?->gender ? ucfirst($admission->patient->gender) : '—' }}
                                        · {{ $admission->patient?->phone ?? '—' }}
                                    </div>
                                </td>

                                <!-- Allocated Bed -->
                                <td>
                                    @if($admission->status === 'admitted' && $admission->currentBed)
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-teal text-white font-monospace px-2 py-1">
                                                <i class="bi bi-door-open me-1"></i>{{ $admission->currentBed->bed_number }}
                                            </span>
                                            <div>
                                                <span class="d-block fw-semibold text-navy small">{{ $admission->currentBed->room_number ?? 'Ward Room' }}</span>
                                                <span class="d-block text-muted-hms" style="font-size: 0.72rem;">{{ $admission->currentBed->category_label }}</span>
                                            </div>
                                        </div>
                                    @elseif($admission->status === 'discharged')
                                        <span class="badge bg-light text-muted border">Bed Released</span>
                                    @elseif($admission->status === 'cancelled')
                                        <span class="badge bg-light text-danger border">Cancelled / Released</span>
                                    @else
                                        <span class="badge bg-secondary text-white">None</span>
                                    @endif
                                </td>

                                <!-- Department & Doctor -->
                                <td>
                                    <span class="d-block fw-semibold text-navy">
                                        {{ $admission->department?->name ?? ($admission->currentBed?->department?->name ?? 'General Ward') }}
                                    </span>
                                    <span class="d-block text-muted-hms small">
                                        <i class="bi bi-person-badge text-teal me-1"></i>{{ $admission->attendingDoctor?->name ?? 'Unassigned' }}
                                    </span>
                                </td>

                                <!-- Admission Date & Stay Duration -->
                                <td>
                                    <span class="d-block text-navy fw-medium">
                                        {{ $admission->admission_date ? $admission->admission_date->format('d M Y, h:i A') : '—' }}
                                    </span>
                                    <span class="d-block text-muted-hms small">
                                        Duration: <strong class="text-teal">{{ $admission->stay_duration }}</strong>
                                    </span>
                                </td>

                                <!-- Status Badge -->
                                <td>
                                    <span class="badge bg-{{ $admission->status_badge_class }} text-white">
                                        {{ $admission->status_label }}
                                    </span>
                                </td>

                                <!-- Action Buttons -->
                                <td class="text-end pe-3">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="font-size: 0.85rem;">
                                            <li>
                                                <a class="dropdown-item py-1" href="{{ route('portal.admissions.show', $admission) }}">
                                                    <i class="bi bi-eye text-primary me-2"></i>View Stay Dossier
                                                </a>
                                            </li>
                                            @if($admission->status === 'admitted')
                                                @can('transferBed', $admission)
                                                    <li>
                                                        <a class="dropdown-item py-1" href="{{ route('portal.admissions.show', $admission) }}#transfer-bed">
                                                            <i class="bi bi-arrow-left-right text-teal me-2"></i>Transfer Bed
                                                        </a>
                                                    </li>
                                                @endcan
                                                @can('cancel', $admission)
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item py-1 text-danger" href="{{ route('portal.admissions.show', $admission) }}#cancel-modal">
                                                            <i class="bi bi-x-circle me-2"></i>Cancel Admission
                                                        </a>
                                                    </li>
                                                @endcan
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted-hms">
                                    <i class="bi bi-hospital fs-1 text-muted d-block mb-2"></i>
                                    <div class="fw-semibold">No inpatient admission records found.</div>
                                    <p class="small text-muted-hms mb-3">Admit patients to General or Department beds using the button below.</p>
                                    @can('create', App\Models\Admission::class)
                                        <a href="{{ route('portal.admissions.create') }}" class="btn btn-teal btn-sm">
                                            <i class="bi bi-box-arrow-in-right me-1"></i>Admit First Inpatient
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($admissions->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $admissions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.portal>

