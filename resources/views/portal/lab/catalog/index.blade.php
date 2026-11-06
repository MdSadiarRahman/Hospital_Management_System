<x-layouts.portal role="lab" title="Diagnostic Laboratory Test Catalog — Meghna Clinic" activeNav="lab-catalog">
    <!-- Header -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 58px; height: 58px; font-size: 1.6rem;">
                        <i class="bi bi-card-checklist"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-navy mb-0">Diagnostic Laboratory Test Catalog</h4>
                        <p class="text-muted-hms small mb-0 mt-1">
                            Configurable diagnostic test master, specimen requirements, turnaround times, internal/external routing, and official tariffs
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('portal.lab-catalog.create') }}" class="btn btn-sm btn-teal d-flex align-items-center gap-1 shadow-sm">
                        <i class="bi bi-plus-circle"></i>
                        <span>Add New Diagnostic Test</span>
                    </a>
                    <a href="{{ url('/previews/lab') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Lab Workstation
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Metric KPI Bento -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Total Catalog Tests" 
                value="{{ $statistics['total_tests'] }} Tests" 
                icon="journal-medical" 
                color="primary"
                trend="{{ $statistics['categories_count'] }} Clinical Disciplines"
                trendUp="true"
                subtitle="Configured diagnostic master"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Active Available Tests" 
                value="{{ $statistics['active_tests'] }} Active" 
                icon="check2-circle" 
                color="success"
                trend="{{ $statistics['inactive_tests'] }} Inactive"
                trendUp="true"
                subtitle="Available for clinical order"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="In-House / Internal Lab" 
                value="{{ $statistics['internal_tests'] }} Tests" 
                icon="hospital" 
                color="teal"
                trend="Processed in Meghna Lab"
                trendUp="true"
                subtitle="On-site rapid processing"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Outsourced / External" 
                value="{{ $statistics['external_tests'] }} Tests" 
                icon="buildings" 
                color="warning"
                trend="{{ $statistics['partners_count'] }} Partner Reference Labs"
                trendUp="false"
                subtitle="Specialized send-out tests"
            />
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('portal.lab-catalog.index') }}" class="row g-2 align-items-center">
                <!-- Search Term -->
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Search Tests</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="Search by test name, code, sample type..." value="{{ $filters['q'] ?? '' }}">
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Category / Discipline</label>
                    <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" @selected(($filters['category'] ?? '') === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Source / Availability Filter -->
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Availability</label>
                    <select name="source" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all">All Locations</option>
                        <option value="internal" @selected(($filters['source'] ?? '') === 'internal')>Internal Lab Only</option>
                        <option value="external" @selected(($filters['source'] ?? '') === 'external')>External / Outsourced</option>
                        <option value="both" @selected(($filters['source'] ?? '') === 'both')>Both Internal & External</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all">All Statuses</option>
                        <option value="1" @selected(($filters['status'] ?? '') === '1')>Active Only</option>
                        <option value="0" @selected(($filters['status'] ?? '') === '0')>Inactive Only</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-1 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-sm btn-teal w-100" title="Apply Filter">
                        <i class="bi bi-funnel"></i>
                    </button>
                    <a href="{{ route('portal.lab-catalog.index') }}" class="btn btn-sm btn-outline-secondary w-100" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Test Catalog Table -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
            <div>
                <h5 class="fw-bold text-navy mb-0">Diagnostic Tests Master List</h5>
                <span class="text-muted small">Showing {{ $tests->total() }} diagnostic tests registered in clinical catalog</span>
            </div>
            <span class="badge bg-light text-navy border px-3 py-2">
                <i class="bi bi-shield-check text-teal me-1"></i> Unique Code Enforcement & Tarif Control
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Code</th>
                        <th>Test Name & Discipline</th>
                        <th>Sample Type</th>
                        <th>Turnaround</th>
                        <th>Availability</th>
                        <th class="text-end">Standard Charge</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tests as $test)
                        <tr class="{{ !$test->is_active ? 'opacity-75 bg-light-subtle' : '' }}">
                            <!-- Test Code -->
                            <td class="ps-4">
                                <span class="badge bg-navy font-monospace px-2 py-1 fs-6">
                                    {{ $test->code }}
                                </span>
                            </td>

                            <!-- Test Name & Category -->
                            <td>
                                <a href="{{ route('portal.lab-catalog.show', $test->id) }}" class="fw-bold text-teal text-decoration-none d-block">
                                    {{ $test->name }}
                                </a>
                                <div class="small text-muted mt-1">
                                    <span class="badge bg-light text-secondary border me-1">{{ $test->category }}</span>
                                    @if($test->description)
                                        <span class="text-truncate d-inline-block align-middle" style="max-width: 250px;" title="{{ $test->description }}">
                                            &bull; {{ $test->description }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Sample Type -->
                            <td>
                                <span class="badge bg-light-subtle text-dark border">
                                    <i class="bi bi-eyedropper text-danger me-1"></i>{{ $test->sample_type ?? 'Whole Blood' }}
                                </span>
                            </td>

                            <!-- Processing Time -->
                            <td>
                                <div class="small text-navy fw-semibold">
                                    <i class="bi bi-clock me-1 text-muted"></i>{{ $test->turnaround_time ?? '4 hours' }}
                                </div>
                            </td>

                            <!-- Availability / Routing -->
                            <td>
                                <span class="badge bg-{{ $test->availability_badge_class }} px-2 py-1">
                                    {{ $test->availability_label }}
                                </span>
                                @if($test->labPartner)
                                    <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                        via {{ $test->labPartner->name }}
                                    </div>
                                @endif
                            </td>

                            <!-- Standard Charge -->
                            <td class="text-end">
                                <span class="fw-bold text-navy fs-6 tabular-nums">
                                    {{ $test->formatted_price }}
                                </span>
                            </td>

                            <!-- Active / Inactive Status -->
                            <td class="text-center">
                                <span class="badge bg-{{ $test->status_badge_class }} px-2 py-1">
                                    {{ $test->status_label }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('portal.lab-catalog.show', $test->id) }}" class="btn btn-outline-teal" title="View Test Specifications">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('portal.lab-catalog.edit', $test->id) }}" class="btn btn-outline-secondary" title="Edit Configuration">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('portal.lab-catalog.toggle-status', $test->id) }}" class="d-inline" onsubmit="return confirm('Change status for {{ $test->name }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-{{ $test->is_active ? 'warning' : 'success' }}" title="{{ $test->is_active ? 'Deactivate Test' : 'Activate Test' }}">
                                            <i class="bi bi-{{ $test->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-card-checklist fs-1 d-block mb-2 text-secondary"></i>
                                    <p class="mb-1 fw-bold">No Diagnostic Tests Found</p>
                                    <small>Try adjusting your search criteria or configure a new diagnostic test above.</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tests->hasPages())
            <div class="card-footer bg-white border-top py-3 px-4">
                {{ $tests->links() }}
            </div>
        @endif
    </div>
</x-layouts.portal>

