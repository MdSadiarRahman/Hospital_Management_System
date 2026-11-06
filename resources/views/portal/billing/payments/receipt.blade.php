<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Money Receipt #{{ $payment->receipt_number }} — Meghna Clinic & Nursing Home</title>
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
        .receipt-box {
            max-width: 800px;
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
        .stamp-box {
            display: inline-block;
            border: 2px dashed #10b981;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            color: #10b981;
            background-color: #ecfdf5;
            letter-spacing: 1px;
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
            .receipt-box {
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

    {{-- Floating Control Bar --}}
    <div class="no-print bg-white border-bottom py-2 shadow-sm sticky-top">
        <div class="container d-flex justify-content-between align-items-center" style="max-width: 800px;">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portal.payments.show', $payment) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Transaction
                </a>
                <span class="text-muted small">Money Receipt #{{ $payment->receipt_number }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" onclick="window.print()" class="btn btn-sm btn-primary">
                    <i class="bi bi-printer-fill me-1"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>

    <div class="receipt-box">
        {{-- Hospital Header --}}
        <div class="row align-items-center border-bottom pb-3 mb-3">
            <div class="col-8">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div class="bg-primary text-white rounded p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #123B5D !important;">
                        <i class="bi bi-hospital fs-5"></i>
                    </div>
                    <div>
                        <h4 class="header-title mb-0">MEGHNA CLINIC & NURSING HOME</h4>
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
                <div class="bengali-text text-muted small mb-2">টাকা প্রাপ্তি রসিদ</div>
                <div class="stamp-box">RECEIVED WITH THANKS</div>
            </div>
        </div>

        {{-- Meta Grid --}}
        <div class="row g-2 mb-3 small">
            <div class="col-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted ps-0" style="width: 38%;">Receipt No:</td>
                        <td class="fw-bold font-monospace text-navy">{{ $payment->receipt_number }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Payment Date:</td>
                        <td class="fw-semibold">{{ $payment->payment_date ? $payment->payment_date->format('d M Y, h:i A') : $payment->created_at->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Collected By:</td>
                        <td>{{ $payment->receivedBy->name ?? 'Cashier' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted ps-0" style="width: 38%;">Patient ID:</td>
                        <td class="fw-bold font-monospace text-navy">{{ $payment->bill->patient->patient_code ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Patient Name:</td>
                        <td class="fw-bold text-dark">{{ $payment->bill->patient->name ?? 'Walk-in Patient' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Invoice Reference:</td>
                        <td class="fw-bold font-monospace text-primary">{{ $payment->bill->invoice_number ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Payment Details Table --}}
        <div class="card border rounded-3 p-3 mb-3 bg-light bg-opacity-50">
            <div class="row align-items-center">
                <div class="col-7">
                    <span class="text-muted small text-uppercase fw-semibold d-block">Payment Method & Reference</span>
                    <div class="fw-bold text-dark mt-1">
                        <i class="bi bi-check2-circle text-success me-1"></i>
                        {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                        @if($payment->transaction_ref)
                            <span class="text-muted font-monospace small">({{ $payment->transaction_ref }})</span>
                        @endif
                    </div>
                    @if($payment->notes)
                        <div class="text-muted small mt-1">
                            <em>Remarks: {{ $payment->notes }}</em>
                        </div>
                    @endif
                </div>
                <div class="col-5 text-end border-start">
                    <span class="text-muted small text-uppercase fw-semibold d-block">Received Amount (প্রাপ্ত টাকা)</span>
                    <h3 class="fw-bold font-monospace text-success mt-1 mb-0">
                        ৳ {{ number_format($payment->amount, 2) }}
                    </h3>
                </div>
            </div>
        </div>

        {{-- Invoice Reconciliation Summary --}}
        @if($payment->bill)
            <div class="border rounded-3 p-3 mb-4 small bg-white">
                <span class="text-muted text-uppercase fw-semibold d-block mb-2">Invoice Reconciliation (বিল সমন্বয় বিবরণ)</span>
                <div class="row g-2">
                    <div class="col-4">
                        <span class="text-muted d-block">Total Invoice Bill:</span>
                        <span class="font-monospace fw-bold text-dark">৳ {{ number_format($payment->bill->total_amount, 2) }}</span>
                    </div>
                    <div class="col-4">
                        <span class="text-muted d-block">Total Paid to Date:</span>
                        <span class="font-monospace fw-bold text-success">৳ {{ number_format($payment->bill->paid_amount, 2) }}</span>
                    </div>
                    <div class="col-4">
                        <span class="text-muted d-block">Remaining Due (বকেয়া):</span>
                        <span class="font-monospace fw-bold {{ $payment->bill->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                            ৳ {{ number_format($payment->bill->due_amount, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Signatures Section --}}
        <div class="row pt-4 mt-4 text-center small text-muted">
            <div class="col-4">
                <div class="border-top pt-2 mx-3">
                    Patient / Attendant Signature
                </div>
            </div>
            <div class="col-4">
                <div class="border-top pt-2 mx-3">
                    Collected By (Cashier)
                </div>
            </div>
            <div class="col-4">
                <div class="border-top pt-2 mx-3">
                    Authorized Medical Officer / Super
                </div>
            </div>
        </div>

        <div class="text-center text-muted mt-4 pt-2 border-top small" style="font-size: 0.75rem;">
            This is a computer-generated money receipt from Meghna Clinic & Nursing Home, Dinajpur. Valid with official clinic seal.
        </div>
    </div>

</body>
</html>

