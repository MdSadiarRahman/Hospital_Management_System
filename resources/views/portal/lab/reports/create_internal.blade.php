<x-layouts.portal role="lab" title="Record Internal Diagnostic Results — {{ $item->test_name }}" activeNav="lab-reports">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <!-- Header -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 52px; height: 52px; font-size: 1.5rem;">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-navy mb-0">Record In-House Diagnostic Results</h4>
                                <p class="text-muted-hms small mb-0 mt-1">
                                    Investigation: <strong class="text-navy">{{ $item->test_name }}</strong> &bull; Order: 
                                    <span class="font-monospace text-teal">{{ $item->labOrder?->order_number }}</span>
                                </p>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('portal.lab-orders.show', $item->lab_order_id) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Order Dossier
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
                        <h6 class="fw-bold mb-0">Please correct the following errors:</h6>
                    </div>
                    <ul class="mb-0 small ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Patient & Investigation Summary Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted small d-block">Patient Name:</span>
                            <strong class="text-navy">{{ $item->labOrder?->patient?->name }}</strong>
                            <div class="small text-muted font-monospace">{{ $item->labOrder?->patient?->patient_code }}</div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted small d-block">Prescribing Doctor:</span>
                            <strong class="text-navy">Dr. {{ $item->labOrder?->doctor?->name ?? 'Medical Officer' }}</strong>
                            <div class="small text-muted">{{ $item->labOrder?->doctor?->staffProfile?->specialization ?? 'General' }}</div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted small d-block">Specimen Barcode:</span>
                            @if($item->sample)
                                <span class="badge bg-light text-navy border font-monospace fs-6">
                                    <i class="bi bi-upc-scan text-teal"></i> {{ $item->sample->barcode }}
                                </span>
                            @else
                                <span class="text-muted small">Accession pending</span>
                            @endif
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted small d-block">Specimen Type:</span>
                            <span class="badge bg-teal-subtle text-teal border border-teal-subtle">
                                {{ $item->labTest?->sample_type ?? 'Specimen' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ route('portal.lab-reports.store-internal', $item->id) }}" method="POST" id="internalResultForm">
                @csrf

                <!-- Structured Measured Parameters -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-list-check text-teal"></i> Measured Diagnostic Parameters & Values
                            </h6>
                            <small class="text-muted">Enter quantitative/qualitative analyte findings with reference ranges and abnormal flags.</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-teal" onclick="addParameterRow()">
                            <i class="bi bi-plus-circle me-1"></i> Add Parameter Row
                        </button>
                    </div>

                    <div class="table-responsive p-3">
                        <table class="table table-bordered align-middle mb-0" id="parametersTable">
                            <thead class="bg-light small text-uppercase">
                                <tr>
                                    <th style="width: 30%;">Analyte / Parameter <span class="text-danger">*</span></th>
                                    <th style="width: 20%;">Measured Value <span class="text-danger">*</span></th>
                                    <th style="width: 15%;">Unit</th>
                                    <th style="width: 20%;">Biological Reference Interval</th>
                                    <th style="width: 10%;">Flag</th>
                                    <th style="width: 5%;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="parametersTbody">
                                @php
                                    $oldParams = old('parameters', $existingReport?->parameters ?? [
                                        ['parameter' => '', 'value' => '', 'unit' => '', 'reference_range' => '', 'flag' => 'normal']
                                    ]);
                                @endphp

                                @foreach($oldParams as $idx => $param)
                                    <tr class="parameter-row">
                                        <td>
                                            <input type="text" name="parameters[{{ $idx }}][parameter]" class="form-control form-control-sm" 
                                                value="{{ $param['parameter'] ?? '' }}" placeholder="e.g. Hemoglobin, WBC..." required>
                                        </td>
                                        <td>
                                            <input type="text" name="parameters[{{ $idx }}][value]" class="form-control form-control-sm font-monospace fw-bold text-navy" 
                                                value="{{ $param['value'] ?? '' }}" placeholder="Measured result..." required>
                                        </td>
                                        <td>
                                            <input type="text" name="parameters[{{ $idx }}][unit]" class="form-control form-control-sm" 
                                                value="{{ $param['unit'] ?? '' }}" placeholder="g/dL, /cumm...">
                                        </td>
                                        <td>
                                            <input type="text" name="parameters[{{ $idx }}][reference_range]" class="form-control form-control-sm font-monospace" 
                                                value="{{ $param['reference_range'] ?? '' }}" placeholder="e.g. 12.0 - 16.0">
                                        </td>
                                        <td>
                                            <select name="parameters[{{ $idx }}][flag]" class="form-select form-select-sm">
                                                <option value="normal" {{ ($param['flag'] ?? '') === 'normal' ? 'selected' : '' }}>Normal</option>
                                                <option value="high" {{ ($param['flag'] ?? '') === 'high' ? 'selected' : '' }}>High</option>
                                                <option value="low" {{ ($param['flag'] ?? '') === 'low' ? 'selected' : '' }}>Low</option>
                                                <option value="abnormal" {{ ($param['flag'] ?? '') === 'abnormal' ? 'selected' : '' }}>Abnormal</option>
                                                <option value="critical" {{ ($param['flag'] ?? '') === 'critical' ? 'selected' : '' }}>Critical</option>
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger p-1" onclick="removeParameterRow(this)" title="Remove row">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Clinical Findings & Panic Values Alert -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-chat-left-text text-teal"></i> Findings Summary & Panic Values Alert
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="findings_summary" class="form-label fw-semibold text-navy">
                                Technologist Impression / Summary Findings
                            </label>
                            <textarea name="findings_summary" id="findings_summary" rows="3" class="form-control @error('findings_summary') is-invalid @enderror" 
                                placeholder="Microscopic examination notes, morphological impressions, analyzer run notes...">{{ old('findings_summary', $existingReport?->findings_summary) }}</textarea>
                            @error('findings_summary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Critical Panic Value Checkbox -->
                        <div class="p-3 border rounded-3 bg-danger-subtle border-danger-subtle d-flex align-items-center gap-3">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input fs-5" type="checkbox" name="is_critical" value="1" id="is_critical"
                                    {{ old('is_critical', $existingReport?->is_critical) ? 'checked' : '' }}>
                            </div>
                            <label class="form-check-label cursor-pointer" for="is_critical">
                                <strong class="text-danger d-block">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Flag as Critical / Panic Value Alert
                                </strong>
                                <small class="text-muted">
                                    Check this if any measured parameters fall into critical danger thresholds requiring immediate clinical doctor intervention.
                                </small>
                            </label>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer bg-white border-top p-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                        <div class="text-muted small">
                            <i class="bi bi-shield-lock-fill text-success me-1"></i>
                            Submitting records you as uploader and sets status to <strong>Report Received</strong>. A doctor must review and verify the findings.
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('portal.lab-orders.show', $item->lab_order_id) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-teal px-4 shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Save Diagnostic Results
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Script for Dynamic Parameter Rows -->
    <script>
        let rowIndex = {{ count($oldParams) }};

        function addParameterRow() {
            const tbody = document.getElementById('parametersTbody');
            const tr = document.createElement('tr');
            tr.className = 'parameter-row';
            tr.innerHTML = `
                <td>
                    <input type="text" name="parameters[${rowIndex}][parameter]" class="form-control form-control-sm" placeholder="e.g. Platelet Count..." required>
                </td>
                <td>
                    <input type="text" name="parameters[${rowIndex}][value]" class="form-control form-control-sm font-monospace fw-bold text-navy" placeholder="Measured result..." required>
                </td>
                <td>
                    <input type="text" name="parameters[${rowIndex}][unit]" class="form-control form-control-sm" placeholder="e.g. mg/dL, %...">
                </td>
                <td>
                    <input type="text" name="parameters[${rowIndex}][reference_range]" class="form-control form-control-sm font-monospace" placeholder="Reference interval...">
                </td>
                <td>
                    <select name="parameters[${rowIndex}][flag]" class="form-select form-select-sm">
                        <option value="normal" selected>Normal</option>
                        <option value="high">High</option>
                        <option value="low">Low</option>
                        <option value="abnormal">Abnormal</option>
                        <option value="critical">Critical</option>
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger p-1" onclick="removeParameterRow(this)" title="Remove row">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            rowIndex++;
        }

        function removeParameterRow(button) {
            const tbody = document.getElementById('parametersTbody');
            if (tbody.querySelectorAll('.parameter-row').length > 1) {
                button.closest('tr').remove();
            } else {
                alert('At least one diagnostic parameter row must be retained.');
            }
        }
    </script>
</x-layouts.portal>

