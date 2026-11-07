<x-layouts.admin title="Administrator Control Center" activeNav="dashboard">
    <div class="container-fluid px-0">
        {{-- Page Header --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Control Center</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold text-navy mb-0">Hospital Administration Overview</h1>
                <p class="text-muted-hms small mb-0 mt-1">
                    Meghna Clinic &amp; Nursing Home &middot; Shohid Minar Mor, Dinajpur &middot; Real-time Operations &amp; Clinical Health
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('portal.reports.index') }}" class="btn btn-outline-navy d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Reports &amp; Analytics</span>
                </a>
                <a href="{{ route('portal.admissions.index') }}" class="btn btn-outline-teal d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-door-open"></i>
                    <span>Inpatient Ward</span>
                </a>
                <a href="{{ route('portal.billing.index') }}" class="btn btn-navy d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-cash-stack"></i>
                    <span>Cashier Desk</span>
                </a>
            </div>
        </div>

        {{-- Primary Operational Bento KPIs (Actual Database Data) --}}
        <div class="row g-3 mb-4">
            {{-- Total Registered Patients --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 rounded-3">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Total Registered Patients</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary p-2 rounded-2">
                                <i class="bi bi-people-fill fs-6"></i>
                            </span>
                        </div>
                        <div class="h3 fw-bold text-navy mb-0">{{ number_format($metrics['total_patients']) }}</div>
                        <div class="text-muted small mt-1">
                            <span class="text-teal fw-semibold">+{{ $metrics['today_patients'] }}</span> today's patient intakes
                        </div>
                    </div>
                </div>
            </div>

            {{-- Today's Appointments --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 rounded-3">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Today's Appointments</span>
                            <span class="badge bg-teal bg-opacity-10 text-teal p-2 rounded-2">
                                <i class="bi bi-calendar2-check-fill fs-6"></i>
                            </span>
                        </div>
                        <div class="h3 fw-bold text-navy mb-0">{{ number_format($metrics['today_appointments']) }}</div>
                        <div class="text-muted small mt-1">
                            {{ $metrics['today_appointments_breakdown']['confirmed'] }} confirmed &middot; {{ $metrics['today_appointments_breakdown']['pending'] }} pending
                        </div>
                    </div>
                </div>
            </div>

            {{-- 10-Bed Inpatient Occupancy --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-4 border-info">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">10-Bed Occupancy</span>
                            <span class="badge bg-info bg-opacity-10 text-info p-2 rounded-2">
                                <i class="bi bi-hospital-fill fs-6"></i>
                            </span>
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <div class="h3 fw-bold text-navy mb-0">{{ $metrics['occupied_beds'] }} / {{ $metrics['total_beds'] }}</div>
                            <span class="badge bg-info text-white font-monospace">{{ $metrics['occupancy_rate'] }}%</span>
                        </div>
                        <div class="text-muted small mt-1">
                            <strong class="text-success">{{ $metrics['available_beds'] }} available</strong> &middot; {{ $metrics['cleaning_beds'] }} cleaning
                        </div>
                    </div>
                </div>
            </div>

            {{-- Today's Collections & Outstanding Due --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-4 border-success">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Today's Collections</span>
                            <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-2">
                                <i class="bi bi-cash-stack fs-6"></i>
                            </span>
                        </div>
                        <div class="h3 fw-bold text-success mb-0">&#2547;{{ number_format($metrics['today_payments'], 2) }}</div>
                        <div class="text-danger small mt-1">
                            Outstanding Dues: <strong>&#2547;{{ number_format($metrics['outstanding_due'], 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Second Row: Charts & Visualizations --}}
        <div class="row g-4 mb-4">
            {{-- 7-Day Patient Volume & Collections Trend Chart --}}
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-navy mb-0"><i class="bi bi-graph-up text-teal me-2"></i>7-Day Outpatient &amp; Admissions Trend</h5>
                        <span class="badge bg-light text-navy border">Last 7 Days Activity</span>
                    </div>
                    <div class="card-body p-3">
                        <canvas id="trendChart" style="max-height: 280px;"></canvas>
                    </div>
                </div>
            </div>

            {{-- 10-Bed Inpatient Status Chart --}}
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold text-navy mb-0"><i class="bi bi-door-open text-navy me-2"></i>Inpatient Ward Bed Status</h5>
                    </div>
                    <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                        <div style="width: 200px; height: 200px;">
                            <canvas id="bedStatusChart"></canvas>
                        </div>
                        <div class="row w-100 text-center mt-3 g-2 small">
                            <div class="col-4">
                                <div class="p-1 bg-light rounded">
                                    <div class="fw-bold text-danger">{{ $metrics['occupied_beds'] }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">Occupied</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-1 bg-light rounded">
                                    <div class="fw-bold text-success">{{ $metrics['available_beds'] }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">Available</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-1 bg-light rounded">
                                    <div class="fw-bold text-warning">{{ $metrics['cleaning_beds'] }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">Cleaning</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Third Row: Operational Highlights (Departments, Lab, Discharges) --}}
        <div class="row g-4 mb-4">
            {{-- Department Patient Statistics --}}
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-navy mb-0"><i class="bi bi-diagram-3 text-teal me-2"></i>Department-wise Patient Load</h5>
                        <a href="{{ route('portal.reports.show', 'department_performance') }}" class="small text-teal text-decoration-none">View Detailed Report &rarr;</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small text-uppercase">
                                    <tr>
                                        <th class="ps-3 py-2">Department</th>
                                        <th class="py-2 text-center">Total Visits</th>
                                        <th class="py-2 text-center">Active Inpatients</th>
                                        <th class="pe-3 py-2 text-center">Today's Appts</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($metrics['department_stats'] as $dept)
                                        <tr>
                                            <td class="ps-3 py-2 fw-semibold text-navy">{{ $dept->name }}</td>
                                            <td class="py-2 text-center font-monospace">{{ $dept->total_visits }}</td>
                                            <td class="py-2 text-center">
                                                @if($dept->active_admissions > 0)
                                                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle font-monospace">{{ $dept->active_admissions }}</span>
                                                @else
                                                    <span class="text-muted small">0</span>
                                                @endif
                                            </td>
                                            <td class="pe-3 py-2 text-center font-monospace">{{ $dept->today_appointments }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-3 text-muted">No clinical departments registered.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Laboratory & Discharge Pulse --}}
            <div class="col-12 col-lg-6">
                <div class="row g-3 h-100">
                    {{-- Lab Status Card --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold text-navy mb-0"><i class="bi bi-file-earmark-medical text-teal me-1"></i>Diagnostic Laboratory Status</h6>
                                    <a href="{{ route('portal.reports.show', 'laboratory') }}" class="small text-teal text-decoration-none">Lab Report &rarr;</a>
                                </div>
                                <div class="row g-2 text-center">
                                    <div class="col-3">
                                        <div class="p-2 border rounded bg-light">
                                            <div class="h4 fw-bold text-navy mb-0">{{ $metrics['lab_report_status']['total'] }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">Total Reports</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="p-2 border border-warning-subtle rounded bg-warning-subtle">
                                            <div class="h4 fw-bold text-warning-emphasis mb-0">{{ $metrics['lab_report_status']['pending'] }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">Pending</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="p-2 border border-success-subtle rounded bg-success-subtle">
                                            <div class="h4 fw-bold text-success mb-0">{{ $metrics['lab_report_status']['verified'] }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">Verified</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="p-2 border rounded bg-light">
                                            <div class="h4 fw-bold text-teal mb-0">{{ $metrics['lab_report_status']['today_orders'] }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">Today Orders</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Discharge Pulse Card --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold text-navy mb-0"><i class="bi bi-box-arrow-right text-success me-1"></i>Discharge Clearance Station</h6>
                                    <a href="{{ route('portal.discharges.index') }}" class="small text-teal text-decoration-none">Clearance Workbench &rarr;</a>
                                </div>
                                <div class="row g-2 text-center">
                                    <div class="col-3">
                                        <div class="p-2 border rounded bg-light">
                                            <div class="h4 fw-bold text-navy mb-0">{{ $metrics['discharged_today'] }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">Discharged Today</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="p-2 border border-success-subtle rounded bg-success-subtle">
                                            <div class="h4 fw-bold text-success mb-0">{{ $metrics['dual_clearance_ready'] }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">Ready for Exit</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="p-2 border border-info-subtle rounded bg-info-subtle">
                                            <div class="h4 fw-bold text-navy mb-0">{{ $metrics['pending_clinical_clearance'] }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">Need Doctor</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="p-2 border border-warning-subtle rounded bg-warning-subtle">
                                            <div class="h4 fw-bold text-warning-emphasis mb-0">{{ $metrics['pending_financial_clearance'] }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">Need Cashier</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fourth Row: Security Audit Trail & Financial Breakdown --}}
        <div class="row g-4">
            {{-- Audit Logs --}}
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold text-navy mb-0"><i class="bi bi-shield-check text-navy me-2"></i>Security &amp; Clinical Audit Stream</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light small text-uppercase">
                                    <tr>
                                        <th class="ps-3 py-2">Timestamp</th>
                                        <th class="py-2">Actor</th>
                                        <th class="py-2">Event</th>
                                        <th class="pe-3 py-2">Resource</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($metrics['recent_audit_logs'] as $log)
                                        <tr>
                                            <td class="ps-3 py-2 font-monospace small text-muted">{{ $log->created_at->format('d M, h:i A') }}</td>
                                            <td class="py-2 small fw-semibold text-navy">{{ $log->user ? $log->user->name : 'System' }}</td>
                                            <td class="py-2">
                                                <span class="badge bg-light text-navy border font-monospace" style="font-size: 0.7rem;">
                                                    {{ $log->event }}
                                                </span>
                                            </td>
                                            <td class="pe-3 py-2 small text-muted font-monospace" style="font-size: 0.75rem;">
                                                {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-3 text-muted small">No audit logs recorded yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment Method Distribution --}}
            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-navy mb-0"><i class="bi bi-pie-chart text-teal me-2"></i>This Month's Collections</h5>
                        <span class="badge bg-success font-monospace">&#2547;{{ number_format($metrics['this_month_collections'], 2) }}</span>
                    </div>
                    <div class="card-body p-3">
                        <ul class="list-group list-group-flush small">
                            @forelse($metrics['payment_methods_distribution'] as $method)
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-navy me-2 font-monospace text-uppercase">{{ $method->payment_method }}</span>
                                        <span class="text-muted">({{ $method->count }} transactions)</span>
                                    </div>
                                    <span class="fw-bold text-navy font-monospace">&#2547;{{ number_format((float)$method->total_amount, 2) }}</span>
                                </li>
                            @empty
                                <li class="list-group-item px-0 text-muted text-center py-3">No payments recorded this month yet.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Chart.js Initialization --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. 7-Day Trend Chart
            const trendCtx = document.getElementById('trendChart');
            if (trendCtx) {
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($metrics['trends_7_days']['labels']) !!},
                        datasets: [
                            {
                                label: 'Outpatient Visits',
                                data: {!! json_encode($metrics['trends_7_days']['visits']) !!},
                                borderColor: '#087F8C',
                                backgroundColor: 'rgba(8, 127, 140, 0.1)',
                                tension: 0.3,
                                fill: true,
                            },
                            {
                                label: 'Inpatient Admissions',
                                data: {!! json_encode($metrics['trends_7_days']['admissions']) !!},
                                borderColor: '#123B5D',
                                backgroundColor: 'rgba(18, 59, 93, 0.1)',
                                tension: 0.3,
                                fill: true,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top' }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 }
                            }
                        }
                    }
                });
            }

            // 2. Bed Status Doughnut Chart
            const bedCtx = document.getElementById('bedStatusChart');
            if (bedCtx) {
                new Chart(bedCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Occupied', 'Available', 'Cleaning', 'Maintenance'],
                        datasets: [{
                            data: [
                                {{ $metrics['occupied_beds'] }},
                                {{ $metrics['available_beds'] }},
                                {{ $metrics['cleaning_beds'] }},
                                {{ $metrics['maintenance_beds'] }}
                            ],
                            backgroundColor: ['#EF4444', '#10B981', '#F59E0B', '#64748B'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        cutout: '65%'
                    }
                });
            }
        });
    </script>
    @endpush
</x-layouts.admin>

