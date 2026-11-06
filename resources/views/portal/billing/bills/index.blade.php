<x-layouts.portal title="Invoices & Billing Ledger — Meghna Clinic">
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
                    <strong>Error occurred:</strong>
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
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none">Portal</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Billing & Invoices</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">Hospital Billing & Cashier Desk</h1>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success">Financial Ledger</span>
                </div>
                <p class="text-muted small mb-0">Generate patient invoices, calculate multi-item charges, apply authorized concessions, and print official money receipts.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portal.services.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-tags me-1"></i> Service Catalog
                </a>
                <a href="{{ route('portal.billing.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Generate New Invoice
                </a>
            </div>
        </div>

        {{-- Financial Metrics Row --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Total Invoiced</span>
                            <h3 class="fw-bold mb-0 text-navy mt-1">৳ {{ number_format($stats['total_billed'], 2) }}</h3>
                            <div class="small text-muted mt-1">{{ number_format($stats['total_invoices']) }} total invoices</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-receipt fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Collected Revenue</span>
                            <h3 class="fw-bold mb-0 text-success mt-1">৳ {{ number_format($stats['total_collected'], 2) }}</h3>
                            <div class="small text-muted mt-1">{{ number_format($stats['paid_count']) }} paid in full</div>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-cash-stack fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Outstanding Dues</span>
                            <h3 class="fw-bold mb-0 text-danger mt-1">৳ {{ number_format($stats['total_due'], 2) }}</h3>
                            <div class="small text-muted mt-1">{{ number_format($stats['unpaid_count'] + $stats['partial_count']) }} pending settlement</div>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                            <i class="bi bi-exclamation-octagon fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Unpaid Invoices</span>
                            <h3 class="fw-bold mb-0 text-warning mt-1">{{ number_format($stats['unpaid_count']) }}</h3>
                            <div class="small text-muted mt-1">{{ number_format($stats['partial_count']) }} partial payments</div>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters & Search Card --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <form action="{{ route('portal.billing.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Search invoice #, patient name, code, phone..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid Only</option>
                            <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial Dues</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid in Full</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From Date">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To Date">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('portal.billing.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Invoices Table Card --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Invoice # & Date</th>
                            <th>Patient Information</th>
                            <th>Episode / Source</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-end">Due Amount</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bills as $bill)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold font-monospace text-navy">
                                        <a href="{{ route('portal.billing.show', $bill) }}" class="text-decoration-none text-navy">
                                            {{ $bill->invoice_number }}
                                        </a>
                                    </div>
                                    <div class="small text-muted">
                                        {{ $bill->bill_date ? $bill->bill_date->format('d M Y, h:i A') : $bill->created_at->format('d M Y') }}
                                    </div>
                                </td>
                                <td>
                                    @if($bill->patient)
                                        <div class="fw-semibold text-dark">{{ $bill->patient->name }}</div>
                                        <div class="small text-muted font-monospace">
                                            {{ $bill->patient->patient_code }} • {{ $bill->patient->phone ?? 'N/A' }}
                                        </div>
                                    @else
                                        <span class="text-muted">Unlinked Patient</span>
                                    @endif
                                </td>
                                <td>
                                    @if($bill->admission)
                                        <span class="badge bg-purple bg-opacity-10 text-purple border" style="color: #6f42c1; border-color: #6f42c1;">
                                            <i class="bi bi-hospital me-1"></i> Inpatient Bed
                                        </span>
                                    @elseif($bill->visit)
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                            <i class="bi bi-ticket-detailed me-1"></i> OPD Visit
                                        </span>
                                    @else
                                        <span class="badge bg-light text-secondary border">Direct Service</span>
                                    @endif
                                </td>
                                <td class="text-end font-monospace text-muted">
                                    ৳ {{ number_format($bill->subtotal_amount, 2) }}
                                </td>
                                <td class="text-end font-monospace text-success">
                                    @if($bill->discount_amount > 0)
                                        -৳ {{ number_format($bill->discount_amount, 2) }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end font-monospace fw-bold text-navy">
                                    ৳ {{ number_format($bill->total_amount, 2) }}
                                </td>
                                <td class="text-end font-monospace fw-bold {{ $bill->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                                    ৳ {{ number_format($bill->due_amount, 2) }}
                                </td>
                                <td class="text-center">
                                    @if($bill->status === 'paid')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1">
                                            <i class="bi bi-check-circle me-1"></i> Paid
                                        </span>
                                    @elseif($bill->status === 'partial')
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-2 py-1">
                                            <i class="bi bi-hourglass-split me-1"></i> Partial
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1">
                                            <i class="bi bi-exclamation-circle me-1"></i> Unpaid
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('portal.billing.show', $bill) }}" class="btn btn-sm btn-outline-primary" title="View Dossier">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('portal.billing.print', $bill) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print Invoice / Money Receipt">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-receipt display-6 d-block mb-2 text-secondary"></i>
                                    <p class="mb-0 fw-semibold">No invoices found matching your filters.</p>
                                    <p class="small">Click "Generate New Invoice" to create an invoice for a patient.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($bills->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $bills->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.portal>

