<x-layouts.portal title="Payments & Money Receipts — Meghna Clinic">
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
                        <li class="breadcrumb-item active" aria-current="page">Payments & Receipts</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">Payments Ledger & Money Receipts</h1>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success">Cashier Ledger</span>
                </div>
                <p class="text-muted small mb-0">Track all patient cash, card, and mobile banking payment transactions, reconcile receipts, and audit collections.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portal.billing.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-receipt me-1"></i> Invoices Ledger
                </a>
                <a href="{{ route('portal.refunds.index') }}" class="btn btn-outline-danger">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Refund Vouchers
                </a>
            </div>
        </div>

        {{-- Financial Metrics Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Gross Collected</span>
                            <h3 class="fw-bold mb-0 text-success mt-1">৳ {{ number_format($stats['total_collected'], 2) }}</h3>
                            <div class="small text-muted mt-1">{{ number_format($stats['total_payments_count']) }} receipts issued</div>
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
                            <span class="text-muted small text-uppercase fw-semibold">Net Realized</span>
                            <h3 class="fw-bold mb-0 text-navy mt-1">৳ {{ number_format($stats['net_revenue'], 2) }}</h3>
                            <div class="small text-muted mt-1">After approved refunds</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Total Refunded</span>
                            <h3 class="fw-bold mb-0 text-danger mt-1">৳ {{ number_format($stats['total_refunded'], 2) }}</h3>
                            <div class="small text-muted mt-1">Authorized concession returns</div>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                            <i class="bi bi-arrow-counterclockwise fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Payment Methods</span>
                            <div class="small mt-1">
                                <span class="badge bg-light text-dark border">Cash: ৳{{ number_format($stats['cash_total'], 0) }}</span>
                                <span class="badge bg-light text-dark border">MFS: ৳{{ number_format($stats['mfs_total'], 0) }}</span>
                            </div>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                            <i class="bi bi-credit-card fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters & Search Card --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <form action="{{ route('portal.payments.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Receipt #, TrxID, invoice #, patient name..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="payment_method" class="form-select">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card (POS)</option>
                            <option value="mobile_banking" {{ request('payment_method') === 'mobile_banking' ? 'selected' : '' }}>Mobile Banking (MFS)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Successful</option>
                            <option value="partially_refunded" {{ request('status') === 'partially_refunded' ? 'selected' : '' }}>Partially Refunded</option>
                            <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Fully Refunded</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('portal.payments.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Payments Table Card --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Receipt # & Date</th>
                            <th>Invoice Reference</th>
                            <th>Patient Information</th>
                            <th>Method & Reference</th>
                            <th>Cashier</th>
                            <th class="text-end">Collected (BDT)</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold font-monospace text-navy">
                                        <a href="{{ route('portal.payments.show', $payment) }}" class="text-decoration-none text-navy">
                                            {{ $payment->receipt_number }}
                                        </a>
                                    </div>
                                    <div class="small text-muted">
                                        {{ $payment->payment_date ? $payment->payment_date->format('d M Y, h:i A') : $payment->created_at->format('d M Y') }}
                                    </div>
                                </td>
                                <td>
                                    @if($payment->bill)
                                        <a href="{{ route('portal.billing.show', $payment->bill) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                            {{ $payment->bill->invoice_number }}
                                        </a>
                                        <div class="small text-muted">
                                            Total: ৳{{ number_format($payment->bill->total_amount, 2) }}
                                        </div>
                                    @else
                                        <span class="text-muted">Unlinked Invoice</span>
                                    @endif
                                </td>
                                <td>
                                    @if($payment->bill && $payment->bill->patient)
                                        <div class="fw-semibold text-dark">{{ $payment->bill->patient->name }}</div>
                                        <div class="small text-muted font-monospace">
                                            {{ $payment->bill->patient->patient_code }} • {{ $payment->bill->patient->phone ?? 'N/A' }}
                                        </div>
                                    @else
                                        <span class="text-muted">Walk-in Patient</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $mColor = match($payment->payment_method) {
                                            'cash' => 'success',
                                            'card' => 'primary',
                                            'mobile_banking' => 'warning',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $mColor }} bg-opacity-10 text-{{ $mColor }} border border-{{ $mColor }}">
                                        {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                    </span>
                                    @if($payment->transaction_ref)
                                        <div class="small font-monospace text-muted mt-1">{{ $payment->transaction_ref }}</div>
                                    @endif
                                </td>
                                <td class="small text-dark">{{ $payment->receivedBy->name ?? 'Cashier' }}</td>
                                <td class="text-end font-monospace">
                                    <div class="fw-bold text-success fs-6">৳ {{ number_format($payment->amount, 2) }}</div>
                                    @if($payment->refunded_amount > 0)
                                        <div class="small text-danger">Refunded: -৳ {{ number_format($payment->refunded_amount, 2) }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($payment->status === 'success')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1">
                                            <i class="bi bi-check-circle me-1"></i> Success
                                        </span>
                                    @elseif($payment->status === 'partially_refunded')
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-2 py-1">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Partial Refund
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1">
                                            <i class="bi bi-x-circle me-1"></i> Refunded
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('portal.payments.show', $payment) }}" class="btn btn-sm btn-outline-primary" title="View Transaction Dossier">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('portal.payments.receipt', $payment) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print Money Receipt">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-cash-stack display-6 d-block mb-2 text-secondary"></i>
                                    <p class="mb-0 fw-semibold">No payment records found matching your filters.</p>
                                    <p class="small">Open an invoice to record a new patient payment.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.portal>

