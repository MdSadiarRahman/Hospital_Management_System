<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinical Diagnostic Report — #{{ $report->id }} — Meghna Clinic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #263445;
        }
        .report-page {
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
            color: rgba(18, 59, 93, 0.03);
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
            .report-page {
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
            <a href="{{ route('portal.lab-reports.show', $report->id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Report Dossier
            </a>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-sm btn-teal text-white shadow-sm">
                    <i class="bi bi-printer me-1"></i> Print Report
                </button>
            </div>
        </div>
    </div>

    <div class="report-page">
        <div class="watermark">Diagnostic Report</div>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
            <div>
                <h3 class="clinic-brand mb-1">MEGHNA CLINIC & NURSING HOME</h3>
                <p class="text-muted small mb-1">Shohid Minar Mor, Dinajpur, Bangladesh &bull; Hotline: +880 1700-000000</p>
                <div class="badge bg-teal text-white px-2 py-1 small">
                    Department of Laboratory Medicine & Pathology
                </div>
            </div>
            <div class="text-end">
                <div class="fw-bold font-monospace fs-5 text-navy">REP-{{ str_pad($report->id, 6, '0', STR_PAD_LEFT) }}</div>
                <div class="small text-muted">Date: {{ $report->created_at->format('d M Y, h:i A') }}</div>
                <div class="mt-1">
                    <span class="badge {{ $report->review_status === 'reviewed' ? 'bg-success text-white' : 'bg-warning text-dark' }}">
                        {{ $report->review_status === 'reviewed' ? 'MEDICALLY VERIFIED' : 'PENDING REVIEW' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Patient & Prescriber Info -->
        <div class="row g-3 p-3 bg-light rounded-3 mb-4 border">
            <div class="col-6">
                <div class="small text-muted text-uppercase fw-semibold">Patient Information</div>
                <div class="fw-bold text-navy fs-6">{{ $report->patient?->name }}</div>
                <div class="small text-muted font-monospace">
                    ID: {{ $report->patient?->patient_code }} &bull; Sex/Age: {{ $report->patient?->age_gender ?? 'N/A' }}
                </div>
                <div class="small text-muted">Contact: {{ $report->patient?->phone ?? 'N/A' }}</div>
            </div>
            <div class="col-6 text-end">
                <div class="small text-muted text-uppercase fw-semibold">Prescribing Clinician</div>
                <div class="fw-bold text-navy fs-6">Dr. {{ $report->doctor?->name }}</div>
                <div class="small text-muted">{{ $report->doctor?->staffProfile?->specialization ?? 'Medical Officer' }}</div>
                <div class="small text-muted font-monospace">Order: {{ $report->orderItem?->labOrder?->order_number }}</div>
            </div>
        </div>

        <!-- Specimen & Investigation Context -->
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <div>
                <strong>Investigation:</strong> <span class="fw-bold text-navy fs-6">{{ $report->test_name }}</span>
            </div>
            <div>
                <strong>Specimen:</strong> {{ $report->orderItem?->sample?->sample_type ?? ($report->orderItem?->labTest?->sample_type ?? 'Specimen') }}
                @if($report->orderItem?->sample)
                    <span class="badge bg-light text-navy border font-monospace ms-1">{{ $report->orderItem->sample->barcode }}</span>
                @endif
            </div>
        </div>

        @if($report->source === 'external')
            <div class="mb-3 p-2 bg-warning-subtle rounded border border-warning-subtle small">
                <strong>Outsourced Reference Center:</strong> {{ $report->labPartner?->name ?? 'External Lab' }}
                @if($report->external_reference_number)
                    &bull; <strong>Ref #:</strong> {{ $report->external_reference_number }}
                @endif
            </div>
        @endif

        @if($report->is_critical)
            <div class="alert alert-danger p-2 mb-3 small fw-bold">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> ABNORMAL / CRITICAL PANIC VALUES DETECTED
            </div>
        @endif

        <!-- Structured Parameters Table -->
        @if(!empty($report->parameters))
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th style="width: 35%;">Analyte / Investigation</th>
                            <th style="width: 20%;">Measured Result</th>
                            <th style="width: 15%;">Unit</th>
                            <th style="width: 20%;">Biological Reference Range</th>
                            <th style="width: 10%;" class="text-center">Flag</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report->parameters as $param)
                            <tr>
                                <td class="fw-semibold">{{ $param['parameter'] ?? '' }}</td>
                                <td class="font-monospace fw-bold fs-6">{{ $param['value'] ?? '' }}</td>
                                <td class="small">{{ $param['unit'] ?? '-' }}</td>
                                <td class="small font-monospace">{{ $param['reference_range'] ?? '-' }}</td>
                                <td class="text-center font-monospace small text-uppercase fw-bold {{ ($param['flag'] ?? '') === 'normal' ? 'text-success' : 'text-danger' }}">
                                    {{ $param['flag'] ?? 'normal' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Findings Summary -->
        @if($report->findings_summary)
            <div class="mb-4">
                <div class="small text-muted text-uppercase fw-semibold mb-1">Technologist Impression / Findings:</div>
                <div class="p-3 bg-light rounded border small">
                    {{ $report->findings_summary }}
                </div>
            </div>
        @endif

        <!-- Doctor Review Notes -->
        @if($report->doctor_notes)
            <div class="mb-4">
                <div class="small text-muted text-uppercase fw-semibold mb-1">Attending Doctor Clinical Evaluation & Advice:</div>
                <div class="p-3 bg-light-subtle rounded border small text-dark fst-italic">
                    {{ $report->doctor_notes }}
                </div>
            </div>
        @endif

        <!-- Signatures Footer -->
        <div class="row pt-5 mt-5">
            <div class="col-6 text-center">
                <div style="border-top: 1px dashed #94a3b8; width: 80%; margin: 0 auto; padding-top: 5px;">
                    <div class="fw-semibold small">{{ $report->uploadedBy?->name ?? 'Medical Technologist' }}</div>
                    <small class="text-muted">Medical Technologist (Lab)</small>
                </div>
            </div>
            <div class="col-6 text-center">
                <div style="border-top: 1px dashed #94a3b8; width: 80%; margin: 0 auto; padding-top: 5px;">
                    <div class="fw-semibold small">Dr. {{ $report->reviewedBy?->name ?? 'Attending Clinician' }}</div>
                    <small class="text-muted">Consultant / Attending Physician</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

