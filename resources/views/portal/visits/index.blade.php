<x-layouts.portal role="reception" title="Patient Visits & Triage Intake">
    <div class="container-fluid px-0">
        <!-- Page Header & Action Toolbar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Patient Visits</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">Patient Visits & Triage Intake</h1>
                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle rounded-pill px-2.5 py-1">
                        OPD & Emergency Queue
                    </span>
                </div>
                <p class="text-muted-hms mb-0 small">Meghna Clinic & Nursing Home — Shohid Minar Mor, Dinajpur</p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                @can('create', App\Models\Visit::class)
                    <a href="{{ route('portal.visits.create') }}" class="btn btn-teal d-inline-flex align-items-center gap-2 shadow-xs">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>New Patient Check-in</span>
                    </a>
                @endcan

                <a href="{{ route('portal.patients.index') }}" class="btn btn-outline-navy d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-person-vcard"></i>
                    <span>Patient Registry</span>
                </a>
            </div>
        </div>

        <!-- Alert Notification -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Bento KPI Metrics Bar -->
        <div class="row g-3 mb-4">
            <div class="col-xl col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Today's Total Visits</span>
                        <span class="rounded bg-primary bg-opacity-10 text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-ticket-detailed-fill"></i>
                        </span>
                    </div>
                    <div class="h3 fw-bold text-navy mb-0">{{ number_format($metrics['total_today']) }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">Check-ins for {{ now()->format('d M Y') }}</span>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Waiting in Queue</span>
                        <span class="rounded bg-warning bg-opacity-10 text-warning p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-hourglass-split"></i>
                        </span>
                    </div>
                    <div class="h3 fw-bold text-warning mb-0">{{ number_format($metrics['waiting']) }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">Awaiting doctor consultation</span>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">In Consultation</span>
                        <span class="rounded bg-teal bg-opacity-10 text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-person-pulse"></i>
                        </span>
                    </div>
                    <div class="h3 fw-bold text-teal mb-0">{{ number_format($metrics['in_consultation']) }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">Active in doctor chambers</span>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Emergency Cases</span>
                        <span class="rounded bg-danger bg-opacity-10 text-danger p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </span>
                    </div>
                    <div class="h3 fw-bold text-danger mb-0">{{ number_format($metrics['emergency_today']) }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">High priority triage</span>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted-hms small fw-semibold">Admitted Inpatients</span>
                        <span class="rounded bg-navy bg-opacity-10 text-navy p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-hospital-fill"></i>
                        </span>
                    </div>
                    <div class="h3 fw-bold text-navy mb-0">{{ number_format($metrics['admitted_today']) }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">Admitted from visit today</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar Card -->
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3 mb-4">
            <form method="GET" action="{{ route('portal.visits.index') }}" class="row g-2 align-items-center">
                <div class="col-lg-4 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" 
                               placeholder="Search Visit No, Patient Code, Name, or Phone..." 
                               value="{{ $search ?? '' }}">
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6">
                    <select name="visit_type" class="form-select">
                        <option value="">All Visit Types</option>
                        <option value="regular" @selected(($filters['visit_type'] ?? '') === 'regular')>Regular OPD</option>
                        <option value="emergency" @selected(($filters['visit_type'] ?? '') === 'emergency')>Emergency</option>
                        <option value="follow_up" @selected(($filters['visit_type'] ?? '') === 'follow_up')>Follow-up</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6">
                    <select name="triage_level" class="form-select">
                        <option value="">All Triage Levels</option>
                        <option value="normal" @selected(($filters['triage_level'] ?? '') === 'normal')>Normal (Green)</option>
                        <option value="priority" @selected(($filters['triage_level'] ?? '') === 'priority')>Priority (Yellow)</option>
                        <option value="critical" @selected(($filters['triage_level'] ?? '') === 'critical')>Critical (Red)</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="waiting" @selected(($filters['status'] ?? '') === 'waiting')>Waiting</option>
                        <option value="in_consultation" @selected(($filters['status'] ?? '') === 'in_consultation')>In Consultation</option>
                        <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Completed</option>
                        <option value="admitted" @selected(($filters['status'] ?? '') === 'admitted')>Admitted</option>
                        <option value="referred_out" @selected(($filters['status'] ?? '') === 'referred_out')>Referred Out</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6 d-flex gap-2">
                    <button type="submit" class="btn btn-navy flex-grow-1 shadow-xs">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                    @if(!empty($search) || !empty(array_filter($filters ?? [])))
                        <a href="{{ route('portal.visits.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Visits Data Table Card -->
        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                        <thead class="table-light">
                            <tr class="text-uppercase text-muted-hms" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                <th class="ps-3 py-3">Visit Number</th>
                                <th class="py-3">Patient Master Record</th>
                                <th class="py-3">Date & Time</th>
                                <th class="py-3">Visit Type</th>
                                <th class="py-3">Triage</th>
                                <th class="py-3">Care Setting</th>
                                <th class="py-3">Status</th>
                                <th class="text-end pe-3 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($visits as $visit)
                                <tr>
                                    <!-- Visit Number -->
                                    <td class="ps-3">
                                        <a href="{{ route('portal.visits.show', $visit->id) }}" class="fw-bold font-monospace text-navy text-decoration-none hover-underline">
                                            {{ $visit->visit_number }}
                                        </a>
                                        @if($visit->appointment_id)
                                            <span class="d-block text-muted" style="font-size: 0.725rem;">
                                                <i class="bi bi-calendar2-check"></i> Appt #{{ $visit->appointment_id }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Patient Master Link -->
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-teal bg-opacity-10 text-teal d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                {{ strtoupper(substr($visit->patient->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('portal.patients.show', $visit->patient_id) }}" class="fw-semibold text-navy text-decoration-none hover-underline d-block">
                                                    {{ $visit->patient->name }}
                                                </a>
                                                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.75rem;">
                                                    <span class="font-monospace">{{ $visit->patient->patient_code }}</span>
                                                    <span>•</span>
                                                    <span>{{ $visit->patient->phone }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Visit Date & Time -->
                                    <td>
                                        <span class="fw-semibold text-navy d-block">
                                            {{ $visit->visit_date ? $visit->visit_date->format('d M Y') : 'N/A' }}
                                        </span>
                                        <span class="text-muted" style="font-size: 0.75rem;">
                                            {{ $visit->visit_date ? $visit->visit_date->format('h:i A') : '' }}
                                        </span>
                                    </td>

                                    <!-- Visit Type -->
                                    <td>
                                        @if($visit->visit_type === 'emergency')
                                            <span class="badge bg-danger text-white">
                                                <i class="bi bi-lightning-fill"></i> Emergency
                                            </span>
                                        @elseif($visit->visit_type === 'follow_up')
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                                <i class="bi bi-arrow-repeat"></i> Follow-up
                                            </span>
                                        @else
                                            <span class="badge bg-light text-dark border">
                                                Regular OPD
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Triage Severity Level -->
                                    <td>
                                        @if($visit->triage_level === 'critical')
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle fw-bold">
                                                <i class="bi bi-exclamation-octagon-fill me-0.5"></i> Critical
                                            </span>
                                        @elseif($visit->triage_level === 'priority')
                                            <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle fw-semibold">
                                                <i class="bi bi-exclamation-triangle-fill me-0.5"></i> Priority
                                            </span>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">
                                                <i class="bi bi-check-circle-fill me-0.5"></i> Normal
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Outpatient vs Inpatient Care Setting -->
                                    <td>
                                        @if($visit->is_inpatient)
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle">
                                                <i class="bi bi-hospital"></i> Inpatient (Admitted)
                                            </span>
                                            @if($visit->admission && $visit->admission->currentBedAssignment)
                                                <span class="d-block text-muted font-monospace" style="font-size: 0.725rem;">
                                                    Bed: {{ $visit->admission->currentBedAssignment->bed->bed_number }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="badge bg-light text-muted border">
                                                <i class="bi bi-person-walking"></i> Outpatient
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        @if($visit->status === 'waiting')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                                <i class="bi bi-hourglass-split"></i> Waiting
                                            </span>
                                        @elseif($visit->status === 'in_consultation')
                                            <span class="badge bg-teal-subtle text-teal border border-teal-subtle">
                                                <i class="bi bi-person-pulse"></i> In Consultation
                                            </span>
                                        @elseif($visit->status === 'completed')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bi bi-check2"></i> Completed
                                            </span>
                                        @elseif($visit->status === 'admitted')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                <i class="bi bi-hospital"></i> Admitted
                                            </span>
                                        @elseif($visit->status === 'referred_out')
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                <i class="bi bi-box-arrow-up-right"></i> Referred Out
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('portal.visits.show', $visit->id) }}" class="btn btn-outline-navy" title="View Visit Dossier">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('print', $visit)
                                                <a href="{{ route('portal.visits.print-token', $visit->id) }}" target="_blank" class="btn btn-outline-teal" title="Print Queue Token">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                            @endcan
                                            @can('update', $visit)
                                                <a href="{{ route('portal.visits.edit', $visit->id) }}" class="btn btn-outline-secondary" title="Update Status / Triage">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="bi bi-ticket-detailed text-muted-hms display-5 mb-3"></i>
                                            <h3 class="h6 fw-bold text-navy mb-1">No Patient Visits Found</h3>
                                            <p class="text-muted small mb-3">No patient check-in records match the selected search or filter criteria.</p>
                                            @can('create', App\Models\Visit::class)
                                                <a href="{{ route('portal.visits.create') }}" class="btn btn-sm btn-teal">
                                                    <i class="bi bi-plus-circle me-1"></i> Register New Check-in
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($visits->hasPages())
                    <div class="p-3 border-top d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            Showing {{ $visits->firstItem() }} to {{ $visits->lastItem() }} of {{ $visits->total() }} visits
                        </span>
                        {{ $visits->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.portal>
