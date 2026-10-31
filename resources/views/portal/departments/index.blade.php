<x-layouts.portal role="admin" title="Department & Ward Management" activeNav="departments">
    <div class="container-fluid p-0">
        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <x-breadcrumb :items="['Administration' => url('/previews/admin'), 'Departments & Wards' => null]" class="mb-1" />
                <h1 class="h3 fw-bold text-navy mb-0">Department & Ward Management</h1>
                <p class="text-muted-hms mb-0" style="font-size: 0.875rem;">
                    Configure hospital clinical units, support departments, doctor assignments, and bed allocations.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                @can('create', App\Models\Department::class)
                    <a href="{{ route('portal.departments.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>Register Department</span>
                    </a>
                @endcan
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

        <!-- Summary KPI Bento Grid -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 border-primary">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Departments</span>
                            <h3 class="fw-bold text-navy mb-0 mt-1">{{ $totalDepartments }}</h3>
                            <span class="text-muted" style="font-size: 0.78rem;">Hospital Units</span>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-diagram-3 fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 border-teal">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Clinical Units</span>
                            <h3 class="fw-bold text-navy mb-0 mt-1">{{ $totalClinical }}</h3>
                            <span class="text-muted" style="font-size: 0.78rem;">Patient Care & OPD/IPD</span>
                        </div>
                        <div class="rounded-circle bg-teal bg-opacity-10 text-teal p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-hospital fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 border-info">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Configured Beds</span>
                            <h3 class="fw-bold text-navy mb-0 mt-1">{{ $totalBeds }}</h3>
                            <span class="text-muted" style="font-size: 0.78rem;">Inpatient Capacity</span>
                        </div>
                        <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-door-open fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 border-success">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Assigned Doctors</span>
                            <h3 class="fw-bold text-navy mb-0 mt-1">{{ $totalDoctors }}</h3>
                            <span class="text-muted" style="font-size: 0.78rem;">Consultants & Specialists</span>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-person-badge fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3 mb-4">
            <form action="{{ route('portal.departments.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" 
                               placeholder="Search department name, code, or specialty..." 
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-sm-6 col-md-3">
                    <select name="type" class="form-select bg-light">
                        <option value="">All Classification Types</option>
                        <option value="clinical" {{ request('type') === 'clinical' ? 'selected' : '' }}>Clinical Specialties Only</option>
                        <option value="non_clinical" {{ request('type') === 'non_clinical' ? 'selected' : '' }}>Support / Administrative</option>
                    </select>
                </div>

                <div class="col-sm-6 col-md-2">
                    <select name="status" class="form-select bg-light">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Units</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive / Closed</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-navy flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'type', 'status']))
                        <a href="{{ route('portal.departments.index') }}" class="btn btn-outline-secondary" title="Clear Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Department Listing Grid -->
        @if($departments->isEmpty())
            <div class="card border-0 shadow-sm rounded-3 p-5 text-center bg-white">
                <div class="py-4">
                    <div class="mb-3 text-muted">
                        <i class="bi bi-diagram-3 display-3 opacity-50"></i>
                    </div>
                    <h4 class="fw-bold text-navy">No Departments Found</h4>
                    <p class="text-muted mb-4" style="max-width: 450px; margin: 0 auto;">
                        @if(request()->anyFilled(['search', 'type', 'status']))
                            No departments matched your search or filter criteria. Try adjusting the query.
                        @else
                            No hospital departments are registered in the system yet.
                        @endif
                    </p>
                    @can('create', App\Models\Department::class)
                        <a href="{{ route('portal.departments.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Register First Department
                        </a>
                    @endcan
                </div>
            </div>
        @else
            <div class="row g-4">
                @foreach($departments as $dept)
                    @php
                        $bedCount = $dept->beds->count();
                        $occupiedBeds = $dept->beds->where('status', 'occupied')->count();
                        $availableBeds = $dept->beds->where('status', 'available')->count();
                        $doctorCount = $dept->staffProfiles->filter(fn($p) => $p->user && $p->user->hasRole('doctor'))->count();
                        $supportStaffCount = $dept->staffProfiles->reject(fn($p) => $p->user && $p->user->hasRole('doctor'))->count();
                    @endphp
                    <div class="col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white d-flex flex-column transition-hover">
                            <!-- Card Header -->
                            <div class="card-header bg-transparent border-bottom p-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-navy px-2.5 py-1.5 font-monospace fw-bold text-white rounded">
                                        {{ $dept->code }}
                                    </span>
                                    @if($dept->is_clinical)
                                        <span class="badge bg-teal-subtle text-teal border border-teal-subtle rounded-pill px-2.5 py-1 font-sans">
                                            <i class="bi bi-heart-pulse me-1"></i> Clinical
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 font-sans">
                                            <i class="bi bi-building me-1"></i> Support
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    @if($dept->is_active)
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

                            <!-- Card Body -->
                            <div class="card-body p-3 d-flex flex-column">
                                <h2 class="h5 fw-bold text-navy mb-2">
                                    <a href="{{ route('portal.departments.show', $dept) }}" class="text-navy text-decoration-none hover-teal">
                                        {{ $dept->name }}
                                    </a>
                                </h2>
                                <p class="text-muted mb-3 flex-grow-1" style="font-size: 0.85rem; line-height: 1.45;">
                                    {{ Str::limit($dept->description ?: 'No operational description recorded for this department.', 100) }}
                                </p>

                                <!-- Mini Metrics Bento -->
                                <div class="bg-light rounded-3 p-2.5 mb-3">
                                    <div class="row g-2 text-center" style="font-size: 0.8rem;">
                                        <div class="col-4 border-end">
                                            <span class="text-muted d-block text-uppercase" style="font-size: 0.68rem;">Doctors</span>
                                            <span class="fw-bold text-navy fs-6">{{ $doctorCount }}</span>
                                        </div>
                                        <div class="col-4 border-end">
                                            <span class="text-muted d-block text-uppercase" style="font-size: 0.68rem;">Nurses/Staff</span>
                                            <span class="fw-bold text-navy fs-6">{{ $supportStaffCount }}</span>
                                        </div>
                                        <div class="col-4">
                                            <span class="text-muted d-block text-uppercase" style="font-size: 0.68rem;">Beds</span>
                                            <span class="fw-bold text-navy fs-6">{{ $bedCount }}</span>
                                            @if($bedCount > 0)
                                                <small class="d-block text-success fw-semibold" style="font-size: 0.68rem;">{{ $availableBeds }} Free</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Card Actions -->
                                <div class="d-flex align-items-center justify-content-between pt-2 border-top gap-2">
                                    <a href="{{ route('portal.departments.show', $dept) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-eye"></i> Details & Beds
                                    </a>

                                    <div class="d-flex align-items-center gap-1">
                                        @can('update', $dept)
                                            <a href="{{ route('portal.departments.edit', $dept) }}" class="btn btn-sm btn-light text-navy" title="Edit Department">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <!-- Toggle Status Button -->
                                            <form action="{{ route('portal.departments.toggle-status', $dept) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-light {{ $dept->is_active ? 'text-warning' : 'text-success' }}" 
                                                        title="{{ $dept->is_active ? 'Deactivate Department' : 'Activate Department' }}"
                                                        onclick="return confirm('Are you sure you want to {{ $dept->is_active ? 'deactivate' : 'activate' }} the {{ $dept->name }} department?')">
                                                    <i class="bi {{ $dept->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.portal>
