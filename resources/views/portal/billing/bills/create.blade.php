<x-layouts.portal title="Generate Patient Invoice — Meghna Clinic">
    <div class="container-fluid py-4">

        {{-- Flash Alerts --}}
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <strong>Please correct the following errors:</strong>
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
                        <li class="breadcrumb-item active" aria-current="page">New Invoice</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold text-navy mb-0">Generate Patient Invoice</h1>
                <p class="text-muted small mb-0">Assemble multiple hospital services, apply authorized clinical concessions, and issue official clinic bill.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portal.billing.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Invoices
                </a>
            </div>
        </div>

        <form action="{{ route('portal.billing.store') }}" method="POST" id="billingForm">
            @csrf

            <div class="row g-4">
                {{-- Left Column: Patient & Line Items --}}
                <div class="col-lg-8">
                    {{-- Patient & Episode Card --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <h5 class="fw-bold text-navy mb-1">
                                <i class="bi bi-person-badge text-primary me-2"></i> Patient & Encounter Details
                            </h5>
                            <p class="text-muted small mb-0">Select the registered patient and link to an existing consultation or admission if applicable.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Registered Patient <span class="text-danger">*</span></label>
                                    @if($selectedPatient)
                                        <input type="hidden" name="patient_id" value="{{ $selectedPatient->id }}">
                                        <div class="p-2 border rounded bg-light">
                                            <div class="fw-bold text-navy">{{ $selectedPatient->name }}</div>
                                            <div class="small text-muted font-monospace">{{ $selectedPatient->patient_code }} • Phone: {{ $selectedPatient->phone ?? 'N/A' }}</div>
                                        </div>
                                    @else
                                        <select name="patient_id" class="form-select" required id="patientSelect">
                                            <option value="">Select registered patient...</option>
                                            @foreach(\App\Models\Patient::latest('id')->take(100)->get() as $p)
                                                <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                                                    {{ $p->name }} ({{ $p->patient_code }}) - {{ $p->phone }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Invoice Date <span class="text-danger">*</span></label>
                                    <input type="date" name="bill_date" class="form-control" value="{{ old('bill_date', date('Y-m-d')) }}" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Linked Admission / Bed</label>
                                    @if($selectedAdmission)
                                        <input type="hidden" name="admission_id" value="{{ $selectedAdmission->id }}">
                                        <div class="p-2 border rounded bg-light small">
                                            <span class="badge bg-purple text-white">IPD</span> Bed: {{ $selectedAdmission->currentBedAssignment?->bed?->bed_number ?? 'Bed assigned' }}
                                        </div>
                                    @else
                                        <input type="number" name="admission_id" class="form-control" placeholder="Admission ID (optional)" value="{{ old('admission_id') }}">
                                    @endif
                                </div>

                                @if($selectedVisit)
                                    <input type="hidden" name="visit_id" value="{{ $selectedVisit->id }}">
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Bill Items Card --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="fw-bold text-navy mb-1">
                                    <i class="bi bi-cart-check text-primary me-2"></i> Billable Services & Items
                                </h5>
                                <p class="text-muted small mb-0">Select from pre-configured hospital tariffs or enter custom item charges.</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addRowBtn">
                                <i class="bi bi-plus-lg me-1"></i> Add Another Item
                            </button>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table align-middle" id="itemsTable">
                                    <thead class="table-light text-muted small text-uppercase">
                                        <tr>
                                            <th style="width: 32%;">Service / Item Name</th>
                                            <th style="width: 20%;">Category / Type</th>
                                            <th style="width: 18%;" class="text-end">Unit Tariff (BDT)</th>
                                            <th style="width: 12%;" class="text-center">Qty</th>
                                            <th style="width: 14%;" class="text-end">Total (BDT)</th>
                                            <th style="width: 4%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                        {{-- Row 0 --}}
                                        <tr class="item-row">
                                            <td>
                                                <select name="items[0][service_id]" class="form-select form-select-sm service-select mb-1">
                                                    <option value="">-- Choose from Catalog --</option>
                                                    @foreach($categories as $category)
                                                        <optgroup label="{{ $category->name }}">
                                                            @foreach($category->services as $srv)
                                                                <option value="{{ $srv->id }}"
                                                                    data-name="{{ $srv->name }}"
                                                                    data-code="{{ $srv->code }}"
                                                                    data-price="{{ $srv->price }}"
                                                                    data-category="{{ $category->code }}">
                                                                    [{{ $srv->code }}] {{ $srv->name }} (৳{{ number_format($srv->price, 2) }})
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endforeach
                                                </select>
                                                <input type="text" name="items[0][item_name]" class="form-control form-control-sm item-name-input" placeholder="Or custom item description..." required>
                                            </td>
                                            <td>
                                                <select name="items[0][item_type]" class="form-select form-select-sm item-type-select">
                                                    <option value="service">Other Service</option>
                                                    <option value="consultation">Consultation</option>
                                                    <option value="bed">Bed Charge</option>
                                                    <option value="lab">Lab Test</option>
                                                    <option value="surgery">OT / Surgery</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" name="items[0][unit_price]" class="form-control form-control-sm text-end font-monospace unit-price-input" placeholder="0.00" value="0.00" required>
                                            </td>
                                            <td>
                                                <input type="number" min="1" max="100" name="items[0][quantity]" class="form-control form-control-sm text-center quantity-input" value="1" required>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm text-end font-monospace fw-bold row-total bg-light" value="0.00" readonly>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-link text-danger remove-row-btn p-0" title="Remove row">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Invoice Notes --}}
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-4">
                            <label class="form-label fw-semibold small">Invoice Remarks / Billing Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes for cashier or patient receipt (e.g. Bed stay from 01/11 to 04/11, Dr. prescription ref)...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Financial Calculation Summary --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 1.5rem;">
                        <div class="card-header bg-navy text-white pt-3 px-4 pb-3 rounded-top-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-calculator me-2"></i> Financial Summary
                            </h5>
                            <span class="small opacity-75">Automated decimal calculation</span>
                        </div>
                        <div class="card-body p-4">
                            {{-- Subtotal --}}
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted fw-semibold">Subtotal Amount</span>
                                <span class="font-monospace fw-bold fs-5 text-dark" id="displaySubtotal">৳ 0.00</span>
                            </div>

                            <hr class="text-muted opacity-25 my-3">

                            {{-- Discount / Concession --}}
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-semibold text-muted small mb-0">Authorized Discount (BDT)</label>
                                    @if($canDiscount)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success small">Authorized</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary small">Locked</span>
                                    @endif
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">৳</span>
                                    <input type="number" step="0.01" min="0" name="discount_amount" id="discountInput"
                                        class="form-control font-monospace"
                                        value="{{ old('discount_amount', '0.00') }}"
                                        {{ $canDiscount ? '' : 'readonly' }}>
                                </div>
                                @if(!$canDiscount)
                                    <div class="form-text small text-muted">Special discount approval requires administrator authorization.</div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted small mb-1">Discount Justification Reason</label>
                                <input type="text" name="discount_reason" id="discountReasonInput"
                                    class="form-control form-control-sm"
                                    placeholder="e.g. Needy patient concession, Director approval"
                                    value="{{ old('discount_reason') }}"
                                    {{ $canDiscount ? '' : 'readonly' }}>
                            </div>

                            <hr class="text-muted opacity-25 my-3">

                            {{-- Net Total --}}
                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded-3">
                                <span class="fw-bold text-navy">Net Total Payable</span>
                                <span class="font-monospace fw-bold fs-4 text-primary" id="displayTotal">৳ 0.00</span>
                            </div>

                            {{-- Due Amount --}}
                            <div class="d-flex justify-content-between align-items-center mb-4 px-1">
                                <span class="text-muted small">Initial Due Amount</span>
                                <span class="font-monospace fw-bold text-danger" id="displayDue">৳ 0.00</span>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                <i class="bi bi-check2-circle me-1"></i> Issue Patient Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>

    {{-- Dynamic Calculation Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let rowIndex = 1;
            const itemsTableBody = document.getElementById('itemsTableBody');
            const addRowBtn = document.getElementById('addRowBtn');
            const discountInput = document.getElementById('discountInput');
            const displaySubtotal = document.getElementById('displaySubtotal');
            const displayTotal = document.getElementById('displayTotal');
            const displayDue = document.getElementById('displayDue');

            function calculateTotals() {
                let subtotal = 0.00;
                const rows = itemsTableBody.querySelectorAll('.item-row');

                rows.forEach(row => {
                    const unitPriceInput = row.querySelector('.unit-price-input');
                    const qtyInput = row.querySelector('.quantity-input');
                    const rowTotalInput = row.querySelector('.row-total');

                    const price = parseFloat(unitPriceInput.value) || 0;
                    const qty = parseInt(qtyInput.value) || 1;
                    const total = price * qty;

                    rowTotalInput.value = total.toFixed(2);
                    subtotal += total;
                });

                let discount = parseFloat(discountInput.value) || 0;
                if (discount > subtotal) {
                    discount = subtotal;
                    discountInput.value = subtotal.toFixed(2);
                }

                const netTotal = Math.max(0, subtotal - discount);

                displaySubtotal.textContent = '৳ ' + subtotal.toFixed(2);
                displayTotal.textContent = '৳ ' + netTotal.toFixed(2);
                displayDue.textContent = '৳ ' + netTotal.toFixed(2);
            }

            function attachRowEvents(row) {
                const serviceSelect = row.querySelector('.service-select');
                const itemNameInput = row.querySelector('.item-name-input');
                const itemTypeSelect = row.querySelector('.item-type-select');
                const unitPriceInput = row.querySelector('.unit-price-input');
                const qtyInput = row.querySelector('.quantity-input');
                const removeBtn = row.querySelector('.remove-row-btn');

                serviceSelect.addEventListener('change', function () {
                    const selectedOpt = serviceSelect.options[serviceSelect.selectedIndex];
                    if (selectedOpt && selectedOpt.value) {
                        const name = selectedOpt.getAttribute('data-name');
                        const price = parseFloat(selectedOpt.getAttribute('data-price')) || 0;
                        const cat = selectedOpt.getAttribute('data-category');

                        itemNameInput.value = name;
                        unitPriceInput.value = price.toFixed(2);

                        // Match category to item_type
                        if (cat === 'CONSULTATION') itemTypeSelect.value = 'consultation';
                        else if (cat === 'BED_CHARGES') itemTypeSelect.value = 'bed';
                        else if (cat === 'LAB_TESTS') itemTypeSelect.value = 'lab';
                        else if (cat === 'OPERATION_CHARGES') itemTypeSelect.value = 'surgery';
                        else itemTypeSelect.value = 'service';
                    }
                    calculateTotals();
                });

                unitPriceInput.addEventListener('input', calculateTotals);
                qtyInput.addEventListener('input', calculateTotals);

                removeBtn.addEventListener('click', function () {
                    if (itemsTableBody.querySelectorAll('.item-row').length > 1) {
                        row.remove();
                        calculateTotals();
                    } else {
                        alert('Invoice must have at least one billable item.');
                    }
                });
            }

            // Attach events to initial row
            itemsTableBody.querySelectorAll('.item-row').forEach(attachRowEvents);

            // Add new row dynamically
            addRowBtn.addEventListener('click', function () {
                const firstRow = itemsTableBody.querySelector('.item-row');
                const newRow = firstRow.cloneNode(true);

                // Update input names for new index
                newRow.querySelectorAll('select, input').forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) {
                        input.setAttribute('name', name.replace(/\[\d+\]/, '[' + rowIndex + ']'));
                    }
                    if (input.classList.contains('item-name-input') || input.classList.contains('service-select')) {
                        input.value = '';
                    } else if (input.classList.contains('unit-price-input')) {
                        input.value = '0.00';
                    } else if (input.classList.contains('quantity-input')) {
                        input.value = '1';
                    } else if (input.classList.contains('row-total')) {
                        input.value = '0.00';
                    }
                });

                itemsTableBody.appendChild(newRow);
                attachRowEvents(newRow);
                rowIndex++;
                calculateTotals();
            });

            discountInput.addEventListener('input', calculateTotals);

            calculateTotals();
        });
    </script>
</x-layouts.portal>

