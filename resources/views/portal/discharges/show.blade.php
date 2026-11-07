<x-layouts.portal role="admin" title="Discharge Dossier: {{ $admission->admission_number }}" activeNav="discharges">
    <div class="container-fluid px-0">
        {{-- Page Header --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('portal.discharges.index') }}" class="text-decoration-none text-muted-hms">Discharge Clearances</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">{{ $admission->admission_number }}</li>
                    </ol>
                </nav>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">Discharge Dossier: {{ $admission->admission_number }}</h1>
                    @if($admission->status === 'discharged')
                        <span class="badge bg-secondary text-white font-monospace fs-6 px-3 py-1">
                            <i class="bi bi-house-check me-1"></i>DISCHARGED
                        </span>
                    @elseif($admission->isReadyForDischarge())
                        <span class="badge bg-success text-white font-monospace fs-6 px-3 py-1">
                            <i class="bi bi-door-open me-1"></i>READY FOR EXIT
                        </span>
                    @else
                        <span class="badge bg-primary text-white font-monospace fs-6 px-3 py-1">
                            <i class="bi bi-hourglass-split me-1"></i>CLEARANCE IN PROGRESS
                        </span>
                    @endif
                </div>
                <p class="text-muted-hms small mb-0 mt-1">
                    Patient: <strong class="text-navy">{{ $admission->patient->name }}</strong> ({{ $admission->patient->patient_code }}) &middot;
                    Bed: <strong class="text-teal">{{ $admission->currentBed ? 'Bed ' . $admission->currentBed->bed_number : ($admission->status === 'discharged' ? 'Bed Released' : 'N/A') }}</strong> &middot;
                    Admitted: {{ $admission->admission_date ? $admission->admission_date->format('d M Y, h:i A') : 'N/A' }}
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                @if($admission->dischargeSummary)
                    <a href="{{ route('portal.discharges.print-summary', $admission) }}" target="_blank" class="btn btn-outline-navy d-inline-flex align-items-center gap-2 shadow-xs">
                        <i class="bi bi-printer"></i>
                        <span>Print Summary &amp; Certificate</span>
                    </a>
                @endif
                <a href="{{ route('portal.admissions.show', $admission) }}" class="btn btn-outline-teal d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-hospital"></i>
                    <span>Admission Dossier</span>
                </a>
                <a href="{{ route('portal.discharges.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-arrow-left"></i>
                    <span>All Clearances</span>
                </a>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-xs border-0 mb-4" role="alert">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-xs border-0 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-xs border-0 mb-4" role="alert">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i>Please fix the following validation errors:</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Dual-Clearance Multi-Step Progress Tracker --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold text-navy mb-3">Discharge Gateways &amp; Clearance Verification</h5>
                <div class="row g-3 text-center">
                    {{-- 1. Doctor Clinical Clearance --}}
                    <div class="col-12 col-md-3">
                        <div class="p-3 rounded-3 border {{ $admission->clinical_clearance ? 'border-success bg-success-subtle' : 'border-warning bg-light' }} h-100">
                            <div class="fs-4 mb-1">
                                @if($admission->clinical_clearance)
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                    <i class="bi bi-hourglass-split text-warning"></i>
                                @endif
                            </div>
                            <div class="fw-bold text-navy">1. Clinical Clearance</div>
                            <div class="small {{ $admission->clinical_clearance ? 'text-success fw-semibold' : 'text-muted' }}">
                                @if($admission->clinical_clearance)
                                    Cleared by {{ $admission->clinicalClearanceBy ? $admission->clinicalClearanceBy->name : 'Doctor' }}
                                    <div style="font-size: 0.7rem;">{{ $admission->clinical_clearance_at ? $admission->clinical_clearance_at->format('d M, h:i A') : '' }}</div>
                                @else
                                    Pending Doctor Clinical Sign-off
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- 2. Nursing Exit Clearance --}}
                    <div class="col-12 col-md-3">
                        <div class="p-3 rounded-3 border {{ $admission->nursing_clearance ? 'border-success bg-success-subtle' : 'border-secondary bg-light' }} h-100">
                            <div class="fs-4 mb-1">
                                @if($admission->nursing_clearance)
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                    <i class="bi bi-dash-circle text-muted"></i>
                                @endif
                            </div>
                            <div class="fw-bold text-navy">2. Nursing Exit Clearance</div>
                            <div class="small {{ $admission->nursing_clearance ? 'text-success fw-semibold' : 'text-muted' }}">
                                @if($admission->nursing_clearance)
                                    Cleared by {{ $admission->nursingClearanceBy ? $admission->nursingClearanceBy->name : 'Nurse' }}
                                    <div style="font-size: 0.7rem;">{{ $admission->nursing_clearance_at ? $admission->nursing_clearance_at->format('d M, h:i A') : '' }}</div>
                                @else
                                    Exit Checklist Pending
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- 3. Financial Clearance --}}
                    @php
                        $bill = $admission->bill;
                        $due = $bill ? (float)$bill->due_amount : 0;
                    @endphp
                    <div class="col-12 col-md-3">
                        <div class="p-3 rounded-3 border {{ $admission->financial_clearance ? ($admission->financial_exception_allowed ? 'border-warning bg-warning-subtle' : 'border-success bg-success-subtle') : 'border-danger bg-light' }} h-100">
                            <div class="fs-4 mb-1">
                                @if($admission->financial_clearance)
                                    @if($admission->financial_exception_allowed)
                                        <i class="bi bi-shield-exclamation text-warning"></i>
                                    @else
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @endif
                                @else
                                    <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                                @endif
                            </div>
                            <div class="fw-bold text-navy">3. Financial Clearance</div>
                            <div class="small {{ $admission->financial_clearance ? 'text-success fw-semibold' : 'text-danger fw-semibold' }}">
                                @if($admission->financial_clearance)
                                    @if($admission->financial_exception_allowed)
                                        Cleared via Exception (Due: &#2547;{{ number_format($due, 2) }})
                                    @else
                                        Settled &amp; Cleared (Due: &#2547;0.00)
                                    @endif
                                    <div style="font-size: 0.7rem;" class="text-muted">{{ $admission->financial_clearance_at ? $admission->financial_clearance_at->format('d M, h:i A') : '' }}</div>
                                @else
                                    Pending Cashier Clearance
                                    @if($due > 0)
                                        <div style="font-size: 0.75rem;">Due: &#2547;{{ number_format($due, 2) }}</div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- 4. Final Discharge & Bed Release --}}
                    <div class="col-12 col-md-3">
                        <div class="p-3 rounded-3 border {{ $admission->status === 'discharged' ? 'border-primary bg-primary-subtle' : ($admission->isReadyForDischarge() ? 'border-success bg-light' : 'border-secondary bg-light') }} h-100">
                            <div class="fs-4 mb-1">
                                @if($admission->status === 'discharged')
                                    <i class="bi bi-house-check-fill text-primary"></i>
                                @elseif($admission->isReadyForDischarge())
                                    <i class="bi bi-door-open-fill text-success"></i>
                                @else
                                    <i class="bi bi-lock-fill text-muted"></i>
                                @endif
                            </div>
                            <div class="fw-bold text-navy">4. Final Exit &amp; Bed Release</div>
                            <div class="small {{ $admission->status === 'discharged' ? 'text-primary fw-semibold' : ($admission->isReadyForDischarge() ? 'text-success fw-bold' : 'text-muted') }}">
                                @if($admission->status === 'discharged')
                                    Discharged &middot; Bed Released
                                    <div style="font-size: 0.7rem;">{{ $admission->discharge_date ? $admission->discharge_date->format('d M, h:i A') : '' }}</div>
                                @elseif($admission->isReadyForDischarge())
                                    All Gateways Open &mdash; Ready
                                @else
                                    Locked Until Dual Clearances
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Left Column: Clearances & Forms --}}
            <div class="col-12 col-lg-8">
                
                {{-- 1. Doctor Clinical Clearance Section --}}
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $admission->clinical_clearance ? 'bg-success' : 'bg-warning text-dark' }} p-2 rounded-2">
                                <i class="bi bi-heart-pulse-fill"></i>
                            </span>
                            <h5 class="fw-bold text-navy mb-0">Doctor Clinical Clearance &amp; Summary</h5>
                        </div>
                        @if($admission->clinical_clearance && $admission->status === 'admitted' && $canClinical)
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#revokeClinicalModal">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Revoke Clinical Clearance
                            </button>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        @if($admission->clinical_clearance)
                            {{-- Clinical Clearance Granted View --}}
                            <div class="alert alert-success border-0 bg-success-subtle d-flex align-items-center gap-2 py-2 mb-3">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                <div>
                                    <strong>Clinical Clearance Signed:</strong> Cleared by
                                    <strong>{{ $admission->clinicalClearanceBy ? $admission->clinicalClearanceBy->name : 'Medical Officer' }}</strong>
                                    on {{ $admission->clinical_clearance_at ? $admission->clinical_clearance_at->format('d M Y, h:i A') : 'N/A' }}.
                                </div>
                            </div>

                            @if($admission->dischargeSummary)
                                <div class="bg-light p-3 rounded-3 mb-3">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <span class="text-muted small text-uppercase fw-semibold d-block">Discharge Diagnosis:</span>
                                            <span class="fw-bold text-navy">{{ $admission->dischargeSummary->discharge_diagnosis }}</span>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <span class="text-muted small text-uppercase fw-semibold d-block">Condition at Discharge:</span>
                                            <span class="badge bg-navy text-white text-uppercase font-monospace">
                                                {{ str_replace('_', ' ', $admission->dischargeSummary->condition_at_discharge) }}
                                            </span>
                                        </div>
                                        @if($admission->dischargeSummary->hospital_course)
                                            <div class="col-12">
                                                <span class="text-muted small text-uppercase fw-semibold d-block">Hospital Course &amp; Progression:</span>
                                                <p class="mb-0 text-dark small" style="white-space: pre-line;">{{ $admission->dischargeSummary->hospital_course }}</p>
                                            </div>
                                        @endif
                                        <div class="col-12">
                                            <span class="text-muted small text-uppercase fw-semibold d-block">Treatment Provided:</span>
                                            <p class="mb-0 text-dark small" style="white-space: pre-line;">{{ $admission->dischargeSummary->treatment_given }}</p>
                                        </div>
                                        @if($admission->dischargeSummary->investigations_summary)
                                            <div class="col-12">
                                                <span class="text-muted small text-uppercase fw-semibold d-block">Investigations &amp; Diagnostic Summary:</span>
                                                <p class="mb-0 text-dark small" style="white-space: pre-line;">{{ $admission->dischargeSummary->investigations_summary }}</p>
                                            </div>
                                        @endif
                                        <div class="col-12">
                                            <span class="text-muted small text-uppercase fw-semibold d-block">Discharge Medications:</span>
                                            <div class="bg-white p-2 rounded border small font-monospace text-dark" style="white-space: pre-line;">{{ $admission->dischargeSummary->discharge_medications }}</div>
                                        </div>
                                        <div class="col-12">
                                            <span class="text-muted small text-uppercase fw-semibold d-block">Follow-Up Instructions:</span>
                                            <p class="mb-0 text-dark small" style="white-space: pre-line;">{{ $admission->dischargeSummary->follow_up_instructions }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        @else
                            {{-- Form for Doctor Clinical Clearance --}}
                            @if($canClinical && $admission->status === 'admitted')
                                <form action="{{ route('portal.discharges.clinical-clearance', $admission) }}" method="POST">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-12 col-md-8">
                                            <label class="form-label fw-semibold small text-navy">Discharge Diagnosis <span class="text-danger">*</span></label>
                                            <input type="text" name="discharge_diagnosis" class="form-control" 
                                                value="{{ old('discharge_diagnosis', $admission->diagnosis ?? '') }}" 
                                                placeholder="e.g. Acute Appendicitis (Post-Op Day 3, Uneventful Recovery)" required>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold small text-navy">Condition at Discharge <span class="text-danger">*</span></label>
                                            <select name="condition_at_discharge" class="form-select" required>
                                                <option value="improved" {{ old('condition_at_discharge', 'improved') === 'improved' ? 'selected' : '' }}>Improved (Standard Recovery)</option>
                                                <option value="cured" {{ old('condition_at_discharge') === 'cured' ? 'selected' : '' }}>Cured</option>
                                                <option value="referred" {{ old('condition_at_discharge') === 'referred' ? 'selected' : '' }}>Referred to Higher Center</option>
                                                <option value="dor" {{ old('condition_at_discharge') === 'dor' ? 'selected' : '' }}>Discharge on Request (DOR / DAMA)</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small text-navy">Hospital Course &amp; Clinical Progress</label>
                                            <textarea name="hospital_course" class="form-control" rows="2" 
                                                placeholder="Brief narrative of the clinical progress, vitals stabilization, and recovery while admitted...">{{ old('hospital_course') }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small text-navy">Treatment Provided <span class="text-danger">*</span></label>
                                            <textarea name="treatment_given" class="form-control" rows="2" 
                                                placeholder="Surgical procedures performed, IV fluids, IV antibiotics, supportive therapy given during inpatient stay..." required>{{ old('treatment_given') }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small text-navy">Investigations &amp; Lab Summary</label>
                                            <textarea name="investigations_summary" class="form-control" rows="2" 
                                                placeholder="CBC, USG findings, Electrolytes, Serum Creatinine results summary...">{{ old('investigations_summary') }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small text-navy">Discharge Medications &amp; Dosages <span class="text-danger">*</span></label>
                                            <textarea name="discharge_medications" class="form-control font-monospace" rows="3" 
                                                placeholder="1. Tab. Cefuroxime 500mg — 1 tab twice daily for 5 days&#10;2. Tab. Paracetamol 500mg — 1 tab if pain (PRN)&#10;3. Cap. Omeprazole 20mg — 1 cap daily before breakfast" required>{{ old('discharge_medications') }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small text-navy">Follow-Up Instructions &amp; Warning Signs <span class="text-danger">*</span></label>
                                            <textarea name="follow_up_instructions" class="form-control" rows="2" 
                                                placeholder="Follow-up at OPD in 7 days for suture removal. Report immediately to emergency if high fever, severe abdominal pain, or bleeding occurs." required>{{ old('follow_up_instructions') }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small text-navy">Clinical Notes / Discharge Remarks</label>
                                            <input type="text" name="remarks" class="form-control" 
                                                placeholder="Additional clinical remarks (e.g. Diet advice, activity restriction)..." value="{{ old('remarks') }}">
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-teal">
                                                <i class="bi bi-clipboard2-check me-1"></i>Grant Doctor Clinical Clearance &amp; Save Summary
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <div class="p-3 bg-light rounded text-muted small text-center">
                                    <i class="bi bi-lock me-1"></i>Doctor Clinical Clearance is pending. Only authorized Medical Officers / Super Admins can sign off on clinical discharge.
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- 2. Nursing Discharge Clearance Section --}}
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $admission->nursing_clearance ? 'bg-success' : 'bg-secondary' }} p-2 rounded-2">
                                <i class="bi bi-person-check-fill"></i>
                            </span>
                            <h5 class="fw-bold text-navy mb-0">Nursing Exit Clearance</h5>
                        </div>
                        @if($admission->nursing_clearance && $admission->status === 'admitted' && $canNursing)
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#revokeNursingModal">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Revoke Nursing Clearance
                            </button>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        @if($admission->nursing_clearance)
                            <div class="alert alert-success border-0 bg-success-subtle d-flex align-items-center gap-2 py-2 mb-0">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                <div>
                                    <strong>Nursing Clearance Recorded:</strong> Cleared by
                                    <strong>{{ $admission->nursingClearanceBy ? $admission->nursingClearanceBy->name : 'Staff Nurse' }}</strong>
                                    on {{ $admission->nursing_clearance_at ? $admission->nursing_clearance_at->format('d M Y, h:i A') : 'N/A' }}.
                                    @if($admission->nursing_clearance_notes)
                                        <div class="mt-1 small text-dark fst-italic">"{{ $admission->nursing_clearance_notes }}"</div>
                                    @endif
                                </div>
                            </div>
                        @else
                            @if($canNursing && $admission->status === 'admitted')
                                <form action="{{ route('portal.discharges.nursing-clearance', $admission) }}" method="POST">
                                    @csrf
                                    <div class="row g-3 align-items-end">
                                        <div class="col-12 col-md-8">
                                            <label class="form-label fw-semibold small text-navy">Nursing Exit Checklist &amp; Notes</label>
                                            <input type="text" name="nursing_clearance_notes" class="form-control" 
                                                value="{{ old('nursing_clearance_notes', 'Cannula removed, wound dressing intact, take-home medicines and discharge instructions explained to attendant.') }}" 
                                                placeholder="e.g. IV cannula removed, vital signs stable, belongings handed over...">
                                        </div>
                                        <div class="col-12 col-md-4 text-end">
                                            <button type="submit" class="btn btn-teal w-100">
                                                <i class="bi bi-check-lg me-1"></i>Grant Nursing Clearance
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <div class="p-3 bg-light rounded text-muted small text-center">
                                    <i class="bi bi-lock me-1"></i>Nursing Exit Clearance is pending. Ward nurses can record catheter/cannula removal and checklist sign-off.
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- 3. Financial Clearance Section --}}
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $admission->financial_clearance ? 'bg-success' : 'bg-warning text-dark' }} p-2 rounded-2">
                                <i class="bi bi-cash-coin"></i>
                            </span>
                            <h5 class="fw-bold text-navy mb-0">Financial Settlement &amp; Billing Clearance</h5>
                        </div>
                        @if($admission->financial_clearance && $admission->status === 'admitted' && $canFinancial)
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#revokeFinancialModal">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Revoke Financial Clearance
                            </button>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        {{-- Bill Overview --}}
                        @if($bill)
                            <div class="p-3 bg-light rounded-3 mb-3">
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <span class="text-muted small d-block">Bill Number:</span>
                                        <a href="{{ route('portal.billing.show', $bill) }}" class="fw-bold font-monospace text-teal text-decoration-none">
                                            {{ $bill->invoice_number }} <i class="bi bi-box-arrow-up-right small"></i>
                                        </a>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <span class="text-muted small d-block">Total Billed:</span>
                                        <span class="fw-bold text-navy">&#2547;{{ number_format((float)$bill->total_amount, 2) }}</span>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <span class="text-muted small d-block">Paid to Date:</span>
                                        <span class="fw-bold text-success">&#2547;{{ number_format((float)$bill->paid_amount, 2) }}</span>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <span class="text-muted small d-block">Outstanding Due:</span>
                                        <span class="fw-bold {{ $due > 0 ? 'text-danger' : 'text-success' }}">
                                            &#2547;{{ number_format($due, 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning py-2 mb-3 small">
                                <i class="bi bi-exclamation-triangle me-1"></i>No bill has been generated yet for this admission. Go to <a href="{{ route('portal.billing.create', ['admission_id' => $admission->id]) }}" class="alert-link">Create Inpatient Bill</a> to generate charges.
                            </div>
                        @endif

                        @if($admission->financial_clearance)
                            {{-- Financial Clearance Status --}}
                            @if($admission->financial_exception_allowed)
                                <div class="alert alert-warning border-0 bg-warning-subtle py-2 mb-0">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-shield-exclamation text-warning fs-5"></i>
                                        <div>
                                            <strong>Financial Clearance Granted Under Authorized Dues Exception:</strong>
                                            <div class="small mt-1">Authorized by: <strong>{{ $admission->financialExceptionBy ? $admission->financialExceptionBy->name : 'Administrator' }}</strong> on {{ $admission->financial_clearance_at ? $admission->financial_clearance_at->format('d M Y, h:i A') : 'N/A' }}</div>
                                            <div class="small mt-1 text-danger">Reason: &ldquo;{{ $admission->financial_exception_reason }}&rdquo;</div>
                                            <div class="small text-muted mt-1">Remaining Due Balance: &#2547;{{ number_format($due, 2) }} (Receivable post-discharge)</div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-success border-0 bg-success-subtle d-flex align-items-center gap-2 py-2 mb-0">
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                    <div>
                                        <strong>Financial Clearance Granted (Fully Settled):</strong> Cleared by
                                        <strong>{{ $admission->financialClearanceBy ? $admission->financialClearanceBy->name : 'Cashier' }}</strong>
                                        on {{ $admission->financial_clearance_at ? $admission->financial_clearance_at->format('d M Y, h:i A') : 'N/A' }}.
                                    </div>
                                </div>
                            @endif

                        @else
                            {{-- Financial Clearance Form --}}
                            @if($canFinancial && $admission->status === 'admitted')
                                @if($due == 0)
                                    <form action="{{ route('portal.discharges.financial-clearance', $admission) }}" method="POST">
                                        @csrf
                                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3">
                                            <div>
                                                <div class="fw-bold text-success"><i class="bi bi-check-circle me-1"></i>All inpatient dues are settled (&#2547;0.00 Due).</div>
                                                <div class="small text-muted">Ready to issue financial clearance without any exception.</div>
                                            </div>
                                            <button type="submit" class="btn btn-success">
                                                <i class="bi bi-check2-all me-1"></i>Grant Financial Clearance
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    {{-- Outstanding Due Case: Exception Policy Mandatory --}}
                                    <div class="border border-danger-subtle rounded-3 p-3 bg-danger-subtle mb-3">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                                            <strong class="text-danger">Outstanding Balance: &#2547;{{ number_format($due, 2) }} Due</strong>
                                        </div>
                                        <p class="small text-muted mb-0">
                                            Hospital policy strictly forbids automatic financial clearance when dues exist. To discharge this patient with outstanding balance, an explicitly authorized exception policy is required.
                                        </p>
                                    </div>

                                    @if($canDuesException)
                                        <form action="{{ route('portal.discharges.financial-clearance', $admission) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="allow_dues_exception" value="1">
                                            <div class="card border border-warning rounded-3 p-3 bg-light">
                                                <div class="fw-bold text-navy mb-2"><i class="bi bi-shield-lock me-1"></i>Authorized Dues Exception Authorization</div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold text-navy">
                                                        Mandatory Justification / Exception Reason <span class="text-danger">*</span>
                                                    </label>
                                                    <textarea name="exception_reason" class="form-control" rows="2" 
                                                        placeholder="State the approved reason (e.g. Approved by Medical Director / Post-discharge bank installment promissory signed / Indigent charity concession approved)..." required>{{ old('exception_reason') }}</textarea>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="small text-muted">
                                                        Authorized by: <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->role->display_name ?? 'Administrator' }})
                                                    </div>
                                                    <button type="submit" class="btn btn-warning text-dark fw-semibold">
                                                        <i class="bi bi-shield-check me-1"></i>Authorize Dues Exception &amp; Clear
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    @else
                                        <div class="p-3 bg-light rounded text-muted small text-center">
                                            <i class="bi bi-shield-x me-1"></i>Outstanding dues exist. Only Hospital Administrators / Medical Directors have authority to grant financial clearance under exception policy. Attendant must pay dues at Cashier Desk.
                                            @if($bill)
                                                <div class="mt-2">
                                                    <a href="{{ route('portal.payments.create', ['bill_id' => $bill->id]) }}" class="btn btn-sm btn-teal">
                                                        <i class="bi bi-cash me-1"></i>Collect Remaining Payment (&#2547;{{ number_format($due, 2) }})
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            @else
                                <div class="p-3 bg-light rounded text-muted small text-center">
                                    <i class="bi bi-lock me-1"></i>Financial clearance is pending cashier sign-off or dues settlement.
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- 4. Final Discharge Approval & Bed Release Section --}}
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $admission->status === 'discharged' ? 'bg-primary' : ($admission->isReadyForDischarge() ? 'bg-success' : 'bg-secondary') }} p-2 rounded-2">
                                <i class="bi bi-door-open-fill"></i>
                            </span>
                            <h5 class="fw-bold text-navy mb-0">Final Discharge Approval &amp; Bed Sanitization Release</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @if($admission->status === 'discharged')
                            <div class="alert alert-info border-0 bg-info-subtle py-3 mb-0">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-check-circle-fill text-teal fs-3"></i>
                                    <div>
                                        <h6 class="fw-bold text-navy mb-1">Patient Successfully Discharged</h6>
                                        <div class="small text-muted">
                                            Discharged on <strong>{{ $admission->discharge_date ? $admission->discharge_date->format('d M Y, h:i A') : 'N/A' }}</strong>
                                            by <strong>{{ $admission->dischargedBy ? $admission->dischargedBy->name : 'Authorized Staff' }}</strong>.
                                        </div>
                                        <div class="small text-teal mt-1">
                                            <i class="bi bi-stars me-1"></i>Bed assignment was released; Bed has been moved to <strong>Cleaning &amp; Terminal Disinfection</strong> status.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            @if($admission->isReadyForDischarge())
                                <div class="alert alert-success border-0 bg-success-subtle py-2 mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                        <div>
                                            <strong>All Required Clearances Satisfied!</strong> Doctor Clinical Clearance and Cashier Financial Clearance are both complete.
                                        </div>
                                    </div>
                                </div>

                                @if($canApprove)
                                    <form action="{{ route('portal.discharges.approve', $admission) }}" method="POST">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small fw-semibold text-navy">Discharge Classification <span class="text-danger">*</span></label>
                                                <select name="discharge_type" class="form-select" required>
                                                    <option value="normal" {{ old('discharge_type') === 'normal' ? 'selected' : '' }}>Normal Medical Discharge</option>
                                                    <option value="against_medical_advice" {{ old('discharge_type') === 'against_medical_advice' ? 'selected' : '' }}>Discharged Against Medical Advice (DAMA)</option>
                                                    <option value="transferred" {{ old('discharge_type') === 'transferred' ? 'selected' : '' }}>Transferred to Specialized Center</option>
                                                    <option value="absconded" {{ old('discharge_type') === 'absconded' ? 'selected' : '' }}>Absconded</option>
                                                    <option value="expired" {{ old('discharge_type') === 'expired' ? 'selected' : '' }}>Expired</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small fw-semibold text-navy">Final Exit Notes / Handover Details</label>
                                                <input type="text" name="notes" class="form-control" 
                                                    placeholder="e.g. Discharged in stable condition. Given discharge certificate." value="{{ old('notes') }}">
                                            </div>
                                            <div class="col-12">
                                                <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between border">
                                                    <div>
                                                        <div class="fw-semibold text-navy">Bed Sanitization Release Protocol</div>
                                                        <div class="small text-muted">
                                                            Executing final discharge will instantly release {{ $admission->currentBed ? 'Bed ' . $admission->currentBed->bed_number : 'the bed' }} and transition its status to <strong>Cleaning</strong> for nursing disinfection.
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-navy btn-lg shadow-sm" onclick="return confirm('Execute final discharge and release bed to cleaning status?')">
                                                        <i class="bi bi-box-arrow-right me-1"></i>Approve Final Discharge
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                @else
                                    <div class="p-3 bg-light rounded text-muted small text-center">
                                        <i class="bi bi-lock me-1"></i>Awaiting authorized supervisor/doctor/nurse to confirm patient departure.
                                    </div>
                                @endif
                            @else
                                <div class="p-4 bg-light rounded-3 text-center">
                                    <i class="bi bi-shield-lock text-muted fs-1 d-block mb-2"></i>
                                    <h6 class="fw-bold text-navy mb-1">Final Discharge Gate Locked</h6>
                                    <p class="small text-muted mb-3">Both Clinical and Financial clearances must be completed before final exit approval can be issued.</p>
                                    <div class="d-inline-flex flex-column gap-1 text-start small">
                                        <span class="{{ $admission->clinical_clearance ? 'text-success' : 'text-danger' }}">
                                            <i class="bi {{ $admission->clinical_clearance ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                                            Doctor Clinical Clearance {{ $admission->clinical_clearance ? 'Complete' : 'Pending' }}
                                        </span>
                                        <span class="{{ $admission->financial_clearance ? 'text-success' : 'text-danger' }}">
                                            <i class="bi {{ $admission->financial_clearance ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                                            Cashier Financial Clearance {{ $admission->financial_clearance ? 'Complete' : 'Pending' }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- 5. Complete Discharge History Timeline --}}
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold text-navy mb-0"><i class="bi bi-clock-history me-2"></i>Discharge Audit Trail &amp; History</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light small text-uppercase">
                                    <tr>
                                        <th class="ps-3 py-2">Timestamp</th>
                                        <th class="py-2">Clearance Type</th>
                                        <th class="py-2">Action</th>
                                        <th class="py-2">Performed By</th>
                                        <th class="pe-3 py-2">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($admission->dischargeHistories as $history)
                                        <tr>
                                            <td class="ps-3 py-2 font-monospace small">{{ $history->created_at->format('d M Y, h:i A') }}</td>
                                            <td class="py-2">
                                                <span class="badge bg-light text-navy border font-monospace text-uppercase" style="font-size: 0.7rem;">
                                                    {{ $history->clearance_type }}
                                                </span>
                                            </td>
                                            <td class="py-2">
                                                <span class="badge bg-{{ $history->badge_color }} font-monospace" style="font-size: 0.7rem;">
                                                    {{ $history->action }}
                                                </span>
                                            </td>
                                            <td class="py-2 small fw-semibold text-navy">{{ $history->performedBy ? $history->performedBy->name : 'System' }}</td>
                                            <td class="pe-3 py-2 small text-muted">{{ $history->remarks ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-3 text-muted small">No discharge history events recorded yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column: Patient & Inpatient Summary Sidebar --}}
            <div class="col-12 col-lg-4">
                {{-- Patient Identity Card --}}
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar bg-navy text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 48px; height: 48px;">
                                {{ substr($admission->patient->name, 0, 2) }}
                            </div>
                            <div>
                                <h5 class="fw-bold text-navy mb-0">{{ $admission->patient->name }}</h5>
                                <div class="font-monospace text-muted small">{{ $admission->patient->patient_code }}</div>
                            </div>
                        </div>

                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">Gender / Age:</span>
                                <span class="fw-semibold text-navy">{{ ucfirst($admission->patient->gender) }} / {{ $admission->patient->age ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">Contact Phone:</span>
                                <span class="fw-semibold text-navy font-monospace">{{ $admission->patient->phone ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">Blood Group:</span>
                                <span class="fw-semibold text-danger">{{ $admission->patient->blood_group ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">Admission No:</span>
                                <span class="fw-semibold font-monospace text-navy">{{ $admission->admission_number }}</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">Admission Date:</span>
                                <span class="fw-semibold text-navy">{{ $admission->admission_date ? $admission->admission_date->format('d M Y, h:i A') : 'N/A' }}</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">Length of Stay:</span>
                                <span class="fw-bold text-teal">{{ $admission->stay_duration }}</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">Department:</span>
                                <span class="fw-semibold text-navy">{{ $admission->department ? $admission->department->name : 'N/A' }}</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">Attending Doctor:</span>
                                <span class="fw-semibold text-navy">{{ $admission->attendingDoctor ? $admission->attendingDoctor->name : 'N/A' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Bed Status Card --}}
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-body p-3">
                        <h6 class="fw-bold text-navy mb-2"><i class="bi bi-door-open me-1"></i>Current Inpatient Bed</h6>
                        @if($admission->currentBed)
                            <div class="p-3 bg-light rounded-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-navy fs-5">Bed {{ $admission->currentBed->bed_number }}</span>
                                    <span class="badge bg-teal">{{ ucfirst($admission->currentBed->type) }}</span>
                                </div>
                                <div class="small text-muted">Ward / Room: {{ $admission->currentBed->room_number ?? 'General Ward' }}</div>
                                <div class="small text-muted">Daily Rate: &#2547;{{ number_format((float)$admission->currentBed->daily_rate, 2) }}/day</div>
                            </div>
                        @elseif($admission->status === 'discharged')
                            <div class="p-3 bg-light rounded-3 text-center text-muted small">
                                <i class="bi bi-check-circle text-teal fs-4 d-block mb-1"></i>
                                Bed has been vacated and transferred to Cleaning queue.
                            </div>
                        @else
                            <div class="p-3 bg-light rounded-3 text-center text-muted small">
                                No active bed assigned to this admission.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Clinic Emergency & Policy Contact --}}
                <div class="card border-0 shadow-sm rounded-3 bg-navy text-white">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-1"><i class="bi bi-hospital me-1"></i>Meghna Clinic &amp; Nursing Home</h6>
                        <p class="small text-white-50 mb-2">Shohid Minar Mor, Dinajpur &middot; 24/7 Clinical Care</p>
                        <div class="small text-white-50 border-top border-white-50 pt-2">
                            For financial clearance exceptions or clinical queries, contact Medical Superintendent Desk.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Revoke Modals --}}
    {{-- 1. Revoke Clinical Modal --}}
    <div class="modal fade" id="revokeClinicalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('portal.discharges.revoke', $admission) }}" method="POST">
                    @csrf
                    <input type="hidden" name="clearance_type" value="clinical">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Revoke Doctor Clinical Clearance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">Revoking clinical clearance will withdraw the discharge sign-off and re-lock final discharge until re-cleared.</p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-navy">Reason for Revocation <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="2" placeholder="e.g. Patient developed postoperative fever; requires 24h further antibiotic observation..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Revocation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. Revoke Nursing Modal --}}
    <div class="modal fade" id="revokeNursingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('portal.discharges.revoke', $admission) }}" method="POST">
                    @csrf
                    <input type="hidden" name="clearance_type" value="nursing">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Revoke Nursing Clearance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-navy">Reason for Revocation <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="2" placeholder="State reason..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Revocation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 3. Revoke Financial Modal --}}
    <div class="modal fade" id="revokeFinancialModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('portal.discharges.revoke', $admission) }}" method="POST">
                    @csrf
                    <input type="hidden" name="clearance_type" value="financial">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Revoke Financial Clearance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">Revoking financial clearance will flag the admission as pending payment settlement.</p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-navy">Reason for Revocation <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="2" placeholder="e.g. Unbilled pharmacy charges added; payment required..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Revocation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.portal>
