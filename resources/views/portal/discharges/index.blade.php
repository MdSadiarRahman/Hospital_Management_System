<x-layouts.portal role="admin" title="Discharge Clearances Workbench" activeNav="discharges">
    <div class="container-fluid px-0">
        {{-- Page Header --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Discharge Clearances</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold text-navy mb-0">Dual-Clearance Discharge Workbench</h1>
                <p class="text-muted-hms small mb-0 mt-1">
                    Meghna Clinic &amp; Nursing Home &middot; Clinical &amp; Financial Clearances, Bed Release, &amp; Post-Care Summaries
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('portal.admissions.index') }}" class="btn btn-outline-navy d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-hospital"></i>
                    <span>Inpatient Ward</span>
                </a>
                <a href="{{ route('portal.billing.index') }}" class="btn btn-outline-teal d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-receipt"></i>
                    <span>Cashier Desk</span>
                </a>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-xs border-0" role="alert">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-xs border-0" role="alert">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- KPI Summary Bento Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-3">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Inpatients</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary p-2 rounded-2">
                                <i class="bi bi-hospital"></i>
                            </span>
                        </div>
                        <div class="h3 fw-bold text-navy mb-0">{{ $stats['active_inpatients'] }}</div>
                        <div class="text-muted small mt-1">Currently in Beds</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-4 border-success">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Ready for Exit</span>
                            <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-2">
                                <i class="bi bi-door-open-fill"></i>
                            </span>
                        </div>
                        <div class="h3 fw-bold text-success mb-0">{{ $stats['ready_for_discharge'] }}</div>
                        <div class="text-muted small mt-1">Both Clinical &amp; Financial Cleared</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-4 border-info">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Need Doctor</span>
                            <span class="badge bg-info bg-opacity-10 text-info p-2 rounded-2">
                                <i class="bi bi-heart-pulse"></i>
                            </span>
                        </div>
                        <div class="h3 fw-bold text-navy mb-0">{{ $stats['pending_clinical'] }}</div>
                        <div class="text-muted small mt-1">Pending Clinical Sign-off</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-4 border-warning">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Need Billing</span>
                            <span class="badge bg-warning bg-opacity-10 text-warning p-2 rounded-2">
                                <i class="bi bi-cash-stack"></i>
                            </span>
                        </div>
                        <div class="h3 fw-bold text-navy mb-0">{{ $stats['pending_financial'] }}</div>
                        <div class="text-muted small mt-1">Pending Settlement</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 rounded-3">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Discharged Today</span>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary p-2 rounded-2">
                                <i class="bi bi-calendar-check"></i>
                            </span>
                        </div>
                        <div class="h3 fw-bold text-navy mb-0">{{ $stats['discharged_today'] }}</div>
                        <div class="text-muted small mt-1">Beds Released to Cleaning</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <form action="{{ route('portal.discharges.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-12 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                placeholder="Search Patient name, ID, Admission #, Bed..." 
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <select name="clearance_filter" class="form-select">
                            <option value="">All Clearance Stages</option>
                            <option value="ready" {{ request('clearance_filter') === 'ready' ? 'selected' : '' }}>Ready for Final Exit (Both Cleared)</option>
                            <option value="pending_clinical" {{ request('clearance_filter') === 'pending_clinical' ? 'selected' : '' }}>Pending Clinical Clearance</option>
                            <option value="pending_financial" {{ request('clearance_filter') === 'pending_financial' ? 'selected' : '' }}>Pending Financial Clearance</option>
                            <option value="pending_nursing" {{ request('clearance_filter') === 'pending_nursing' ? 'selected' : '' }}>Pending Nursing Clearance</option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <select name="status" class="form-select">
                            <option value="admitted" {{ request('status', 'admitted') === 'admitted' ? 'selected' : '' }}>Currently Admitted (Active)</option>
                            <option value="discharged" {{ request('status') === 'discharged' ? 'selected' : '' }}>Discharged History</option>
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-navy flex-grow-1">Filter</button>
                        <a href="{{ route('portal.discharges.index') }}" class="btn btn-light" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Workbench Table --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-navy text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <tr>
                                <th class="ps-3 py-3">Admission &amp; Bed</th>
                                <th class="py-3">Patient Details</th>
                                <th class="py-3">Doctor &amp; Dept</th>
                                <th class="py-3 text-center">Clinical Clearance</th>
                                <th class="py-3 text-center">Nursing Exit</th>
                                <th class="py-3 text-center">Financial Clearance</th>
                                <th class="py-3 text-center">Overall Exit Status</th>
                                <th class="pe-3 py-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($admissions as $admission)
                                @php
                                    $bill = $admission->bill;
                                    $due = $bill ? (float)$bill->due_amount : 0;
                                    $ready = $admission->isReadyForDischarge();
                                @endphp
                                <tr>
                                    {{-- Admission & Bed --}}
                                    <td class="ps-3 py-3">
                                        <div class="fw-bold font-monospace text-navy">{{ $admission->admission_number }}</div>
                                        <div class="small text-muted">
                                            Adm: {{ $admission->admission_date ? $admission->admission_date->format('d M Y') : 'N/A' }}
                                        </div>
                                        <div class="mt-1">
                                            @if($admission->currentBed)
                                                <span class="badge bg-teal-subtle text-teal border border-teal-subtle">
                                                    <i class="bi bi-door-open me-1"></i>Bed {{ $admission->currentBed->bed_number }}
                                                </span>
                                            @elseif($admission->status === 'discharged')
                                                <span class="badge bg-light text-muted border">
                                                    <i class="bi bi-check-all me-1"></i>Bed Released
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted">No Bed</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Patient Details --}}
                                    <td class="py-3">
                                        <div class="fw-semibold text-navy">{{ $admission->patient->name }}</div>
                                        <div class="small text-muted font-monospace">{{ $admission->patient->patient_code }}</div>
                                        <div class="small text-muted">
                                            {{ ucfirst($admission->patient->gender) }} &middot; {{ $admission->patient->age ?? 'Age N/A' }}
                                        </div>
                                    </td>

                                    {{-- Doctor & Dept --}}
                                    <td class="py-3">
                                        <div class="small fw-semibold text-navy">
                                            {{ $admission->attendingDoctor ? $admission->attendingDoctor->name : 'Unassigned Doctor' }}
                                        </div>
                                        <div class="small text-muted">
                                            {{ $admission->department ? $admission->department->name : 'General Care' }}
                                        </div>
                                    </td>

                                    {{-- Clinical Clearance --}}
                                    <td class="py-3 text-center">
                                        @if($admission->clinical_clearance)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="bi bi-check-circle-fill me-1"></i>Cleared
                                            </span>
                                            <div class="text-muted" style="font-size: 0.7rem;">
                                                {{ $admission->clinical_clearance_at ? $admission->clinical_clearance_at->format('d M, h:i A') : '' }}
                                            </div>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                                <i class="bi bi-clock-history me-1"></i>Pending Dr.
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Nursing Exit Clearance --}}
                                    <td class="py-3 text-center">
                                        @if($admission->nursing_clearance)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="bi bi-check-circle-fill me-1"></i>Cleared
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted border px-2 py-1">
                                                <i class="bi bi-dash-circle me-1"></i>Pending Nurse
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Financial Clearance --}}
                                    <td class="py-3 text-center">
                                        @if($admission->financial_clearance)
                                            @if($admission->financial_exception_allowed)
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" title="Authorized Exception: {{ $admission->financial_exception_reason }}">
                                                    <i class="bi bi-shield-exclamation me-1"></i>Exception Granted
                                                </span>
                                                <div class="text-danger fw-semibold" style="font-size: 0.7rem;">
                                                    Due: &#2547;{{ number_format($due, 2) }}
                                                </div>
                                            @else
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Fully Settled
                                                </span>
                                            @endif
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                <i class="bi bi-exclamation-circle me-1"></i>Pending
                                            </span>
                                            @if($due > 0)
                                                <div class="text-danger fw-semibold" style="font-size: 0.7rem;">
                                                    Due: &#2547;{{ number_format($due, 2) }}
                                                </div>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Overall Exit Status --}}
                                    <td class="py-3 text-center">
                                        @if($admission->status === 'discharged')
                                            <span class="badge bg-secondary text-white px-2 py-1">
                                                <i class="bi bi-house-check-fill me-1"></i>Discharged
                                            </span>
                                            <div class="text-muted" style="font-size: 0.7rem;">
                                                {{ $admission->discharge_date ? $admission->discharge_date->format('d M, h:i A') : '' }}
                                            </div>
                                        @elseif($ready)
                                            <span class="badge bg-success text-white px-2 py-1 animate-pulse">
                                                <i class="bi bi-door-open-fill me-1"></i>Ready for Exit
                                            </span>
                                        @else
                                            <span class="badge bg-info-subtle text-navy border border-info-subtle px-2 py-1">
                                                In Progress
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="pe-3 py-3 text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('portal.discharges.show', $admission) }}" class="btn btn-navy" title="Open Discharge Dossier">
                                                <i class="bi bi-clipboard2-check me-1"></i>Dossier
                                            </a>
                                            @if($admission->dischargeSummary)
                                                <a href="{{ route('portal.discharges.print-summary', $admission) }}" target="_blank" class="btn btn-outline-secondary" title="Print Discharge Summary & Certificate">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-muted-hms"></i>
                                        No admission records matching the current clearance filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($admissions->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        {{ $admissions->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.portal>

