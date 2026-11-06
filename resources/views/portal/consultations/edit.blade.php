<x-layouts.portal role="doctor" title="Edit Consultation #CNS-{{ str_pad($consultation->id, 5, '0', STR_PAD_LEFT) }} — Meghna Clinic & Nursing Home" activeNav="consultations">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('portal.doctor') }}" class="text-teal text-decoration-none">Doctor Workstation</a></li>
            <li class="breadcrumb-item"><a href="{{ route('portal.consultations.index') }}" class="text-teal text-decoration-none">Consultations</a></li>
            <li class="breadcrumb-item"><a href="{{ route('portal.consultations.show', $consultation) }}" class="text-teal text-decoration-none">#CNS-{{ str_pad($consultation->id, 5, '0', STR_PAD_LEFT) }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit & Correct</li>
        </ol>
    </nav>

    <!-- Header Dossier Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle {{ $consultation->is_finalized ? 'bg-warning text-dark' : 'bg-teal text-white' }} d-flex align-items-center justify-content-center shadow-sm" style="width: 52px; height: 52px; font-size: 1.3rem;">
                        <i class="bi {{ $consultation->is_finalized ? 'bi-shield-exclamation' : 'bi-pencil' }}"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h4 class="fw-bold text-navy mb-0">{{ $patient->name }}</h4>
                            <span class="badge bg-teal font-monospace">{{ $patient->patient_code }}</span>
                            <span class="badge bg-{{ $consultation->status_badge_class }} text-uppercase">{{ $consultation->status }}</span>
                            <span class="badge bg-light text-secondary border font-monospace">#CNS-{{ str_pad($consultation->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <p class="text-muted-hms small mb-0">
                            Visit Ref: <strong class="text-dark font-monospace">{{ $visit->visit_number }}</strong> &bull; 
                            Doctor: <strong class="text-dark">Dr. {{ $doctor->name }}</strong> &bull; 
                            Created: {{ $consultation->created_at->format('d M Y, h:i A') }}
                        </p>
                    </div>
                </div>

                <div>
                    <a href="{{ route('portal.consultations.show', $consultation) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i> Cancel & Return to Dossier
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($consultation->is_finalized)
        <!-- Mandatory Warning Banner for Finalized Medical Records -->
        <div class="alert alert-warning border-warning shadow-sm mb-4 rounded-3 d-flex align-items-start gap-3">
            <i class="bi bi-shield-lock-fill text-warning fs-2 flex-shrink-0"></i>
            <div>
                <h6 class="fw-bold text-navy mb-1">Regulatory Notice: Amending Finalized Medical Record</h6>
                <p class="small mb-0 text-dark">
                    This consultation is officially <strong>finalized</strong> and forms a legal healthcare document. To maintain medical audit integrity and regulatory standards, you must document a clear, clinical reason for any corrections or revisions. Every change (before & after values) will be preserved in the audit log.
                </p>
            </div>
        </div>
    @endif

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

    <form method="POST" action="{{ route('portal.consultations.update', $consultation) }}" id="editConsultationForm">
        @csrf
        @method('PUT')

        @if($consultation->is_finalized)
            <!-- Mandatory Correction Reason Field for Finalized Records -->
            <div class="card border-warning border-2 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-warning-subtle text-warning-emphasis border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-pen-fill text-warning"></i>
                        <h5 class="fw-bold text-navy mb-0">Reason for Clinical Correction (Mandatory)</h5>
                        <span class="text-danger small">*</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <label class="form-label small fw-semibold text-navy">Document clinical rationale for amending this finalized record:</label>
                    <textarea name="correction_reason" class="form-control @error('correction_reason') is-invalid @enderror" rows="2" placeholder="e.g. Revised diagnosis following confirmatory ultrasound; adjusted dosage of cefixime from 200mg to 400mg..." required>{{ old('correction_reason') }}</textarea>
                    @error('correction_reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        @endif

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
                        <textarea name="chief_complaints" class="form-control @error('chief_complaints') is-invalid @enderror" rows="3" required>{{ old('chief_complaints', $consultation->chief_complaints) }}</textarea>
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
                        <textarea name="clinical_notes" class="form-control @error('clinical_notes') is-invalid @enderror" rows="4">{{ old('clinical_notes', $consultation->clinical_notes) }}</textarea>
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
                        @php
                            $diagnosesData = old('diagnoses', $consultation->diagnoses->map(fn($d) => [
                                'id' => $d->id,
                                'condition_name' => $d->condition_name,
                                'icd_code' => $d->icd_code,
                                'diagnosis_type' => $d->diagnosis_type,
                            ])->toArray());
                            if (empty($diagnosesData)) {
                                $diagnosesData = [['condition_name' => '', 'icd_code' => '', 'diagnosis_type' => 'provisional']];
                            }
                        @endphp

                        <div id="diagnosesContainer" class="d-flex flex-column gap-3">
                            @foreach($diagnosesData as $idx => $diag)
                                <div class="diagnosis-row p-3 border rounded-3 bg-light bg-opacity-50 position-relative">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small fw-semibold text-navy mb-1">Condition / Disease Name <span class="text-danger">*</span></label>
                                            <input type="text" name="diagnoses[{{ $idx }}][condition_name]" class="form-control form-control-sm" placeholder="e.g. Acute Appendicitis, Hypertension" value="{{ $diag['condition_name'] ?? '' }}">
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label small fw-semibold text-navy mb-1">ICD-10 (Optional)</label>
                                            <input type="text" name="diagnoses[{{ $idx }}][icd_code]" class="form-control form-control-sm font-monospace" placeholder="e.g. K35.80" value="{{ $diag['icd_code'] ?? '' }}">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small fw-semibold text-navy mb-1">Type</label>
                                            <select name="diagnoses[{{ $idx }}][diagnosis_type]" class="form-select form-select-sm">
                                                <option value="provisional" @selected(($diag['diagnosis_type'] ?? '') === 'provisional')>Provisional</option>
                                                <option value="final" @selected(($diag['diagnosis_type'] ?? '') === 'final')>Final</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-1 text-end mt-md-4">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-diagnosis-btn" title="Remove Diagnosis">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- 4. Prescription & Medication Regimen (Rx) -->
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
                        @php
                            $medsData = old('medications', $prescription ? $prescription->items->map(fn($m) => [
                                'medicine_name' => $m->medicine_name,
                                'dosage' => $m->dosage,
                                'frequency' => $m->frequency,
                                'duration' => $m->duration,
                                'instructions' => $m->instructions,
                            ])->toArray() : []);
                        @endphp

                        <div id="medicationsContainer" class="d-flex flex-column gap-3">
                            @forelse($medsData as $idx => $med)
                                <div class="medication-row p-3 border rounded-3 bg-light bg-opacity-50">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-12 col-md-4">
                                            <label class="form-label small fw-semibold text-navy mb-1">Medicine Name <span class="text-danger">*</span></label>
                                            <input type="text" name="medications[{{ $idx }}][medicine_name]" class="form-control form-control-sm" placeholder="e.g. Tab. Cefixime 200mg" value="{{ $med['medicine_name'] ?? '' }}">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small fw-semibold text-navy mb-1">Dosage</label>
                                            <input type="text" name="medications[{{ $idx }}][dosage]" class="form-control form-control-sm" value="{{ $med['dosage'] ?? '1 Tab' }}">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small fw-semibold text-navy mb-1">Frequency</label>
                                            <input type="text" name="medications[{{ $idx }}][frequency]" class="form-control form-control-sm" value="{{ $med['frequency'] ?? '1+0+1' }}">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small fw-semibold text-navy mb-1">Duration</label>
                                            <input type="text" name="medications[{{ $idx }}][duration]" class="form-control form-control-sm" value="{{ $med['duration'] ?? '5 days' }}">
                                        </div>
                                        <div class="col-6 col-md-2 text-end mt-md-4">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-medication-btn" title="Remove Drug">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <input type="text" name="medications[{{ $idx }}][instructions]" class="form-control form-control-sm" placeholder="Special directions..." value="{{ $med['instructions'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="medication-row p-3 border rounded-3 bg-light bg-opacity-50">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-12 col-md-4">
                                            <label class="form-label small fw-semibold text-navy mb-1">Medicine Name <span class="text-danger">*</span></label>
                                            <input type="text" name="medications[0][medicine_name]" class="form-control form-control-sm" placeholder="e.g. Tab. Cefixime 200mg">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small fw-semibold text-navy mb-1">Dosage</label>
                                            <input type="text" name="medications[0][dosage]" class="form-control form-control-sm" value="1 Tab">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small fw-semibold text-navy mb-1">Frequency</label>
                                            <input type="text" name="medications[0][frequency]" class="form-control form-control-sm" value="1+0+1">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small fw-semibold text-navy mb-1">Duration</label>
                                            <input type="text" name="medications[0][duration]" class="form-control form-control-sm" value="5 days">
                                        </div>
                                        <div class="col-6 col-md-2 text-end mt-md-4">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-medication-btn" title="Remove Drug" style="display: none;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <input type="text" name="medications[0][instructions]" class="form-control form-control-sm" placeholder="Special directions...">
                                        </div>
                                    </div>
                                </div>
                            @endforelse
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
                            <textarea name="advice" class="form-control" rows="3">{{ old('advice', $prescription?->advice) }}</textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-5">
                                <label class="form-label small fw-semibold text-navy">Recommended Follow-up Review Date</label>
                                <input type="date" name="follow_up_date" class="form-control form-control-sm font-monospace" min="{{ date('Y-m-d') }}" value="{{ old('follow_up_date', $prescription?->follow_up_date?->toDateString()) }}">
                            </div>
                            <div class="col-12 col-md-7">
                                <label class="form-label small fw-semibold text-navy">Follow-up Clinical Notes</label>
                                <input type="text" name="follow_up_instructions" class="form-control form-control-sm" placeholder="e.g. Bring repeat lab reports..." value="{{ old('follow_up_instructions') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Toolbar: Save Draft vs Finalize vs Save Correction -->
                <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                            <a href="{{ route('portal.consultations.show', $consultation) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Cancel & Return
                            </a>
                            <div class="d-flex gap-2 w-100 w-sm-auto justify-content-end">
                                @if(!$consultation->is_finalized)
                                    <button type="submit" name="status" value="draft" class="btn btn-outline-navy px-4">
                                        <i class="bi bi-save me-1"></i> Update Draft
                                    </button>
                                    <button type="submit" name="status" value="finalized" class="btn btn-teal px-4 shadow-sm" onclick="return confirm('Finalize this consultation permanently?');">
                                        <i class="bi bi-check2-circle me-1"></i> Finalize Consultation
                                    </button>
                                @else
                                    <input type="hidden" name="status" value="finalized">
                                    <button type="submit" class="btn btn-warning px-4 shadow-sm text-dark fw-semibold" onclick="return confirm('Commit this clinical correction to the permanent medical audit log?');">
                                        <i class="bi bi-shield-check me-1"></i> Save Clinical Correction
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Clinical Dossier Sidebar (4 cols) -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-navy mb-0">
                            <i class="bi bi-shield-exclamation text-teal me-1"></i> Clinical Correction Policies
                        </h6>
                    </div>
                    <div class="card-body p-3 small text-muted-hms">
                        <p class="mb-2">Under Meghna Clinic clinical governance regulations:</p>
                        <ul class="ps-3 mb-3">
                            <li class="mb-1"><strong>Finalized consultations</strong> cannot be deleted by clinical users.</li>
                            <li class="mb-1">All modifications to chief complaints, diagnoses, and drug regimens will be registered with <strong>timestamps, doctor identity, IP, and reason</strong>.</li>
                            <li class="mb-1">Draft consultations can be updated freely until finalized.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Repeater Scripts -->
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Diagnoses dynamic repeater
            let diagIndex = {{ count($diagnosesData) }};
            const diagContainer = document.getElementById('diagnosesContainer');
            const addDiagBtn = document.getElementById('addDiagnosisBtn');

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
            });

            diagContainer.addEventListener('click', function (e) {
                if (e.target.closest('.remove-diagnosis-btn')) {
                    if (diagContainer.querySelectorAll('.diagnosis-row').length > 1) {
                        e.target.closest('.diagnosis-row').remove();
                    } else {
                        alert('At least one diagnosis row must remain.');
                    }
                }
            });

            // Medications dynamic repeater
            let medIndex = {{ count($medsData) ?: 1 }};
            const medContainer = document.getElementById('medicationsContainer');
            const addMedBtn = document.getElementById('addMedicationBtn');

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
                            <input type="text" name="medications[${medIndex}][dosage]" class="form-control form-control-sm" value="1 Tab">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-navy mb-1">Frequency</label>
                            <input type="text" name="medications[${medIndex}][frequency]" class="form-control form-control-sm" value="1+0+1">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-navy mb-1">Duration</label>
                            <input type="text" name="medications[${medIndex}][duration]" class="form-control form-control-sm" value="5 days">
                        </div>
                        <div class="col-6 col-md-2 text-end mt-md-4">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-medication-btn" title="Remove Drug">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="col-12 mt-2">
                            <input type="text" name="medications[${medIndex}][instructions]" class="form-control form-control-sm" placeholder="Special directions...">
                        </div>
                    </div>
                `;
                medContainer.appendChild(newRow);
                medIndex++;
            });

            medContainer.addEventListener('click', function (e) {
                if (e.target.closest('.remove-medication-btn')) {
                    if (medContainer.querySelectorAll('.medication-row').length > 1) {
                        e.target.closest('.medication-row').remove();
                    } else {
                        // Clear inputs if last row
                        const inputs = e.target.closest('.medication-row').querySelectorAll('input');
                        inputs.forEach(i => i.value = '');
                    }
                }
            });
        });
    </script>
    @endpush
</x-layouts.portal>

