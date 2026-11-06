<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Referral Slip — {{ $referral->referral_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #1e293b;
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
            border-bottom: 2px solid #b91c1c;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .section-title {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #b91c1c;
            background-color: #fef2f2;
            padding: 4px 10px;
            border-left: 3px solid #dc2626;
            margin-top: 16px;
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
            color: #0f172a;
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
            <a href="{{ route('portal.referrals.show', $referral) }}" class="btn btn-sm btn-outline-secondary">
                &larr; Back to Dossier
            </a>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-sm btn-danger">
                    <i class="bi bi-printer me-1"></i> Print Handover Slip
                </button>
            </div>
        </div>

        {{-- Clinic Header --}}
        <div class="clinic-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-0 text-navy" style="color: #123B5D;">MEGHNA CLINIC & NURSING HOME</h3>
                <div class="small text-muted">Shohid Minar Mor, Dinajpur, Bangladesh | Emergency Hotline: +880 1700-000000</div>
                <div class="small text-muted">Govt Reg. No: DGHS-DNP-2024-001 | 24/7 Emergency & Inpatient Care</div>
            </div>
            <div class="text-end">
                <div class="badge bg-danger fs-6 px-3 py-2">EMERGENCY REFERRAL</div>
                <div class="font-monospace small text-muted mt-1">{{ $referral->referral_number }}</div>
            </div>
        </div>

        {{-- Destination Banner --}}
        <div class="p-3 bg-light border-start border-4 border-danger rounded mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Referred To (Destination Center):</span>
                    <h5 class="fw-bold text-dark mb-0">{{ $referral->facility_name }}</h5>
                    <div class="small text-danger fw-semibold">{{ $referral->destination_department ?? 'Emergency & ICU' }}</div>
                </div>
                <div class="text-end">
                    @if($referral->is_critical)
                        <span class="badge bg-danger">CRITICAL CONDITION</span>
                    @endif
                    @if($referral->requires_icu)
                        <span class="badge bg-dark">ICU/CCU TRANSFER</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Patient Profile --}}
        <div class="row g-2 mb-2">
            <div class="col-4">
                <div class="data-label">PATIENT NAME</div>
                <div class="data-value">{{ $referral->patient?->name }}</div>
            </div>
            <div class="col-3">
                <div class="data-label">PATIENT CODE</div>
                <div class="data-value font-monospace">{{ $referral->patient?->patient_code }}</div>
            </div>
            <div class="col-3">
                <div class="data-label">AGE / GENDER</div>
                <div class="data-value">{{ $referral->patient?->formatted_age }} / {{ ucfirst($referral->patient?->gender ?? '') }}</div>
            </div>
            <div class="col-2">
                <div class="data-label">BLOOD GROUP</div>
                <div class="data-value text-danger">{{ $referral->patient?->blood_group ?? 'N/A' }}</div>
            </div>

            <div class="col-4">
                <div class="data-label">RELATIVE / GUARDIAN PHONE</div>
                <div class="data-value">{{ $referral->patient?->phone ?? 'N/A' }}</div>
            </div>
            <div class="col-4">
                <div class="data-label">ORIGINATING CLINIC STAY</div>
                <div class="data-value">
                    @if($referral->admission)
                        Inpatient (Bed: {{ $referral->admission->currentBedAssignment?->bed?->bed_number ?? 'N/A' }})
                    @else
                        Emergency Triage Room
                    @endif
                </div>
            </div>
            <div class="col-4">
                <div class="data-label">REFERRAL DATE & TIME</div>
                <div class="data-value">{{ $referral->referred_at?->format('d M Y, h:i A') }}</div>
            </div>
        </div>

        {{-- Reason for Transfer --}}
        <div class="section-title">1. Clinical Reason for Tertiary Escalation</div>
        <div class="p-2 border rounded small bg-light mb-3">
            <strong class="text-danger d-block mb-1">Reason for Transfer:</strong>
            {!! nl2br(e($referral->clinical_reason)) !!}
            <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                *Note: Meghna Clinic does not maintain on-site mechanical ventilators or ICU beds. Transfer initiated for critical care facilities.
            </div>
        </div>

        {{-- Emergency Assessment & Vitals Snapshot --}}
        <div class="section-title">2. Emergency Assessment & Vital Signs on Handover</div>
        @php $v = $referral->vitals_snapshot ?? []; @endphp
        <table class="table table-sm table-bordered text-center small mb-3">
            <thead class="table-light">
                <tr>
                    <th>Blood Pressure</th>
                    <th>Heart Rate</th>
                    <th>SpO2 (Oxygen)</th>
                    <th>Temperature</th>
                    <th>Respiratory Rate</th>
                    <th>Consciousness / GCS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="fw-bold">{{ ($v['bp_systolic'] ?? '--') . '/' . ($v['bp_diastolic'] ?? '--') }} mmHg</td>
                    <td class="fw-bold">{{ $v['heart_rate'] ?? '--' }} bpm</td>
                    <td class="fw-bold {{ (isset($v['spo2']) && $v['spo2'] < 92) ? 'text-danger' : '' }}">{{ isset($v['spo2']) ? $v['spo2'].'%' : '--' }}</td>
                    <td>{{ isset($v['temperature']) ? $v['temperature'].' °C' : '--' }}</td>
                    <td>{{ isset($v['respiratory_rate']) ? $v['respiratory_rate'].'/min' : '--' }}</td>
                    <td>{{ ucfirst($v['consciousness_level'] ?? 'Alert') }} (GCS: {{ $v['gcs_score'] ?? '15' }}/15)</td>
                </tr>
            </tbody>
        </table>

        <div class="mb-3">
            <div class="data-label">CLINICAL ASSESSMENT FINDINGS</div>
            <p class="small mb-0 p-2 border rounded bg-light">{!! nl2br(e($referral->emergency_assessment ?: 'Stabilized bedside.')) !!}</p>
        </div>

        {{-- Interventions Given --}}
        <div class="section-title">3. Immediate Interventions Administered at Meghna Clinic</div>
        <div class="p-2 border rounded small bg-light mb-3">
            {!! nl2br(e($referral->interventions_given ?: 'Intravenous access established, continuous pulse oximetry, fluid resuscitation.')) !!}
        </div>

        {{-- Transport & Transit Care --}}
        <div class="section-title">4. Transport & Ambulance Transit Care</div>
        <div class="row g-2 mb-3">
            <div class="col-4">
                <div class="data-label">TRANSPORT VEHICLE</div>
                <div class="data-value">{{ $referral->transport_type ?: 'Ambulance' }}</div>
            </div>
            <div class="col-4">
                <div class="data-label">OXYGEN SUPPORT IN TRANSIT</div>
                <div class="data-value">{{ $referral->oxygen_support_transit ? 'Yes (Continuous O2)' : 'No' }}</div>
            </div>
            <div class="col-4">
                <div class="data-label">ACCOMPANYING CLINICAL STAFF</div>
                <div class="data-value">{{ $referral->accompanying_staff ?: 'Family accompanied' }}</div>
            </div>
            <div class="col-6">
                <div class="data-label">DRIVER / AMBULANCE CONTACT</div>
                <div class="data-value">{{ $referral->ambulance_driver_contact ?: 'N/A' }}</div>
            </div>
            <div class="col-6">
                <div class="data-label">RECEIVING DESK CONTACT</div>
                <div class="data-value">{{ $referral->receiving_doctor_contact ?: 'Emergency Admission Counter' }}</div>
            </div>
        </div>

        {{-- Signatures Block --}}
        <div class="row pt-5 mt-4 border-top">
            <div class="col-6 text-center">
                <div class="border-top border-dark pt-1 small fw-bold">
                    Dr. {{ $referral->authorizedBy?->name }}
                </div>
                <div class="text-muted" style="font-size: 0.75rem;">Authorizing Physician Signature & Stamp</div>
                <div class="text-muted" style="font-size: 0.7rem;">Meghna Clinic & Nursing Home, Dinajpur</div>
            </div>
            <div class="col-6 text-center">
                <div class="border-top border-dark pt-1 small fw-bold">
                    Receiving Doctor Signature & Stamp
                </div>
                <div class="text-muted" style="font-size: 0.75rem;">{{ $referral->facility_name }}</div>
                <div class="text-muted" style="font-size: 0.7rem;">Emergency Handover Receipt</div>
            </div>
        </div>

        <div class="text-center text-muted mt-4 pt-2" style="font-size: 0.7rem;">
            Generated from Meghna Clinic HMS on {{ now()->format('d M Y, h:i A') }} | Clinical Handover Document
        </div>
    </div>

</body>
</html>

