<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $bill->invoice_number }} — Meghna Clinic & Nursing Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #263445;
            background-color: #f8fafc;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .bengali-text {
            font-family: 'Hind Siliguri', sans-serif;
        }
        .invoice-box {
            max-width: 860px;
            margin: 20px auto;
            padding: 30px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header-title {
            color: #123B5D;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .table-invoice th {
            background-color: #f1f5f9 !important;
            color: #123B5D;
            font-size: 0.82rem;
            text-transform: uppercase;
            font-weight: 700;
            border-bottom: 2px solid #cbd5e1;
        }
        .table-invoice td {
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 10px;
            font-size: 0.9rem;
        }
        .stamp-box {
            display: inline-block;
            border: 2px dashed #94a3b8;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }
        .stamp-paid {
            border-color: #10b981;
            color: #10b981;
            background-color: #ecfdf5;
        }
        .stamp-partial {
            border-color: #f59e0b;
            color: #f59e0b;
            background-color: #fffbeb;
        }
        .stamp-unpaid {
            border-color: #ef4444;
            color: #ef4444;
            background-color: #fef2f2;
        }
        @media print {
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .invoice-box {
                border: none !important;
                box-shadow: none !important;
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 10px 15px !important;
            }
        }
    </style>
</head>
<body>

    {{-- Floating Print Control Bar --}}
    <div class="no-print bg-white border-bottom py-2 shadow-sm sticky-top">
        <div class="container d-flex justify-content-between align-items-center" style="max-width: 860px;">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portal.billing.show', $bill) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dossier
                </a>
                <span class="text-muted small">Invoice #{{ $bill->invoice_number }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" onclick="window.print()" class="btn btn-sm btn-primary">
                    <i class="bi bi-printer-fill me-1"></i> Print Invoice / Receipt
                </button>
            </div>
        </div>
    </div>

    <div class="invoice-box">
        {{-- Hospital Header --}}
        <div class="row align-items-center border-bottom pb-3 mb-3">
            <div class="col-8">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div class="bg-primary text-white rounded p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #123B5D !important;">
                        <i class="bi bi-hospital fs-5"></i>
                    </div>
                    <div>
                        <h3 class="header-title mb-0">MEGHNA CLINIC & NURSING HOME</h3>
                        <div class="bengali-text text-muted small fw-semibold">মেঘনা ক্লিনিক অ্যান্ড নার্সিং হোম • দিনাজপুর</div>
                    </div>
                </div>
                <p class="text-muted small mb-0">
                    Shohid Minar Mor, Dinajpur - 5200, Bangladesh<br>
                    24/7 Emergency & Inpatient Care | Hotline: +880 1711-000000 | Reg: DGHS-DNP-2024
                </p>
            </div>
            <div class="col-4 text-end">
                <h5 class="fw-bold text-navy mb-1">MONEY RECEIPT</h5>
                <div class="bengali-text text-muted small mb-2">রোগীর ইনভয়েস ও রসিদ</div>
                @if($bill->status === 'paid')
                    <div class="stamp-box stamp-paid">PAID IN FULL</div>
                @elseif($bill->status === 'partial')
                    <div class="stamp-box stamp-partial">PARTIAL PAID</div>
                @else
                    <div class="stamp-box stamp-unpaid">UNPAID / DUE</div>
                @endif
            </div>
        </div>

        {{-- Invoice & Patient Meta Grid --}}
        <div class="row g-2 mb-3 small">
            <div class="col-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted ps-0" style="width: 32%;">Invoice No:</td>
                        <td class="fw-bold font-monospace text-navy">{{ $bill->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Billing Date:</td>
                        <td class="fw-semibold">{{ $bill->bill_date ? $bill->bill_date->format('d M Y, h:i A') : $bill->created_at->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Prepared By:</td>
                        <td>{{ $bill->createdBy->name ?? 'Cashier Desk' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted ps-0" style="width: 32%;">Patient ID:</td>
                        <td class="fw-bold font-monospace text-navy">{{ $bill->patient->patient_code ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Patient Name:</td>
                        <td class="fw-bold text-dark">{{ $bill->patient->name ?? 'Direct Patient' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Age / Gender:</td>
                        <td>{{ $bill->patient->formatted_age ?? 'N/A' }} / {{ ucfirst($bill->patient->gender ?? 'N/A') }} • Phone: {{ $bill->patient->phone ?? 'N/A' }}</td>
                    </tr>
                    @if($bill->admission)
                        <tr>
                            <td class="text-muted ps-0">Inpatient Bed:</td>
                            <td class="fw-semibold text-primary">Bed {{ $bill->admission->currentBedAssignment?->bed?->bed_number ?? 'Assigned' }} (Admission #{{ $bill->admission->id }})</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Bill Line Items Table --}}
        <div class="table-responsive mb-3">
            <table class="table table-invoice mb-0">
                <thead>
                    <tr>
                        <th style="width: 6%;" class="text-center">#</th>
                        <th style="width: 48%;">Service / Procedural Charge</th>
                        <th style="width: 14%;" class="text-center">Category</th>
                        <th style="width: 10%;" class="text-center">Qty</th>
                        <th style="width: 11%;" class="text-end">Unit Tariff</th>
                        <th style="width: 11%;" class="text-end">Total (BDT)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bill->items as $index => $item)
                        <tr>
                            <td class="text-center text-muted small">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $item->item_name }}</div>
                                @if($item->service_code)
                                    <span class="text-muted small font-monospace">[{{ $item->service_code }}]</span>
                                @endif
                            </td>
                            <td class="text-center text-muted small text-capitalize">{{ $item->item_type }}</td>
                            <td class="text-center font-monospace">{{ $item->quantity }}</td>
                            <td class="text-end font-monospace">৳ {{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end font-monospace fw-bold">৳ {{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Financial Calculation Box --}}
        <div class="row align-items-start mb-4">
            <div class="col-7">
                @if($bill->notes)
                    <div class="p-2 border rounded bg-light small mb-2">
                        <strong class="d-block text-dark">Notes / Instructions:</strong>
                        <span class="text-muted">{{ $bill->notes }}</span>
                    </div>
                @endif

                @if($bill->discount_amount > 0)
                    <div class="p-2 border border-success border-opacity-25 rounded bg-success bg-opacity-10 small">
                        <strong class="text-success"><i class="bi bi-check-circle me-1"></i> Authorized Concession:</strong>
                        <div class="text-muted">
                            Approved by: <strong>{{ $bill->discountAuthorizedBy->name ?? 'Clinic Management' }}</strong>
                            @if($bill->discount_reason)
                                — Reason: "{{ $bill->discount_reason }}"
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-5">
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted ps-0">Gross Subtotal (মোট অঙ্ক):</td>
                        <td class="text-end font-monospace fw-semibold">৳ {{ number_format($bill->subtotal_amount, 2) }}</td>
                    </tr>
                    @if($bill->discount_amount > 0)
                        <tr>
                            <td class="text-success ps-0">Discount (মওকুফ / ছাড়):</td>
                            <td class="text-end font-monospace text-success fw-semibold">-৳ {{ number_format($bill->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="border-top border-bottom">
                        <td class="fw-bold text-navy ps-0 py-2">Net Payable (সর্বমোট প্রদেয়):</td>
                        <td class="text-end font-monospace fw-bold fs-6 text-navy py-2">৳ {{ number_format($bill->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Paid Amount (জমা হয়েছে):</td>
                        <td class="text-end font-monospace text-success fw-bold">৳ {{ number_format($bill->paid_amount, 2) }}</td>
                    </tr>
                    <tr class="border-top">
                        <td class="fw-bold ps-0 text-dark">Balance Due (বকেয়া):</td>
                        <td class="text-end font-monospace fw-bold {{ $bill->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                            ৳ {{ number_format($bill->due_amount, 2) }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Signatures Section --}}
        <div class="row pt-4 mt-4 text-center small text-muted">
            <div class="col-4">
                <div class="border-top pt-2 mx-3">
                    Patient / Attendant Signature
                </div>
            </div>
            <div class="col-4">
                <div class="border-top pt-2 mx-3">
                    Prepared By (Cashier)
                </div>
            </div>
            <div class="col-4">
                <div class="border-top pt-2 mx-3">
                    Authorized Medical Officer / Super
                </div>
            </div>
        </div>

        <div class="text-center text-muted mt-4 pt-2 border-top small" style="font-size: 0.75rem;">
            This is a computer-generated official receipt from Meghna Clinic & Nursing Home, Dinajpur. Generated on {{ now()->format('d/m/Y h:i A') }}.
        </div>
    </div>

</body>
</html>

