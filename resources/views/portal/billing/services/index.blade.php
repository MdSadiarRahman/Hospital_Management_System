<x-layouts.portal title="Service Catalog & Tariffs — Meghna Clinic">
    <div class="container-fluid py-4">

        {{-- Flash Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <strong>Action blocked:</strong>
                </div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('portal.billing.index') }}" class="text-decoration-none">Billing</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Service Catalog</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">Service Catalog & Tariffs Master</h1>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">Configurable Pricing</span>
                </div>
                <p class="text-muted small mb-0">Manage hospital service rates, bed charges, diagnostic test prices, OT fees, and clinical tariffs for Meghna Clinic.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portal.billing.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-receipt me-1"></i> View Invoices
                </a>
                @if($canManage)
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createServiceModal">
                        <i class="bi bi-plus-circle me-1"></i> Add New Service
                    </button>
                @endif
            </div>
        </div>

        {{-- Price Integrity Notice --}}
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-start mb-4 rounded-3" role="alert">
            <i class="bi bi-shield-check fs-4 me-3 text-primary"></i>
            <div>
                <strong class="d-block text-dark">Historical Price Preservation Guarantee:</strong>
                <span class="small text-muted">
                    Modifying a tariff price here updates future invoices only. All existing invoices and previously billed line items permanently preserve their historical price snapshot at the exact time of creation.
                </span>
            </div>
        </div>

        {{-- Metrics Row --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Total Services</span>
                            <h3 class="fw-bold mb-0 text-navy mt-1">{{ number_format($stats['total_services']) }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-tags-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Active Services</span>
                            <h3 class="fw-bold mb-0 text-success mt-1">{{ number_format($stats['active_services']) }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Inactive Services</span>
                            <h3 class="fw-bold mb-0 text-secondary mt-1">{{ number_format($stats['inactive_services']) }}</h3>
                        </div>
                        <div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded-circle">
                            <i class="bi bi-pause-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Service Categories</span>
                            <h3 class="fw-bold mb-0 text-info mt-1">{{ number_format($stats['total_categories']) }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                            <i class="bi bi-folder-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters & Search Card --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <form action="{{ route('portal.services.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Search service name, code, description..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="category_id" class="form-select">
                            <option value="">All Service Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('portal.services.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Services Table Card --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Code</th>
                            <th>Service Name & Details</th>
                            <th>Category</th>
                            <th class="text-end">Standard Tariff (BDT)</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $service)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-light text-dark font-monospace border">{{ $service->code }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $service->name }}</div>
                                    @if($service->description)
                                        <div class="small text-muted text-truncate" style="max-width: 380px;">{{ $service->description }}</div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $catColor = match($service->category->code ?? '') {
                                            'CONSULTATION' => 'primary',
                                            'BED_CHARGES' => 'info',
                                            'LAB_TESTS' => 'success',
                                            'OPERATION_CHARGES' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $catColor }} bg-opacity-10 text-{{ $catColor }} border border-{{ $catColor }}">
                                        {{ $service->category->name ?? 'General' }}
                                    </span>
                                </td>
                                <td class="text-end font-monospace fw-bold text-navy">
                                    ৳ {{ number_format($service->price, 2) }}
                                </td>
                                <td class="text-center">
                                    @if($service->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        @if($canManage)
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editServiceModal{{ $service->id }}"
                                                title="Edit Service Tariff">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <form action="{{ route('portal.services.toggle', $service) }}" method="POST" class="d-inline" onsubmit="return confirm('Change status for {{ $service->name }}?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $service->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    title="{{ $service->is_active ? 'Deactivate Service' : 'Activate Service' }}">
                                                    <i class="bi {{ $service->is_active ? 'bi-pause-fill' : 'bi-play-fill' }}"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge bg-light text-muted">Read-only</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Edit Service Modal --}}
                            @if($canManage)
                                <div class="modal fade" id="editServiceModal{{ $service->id }}" tabindex="-1" aria-labelledby="editServiceLabel{{ $service->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('portal.services.update', $service) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold" id="editServiceLabel{{ $service->id }}">
                                                        <i class="bi bi-pencil-square text-primary me-1"></i> Edit Service Tariff
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-warning py-2 small mb-3">
                                                        <i class="bi bi-exclamation-circle-fill me-1"></i>
                                                        Code: <strong>{{ $service->code }}</strong> (Immutable to maintain ledger audit integrity).
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Category <span class="text-danger">*</span></label>
                                                        <select name="category_id" class="form-select" required>
                                                            @foreach($categories as $category)
                                                                <option value="{{ $category->id }}" {{ $service->category_id == $category->id ? 'selected' : '' }}>
                                                                    {{ $category->name }}
                                                                </option>
                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Service Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" class="form-control" value="{{ $service->name }}" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Standard Price / Tariff (BDT) <span class="text-danger">*</span></label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">৳</span>
                                                            <input type="number" step="0.01" min="0" name="price" class="form-control font-monospace" value="{{ $service->price }}" required>
                                                        </div>
                                                        <div class="form-text small">Use decimal precision (e.g. 500.00). Price changes are recorded in audit logs.</div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Description / Clinical Instructions</label>
                                                        <textarea name="description" class="form-control" rows="2">{{ $service->description }}</textarea>
                                                    </div>

                                                    <div class="form-check form-switch mb-2">
                                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editActive{{ $service->id }}" {{ $service->is_active ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-semibold small" for="editActive{{ $service->id }}">
                                                            Active for New Invoices
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="bi bi-save me-1"></i> Save Tariff Changes
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-tags display-6 d-block mb-2 text-secondary"></i>
                                    <p class="mb-0 fw-semibold">No services found matching your criteria.</p>
                                    <p class="small">Try resetting the search filters or add a new service to the catalog.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($services->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $services->links() }}
                </div>
            @endif
        </div>

    </div>

    {{-- Create Service Modal --}}
    @if($canManage)
        <div class="modal fade" id="createServiceModal" tabindex="-1" aria-labelledby="createServiceLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('portal.services.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="createServiceLabel">
                                <i class="bi bi-plus-circle text-primary me-1"></i> Register New Hospital Service
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Service Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category...</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Service Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Emergency Appendectomy OT Charge" value="{{ old('name') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Service Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control text-uppercase font-monospace" placeholder="e.g. SRV-OT-007" value="{{ old('code') }}" required>
                                <div class="form-text small">Unique alphanumeric identifier for invoice itemization.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Standard Price / Tariff (BDT) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" min="0" name="price" class="form-control font-monospace" placeholder="0.00" value="{{ old('price') }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Description / Clinical Notes</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Clinical scope, inclusions, or operational notes">{{ old('description') }}</textarea>
                            </div>

                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="newActive" checked>
                                <label class="form-check-label fw-semibold small" for="newActive">
                                    Active & Available for Billing
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Register Service
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</x-layouts.portal>

