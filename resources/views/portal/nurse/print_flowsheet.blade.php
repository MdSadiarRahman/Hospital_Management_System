<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bedside Vitals Flowsheet — {{ $patient->name }} — Meghna Clinic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #263445;
        }
        .flowsheet-page {
            max-width: 960px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            padding: 36px;
            border: 1px solid #e2e8f0;
        }
        .clinic-brand {
            color: #123B5D;
            font-weight: 800;
        }
        @media print {
            body { background: #ffffff !important; }
            .flowsheet-page {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 10px !important;
                max-width: 100% !important;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print text-center py-2 bg-dark text-white">
        <button onclick="window.print()" class="btn btn-sm btn-teal text-white px-3 me-2">
            <i class="bi bi-printer me-1"></i> Print Bedside Flowsheet
        </button>
        <button onclick="window.close()" class="btn btn-sm btn-outline-light px-3">Close</button>
    </div>

    <div class="flowsheet-page">
        <!-- Clinic Official Letterhead -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
            <div>
                <h3 class="clinic-brand mb-0">MEGHNA CLINIC & NURSING HOME</h3>
                <div class="text-muted small">Shohid Minar Mor, Dinajpur Sadar, Dinajpur &bull; Phone: +8801711000000</div>
                <div class="fw-bold text-teal small mt-1">INPATIENT BEDSIDE VITAL SIGNS & CLINICAL OBSERVATIONS FLOWSHEET</div>
            </div>
            <div class="text-end font-monospace small">
                <div><strong>Adm #:</strong> {{ $admission->admission_number }}</div>
                <div><strong>Bed:</strong> {{ $admission->currentBed?->bed_number ?? 'Inpatient' }}</div>
                <div><strong>Printed:</strong> {{ now()->format('d M Y, h:i A') }}</div>
            </div>
        </div>

        <!-- Inpatient Demographics Header -->
        <div class="p-3 bg-light rounded border mb-4 small">
            <div class="row g-2">
                <div class="col-4"><strong>Patient:</strong> {{ $patient->name }}</div>
                <div class="col-4"><strong>Code:</strong> {{ $patient->patient_code }}</div>
                <div class="col-4"><strong>Age/Sex:</strong> {{ $patient->formatted_age }} / {{ ucfirst($patient->gender) }}</div>
                <div class="col-4"><strong>Department:</strong> {{ $admission->department?->name ?? 'Ward' }}</div>
                <div class="col-4"><strong>Attending Doctor:</strong> Dr. {{ $admission->attendingDoctor?->name ?? 'Unassigned' }}</div>
                <div class="col-4"><strong>Admitted At:</strong> {{ $admission->admission_date?->format('d M Y') }}</div>
            </div>
        </div>

        <!-- Observation Records Table -->
        <table class="table table-sm table-bordered align-middle text-center small mb-4">
            <thead class="table-light">
                <tr>
                    <th class="text-start">Date & Time</th>
                    <th>Shift</th>
                    <th>Temp (°F)</th>
                    <th>BP (mmHg)</th>
                    <th>Pulse (bpm)</th>
                    <th>SpO2 (%)</th>
                    <th>RR (/min)</th>
                    <th>Wt (kg)</th>
                    <th class="text-start" style="width: 25%;">Bedside Notes & Status</th>
                    <th>Nurse</th>
                </tr>
            </thead>
            <tbody>
                @forelse($timelineNotes as $n)
                    <tr class="{{ $n->is_flagged_abnormal ? 'table-warning' : '' }}">
                        <td class="text-start font-monospace">{{ $n->recorded_at->format('d M, h:i A') }}</td>
                        <td>{{ ucfirst($n->shift) }}</td>
                        <td class="{{ $n->isFever() ? 'fw-bold text-danger' : '' }}">{{ $n->temperature ?? '—' }}</td>
                        <td>{{ $n->blood_pressure ?? '—' }}</td>
                        <td class="{{ $n->isTachycardia() || $n->isBradycardia() ? 'fw-bold text-danger' : '' }}">{{ $n->pulse_rate ?? '—' }}</td>
                        <td class="{{ $n->isHypoxia() ? 'fw-bold text-danger' : '' }}">{{ $n->spo2 ? $n->spo2 . '%' : '—' }}</td>
                        <td>{{ $n->respiratory_rate ?? '—' }}</td>
                        <td>{{ $n->weight ?? '—' }}</td>
                        <td class="text-start">
                            <div>{{ $n->observations }}</div>
                            @if($n->is_flagged_abnormal && !empty($n->clinical_flags))
                                <div class="text-danger small fst-italic mt-1">
                                    [Alert: {{ implode(', ', $n->clinical_flags) }}]
                                </div>
                            @endif
                            @if($n->reviewedBy)
                                <div class="text-success small mt-1">
                                    ✓ Verified by Dr. {{ $n->reviewedBy->name }}
                                </div>
                            @endif
                        </td>
                        <td>Sister {{ $n->nurse?->name }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="py-3 text-muted">No vital signs flow records found for this admission.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Sign-off Block -->
        <div class="row pt-4 mt-4 border-top text-center small">
            <div class="col-4">
                <div class="border-top border-dark pt-1 mt-4">Inpatient Staff Nurse Signature</div>
            </div>
            <div class="col-4">
                <div class="border-top border-dark pt-1 mt-4">Ward In-Charge Sister Signature</div>
            </div>
            <div class="col-4">
                <div class="border-top border-dark pt-1 mt-4">Attending Physician / MO Signature</div>
            </div>
        </div>

        <div class="text-center text-muted small mt-4" style="font-size: 0.7rem;">
            Meghna Clinic & Nursing Home &bull; Official Inpatient Clinical Record &bull; Computer Generated Flowsheet
        </div>
    </div>
</body>
</html>

