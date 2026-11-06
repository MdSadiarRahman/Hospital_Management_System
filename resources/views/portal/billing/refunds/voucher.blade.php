<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Voucher #{{ $refund->refund_voucher }} — Meghna Clinic & Nursing Home</title>
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
        .voucher-box {
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
            border: 2px dashed #ef4444;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            color: #ef4444;
            background-color: #fef2f2;
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
            .voucher-box {
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
                @if($refund->payment)
                    <a href="{{ route('portal.payments.show', $refund->payment) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Payment
                    </a>
                @endif
                <span class="text-muted small">Refund Voucher #{{ $refund->refund_voucher }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" onclick="window.print()" class="btn btn-sm btn-danger text-white">
                    <i class="bi bi-printer-fill me-1"></i> Print Refund Voucher
                </button>
            </div>
        </div>
    </div>

    <div class="voucher-box">
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
                <h5 class="fw-bold text-danger mb-1">REFUND VOUCHER</h5>
                <div class="bengali-text text-muted small mb-2">টাকা ফেরত ভাউচার</div>
                <div class="stamp-box">REFUND DISBURSED</div>
            </div>
        </div>

        {{-- Meta Grid --}}
        <div class="row g-2 mb-3 small">
            <div class="col-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted ps-0" style="width: 40%;">Voucher No:</td>
                        <td class="fw-bold font-monospace text-danger">{{ $refund->refund_voucher }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Disbursement Date:</td>
                        <td class="fw-semibold">{{ $refund->refunded_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Original Receipt:</td>
                        <td class="fw-bold font-monospace text-navy">{{ $refund->payment->receipt_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Invoice Reference:</td>
                        <td class="font-monospace text-primary">{{ $refund->bill->invoice_number ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted ps-0" style="width: 40%;">Patient ID:</td>
                        <td class="fw-bold font-monospace text-navy">{{ $refund->bill->patient->patient_code ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Patient Name:</td>
                        <td class="fw-bold text-dark">{{ $refund->bill->patient->name ?? 'Walk-in Patient' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Approved Authority:</td>
                        <td class="fw-semibold text-dark">{{ $refund->approvedBy->name ?? 'Medical Super' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Refund Method:</td>
                        <td class="text-capitalize">{{ str_replace('_', ' ', $refund->refund_method) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Amount Box --}}
        <div class="card border border-danger border-opacity-25 rounded-3 p-3 mb-3 bg-danger bg-opacity-10">
            <div class="row align-items-center">
                <div class="col-7">
                    <span class="text-danger small text-uppercase fw-semibold d-block">Mandatory Approval Justification</span>
                    <div class="fw-bold text-dark mt-1">
                        "{{ $refund->reason }}"
                    </div>
                    @if($refund->transaction_ref)
                        <div class="text-muted small mt-1 font-monospace">
                            Ref / TrxID: {{ $refund->transaction_ref }}
                        </div>
                    @endif
                </div>
                <div class="col-5 text-end border-start border-danger border-opacity-25">
                    <span class="text-danger small text-uppercase fw-semibold d-block">Refunded Amount (ফেরতকৃত অর্থ)</span>
                    <h3 class="fw-bold font-monospace text-danger mt-1 mb-0">
                        ৳ {{ number_format($refund->amount, 2) }}
                    </h3>
                </div>
            </div>
        </div>

        {{-- Signatures Section --}}
        <div class="row pt-5 mt-4 text-center small text-muted">
            <div class="col-4">
                <div class="border-top pt-2 mx-3">
                    Recipient / Patient Signature
                </div>
            </div>
            <div class="col-4">
                <div class="border-top pt-2 mx-3">
                    Cashier (Disbursed By)
                </div>
            </div>
            <div class="col-4">
                <div class="border-top pt-2 mx-3">
                    Superintendent / Authorizer Signature
                </div>
            </div>
        </div>

        <div class="text-center text-muted mt-4 pt-2 border-top small" style="font-size: 0.75rem;">
            Official financial refund voucher issued by Meghna Clinic & Nursing Home, Dinajpur.
        </div>
    </div>

</body>
</html>

