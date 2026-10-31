<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Medical Record Dossier — {{ $patient->patient_code }} — {{ $patient->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css'])
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, sans-serif;
            color: #1e293b;
        }
        .chart-container {
            max-width: 900px;
            margin: 30px auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            padding: 32px;
        }
        @media print {
            body {
                background: #ffffff;
            }
            .no-print {
                display: none !important;
            }
            .chart-container {
                border: none;
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <!-- Top Action Bar -->
    <div class="no-print bg-white border-bottom py-3 shadow-xs mb-4 text-center">
        <div class="d-flex align-items-center justify-content-center gap-3">
            <button onclick="window.print()" class="btn btn-navy d-inline-flex align-items-center gap-2 shadow-xs">
                <i class="bi bi-printer-fill"></i>
                <span>Print Official Medical Chart</span>
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary">
                Close Window
            </button>
        </div>
        <span class="text-muted small d-block mt-1">Official Confidential Clinical Record • Meghna Clinic & Nursing Home</span>
    </div>

    <!-- Chart Container -->
    <div class="chart-container">
        <!-- Clinic Branding Header -->
        <div class="border-bottom pb-4 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center fw-bold" style="width: 48px; height: 48px; font-size: 1.5rem;">
                        <i class="bi bi-hospital"></i>
                    </div>
                    <div>
                        <h1 class="h4 fw-bold text-navy mb-0" style="letter-spacing: -0.5px;">MEGHNA CLINIC AND NURSING HOME</h1>
                        <p class="text-teal fw-semibold mb-0" style="font-size: 0.85rem;">Shohid Minar Mor, Dinajpur Sadar, Dinajpur, Bangladesh</p>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Phone: 01711-000000 • Email: info@meghnaclinic.com • Reg No: DGHS-DNP-2024</p>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-navy text-white font-monospace px-3 py-2" style="font-size: 0.95rem;">
                        {{ $patient->patient_code }}
                    </span>
                    <span class="d-block text-muted small mt-1">Printed: {{ now()->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>

        <div class="text-center py-2 bg-light rounded-3 border mb-4">
            <h2 class="h6 fw-bold text-navy mb-0 text-uppercase" style="letter-spacing: 1px;">
                CONFIDENTIAL PATIENT MEDICAL RECORD & CLINICAL DOSSIER
            </h2>
        </div>

        <!-- Section 1: Patient Master Demographics -->
        <div class="card border rounded-3 p-3 mb-4 bg-light bg-opacity-50">
            <h3 class="h6 fw-bold text-navy border-bottom pb-2 mb-3">1. PATIENT MASTER DEMOGRAPHICS</h3>
            <div class="row g-2" style="font-size: 0.85rem;">
                <div class="col-md-4"><strong>Full Legal Name:</strong> {{ $patient->name }}</div>
                <div class="col-md-4"><strong>Patient ID:</strong> <span class="font-monospace fw-bold">{{ $patient->patient_code }}</span></div>
                <div class="col-md-4"><strong>Blood Group:</strong> <span class="text-danger fw-bold">{{ $patient->blood_group ?? 'Not Recorded' }}</span></div>
                <div class="col-md-4"><strong>Date of Birth / Age:</strong> {{ $patient->date_of_birth ? $patient->date_of_birth->format('d M Y') : 'N/A' }} ({{ $patient->formatted_age }})</div>
                <div class="col-md-4"><strong>Gender:</strong> {{ ucfirst($patient->gender) }}</div>
                <div class="col-md-4"><strong>Primary Phone:</strong> <span class="font-monospace">{{ $patient->phone }}</span></div>
                <div class="col-md-4"><strong>NID / Birth Cert:</strong> {{ $patient->nid_or_birth_cert ?? 'None' }}</div>
                <div class="col-md-4"><strong>Registration Date:</strong> {{ $patient->registration_date ? $patient->registration_date->format('d M Y') : $patient->created_at->format('d M Y') }}</div>
                <div class="col-md-4"><strong>Emergency Kin:</strong> {{ $patient->primaryContact ? $patient->primaryContact->name . ' (' . $patient->primaryContact->phone . ')' : 'None' }}</div>
                <div class="col-12"><strong>Address:</strong> {{ $patient->address ?? 'Shohid Minar Mor, Dinajpur' }}</div>
            </div>
        </div>

        <!-- Section 2: Clinical Summary Metrics -->
        <div class="row g-2 mb-4 text-center">
            <div class="col">
                <div class="p-2 border rounded bg-white">
                    <div class="small text-muted">Total Visits</div>
                    <div class="fw-bold text-navy fs-5">{{ $clinicalMetrics['total_visits'] }}</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 border rounded bg-white">
                    <div class="small text-muted">Consultations</div>
                    <div class="fw-bold text-navy fs-5">{{ $clinicalMetrics['consultations_count'] }}</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 border rounded bg-white">
                    <div class="small text-muted">Admissions</div>
                    <div class="fw-bold text-navy fs-5">{{ $clinicalMetrics['admissions_count'] }}</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 border rounded bg-white">
                    <div class="small text-muted">Prescriptions</div>
                    <div class="fw-bold text-navy fs-5">{{ $clinicalMetrics['prescriptions_count'] }}</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 border rounded bg-white">
                    <div class="small text-muted">Lab Orders</div>
                    <div class="fw-bold text-navy fs-5">{{ $clinicalMetrics['lab_orders_count'] }}</div>
                </div>
            </div>
        </div>

        <!-- Section 3: Visit History -->
        <div class="mb-4">
            <h3 class="h6 fw-bold text-navy border-bottom pb-2 mb-2">2. PATIENT VISIT HISTORY</h3>
            @if($visits->isNotEmpty())
                <table class="table table-sm table-bordered" style="font-size: 0.8rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Visit No</th>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Triage</th>
                            <th>Care Setting</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($visits as $v)
                            <tr>
                                <td class="font-monospace fw-bold">{{ $v->visit_number }}</td>
                                <td>{{ $v->visit_date ? $v->visit_date->format('d M Y, h:i A') : 'N/A' }}</td>
                                <td>{{ ucfirst($v->visit_type) }}</td>
                                <td>{{ ucfirst($v->triage_level) }}</td>
                                <td>{{ $v->is_inpatient ? 'Inpatient' : 'Outpatient' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $v->status)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted small">No visit records found.</p>
            @endif
        </div>

        <!-- Section 4: Previous Consultations & Diagnoses -->
        <div class="mb-4">
            <h3 class="h6 fw-bold text-navy border-bottom pb-2 mb-2">3. CLINICAL CONSULTATIONS & DIAGNOSES</h3>
            @if($consultations->isNotEmpty())
                @foreach($consultations as $c)
                    <div class="border rounded p-2.5 mb-2" style="font-size: 0.8rem;">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold text-navy">Dr. {{ $c->doctor->name }} ({{ $c->doctor->staffProfile?->department?->name ?? 'Medical Officer' }})</span>
                            <span class="text-muted">{{ $c->created_at->format('d M Y, h:i A') }} • Visit #{{ $c->visit->visit_number }}</span>
                        </div>
                        <div><strong>Chief Complaints:</strong> {{ $c->chief_complaints }}</div>
                        @if($c->clinical_notes)
                            <div><strong>Clinical Notes:</strong> {{ $c->clinical_notes }}</div>
                        @endif
                        @if($c->diagnoses && $c->diagnoses->isNotEmpty())
                            <div class="mt-1">
                                <strong>Diagnoses:</strong>
                                @foreach($c->diagnoses as $d)
                                    <span class="badge bg-light text-dark border">{{ $d->condition_name }} @if($d->icd_code)({{ $d->icd_code }})@endif</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <p class="text-muted small">No clinical consultation notes on file.</p>
            @endif
        </div>

        <!-- Section 5: Previous Admissions & Inpatient Stays -->
        <div class="mb-4">
            <h3 class="h6 fw-bold text-navy border-bottom pb-2 mb-2">4. INPATIENT ADMISSIONS & 10-BED STAYS</h3>
            @if($admissions->isNotEmpty())
                <table class="table table-sm table-bordered" style="font-size: 0.8rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Admission No</th>
                            <th>Admission Date</th>
                            <th>Discharge Date</th>
                            <th>Bed</th>
                            <th>Attending Doctor</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admissions as $a)
                            <tr>
                                <td class="font-monospace fw-bold">{{ $a->admission_number }}</td>
                                <td>{{ $a->admission_date ? $a->admission_date->format('d M Y') : 'N/A' }}</td>
                                <td>{{ $a->discharge_date ? $a->discharge_date->format('d M Y') : 'Active' }}</td>
                                <td>{{ $a->currentBedAssignment?->bed?->bed_number ?? 'Bed Allocated' }}</td>
                                <td>{{ $a->attendingDoctor?->name ?? 'Medical Officer' }}</td>
                                <td>{{ $a->admission_reason }}</td>
                                <td>{{ ucfirst($a->status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted small">No inpatient admission stays on file.</p>
            @endif
        </div>

        <!-- Section 6: Prescriptions & Medication History -->
        <div class="mb-4">
            <h3 class="h6 fw-bold text-navy border-bottom pb-2 mb-2">5. PRESCRIBED MEDICATIONS & REGIMENS</h3>
            @if($prescriptions->isNotEmpty())
                @foreach($prescriptions as $p)
                    <div class="border rounded p-2.5 mb-2" style="font-size: 0.8rem;">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold text-navy">Prescribed by Dr. {{ $p->consultation?->doctor?->name ?? 'Medical Officer' }}</span>
                            <span class="text-muted">{{ $p->created_at->format('d M Y') }}</span>
                        </div>
                        @if($p->items && $p->items->isNotEmpty())
                            <table class="table table-sm table-bordered mb-1" style="font-size: 0.75rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Medicine Name</th>
                                        <th>Dosage</th>
                                        <th>Frequency</th>
                                        <th>Duration</th>
                                        <th>Instructions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($p->items as $it)
                                        <tr>
                                            <td class="fw-bold">{{ $it->medicine_name }}</td>
                                            <td>{{ $it->dosage }}</td>
                                            <td>{{ $it->frequency }}</td>
                                            <td>{{ $it->duration }}</td>
                                            <td>{{ $it->instructions ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                        @if($p->advice)
                            <div class="text-muted small"><strong>Advice:</strong> {{ $p->advice }}</div>
                        @endif
                    </div>
                @endforeach
            @else
                <p class="text-muted small">No prescription records on file.</p>
            @endif
        </div>

        <!-- Section 7: Laboratory & Diagnostic History -->
        <div class="mb-4">
            <h3 class="h6 fw-bold text-navy border-bottom pb-2 mb-2">6. DIAGNOSTIC LABORATORY HISTORY</h3>
            @if($labOrders->isNotEmpty())
                <table class="table table-sm table-bordered" style="font-size: 0.8rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Order No</th>
                            <th>Date</th>
                            <th>Source</th>
                            <th>Tests</th>
                            <th>Order Status</th>
                            <th>Review Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($labOrders as $lo)
                            <tr>
                                <td class="font-monospace fw-bold">{{ $lo->order_number }}</td>
                                <td>{{ $lo->created_at->format('d M Y') }}</td>
                                <td>{{ ucfirst($lo->source) }} @if($lo->labPartner) ({{ $lo->labPartner->name }}) @endif</td>
                                <td>{{ $lo->items->pluck('test_name')->join(', ') }}</td>
                                <td>{{ ucfirst($lo->status) }}</td>
                                <td>
                                    @php
                                        $isReviewed = $lo->items->every(fn($i) => $i->report && $i->report->review_status === 'reviewed');
                                    @endphp
                                    {{ $isReviewed && $lo->items->isNotEmpty() ? 'Reviewed by Doctor' : 'Pending Review' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted small">No laboratory investigations recorded.</p>
            @endif
        </div>

        <!-- Section 8: Discharge History -->
        <div class="mb-4">
            <h3 class="h6 fw-bold text-navy border-bottom pb-2 mb-2">7. INPATIENT DISCHARGE SUMMARIES</h3>
            @if($dischargeSummaries->isNotEmpty())
                @foreach($dischargeSummaries as $ds)
                    <div class="border rounded p-2.5 mb-2" style="font-size: 0.8rem;">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold text-navy">Admission #{{ $ds->admission->admission_number }}</span>
                            <span class="text-muted">{{ $ds->created_at->format('d M Y') }} • Condition: {{ ucfirst($ds->condition_at_discharge) }}</span>
                        </div>
                        <div><strong>Discharge Diagnosis:</strong> {{ $ds->discharge_diagnosis }}</div>
                        <div><strong>Treatment Given:</strong> {{ $ds->treatment_given }}</div>
                        <div><strong>Medications at Discharge:</strong> {{ $ds->discharge_medications }}</div>
                        @if($ds->follow_up_instructions)
                            <div><strong>Follow-up Instructions:</strong> {{ $ds->follow_up_instructions }}</div>
                        @endif
                        <div class="text-muted small mt-1 text-end">Authorized by: Dr. {{ $ds->authorizedBy?->name ?? 'Attending Doctor' }}</div>
                    </div>
                @endforeach
            @else
                <p class="text-muted small">No concluded discharge summaries on file.</p>
            @endif
        </div>

        <!-- Sign-off Block -->
        <div class="pt-5 mt-4 border-top">
            <div class="row text-center" style="font-size: 0.8rem;">
                <div class="col-4">
                    <div class="border-top pt-2 mx-3">
                        <strong>Prepared By (Staff)</strong>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-top pt-2 mx-3">
                        <strong>Attending Medical Officer</strong>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-top pt-2 mx-3">
                        <strong>Medical Superintendent</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
