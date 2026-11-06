<x-layouts.portal role="lab" title="Diagnostic Report Dossier — {{ $report->test_name }}" activeNav="lab-reports">
    <!-- Header -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 58px; height: 58px; font-size: 1.6rem;">
                        <i class="bi bi-file-earmark-medical"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <h4 class="fw-bold text-navy mb-0">{{ $report->test_name }}</h4>
                            <span class="badge {{ $report->source_badge_class }}">{{ ucfirst($report->source) }}</span>
                            <span class="badge {{ $report->report_status_badge_class }}">{{ $report->report_status_label }}</span>
                            <span class="badge {{ $report->review_status_badge_class }}">{{ $report->review_status_label }}</span>
                            @if($report->is_critical)
                                <span class="badge bg-danger text-white"><i class="bi bi-exclamation-triangle-fill"></i> CRITICAL / PANIC</span>
                            @endif
                        </div>
                        <p class="text-muted-hms small mb-0 mt-1">
                            Report ID: <span class="font-monospace text-dark">#{{ $report->id }}</span> &bull; Recorded on {{ $report->created_at->format('d M Y, h:i A') }} &bull; Order: 
                            <a href="{{ route('portal.lab-orders.show', $report->orderItem?->lab_order_id) }}" class="fw-semibold text-teal font-monospace text-decoration-none">
                                {{ $report->orderItem?->labOrder?->order_number }}
                            </a>
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('portal.lab-reports.print', $report->id) }}" target="_blank" class="btn btn-sm btn-outline-navy d-flex align-items-center gap-1 shadow-sm">
                        <i class="bi bi-printer"></i>
                        <span>Print Clinical Slip</span>
                    </a>
                    @if($report->hasFile())
                        <a href="{{ route('portal.lab-reports.stream', $report->id) }}" target="_blank" class="btn btn-sm btn-outline-teal d-flex align-items-center gap-1 shadow-sm">
                            <i class="bi bi-eye"></i>
                            <span>View Document</span>
                        </a>
                        <a href="{{ route('portal.lab-reports.download', $report->id) }}" class="btn btn-sm btn-teal d-flex align-items-center gap-1 shadow-sm">
                            <i class="bi bi-download"></i>
                            <span>Download File</span>
                        </a>
                    @endif
                    @if(!$report->isReviewed() && (auth()->user()->hasRole(['doctor', 'super_admin', 'administrator']) || auth()->user()->hasPermission('lab.approve')))
                        <button type="button" class="btn btn-sm btn-success d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#reviewReportModal">
                            <i class="bi bi-check2-circle"></i>
                            <span>Doctor Review</span>
                        </button>
                    @endif
                    <a href="{{ route('portal.lab-reports.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Reports Workbench
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($report->is_critical)
        <!-- Critical Panic Value Warning Banner -->
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-3">
            <i class="bi bi-exclamation-octagon-fill fs-2 text-danger"></i>
            <div>
                <h6 class="fw-bold mb-1">CRITICAL CLINICAL ALERT — ABNORMAL / PANIC VALUES DETECTED</h6>
                <div class="small">
                    This diagnostic report contains critical threshold results flagged for emergency doctor notification and priority clinical intervention.
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Column: Patient Context, Test Parameters, File Preview -->
        <div class="col-lg-8">
            <!-- Patient & Investigation Metadata Bento -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-person-lines-fill text-teal"></i> Patient & Requisition Details
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Patient Name:</span>
                            @if($report->patient)
                                <a href="{{ route('portal.patients.show', $report->patient->id) }}" class="fw-bold text-navy text-decoration-none">
                                    {{ $report->patient->name }}
                                </a>
                                <div class="small text-muted font-monospace">
                                    {{ $report->patient->patient_code }} &bull; {{ $report->patient->age_gender ?? 'N/A' }}
                                </div>
                            @else
                                <span class="text-muted">Unlinked Patient</span>
                            @endif
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Prescribing Doctor:</span>
                            <div class="fw-bold text-navy">Dr. {{ $report->doctor?->name ?? 'Medical Officer' }}</div>
                            <div class="small text-muted">{{ $report->doctor?->staffProfile?->specialization ?? 'Clinical Medicine' }}</div>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Specimen Accession:</span>
                            @if($report->orderItem?->sample)
                                <span class="badge bg-light text-navy border font-monospace">
                                    <i class="bi bi-upc-scan text-teal"></i> {{ $report->orderItem->sample->barcode }}
                                </span>
                                <span class="small text-muted ms-1">({{ $report->orderItem->sample->sample_type }})</span>
                            @else
                                <span class="text-muted small">Not accessioned</span>
                            @endif
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Uploaded / Recorded By:</span>
                            <div class="small text-dark fw-semibold">
                                {{ $report->uploadedBy?->name ?? 'Medical Technologist' }}
                            </div>
                            <div class="small text-muted">
                                Timestamp: {{ $report->uploaded_at ? $report->uploaded_at->format('d M Y, h:i A') : $report->created_at->format('d M Y, h:i A') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Internal Lab: Structured Parameters Table -->
            @if($report->source === 'internal' && !empty($report->parameters))
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-table text-teal"></i> Structured Investigation Findings & Reference Ranges
                        </h6>
                        <span class="badge bg-light text-navy border">{{ count($report->parameters) }} Parameters</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="bg-light small text-uppercase">
                                <tr>
                                    <th style="width: 35%;">Analyte / Parameter</th>
                                    <th style="width: 20%;">Measured Result</th>
                                    <th style="width: 15%;">Unit</th>
                                    <th style="width: 20%;">Biological Reference Range</th>
                                    <th style="width: 10%;" class="text-center">Status Flag</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report->parameters as $param)
                                    @php
                                        $flag = strtolower($param['flag'] ?? 'normal');
                                        $badgeClass = match($flag) {
                                            'critical' => 'bg-danger text-white',
                                            'high', 'abnormal' => 'bg-warning text-dark',
                                            'low' => 'bg-info text-dark',
                                            default => 'bg-success-subtle text-success border border-success-subtle',
                                        };
                                    @endphp
                                    <tr class="{{ in_array($flag, ['critical', 'high', 'abnormal']) ? 'table-warning-subtle' : '' }}">
                                        <td class="fw-semibold text-dark">{{ $param['parameter'] ?? '' }}</td>
                                        <td class="font-monospace fw-bold text-navy fs-6">{{ $param['value'] ?? '' }}</td>
                                        <td class="small text-muted">{{ $param['unit'] ?? '-' }}</td>
                                        <td class="font-monospace small text-muted">{{ $param['reference_range'] ?? '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $badgeClass }} text-uppercase" style="font-size: 0.75rem;">
                                                {{ $flag }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- External Lab: Secure Document Card -->
            @if($report->source === 'external')
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-lock2 text-teal"></i> Non-Public Diagnostic Document
                        </h6>
                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle">
                            {{ $report->labPartner?->name ?? 'External Lab' }}
                        </span>
                    </div>
                    <div class="card-body p-4">
                        @if($report->hasFile())
                            <div class="border rounded-3 p-3 bg-light-subtle d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded bg-navy text-white p-3 fs-3">
                                        <i class="bi bi-file-earmark-{{ $report->isPdf() ? 'pdf' : 'image' }}"></i>
                                    </div>
                                    <div>
                                        <strong class="text-navy d-block">{{ $report->original_file_name ?: basename($report->file_path) }}</strong>
                                        <div class="small text-muted">
                                            Size: {{ $report->formatted_file_size }} &bull; Format: {{ strtoupper(explode('/', $report->file_type ?? 'PDF')[1] ?? 'PDF') }}
                                        </div>
                                        <div class="small text-muted font-monospace mt-1">
                                            <i class="bi bi-shield-check text-success"></i> Non-public storage: {{ $report->file_path }}
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('portal.lab-reports.stream', $report->id) }}" target="_blank" class="btn btn-sm btn-outline-navy">
                                        <i class="bi bi-eye me-1"></i> Stream View
                                    </a>
                                    <a href="{{ route('portal.lab-reports.download', $report->id) }}" class="btn btn-sm btn-teal">
                                        <i class="bi bi-download me-1"></i> Download
                                    </a>
                                </div>
                            </div>

                            @if($report->isImage())
                                <div class="text-center p-2 border rounded bg-white">
                                    <img src="{{ route('portal.lab-reports.stream', $report->id) }}" alt="Medical Scan" class="img-fluid rounded" style="max-height: 400px;">
                                </div>
                            @endif
                        @else
                            <p class="text-muted small mb-0">No digital report file attached.</p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Summary Findings & Impression -->
            @if($report->findings_summary)
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-chat-left-text text-teal"></i> Technologist Impression & Findings Summary
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-dark bg-light rounded p-3 fst-italic">
                            {{ $report->findings_summary }}
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Medical Review Status, History Timeline, Partner Info -->
        <div class="col-lg-4">
            <!-- Medical Doctor Review Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white" id="review-section">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-patch-check-fill text-teal"></i> Doctor Medical Review
                    </h6>
                    <span class="badge {{ $report->review_status_badge_class }}">
                        {{ $report->review_status_label }}
                    </span>
                </div>
                <div class="card-body p-4">
                    @if($report->isReviewed())
                        <div class="text-center py-2 mb-3">
                            <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px; font-size: 1.5rem;">
                                <i class="bi bi-check2"></i>
                            </div>
                            <h6 class="fw-bold text-success mb-0">Medically Reviewed & Verified</h6>
                            <small class="text-muted">Validated by attending clinician</small>
                        </div>

                        <div class="border-top pt-3 small">
                            <div class="mb-2">
                                <span class="text-muted d-block">Reviewed By:</span>
                                <strong class="text-navy">Dr. {{ $report->reviewedBy?->name ?? 'Attending Doctor' }}</strong>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted d-block">Review Timestamp:</span>
                                <div>{{ $report->reviewed_at?->format('d M Y, h:i A') ?? 'N/A' }}</div>
                            </div>
                            @if($report->doctor_notes)
                                <div class="mt-3">
                                    <span class="text-muted d-block">Clinician Remarks & Directives:</span>
                                    <div class="p-2 bg-light rounded text-dark fst-italic mt-1 border">
                                        {{ $report->doctor_notes }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-warning py-3 small mb-3">
                            <i class="bi bi-hourglass-split me-1"></i>
                            <strong>Report Received &bull; Pending Review</strong>
                            <p class="mb-0 mt-1">This report is recorded in the laboratory system, but has not yet been medically verified by a licensed doctor.</p>
                        </div>

                        @if(auth()->user()->hasRole(['doctor', 'super_admin', 'administrator']) || auth()->user()->hasPermission('lab.approve'))
                            <button type="button" class="btn btn-success btn-sm w-100 shadow-sm py-2" data-bs-toggle="modal" data-bs-target="#reviewReportModal">
                                <i class="bi bi-check2-circle me-1"></i> Perform Doctor Medical Review
                            </button>
                        @endif
                    @endif
                </div>
            </div>

            <!-- External Partner Details (if outsourced) -->
            @if($report->source === 'external' && $report->labPartner)
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-buildings text-teal"></i> Reference Diagnostic Center
                        </h6>
                    </div>
                    <div class="card-body p-4 small">
                        <div class="fw-bold text-navy fs-6 mb-1">{{ $report->labPartner->name }}</div>
                        <span class="badge bg-light text-navy border font-monospace mb-2">{{ $report->labPartner->code }}</span>
                        <div><i class="bi bi-geo-alt me-1"></i> {{ $report->labPartner->address ?? 'Dinajpur' }}</div>
                        <div><i class="bi bi-telephone me-1"></i> {{ $report->labPartner->phone ?? 'N/A' }}</div>
                        @if($report->external_reference_number)
                            <div class="mt-2 pt-2 border-top">
                                <span class="text-muted d-block">Partner Sample Accession #:</span>
                                <strong class="font-monospace text-teal">{{ $report->external_reference_number }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Complete Report History Audit Timeline -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-teal"></i> Report Lifecycle History
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if($report->histories->isNotEmpty())
                        <div class="position-relative ps-3" style="border-left: 2px solid #E2E8F0;">
                            @foreach($report->histories as $history)
                                <div class="mb-4 position-relative">
                                    <div class="position-absolute" style="left: -23px; top: 0;">
                                        <span class="badge rounded-circle {{ $history->action_badge_class }} p-1 d-inline-block" style="width: 12px; height: 12px;"></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge {{ $history->action_badge_class }} text-capitalize" style="font-size: 0.75rem;">
                                            {{ $history->action_label }}
                                        </span>
                                        <span class="small text-muted font-monospace" style="font-size: 0.75rem;">
                                            {{ $history->created_at->format('d M, h:i A') }}
                                        </span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-person me-1"></i> {{ $history->changedBy?->name ?? 'System' }}
                                    </div>
                                    @if($history->notes)
                                        <div class="small text-dark bg-light rounded p-2 mt-2 fst-italic">
                                            {{ $history->notes }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small mb-0">No prior report revisions recorded.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Doctor Review -->
    @if(!$report->isReviewed() && (auth()->user()->hasRole(['doctor', 'super_admin', 'administrator']) || auth()->user()->hasPermission('lab.approve')))
        <div class="modal fade" id="reviewReportModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form action="{{ route('portal.lab-reports.review', $report->id) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-success text-white">
                            <h6 class="modal-title fw-bold">
                                <i class="bi bi-patch-check me-1"></i> Doctor Medical Review & Validation
                            </h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="small text-muted mb-3">
                                As attending physician, verifying this report validates the diagnostic findings for clinical decision-making.
                            </p>

                            <div class="mb-3">
                                <label for="doctor_notes" class="form-label fw-semibold text-navy">
                                    Clinical Doctor Notes / Treatment Plan Directives
                                </label>
                                <textarea name="doctor_notes" id="doctor_notes" class="form-control" rows="3" 
                                    placeholder="e.g. Findings consistent with acute microcytic anemia; start oral iron therapy 200mg BD for 6 weeks, recheck CBC in 1 month..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="modal_findings_summary" class="form-label fw-semibold text-navy">
                                    Amend / Finalize Findings Summary (Optional)
                                </label>
                                <textarea name="findings_summary" id="modal_findings_summary" class="form-control" rows="2">{{ $report->findings_summary }}</textarea>
                            </div>

                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_critical" value="1" id="modal_is_critical" {{ $report->is_critical ? 'checked' : '' }}>
                                <label class="form-check-label text-danger fw-semibold" for="modal_is_critical">
                                    Flag as critical panic value requiring urgent follow-up
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success btn-sm px-3">
                                <i class="bi bi-check2-circle me-1"></i> Confirm & Medically Verify Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</x-layouts.portal>

