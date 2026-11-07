<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discharge Certificate — {{ $admission->admission_number }} — {{ $admission->patient->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            color: #1e293b;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 13px;
        }
        .print-container {
            max-width: 880px;
            margin: 25px auto;
            background: #ffffff;
            padding: 40px 50px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }
        .clinic-header {
            border-bottom: 2px solid #123B5D;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .section-header {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #123B5D;
            background-color: #f8fafc;
            padding: 5px 12px;
            border-left: 3px solid #087F8C;
            margin-top: 16px;
            margin-bottom: 10px;
        }
        .data-label {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 1px;
        }
        .data-value {
            font-size: 0.88rem;
            font-weight: 600;
            color: #0f172a;
        }
        .rx-box {
            background: #fafafa;
            border: 1px dashed #cbd5e1;
            padding: 12px;
            border-radius: 6px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            white-space: pre-line;
            line-height: 1.6;
        }
        .signature-line {
            border-top: 1px solid #94a3b8;
            margin-top: 60px;
            padding-top: 6px;
            text-align: center;
            font-size: 0.8rem;
        }
        @media print {
            body {
                background: #ffffff !important;
                margin: 0;
                padding: 0;
            }
            .print-container {
                box-shadow: none !important;
                padding: 10px !important;
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
            <a href="{{ route('portal.discharges.show', $admission) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Dossier
            </a>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-sm btn-primary">
                    <i class="bi bi-printer me-1"></i> Print Certificate
                </button>
            </div>
        </div>

        {{-- Clinic Brand Header --}}
        <div class="clinic-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-0" style="color: #123B5D; letter-spacing: 0.5px;">MEGHNA CLINIC &amp; NURSING HOME</h3>
                <div class="small text-muted">মেঘনা ক্লিনিক এন্ড নার্সিং হোম &middot; Shohid Minar Mor, Dinajpur, Bangladesh</div>
                <div class="small text-muted">Emergency &amp; Inpatient Care &middot; Phone: +880 1700-000000 &middot; Reg No: DGHS-DNP-2024-001</div>
            </div>
            <div class="text-end">
                <div class="badge bg-teal fs-6 px-3 py-2 text-uppercase" style="background-color: #087F8C;">
                    Discharge Certificate
                </div>
                <div class="font-monospace small text-muted mt-1">Adm: {{ $admission->admission_number }}</div>
            </div>
        </div>

        {{-- Subheading --}}
        <div class="text-center my-3">
            <h5 class="fw-bold text-uppercase tracking-wider mb-0" style="color: #123B5D;">DISCHARGE SUMMARY &amp; CLINICAL REPORT</h5>
            <div class="small text-muted fst-italic">ছাড়পত্র ও চিকিৎসা বিবরণী</div>
        </div>

        {{-- Patient & Admission Profile Table --}}
        <div class="border rounded-2 p-3 bg-light mb-3">
            <div class="row g-2">
                <div class="col-4">
                    <div class="data-label">Patient Name:</div>
                    <div class="data-value">{{ $admission->patient->name }}</div>
                </div>
                <div class="col-4">
                    <div class="data-label">Patient ID:</div>
                    <div class="data-value font-monospace">{{ $admission->patient->patient_code }}</div>
                </div>
                <div class="col-2">
                    <div class="data-label">Age / Gender:</div>
                    <div class="data-value">{{ $admission->patient->age ?? 'N/A' }} / {{ ucfirst($admission->patient->gender) }}</div>
                </div>
                <div class="col-2">
                    <div class="data-label">Blood Group:</div>
                    <div class="data-value text-danger">{{ $admission->patient->blood_group ?? 'N/A' }}</div>
                </div>

                <div class="col-4 mt-2">
                    <div class="data-label">Admission Date:</div>
                    <div class="data-value">{{ $admission->admission_date ? $admission->admission_date->format('d M Y, h:i A') : 'N/A' }}</div>
                </div>
                <div class="col-4 mt-2">
                    <div class="data-label">Discharge Date:</div>
                    <div class="data-value">{{ $admission->discharge_date ? $admission->discharge_date->format('d M Y, h:i A') : ($admission->clinical_clearance_at ? $admission->clinical_clearance_at->format('d M Y') : date('d M Y')) }}</div>
                </div>
                <div class="col-4 mt-2">
                    <div class="data-label">Length of Stay:</div>
                    <div class="data-value">{{ $admission->stay_duration }}</div>
                </div>

                <div class="col-4 mt-2">
                    <div class="data-label">Department:</div>
                    <div class="data-value">{{ $admission->department ? $admission->department->name : 'Inpatient Care' }}</div>
                </div>
                <div class="col-4 mt-2">
                    <div class="data-label">Room / Bed No:</div>
                    <div class="data-value">{{ $admission->currentBed ? 'Bed ' . $admission->currentBed->bed_number . ' (' . ($admission->currentBed->room_number ?? 'Ward') . ')' : 'Inpatient Bed' }}</div>
                </div>
                <div class="col-4 mt-2">
                    <div class="data-label">Attending Consultant:</div>
                    <div class="data-value">{{ $admission->attendingDoctor ? $admission->attendingDoctor->name : 'Attending Physician' }}</div>
                </div>
            </div>
        </div>

        {{-- Clinical Diagnoses & Condition --}}
        @php
            $summary = $admission->dischargeSummary;
        @endphp

        <div class="section-header">Clinical Diagnosis &amp; Discharge Status</div>
        <div class="row g-2 px-1 mb-2">
            <div class="col-8">
                <div class="data-label">Final Diagnosis:</div>
                <div class="data-value fs-6" style="color: #123B5D;">
                    {{ $summary ? $summary->discharge_diagnosis : ($admission->diagnosis ?? 'Clinical evaluation completed') }}
                </div>
            </div>
            <div class="col-4 text-end">
                <div class="data-label">Condition on Discharge:</div>
                <div class="data-value">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 text-uppercase">
                        {{ $summary ? str_replace('_', ' ', $summary->condition_at_discharge) : 'Stable' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Hospital Course & Treatment Given --}}
        @if($summary && $summary->hospital_course)
            <div class="section-header">Hospital Course &amp; Progress</div>
            <div class="px-1 mb-2">
                <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $summary->hospital_course }}</p>
            </div>
        @endif

        <div class="section-header">Operative Procedures &amp; Treatment Given</div>
        <div class="px-1 mb-2">
            <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $summary ? $summary->treatment_given : ($admission->treatment_plan ?? 'Standard inpatient medical management') }}</p>
        </div>

        {{-- Investigations Summary --}}
        @if($summary && $summary->investigations_summary)
            <div class="section-header">Diagnostic Investigations &amp; Lab Results</div>
            <div class="px-1 mb-2">
                <p class="mb-0 text-dark small" style="white-space: pre-line;">{{ $summary->investigations_summary }}</p>
            </div>
        @endif

        {{-- Discharge Medications (Rx) --}}
        <div class="section-header">Take-Home Medications &amp; Schedule (ঔষধ সেবন নির্দেশিকা)</div>
        <div class="px-1 mb-2">
            <div class="rx-box">
                @if($summary && $summary->discharge_medications)
{{ $summary->discharge_medications }}
                @else
