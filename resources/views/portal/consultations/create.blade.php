<x-layouts.portal role="doctor" title="Author Consultation — {{ $patient->name }} — Meghna Clinic & Nursing Home" activeNav="consultations">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('portal.doctor') }}" class="text-teal text-decoration-none">Doctor Workstation</a></li>
            <li class="breadcrumb-item"><a href="{{ route('portal.consultations.index') }}" class="text-teal text-decoration-none">Consultations</a></li>
            <li class="breadcrumb-item active" aria-current="page">Author Consultation</li>
        </ol>
    </nav>

    <!-- Patient Dossier Header Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-navy text-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 54px; height: 54px; font-size: 1.4rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h4 class="fw-bold text-navy mb-0">{{ $patient->name }}</h4>
                            <span class="badge bg-teal font-monospace">{{ $patient->patient_code }}</span>
                            <span class="badge {{ $visit->visit_type === 'emergency' ? 'bg-danger' : 'bg-primary-subtle text-primary border' }}">
                                {{ ucfirst($visit->visit_type) }} Visit
                            </span>
                            @if($visit->triage_level === 'critical')
                                <span class="badge bg-danger animate-pulse">Critical Triage</span>
                            @elseif($visit->triage_level === 'priority')
                                <span class="badge bg-warning text-dark">Priority Triage</span>
                            @endif
                        </div>
                        <div class="text-muted-hms small d-flex flex-wrap gap-3">
                            <span><i class="bi bi-calendar3 me-1 text-teal"></i> Age: <strong class="text-dark">{{ $patient->formatted_age }}</strong></span>
                            <span><i class="bi bi-gender-ambiguous me-1 text-teal"></i> Gender: <strong class="text-dark">{{ ucfirst($patient->gender) }}</strong></span>
                            <span><i class="bi bi-droplet me-1 text-teal"></i> Blood Group: <strong class="text-dark">{{ $patient->blood_group ?? 'Unknown' }}</strong></span>
                            <span><i class="bi bi-telephone me-1 text-teal"></i> Phone: <strong class="text-dark">{{ $patient->phone }}</strong></span>
                            <span><i class="bi bi-ticket-detailed me-1 text-teal"></i> Visit: <strong class="text-dark font-monospace">{{ $visit->visit_number }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    @if($visit->admission)
                        <div class="bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-3 px-3 py-2 text-center">
                            <div class="small fw-bold"><i class="bi bi-hospital me-1"></i> INPATIENT ADMITTED</div>
                            <div class="font-monospace small">
                                Bed: {{ $visit->admission->currentBedAssignment?->bed?->bed_number ?? 'Bed Assigned' }} &bull; 
                                {{ $visit->admission->department?->name ?? 'Ward' }}
                            </div>
                        </div>
                    @else
                        <div class="bg-light border rounded-3 px-3 py-2 text-center">
                            <div class="small fw-semibold text-muted">Outpatient Consultation</div>
                            <div class="font-monospace text-teal small fw-bold">OPD Desk</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Clinical Governance & Non-Automated Disclaimer Alert -->
    <div class="alert alert-warning border-warning-subtle shadow-sm mb-4 rounded-3 d-flex align-items-center gap-3">
        <i class="bi bi-exclamation-triangle-fill text-warning fs-3 flex-shrink-0"></i>
        <div class="small">
            <strong class="text-navy">Clinical Record & Regulatory Disclaimer:</strong>
            This software strictly records clinical decisions made by authorized healthcare professionals (registered doctors). No automated medical diagnosis is performed. Clinicians are solely responsible for diagnoses, drug selections, dosages, and patient management plans.
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger shadow-sm mb-4 rounded-3">
            <div class="fw-bold mb-1"><i class="bi bi-x-circle me-1"></i> Please resolve the following clinical data issues:</div>
            <ul class="mb-0 small ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('portal.consultations.store') }}" id="consultationForm">
        @csrf
        <input type="hidden" name="visit_id" value="{{ $visit->id }}">

        <div class="row g-4">
            <!-- Left Clinical Authoring Column (8 cols) -->
            <div class="col-12 col-lg-8">
                <!-- 1. Presenting Chief Complaints -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-teal-subtle text-teal p-1 px-2 fw-bold font-monospace">1</span>
                            <h5 class="fw-bold text-navy mb-0">Chief Complaints (Mandatory)</h5>
                            <span class="text-danger small">*</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted-hms small mb-2">Record presenting symptoms, duration, and patient-reported complaints (e.g., "Severe lower abdominal pain for 3 days, intermittent fever, nausea").</p>
                        <textarea name="chief_complaints" class="form-control @error('chief_complaints') is-invalid @enderror" rows="3" placeholder="Enter chief presenting complaints, onset, and duration..." required>{{ old('chief_complaints') }}</textarea>
                        @error('chief_complaints')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- 2. Clinical Notes & Physical Examination Findings -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-navy-subtle text-navy p-1 px-2 fw-bold font-monospace">2</span>
                            <h5 class="fw-bold text-navy mb-0">Clinical Notes & Physical Examination</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted-hms small mb-2">Document systemic examination, vital sign interpretations, abdominal/pelvic findings, surgical site evaluations, or physician observations.</p>
                        <textarea name="clinical_notes" class="form-control @error('clinical_notes') is-invalid @enderror" rows="4" placeholder="Vitals review, systemic examination, tenderness, chest auscultation, wound status...">{{ old('clinical_notes') }}</textarea>
                        @error('clinical_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- 3. Structured Diagnoses (Dynamic Repeater) -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-teal-subtle text-teal p-1 px-2 fw-bold font-monospace">3</span>
                            <h5 class="fw-bold text-navy mb-0">Clinical Diagnoses</h5>
                            <span class="badge bg-light text-muted border">Mandatory for Finalization</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-teal d-flex align-items-center gap-1" id="addDiagnosisBtn">
                            <i class="bi bi-plus-circle"></i> Add Diagnosis
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted-hms small mb-3">Record primary and differential diagnoses. Specify whether each diagnosis is Provisional (under investigation) or Final (confirmed clinical diagnosis).</p>
                        
                        <div id="diagnosesContainer" class="d-flex flex-column gap-3">
                            <!-- Template/Initial Row -->
                            <div class="diagnosis-row p-3 border rounded-3 bg-light bg-opacity-50 position-relative">
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-semibold text-navy mb-1">Condition / Disease Name <span class="text-danger">*</span></label>
                                        <input type="text" name="diagnoses[0][condition_name]" class="form-control form-control-sm" placeholder="e.g. Acute Appendicitis, Hypertension" value="{{ old('diagnoses.0.condition_name') }}">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label small fw-semibold text-navy mb-1">ICD-10 (Optional)</label>
                                        <input type="text" name="diagnoses[0][icd_code]" class="form-control form-control-sm font-monospace" placeholder="e.g. K35.80" value="{{ old('diagnoses.0.icd_code') }}">
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label class="form-label small fw-semibold text-navy mb-1">Type</label>
                                        <select name="diagnoses[0][diagnosis_type]" class="form-select form-select-sm">
                                            <option value="provisional" @selected(old('diagnoses.0.diagnosis_type') === 'provisional')>Provisional</option>
                                            <option value="final" @selected(old('diagnoses.0.diagnosis_type') === 'final')>Final</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-1 text-end mt-md-4">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-diagnosis-btn" title="Remove Diagnosis" style="display: none;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Prescription & Medication Regimen (Dynamic Repeater) -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-navy-subtle text-navy p-1 px-2 fw-bold font-monospace">4</span>
                            <h5 class="fw-bold text-navy mb-0">Prescription & Medication Regimen (Rx)</h5>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-teal d-flex align-items-center gap-1" id="addMedicationBtn">
                            <i class="bi bi-plus-circle"></i> Add Drug
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted-hms small mb-3">Prescribe pharmacological therapy. Pharmacy dispensing and inventory are handled in later phases; clinical records are captured here.</p>

                        <div id="medicationsContainer" class="d-flex flex-column gap-3">
                            <div class="medication-row p-3 border rounded-3 bg-light bg-opacity-50">
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small fw-semibold text-navy mb-1">Medicine Name <span class="text-danger">*</span></label>
                                        <input type="text" name="medications[0][medicine_name]" class="form-control form-control-sm" placeholder="e.g. Tab. Cefixime 200mg" value="{{ old('medications.0.medicine_name') }}">
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label class="form-label small fw-semibold text-navy mb-1">Dosage</label>
                                        <input type="text" name="medications[0][dosage]" class="form-control form-control-sm" placeholder="e.g. 1 Tablet" value="{{ old('medications.0.dosage', '1 Tab') }}">
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label class="form-label small fw-semibold text-navy mb-1">Frequency</label>
                                        <input type="text" name="medications[0][frequency]" class="form-control form-control-sm" placeholder="e.g. 1+0+1" value="{{ old('medications.0.frequency', '1+0+1') }}">
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label class="form-label small fw-semibold text-navy mb-1">Duration</label>
                                        <input type="text" name="medications[0][duration]" class="form-control form-control-sm" placeholder="e.g. 5 days" value="{{ old('medications.0.duration', '5 days') }}">
                                    </div>
                                    <div class="col-6 col-md-2 text-end mt-md-4">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-medication-btn" title="Remove Drug" style="display: none;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <input type="text" name="medications[0][instructions]" class="form-control form-control-sm" placeholder="Special directions: e.g. After meals, take with full glass of water..." value="{{ old('medications.0.instructions') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Treatment Plan & Follow-up Instructions -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-teal-subtle text-teal p-1 px-2 fw-bold font-monospace">5</span>
                            <h5 class="fw-bold text-navy mb-0">Treatment Advice & Follow-up Instructions</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-navy">Non-Pharmacological Treatment Advice & Diet</label>
                            <textarea name="advice" class="form-control" rows="3" placeholder="Dietary restrictions, wound care instructions, bed rest, hydration instructions, red-flag symptoms for immediate emergency return...">{{ old('advice') }}</textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-5">
                                <label class="form-label small fw-semibold text-navy">Recommended Follow-up Review Date</label>
                                <input type="date" name="follow_up_date" class="form-control form-control-sm font-monospace" min="{{ date('Y-m-d') }}" value="{{ old('follow_up_date') }}">
                                <div class="form-text small">Select review date if follow-up is recommended.</div>
                            </div>
                            <div class="col-12 col-md-7">
                                <label class="form-label small fw-semibold text-navy">Follow-up Clinical Notes / Tests to Bring</label>
                                <input type="text" name="follow_up_instructions" class="form-control form-control-sm" placeholder="e.g. Bring repeat CBC and USG report after 7 days" value="{{ old('follow_up_instructions') }}">
                            </div>
                        </div>

                        @if($visit->admission)
                            <hr class="my-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <h6 class="fw-bold text-navy mb-2"><i class="bi bi-clipboard2-pulse text-teal me-1"></i> Inpatient Ward Treatment Plan Order</h6>
                                <p class="small text-muted mb-2">Because this patient is admitted to an inpatient bed ({{ $visit->admission->currentBedAssignment?->bed?->bed_number }}), enter clinical orders for nursing staff and ward management below:</p>
                                <textarea name="inpatient_treatment_plan" class="form-control form-control-sm" rows="3" placeholder="IV fluid protocol, hourly BP/Pulse monitoring, oxygen support, wound dressing frequency...">{{ old('inpatient_treatment_plan') }}</textarea>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Action Toolbar: Save Draft vs Finalize -->
                <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                            <a href="{{ route('portal.doctor') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Cancel & Return
                            </a>
                            <div class="d-flex gap-2 w-100 w-sm-auto justify-content-end">
                                <button type="submit" name="status" value="draft" class="btn btn-outline-navy px-4">
                                    <i class="bi bi-save me-1"></i> Save as Draft
                                </button>
                                <button type="submit" name="status" value="finalized" class="btn btn-teal px-4 shadow-sm" onclick="return confirm('Are you sure you want to finalize this clinical consultation? Once finalized, amendments will require documented correction reasons.');">
                                    <i class="bi bi-check2-circle me-1"></i> Finalize Consultation
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Clinical Dossier Sidebar (4 cols) -->
            <div class="col-12 col-lg-4">
                <!-- Past Medical History & Previous Consultations -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-navy mb-0">
                            <i class="bi bi-clock-history text-teal me-1"></i> Patient Clinical History
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        @if($pastConsultations->isNotEmpty())
                            <div class="d-flex flex-column gap-3">
                                @foreach($pastConsultations as $past)
                                    <div class="p-3 border rounded-3 bg-light">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small fw-bold text-navy">Dr. {{ $past->doctor->name }}</span>
                                            <span class="badge bg-{{ $past->status_badge_class }}" style="font-size: 0.65rem;">{{ $past->status }}</span>
                                        </div>
                                        <div class="small text-muted font-monospace mb-2">{{ $past->created_at->format('d M Y, h:i A') }}</div>
                                        
                                        <div class="small mb-1">
                                            <strong>Complaints:</strong> {{ Str::limit($past->chief_complaints, 60) }}
                                        </div>

                                        @if($past->diagnoses->isNotEmpty())
                                            <div class="small mb-2">
                                                <strong>Diagnoses:</strong>
                                                @foreach($past->diagnoses as $d)
                                                    <span class="badge bg-white text-dark border">{{ $d->condition_name }}</span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <a href="{{ route('portal.consultations.show', $past) }}" target="_blank" class="small text-teal text-decoration-none">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> View Past Record
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4 text-muted small">
                                <i class="bi bi-journal-medical fs-2 text-secondary opacity-50 d-block mb-1"></i>
                                No previous consultation history on record for this patient.
                            </div>
                        @endif

                        @if($pastVisits->isNotEmpty())
                            <div class="mt-3 pt-3 border-top">
                                <div class="small fw-bold text-navy mb-2">Recent Visits Log:</div>
                                <ul class="list-unstyled mb-0 small">
                                    @foreach($pastVisits as $pv)
                                        <li class="d-flex justify-content-between py-1 border-bottom">
                                            <span class="font-monospace">{{ $pv->visit_number }} ({{ ucfirst($pv->visit_type) }})</span>
                                            <span class="text-muted">{{ $pv->visit_date->format('d/m/Y') }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Emergency & Triage Summary -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-navy mb-0">
                            <i class="bi bi-info-circle text-teal me-1"></i> Visit Details
                        </h6>
                    </div>
                    <div class="card-body p-3 small">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Visit Reference:</span>
                            <span class="font-monospace fw-bold text-dark">{{ $visit->visit_number }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Intake Timestamp:</span>
                            <span>{{ $visit->visit_date->format('d M Y, h:i A') }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Visit Category:</span>
                            <span class="fw-semibold">{{ ucfirst($visit->visit_type) }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">Triage Priority:</span>
                            <span class="fw-bold text-{{ $visit->triage_level === 'critical' ? 'danger' : ($visit->triage_level === 'priority' ? 'warning' : 'success') }}">
                                {{ ucfirst($visit->triage_level) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Dynamic Repeater Scripts -->
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Diagnoses Dynamic Repeater
            let diagIndex = {{ count(old('diagnoses', [0])) }};
            const diagContainer = document.getElementById('diagnosesContainer');
            const addDiagBtn = document.getElementById('addDiagnosisBtn');

            function updateDiagRemoveButtons() {
                const rows = diagContainer.querySelectorAll('.diagnosis-row');
                rows.forEach((row, idx) => {
                    const removeBtn = row.querySelector('.remove-diagnosis-btn');
                    if (removeBtn) {
                        removeBtn.style.display = rows.length > 1 ? 'inline-block' : 'none';
                    }
                });
            }

            addDiagBtn.addEventListener('click', function () {
                const newRow = document.createElement('div');
                newRow.className = 'diagnosis-row p-3 border rounded-3 bg-light bg-opacity-50 position-relative';
                newRow.innerHTML = `
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-navy mb-1">Condition / Disease Name <span class="text-danger">*</span></label>
                            <input type="text" name="diagnoses[${diagIndex}][condition_name]" class="form-control form-control-sm" placeholder="e.g. Acute Appendicitis, Hypertension" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-semibold text-navy mb-1">ICD-10 (Optional)</label>
                            <input type="text" name="diagnoses[${diagIndex}][icd_code]" class="form-control form-control-sm font-monospace" placeholder="e.g. K35.80">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-navy mb-1">Type</label>
                            <select name="diagnoses[${diagIndex}][diagnosis_type]" class="form-select form-select-sm">
                                <option value="provisional">Provisional</option>
                                <option value="final">Final</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-1 text-end mt-md-4">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-diagnosis-btn" title="Remove Diagnosis">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                diagContainer.appendChild(newRow);
                diagIndex++;
                updateDiagRemoveButtons();
            });

            diagContainer.addEventListener('click', function (e) {
                if (e.target.closest('.remove-diagnosis-btn')) {
                    e.target.closest('.diagnosis-row').remove();
                    updateDiagRemoveButtons();
                }
            });

            updateDiagRemoveButtons();

            // 2. Medications Dynamic Repeater
            let medIndex = {{ count(old('medications', [0])) }};
            const medContainer = document.getElementById('medicationsContainer');
            const addMedBtn = document.getElementById('addMedicationBtn');

            function updateMedRemoveButtons() {
                const rows = medContainer.querySelectorAll('.medication-row');
                rows.forEach((row) => {
                    const removeBtn = row.querySelector('.remove-medication-btn');
                    if (removeBtn) {
                        removeBtn.style.display = rows.length > 1 ? 'inline-block' : 'none';
                    }
                });
            }

            addMedBtn.addEventListener('click', function () {
                const newRow = document.createElement('div');
                newRow.className = 'medication-row p-3 border rounded-3 bg-light bg-opacity-50';
                newRow.innerHTML = `
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-navy mb-1">Medicine Name <span class="text-danger">*</span></label>
                            <input type="text" name="medications[${medIndex}][medicine_name]" class="form-control form-control-sm" placeholder="e.g. Tab. Cefixime 200mg" required>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-navy mb-1">Dosage</label>
                            <input type="text" name="medications[${medIndex}][dosage]" class="form-control form-control-sm" placeholder="e.g. 1 Tablet" value="1 Tab">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-navy mb-1">Frequency</label>
                            <input type="text" name="medications[${medIndex}][frequency]" class="form-control form-control-sm" placeholder="e.g. 1+0+1" value="1+0+1">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-navy mb-1">Duration</label>
                            <input type="text" name="medications[${medIndex}][duration]" class="form-control form-control-sm" placeholder="e.g. 5 days" value="5 days">
                        </div>
                        <div class="col-6 col-md-2 text-end mt-md-4">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-medication-btn" title="Remove Drug">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="col-12 mt-2">
                            <input type="text" name="medications[${medIndex}][instructions]" class="form-control form-control-sm" placeholder="Special directions: e.g. After meals, take with full glass of water...">
                        </div>
                    </div>
                `;
                medContainer.appendChild(newRow);
                medIndex++;
                updateMedRemoveButtons();
            });

            medContainer.addEventListener('click', function (e) {
                if (e.target.closest('.remove-medication-btn')) {
                    e.target.closest('.medication-row').remove();
                    updateMedRemoveButtons();
                }
            });

            updateMedRemoveButtons();
        });
    </script>
    @endpush
</x-layouts.portal>

