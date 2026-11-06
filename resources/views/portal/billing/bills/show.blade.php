<x-layouts.portal title="Invoice {{ $bill->invoice_number }} — Meghna Clinic">
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
                        <li class="breadcrumb-item active" aria-current="page">{{ $bill->invoice_number }}</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">Invoice #{{ $bill->invoice_number }}</h1>
                    @if($bill->status === 'paid')
                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1">
                            <i class="bi bi-check-circle me-1"></i> Paid in Full
                        </span>
                    @elseif($bill->status === 'partial')
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-2 py-1">
                            <i class="bi bi-hourglass-split me-1"></i> Partial Due
                        </span>
                    @else
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1">
                            <i class="bi bi-exclamation-circle me-1"></i> Unpaid
                        </span>
                    @endif
                </div>
                <p class="text-muted small mb-0">Issued on {{ $bill->bill_date ? $bill->bill_date->format('d M Y, h:i A') : $bill->created_at->format('d M Y') }} by {{ $bill->createdBy->name ?? 'Staff Cashier' }}.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portal.billing.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Ledger
                </a>
                <a href="{{ route('portal.billing.print', $bill) }}" target="_blank" class="btn btn-outline-primary">
                    <i class="bi bi-printer me-1"></i> Print Invoice / Receipt
                </a>
                @if($bill->status !== 'paid')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Line Item
                    </button>
                @endif
            </div>
        </div>

        <div class="row g-4">
            {{-- Main Column: Patient Info & Line Items --}}
            <div class="col-lg-8">
                {{-- Patient & Episode Info Card --}}
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6 border-end">
                                <span class="text-muted small text-uppercase fw-semibold">Patient Information</span>
                                @if($bill->patient)
                                    <h5 class="fw-bold text-navy mt-1 mb-1">{{ $bill->patient->name }}</h5>
                                    <div class="small text-muted mb-1">
                                        <span class="font-monospace fw-semibold">{{ $bill->patient->patient_code }}</span> •
                                        {{ $bill->patient->formatted_age ?? 'Age N/A' }} •
                                        {{ ucfirst($bill->patient->gender ?? 'N/A') }}
                                    </div>
                                    <div class="small text-muted">
                                        <i class="bi bi-telephone me-1"></i> {{ $bill->patient->phone ?? 'No phone recorded' }}
                                    </div>
                                @else
                                    <p class="text-muted mb-0">No patient record linked.</p>
                                @endif
                            </div>

                            <div class="col-md-6 ps-md-4">
                                <span class="text-muted small text-uppercase fw-semibold">Encounter Details</span>
                                <div class="mt-1">
                                    @if($bill->admission)
                                        <div class="fw-semibold text-dark">
                                            <i class="bi bi-hospital text-primary me-1"></i> Inpatient Admission #{{ $bill->admission->id }}
                                        </div>
                                        <div class="small text-muted">
                                            Bed: <strong>{{ $bill->admission->currentBedAssignment?->bed?->bed_number ?? 'Assigned Ward' }}</strong>
                                            ({{ ucfirst($bill->admission->currentBedAssignment?->bed?->bed_type ?? 'Bed') }})
                                        </div>
                                    @elseif($bill->visit)
                                        <div class="fw-semibold text-dark">
                                            <i class="bi bi-ticket-detailed text-info me-1"></i> OPD Visit #{{ $bill->visit->id }}
                                        </div>
                                        <div class="small text-muted">Token: {{ $bill->visit->visit_number ?? 'OPD' }}</div>
                                    @else
                                        <div class="fw-semibold text-dark">Direct Clinical / Hospital Service</div>
                                        <div class="small text-muted">Direct Cashier Billing</div>
                                    @endif

                                    @if($bill->notes)
                                        <div class="small text-muted mt-2 p-2 bg-light rounded">
                                            <i class="bi bi-chat-left-text me-1"></i> {{ $bill->notes }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Line Items Table Card --}}
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-navy mb-0">Invoice Line Items</h5>
                            <span class="small text-muted">Historical unit rates are snapshot-preserved and immutable.</span>
                        </div>
                        <span class="badge bg-light text-dark border">{{ $bill->items->count() }} line items</span>
                    </div>
                    <div class="card-body p-4 pt-2">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-3">#</th>
                                        <th>Service / Item Description</th>
                                        <th>Type</th>
                                        <th class="text-end">Unit Price (BDT)</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Total Price (BDT)</th>
                                        @if($bill->status !== 'paid')
                                            <th class="text-end pe-3">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bill->items as $index => $item)
                                        <tr>
                                            <td class="ps-3 text-muted small">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $item->item_name }}</div>
                                                @if($item->service_code)
                                                    <span class="badge bg-light text-muted font-monospace border small">{{ $item->service_code }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $typeBadge = match($item->item_type) {
                                                        'consultation' => 'primary',
                                                        'bed' => 'info',
                                                        'lab' => 'success',
                                                        'surgery' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $typeBadge }} bg-opacity-10 text-{{ $typeBadge }} border border-{{ $typeBadge }} small">
                                                    {{ ucfirst($item->item_type) }}
                                                </span>
                                            </td>
                                            <td class="text-end font-monospace">
                                                ৳ {{ number_format($item->unit_price, 2) }}
                                            </td>
                                            <td class="text-center font-monospace">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="text-end font-monospace fw-bold text-navy">
                                                ৳ {{ number_format($item->total_price, 2) }}
                                            </td>
                                            @if($bill->status !== 'paid')
                                                <td class="text-end pe-3">
                                                    @if($bill->items->count() > 1)
                                                        <form action="{{ route('portal.billing.items.remove', [$bill, $item]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove {{ $item->item_name }} from invoice?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Remove Item">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-muted small" title="At least one item required">—</span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">No items in this invoice.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Financial Audit Trail Card --}}
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                        <h6 class="fw-bold text-navy mb-0">
                            <i class="bi bi-shield-check text-success me-1"></i> Financial Audit Trail
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
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bill->auditLogs as $log)
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
                                            <td colspan="4" class="text-muted text-center py-2">No audit entries yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Financial Balance & Concessions --}}
            <div class="col-lg-4">
                {{-- Financial Summary Card --}}
                <div class="card border-0 shadow-sm rounded-3 mb-4 sticky-top" style="top: 1.5rem;">
                    <div class="card-header bg-navy text-white pt-3 px-4 pb-3 rounded-top-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-wallet2 me-2"></i> Financial Statement
                        </h5>
                        <span class="small opacity-75">Meghna Clinic Billing Office</span>
                    </div>
                    <div class="card-body p-4">
                        {{-- Subtotal --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted fw-semibold">Gross Subtotal</span>
                            <span class="font-monospace fw-bold fs-5 text-dark">৳ {{ number_format($bill->subtotal_amount, 2) }}</span>
                        </div>

                        {{-- Concession / Discount --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="text-muted fw-semibold">Discount / Concession</span>
                                @if($bill->discount_authorized_by)
                                    <div class="small text-muted" style="font-size: 0.75rem;">
                                        Auth: <strong>{{ $bill->discountAuthorizedBy->name ?? 'Admin' }}</strong>
                                    </div>
                                    @if($bill->discount_reason)
                                        <div class="small text-muted fst-italic" style="font-size: 0.75rem;">
                                            "{{ $bill->discount_reason }}"
                                        </div>
                                    @endif
                                @endif
                            </div>
                            <span class="font-monospace fw-bold text-success fs-5">
                                @if($bill->discount_amount > 0)
                                    -৳ {{ number_format($bill->discount_amount, 2) }}
                                @else
                                    ৳ 0.00
                                @endif
                            </span>
                        </div>

                        @if($canDiscount && $bill->status !== 'paid')
                            <div class="mb-3 text-end">
                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#discountModal">
                                    <i class="bi bi-pencil me-1"></i> Apply / Modify Concession
                                </button>
                            </div>
                        @endif

                        <hr class="text-muted opacity-25 my-3">

                        {{-- Net Total Payable --}}
                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded-3">
                            <span class="fw-bold text-navy">Net Payable</span>
                            <span class="font-monospace fw-bold fs-4 text-primary">৳ {{ number_format($bill->total_amount, 2) }}</span>
                        </div>

                        {{-- Paid Amount --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted fw-semibold">Paid Amount</span>
                            <span class="font-monospace fw-bold text-success">৳ {{ number_format($bill->paid_amount, 2) }}</span>
                        </div>

                        {{-- Due Amount --}}
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fw-bold text-dark">Current Due Balance</span>
                            <span class="font-monospace fw-bold fs-5 {{ $bill->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                                ৳ {{ number_format($bill->due_amount, 2) }}
                            </span>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-grid gap-2">
                            <a href="{{ route('portal.billing.print', $bill) }}" target="_blank" class="btn btn-primary py-2 fw-semibold">
                                <i class="bi bi-printer me-1"></i> Print Official Invoice Receipt
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Apply Discount Modal --}}
    @if($canDiscount)
        <div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('portal.billing.discount.apply', $bill) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="discountModalLabel">
                                <i class="bi bi-percent text-success me-1"></i> Authorize Invoice Concession
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info py-2 small mb-3">
                                Current Invoice Subtotal: <strong>৳ {{ number_format($bill->subtotal_amount, 2) }}</strong>.
                                Discount cannot exceed subtotal.
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Concession Amount (BDT) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" min="0" max="{{ $bill->subtotal_amount }}" name="discount_amount" class="form-control font-monospace" value="{{ $bill->discount_amount }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Justification Reason <span class="text-danger">*</span></label>
                                <input type="text" name="discount_reason" class="form-control" placeholder="e.g. Director approval, Needy patient concession" value="{{ $bill->discount_reason }}" required>
                                <div class="form-text small">Authorizing user and timestamp will be permanently recorded in the financial audit log.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i> Authorize & Apply Discount
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Add Item Modal --}}
    @if($bill->status !== 'paid')
        <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('portal.billing.items.add', $bill) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="addItemModalLabel">
                                <i class="bi bi-cart-plus text-primary me-1"></i> Add Service to Invoice
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Select From Service Catalog</label>
                                <select name="service_id" id="modalServiceSelect" class="form-select mb-1">
                                    <option value="">-- Custom Non-Catalog Item --</option>
                                    @foreach($categories as $category)
                                        <optgroup label="{{ $category->name }}">
                                            @foreach($category->services as $srv)
                                                <option value="{{ $srv->id }}"
                                                    data-name="{{ $srv->name }}"
                                                    data-price="{{ $srv->price }}"
                                                    data-category="{{ $category->code }}">
                                                    [{{ $srv->code }}] {{ $srv->name }} (৳{{ number_format($srv->price, 2) }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Item / Service Name</label>
                                <input type="text" name="item_name" id="modalItemName" class="form-control" placeholder="Item description...">
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">Unit Price (BDT) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01" min="0" name="unit_price" id="modalUnitPrice" class="form-control font-monospace" placeholder="0.00" value="0.00" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" min="1" max="100" name="quantity" class="form-control" value="1" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i> Add to Invoice
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalSelect = document.getElementById('modalServiceSelect');
                const modalName = document.getElementById('modalItemName');
                const modalPrice = document.getElementById('modalUnitPrice');

                if (modalSelect) {
                    modalSelect.addEventListener('change', function () {
                        const opt = modalSelect.options[modalSelect.selectedIndex];
                        if (opt && opt.value) {
                            modalName.value = opt.getAttribute('data-name');
                            modalPrice.value = parseFloat(opt.getAttribute('data-price')).toFixed(2);
                        }
                    });
                }
            });
        </script>
    @endif
</x-layouts.portal>