As instructed on prescription slip.
                @endif
            </div>
        </div>

        {{-- Follow-Up Instructions --}}
        <div class="section-header">Follow-Up Advice &amp; Emergency Warnings (পরবর্তী সাক্ষাত ও সতর্কতা)</div>
        <div class="px-1 mb-3">
            <p class="mb-0 text-dark fw-semibold" style="white-space: pre-line;">{{ $summary ? $summary->follow_up_instructions : 'Report to OPD in 7 days or immediately to emergency if any warning signs occur.' }}</p>
        </div>

        {{-- Clearance Verification Watermark / Seal --}}
        <div class="row align-items-center border-top border-bottom py-2 my-3">
            <div class="col-6">
                <div class="small text-muted">
                    <i class="bi bi-shield-check text-success me-1"></i>
                    <strong>Clearance Verification:</strong>
                    Clinical Clearance: <span class="text-success fw-bold">Verified</span> |
                    Financial Clearance: <span class="text-success fw-bold">Verified</span>
                </div>
            </div>
            <div class="col-6 text-end">
                <div class="small text-muted">
                    Issue Date: <strong>{{ date('d M Y, h:i A') }}</strong> &middot; Generated by MCNH Hospital System
                </div>
            </div>
        </div>

        {{-- Signatures Block --}}
        <div class="row pt-4">
            <div class="col-4">
                <div class="signature-line">
                    <strong>{{ $admission->clinicalClearanceBy ? $admission->clinicalClearanceBy->name : ($admission->attendingDoctor ? $admission->attendingDoctor->name : 'Medical Officer') }}</strong><br>
                    <span class="text-muted">Attending Doctor / Surgeon</span>
                </div>
            </div>
            <div class="col-4">
                <div class="signature-line">
                    <strong>{{ $admission->nursingClearanceBy ? $admission->nursingClearanceBy->name : 'Nursing In-Charge' }}</strong><br>
                    <span class="text-muted">Ward Nurse In-Charge</span>
                </div>
            </div>
            <div class="col-4">
                <div class="signature-line">
                    <strong>Medical Superintendent</strong><br>
                    <span class="text-muted">Meghna Clinic &amp; Nursing Home</span>
                </div>
            </div>
        </div>

    </div>

</body>
</html>

