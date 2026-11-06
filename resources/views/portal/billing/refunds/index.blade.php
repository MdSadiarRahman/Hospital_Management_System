<x-layouts.portal title="Refund Vouchers — Meghna Clinic">
    <div class="container-fluid py-4">

        {{-- Flash Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('portal.billing.index') }}" class="text-decoration-none">Billing</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Refund Vouchers</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">Refund Vouchers & Returns Ledger</h1>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Authorized Financial Reversals</span>
                </div>
                <p class="text-muted small mb-0">Audit and view all authorized patient payment refunds, cancellation vouchers, and credit reversals.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portal.payments.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-cash-coin me-1"></i> Payments Ledger
                </a>
                <a href="{{ route('portal.billing.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-receipt me-1"></i> Invoices
                </a>
            </div>
        </div>

        {{-- Metrics Row --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Total Refunded Amount</span>
                            <h3 class="fw-bold mb-0 text-danger mt-1">৳ {{ number_format($stats['total_refunded_amount'], 2) }}</h3>
                            <div class="small text-muted mt-1">Authorized clinical & administrative returns</div>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                            <i class="bi bi-arrow-counterclockwise fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Refund Vouchers Issued</span>
                            <h3 class="fw-bold mb-0 text-navy mt-1">{{ number_format($stats['total_refunds_count']) }}</h3>
                            <div class="small text-muted mt-1">Total reversal transactions</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-file-earmark-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters & Search Card --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <form action="{{ route('portal.refunds.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Search voucher #, TrxID, reason, invoice #..." value="{{ request('search') }}">
                        </div>
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
                        <a href="{{ route('portal.refunds.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Refunds Table Card --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Voucher # & Date</th>
                            <th>Original Payment Receipt</th>
                            <th>Invoice & Patient</th>
                            <th>Refund Method</th>
                            <th>Justification Reason</th>
                            <th>Approved By</th>
                            <th class="text-end">Amount (BDT)</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($refunds as $refund)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold font-monospace text-danger">
                                        {{ $refund->refund_voucher }}
                                    </div>
                                    <div class="small text-muted">
                                        {{ $refund->refunded_at->format('d M Y, h:i A') }}
                                    </div>
                                </td>
                                <td>
                                    @if($refund->payment)
                                        <a href="{{ route('portal.payments.show', $refund->payment) }}" class="font-monospace fw-semibold text-decoration-none text-navy">
                                            {{ $refund->payment->receipt_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($refund->bill)
                                        <div class="fw-semibold text-dark">{{ $refund->bill->patient->name ?? 'Patient' }}</div>
                                        <div class="small text-muted font-monospace">{{ $refund->bill->invoice_number }}</div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border text-capitalize">
                                        {{ str_replace('_', ' ', $refund->refund_method) }}
                                    </span>
                                    @if($refund->transaction_ref)
                                        <div class="small font-monospace text-muted mt-1">{{ $refund->transaction_ref }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="small text-dark text-truncate" style="max-width: 250px;" title="{{ $refund->reason }}">
                                        {{ $refund->reason }}
                                    </div>
                                </td>
                                <td class="small text-muted">
                                    {{ $refund->approvedBy->name ?? 'Admin' }}
                                </td>
                                <td class="text-end font-monospace fw-bold text-danger">
                                    ৳ {{ number_format($refund->amount, 2) }}
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('portal.refunds.voucher', $refund) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print Refund Voucher">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-arrow-counterclockwise display-6 d-block mb-2 text-secondary"></i>
                                    <p class="mb-0 fw-semibold">No refund vouchers found.</p>
                                    <p class="small">Refunds can be authorized from an individual payment transaction dossier.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($refunds->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $refunds->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.portal>

