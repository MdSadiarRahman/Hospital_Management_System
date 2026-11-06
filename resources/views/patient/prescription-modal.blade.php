<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Digital Prescription — Meghna Clinic & Nursing Home</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .rx-container { box-shadow: none !important; border: none !important; }
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
                <i class="bi bi-printer me-1"></i> Print Digital Prescription
            </button>
        </div>

        <!-- Official Prescription Card -->
        <div class="card bg-white p-5 border rounded-3 shadow-sm rx-container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                <div>
                    <h1 class="h3 fw-bold text-navy mb-1">Meghna Clinic & Nursing Home</h1>
                    <p class="text-teal fw-semibold mb-1" style="font-size: 0.95rem;">
                        Shohid Minar Mor, Dinajpur Sadar, Dinajpur
                    </p>
                    <small class="text-muted">
                        Phone: +880 1711-000000 &bull; 24/7 Emergency & Maternity Care
                    </small>
                </div>
                <div class="text-end">
                    <span class="badge bg-teal-subtle text-teal border px-3 py-1.5 fw-bold font-monospace" style="font-size: 0.85rem;">
                        {{ $prescription->prescription_number ?? 'Official Digital Rx' }}
                    </span>
                    <div class="text-muted mt-2" style="font-size: 0.8rem;">
                        Date: <strong>{{ $prescription->created_at->format('d M Y') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Doctor & Patient Details -->
            <div class="row g-3 p-3 rounded-3 bg-light border mb-4" style="font-size: 0.875rem;">
                <div class="col-md-6 border-end">
                    <span class="text-muted-hms fw-bold d-block text-uppercase" style="font-size: 0.725rem;">PRESCRIBING CONSULTANT</span>
                    <strong class="text-navy fs-6">{{ $prescription->consultation?->doctor?->name ?? 'Medical Officer' }}</strong>
                    <div class="text-teal fw-semibold">{{ $prescription->consultation?->doctor?->staffProfile?->specialization ?? 'General Medicine' }}</div>
                    <small class="text-muted">{{ $prescription->consultation?->doctor?->staffProfile?->designation ?? 'Consultant' }}</small>
                </div>
                <div class="col-md-6 ps-md-4">
                    <span class="text-muted-hms fw-bold d-block text-uppercase" style="font-size: 0.725rem;">PATIENT INFORMATION</span>
                    <strong class="text-navy fs-6">{{ $patient->name }}</strong>
                    <div class="text-muted">
                        Patient ID: <span class="fw-semibold text-dark">{{ $patient->patient_code }}</span> &bull; 
                        Gender: <span class="text-capitalize">{{ $patient->gender ?? 'N/A' }}</span> &bull; 
                        Blood Group: <strong>{{ $patient->blood_group ?? 'N/A' }}</strong>
                    </div>
                    <small class="text-muted">Phone: {{ $patient->phone }}</small>
                </div>
            </div>

            <!-- Clinical Diagnoses -->
            @if($prescription->consultation?->diagnoses?->isNotEmpty())
                <div class="mb-4">
                    <span class="text-muted-hms fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem;">CLINICAL DIAGNOSIS:</span>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($prescription->consultation->diagnoses as $diag)
                            <span class="badge bg-light text-navy border px-2.5 py-1.5 fw-semibold" style="font-size: 0.825rem;">
                                {{ $diag->condition_name }} @if($diag->icd_code)({{ $diag->icd_code }})@endif
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Rx Medications Table -->
            <div class="mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="fs-3 fw-bold text-teal" style="font-family: serif;">℞</span>
                    <span class="fw-bold text-navy text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">Prescribed Medications</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light text-uppercase text-muted-hms" style="font-size: 0.75rem;">
                            <tr>
                                <th>#</th>
                                <th>Medicine Name</th>
                                <th>Dosage</th>
                                <th>Frequency</th>
                                <th>Duration</th>
                                <th>Instructions</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.875rem;">
                            @foreach($prescription->items as $idx => $item)
                                <tr>
                                    <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                    <td class="fw-bold text-navy">{{ $item->medicine_name }}</td>
                                    <td>{{ $item->dosage }}</td>
                                    <td><span class="badge bg-teal text-white">{{ $item->frequency }}</span></td>
                                    <td>{{ $item->duration }}</td>
                                    <td class="text-muted">{{ $item->instructions ?? 'As directed' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Advice & Follow-up -->
            <div class="p-3 rounded-3 bg-light border mb-5">
                @if($prescription->advice)
                    <div class="mb-2">
                        <strong class="text-navy d-block mb-1" style="font-size: 0.85rem;">Clinical Advice & Special Instructions:</strong>
                        <p class="text-muted-hms mb-0" style="font-size: 0.85rem; line-height: 1.6;">
                            {{ $prescription->advice }}
                        </p>
                    </div>
                @endif
                <div class="text-navy fw-semibold" style="font-size: 0.825rem;">
                    Next Follow-up Consultation: 
                    <span class="text-teal fw-bold">
                        {{ $prescription->follow_up_date ? \Carbon\Carbon::parse($prescription->follow_up_date)->format('d F Y') : 'As advised by physician' }}
                    </span>
                </div>
            </div>

            <!-- Footer Signatures -->
            <div class="d-flex justify-content-between align-items-end pt-4 border-top mt-auto" style="font-size: 0.8rem;">
                <div class="text-muted">
                    Meghna Clinic & Nursing Home &bull; Electronic Health Record System<br>
                    Generated securely from Patient Portal on {{ now()->format('d M Y, h:i A') }}
                </div>
                <div class="text-center" style="min-width: 200px;">
                    <div class="border-top border-dark pt-1 fw-bold text-navy">
                        {{ $prescription->consultation?->doctor?->name ?? 'Attending Doctor' }}
                    </div>
                    <small class="text-muted">Authorized Medical Practitioner</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
