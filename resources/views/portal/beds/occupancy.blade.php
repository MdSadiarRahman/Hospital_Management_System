<x-layouts.portal role="admin" title="10-Bed Live Occupancy Dashboard">
    <div class="container-fluid px-0">
        <!-- Page Header & Action Bar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('portal.beds.index') }}" class="text-decoration-none text-muted-hms">Beds</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Occupancy Dashboard</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">10-Bed Live Occupancy Dashboard</h1>
                    <span class="badge bg-teal text-white font-monospace">Real-time Inpatient Status</span>
                    <span class="badge bg-navy text-white font-monospace">{{ $metrics['occupancy_rate'] }}% Ward Occupancy</span>
                </div>
                <p class="text-muted-hms small mb-0 mt-1">
                    Visual floor distribution, real-time bed allocations, patient movement tracking, and terminal sanitization management.
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('portal.admissions.create') }}" class="btn btn-teal d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Admit Inpatient</span>
                </a>

                <a href="{{ route('portal.beds.transfers') }}" class="btn btn-outline-navy d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-clock-history"></i>
                    <span>Transfer History</span>
                </a>
            </div>
        </div>

        <!-- Navigation Tabs: Live Occupancy vs Directory vs Transfers -->
        <div class="card border-0 shadow-xs mb-4">
            <div class="card-body p-2">
                <ul class="nav nav-pills nav-fill gap-2" style="font-size: 0.88rem;">
                    <li class="nav-item">
                        <a class="nav-link active fw-semibold d-flex align-items-center justify-content-center gap-2 bg-navy text-white" href="{{ route('portal.beds.occupancy') }}">
                            <i class="bi bi-grid-3x3-gap-fill text-teal"></i>
                            <span>Visual Occupancy Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold d-flex align-items-center justify-content-center gap-2 text-navy" href="{{ route('portal.beds.index') }}">
                            <i class="bi bi-table"></i>
                            <span>Beds Inventory & Tariffs</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold d-flex align-items-center justify-content-center gap-2 text-navy" href="{{ route('portal.beds.transfers') }}">
                            <i class="bi bi-arrow-left-right"></i>
                            <span>Transfer & Movement Audit</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Alert Notifications -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Notification:</div>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Bento KPI Grid: Bed Availability Summary -->
        <div class="row g-3 mb-4">
            <!-- Total Hospital Capacity -->
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-xs h-100 p-3 bg-white" style="border-left: 4px solid #123B5D !important;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted-hms text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Capacity</span>
                        <i class="bi bi-hospital fs-5 text-navy"></i>
                    </div>
                    <h3 class="fw-bold text-navy mb-0">{{ $metrics['total_capacity'] }} Beds</h3>
                    <span class="text-muted-hms small" style="font-size: 0.72rem;">Meghna Clinic Ward</span>
                </div>
            </div>

            <!-- Available (Ready for Admission) -->
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-xs h-100 p-3 bg-white" style="border-left: 4px solid #10B981 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted-hms text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Available</span>
                        <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    </div>
                    <h3 class="fw-bold text-success mb-0">{{ $metrics['available'] }}</h3>
                    <span class="text-success small" style="font-size: 0.72rem;">Ready for Inpatients</span>
                </div>
            </div>

            <!-- Occupied (Active Inpatients) -->
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-xs h-100 p-3 bg-white" style="border-left: 4px solid #EF4444 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted-hms text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Occupied</span>
                        <i class="bi bi-person-fill-lock fs-5 text-danger"></i>
                    </div>
                    <h3 class="fw-bold text-danger mb-0">{{ $metrics['occupied'] }}</h3>
                    <span class="text-danger small" style="font-size: 0.72rem;">{{ $metrics['occupancy_rate'] }}% Occupancy</span>
                </div>
            </div>

            <!-- Cleaning & Sanitization -->
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-xs h-100 p-3 bg-white" style="border-left: 4px solid #087F8C !important;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted-hms text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Cleaning</span>
                        <i class="bi bi-stars fs-5 text-teal"></i>
                    </div>
                    <h3 class="fw-bold text-teal mb-0">{{ $metrics['cleaning'] }}</h3>
                    <span class="text-teal small" style="font-size: 0.72rem;">Sanitization Queue</span>
                </div>
            </div>

            <!-- Maintenance & Repairs -->
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-xs h-100 p-3 bg-white" style="border-left: 4px solid #6B7280 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted-hms text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Maintenance</span>
                        <i class="bi bi-tools fs-5 text-secondary"></i>
                    </div>
                    <h3 class="fw-bold text-secondary mb-0">{{ $metrics['maintenance'] }}</h3>
                    <span class="text-muted-hms small" style="font-size: 0.72rem;">Out of Service</span>
                </div>
            </div>

            <!-- Reserved Beds -->
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-xs h-100 p-3 bg-white" style="border-left: 4px solid #F59E0B !important;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted-hms text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Reserved</span>
                        <i class="bi bi-clock-history fs-5 text-warning"></i>
                    </div>
                    <h3 class="fw-bold text-warning mb-0">{{ $metrics['reserved'] }}</h3>
                    <span class="text-muted-hms small" style="font-size: 0.72rem;">Planned Intake</span>
                </div>
            </div>
        </div>

        <!-- Visual Floor Ward Map (Grouped by Department & General Beds) -->
        <div class="mb-4">
            <!-- 1. Clinical Departments (Gynecology, Orthopedic, Surgery, Emergency) -->
            @foreach($department_beds as $dept)
                <div class="card border-0 shadow-xs mb-4">
                    <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-navy bg-opacity-10 text-navy p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-diagram-3-fill"></i>
                            </span>
                            <div>
                                <h5 class="fw-bold text-navy mb-0">{{ $dept->name }} Ward</h5>
                                <span class="text-muted-hms small font-monospace">Ward Code: {{ $dept->code }} · {{ $dept->description ?? 'Specialized Inpatient Care' }}</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-navy border font-monospace">
                                {{ $dept->beds->where('status', 'available')->count() }} / {{ $dept->beds->count() }} Available
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-3 pt-0">
                        <div class="row g-3">
                            @forelse($dept->beds as $bed)
                                <div class="col-12 col-md-6 col-xl-4">
                                    @include('portal.beds.partials.bed_pod', ['bed' => $bed, 'availableBeds' => $available_beds])
                                </div>
                            @empty
                                <div class="col-12 text-center py-3 text-muted-hms small">
                                    No dedicated beds configured in this department yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- 2. General Ward Beds (Common / Multi-Department) -->
            @if($general_beds->isNotEmpty())
                <div class="card border-0 shadow-xs mb-4">
                    <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-teal bg-opacity-10 text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-door-open-fill"></i>
                            </span>
                            <div>
                                <h5 class="fw-bold text-navy mb-0">General Inpatient Ward (Common Beds)</h5>
                                <span class="text-muted-hms small font-monospace">Non-departmental inpatient beds for general observation and flexible patient intake</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-teal border font-monospace">
                                {{ $general_beds->where('status', 'available')->count() }} / {{ $general_beds->count() }} Available
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-3 pt-0">
                        <div class="row g-3">
                            @foreach($general_beds as $bed)
                                <div class="col-12 col-md-6 col-xl-4">
                                    @include('portal.beds.partials.bed_pod', ['bed' => $bed, 'availableBeds' => $available_beds])
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Recent Bed Movement & Transfer Feed -->
        <div class="card border-0 shadow-xs mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded bg-primary bg-opacity-10 text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="bi bi-arrow-left-right"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold text-navy mb-0">Recent Bed Transfers & Patient Movement</h5>
                        <span class="text-muted-hms small">Audit trail of inpatient bed transfers and clinical justifications</span>
                    </div>
                </div>
                <a href="{{ route('portal.beds.transfers') }}" class="btn btn-sm btn-outline-navy">
                    View Full Audit History
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light text-muted-hms text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-3">Patient</th>
                            <th>Occupied Bed</th>
                            <th>Stay Period</th>
                            <th>Transfer Justification</th>
                            <th class="text-end pe-3">Action By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_transfers as $tx)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold text-navy">{{ $tx->admission?->patient?->name ?? 'Patient' }}</div>
                                    <div class="small text-muted-hms font-monospace">
                                        {{ $tx->admission?->patient?->patient_code ?? 'MCNH-N/A' }} · Adm: {{ $tx->admission?->admission_number ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-teal text-white font-monospace px-2 py-1">
                                        Bed {{ $tx->bed?->bed_number }}
                                    </span>
                                    <span class="small text-muted-hms ms-1">{{ $tx->bed?->room_number ?? 'Ward Room' }}</span>
                                </td>
                                <td>
                                    <span class="d-block text-navy fw-medium">{{ $tx->assigned_at ? $tx->assigned_at->format('d M, h:i A') : '—' }}</span>
                                    <span class="d-block small text-muted-hms">
                                        {{ $tx->released_at ? 'Released: ' . $tx->released_at->format('d M, h:i A') : 'Current Occupant' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-navy">{{ $tx->transfer_reason ?? 'Initial Allocation' }}</span>
                                </td>
                                <td class="text-end pe-3">
                                    <span class="small text-muted-hms">{{ $tx->assignedBy?->name ?? 'System' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted-hms small">
                                    No patient bed transfers recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modals: Transfer Bed Modal -->
    <div class="modal fade" id="transferOccupantModal" tabindex="-1" aria-labelledby="transferOccupantModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="transferOccupantForm" method="POST" action="">
                    @csrf
                    <div class="modal-header border-0 bg-teal text-white">
                        <h5 class="modal-title fw-bold" id="transferOccupantModalLabel">
                            <i class="bi bi-arrow-left-right me-2"></i>Transfer Patient to Another Bed
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="p-3 rounded bg-light border mb-3">
                            <div class="small text-muted-hms">Current Bed & Occupant:</div>
                            <div class="fw-bold text-navy fs-6" id="transferCurrentBedDisplay">Bed EM-01</div>
                            <div class="small text-teal fw-medium" id="transferCurrentPatientDisplay">Patient: Most. Rokeya Akhter</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-navy">
                                Target Destination Bed <span class="text-danger">*</span>
                            </label>
                            <select name="target_bed_id" id="transferTargetBedSelect" class="form-select" required>
                                <option value="">-- Choose an Available Bed --</option>
                                @foreach($available_beds as $ab)
                                    <option value="{{ $ab->id }}">
                                        Bed {{ $ab->bed_number }} ({{ $ab->room_number ?? 'Room N/A' }}) — {{ $ab->department?->name ?? 'General Ward' }} · ৳{{ number_format((float)$ab->daily_charge, 2) }}/day [{{ $ab->category_label }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-navy">
                                Transfer Reason & Clinical Justification <span class="text-danger">*</span>
                            </label>
                            <textarea name="reason" rows="3" class="form-control" placeholder="E.g., Patient stabilized, moved to General Ward; or patient requested single cabin..." required></textarea>
                        </div>

                        <div class="alert alert-info border-0 p-2 mb-0 small text-navy">
                            <i class="bi bi-info-circle me-1"></i>
                            The previous bed will automatically be released and placed into <strong>Cleaning</strong> queue. It cannot receive a new patient until sanitization is complete.
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-teal">Confirm Transfer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals: Complete Cleaning Modal -->
    <div class="modal fade" id="completeCleaningModal" tabindex="-1" aria-labelledby="completeCleaningModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="completeCleaningForm" method="POST" action="">
                    @csrf
                    <div class="modal-header border-0 bg-success text-white">
                        <h5 class="modal-title fw-bold" id="completeCleaningModalLabel">
                            <i class="bi bi-stars me-2"></i>Certify Bed Cleaning & Sanitization
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-navy small mb-3">
                            Certifying that <strong id="cleaningBedDisplay">Bed EM-01</strong> has undergone terminal sanitization, linen replacement, and frame disinfection according to clinic infection control protocols.
                        </p>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-navy">
                                Cleaning Notes / Disinfection Certification <span class="text-muted-hms fw-normal">(Optional)</span>
                            </label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="Linen changed, surfaces disinfected with hospital-grade solution, verified by nursing staff..."></textarea>
                        </div>

                        <div class="form-check p-3 rounded bg-light border">
                            <input class="form-check-input ms-0 me-2" type="checkbox" id="sanitizationCheck" required>
                            <label class="form-check-label small fw-semibold text-navy" for="sanitizationCheck">
                                I confirm this bed is fully sanitized and ready for inpatient admission.
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Certify & Mark Available</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals: Complete Maintenance Modal -->
    <div class="modal fade" id="completeMaintenanceModal" tabindex="-1" aria-labelledby="completeMaintenanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="completeMaintenanceForm" method="POST" action="">
                    @csrf
                    <div class="modal-header border-0 bg-secondary text-white">
                        <h5 class="modal-title fw-bold" id="completeMaintenanceModalLabel">
                            <i class="bi bi-tools me-2"></i>Complete Bed Maintenance
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-navy small mb-3">
                            Verify engineering repairs for <strong id="maintenanceBedDisplay">Bed SUR-01</strong> have been completed.
                        </p>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-navy">
                                Repair Resolution Notes
                            </label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="E.g., Traction pulleys repaired, wheel lock replaced, oxygen port pressure tested..."></textarea>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="requires_cleaning" value="1" id="requiresCleaningCheck" checked>
                            <label class="form-check-label small text-navy" for="requiresCleaningCheck">
                                Require sanitization before making available (Place in Cleaning queue)
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-navy">Verify Repairs</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openTransferOccupantModal(bedId, bedNumber, patientName, patientCode) {
            const form = document.getElementById('transferOccupantForm');
            form.action = `{{ url('/portal/beds') }}/${bedId}/transfer`;

            document.getElementById('transferCurrentBedDisplay').textContent = `Bed ${bedNumber}`;
            document.getElementById('transferCurrentPatientDisplay').textContent = `Patient: ${patientName} (${patientCode})`;

            // Hide the current bed from the destination dropdown
            const select = document.getElementById('transferTargetBedSelect');
            Array.from(select.options).forEach(opt => {
                if (opt.value === String(bedId)) {
                    opt.disabled = true;
                } else {
                    opt.disabled = false;
                }
            });

            new bootstrap.Modal(document.getElementById('transferOccupantModal')).show();
        }

        function openCompleteCleaningModal(bedId, bedNumber) {
            const form = document.getElementById('completeCleaningForm');
            form.action = `{{ url('/portal/beds') }}/${bedId}/complete-cleaning`;
            document.getElementById('cleaningBedDisplay').textContent = `Bed ${bedNumber}`;

            new bootstrap.Modal(document.getElementById('completeCleaningModal')).show();
        }

        function openCompleteMaintenanceModal(bedId, bedNumber) {
            const form = document.getElementById('completeMaintenanceForm');
            form.action = `{{ url('/portal/beds') }}/${bedId}/complete-maintenance`;
            document.getElementById('maintenanceBedDisplay').textContent = `Bed ${bedNumber}`;

            new bootstrap.Modal(document.getElementById('completeMaintenanceModal')).show();
        }
    </script>
    @endpush
</x-layouts.portal>
