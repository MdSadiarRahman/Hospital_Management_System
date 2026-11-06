<x-layouts.portal role="lab" title="Diagnostic Test Reports — Meghna Clinic" activeNav="lab-reports">
    <!-- Header -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 58px; height: 58px; font-size: 1.6rem;">
                        <i class="bi bi-file-earmark-medical"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-navy mb-0">Diagnostic Reports & Results Management</h4>
                        <p class="text-muted-hms small mb-0 mt-1">
                            Internal test result entry, external partner report uploads, secure non-public file storage, and doctor review verification
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('portal.lab-orders.index') }}" class="btn btn-sm btn-teal d-flex align-items-center gap-1 shadow-sm">
                        <i class="bi bi-clipboard2-pulse"></i>
                        <span>Orders Workbench</span>
                    </a>
                    <a href="{{ route('portal.lab-catalog.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                        <i class="bi bi-card-checklist"></i>
                        <span>Test Catalog</span>
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

    <!-- Metric KPI Bento -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Total Diagnostic Reports" 
                value="{{ $statistics['total_reports'] }} Reports" 
                icon="file-earmark-medical" 
                color="primary"
                trend="{{ $statistics['internal_reports'] }} in-house tests"
                trendUp="true"
                subtitle="All recorded investigations"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Awaiting Doctor Review" 
                value="{{ $statistics['pending_review'] }} Pending" 
                icon="clock-history" 
                color="warning"
                trend="Physician verification required"
                trendUp="false"
                subtitle="Report received, unreviewed"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Medically Verified" 
                value="{{ $statistics['reviewed'] }} Verified" 
                icon="patch-check-fill" 
                color="success"
                trend="Ready for clinical action"
                trendUp="true"
                subtitle="Physician review complete"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="External Partner Uploads" 
                value="{{ $statistics['external_reports'] }} Outsources" 
                icon="buildings" 
                color="info"
                trend="{{ $statistics['critical_reports'] }} critical panic values"
                trendUp="true"
                subtitle="Secure outsourced PDF/images"
            />
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('portal.lab-reports.index') }}" class="row g-2 align-items-center">
                <!-- Search Term -->
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Search Reports</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="Patient, order #, test, findings..." value="{{ $filters['q'] ?? '' }}">
                    </div>
                </div>

                <!-- Source (Internal vs External) -->
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Testing Source</label>
                    <select name="source" class="form-select form-select-sm">
                        <option value="">All Sources</option>
                        <option value="internal" {{ ($filters['source'] ?? '') === 'internal' ? 'selected' : '' }}>In-House Lab</option>
                        <option value="external" {{ ($filters['source'] ?? '') === 'external' ? 'selected' : '' }}>External Send-Out</option>
                    </select>
                </div>

                <!-- Review Status -->
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Doctor Review Status</label>
                    <select name="review_status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="pending_review" {{ ($filters['review_status'] ?? '') === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                        <option value="reviewed" {{ ($filters['review_status'] ?? '') === 'reviewed' ? 'selected' : '' }}>Medically Verified</option>
                    </select>
                </div>

                <!-- Critical Panic Values -->
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Clinical Priority</label>
                    <select name="is_critical" class="form-select form-select-sm">
                        <option value="">All Reports</option>
                        <option value="1" {{ ($filters['is_critical'] ?? '') === '1' ? 'selected' : '' }}>Critical / Panic Only</option>
                    </select>
                </div>

                <!-- External Partner -->
                <div class="col-md-3 d-flex align-items-end gap-2 pt-3 pt-md-0">
                    <button type="submit" class="btn btn-sm btn-navy d-flex align-items-center gap-1">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    @if(!empty(array_filter($filters)))
                        <a href="{{ route('portal.lab-reports.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-card-checklist text-teal"></i> Diagnostic Investigations & Medical Reports
            </h6>
            <span class="badge bg-light text-navy border">
                Showing {{ $reports->firstItem() ?? 0 }} - {{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }} reports
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Report ID & Test</th>
                        <th>Patient Details</th>
                        <th>Order & Doctor</th>
                        <th>Source / Laboratory</th>
                        <th>Report Status</th>
                        <th>Doctor Medical Review</th>
                        <th>Secure File</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr class="{{ $report->is_critical ? 'table-danger-subtle' : '' }}">
                            <!-- Report ID & Test -->
                            <td class="ps-4">
                                <a href="{{ route('portal.lab-reports.show', $report->id) }}" class="fw-bold text-navy text-decoration-none">
                                    {{ $report->test_name }}
                                </a>
                                <div class="small text-muted font-monospace">
                                    Report #{{ $report->id }} &bull; {{ $report->created_at->format('d M Y, h:i A') }}
                                </div>
                                @if($report->is_critical)
                                    <span class="badge bg-danger text-white mt-1">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Critical / Panic Value
                                    </span>
                                @endif
                            </td>

                            <!-- Patient Details -->
                            <td>
                                @if($report->patient)
                                    <div class="fw-semibold text-dark">{{ $report->patient->name }}</div>
                                    <div class="small text-muted font-monospace">
                                        {{ $report->patient->patient_code }} &bull; {{ $report->patient->age_gender ?? 'N/A' }}
                                    </div>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>

                            <!-- Order & Doctor -->
                            <td>
                                @if($report->orderItem?->labOrder)
                                    <a href="{{ route('portal.lab-orders.show', $report->orderItem->labOrder->id) }}" class="fw-semibold text-teal font-monospace text-decoration-none">
                                        {{ $report->orderItem->labOrder->order_number }}
                                    </a>
                                    <div class="small text-muted">
                                        Dr. {{ $report->orderItem->labOrder->doctor?->name ?? 'Staff Doctor' }}
                                    </div>
                                @else
                                    <span class="text-muted">Direct Order</span>
                                @endif
                            </td>

                            <!-- Source & Partner -->
                            <td>
                                @if($report->source === 'internal')
                                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle">
                                        <i class="bi bi-hospital me-1"></i> In-House Lab
                                    </span>
                                    @if(!empty($report->parameters))
                                        <div class="small text-muted mt-1 font-monospace">
                                            {{ count($report->parameters) }} measured parameters
                                        </div>
                                    @endif
                                @else
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle">
                                        <i class="bi bi-buildings me-1"></i> External
                                    </span>
                                    @if($report->labPartner)
                                        <div class="small text-muted mt-1 text-truncate" style="max-width: 140px;" title="{{ $report->labPartner->name }}">
                                            {{ $report->labPartner->name }}
                                        </div>
                                    @endif
                                @endif
                            </td>

                            <!-- Report Status (Received vs Reviewed) -->
                            <td>
                                <span class="badge {{ $report->report_status_badge_class }}">
                                    {{ $report->report_status_label }}
                                </span>
                                <div class="small text-muted mt-1">
                                    by {{ $report->uploadedBy?->name ?? 'Lab Technologist' }}
                                </div>
                            </td>

                            <!-- Doctor Medical Review -->
                            <td>
                                <span class="badge {{ $report->review_status_badge_class }}">
                                    {{ $report->review_status_label }}
                                </span>
                                @if($report->isReviewed() && $report->reviewedBy)
                                    <div class="small text-muted mt-1">
                                        Dr. {{ $report->reviewedBy->name }} &bull; {{ $report->reviewed_at?->format('d M, h:i A') }}
                                    </div>
                                @endif
                            </td>

                            <!-- Secure File Attachment -->
                            <td>
                                @if($report->hasFile())
                                    <a href="{{ route('portal.lab-reports.stream', $report->id) }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm" title="View Secure Report">
                                        <i class="bi bi-file-earmark-{{ $report->isPdf() ? 'pdf' : 'image' }}"></i>
                                        <span class="small font-monospace">{{ $report->formatted_file_size }}</span>
                                    </a>
                                @else
                                    <span class="badge bg-light text-muted border">Structured Form</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('portal.lab-reports.show', $report->id) }}">
                                                <i class="bi bi-folder2-open text-primary me-2"></i> View Report Dossier
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('portal.lab-reports.print', $report->id) }}" target="_blank">
                                                <i class="bi bi-printer text-navy me-2"></i> Print Clinical Report
                                            </a>
                                        </li>
                                        @if($report->hasFile())
                                            <li>
                                                <a class="dropdown-item" href="{{ route('portal.lab-reports.download', $report->id) }}">
                                                    <i class="bi bi-download text-teal me-2"></i> Download File
                                                </a>
                                            </li>
                                        @endif
                                        @if(!$report->isReviewed() && (auth()->user()->hasRole(['doctor', 'super_admin', 'administrator']) || auth()->user()->hasPermission('lab.approve')))
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-success fw-semibold" href="{{ route('portal.lab-reports.show', $report->id) }}#review-section">
                                                    <i class="bi bi-check2-circle text-success me-2"></i> Perform Medical Review
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-file-earmark-medical fs-1 d-block mb-2 text-muted-hms"></i>
                                    <h6 class="fw-bold">No Laboratory Reports Recorded Yet</h6>
                                    <p class="small mb-3">Diagnostic reports will appear here once results are entered or uploaded from partner labs.</p>
                                    <a href="{{ route('portal.lab-orders.index') }}" class="btn btn-sm btn-teal">
                                        <i class="bi bi-clipboard2-pulse me-1"></i> Go to Orders Queue
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $reports->withQueryString()->links() }}
            </div>
        @endif
    </div>
</x-layouts.portal>

