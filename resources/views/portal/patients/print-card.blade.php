<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Card — {{ $patient->patient_code }} — Meghna Clinic</title>
    @vite(['resources/css/app.css'])
    <style>
        body {
            background-color: #f4f7fa;
            color: #263445;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .slip-container {
            max-width: 650px;
            margin: 30px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }
        .clinic-brand-title {
            color: #123B5D;
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .patient-code-box {
            background: #f8fafc;
            border: 2px dashed #087F8C;
            border-radius: 6px;
            padding: 10px 15px;
            text-align: center;
        }
        .fake-barcode {
            font-family: 'Courier New', Courier, monospace;
            font-size: 1.6rem;
            letter-spacing: 5px;
            color: #123B5D;
            font-weight: 900;
        }
        .field-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
        }
        .field-value {
            font-size: 0.925rem;
            font-weight: 600;
            color: #123B5D;
        }
        @media print {
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .slip-container {
                box-shadow: none !important;
                border: 1px solid #333 !important;
                margin: 0 auto !important;
                max-width: 100% !important;
                padding: 20px !important;
                page-break-inside: avoid;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container py-3">
        <!-- Print Controls -->
        <div class="text-center mb-3 no-print">
            <button onclick="window.print()" class="btn btn-teal px-4 py-2 fw-semibold shadow-sm">
                <i class="bi bi-printer-fill me-1"></i> Print Registration Slip
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary px-3 py-2 ms-2">
                Close
            </button>
        </div>

        <div class="slip-container">
            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                <div>
                    <div class="clinic-brand-title">Meghna Clinic & Nursing Home</div>
                    <div class="text-muted small">Shohid Minar Mor, Dinajpur Sadar, Dinajpur, Bangladesh</div>
                    <div class="text-muted small">Tel: +880 1711-000000 &bull; Emergency 24/7 Desk</div>
                </div>
                <div class="text-end">
                    <span class="badge bg-teal px-3 py-1.5" style="font-size: 0.8rem;">
                        PATIENT INTAKE CARD
                    </span>
                    <div class="small text-muted mt-1">Issue Date: {{ date('d M Y') }}</div>
                </div>
            </div>

            <!-- Patient ID & Barcode Box -->
            <div class="patient-code-box mb-4">
                <div class="text-muted-hms small text-uppercase fw-bold mb-1">Master Patient Code</div>
                <div class="fake-barcode">||| {{ $patient->patient_code }} |||</div>
                <div class="fw-bold font-monospace text-teal fs-5 mt-1">{{ $patient->patient_code }}</div>
            </div>

            <!-- Patient Demographics Grid -->
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="field-label">Full Name</div>
                    <div class="field-value fs-5">{{ $patient->name }}</div>
                </div>
                <div class="col-3">
                    <div class="field-label">Age / Gender</div>
                    <div class="field-value">{{ $patient->formatted_age }} / {{ ucfirst($patient->gender) }}</div>
                </div>
                <div class="col-3">
                    <div class="field-label">Blood Group</div>
                    <div class="field-value text-danger">
                        {{ $patient->blood_group ?? 'N/A' }}
                    </div>
                </div>

                <div class="col-6">
                    <div class="field-label">Contact Mobile</div>
                    <div class="field-value font-monospace">{{ $patient->phone }}</div>
                </div>
                <div class="col-6">
                    <div class="field-label">NID / Birth Cert</div>
                    <div class="field-value font-monospace">{{ $patient->nid_or_birth_cert ?? 'Not Provided' }}</div>
                </div>

                <div class="col-12">
                    <div class="field-label">Residential Address</div>
                    <div class="field-value">{{ $patient->address ?? 'Shohid Minar Mor, Dinajpur Sadar, Dinajpur' }}</div>
                </div>
            </div>

            <!-- Emergency Contact Box -->
            <div class="p-3 rounded bg-light border mb-4">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="fw-bold text-navy small text-uppercase">Emergency Guardian / Next of Kin:</span>
                    <span class="badge bg-white text-dark border">Emergency Contact</span>
                </div>
                @if($patient->primaryContact)
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-navy">{{ $patient->primaryContact->name }} ({{ $patient->primaryContact->relationship }})</span>
                        <span class="font-monospace fw-semibold text-danger">{{ $patient->primaryContact->phone }}</span>
                    </div>
                @else
                    <div class="text-muted small">None recorded</div>
                @endif
            </div>

            <!-- Notes & Instructions -->
            <div class="text-muted small border-top pt-3 mb-4" style="font-size: 0.775rem; line-height: 1.5;">
                <strong>Instructions for Patient:</strong> Please present this card at front reception during all future consultations, laboratory tests, emergency triage, and inpatient ward admissions. Do not share your unique patient ID with unauthorized individuals.
            </div>

            <!-- Signature Lines -->
            <div class="row pt-4 text-center" style="font-size: 0.8rem;">
                <div class="col-6">
                    <div class="border-top border-dark pt-1 mx-4">
                        Front Desk Receptionist
                    </div>
                </div>
                <div class="col-6">
                    <div class="border-top border-dark pt-1 mx-4">
                        Official Clinic Seal
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
