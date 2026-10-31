<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Visit Token — {{ $visit->visit_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css'])
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', -apple-system, sans-serif;
            color: #1e293b;
        }
        .token-slip {
            width: 380px;
            margin: 30px auto;
            background: #ffffff;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            padding: 24px;
        }
        @media print {
            body {
                background: #ffffff;
            }
            .no-print {
                display: none !important;
            }
            .token-slip {
                border: 1px solid #94a3b8;
                box-shadow: none;
                margin: 0 auto;
                width: 100%;
                max-width: 360px;
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Print Action Bar (Hidden during print) -->
    <div class="no-print text-center py-3 bg-white border-bottom shadow-xs mb-3">
        <div class="d-flex align-items-center justify-content-center gap-3">
            <button onclick="window.print()" class="btn btn-navy d-inline-flex align-items-center gap-2 shadow-xs">
                <i class="bi bi-printer-fill"></i>
                <span>Print Intake Token</span>
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary">
                Close
            </button>
        </div>
        <span class="text-muted small d-block mt-1">Recommended paper size: POS Thermal Slip or A6 Portrait</span>
    </div>

    <!-- Token Slip Container -->
    <div class="token-slip">
        <!-- Clinic Branding Header -->
        <div class="text-center pb-3 border-bottom mb-3">
            <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.85rem;">
                    <i class="bi bi-hospital"></i>
                </div>
                <h1 class="h6 fw-bold text-navy mb-0" style="letter-spacing: -0.3px;">MEGHNA CLINIC</h1>
            </div>
            <div class="small fw-semibold text-teal" style="font-size: 0.75rem;">& NURSING HOME</div>
            <div class="text-muted" style="font-size: 0.7rem;">Shohid Minar Mor, Dinajpur Sadar, Dinajpur</div>
            <div class="text-muted" style="font-size: 0.7rem;">Emergency Hotline: 01711-000000</div>
        </div>

        <!-- Token Number Display -->
        <div class="text-center py-2 px-3 bg-light rounded-3 border mb-3">
            <span class="text-muted-hms text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">PATIENT INTAKE TOKEN</span>
            <div class="h3 fw-bold text-navy font-monospace my-1" style="letter-spacing: -0.5px;">
                {{ $visit->visit_number }}
            </div>
            <div class="d-flex justify-content-center align-items-center gap-2">
                <span class="badge bg-{{ $visit->triage_level === 'critical' ? 'danger' : ($visit->triage_level === 'priority' ? 'warning text-dark' : 'success') }} text-uppercase px-2 py-1" style="font-size: 0.7rem;">
                    {{ strtoupper($visit->triage_level) }} TRIAGE
                </span>
                <span class="badge bg-light text-dark border text-uppercase px-2 py-1" style="font-size: 0.7rem;">
                    {{ str_replace('_', ' ', $visit->visit_type) }}
                </span>
            </div>
        </div>

        <!-- Patient Master Identity -->
        <div class="mb-3" style="font-size: 0.8rem;">
            <div class="d-flex justify-content-between border-bottom pb-1.5 mb-1.5">
                <span class="text-muted">Patient Name:</span>
                <span class="fw-bold text-navy">{{ $visit->patient->name }}</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-1.5 mb-1.5">
                <span class="text-muted">Patient ID:</span>
                <span class="fw-bold font-monospace text-navy">{{ $visit->patient->patient_code }}</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-1.5 mb-1.5">
                <span class="text-muted">Age / Gender:</span>
                <span class="fw-semibold text-navy">{{ $visit->patient->formatted_age }} / {{ ucfirst($visit->patient->gender) }}</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-1.5 mb-1.5">
                <span class="text-muted">Blood Group:</span>
                <span class="fw-bold text-danger">{{ $visit->patient->blood_group ?? 'Not Recorded' }}</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-1.5 mb-1.5">
                <span class="text-muted">Contact Phone:</span>
                <span class="font-monospace text-navy">{{ $visit->patient->phone }}</span>
            </div>
            <div class="d-flex justify-content-between pb-1">
                <span class="text-muted">Check-in Time:</span>
                <span class="fw-semibold text-navy">{{ $visit->visit_date ? $visit->visit_date->format('d M Y, h:i A') : now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>

        <!-- Doctor & Department Guidance -->
        <div class="p-2.5 rounded bg-light border text-center mb-3" style="font-size: 0.75rem;">
            @if($visit->consultation && $visit->consultation->doctor)
                <div class="fw-bold text-navy">Consultant: Dr. {{ $visit->consultation->doctor->name }}</div>
                <div class="text-muted">{{ $visit->consultation->doctor->staffProfile?->department?->name ?? 'General Medical Officer' }}</div>
            @else
                <div class="fw-semibold text-navy">Please wait in Lobby Queue</div>
                <div class="text-muted">Your token will be called on the chamber display.</div>
            @endif
        </div>

        <!-- Simulated Barcode Container -->
        <div class="text-center pt-2 border-top">
            <div class="font-monospace fw-bold text-muted small mb-1" style="letter-spacing: 3px; font-size: 0.65rem;">
                |||||| | ||||| |||| |||||||| |||| ||
            </div>
            <div class="text-muted font-monospace" style="font-size: 0.65rem;">
                {{ $visit->visit_number }} • MCNH
            </div>
        </div>
    </div>
</body>
</html>
