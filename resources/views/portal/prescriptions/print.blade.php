<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription — {{ $prescription->prescription_number }} — Meghna Clinic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #263445;
        }
        .rx-page {
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
        .rx-symbol {
            font-size: 2.2rem;
            font-family: Georgia, serif;
            font-weight: bold;
            color: #123B5D;
            line-height: 1;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 4.8rem;
            color: rgba(18, 59, 93, 0.04);
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
            .rx-page {
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
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-teal text-white font-monospace">{{ $prescription->prescription_number }}</span>
                <span class="badge bg-{{ $prescription->status_badge_class }} text-uppercase">{{ $prescription->status }}</span>
                <span class="small text-muted">{{ $prescription->created_at->format('d M Y, h:i A') }}</span>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-teal btn-sm shadow-sm d-flex align-items-center gap-1">
                    <i class="bi bi-printer"></i>
                    <span>Print Prescription</span>
                </button>
                <a href="{{ route('portal.prescriptions.show', $prescription) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Return to Dossier
                </a>
            </div>
        </div>
    </div>

    <div class="rx-page position-relative">
        <div class="watermark">Meghna Clinic</div>

        <!-- 1. Header: Clinic Brand & Doctor Credentials -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-3">
            <div>
                <h3 class="clinic-brand mb-0">Meghna Clinic & Nursing Home</h3>
                <div class="small text-muted">Shohid Minar Mor, Dinajpur Sadar, Dinajpur, Bangladesh</div>
                <div class="small text-muted">Emergency Hotline: +880 1712-345678 &bull; 24/7 Clinical & Surgical Care</div>
            </div>
            <div class="text-end">
                <h5 class="fw-bold text-navy mb-0">Dr. {{ $doctor?->name ?? 'Clinical Practitioner' }}</h5>
                <div class="small text-teal fw-semibold">{{ $doctor?->staffProfile?->qualification ?? 'MBBS, Clinical Specialist' }}</div>
                <div class="small text-muted">{{ $doctor?->staffProfile?->designation ?? 'Consultant Physician' }}</div>
                <div class="small font-monospace text-muted">BMDC Reg No: {{ $doctor?->staffProfile?->license_number ?? 'Verified Doctor' }}</div>
                <div class="small text-muted">Dept: {{ $doctor?->staffProfile?->department?->name ?? 'General Medicine' }}</div>
            </div>
        </div>

        <!-- 2. Patient Demographics & Prescription Metadata Ribbon -->
        <div class="p-3 bg-light rounded-3 border mb-4">
            <div class="row g-2 small">
                <div class="col-6 col-md-3">
                    <span class="text-muted">Patient Name:</span>
                    <strong class="text-dark d-block">{{ $patient->name }}</strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-muted">Patient Code:</span>
                    <strong class="text-dark font-monospace d-block">{{ $patient->patient_code }}</strong>
                </div>
                <div class="col-4 col-md-2">
                    <span class="text-muted">Age / Sex:</span>
                    <span class="d-block">{{ $patient->formatted_age }} / {{ ucfirst($patient->gender) }}</span>
                </div>
                <div class="col-4 col-md-2">
                    <span class="text-muted">Prescription #:</span>
                    <strong class="text-teal font-monospace d-block">{{ $prescription->prescription_number }}</strong>
                </div>
                <div class="col-4 col-md-2 text-md-end">
                    <span class="text-muted">Date:</span>
                    <span class="d-block font-monospace">{{ $prescription->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        <!-- 3. Two-Column Clinical Layout -->
        <div class="row g-4">
            <!-- Left Column: Diagnoses & Complaints (4 cols) -->
            <div class="col-4 border-end pe-4" style="min-height: 480px;">
                @if($consultation && $consultation->chief_complaints)
                    <div class="mb-4">
                        <div class="small fw-bold text-navy text-uppercase border-bottom pb-1 mb-2">Chief Complaints</div>
                        <p class="small text-dark mb-0" style="white-space: pre-line;">{{ $consultation->chief_complaints }}</p>
                    </div>
                @endif

                <div class="mb-4">
                    <div class="small fw-bold text-navy text-uppercase border-bottom pb-1 mb-2">Diagnoses</div>
                    @if($diagnoses->isNotEmpty())
                        <ul class="list-unstyled mb-0 small">
                            @foreach($diagnoses as $d)
                                <li class="mb-1">
                                    <strong class="text-dark">&bull; {{ $d->condition_name }}</strong>
                                    <div class="text-muted font-monospace" style="font-size: 0.7rem;">
                                        ({{ ucfirst($d->diagnosis_type) }}@if($d->icd_code), ICD: {{ $d->icd_code }}@endif)
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <span class="small text-muted">Clinical evaluation recorded.</span>
                    @endif
                </div>

                <!-- Footer References -->
                <div class="mt-auto pt-3 border-top small text-muted font-monospace">
                    Visit: {{ $consultation?->visit?->visit_number ?? 'N/A' }}<br>
                    Cons Ref: #CNS-{{ str_pad($consultation?->id ?? 0, 5, '0', STR_PAD_LEFT) }}<br>
                    Security: {{ substr(hash('sha256', $prescription->prescription_number . $prescription->created_at), 0, 12) }}
                </div>
            </div>

            <!-- Right Column: Rx Medication Items & Instructions (8 cols) -->
            <div class="col-8 ps-3">
                <div class="rx-symbol mb-3">&#8478;</div>

                @if($items->isNotEmpty())
                    <div class="d-flex flex-column gap-3 mb-4">
                        @foreach($items as $idx => $item)
                            <div class="pb-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-bold text-navy">
                                        {{ $idx + 1 }}. {{ $item->medicine_name }}
                                    </div>
                                    <div class="font-monospace small text-muted">
                                        {{ $item->duration }}
                                    </div>
                                </div>
                                <div class="font-monospace small text-teal fw-semibold mt-1">
                                    {{ $item->dosage }} &bull; {{ $item->frequency }}
                                </div>
                                @if($item->instructions)
                                    <div class="small text-muted fst-italic mt-1">
                                        Instructions: {{ $item->instructions }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted small fst-italic mb-4">
                        No pharmacological medications prescribed. Follow general clinical advice below.
                    </div>
                @endif

                @if($prescription->advice)
                    <div class="p-3 bg-light rounded border mb-4">
                        <div class="small fw-bold text-navy text-uppercase mb-1">Advice & Care Directions:</div>
                        <p class="small text-dark mb-0" style="white-space: pre-line;">{{ $prescription->advice }}</p>
                    </div>
                @endif

                @if($prescription->follow_up_date)
                    <div class="p-2 border rounded border-teal small text-teal mb-4">
                        <strong>Follow-up Consultation:</strong>
                        {{ $prescription->follow_up_date->format('l, d F Y') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- 4. Footer & Signature Block -->
        <div class="mt-5 pt-4 border-top">
            <div class="row align-items-end">
                <div class="col-7 small text-muted">
                    <p class="mb-0" style="font-size: 0.72rem;">
                        <strong>Secure Medical Record:</strong> Official digital prescription from Meghna Clinic & Nursing Home. Emergency room, maternity, and surgical wards operate 24 hours daily at Shohid Minar Mor, Dinajpur.
                    </p>
                </div>
                <div class="col-5 text-end">
                    <div style="border-top: 1px dashed #94a3b8; width: 190px; margin-left: auto; padding-top: 5px;">
                        <span class="small fw-bold text-navy d-block">Dr. {{ $doctor?->name }}</span>
                        <span class="small text-muted" style="font-size: 0.7rem;">Authorized Doctor's Signature</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

