<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Diagnostic Report — Meghna Clinic & Nursing Home</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .report-container { box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body class="bg-light py-4">
    <div class="container" style="max-width: 820px;">
        <!-- Top Actions -->
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <a href="{{ route('patient.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
            <button onclick="window.print()" class="btn btn-teal btn-sm">
                <i class="bi bi-printer me-1"></i> Print Diagnostic Report
            </button>
        </div>

        <!-- Official Report Card -->
        <div class="card bg-white p-5 border rounded-3 shadow-sm report-container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                <div>
                    <h1 class="h3 fw-bold text-navy mb-1">Meghna Clinic & Nursing Home</h1>
                    <p class="text-teal fw-semibold mb-1" style="font-size: 0.95rem;">
                        Clinical Pathology & Diagnostic Laboratory
                    </p>
                    <small class="text-muted">
                        Shohid Minar Mor, Dinajpur Sadar &bull; Ph: +880 1711-000000
                    </small>
                </div>
                <div class="text-end">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fw-bold text-uppercase" style="font-size: 0.8rem;">
                        <i class="bi bi-check2-circle me-1"></i> Authorized Report
                    </span>
                    <div class="text-muted mt-2" style="font-size: 0.8rem;">
                        Order Ref: <strong>{{ $report->orderItem?->labOrder?->order_number ?? 'LAB-ORD' }}</strong>
                    </div>
                </div>
            </div>

            <!-- Patient Information Box -->
            <div class="row g-3 p-3 rounded-3 bg-light border mb-4" style="font-size: 0.875rem;">
                <div class="col-md-6 border-end">
                    <span class="text-muted-hms fw-bold d-block text-uppercase" style="font-size: 0.725rem;">PATIENT DEMOGRAPHICS</span>
                    <strong class="text-navy fs-6">{{ $patient->name }}</strong>
                    <div class="text-muted">
                        Patient Code: <span class="fw-semibold text-dark">{{ $patient->patient_code }}</span> &bull; 
                        Gender: <span class="text-capitalize">{{ $patient->gender ?? 'N/A' }}</span> &bull; 
                        Blood: <strong>{{ $patient->blood_group ?? 'N/A' }}</strong>
                    </div>
                    <small class="text-muted">Phone: {{ $patient->phone }}</small>
                </div>
                <div class="col-md-6 ps-md-4">
                    <span class="text-muted-hms fw-bold d-block text-uppercase" style="font-size: 0.725rem;">INVESTIGATION DETAILS</span>
                    <strong class="text-navy fs-6">{{ $report->orderItem?->test_name ?? 'Diagnostic Test' }}</strong>
                    <div class="text-muted">
                        Category: <span class="fw-semibold text-teal">{{ $report->orderItem?->labTest?->category ?? 'Hematology' }}</span>
                    </div>
                    <small class="text-muted">
                        Reviewed On: <strong>{{ $report->reviewed_at ? $report->reviewed_at->format('d M Y, h:i A') : $report->created_at->format('d M Y') }}</strong>
                    </small>
                </div>
            </div>

            <!-- Test Parameters Table -->
            @if(!empty($report->parameters) && is_array($report->parameters))
                <div class="mb-4">
                    <h2 class="h6 fw-bold text-navy text-uppercase mb-2" style="font-size: 0.85rem;">Quantitative Parameter Analysis</h2>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light text-uppercase text-muted-hms" style="font-size: 0.75rem;">
                                <tr>
                                    <th>Parameter Investigated</th>
                                    <th>Patient Result Value</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 0.875rem;">
                                @foreach($report->parameters as $param => $val)
                                    <tr>
                                        <td class="fw-semibold text-navy">{{ $param }}</td>
                                        <td class="fw-bold text-teal">{{ $val }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">Recorded</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Findings Summary -->
            <div class="p-3 rounded-3 bg-light border mb-5">
                <strong class="text-navy d-block mb-1" style="font-size: 0.85rem;">Consultant Pathologist Remarks & Findings:</strong>
                <p class="text-muted-hms mb-0" style="font-size: 0.85rem; line-height: 1.6;">
                    {{ $report->findings_summary ?? 'All measured physiological parameters within standard diagnostic reference thresholds.' }}
                </p>
            </div>

            <!-- Footer Signatures -->
            <div class="d-flex justify-content-between align-items-end pt-4 border-top mt-auto" style="font-size: 0.8rem;">
                <div class="text-muted">
                    Meghna Clinic & Nursing Home &bull; Diagnostic Pathology Wing<br>
                    Verified digitally &bull; Valid without physical stamp
                </div>
                <div class="text-center" style="min-width: 220px;">
                    <div class="border-top border-dark pt-1 fw-bold text-navy">
                        {{ $report->reviewedBy?->name ?? 'Attending Pathologist' }}
                    </div>
                    <small class="text-muted">{{ $report->reviewedBy?->staffProfile?->designation ?? 'Consultant Pathologist' }}</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
