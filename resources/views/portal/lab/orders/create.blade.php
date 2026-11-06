<x-layouts.portal role="lab" title="New Diagnostic Laboratory Requisition — Meghna Clinic" activeNav="lab-orders">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <!-- Header -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 52px; height: 52px; font-size: 1.5rem;">
                                <i class="bi bi-file-earmark-plus"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-navy mb-0">Create Diagnostic Test Requisition</h4>
                                <p class="text-muted-hms small mb-0 mt-1">
                                    Doctor's order for in-house laboratory investigations and external reference tests
                                </p>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('portal.lab-orders.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Orders Workbench
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Notification -->
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                        <h6 class="fw-bold mb-0">Please correct the validation errors below:</h6>
                    </div>
                    <ul class="mb-0 small ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('portal.lab-orders.store') }}" method="POST" id="labOrderForm">
                @csrf

                <!-- Patient & Consultation Card -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-person-badge text-teal"></i> Patient & Clinical Consultation
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Patient Selector -->
                            <div class="col-md-6">
                                <label for="patient_id" class="form-label fw-semibold text-navy">
                                    Patient <span class="text-danger">*</span>
                                </label>
                                <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required onchange="handlePatientChange(this)">
                                    <option value="">-- Select Patient --</option>
                                    @foreach($activePatients as $patient)
                                        <option value="{{ $patient->id }}" 
                                            {{ (old('patient_id', $selectedPatient?->id) == $patient->id) ? 'selected' : '' }}
                                            data-code="{{ $patient->patient_code }}"
                                            data-phone="{{ $patient->phone }}">
                                            {{ $patient->name }} ({{ $patient->patient_code }}) &bull; {{ $patient->gender ?? '' }}, {{ $patient->age ? $patient->age . ' yrs' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">Select patient requiring diagnostic tests.</div>
                            </div>

                            <!-- Consultation Selector -->
                            <div class="col-md-6">
                                <label for="consultation_id" class="form-label fw-semibold text-navy">
                                    Linked Clinical Consultation <span class="text-muted small">(Recommended)</span>
                                </label>
                                <select name="consultation_id" id="consultation_id" class="form-select @error('consultation_id') is-invalid @enderror">
                                    <option value="">-- No Direct Consultation Linked --</option>
                                    @foreach($consultations as $consultation)
                                        <option value="{{ $consultation->id }}" {{ (old('consultation_id', $preselectedConsultationId) == $consultation->id) ? 'selected' : '' }}>
                                            Consultation #{{ $consultation->id }} &bull; {{ $consultation->created_at->format('d M Y') }} &bull; Dr. {{ $consultation->doctor?->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('consultation_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">Associates tests with clinical visit history and diagnoses.</div>
                            </div>

                            <!-- Priority -->
                            <div class="col-md-4">
                                <label for="priority" class="form-label fw-semibold text-navy">
                                    Clinical Priority <span class="text-danger">*</span>
                                </label>
                                <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                    <option value="routine" {{ old('priority', 'routine') === 'routine' ? 'selected' : '' }}>Routine (Standard Queue)</option>
                                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent (Same-Day Expedited)</option>
                                    <option value="stat" {{ old('priority') === 'stat' ? 'selected' : '' }}>STAT (Emergency Immediate)</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Source: Internal vs External -->
                            <div class="col-md-4">
                                <label for="source" class="form-label fw-semibold text-navy">
                                    Testing Facility Source <span class="text-danger">*</span>
                                </label>
                                <select name="source" id="source" class="form-select @error('source') is-invalid @enderror" required onchange="togglePartnerSelect(this.value)">
                                    <option value="internal" {{ old('source', 'internal') === 'internal' ? 'selected' : '' }}>In-House (Meghna Clinic Lab)</option>
                                    <option value="external" {{ old('source') === 'external' ? 'selected' : '' }}>External Reference Partner</option>
                                </select>
                                @error('source')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- External Lab Partner (Conditional) -->
                            <div class="col-md-4" id="partnerFieldWrapper" style="{{ old('source') === 'external' ? '' : 'display: none;' }}">
                                <label for="lab_partner_id" class="form-label fw-semibold text-navy">
                                    Partner Reference Laboratory <span class="text-danger">*</span>
                                </label>
                                <select name="lab_partner_id" id="lab_partner_id" class="form-select @error('lab_partner_id') is-invalid @enderror">
                                    <option value="">-- Select Reference Partner --</option>
                                    @foreach($partners as $partner)
                                        <option value="{{ $partner->id }}" {{ old('lab_partner_id') == $partner->id ? 'selected' : '' }}>
                                            {{ $partner->name }} ({{ $partner->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('lab_partner_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Clinical Indication / Notes -->
                            <div class="col-12">
                                <label for="notes" class="form-label fw-semibold text-navy">
                                    Clinical Indication & Specimen Instructions <span class="text-muted small">(Optional)</span>
                                </label>
                                <textarea name="notes" id="notes" rows="2" class="form-control @error('notes') is-invalid @enderror" placeholder="e.g. Suspected acute pyelonephritis, high fever 103F, fasting sample required...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Multiple Diagnostic Tests Selection -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                        <div>
                            <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-card-checklist text-teal"></i> Select Laboratory Tests
                            </h6>
                            <small class="text-muted">Choose one or more diagnostic investigations from the catalog.</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-teal text-white p-2" id="selectedTestsCounter">
                                0 Tests Selected
                            </span>
                            <span class="badge bg-navy text-white p-2" id="totalEstimatedCost">
                                Est. Tariff: ৳0.00
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <!-- Quick Filter Box inside Test Picker -->
                        <div class="mb-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="testCatalogSearch" class="form-control border-start-0" placeholder="Filter tests by name, test code, or specimen type..." onkeyup="filterTestCatalog(this.value)">
                            </div>
                        </div>

                        <!-- Tests Grouped by Category -->
                        @php
                            $grouped = $catalogTests->groupBy('category');
                        @endphp

                        <div class="test-catalog-accordion" id="testAccordion">
                            @foreach($grouped as $category => $tests)
                                <div class="border rounded-3 mb-3 p-3 category-group bg-light-subtle">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold text-navy mb-0 text-capitalize">
                                            <i class="bi bi-folder2-open text-teal me-1"></i> {{ $category }}
                                        </h6>
                                        <span class="badge bg-light text-muted border">{{ $tests->count() }} tests</span>
                                    </div>

                                    <div class="row g-2">
                                        @foreach($tests as $test)
                                            <div class="col-md-6 test-item-wrapper" data-search="{{ strtolower($test->name . ' ' . $test->test_code . ' ' . $test->sample_type . ' ' . $category) }}">
                                                <div class="form-check p-2 border rounded bg-white h-100 d-flex align-items-start gap-2 hover-shadow">
                                                    <input class="form-check-input mt-1 test-checkbox" 
                                                        type="checkbox" 
                                                        name="tests[]" 
                                                        value="{{ $test->id }}" 
                                                        id="test_{{ $test->id }}"
                                                        data-price="{{ $test->price }}"
                                                        data-name="{{ $test->name }}"
                                                        {{ (is_array(old('tests')) && in_array($test->id, old('tests'))) ? 'checked' : '' }}
                                                        onchange="recalculateTotals()">
                                                    <label class="form-check-label w-100 cursor-pointer" for="test_{{ $test->id }}">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <strong class="text-dark">{{ $test->name }}</strong>
                                                            <span class="fw-bold text-teal font-monospace">{{ $test->formatted_price }}</span>
                                                        </div>
                                                        <div class="d-flex flex-wrap align-items-center gap-2 small text-muted mt-1">
                                                            <span class="badge bg-light text-navy border font-monospace">{{ $test->test_code }}</span>
                                                            <span><i class="bi bi-eyedropper text-teal"></i> {{ $test->sample_type }}</span>
                                                            @if($test->availability === 'external')
                                                                <span class="badge bg-warning-subtle text-dark">External</span>
                                                            @endif
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Footer with Actions -->
                    <div class="card-footer bg-white border-top p-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                        <div class="text-muted small">
                            <i class="bi bi-shield-check text-success me-1"></i> Orders will be logged with full traceability and timestamped status history.
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('portal.lab-orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-teal px-4 d-flex align-items-center gap-2 shadow-sm">
                                <i class="bi bi-check2-circle"></i> Authorize & Create Order
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Interactive Script for dynamic test calculation and filtering -->
    <script>
        function togglePartnerSelect(sourceValue) {
            const wrapper = document.getElementById('partnerFieldWrapper');
            const select = document.getElementById('lab_partner_id');
            if (sourceValue === 'external') {
                wrapper.style.display = 'block';
                select.setAttribute('required', 'required');
            } else {
                wrapper.style.display = 'none';
                select.removeAttribute('required');
                select.value = '';
            }
        }

        function filterTestCatalog(query) {
            const q = query.trim().toLowerCase();
            const items = document.querySelectorAll('.test-item-wrapper');
            const categories = document.querySelectorAll('.category-group');

            items.forEach(item => {
                const searchString = item.getAttribute('data-search') || '';
                if (searchString.includes(q)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });

            // Hide empty category boxes
            categories.forEach(cat => {
                const visible = cat.querySelectorAll('.test-item-wrapper:not([style*="display: none"])');
                cat.style.display = visible.length > 0 ? '' : 'none';
            });
        }

        function recalculateTotals() {
            const checkedBoxes = document.querySelectorAll('.test-checkbox:checked');
            let total = 0;
            checkedBoxes.forEach(box => {
                total += parseFloat(box.getAttribute('data-price') || 0);
            });

            const count = checkedBoxes.length;
            document.getElementById('selectedTestsCounter').innerText = count + (count === 1 ? ' Test Selected' : ' Tests Selected');
            document.getElementById('totalEstimatedCost').innerText = 'Est. Tariff: ৳' + total.toFixed(2);
        }

        function handlePatientChange(select) {
            const patientId = select.value;
            if (patientId) {
                // If patient changed, reload consultation dropdown via simple redirect or ajax
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('patient_id', patientId);
                window.location.href = currentUrl.toString();
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            recalculateTotals();
        });
    </script>
</x-layouts.portal>

