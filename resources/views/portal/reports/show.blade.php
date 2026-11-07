<x-layouts.portal role="admin" :title="$reportData['meta']['name']" activeNav="reports">
    <div class="container-fluid px-0">
        {{-- Page Header --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('portal.reports.index') }}" class="text-decoration-none text-muted-hms">Reports</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">{{ $reportData['meta']['name'] }}</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">{{ $reportData['meta']['name'] }}</h1>
                    @if(!empty($reportData['meta']['restricted']))
                        <span class="badge bg-warning text-dark font-monospace"><i class="bi bi-shield-lock me-1"></i>FINANCIAL</span>
                    @endif
                </div>
                <p class="text-muted-hms small mb-0 mt-1">
                    {{ $reportData['meta']['description'] }} &middot; Reporting Period: <strong class="text-teal">{{ $reportData['range_label'] }}</strong>
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('portal.reports.print', array_merge(['type' => $reportData['type']], request()->query())) }}" target="_blank" class="btn btn-navy d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-printer"></i>
                    <span>Print Report</span>
                </a>
                <a href="{{ route('portal.reports.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-arrow-left"></i>
                    <span>All Reports</span>
                </a>
            </div>
        </div>

        {{-- Filter & Date Range Bar --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <form action="{{ route('portal.reports.show', $reportData['type']) }}" method="GET" class="row g-2 align-items-end">
                    {{-- Quick Presets --}}
                    <div class="col-12 mb-2 pb-2 border-bottom d-flex flex-wrap gap-1 align-items-center">
                        <span class="text-muted small fw-semibold me-2">Quick Presets:</span>
                        <a href="{{ route('portal.reports.show', ['type' => $reportData['type'], 'preset' => 'today']) }}" class="btn btn-sm {{ request('preset') === 'today' ? 'btn-teal' : 'btn-light' }}">Today</a>
                        <a href="{{ route('portal.reports.show', ['type' => $reportData['type'], 'preset' => 'yesterday']) }}" class="btn btn-sm {{ request('preset') === 'yesterday' ? 'btn-teal' : 'btn-light' }}">Yesterday</a>
                        <a href="{{ route('portal.reports.show', ['type' => $reportData['type'], 'preset' => 'last_7_days']) }}" class="btn btn-sm {{ request('preset') === 'last_7_days' ? 'btn-teal' : 'btn-light' }}">Last 7 Days</a>
                        <a href="{{ route('portal.reports.show', ['type' => $reportData['type'], 'preset' => 'this_month']) }}" class="btn btn-sm {{ request('preset') === 'this_month' ? 'btn-teal' : 'btn-light' }}">This Month</a>
                        <a href="{{ route('portal.reports.show', ['type' => $reportData['type'], 'preset' => 'last_month']) }}" class="btn btn-sm {{ request('preset') === 'last_month' ? 'btn-teal' : 'btn-light' }}">Last Month</a>
                        <a href="{{ route('portal.reports.show', ['type' => $reportData['type'], 'preset' => 'this_year']) }}" class="btn btn-sm {{ request('preset') === 'this_year' ? 'btn-teal' : 'btn-light' }}">This Year</a>
                    </div>

                    {{-- Custom Date Inputs --}}
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold text-navy mb-1">Start Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date', $reportData['start_date']->format('Y-m-d')) }}">
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold text-navy mb-1">End Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date', $reportData['end_date']->format('Y-m-d')) }}">
                    </div>

                    {{-- Department Filter where applicable --}}
                    @if(in_array($reportData['type'], ['admission_discharge', 'bed_occupancy', 'department_performance', 'operation', 'revenue']))
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label small fw-semibold text-navy mb-1">Department</label>
                            <select name="department_id" class="form-select form-select-sm">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Search Input where applicable --}}
                    @if(in_array($reportData['type'], ['patient_registration', 'visit_history', 'due']))
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label small fw-semibold text-navy mb-1">Search Keyword</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, code, phone..." value="{{ request('search') }}">
                        </div>
                    @endif

                    {{-- Submit & Reset --}}
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-teal btn-sm flex-grow-1">
                            <i class="bi bi-funnel me-1"></i>Apply Filters
                        </button>
                        <a href="{{ route('portal.reports.show', $reportData['type']) }}" class="btn btn-light btn-sm" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Summary KPI Cards --}}
        <div class="row g-3 mb-4">
            @foreach($reportData['summary'] as $key => $val)
                <div class="col-6 col-md-3 col-xl">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-3">
                            <span class="text-muted small fw-semibold text-uppercase d-block mb-1">
                                {{ ucwords(str_replace('_', ' ', $key)) }}
                            </span>
                            <div class="h4 fw-bold text-navy mb-0">
                                @if(str_contains($key, 'amount') || str_contains($key, 'billed') || str_contains($key, 'due') || str_contains($key, 'collected') || str_contains($key, 'discounts'))
                                    &#2547;{{ number_format((float)$val, 2) }}
                                @elseif(str_contains($key, 'rate'))
                                    {{ $val }}%
                                @elseif(is_array($val))
                                    {{ count($val) }} Methods
                                @else
                                    {{ number_format((float)$val) }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Report Records Table --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-navy text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            {{-- Dynamic Table Headers based on Report Type --}}
                            @if($reportData['type'] === 'patient_registration')
                                <tr>
                                    <th class="ps-3 py-3">Patient Code</th>
                                    <th class="py-3">Name</th>
                                    <th class="py-3">Age / Gender</th>
                                    <th class="py-3">Phone</th>
                                    <th class="py-3">Reg Date</th>
                                    <th class="pe-3 py-3 text-center">Verification</th>
                                </tr>
                            @elseif($reportData['type'] === 'visit_history')
                                <tr>
                                    <th class="ps-3 py-3">Visit Number</th>
                                    <th class="py-3">Patient</th>
                                    <th class="py-3">Visit Date &amp; Time</th>
                                    <th class="py-3 text-center">Visit Type</th>
                                    <th class="py-3 text-center">Triage</th>
                                    <th class="pe-3 py-3 text-center">Status</th>
                                </tr>
                            @elseif($reportData['type'] === 'admission_discharge')
                                <tr>
                                    <th class="ps-3 py-3">Admission No</th>
                                    <th class="py-3">Patient</th>
                                    <th class="py-3">Department &amp; Bed</th>
                                    <th class="py-3">Admission Date</th>
                                    <th class="py-3">Discharge Date</th>
                                    <th class="py-3 text-center">Stay</th>
                                    <th class="pe-3 py-3 text-center">Status</th>
                                </tr>
                            @elseif($reportData['type'] === 'bed_occupancy')
                                <tr>
                                    <th class="ps-3 py-3">Bed Number</th>
                                    <th class="py-3">Department</th>
                                    <th class="py-3">Category &amp; Room</th>
                                    <th class="py-3 text-center">Daily Tariff</th>
                                    <th class="py-3 text-center">Status</th>
                                    <th class="pe-3 py-3">Current Inpatient</th>
                                </tr>
                            @elseif($reportData['type'] === 'department_performance')
                                <tr>
                                    <th class="ps-3 py-3">Department</th>
                                    <th class="py-3">Code</th>
                                    <th class="py-3 text-center">Outpatient Visits</th>
                                    <th class="py-3 text-center">Inpatient Admissions</th>
                                    <th class="py-3 text-center">Appointments</th>
                                    <th class="pe-3 py-3 text-center">Surgeries</th>
                                </tr>
                            @elseif($reportData['type'] === 'doctor_consultation')
                                <tr>
                                    <th class="ps-3 py-3">Doctor</th>
                                    <th class="py-3">Patient</th>
                                    <th class="py-3">Visit Ref</th>
                                    <th class="py-3">Date</th>
                                    <th class="pe-3 py-3 text-center">Status</th>
                                </tr>
                            @elseif($reportData['type'] === 'laboratory')
                                <tr>
                                    <th class="ps-3 py-3">Order Number</th>
                                    <th class="py-3">Patient</th>
                                    <th class="py-3">Order Date</th>
                                    <th class="py-3 text-center">Tests Count</th>
                                    <th class="py-3 text-end">Total Charge</th>
                                    <th class="pe-3 py-3 text-center">Report Status</th>
                                </tr>
                            @elseif($reportData['type'] === 'operation')
                                <tr>
                                    <th class="ps-3 py-3">OT Number</th>
                                    <th class="py-3">Patient</th>
                                    <th class="py-3">Procedure Name</th>
                                    <th class="py-3">Primary Surgeon</th>
                                    <th class="py-3">Scheduled Time</th>
                                    <th class="py-3 text-center">Urgency</th>
                                    <th class="pe-3 py-3 text-center">Status</th>
                                </tr>
                            @elseif($reportData['type'] === 'revenue')
                                <tr>
                                    <th class="ps-3 py-3">Invoice No</th>
                                    <th class="py-3">Patient</th>
                                    <th class="py-3">Bill Date</th>
                                    <th class="py-3 text-end">Gross Billed</th>
                                    <th class="py-3 text-end">Discount</th>
                                    <th class="py-3 text-end">Net Billed</th>
                                    <th class="py-3 text-end">Paid</th>
                                    <th class="py-3 text-end">Due</th>
                                    <th class="pe-3 py-3 text-center">Status</th>
                                </tr>
                            @elseif($reportData['type'] === 'payment')
                                <tr>
                                    <th class="ps-3 py-3">Receipt No</th>
                                    <th class="py-3">Invoice Ref</th>
                                    <th class="py-3">Patient</th>
                                    <th class="py-3">Payment Date</th>
                                    <th class="py-3 text-center">Method</th>
                                    <th class="py-3 text-end">Amount Paid</th>
                                    <th class="pe-3 py-3">Cashier</th>
                                </tr>
                            @elseif($reportData['type'] === 'due')
                                <tr>
                                    <th class="ps-3 py-3">Invoice No</th>
                                    <th class="py-3">Patient Name</th>
                                    <th class="py-3">Phone</th>
                                    <th class="py-3">Invoice Date</th>
                                    <th class="py-3 text-end">Total Billed</th>
                                    <th class="py-3 text-end">Paid Amount</th>
                                    <th class="pe-3 py-3 text-end">Outstanding Due</th>
                                </tr>
                            @elseif($reportData['type'] === 'refund')
                                <tr>
                                    <th class="ps-3 py-3">Voucher No</th>
                                    <th class="py-3">Invoice Ref</th>
                                    <th class="py-3">Patient</th>
                                    <th class="py-3">Refund Date</th>
                                    <th class="py-3 text-center">Method</th>
                                    <th class="py-3 text-end">Refunded Amount</th>
                                    <th class="pe-3 py-3">Authorized By</th>
                                </tr>
                            @endif
                        </thead>
                        <tbody class="divide-y">
                            @forelse($reportData['records'] as $row)
                                <tr>
                                    @if($reportData['type'] === 'patient_registration')
                                        <td class="ps-3 py-3 font-monospace text-navy fw-bold">{{ $row->patient_code }}</td>
                                        <td class="py-3 fw-semibold text-navy">{{ $row->name }}</td>
                                        <td class="py-3 small text-muted">{{ $row->age ?? 'N/A' }} / {{ ucfirst($row->gender) }}</td>
                                        <td class="py-3 font-monospace small text-muted">{{ $row->phone }}</td>
                                        <td class="py-3 small text-muted">{{ $row->created_at->format('d M Y') }}</td>
                                        <td class="pe-3 py-3 text-center">
                                            <span class="badge {{ $row->is_verified ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $row->is_verified ? 'Verified' : 'Unverified' }}
                                            </span>
                                        </td>
                                    @elseif($reportData['type'] === 'visit_history')
                                        <td class="ps-3 py-3 font-monospace text-navy fw-bold">{{ $row->visit_number }}</td>
                                        <td class="py-3 fw-semibold text-navy">{{ $row->patient ? $row->patient->name : 'N/A' }}</td>
                                        <td class="py-3 small text-muted">{{ $row->visit_date ? $row->visit_date->format('d M Y, h:i A') : 'N/A' }}</td>
                                        <td class="py-3 text-center">
                                            <span class="badge bg-light text-navy border text-uppercase">{{ $row->visit_type }}</span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="badge bg-{{ $row->triage_level === 'critical' ? 'danger' : ($row->triage_level === 'priority' ? 'warning text-dark' : 'info') }}">
                                                {{ ucfirst($row->triage_level) }}
                                            </span>
                                        </td>
                                        <td class="pe-3 py-3 text-center">
                                            <span class="badge bg-light text-muted border">{{ ucfirst($row->status) }}</span>
                                        </td>
                                    @elseif($reportData['type'] === 'admission_discharge')
                                        <td class="ps-3 py-3 font-monospace text-navy fw-bold">{{ $row->admission_number }}</td>
                                        <td class="py-3 fw-semibold text-navy">{{ $row->patient ? $row->patient->name : 'N/A' }}</td>
                                        <td class="py-3 small text-muted">
                                            {{ $row->department ? $row->department->name : 'General Care' }}
                                            @if($row->currentBed)
                                                <span class="badge bg-teal-subtle text-teal ms-1">Bed {{ $row->currentBed->bed_number }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 small text-muted">{{ $row->admission_date ? $row->admission_date->format('d M Y') : 'N/A' }}</td>
                                        <td class="py-3 small text-muted">{{ $row->discharge_date ? $row->discharge_date->format('d M Y') : 'Active' }}</td>
                                        <td class="py-3 text-center small text-teal fw-bold">{{ $row->stay_duration }}</td>
                                        <td class="pe-3 py-3 text-center">
                                            <span class="badge bg-{{ $row->status === 'discharged' ? 'secondary' : 'success' }}">{{ ucfirst($row->status) }}</span>
                                        </td>
                                    @elseif($reportData['type'] === 'bed_occupancy')
                                        <td class="ps-3 py-3 font-monospace text-navy fw-bold">Bed {{ $row->bed_number }}</td>
                                        <td class="py-3 small text-muted">{{ $row->department ? $row->department->name : 'General' }}</td>
                                        <td class="py-3 small text-muted">{{ ucfirst($row->category) }} &middot; {{ $row->room_number ?? 'Ward' }}</td>
                                        <td class="py-3 text-center font-monospace">&#2547;{{ number_format((float)$row->daily_charge, 2) }}</td>
                                        <td class="py-3 text-center">
                                            <span class="badge bg-{{ $row->status === 'occupied' ? 'danger' : ($row->status === 'available' ? 'success' : 'warning text-dark') }}">
                                                {{ ucfirst($row->status) }}
                                            </span>
                                        </td>
                                        <td class="pe-3 py-3 small text-navy">
                                            {{ $row->currentAssignment && $row->currentAssignment->admission && $row->currentAssignment->admission->patient ? $row->currentAssignment->admission->patient->name : 'Vacant' }}
                                        </td>
                                    @elseif($reportData['type'] === 'department_performance')
                                        <td class="ps-3 py-3 fw-bold text-navy">{{ $row->name }}</td>
                                        <td class="py-3 font-monospace small text-muted">{{ $row->code }}</td>
                                        <td class="py-3 text-center font-monospace">{{ $row->visits_count }}</td>
                                        <td class="py-3 text-center font-monospace">{{ $row->admissions_count }}</td>
                                        <td class="py-3 text-center font-monospace">{{ $row->appointments_count }}</td>
                                        <td class="pe-3 py-3 text-center font-monospace">{{ $row->operations_count }}</td>
                                    @elseif($reportData['type'] === 'doctor_consultation')
                                        <td class="ps-3 py-3 fw-bold text-navy">{{ $row->doctor ? $row->doctor->name : 'Medical Officer' }}</td>
                                        <td class="py-3 fw-semibold text-navy">{{ $row->visit && $row->visit->patient ? $row->visit->patient->name : 'N/A' }}</td>
                                        <td class="py-3 font-monospace small text-muted">{{ $row->visit ? $row->visit->visit_number : 'N/A' }}</td>
                                        <td class="py-3 small text-muted">{{ $row->created_at->format('d M Y, h:i A') }}</td>
                                        <td class="pe-3 py-3 text-center">
                                            <span class="badge {{ $row->finalized_at ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ $row->finalized_at ? 'Finalized' : 'In Progress' }}
                                            </span>
                                        </td>
                                    @elseif($reportData['type'] === 'laboratory')
                                        <td class="ps-3 py-3 font-monospace text-navy fw-bold">{{ $row->order_number }}</td>
                                        <td class="py-3 fw-semibold text-navy">{{ $row->patient ? $row->patient->name : 'N/A' }}</td>
                                        <td class="py-3 small text-muted">{{ $row->created_at ? $row->created_at->format('d M Y') : 'N/A' }}</td>
                                        <td class="py-3 text-center font-monospace">{{ $row->items ? $row->items->count() : 0 }}</td>
                                        <td class="py-3 text-end font-monospace">&#2547;{{ number_format((float)($row->total_charge ?? $row->total_amount ?? 0), 2) }}</td>
                                        <td class="pe-3 py-3 text-center">
                                            <span class="badge bg-{{ $row->status === 'completed' ? 'success' : 'info' }}">{{ ucfirst($row->status) }}</span>
                                        </td>
                                    @elseif($reportData['type'] === 'operation')
                                        <td class="ps-3 py-3 font-monospace text-navy fw-bold">{{ $row->operation_number }}</td>
                                        <td class="py-3 fw-semibold text-navy">{{ $row->patient ? $row->patient->name : 'N/A' }}</td>
                                        <td class="py-3 fw-semibold text-dark">{{ $row->procedure_name }}</td>
                                        <td class="py-3 small text-muted">{{ $row->primarySurgeon ? $row->primarySurgeon->name : 'Surgeon' }}</td>
                                        <td class="py-3 small text-muted">{{ $row->scheduled_at ? $row->scheduled_at->format('d M Y, h:i A') : 'N/A' }}</td>
                                        <td class="py-3 text-center">
                                            <span class="badge bg-{{ $row->urgency === 'emergency' ? 'danger' : 'primary' }} text-uppercase">{{ $row->urgency }}</span>
                                        </td>
                                        <td class="pe-3 py-3 text-center">
                                            <span class="badge bg-light text-navy border">{{ ucfirst($row->status) }}</span>
                                        </td>
                                    @elseif($reportData['type'] === 'revenue')
                                        <td class="ps-3 py-3 font-monospace text-teal fw-bold">{{ $row->invoice_number }}</td>
                                        <td class="py-3 fw-semibold text-navy">{{ $row->patient ? $row->patient->name : 'N/A' }}</td>
                                        <td class="py-3 small text-muted">{{ $row->bill_date ? $row->bill_date->format('d M Y') : 'N/A' }}</td>
                                        <td class="py-3 text-end font-monospace">&#2547;{{ number_format((float)$row->subtotal_amount, 2) }}</td>
                                        <td class="py-3 text-end font-monospace text-muted">&#2547;{{ number_format((float)$row->discount_amount, 2) }}</td>
                                        <td class="py-3 text-end font-monospace fw-bold text-navy">&#2547;{{ number_format((float)$row->total_amount, 2) }}</td>
                                        <td class="py-3 text-end font-monospace text-success">&#2547;{{ number_format((float)$row->paid_amount, 2) }}</td>
                                        <td class="py-3 text-end font-monospace text-danger">&#2547;{{ number_format((float)$row->due_amount, 2) }}</td>
                                        <td class="pe-3 py-3 text-center">
                                            <span class="badge bg-{{ $row->status === 'paid' ? 'success' : ($row->status === 'partial' ? 'warning text-dark' : 'danger') }}">{{ ucfirst($row->status) }}</span>
                                        </td>
                                    @elseif($reportData['type'] === 'payment')
                                        <td class="ps-3 py-3 font-monospace text-navy fw-bold">{{ $row->receipt_number }}</td>
                                        <td class="py-3 font-monospace small text-teal">{{ $row->bill ? $row->bill->invoice_number : 'N/A' }}</td>
                                        <td class="py-3 fw-semibold text-navy">{{ $row->bill && $row->bill->patient ? $row->bill->patient->name : 'N/A' }}</td>
                                        <td class="py-3 small text-muted">{{ $row->payment_date ? $row->payment_date->format('d M Y, h:i A') : 'N/A' }}</td>
                                        <td class="py-3 text-center">
                                            <span class="badge bg-navy text-uppercase font-monospace">{{ $row->payment_method }}</span>
                                        </td>
                                        <td class="py-3 text-end font-monospace fw-bold text-success">&#2547;{{ number_format((float)$row->amount, 2) }}</td>
                                        <td class="pe-3 py-3 small text-muted">{{ $row->receivedBy ? $row->receivedBy->name : 'Cashier' }}</td>
                                    @elseif($reportData['type'] === 'due')
                                        <td class="ps-3 py-3 font-monospace text-teal fw-bold">{{ $row->invoice_number }}</td>
                                        <td class="py-3 fw-semibold text-navy">{{ $row->patient ? $row->patient->name : 'N/A' }}</td>
                                        <td class="py-3 font-monospace small text-muted">{{ $row->patient ? $row->patient->phone : 'N/A' }}</td>
                                        <td class="py-3 small text-muted">{{ $row->bill_date ? $row->bill_date->format('d M Y') : 'N/A' }}</td>
                                        <td class="py-3 text-end font-monospace">&#2547;{{ number_format((float)$row->total_amount, 2) }}</td>
                                        <td class="py-3 text-end font-monospace text-success">&#2547;{{ number_format((float)$row->paid_amount, 2) }}</td>
                                        <td class="pe-3 py-3 text-end font-monospace fw-bold text-danger">&#2547;{{ number_format((float)$row->due_amount, 2) }}</td>
                                    @elseif($reportData['type'] === 'refund')
                                        <td class="ps-3 py-3 font-monospace text-navy fw-bold">{{ $row->refund_voucher ?? $row->refund_number }}</td>
                                        <td class="py-3 font-monospace small text-teal">{{ $row->bill ? $row->bill->invoice_number : 'N/A' }}</td>
                                        <td class="py-3 fw-semibold text-navy">{{ $row->bill && $row->bill->patient ? $row->bill->patient->name : 'N/A' }}</td>
                                        <td class="py-3 small text-muted">{{ $row->refunded_at ? $row->refunded_at->format('d M Y') : ($row->refund_date ? $row->refund_date->format('d M Y') : 'N/A') }}</td>
                                        <td class="py-3 text-center font-monospace text-uppercase small">{{ $row->refund_method }}</td>
                                        <td class="py-3 text-end font-monospace fw-bold text-danger">&#2547;{{ number_format((float)$row->amount, 2) }}</td>
                                        <td class="pe-3 py-3 small text-muted">{{ $row->approvedBy ? $row->approvedBy->name : 'Authority' }}</td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-muted-hms"></i>
                                        No records found for the selected reporting criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($reportData['records'], 'hasPages') && $reportData['records']->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        {{ $reportData['records']->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-layouts.portal>
