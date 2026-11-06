<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic Requisition — {{ $order->order_number }} — Meghna Clinic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #263445;
        }
        .requisition-page {
            max-width: 820px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            padding: 40px;
            border: 1px solid #e2e8f0;
            position: relative;
        }
        .clinic-brand {
            color: #123B5D;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 4rem;
            color: rgba(8, 127, 140, 0.04);
            font-weight: 900;
            pointer-events: none;
            text-transform: uppercase;
            z-index: 0;
            user-select: none;
        }
        @media print {
            body {
                background: #ffffff !important;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .requisition-page {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 20px !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body>
    <!-- Print Toolbar (Hidden during print) -->
    <div class="no-print bg-white border-bottom py-2 sticky-top shadow-sm">
        <div class="container d-flex justify-content-between align-items-center" style="max-width: 820px;">
            <a href="{{ route('portal.lab-orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Order Dossier
            </a>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-sm btn-teal text-white shadow-sm">
                    <i class="bi bi-printer me-1"></i> Print Requisition
                </button>
            </div>
        </div>
    </div>

    <div class="requisition-page">
        <div class="watermark">Meghna Diagnostics</div>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
            <div>
                <h3 class="clinic-brand mb-1">MEGHNA CLINIC & NURSING HOME</h3>
                <p class="text-muted small mb-1">Shohid Minar Mor, Dinajpur, Bangladesh &bull; Emergency: +880 1700-000000</p>
                <div class="badge bg-teal text-white px-2 py-1 small">
                    Department of Laboratory Medicine & Pathology
                </div>
            </div>
            <div class="text-end">
                <div class="fw-bold font-monospace fs-5 text-navy">{{ $order->order_number }}</div>
                <div class="small text-muted">Date: {{ $order->created_at->format('d M Y, h:i A') }}</div>
                <div class="mt-1">
                    @if($order->priority === 'stat')
                        <span class="badge bg-danger text-white">STAT EMERGENCY</span>
                    @elseif($order->priority === 'urgent')
                        <span class="badge bg-warning text-dark">URGENT</span>
                    @else
                        <span class="badge bg-light text-secondary border">ROUTINE</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Patient & Doctor Information Box -->
        <div class="row g-3 p-3 bg-light rounded-3 mb-4 border">
            <div class="col-6">
                <div class="small text-muted text-uppercase fw-semibold">Patient Information</div>
                <div class="fw-bold text-navy fs-6">{{ $order->patient?->name }}</div>
                <div class="small text-muted font-monospace">
                    ID: {{ $order->patient?->patient_code }} &bull; Age/Sex: {{ $order->patient?->age_gender ?? 'N/A' }}
                </div>
                <div class="small text-muted">Phone: {{ $order->patient?->phone ?? 'N/A' }}</div>
            </div>
            <div class="col-6 text-end">
                <div class="small text-muted text-uppercase fw-semibold">Prescribing Clinician</div>
                <div class="fw-bold text-navy fs-6">Dr. {{ $order->doctor?->name }}</div>
                <div class="small text-muted">{{ $order->doctor?->staffProfile?->specialization ?? 'Medical Officer' }}</div>
                <div class="small text-muted">Staff Code: {{ $order->doctor?->staffProfile?->employee_code ?? 'EMP-' . $order->doctor?->id }}</div>
            </div>
        </div>

        <!-- Requisition Routing & Specimen Accession -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <strong>Testing Route:</strong>
                @if($order->source === 'internal')
                    <span class="badge bg-light text-navy border">In-House Meghna Lab</span>
                @else
                    <span class="badge bg-warning-subtle text-dark border">External: {{ $order->labPartner?->name }}</span>
                @endif
            </div>
            @if($order->samples->isNotEmpty())
                <div>
                    <strong>Specimen Barcode:</strong>
                    <span class="badge bg-light text-navy border font-monospace fs-6">
                        {{ $order->samples->first()->barcode }}
                    </span>
                </div>
            @endif
        </div>

        @if($order->notes)
            <div class="mb-4 p-2 bg-light-subtle rounded border small">
                <strong>Clinical Notes / Indications:</strong> {{ $order->notes }}
            </div>
        @endif

        <!-- Tests Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 20%;">Test Code</th>
                        <th style="width: 45%;">Investigation Name</th>
                        <th style="width: 15%;">Specimen</th>
                        <th style="width: 15%;" class="text-end">Tariff (৳)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="font-monospace fw-bold text-navy">{{ $item->test_code }}</td>
                            <td>
                                <div class="fw-semibold">{{ $item->test_name }}</div>
                                @if($item->instructions)
                                    <div class="small text-muted fst-italic">{{ $item->instructions }}</div>
                                @endif
                            </td>
                            <td class="small">{{ $item->labTest?->sample_type ?? 'Specimen' }}</td>
                            <td class="text-end font-monospace">{{ $item->formatted_charge }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="4" class="text-end">Total Diagnostic Tariff:</td>
                        <td class="text-end font-monospace text-navy">{{ $order->formatted_total_charge }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Instructions for Patient & Phlebotomy -->
        <div class="border rounded p-3 mb-5 small text-muted">
            <div class="fw-bold text-navy mb-1">Instructions for Patient & Phlebotomy:</div>
            <ul class="mb-0 ps-3">
                <li>Present this requisition slip at the Meghna Clinic Laboratory counter on the 1st floor.</li>
                <li>Fast for 8–10 hours if fasting blood sugar (FBS) or lipid profile is ordered.</li>
                <li>Collect early morning midstream urine for urine routine and culture tests in sterile container.</li>
                <li>Reports will be available online in the Patient Portal and at the counter upon clinical verification.</li>
            </ul>
        </div>

        <!-- Signatures Footer -->
        <div class="row pt-5 mt-5">
            <div class="col-6 text-center">
                <div style="border-top: 1px dashed #94a3b8; width: 80%; margin: 0 auto; padding-top: 5px;">
                    <small class="text-muted">Prescribing Doctor's Signature</small>
                </div>
            </div>
            <div class="col-6 text-center">
                <div style="border-top: 1px dashed #94a3b8; width: 80%; margin: 0 auto; padding-top: 5px;">
                    <small class="text-muted">Phlebotomist / Receiving Technologist</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

