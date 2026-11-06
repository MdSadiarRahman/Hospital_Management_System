<x-layouts.portal role="lab" title="Diagnostic Test Orders & Lab Workbench — Meghna Clinic" activeNav="lab-orders">
    <!-- Header -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 58px; height: 58px; font-size: 1.6rem;">
                        <i class="bi bi-clipboard2-pulse"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-navy mb-0">Diagnostic Laboratory Orders & Workbench</h4>
                        <p class="text-muted-hms small mb-0 mt-1">
                            Doctor test requisitions, specimen accessioning, internal lab processing, and external referral assignments
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('portal.lab-orders.create') }}" class="btn btn-sm btn-teal d-flex align-items-center gap-1 shadow-sm">
                        <i class="bi bi-plus-circle"></i>
                        <span>New Doctor Requisition</span>
                    </a>
                    <a href="{{ route('portal.lab-catalog.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                        <i class="bi bi-card-checklist"></i>
                        <span>Test Catalog</span>
                    </a>
                    <a href="{{ url('/previews/lab') }}" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
                        <i class="bi bi-speedometer2"></i>
                        <span>Lab Station</span>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Metric KPI Bento -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Total Diagnostic Orders" 
                value="{{ $statistics['total_orders'] }} Orders" 
                icon="clipboard2-check" 
                color="primary"
                trend="{{ $statistics['today_orders'] }} requisitions today"
                trendUp="true"
                subtitle="All recorded requisitions"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Awaiting Phlebotomy" 
                value="{{ $statistics['pending_sample'] }} Pending" 
                icon="eyedropper" 
                color="warning"
                trend="Sample collection needed"
                trendUp="false"
                subtitle="Specimen pending accession"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="In Processing / Analyzers" 
                value="{{ $statistics['processing'] }} In Progress" 
                icon="hourglass-split" 
                color="teal"
                trend="Internal & Reference Labs"
                trendUp="true"
                subtitle="Active bench diagnostics"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="External Send-Outs" 
                value="{{ $statistics['external_assigned'] }} Referred" 
                icon="buildings" 
                color="info"
                trend="{{ $statistics['completed'] }} completed orders"
                trendUp="true"
                subtitle="Partner lab assignments"
            />
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('portal.lab-orders.index') }}" class="row g-2 align-items-center">
                <!-- Search Term -->
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Search Orders</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="Order #, patient name, doctor..." value="{{ $filters['q'] ?? '' }}">
                    </div>
                </div>

                <!-- Processing Status -->
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Processing Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending Collection</option>
                        <option value="sample_collected" {{ ($filters['status'] ?? '') === 'sample_collected' ? 'selected' : '' }}>Sample Collected</option>
                        <option value="processing" {{ ($filters['status'] ?? '') === 'processing' ? 'selected' : '' }}>In Processing</option>
                        <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Priority -->
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Priority</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All Priorities</option>
                        <option value="routine" {{ ($filters['priority'] ?? '') === 'routine' ? 'selected' : '' }}>Routine</option>
                        <option value="urgent" {{ ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="stat" {{ ($filters['priority'] ?? '') === 'stat' ? 'selected' : '' }}>STAT (Emergency)</option>
                    </select>
                </div>

                <!-- Source (Internal vs External) -->
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Source / Lab</label>
                    <select name="source" class="form-select form-select-sm">
                        <option value="">All Sources</option>
                        <option value="internal" {{ ($filters['source'] ?? '') === 'internal' ? 'selected' : '' }}>In-House Lab</option>
                        <option value="external" {{ ($filters['source'] ?? '') === 'external' ? 'selected' : '' }}>External Send-Out</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-3 d-flex align-items-end gap-2 pt-3 pt-md-0">
                    <button type="submit" class="btn btn-sm btn-navy d-flex align-items-center gap-1">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    @if(!empty(array_filter($filters)))
                        <a href="{{ route('portal.lab-orders.index') }}" class="btn btn-sm btn-outline-secondary">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table List -->
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-list-check text-teal"></i> Diagnostic Requisition Queue
            </h6>
            <span class="badge bg-light text-navy border">
                Showing {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} orders
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Order ID & Date</th>
                        <th>Patient Details</th>
                        <th>Prescribing Doctor</th>
                        <th>Ordered Tests</th>
                        <th>Source / Assignment</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th class="text-end">Tariff</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <!-- Order ID & Date -->
                            <td class="ps-4">
                                <a href="{{ route('portal.lab-orders.show', $order->id) }}" class="fw-bold text-navy text-decoration-none font-monospace">
                                    {{ $order->order_number }}
                                </a>
                                <div class="small text-muted">
                                    {{ $order->created_at->format('d M Y, h:i A') }}
                                </div>
                            </td>

                            <!-- Patient -->
                            <td>
                                @if($order->patient)
                                    <div class="fw-semibold text-dark">{{ $order->patient->name }}</div>
                                    <div class="small text-muted font-monospace">
                                        {{ $order->patient->patient_code }} &bull; {{ $order->patient->age_gender ?? 'N/A' }}
                                    </div>
                                @else
                                    <span class="text-muted">Unlinked Patient</span>
                                @endif
                            </td>

                            <!-- Doctor -->
                            <td>
                                @if($order->doctor)
                                    <div class="fw-semibold text-navy">{{ $order->doctor->name }}</div>
                                    <div class="small text-muted">
                                        {{ $order->doctor->staffProfile?->specialization ?? 'Medical Officer' }}
                                    </div>
                                @else
                                    <span class="text-muted">Direct Lab Order</span>
                                @endif
                            </td>

                            <!-- Tests -->
                            <td>
                                <div class="d-flex flex-wrap gap-1 align-items-center" style="max-width: 260px;">
                                    @foreach($order->items->take(3) as $item)
                                        <span class="badge bg-light text-dark border text-truncate" style="max-width: 140px;" title="{{ $item->test_name }}">
                                            {{ $item->test_name }}
                                        </span>
                                    @endforeach
                                    @if($order->items->count() > 3)
                                        <span class="badge bg-secondary-subtle text-secondary small">
                                            +{{ $order->items->count() - 3 }} more
                                        </span>
                                    @endif
                                </div>
                                <div class="small text-muted mt-1">
                                    {{ $order->items->count() }} {{ Str::plural('test', $order->items->count()) }}
                                </div>
                            </td>

                            <!-- Source & Partner -->
                            <td>
                                @if($order->source === 'internal')
                                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle">
                                        <i class="bi bi-hospital me-1"></i> In-House Lab
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle">
                                        <i class="bi bi-buildings me-1"></i> External
                                    </span>
                                    @if($order->labPartner)
                                        <div class="small text-muted mt-1 text-truncate" style="max-width: 150px;" title="{{ $order->labPartner->name }}">
                                            {{ $order->labPartner->name }}
                                        </div>
                                    @endif
                                @endif
                            </td>

                            <!-- Priority -->
                            <td>
                                @if($order->priority === 'stat')
                                    <span class="badge bg-danger text-white">
                                        <i class="bi bi-lightning-fill"></i> STAT
                                    </span>
                                @elseif($order->priority === 'urgent')
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-exclamation-circle-fill"></i> Urgent
                                    </span>
                                @else
                                    <span class="badge bg-light text-secondary border">
                                        Routine
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td>
                                <span class="badge {{ $order->status_badge_class }}">
                                    {{ $order->status_label }}
                                </span>
                                @if($order->samples->isNotEmpty())
                                    <div class="small text-muted font-monospace mt-1">
                                        <i class="bi bi-upc-scan text-teal"></i> {{ $order->samples->first()->barcode }}
                                    </div>
                                @endif
                            </td>

                            <!-- Tariff -->
                            <td class="text-end fw-bold text-navy">
                                {{ $order->formatted_total_charge }}
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('portal.lab-orders.show', $order->id) }}">
                                                <i class="bi bi-folder2-open text-primary me-2"></i> View Order Dossier
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('portal.lab-orders.print-slip', $order->id) }}" target="_blank">
                                                <i class="bi bi-printer text-navy me-2"></i> Print Requisition Slip
                                            </a>
                                        </li>
                                        @if(in_array($order->status, ['pending', 'sample_collected']))
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-teal fw-semibold" href="{{ route('portal.lab-orders.show', $order->id) }}#specimen-section">
                                                    <i class="bi bi-eyedropper text-teal me-2"></i> Collect Specimen
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted-hms"></i>
                                    <h6 class="fw-bold">No Diagnostic Test Orders Found</h6>
                                    <p class="small mb-3">No laboratory requisitions matched your selected filters or search terms.</p>
                                    <a href="{{ route('portal.lab-orders.create') }}" class="btn btn-sm btn-teal">
                                        <i class="bi bi-plus-circle me-1"></i> Create First Diagnostic Order
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $orders->withQueryString()->links() }}
            </div>
        @endif
    </div>
</x-layouts.portal>

