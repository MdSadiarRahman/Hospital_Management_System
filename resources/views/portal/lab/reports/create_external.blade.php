<x-layouts.portal role="lab" title="Upload External Diagnostic Report — {{ $item->test_name }}" activeNav="lab-reports">
    <div class="row justify-content-center">
        <div class="col-xl-9">
            <!-- Header -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center shadow-sm" style="width: 52px; height: 52px; font-size: 1.5rem;">
                                <i class="bi bi-cloud-arrow-up"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-navy mb-0">Upload External Diagnostic Report</h4>
                                <p class="text-muted-hms small mb-0 mt-1">
                                    Outsourced investigation: <strong class="text-navy">{{ $item->test_name }}</strong> &bull; Order: 
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

            <form action="{{ route('portal.lab-reports.store-external', $item->id) }}" method="POST" enctype="multipart/form-data" id="externalReportForm">
                @csrf

                <!-- External Source & Partner Details -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-buildings text-teal"></i> External Laboratory Partner & Reference Details
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Patient Summary -->
                            <div class="col-md-6">
                                <span class="text-muted small d-block">Patient:</span>
                                <strong class="text-navy">{{ $item->labOrder?->patient?->name }}</strong>
                                <span class="small text-muted font-monospace">({{ $item->labOrder?->patient?->patient_code }})</span>
                            </div>

                            <!-- Prescribing Clinician -->
                            <div class="col-md-6">
                                <span class="text-muted small d-block">Prescribing Doctor:</span>
                                <strong class="text-navy">Dr. {{ $item->labOrder?->doctor?->name ?? 'Medical Officer' }}</strong>
                            </div>

                            <!-- Partner Laboratory Dropdown -->
                            <div class="col-md-6">
                                <label for="lab_partner_id" class="form-label fw-semibold text-navy">
                                    External Diagnostic Center / Reference Lab <span class="text-danger">*</span>
                                </label>
                                <select name="lab_partner_id" id="lab_partner_id" class="form-select @error('lab_partner_id') is-invalid @enderror" required>
                                    <option value="">-- Select Partner Center --</option>
                                    @foreach($partners as $partner)
                                        <option value="{{ $partner->id }}" 
                                            {{ old('lab_partner_id', $existingReport?->lab_partner_id ?? $item->labOrder?->lab_partner_id) == $partner->id ? 'selected' : '' }}>
                                            {{ $partner->name }} ({{ $partner->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('lab_partner_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- External Accession / Reference # -->
                            <div class="col-md-6">
                                <label for="external_reference_number" class="form-label fw-semibold text-navy">
                                    External Sample / Lab Report Reference ID
                                </label>
                                <input type="text" name="external_reference_number" id="external_reference_number" 
                                    class="form-control font-monospace @error('external_reference_number') is-invalid @enderror" 
                                    placeholder="e.g. POP-DNP-2026-8912" 
                                    value="{{ old('external_reference_number', $existingReport?->external_reference_number) }}">
                                @error('external_reference_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">Accession number provided on the external physical slip.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File Upload & Security Card -->
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-arrow-up text-teal"></i> Secure Report File Upload
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label for="file" class="form-label fw-semibold text-navy">
                                Select Digital Document / Scan <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="file" id="file" class="form-control form-control-lg @error('file') is-invalid @enderror" 
                                accept=".pdf,.jpg,.jpeg,.png" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small mt-2">
                                <i class="bi bi-info-circle me-1"></i> Permitted file formats: <strong>PDF, JPG, JPEG, PNG</strong>. Maximum file size: <strong>10 MB</strong>.
                            </div>
                        </div>

                        <!-- Findings Summary -->
                        <div class="mb-4">
                            <label for="findings_summary" class="form-label fw-semibold text-navy">
                                External Findings Summary / Result Notes
                            </label>
                            <textarea name="findings_summary" id="findings_summary" rows="3" class="form-control @error('findings_summary') is-invalid @enderror" 
                                placeholder="Summary of findings as reported by the external center (e.g. Negative for malignancy, Hb 11.2, Normal MRI brain)...">{{ old('findings_summary', $existingReport?->findings_summary) }}</textarea>
                            @error('findings_summary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Critical Panic Value Alert Switch -->
                        <div class="p-3 border rounded-3 bg-danger-subtle border-danger-subtle d-flex align-items-center gap-3 mb-4">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input fs-5" type="checkbox" name="is_critical" value="1" id="is_critical"
                                    {{ old('is_critical', $existingReport?->is_critical) ? 'checked' : '' }}>
                            </div>
                            <label class="form-check-label cursor-pointer" for="is_critical">
                                <strong class="text-danger d-block">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Flag as Critical / Panic Value Alert
                                </strong>
                                <small class="text-muted">
                                    Check this if the external laboratory flagged panic or life-threatening test findings.
                                </small>
                            </label>
                        </div>

                        <!-- Security Notice Box -->
                        <div class="alert alert-info border-0 mb-0 d-flex align-items-start gap-3 small">
                            <i class="bi bi-shield-check fs-4 text-info mt-1"></i>
                            <div>
                                <strong class="d-block mb-1">HIPAA & Medical Privacy Enforcement</strong>
                                <span>Uploaded files are placed into non-public secure storage (<code>storage/app/secure_medical_reports/</code>). Direct URL access is prohibited; documents are streamed via authorized session tokens only.</span>
                                <div class="fw-semibold mt-1 text-primary">
                                    Note: Uploading sets status to "Report Received". It will NOT be automatically marked medically reviewed until validated by an attending doctor.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer bg-white border-top p-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                        <div class="text-muted small">
                            <i class="bi bi-person-badge text-teal me-1"></i> Uploading records your staff ID and timestamp in the audit log.
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('portal.lab-orders.show', $item->lab_order_id) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-warning px-4 shadow-sm text-dark fw-semibold">
                                <i class="bi bi-cloud-arrow-up me-1"></i> Securely Upload Report
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.portal>

