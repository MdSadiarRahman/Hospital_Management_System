<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appointment Slip #{{ $appointment->id }} — Meghna Clinic</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #263445;
            background-color: #f8fafc;
            padding: 20px;
        }
        .bengali-text {
            font-family: 'Hind Siliguri', sans-serif;
        }
        .slip-card {
            max-width: 580px;
            margin: 0 auto;
            background: #ffffff;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 30px;
        }
        .token-badge {
            background-color: #123B5D;
            color: #ffffff;
            font-size: 2.2rem;
            font-weight: 800;
            padding: 10px 24px;
            border-radius: 8px;
            display: inline-block;
            letter-spacing: 1px;
        }
        .clinic-brand {
            color: #123B5D;
            font-weight: 800;
            font-size: 1.35rem;
            letter-spacing: -0.5px;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .slip-card {
                border: 1px solid #94a3b8;
                box-shadow: none !important;
                padding: 20px;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="text-center mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary px-4 fw-semibold me-2">
            Print Appointment Slip
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary">
            Close
        </button>
    </div>

    <div class="slip-card shadow-sm">
        <!-- Clinic Header -->
        <div class="text-center pb-3 border-bottom border-2">
            <div class="clinic-brand">MEGHNA CLINIC & NURSING HOME</div>
            <div class="bengali-text fw-semibold text-secondary small">মেঘনা ক্লিনিক এন্ড নার্সিং হোম</div>
            <div class="text-muted small mt-1">
                Shohid Minar Mor, Dinajpur, Bangladesh &bull; Hotline: +880 1711-000004
            </div>
            <div class="badge bg-light text-muted border text-uppercase mt-2 px-2.5 py-1" style="font-size: 0.75rem;">
                OPD Outpatient Consultation Token
            </div>
        </div>

        <!-- Serial Number Token -->
        <div class="text-center py-4 my-2 bg-light rounded-3">
            <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Daily Consultation Serial</span>
            <div class="token-badge">
                #{{ $appointment->serial_number ?: $appointment->id }}
            </div>
            <div class="small text-muted mt-2">
                Appointment Reference: <strong>MCNH-APT-{{ str_pad($appointment->id, 5, '0', STR_PAD_LEFT) }}</strong>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="row g-2 mb-3 small">
            <div class="col-6">
                <span class="text-muted d-block">Patient Name:</span>
                <strong class="text-navy fs-6">{{ $appointment->patient_display_name }}</strong>
            </div>
            <div class="col-6 text-end">
                <span class="text-muted d-block">Phone:</span>
                <strong class="text-navy">{{ $appointment->patient_display_phone }}</strong>
            </div>
            @if($appointment->patient)
            <div class="col-6">
                <span class="text-muted d-block">Patient Code (PID):</span>
                <strong class="text-teal">{{ $appointment->patient->patient_code }}</strong>
            </div>
            <div class="col-6 text-end">
                <span class="text-muted d-block">Gender / Age:</span>
                <strong>{{ ucfirst($appointment->patient->gender ?? 'N/A') }} ({{ $appointment->patient->age ?? '—' }} yrs)</strong>
            </div>
            @endif
            <div class="col-12"><hr class="my-2 border-dashed"></div>
            <div class="col-7">
                <span class="text-muted d-block">Consulting Doctor:</span>
                <strong class="text-navy">{{ $appointment->doctor?->name }}</strong>
                <div class="text-muted" style="font-size: 0.78rem;">{{ $appointment->doctor?->staffProfile?->designation ?? 'Consultant' }}</div>
            </div>
            <div class="col-5 text-end">
                <span class="text-muted d-block">Department & Chamber:</span>
                <strong class="text-teal">{{ $appointment->department?->name }}</strong>
                <div class="text-muted" style="font-size: 0.78rem;">{{ $appointment->doctor?->staffProfile?->room_number ?? 'OPD Room 101' }}</div>
            </div>
            <div class="col-12"><hr class="my-2 border-dashed"></div>
            <div class="col-6">
                <span class="text-muted d-block">Scheduled Date:</span>
                <strong class="text-navy fs-6">{{ $appointment->appointment_date->format('d M Y (l)') }}</strong>
            </div>
            <div class="col-6 text-end">
                <span class="text-muted d-block">Scheduled Slot Time:</span>
                <strong class="text-navy fs-6">{{ $appointment->appointment_time ? date('h:i A', strtotime($appointment->appointment_time)) : $appointment->appointment_date->format('h:i A') }}</strong>
            </div>
        </div>

        <!-- Instructions (English & Bengali) -->
        <div class="bg-light p-3 rounded-2 text-start small border mt-3">
            <div class="fw-bold text-navy mb-1">
                <i class="bi bi-info-circle me-1"></i> Patient Instructions / নির্দেশনাবলী:
            </div>
            <ul class="mb-0 ps-3 text-muted" style="font-size: 0.8rem; line-height: 1.5;">
                <li>Please report to the OPD reception desk <strong>15 minutes</strong> prior to your scheduled slot.</li>
                <li class="bengali-text">অনুগ্রহ করে নির্ধারিত সময়ের ১৫ মিনিট পূর্বে অভ্যর্থনা ডেস্কে উপস্থিত হয়ে টোকেন নিশ্চিত করুন।</li>
                <li>Bring previous medical reports, prescriptions, and patient ID card if available.</li>
            </ul>
        </div>

        <!-- Footer -->
        <div class="text-center pt-3 text-muted" style="font-size: 0.75rem;">
            Printed on: {{ now()->format('d M Y, h:i A') }} &bull; Meghna Clinic Reception Desk
        </div>
    </div>
</body>
</html>
