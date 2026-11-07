<x-layouts.portal role="admin" title="Hospital Reports & Analytics Center" activeNav="reports">
    <div class="container-fluid px-0">
        {{-- Page Header --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Reports Center</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold text-navy mb-0">Hospital Reports &amp; Analytics Center</h1>
                <p class="text-muted-hms small mb-0 mt-1">
                    Meghna Clinic &amp; Nursing Home &middot; Comprehensive Clinical, Operational, and Financial Audit Reports
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('portal.admin') }}" class="btn btn-outline-navy d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-speedometer2"></i>
                    <span>Admin Dashboard</span>
                </a>
            </div>
        </div>

        {{-- Section 1: Clinical & Operational Reports --}}
        <h5 class="fw-bold text-navy mb-3">
            <i class="bi bi-hospital text-teal me-2"></i>Clinical &amp; Healthcare Operational Reports
        </h5>
        <div class="row g-3 mb-5">
            @foreach($reports as $key => $report)
                @if($report['category'] !== 'financial')
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm rounded-3 h-100 hover-shadow transition">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-teal bg-opacity-10 text-teal p-2 rounded-2">
                                            <i class="bi bi-{{ $report['icon'] }} fs-5"></i>
                                        </span>
                                        <span class="badge bg-light text-navy border text-uppercase" style="font-size: 0.65rem;">
                                            {{ $report['category'] }}
                                        </span>
                                    </div>
                                    <h6 class="fw-bold text-navy mb-1">{{ $report['name'] }}</h6>
                                    <p class="text-muted small mb-3">{{ $report['description'] }}</p>
                                </div>
                                <div class="d-flex gap-2 pt-2 border-top">
                                    <a href="{{ route('portal.reports.show', $key) }}" class="btn btn-teal btn-sm flex-grow-1">
                                        <i class="bi bi-file-earmark-text me-1"></i>View Report
                                    </a>
                                    <a href="{{ route('portal.reports.print', $key) }}" target="_blank" class="btn btn-outline-secondary btn-sm" title="Print Report">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Section 2: Financial & Revenue Reports (Restricted Access) --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold text-navy mb-0">
                <i class="bi bi-shield-lock text-warning me-2"></i>Financial &amp; Revenue Reports
            </h5>
            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                <i class="bi bi-lock-fill me-1"></i>Restricted: Administrator &amp; Billing Authority Only
            </span>
        </div>
        <div class="row g-3">
            @foreach($reports as $key => $report)
                @if($report['category'] === 'financial')
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-warning">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-warning bg-opacity-10 text-warning-emphasis p-2 rounded-2">
                                            <i class="bi bi-{{ $report['icon'] }} fs-5"></i>
                                        </span>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.65rem;">
                                            <i class="bi bi-lock me-1"></i>Restricted
                                        </span>
                                    </div>
                                    <h6 class="fw-bold text-navy mb-1">{{ $report['name'] }}</h6>
                                    <p class="text-muted small mb-3">{{ $report['description'] }}</p>
                                </div>
                                <div class="d-flex gap-2 pt-2 border-top">
                                    @if($report['accessible'])
                                        <a href="{{ route('portal.reports.show', $key) }}" class="btn btn-navy btn-sm flex-grow-1">
                                            <i class="bi bi-file-earmark-text me-1"></i>View Financial Report
                                        </a>
                                        <a href="{{ route('portal.reports.print', $key) }}" target="_blank" class="btn btn-outline-secondary btn-sm" title="Print Report">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    @else
                                        <button class="btn btn-light btn-sm flex-grow-1 text-muted" disabled title="Access restricted by role">
                                            <i class="bi bi-lock-fill me-1"></i>Access Restricted
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

    </div>
</x-layouts.portal>

