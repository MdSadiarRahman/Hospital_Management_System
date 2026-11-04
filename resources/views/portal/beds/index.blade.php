<x-layouts.portal role="admin" title="10-Bed Inpatient Station & Ward Inventory">
    <div class="container-fluid px-0">
        <!-- Page Header & Action Bar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Bed Management</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">Inpatient Bed Management Station</h1>
                    <span class="badge bg-teal text-white font-monospace">10 Beds Initial Capacity</span>
                    <span class="badge bg-navy text-white font-monospace">{{ $metrics['occupancy_rate'] }}% Occupancy</span>
                </div>
                <p class="text-muted-hms small mb-0 mt-1">
                    Live ward bed distribution across General, Gynecology, Orthopedic, and Surgery departments.
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                @can('create', App\Models\Bed::class)
                    <button type="button" class="btn btn-teal d-inline-flex align-items-center gap-2 shadow-xs" data-bs-toggle="modal" data-bs-target="#createBedModal">
                        <i class="bi bi-plus-circle"></i>
                        <span>Provision New Bed</span>
                    </button>
                @endcan

                <a href="{{ route('portal.departments.index') }}" class="btn btn-outline-navy d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-diagram-3"></i>
                    <span>Department Wards</span>
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

        <!-- Bento KPI Grid: 10 Beds Live Status -->
        <div class="row g-3 mb-4">
            <!-- Total Capacity -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Total Capacity</span>
                        <span class="rounded bg-primary bg-opacity-10 text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-door-open-fill"></i>
                        </span>
                    </div>
                    <div class="h3 fw-bold text-navy mb-0">{{ $metrics['total_capacity'] }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">Configurable & Expandable</span>
                </div>
            </div>

            <!-- Available Beds -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Available</span>
                        <span class="rounded bg-success bg-opacity-10 text-success p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-check2-circle"></i>
                        </span>
                    </div>
                    <div class="h3 fw-bold text-success mb-0">{{ $metrics['available'] }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">Ready for immediate admission</span>
                </div>
            </div>

            <!-- Occupied Beds -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Occupied</span>
                        <span class="rounded bg-danger bg-opacity-10 text-danger p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-person-fill-lock"></i>
                        </span>
                    </div>
                    <div class="h3 fw-bold text-danger mb-0">{{ $metrics['occupied'] }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">Active inpatients</span>
                </div>
            </div>

            <!-- Reserved Beds -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Reserved</span>
                        <span class="rounded bg-warning bg-opacity-10 text-warning p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-clock-history"></i>
                        </span>
                    </div>
                    <div class="h3 fw-bold text-warning-emphasis mb-0">{{ $metrics['reserved'] }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">Scheduled procedures/OT</span>
                </div>
            </div>

            <!-- Cleaning & Turnover -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Cleaning</span>
                        <span class="rounded bg-info bg-opacity-10 text-info p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-stars"></i>
                        </span>
                    </div>
                    <div class="h3 fw-bold text-info mb-0">{{ $metrics['cleaning'] }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">Sanitization in progress</span>
                </div>
            </div>

            <!-- Maintenance -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Maintenance</span>
                        <span class="rounded bg-secondary bg-opacity-10 text-secondary p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-tools"></i>
                        </span>
                    </div>
                    <div class="h3 fw-bold text-secondary mb-0">{{ $metrics['maintenance'] }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">Out of service / repairs</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3 mb-4">
            <form method="GET" action="{{ route('portal.beds.index') }}" class="row g-2 align-items-center">
                <div class="col-lg-3 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted-hms"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search Bed / Room Number..." value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>

                <div class="col-lg-2 col-md-4">
                    <select name="department_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Departments & Wards</option>
                        <option value="general" {{ ($filters['department_id'] ?? '') === 'general' ? 'selected' : '' }}>General Beds (Common)</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ ($filters['department_id'] ?? '') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-4">
                    <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="general" {{ ($filters['category'] ?? '') === 'general' ? 'selected' : '' }}>General Ward Bed</option>
                        <option value="department" {{ ($filters['category'] ?? '') === 'department' ? 'selected' : '' }}>Department Specialized</option>
                        <option value="cabin" {{ ($filters['category'] ?? '') === 'cabin' ? 'selected' : '' }}>Private Cabin</option>
                        <option value="post_op" {{ ($filters['category'] ?? '') === 'post_op' ? 'selected' : '' }}>Post-Operative Recovery</option>
                        <option value="emergency" {{ ($filters['category'] ?? '') === 'emergency' ? 'selected' : '' }}>Emergency Observation</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-4">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="available" {{ ($filters['status'] ?? '') === 'available' ? 'selected' : '' }}>Available</option>
                        <option value="occupied" {{ ($filters['status'] ?? '') === 'occupied' ? 'selected' : '' }}>Occupied</option>
                        <option value="reserved" {{ ($filters['status'] ?? '') === 'reserved' ? 'selected' : '' }}>Reserved</option>
                        <option value="cleaning" {{ ($filters['status'] ?? '') === 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                        <option value="maintenance" {{ ($filters['status'] ?? '') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-8 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-navy btn-sm d-inline-flex align-items-center gap-1">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                    @if(!empty(array_filter($filters)))
                        <a href="{{ route('portal.beds.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Live Visual Bed Ward Grid (Bento Style Cards) -->
        <div class="row g-3 mb-4">
            @forelse($beds as $bed)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100 position-relative overflow-hidden border-top border-4 border-{{ $bed->status_badge_class }}">
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            <!-- Top Card Header -->
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="rounded bg-light text-navy p-2 d-inline-flex align-items-center justify-content-center fw-bold font-monospace" style="width: 40px; height: 40px; font-size: 1rem;">
                                            <i class="bi bi-hospital"></i>
                                        </span>
                                        <div>
                                            <h2 class="h5 fw-bold text-navy mb-0 font-monospace">{{ $bed->bed_number }}</h2>
                                            <span class="text-muted small" style="font-size: 0.75rem;">
                                                Room: <strong class="text-navy">{{ $bed->room_number ?? 'General Ward' }}</strong>
                                            </span>
                                        </div>
                                    </div>
                                    <span class="badge bg-{{ $bed->status_badge_class }} text-uppercase px-2.5 py-1.5" style="font-size: 0.7rem;">
                                        {{ ucfirst($bed->status) }}
                                    </span>
                                </div>

                                <!-- Department & Category Metadata -->
                                <div class="mb-2.5" style="font-size: 0.8rem;">
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted-hms">Department:</span>
                                        <span class="fw-semibold text-navy">
                                            @if($bed->department)
                                                {{ $bed->department->name }}
                                            @else
                                                <span class="badge bg-light text-dark border">General Ward</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted-hms">Category:</span>
                                        <span class="text-navy">{{ $bed->category_label }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted-hms">Daily Charge:</span>
                                        <span class="font-monospace fw-bold text-teal">৳ {{ number_format($bed->daily_charge, 2) }}</span>
                                    </div>
                                </div>

                                <!-- Occupant / Status Info Box -->
                                @if($bed->status === 'occupied' && $bed->currentAssignment)
                                    <div class="p-2.5 rounded bg-danger bg-opacity-10 border border-danger-subtle mb-3" style="font-size: 0.775rem;">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="fw-bold text-danger text-truncate">
                                                <i class="bi bi-person-fill"></i> {{ $bed->currentAssignment->admission?->patient?->name ?? 'Occupant' }}
                                            </span>
                                            <span class="badge bg-danger text-white font-monospace" style="font-size: 0.65rem;">
                                                {{ $bed->currentAssignment->admission?->patient?->patient_code }}
                                            </span>
                                        </div>
                                        <div class="text-muted small">
                                            Admitted: {{ $bed->currentAssignment->assigned_at?->format('d M, h:i A') }}
                                        </div>
                                        <div class="text-muted small text-truncate">
                                            Dr: {{ $bed->currentAssignment->admission?->attendingDoctor?->name ?? 'Medical Officer' }}
                                        </div>
                                    </div>
                                @elseif($bed->status === 'cleaning')
                                    <div class="p-2 rounded bg-info bg-opacity-10 border border-info-subtle text-info mb-3 small">
                                        <i class="bi bi-stars me-1"></i> Sanitization in progress. Ready soon.
                                    </div>
                                @elseif($bed->status === 'maintenance')
                                    <div class="p-2 rounded bg-secondary bg-opacity-10 border border-secondary-subtle text-secondary mb-3 small">
                                        <i class="bi bi-tools me-1"></i> Under engineering repair/inspection.
                                    </div>
                                @else
                                    <div class="p-2 rounded bg-success bg-opacity-10 border border-success-subtle text-success mb-3 small">
                                        <i class="bi bi-check-circle me-1"></i> Cleaned & ready for admission.
                                    </div>
                                @endif
                            </div>

                            <!-- Bottom Action Buttons -->
                            <div class="d-flex align-items-center justify-content-between pt-2 border-top gap-1">
                                <a href="{{ route('portal.beds.show', $bed) }}" class="btn btn-outline-navy btn-sm py-1 px-2" style="font-size: 0.75rem;" title="View Bed Dossier">
                                    <i class="bi bi-eye"></i> Details
                                </a>

                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm py-1 px-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.75rem;">
                                        Quick Action
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.8rem;">
                                        @can('updateStatus', $bed)
                                            <li>
                                                <button class="dropdown-item" type="button" onclick="openStatusModal({{ $bed->id }}, '{{ $bed->bed_number }}', '{{ $bed->status }}')">
                                                    <i class="bi bi-arrow-repeat text-teal me-2"></i> Change Bed Status
                                                </button>
                                            </li>
                                        @endcan

                                        @can('update', $bed)
                                            <li>
                                                <a class="dropdown-item" href="{{ route('portal.beds.edit', $bed) }}">
                                                    <i class="bi bi-pencil text-primary me-2"></i> Edit Specifications
                                                </a>
                                            </li>
                                        @endcan

                                        @can('delete', $bed)
                                            @if($bed->status !== 'occupied')
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="{{ route('portal.beds.destroy', $bed) }}" onsubmit="return confirm('Are you sure you want to decommission and remove Bed {{ $bed->bed_number }}?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="bi bi-trash me-2"></i> Remove Bed
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                        @endcan
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-5 text-center">
                        <i class="bi bi-hospital fs-1 text-muted-hms mb-3"></i>
                        <h3 class="h5 fw-bold text-navy">No beds matching your criteria</h3>
                        <p class="text-muted-hms small mb-3">Adjust your filters or provision new inpatient beds.</p>
                        @can('create', App\Models\Bed::class)
                            <div>
                                <button type="button" class="btn btn-teal btn-sm" data-bs-toggle="modal" data-bs-target="#createBedModal">
                                    <i class="bi bi-plus-circle me-1"></i> Provision Bed
                                </button>
                            </div>
                        @endcan
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($beds->hasPages())
            <div class="d-flex justify-content-end mb-4">
                {{ $beds->links() }}
            </div>
        @endif
    </div>

    <!-- Modal 1: Provision New Bed Modal (Admin) -->
    @can('create', App\Models\Bed::class)
        <div class="modal fade" id="createBedModal" tabindex="-1" aria-labelledby="createBedModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form method="POST" action="{{ route('portal.beds.store') }}">
                        @csrf
                        <div class="modal-header bg-navy text-white">
                            <h5 class="modal-title h6 fw-bold mb-0" id="createBedModalLabel">
                                <i class="bi bi-plus-circle me-1 text-teal"></i> Provision New Bed in Ward
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-navy fw-semibold small" for="bed_number">
                                        Bed Number <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="bed_number" id="bed_number" class="form-control form-control-sm text-uppercase font-monospace" placeholder="e.g. GYN-03 or GEN-05" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-navy fw-semibold small" for="room_number">
                                        Room Number
                                    </label>
                                    <input type="text" name="room_number" id="room_number" class="form-control form-control-sm font-monospace" placeholder="e.g. Room 102">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-navy fw-semibold small" for="department_id">
                                        Department Assignment
                                    </label>
                                    <select name="department_id" id="department_id" class="form-select form-select-sm">
                                        <option value="">General Ward (Unassigned to Dept)</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-navy fw-semibold small" for="category">
                                        Bed Category <span class="text-danger">*</span>
                                    </label>
                                    <select name="category" id="category" class="form-select form-select-sm" required>
                                        <option value="general">General Bed</option>
                                        <option value="department" selected>Department Specialized Bed</option>
                                        <option value="cabin">Private Cabin</option>
                                        <option value="post_op">Post-Operative Recovery</option>
                                        <option value="emergency">Emergency Observation</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-navy fw-semibold small" for="daily_charge">
                                        Daily Tariff (৳) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" step="0.01" min="0" name="daily_charge" id="daily_charge" class="form-control form-control-sm font-monospace" value="1200.00" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-navy fw-semibold small" for="status">
                                        Initial Status <span class="text-danger">*</span>
                                    </label>
                                    <select name="status" id="status" class="form-select form-select-sm" required>
                                        <option value="available" selected>Available (Ready)</option>
                                        <option value="cleaning">Cleaning</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="reserved">Reserved</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label text-navy fw-semibold small" for="description">
                                        Bed Specifications & Description
                                    </label>
                                    <textarea name="description" id="description" class="form-control form-control-sm" rows="2" placeholder="e.g. Equipped with central oxygen, motorized Fowler mechanism, cardiac monitor stand..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2 px-4">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-teal btn-sm">
                                <i class="bi bi-check2-circle me-1"></i> Provision Bed
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    <!-- Modal 2: Quick Status Change Modal -->
    <div class="modal fade" id="statusChangeModal" tabindex="-1" aria-labelledby="statusChangeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="statusChangeForm" method="POST" action="">
                    @csrf
                    <div class="modal-header bg-navy text-white">
                        <h5 class="modal-title h6 fw-bold mb-0" id="statusChangeModalLabel">
                            <i class="bi bi-arrow-repeat me-1 text-teal"></i> Update Bed Status: <span id="modalBedNumber" class="font-monospace text-teal"></span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-navy fw-semibold small" for="modal_new_status">
                                New Status <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="modal_new_status" class="form-select form-select-sm" required>
                                <option value="available">Available (Clean & Ready)</option>
                                <option value="cleaning">Cleaning / Sanitization</option>
                                <option value="maintenance">Maintenance / Engineering Repair</option>
                                <option value="reserved">Reserved for Upcoming OT/Admission</option>
                            </select>
                            <span class="text-muted small mt-1 d-block" style="font-size: 0.725rem;">
                                Note: Beds are marked 'Occupied' automatically when patients are admitted.
                            </span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-navy fw-semibold small" for="modal_reason">
                                Reason for Status Transition
                            </label>
                            <input type="text" name="reason" id="modal_reason" class="form-control form-control-sm" placeholder="e.g. Terminal cleaning complete, linen changed, ready for admission.">
                        </div>

                        <div class="mb-0">
                            <label class="form-label text-navy fw-semibold small" for="modal_notes">
                                Detailed Nursing/Maintenance Notes
                            </label>
                            <textarea name="notes" id="modal_notes" class="form-control form-control-sm" rows="2" placeholder="Optional notes for shift handover..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-4">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-teal btn-sm">
                            <i class="bi bi-check-lg me-1"></i> Save Status Change
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Helper for Status Modal -->
    <script>
        function openStatusModal(bedId, bedNumber, currentStatus) {
            document.getElementById('modalBedNumber').textContent = bedNumber;
            document.getElementById('modal_new_status').value = currentStatus !== 'occupied' ? currentStatus : 'cleaning';
            document.getElementById('statusChangeForm').action = `{{ url('/portal/beds') }}/${bedId}/status`;
            
            const modal = new bootstrap.Modal(document.getElementById('statusChangeModal'));
            modal.show();
        }
    </script>
</x-layouts.portal>

