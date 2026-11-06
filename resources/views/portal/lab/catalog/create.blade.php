<x-layouts.portal role="lab" title="Configure New Diagnostic Test — Meghna Clinic" activeNav="lab-catalog">
    <!-- Header -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 58px; height: 58px; font-size: 1.6rem;">
                        <i class="bi bi-plus-circle-fill"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-navy mb-0">Configure New Diagnostic Test</h4>
                        <p class="text-muted-hms small mb-0 mt-1">
                            Register a new laboratory test in the hospital catalog with pricing, specimen specifications, and turnaround time
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('portal.lab-catalog.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Catalog
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Alerts -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please correct the following errors:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Configuration Form -->
    <form method="POST" action="{{ route('portal.lab-catalog.store') }}">
        @csrf

        <div class="row g-4">
            <!-- Left Column: Core Identifiers & Clinical Parameters -->
            <div class="col-lg-8">
                <!-- 1. Basic Identification -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h6 class="fw-bold text-navy mb-0">
                            <i class="bi bi-tag-fill text-teal me-2"></i>1. Test Identification & Discipline
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Test Name -->
                            <div class="col-md-8">
                                <label for="name" class="form-label small fw-semibold text-navy">Diagnostic Test Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Complete Blood Count (CBC) with ESR, Lipid Profile" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Test Code -->
                            <div class="col-md-4">
                                <label for="code" class="form-label small fw-semibold text-navy">Unique Test Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" id="code" class="form-control font-monospace @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="e.g. CBC01, LFT01" style="text-transform: uppercase;" required>
                                <small class="text-muted" style="font-size: 0.75rem;">Alphanumeric short code (must be unique).</small>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Category / Discipline -->
                            <div class="col-md-6">
                                <label for="category" class="form-label small fw-semibold text-navy">Laboratory Category / Discipline <span class="text-danger">*</span></label>
                                <input type="text" name="category" id="category" list="categoryOptions" class="form-control @error('category') is-invalid @enderror" value="{{ old('category') }}" placeholder="Choose or type category..." required>
                                <datalist id="categoryOptions">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}"></option>
                                    @endforeach
                                </datalist>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sample / Specimen Type -->
                            <div class="col-md-6">
                                <label for="sample_type" class="form-label small fw-semibold text-navy">Specimen / Sample Type</label>
                                <input type="text" name="sample_type" id="sample_type" list="sampleTypeOptions" class="form-control @error('sample_type') is-invalid @enderror" value="{{ old('sample_type', 'Whole Blood') }}" placeholder="e.g. Whole Blood, Serum, Urine">
                                <datalist id="sampleTypeOptions">
                                    @foreach($sampleTypes as $st)
                                        <option value="{{ $st }}"></option>
                                    @endforeach
                                </datalist>
                                @error('sample_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label for="description" class="form-label small fw-semibold text-navy">Clinical Description / Indications</label>
                                <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Brief clinical purpose, measured parameters (e.g. Hb, TC, DC, Platelets, ESR), or diagnosis utility...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Preparation Instructions -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h6 class="fw-bold text-navy mb-0">
                            <i class="bi bi-info-circle-fill text-teal me-2"></i>2. Specimen Handling & Patient Instructions
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-0">
                            <label for="instructions" class="form-label small fw-semibold text-navy">Patient Preparation & Sample Collection Protocol</label>
                            <textarea name="instructions" id="instructions" rows="2" class="form-control @error('instructions') is-invalid @enderror" placeholder="e.g. 10-12 hours overnight fasting required; collect first morning midstream urine; no prior barium intake for radiology...">{{ old('instructions') }}</textarea>
                            <small class="text-muted" style="font-size: 0.75rem;">Instructions displayed to receptionists and nurses during specimen collection.</small>
                            @error('instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Pricing, Processing Time, & Routing -->
            <div class="col-lg-4">
                <!-- 3. Pricing / Standard Charge -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h6 class="fw-bold text-navy mb-0">
                            <i class="bi bi-currency-exchange text-teal me-2"></i>3. Tariff & Pricing
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="standard_charge" class="form-label small fw-semibold text-navy">Standard Charge / Price (BDT) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">৳</span>
                                <input type="number" step="0.01" min="0" name="standard_charge" id="standard_charge" class="form-control form-control-lg fw-bold text-navy @error('standard_charge') is-invalid @enderror" value="{{ old('standard_charge', '0.00') }}" placeholder="0.00" required>
                            </div>
                            <small class="text-muted" style="font-size: 0.75rem;">Official tariff billed to inpatient and OPD accounts.</small>
                            @error('standard_charge')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 4. Turnaround & Availability Routing -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h6 class="fw-bold text-navy mb-0">
                            <i class="bi bi-clock-history text-teal me-2"></i>4. Processing & Routing
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <!-- Turnaround Time -->
                        <div class="mb-3">
                            <label for="turnaround_time" class="form-label small fw-semibold text-navy">Expected Processing Time</label>
                            <input type="text" name="turnaround_time" id="turnaround_time" class="form-control @error('turnaround_time') is-invalid @enderror" value="{{ old('turnaround_time', '4 hours') }}" placeholder="e.g. 2 hours, 24 hours, Same Day">
                            <small class="text-muted" style="font-size: 0.75rem;">Estimated time from sample receipt to validated report.</small>
                            @error('turnaround_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Default Availability -->
                        <div class="mb-3">
                            <label for="default_source" class="form-label small fw-semibold text-navy">Availability & Routing <span class="text-danger">*</span></label>
                            <select name="default_source" id="default_source" class="form-select @error('default_source') is-invalid @enderror" onchange="togglePartnerSelect(this.value)">
                                <option value="internal" @selected(old('default_source') === 'internal' || !old('default_source'))>Internal Laboratory Only</option>
                                <option value="external" @selected(old('default_source') === 'external')>Outsourced / External Reference Lab</option>
                                <option value="both" @selected(old('default_source') === 'both')>Both Internal & External</option>
                            </select>
                            @error('default_source')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- External Reference Partner -->
                        <div class="mb-3 {{ old('default_source') === 'external' || old('default_source') === 'both' ? '' : 'd-none' }}" id="partnerSelectGroup">
                            <label for="lab_partner_id" class="form-label small fw-semibold text-navy">Reference Partner Laboratory</label>
                            <select name="lab_partner_id" id="lab_partner_id" class="form-select @error('lab_partner_id') is-invalid @enderror">
                                <option value="">-- Select Partner Lab (Optional) --</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}" @selected(old('lab_partner_id') == $partner->id)>
                                        {{ $partner->name }} ({{ $partner->code }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted" style="font-size: 0.75rem;">Support future laboratory expansion & outsourced testing.</small>
                            @error('lab_partner_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Active Status Checkbox -->
                        <div class="form-check form-switch mt-3 pt-2 border-top">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1" @checked(old('is_active', true))>
                            <label class="form-check-label fw-semibold text-navy" for="is_active">
                                Active & Available in Catalog
                            </label>
                            <div class="small text-muted" style="font-size: 0.75rem;">
                                Inactive tests will be hidden from doctor order forms.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Bento -->
                <div class="card border-0 shadow-sm rounded-3 bg-white">
                    <div class="card-body p-4">
                        <button type="submit" class="btn btn-teal w-100 py-2 fw-semibold shadow-sm mb-2">
                            <i class="bi bi-check-circle me-1"></i> Save Diagnostic Test
                        </button>
                        <a href="{{ route('portal.lab-catalog.index') }}" class="btn btn-outline-secondary w-100">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function togglePartnerSelect(source) {
            const group = document.getElementById('partnerSelectGroup');
            if (source === 'external' || source === 'both') {
                group.classList.remove('d-none');
            } else {
                group.classList.add('d-none');
            }
        }
    </script>
    @endpush
</x-layouts.portal>

