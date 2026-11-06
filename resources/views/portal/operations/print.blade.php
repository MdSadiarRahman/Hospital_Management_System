<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surgical Operation Record — {{ $operation->operation_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #212529;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .print-container {
            max-width: 860px;
            margin: 20px auto;
            background: #ffffff;
            padding: 35px 45px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .clinic-header {
            border-bottom: 2px solid #123B5D;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #123B5D;
            background-color: #f1f5f9;
            padding: 4px 10px;
            border-left: 3px solid #087F8C;
            margin-top: 18px;
            margin-bottom: 10px;
        }
        .data-label {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 2px;
        }
        .data-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
        }
        @media print {
            body {
                background: #ffffff !important;
                margin: 0;
                padding: 0;
            }
            .print-container {
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="print-container">
        {{-- Print Action Controls --}}
        <div class="no-print d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <a href="{{ route('portal.operations.show', $operation) }}" class="btn btn-sm btn-outline-secondary">
                &larr; Back to Dossier
            </a>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-sm btn-primary">
                    <i class="bi bi-printer me-1"></i> Print Surgical Sheet
                </button>
            </div>
        </div>

        {{-- Clinic Header --}}
        <div class="clinic-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-0 text-navy" style="color: #123B5D;">MEGHNA CLINIC & NURSING HOME</h3>
                <div class="small text-muted">Shohid Minar Mor, Dinajpur, Bangladesh | Hotline: +880 1700-000000</div>
                <div class="small text-muted">Govt Reg. No: DGHS-DNP-2024-001 | 24/7 Clinical Care & Surgery</div>
            </div>
            <div class="text-end">
                <div class="badge bg-dark fs-6 px-3 py-2">SURGICAL RECORD</div>
                <div class="font-monospace small text-muted mt-1">{{ $operation->operation_number }}</div>
            </div>
        </div>

        {{-- Patient & Admission Dossier --}}
        <div class="row g-2 mb-3">
            <div class="col-4">
                <div class="data-label">PATIENT NAME</div>
                <div class="data-value">{{ $operation->patient?->name }}</div>
            </div>
            <div class="col-3">
                <div class="data-label">PATIENT ID</div>
                <div class="data-value font-monospace">{{ $operation->patient?->patient_code }}</div>
            </div>
            <div class="col-3">
                <div class="data-label">AGE / GENDER</div>
                <div class="data-value">{{ $operation->patient?->formatted_age }} / {{ ucfirst($operation->patient?->gender ?? '') }}</div>
            </div>
            <div class="col-2">
                <div class="data-label">BLOOD GROUP</div>
                <div class="data-value text-danger">{{ $operation->patient?->blood_group ?? 'N/A' }}</div>
            </div>

            <div class="col-4">
                <div class="data-label">INPATIENT ADMISSION #</div>
                <div class="data-value font-monospace">{{ $operation->admission?->admission_number }}</div>
            </div>
            <div class="col-3">
                <div class="data-label">ALLOCATED BED</div>
                <div class="data-value">{{ $operation->admission?->currentBedAssignment?->bed?->bed_number ?? 'N/A' }}</div>
            </div>
            <div class="col-5">
                <div class="data-label">DEPARTMENT / WARD</div>
                <div class="data-value">{{ $operation->department?->name ?? 'General Surgery' }}</div>
            </div>
        </div>

        {{-- Surgical Procedure & Theatre Schedule --}}
        <div class="section-title">1. Operative Procedure & Theatre Details</div>
        <div class="row g-2 mb-2">
            <div class="col-6">
                <div class="data-label">PROCEDURE NAME</div>
                <div class="data-value fs-6 text-primary">{{ $operation->procedure_name }}</div>
            </div>
            <div class="col-3">
                <div class="data-label">OT SUITE / ROOM</div>
                <div class="data-value">{{ $operation->ot_room }}</div>
            </div>
            <div class="col-3">
                <div class="data-label">CLINICAL URGENCY</div>
                <div class="data-value text-uppercase">{{ $operation->urgency }}</div>
            </div>

            <div class="col-4">
                <div class="data-label">SCHEDULED TIME</div>
                <div class="data-value">{{ $operation->scheduled_at?->format('d M Y, h:i A') }}</div>
            </div>
            <div class="col-4">
                <div class="data-label">COMMENCED (STARTED)</div>
                <div class="data-value">{{ $operation->started_at ? $operation->started_at->format('d M Y, h:i A') : 'N/A' }}</div>
            </div>
            <div class="col-4">
                <div class="data-label">COMPLETED (ENDED)</div>
                <div class="data-value">{{ $operation->ended_at ? $operation->ended_at->format('d M Y, h:i A') : 'N/A' }}</div>
            </div>
        </div>

        {{-- Surgical Team --}}
        <div class="section-title">2. Surgical Care Team</div>
        <div class="row g-2 mb-2">
            <div class="col-3">
                <div class="data-label">PRIMARY SURGEON</div>
                <div class="data-value">Dr. {{ $operation->primarySurgeon?->name }}</div>
            </div>
            <div class="col-3">
                <div class="data-label">ASSISTANT SURGEON</div>
                <div class="data-value">{{ $operation->assistantSurgeon ? 'Dr. ' . $operation->assistantSurgeon->name : 'Solo Surgery' }}</div>
            </div>
            <div class="col-3">
                <div class="data-label">ANESTHETIST</div>
                <div class="data-value">{{ $operation->anesthetist_name ?: 'N/A' }}</div>
            </div>
            <div class="col-3">
                <div class="data-label">OT / SCRUB NURSE</div>
                <div class="data-value">{{ $operation->ot_nurse_name ?: 'N/A' }}</div>
            </div>
        </div>

        {{-- Clinical Indication & Pre-Op Notes --}}
        <div class="section-title">3. Pre-Operative Indication / Diagnosis</div>
        <p class="small mb-3 ps-2">{{ $operation->indication ?: 'None specified.' }}</p>

        {{-- Operative Notes & Findings --}}
        <div class="section-title">4. Operative Findings & Surgical Technique Notes</div>
        <div class="p-2 border rounded small bg-light mb-3" style="min-height: 80px;">
            {!! nl2br(e($operation->operation_notes ?: 'Pending operative notes recording.')) !!}
        </div>

        {{-- Post-Operative Instructions --}}
        <div class="section-title">5. Immediate Post-Operative Care & Recovery Orders</div>
        <div class="p-2 border rounded small bg-light mb-3" style="min-height: 60px;">
            {!! nl2br(e($operation->post_op_notes ?: 'Routine post-anesthesia recovery care.')) !!}
        </div>

        {{-- Post-Operative Observations Summary --}}
        @if($operation->postOpObservations->isNotEmpty())
            <div class="section-title">6. Post-Operative Bedside Recovery Observations</div>
            <table class="table table-sm table-bordered small mb-3">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>Vitals (BP, HR, SpO2, Temp)</th>
                        <th>Consciousness & Pain</th>
                        <th>Site / Drain Output</th>
                        <th>Notes</th>
                        <th>Clinician</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($operation->postOpObservations as $obs)
                        <tr>
                            <td>{{ $obs->observed_at->format('d/m H:i') }}</td>
                            <td>
                                BP: {{ $obs->blood_pressure ?: '--' }} | 
                                HR: {{ $obs->heart_rate ?: '--' }} | 
                                SpO2: {{ $obs->spo2_percent ? $obs->spo2_percent.'%' : '--' }}
                            </td>
                            <td>{{ ucfirst($obs->consciousness_level) }} (Pain: {{ $obs->pain_score ?? '--' }}/10)</td>
                            <td>{{ $obs->surgical_site_condition ?: 'Dressing intact' }} ({{ $obs->drain_output_ml ? $obs->drain_output_ml.'mL' : '0' }})</td>
                            <td>{{ $obs->observation_notes }}</td>
                            <td>{{ $obs->recordedBy?->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Signatures Block --}}
        <div class="row pt-5 mt-4 border-top">
            <div class="col-4 text-center">
                <div class="border-top border-dark pt-1 small fw-bold">
                    Dr. {{ $operation->primarySurgeon?->name }}
                </div>
                <div class="text-muted" style="font-size: 0.75rem;">Lead Surgeon Signature</div>
            </div>
            <div class="col-4 text-center">
                <div class="border-top border-dark pt-1 small fw-bold">
                    {{ $operation->anesthetist_name ?: 'Consultant Anesthetist' }}
                </div>
                <div class="text-muted" style="font-size: 0.75rem;">Anesthetist Signature</div>
            </div>
            <div class="col-4 text-center">
                <div class="border-top border-dark pt-1 small fw-bold">
                    {{ $operation->ot_nurse_name ?: 'In-Charge OT Nurse' }}
                </div>
                <div class="text-muted" style="font-size: 0.75rem;">Operating Theatre In-Charge</div>
            </div>
        </div>

        <div class="text-center text-muted mt-4 pt-2" style="font-size: 0.7rem;">
            Generated from Meghna Clinic HMS on {{ now()->format('d M Y, h:i A') }} | Physical Patient File Copy
        </div>
    </div>

</body>
</html>

