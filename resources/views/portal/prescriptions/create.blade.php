<x-layouts.portal role="doctor" title="Create Prescription — {{ $patient->name }} — Meghna Clinic & Nursing Home" activeNav="prescriptions">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('portal.doctor') }}" class="text-teal text-decoration-none">Doctor Workstation</a></li>
            <li class="breadcrumb-item"><a href="{{ route('portal.prescriptions.index') }}" class="text-teal text-decoration-none">Prescriptions</a></li>
            <li class="breadcrumb-item active" aria-current="page">New Prescription</li>
        </ol>
    </nav>

    <!-- Consultation & Patient Summary Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 54px; height: 54px; font-size: 1.4rem;">
                        <i class="bi bi-capsule"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h4 class="fw-bold text-navy mb-0">{{ $patient->name }}</h4>
                            <span class="badge bg-teal font-monospace">{{ $patient->patient_code }}</span>
                            <span class="badge bg-navy font-monospace">Consultation #CNS-{{ str_pad($consultation->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="text-muted-hms small d-flex flex-wrap gap-3">
                            <span><i class="bi bi-person-badge text-teal me-1"></i> Prescribing Doctor: <strong class="text-dark">Dr. {{ $doctor->name }}</strong></span>
                            <span><i class="bi bi-ticket-detailed text-teal me-1"></i> Visit: <strong class="text-dark font-monospace">{{ $consultation->visit?->visit_number }}</strong></span>
                            <span><i class="bi bi-calendar3 text-teal me-1"></i> Age/Sex: <strong class="text-dark">{{ $patient->formatted_age }}, {{ ucfirst($patient->gender) }}</strong></span>
                            <span><i class="bi bi-droplet text-teal me-1"></i> Blood: <strong class="text-danger">{{ $patient->blood_group ?? 'Unknown' }}</strong></span>
                        </div>
                    </div>
                </div>

                <div>
                    <a href="{{ route('portal.consultations.show', $consultation) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Return to Consultation Dossier
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Legal & Scope Alert -->
    <div class="alert alert-warning border-warning-subtle shadow-sm mb-4 rounded-3 d-flex align-items-center gap-3">
        <i class="bi bi-shield-check text-warning fs-3 flex-shrink-0"></i>
        <div class="small">
            <strong class="text-navy">Clinical Responsibility:</strong>
            Prescriptions issued in this system constitute binding medical directives authored by registered practitioners. Only authorized doctors can approve prescriptions.
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger shadow-sm mb-4 rounded-3">
            <div class="fw-bold mb-1"><i class="bi bi-x-circle me-1"></i> Please resolve the following issues:</div>
            <ul class="mb-0 small ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('portal.prescriptions.store') }}" id="prescriptionForm">
        @csrf
        <input type="hidden" name="consultation_id" value="{{ $consultation->id }}">

        <div class="row g-4">
            <!-- Left Column: Medications & Regimen (8 cols) -->
            <div class="col-12 col-lg-8">
                <!-- Medications Repeater Card -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-4 text-teal fw-bold" style="font-family: Georgia, serif;">℞</span>
                            <h5 class="fw-bold text-navy mb-0">Pharmacological Regimen & Drugs</h5>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-teal d-flex align-items-center gap-1" id="addMedicationBtn">
                            <i class="bi bi-plus-circle"></i> Add Drug
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted-hms small mb-3">Record drug names, strengths, dosages, intake intervals, and duration.</p>

                        <div id="medicationsContainer" class="d-flex flex-column gap-3">
                            <div class="medication-row p-3 border rounded-3 bg-light bg-opacity-50">
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small fw-semibold text-navy mb-1">Medicine Name <span class="text-danger">*</span></label>
                                        <input type="text" name="items[0][medicine_name]" class="form-control form-control-sm" placeholder="e.g. Tab. Cefixime 200mg" value="{{ old('items.0.medicine_name') }}" required>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label class="form-label small fw-semibold text-navy mb-1">Dosage <span class="text-danger">*</span></label>
                                        <input type="text" name="items[0][dosage]" class="form-control form-control-sm" placeholder="e.g. 1 Tab" value="{{ old('items.0.dosage', '1 Tab') }}" required>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label class="form-label small fw-semibold text-navy mb-1">Frequency <span class="text-danger">*</span></label>
                                        <input type="text" name="items[0][frequency]" class="form-control form-control-sm" placeholder="e.g. 1+0+1" value="{{ old('items.0.frequency', '1+0+1') }}" required>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label class="form-label small fw-semibold text-navy mb-1">Duration <span class="text-danger">*</span></label>
                                        <input type="text" name="items[0][duration]" class="form-control form-control-sm" placeholder="e.g. 5 days" value="{{ old('items.0.duration', '5 days') }}" required>
                                    </div>
                                    <div class="col-6 col-md-2 text-end mt-md-4">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-medication-btn" title="Remove Drug" style="display: none;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <input type="text" name="items[0][instructions]" class="form-control form-control-sm" placeholder="Special directions: e.g. After meals, take with plenty of water..." value="{{ old('items.0.instructions') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advice & Follow-up Card -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold text-navy mb-0">Non-Pharmacological Advice & Review Date</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-navy">Clinical Advice & Instructions</label>
                            <textarea name="advice" class="form-control" rows="3" placeholder="Dietary instructions, physical rest, fluid intake, wound dressing guidelines, emergency return warnings...">{{ old('advice') }}</textarea>
                        </div>

                        <div>
                            <label class="form-label small fw-semibold text-navy">Recommended Follow-up Date</label>
                            <input type="date" name="follow_up_date" class="form-control form-control-sm font-monospace" min="{{ date('Y-m-d') }}" value="{{ old('follow_up_date') }}" style="max-width: 250px;">
                        </div>
                    </div>
                </div>

                <!-- Action Toolbar -->
                <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                            <a href="{{ route('portal.consultations.show', $consultation) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Cancel
                            </a>
                            <div class="d-flex gap-2">
                                <button type="submit" name="status" value="draft" class="btn btn-outline-navy px-4">
                                    <i class="bi bi-save me-1"></i> Save as Draft
                                </button>
                                <button type="submit" name="status" value="approved" class="btn btn-teal px-4 shadow-sm" onclick="return confirm('Officially approve this prescription? Approved prescriptions become visible to the patient and legal records.');">
                                    <i class="bi bi-check2-circle me-1"></i> Approve Prescription
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Consultation Context (4 cols) -->
            <div class="col-12 col-lg-4">
                <!-- Diagnoses Context -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-navy mb-0">
                            <i class="bi bi-journal-medical text-teal me-1"></i> Active Clinical Diagnoses
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        @if($diagnoses->isNotEmpty())
                            <ul class="list-unstyled mb-0 small">
                                @foreach($diagnoses as $diag)
                                    <li class="p-2 border rounded mb-2 bg-light">
                                        <div class="fw-bold text-navy">{{ $diag->condition_name }}</div>
                                        <div class="text-muted font-monospace" style="font-size: 0.75rem;">
                                            Type: {{ ucfirst($diag->diagnosis_type) }} @if($diag->icd_code)&bull; ICD: {{ $diag->icd_code }}@endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="text-muted small fst-italic">No diagnoses recorded for this consultation.</div>
                        @endif
                    </div>
                </div>

                <!-- Complaints Context -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-navy mb-0">
                            <i class="bi bi-chat-left-dots text-teal me-1"></i> Presenting Complaints
                        </h6>
                    </div>
                    <div class="card-body p-3 small text-muted">
                        <p class="mb-0" style="white-space: pre-line;">{{ $consultation->chief_complaints }}</p>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let medIndex = {{ count(old('items', [0])) }};
            const medContainer = document.getElementById('medicationsContainer');
            const addMedBtn = document.getElementById('addMedicationBtn');

            function updateRemoveButtons() {
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
                            <input type="text" name="items[${medIndex}][medicine_name]" class="form-control form-control-sm" placeholder="e.g. Tab. Cefixime 200mg" required>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-navy mb-1">Dosage <span class="text-danger">*</span></label>
                            <input type="text" name="items[${medIndex}][dosage]" class="form-control form-control-sm" placeholder="e.g. 1 Tab" value="1 Tab" required>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-navy mb-1">Frequency <span class="text-danger">*</span></label>
                            <input type="text" name="items[${medIndex}][frequency]" class="form-control form-control-sm" placeholder="e.g. 1+0+1" value="1+0+1" required>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-navy mb-1">Duration <span class="text-danger">*</span></label>
                            <input type="text" name="items[${medIndex}][duration]" class="form-control form-control-sm" placeholder="e.g. 5 days" value="5 days" required>
                        </div>
                        <div class="col-6 col-md-2 text-end mt-md-4">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-medication-btn" title="Remove Drug">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="col-12 mt-2">
                            <input type="text" name="items[${medIndex}][instructions]" class="form-control form-control-sm" placeholder="Special directions: e.g. After meals...">
                        </div>
                    </div>
                `;
                medContainer.appendChild(newRow);
                medIndex++;
                updateRemoveButtons();
            });

            medContainer.addEventListener('click', function (e) {
                if (e.target.closest('.remove-medication-btn')) {
                    if (medContainer.querySelectorAll('.medication-row').length > 1) {
                        e.target.closest('.medication-row').remove();
                        updateRemoveButtons();
                    }
                }
            });

            updateRemoveButtons();
        });
    </script>
    @endpush
</x-layouts.portal>

