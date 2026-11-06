<x-layouts.portal role="doctor" title="Edit Prescription {{ $prescription->prescription_number }} — Meghna Clinic & Nursing Home" activeNav="prescriptions">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('portal.doctor') }}" class="text-teal text-decoration-none">Doctor Workstation</a></li>
            <li class="breadcrumb-item"><a href="{{ route('portal.prescriptions.index') }}" class="text-teal text-decoration-none">Prescriptions</a></li>
            <li class="breadcrumb-item"><a href="{{ route('portal.prescriptions.show', $prescription) }}" class="text-teal text-decoration-none">{{ $prescription->prescription_number }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit & Correct</li>
        </ol>
    </nav>

    <!-- Header Dossier Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle {{ $prescription->is_approved ? 'bg-warning text-dark' : 'bg-teal text-white' }} d-flex align-items-center justify-content-center shadow-sm" style="width: 52px; height: 52px; font-size: 1.3rem;">
                        <i class="bi {{ $prescription->is_approved ? 'bi-shield-exclamation' : 'bi-pencil' }}"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h4 class="fw-bold text-navy mb-0">{{ $patient->name }}</h4>
                            <span class="badge bg-teal font-monospace">{{ $patient->patient_code }}</span>
                            <span class="badge bg-{{ $prescription->status_badge_class }} text-uppercase">{{ $prescription->status }}</span>
                            <span class="badge bg-light text-navy border font-monospace">{{ $prescription->prescription_number }}</span>
                        </div>
                        <p class="text-muted-hms small mb-0">
                            Consultation: <strong class="text-dark font-monospace">#CNS-{{ str_pad($consultation->id, 5, '0', STR_PAD_LEFT) }}</strong> &bull; 
                            Doctor: <strong class="text-dark">Dr. {{ $doctor?->name }}</strong>
                        </p>
                    </div>
                </div>

                <div>
                    <a href="{{ route('portal.prescriptions.show', $prescription) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i> Cancel & Return
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($prescription->is_approved)
        <!-- Mandatory Correction Alert for Approved Records -->
        <div class="alert alert-warning border-warning shadow-sm mb-4 rounded-3 d-flex align-items-start gap-3">
            <i class="bi bi-shield-lock-fill text-warning fs-2 flex-shrink-0"></i>
            <div>
                <h6 class="fw-bold text-navy mb-1">Clinical Audit Policy: Amending Approved Prescription</h6>
                <p class="small mb-0 text-dark">
                    This prescription is <strong>officially approved</strong> and visible to the patient. Modifying medication dosages or items requires documenting an explicit clinical correction reason. All changes are logged into the permanent audit trail.
                </p>
            </div>
        </div>
    @endif

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

    <form method="POST" action="{{ route('portal.prescriptions.update', $prescription) }}" id="editPrescriptionForm">
        @csrf
        @method('PUT')

        @if($prescription->is_approved)
            <!-- Mandatory Correction Reason for Approved Records -->
            <div class="card border-warning border-2 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-warning-subtle text-warning-emphasis border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-pen-fill text-warning"></i>
                        <h5 class="fw-bold text-navy mb-0">Reason for Clinical Correction (Mandatory)</h5>
                        <span class="text-danger small">*</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <label class="form-label small fw-semibold text-navy">Document clinical rationale for amending this prescription:</label>
                    <textarea name="correction_reason" class="form-control @error('correction_reason') is-invalid @enderror" rows="2" placeholder="e.g. Adjusted dosage of cefixime from 200mg to 400mg due to persistence of high fever..." required>{{ old('correction_reason') }}</textarea>
                    @error('correction_reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        @endif

        <div class="row g-4">
            <!-- Left Column: Medications (8 cols) -->
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
                        @php
                            $itemsData = old('items', $prescription->items->map(fn($i) => [
                                'medicine_name' => $i->medicine_name,
                                'dosage' => $i->dosage,
                                'frequency' => $i->frequency,
                                'duration' => $i->duration,
                                'instructions' => $i->instructions,
                            ])->toArray());
                            if (empty($itemsData)) {
                                $itemsData = [['medicine_name' => '', 'dosage' => '1 Tab', 'frequency' => '1+0+1', 'duration' => '5 days', 'instructions' => '']];
                            }
                        @endphp

                        <div id="medicationsContainer" class="d-flex flex-column gap-3">
                            @foreach($itemsData as $idx => $item)
                                <div class="medication-row p-3 border rounded-3 bg-light bg-opacity-50">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-12 col-md-4">
                                            <label class="form-label small fw-semibold text-navy mb-1">Medicine Name <span class="text-danger">*</span></label>
                                            <input type="text" name="items[{{ $idx }}][medicine_name]" class="form-control form-control-sm" placeholder="e.g. Tab. Cefixime 200mg" value="{{ $item['medicine_name'] ?? '' }}" required>
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small fw-semibold text-navy mb-1">Dosage <span class="text-danger">*</span></label>
                                            <input type="text" name="items[{{ $idx }}][dosage]" class="form-control form-control-sm" value="{{ $item['dosage'] ?? '1 Tab' }}" required>
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small fw-semibold text-navy mb-1">Frequency <span class="text-danger">*</span></label>
                                            <input type="text" name="items[{{ $idx }}][frequency]" class="form-control form-control-sm" value="{{ $item['frequency'] ?? '1+0+1' }}" required>
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small fw-semibold text-navy mb-1">Duration <span class="text-danger">*</span></label>
                                            <input type="text" name="items[{{ $idx }}][duration]" class="form-control form-control-sm" value="{{ $item['duration'] ?? '5 days' }}" required>
                                        </div>
                                        <div class="col-6 col-md-2 text-end mt-md-4">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-medication-btn" title="Remove Drug">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <input type="text" name="items[{{ $idx }}][instructions]" class="form-control form-control-sm" placeholder="Special directions..." value="{{ $item['instructions'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
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
                            <textarea name="advice" class="form-control" rows="3">{{ old('advice', $prescription->advice) }}</textarea>
                        </div>

                        <div>
                            <label class="form-label small fw-semibold text-navy">Recommended Follow-up Date</label>
                            <input type="date" name="follow_up_date" class="form-control form-control-sm font-monospace" min="{{ date('Y-m-d') }}" value="{{ old('follow_up_date', $prescription->follow_up_date?->toDateString()) }}" style="max-width: 250px;">
                        </div>
                    </div>
                </div>

                <!-- Action Toolbar -->
                <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                            <a href="{{ route('portal.prescriptions.show', $prescription) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Cancel
                            </a>
                            <div class="d-flex gap-2">
                                @if(!$prescription->is_approved)
                                    <button type="submit" name="status" value="draft" class="btn btn-outline-navy px-4">
                                        <i class="bi bi-save me-1"></i> Update Draft
                                    </button>
                                    <button type="submit" name="status" value="approved" class="btn btn-teal px-4 shadow-sm" onclick="return confirm('Officially approve this prescription?');">
                                        <i class="bi bi-check2-circle me-1"></i> Approve Prescription
                                    </button>
                                @else
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-warning px-4 shadow-sm text-dark fw-semibold" onclick="return confirm('Save this clinical correction to the permanent audit log?');">
                                        <i class="bi bi-shield-check me-1"></i> Save Clinical Correction
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-navy mb-0">
                            <i class="bi bi-shield-exclamation text-teal me-1"></i> Prescription Governance
                        </h6>
                    </div>
                    <div class="card-body p-3 small text-muted-hms">
                        <ul class="ps-3 mb-0">
                            <li class="mb-2"><strong>Approved prescriptions</strong> cannot be deleted by clinical users.</li>
                            <li class="mb-2">Any revisions to drugs, strengths, or intake frequency will register <strong>timestamps, doctor identity, IP, and reason</strong>.</li>
                            <li>Commercial dispensing & sales are deferred to a future phase.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let medIndex = {{ count($itemsData) }};
            const medContainer = document.getElementById('medicationsContainer');
            const addMedBtn = document.getElementById('addMedicationBtn');

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
                            <input type="text" name="items[${medIndex}][dosage]" class="form-control form-control-sm" value="1 Tab" required>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-navy mb-1">Frequency <span class="text-danger">*</span></label>
                            <input type="text" name="items[${medIndex}][frequency]" class="form-control form-control-sm" value="1+0+1" required>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-navy mb-1">Duration <span class="text-danger">*</span></label>
                            <input type="text" name="items[${medIndex}][duration]" class="form-control form-control-sm" value="5 days" required>
                        </div>
                        <div class="col-6 col-md-2 text-end mt-md-4">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-medication-btn" title="Remove Drug">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="col-12 mt-2">
                            <input type="text" name="items[${medIndex}][instructions]" class="form-control form-control-sm" placeholder="Special directions...">
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
                        alert('At least one medicine item must remain in the prescription.');
                    }
                }
            });
        });
    </script>
    @endpush
</x-layouts.portal>

