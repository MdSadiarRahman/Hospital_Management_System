<x-layouts.portal title="Money Receipt {{ $payment->receipt_number }} — Meghna Clinic">
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
                        <li class="breadcrumb-item"><a href="{{ route('portal.payments.index') }}" class="text-decoration-none">Payments</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $payment->receipt_number }}</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">Money Receipt #{{ $payment->receipt_number }}</h1>
                    @if($payment->status === 'success')
                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1">
                            <i class="bi bi-check-circle me-1"></i> Success
                        </span>
                    @elseif($payment->status === 'partially_refunded')
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-2 py-1">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Partially Refunded
                        </span>
                    @else
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1">
                            <i class="bi bi-x-circle me-1"></i> Fully Refunded
                        </span>
                    @endif
                </div>
                <p class="text-muted small mb-0">Collected on {{ $payment->payment_date ? $payment->payment_date->format('d M Y, h:i A') : $payment->created_at->format('d M Y') }} by {{ $payment->receivedBy->name ?? 'Cashier' }}.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portal.payments.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Ledger
                </a>
                <a href="{{ route('portal.payments.receipt', $payment) }}" target="_blank" class="btn btn-outline-primary">
                    <i class="bi bi-printer me-1"></i> Print Money Receipt
                </a>
                @if($canRefund && $payment->status !== 'refunded' && $payment->net_amount > 0)
                    <button type="button" class="btn btn-danger text-white" data-bs-toggle="modal" data-bs-target="#refundModal">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Authorize Refund
                    </button>
                @endif
            </div>
        </div>

        <div class="row g-4">
            {{-- Main Column: Payment & Invoice Details --}}
            <div class="col-lg-8">
                {{-- Transaction Info Card --}}
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <h5 class="fw-bold text-navy mb-0">
                            <i class="bi bi-cash-coin text-success me-2"></i> Payment Details
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-sm-6 col-md-3">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Collected Amount</span>
                                <h4 class="fw-bold font-monospace text-success mt-1 mb-0">৳ {{ number_format($payment->amount, 2) }}</h4>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Refunded Amount</span>
                                <h4 class="fw-bold font-monospace text-danger mt-1 mb-0">৳ {{ number_format($payment->refunded_amount, 2) }}</h4>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Net Balance Realized</span>
                                <h4 class="fw-bold font-monospace text-navy mt-1 mb-0">৳ {{ number_format($payment->net_amount, 2) }}</h4>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Payment Method</span>
                                <div class="mt-1">
                                    @php
                                        $methodBadge = match($payment->payment_method) {
                                            'cash' => 'success',
                                            'card' => 'primary',
                                            'mobile_banking' => 'warning',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $methodBadge }} bg-opacity-10 text-{{ $methodBadge }} border border-{{ $methodBadge }} fs-6">
                                        {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                    </span>
                                </div>
                            </div>

                            @if($payment->transaction_ref)
                                <div class="col-md-6">
                                    <span class="text-muted small text-uppercase fw-semibold d-block">Transaction Reference / TrxID</span>
                                    <span class="font-monospace fw-bold text-dark">{{ $payment->transaction_ref }}</span>
                                </div>
                            @endif

                            @if($payment->notes)
                                <div class="col-12">
                                    <span class="text-muted small text-uppercase fw-semibold d-block">Cashier Notes</span>
                                    <p class="text-muted small mb-0 p-2 bg-light rounded">{{ $payment->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Associated Invoice Details Card --}}
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-navy mb-0">
                            <i class="bi bi-receipt text-primary me-2"></i> Linked Hospital Invoice
                        </h5>
                        @if($payment->bill)
                            <a href="{{ route('portal.billing.show', $payment->bill) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-box-arrow-up-right me-1"></i> View Full Bill
                            </a>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        @if($payment->bill)
                            <div class="row g-3">
                                <div class="col-md-6 border-end">
                                    <span class="text-muted small text-uppercase fw-semibold">Patient Information</span>
                                    <h5 class="fw-bold text-navy mt-1 mb-1">{{ $payment->bill->patient->name ?? 'Walk-in' }}</h5>
                                    <div class="small text-muted font-monospace">
                                        {{ $payment->bill->patient->patient_code ?? 'N/A' }} • Phone: {{ $payment->bill->patient->phone ?? 'N/A' }}
                                    </div>
                                    <div class="small text-muted mt-1">
                                        {{ $payment->bill->patient->formatted_age ?? 'Age N/A' }} • {{ ucfirst($payment->bill->patient->gender ?? 'N/A') }}
                                    </div>
                                </div>
                                <div class="col-md-6 ps-md-4">
                                    <span class="text-muted small text-uppercase fw-semibold">Bill Financial Status</span>
                                    <div class="d-flex justify-content-between align-items-center mt-2 small">
                                        <span class="text-muted">Invoice No:</span>
                                        <span class="font-monospace fw-bold text-navy">{{ $payment->bill->invoice_number }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1 small">
                                        <span class="text-muted">Invoice Total:</span>
                                        <span class="font-monospace fw-bold">৳ {{ number_format($payment->bill->total_amount, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1 small">
                                        <span class="text-muted">Current Paid:</span>
                                        <span class="font-monospace fw-bold text-success">৳ {{ number_format($payment->bill->paid_amount, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1 small">
                                        <span class="text-muted">Remaining Due:</span>
                                        <span class="font-monospace fw-bold {{ $payment->bill->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                                            ৳ {{ number_format($payment->bill->due_amount, 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <p class="text-muted mb-0">No invoice linked to this payment.</p>
                        @endif
                    </div>
                </div>

                {{-- Refunds on this Payment Card --}}
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-navy mb-0">
                            <i class="bi bi-arrow-counterclockwise text-danger me-2"></i> Processed Refund Vouchers
                        </h5>
                        <span class="badge bg-light text-dark border">{{ $payment->refunds->count() }} refunds</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-3">Voucher #</th>
                                        <th>Date</th>
                                        <th>Method</th>
                                        <th>Reason</th>
                                        <th>Approved By</th>
                                        <th class="text-end">Amount (BDT)</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payment->refunds as $refund)
                                        <tr>
                                            <td class="ps-3 font-monospace fw-bold text-danger">{{ $refund->refund_voucher }}</td>
                                            <td class="small text-muted">{{ $refund->refunded_at->format('d M Y, h:i A') }}</td>
                                            <td class="small text-capitalize">{{ str_replace('_', ' ', $refund->refund_method) }}</td>
                                            <td class="small text-dark">{{ $refund->reason }}</td>
                                            <td class="small text-muted">{{ $refund->approvedBy->name ?? 'Admin' }}</td>
                                            <td class="text-end font-monospace fw-bold text-danger">৳ {{ number_format($refund->amount, 2) }}</td>
                                            <td class="text-end pe-3">
                                                <a href="{{ route('portal.refunds.voucher', $refund) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print Refund Voucher">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted small">No refunds have been processed on this receipt.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Financial Audit Log Card --}}
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                        <h6 class="fw-bold text-navy mb-0">
                            <i class="bi bi-shield-check text-success me-1"></i> Transaction Audit Trail
                        </h6>
                    </div>
                    <div class="card-body p-4 pt-2">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle small mb-0">
                                <thead class="text-muted">
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Action Event</th>
                                        <th>Actor</th>
                                        <th>Values</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payment->auditLogs as $log)
                                        <tr>
                                            <td class="text-muted font-monospace">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                            <td>
                                                <span class="badge bg-light text-dark font-monospace border">{{ $log->event }}</span>
                                            </td>
                                            <td>{{ $log->user->name ?? 'System' }}</td>
                                            <td class="text-muted font-monospace small">
                                                @if($log->new_values)
                                                    {{ json_encode($log->new_values) }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-muted text-center py-2">No audit entries.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Summary Card --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 1.5rem;">
                    <div class="card-header bg-navy text-white pt-3 px-4 pb-3 rounded-top-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-receipt me-2"></i> Cashier Settlement
                        </h5>
                        <span class="small opacity-75">Meghna Clinic Billing Desk</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted fw-semibold">Payment Amount</span>
                            <span class="font-monospace fw-bold text-success fs-5">৳ {{ number_format($payment->amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted fw-semibold">Refunded Amount</span>
                            <span class="font-monospace fw-bold text-danger fs-5">-৳ {{ number_format($payment->refunded_amount, 2) }}</span>
                        </div>
                        <hr class="text-muted opacity-25 my-3">
                        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded-3">
                            <span class="fw-bold text-navy">Net Realized</span>
                            <span class="font-monospace fw-bold fs-4 text-primary">৳ {{ number_format($payment->net_amount, 2) }}</span>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="{{ route('portal.payments.receipt', $payment) }}" target="_blank" class="btn btn-primary py-2 fw-semibold">
                                <i class="bi bi-printer me-1"></i> Print Money Receipt
                            </a>
                            @if($canRefund && $payment->status !== 'refunded' && $payment->net_amount > 0)
                                <button type="button" class="btn btn-outline-danger py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#refundModal">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Authorize Refund Voucher
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Authorize Refund Modal --}}
    @if($canRefund && $payment->status !== 'refunded' && $payment->net_amount > 0)
        <div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('portal.payments.refund', $payment) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="refundModalLabel">
                                <i class="bi bi-arrow-counterclockwise text-danger me-1"></i> Authorize Payment Refund
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="p-3 bg-light rounded-3 mb-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small d-block">Available Refundable Balance:</span>
                                    <h4 class="fw-bold text-success mb-0 font-monospace">৳ {{ number_format($payment->net_amount, 2) }}</h4>
                                </div>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                    Receipt #{{ $payment->receipt_number }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Refund Amount (BDT) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" min="0.01" max="{{ $payment->net_amount }}" name="amount" class="form-control font-monospace fs-5 fw-bold" value="{{ $payment->net_amount }}" required>
                                </div>
                                <div class="form-text small">Enter full or partial refund amount. Cannot exceed refundable balance.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Refund Method</label>
                                <select name="refund_method" class="form-select">
                                    <option value="cash" {{ $payment->payment_method === 'cash' ? 'selected' : '' }}>Cash (নগদ ফেরত)</option>
                                    <option value="mobile_banking" {{ $payment->payment_method === 'mobile_banking' ? 'selected' : '' }}>Mobile Banking (MFS Transfer)</option>
                                    <option value="card" {{ $payment->payment_method === 'card' ? 'selected' : '' }}>Card Reversal</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Refund Transaction Reference</label>
                                <input type="text" name="transaction_ref" class="form-control font-monospace" placeholder="e.g. bKash cash-out TrxID or voucher ref">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Mandatory Justification Reason <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control" rows="3" placeholder="Clinical justification or administrative reason for approving this refund..." required></textarea>
                                <div class="form-text small">Your name and timestamp will be permanently recorded in the financial audit log.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-check-circle me-1"></i> Authorize & Process Refund
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</x-layouts.portal>

