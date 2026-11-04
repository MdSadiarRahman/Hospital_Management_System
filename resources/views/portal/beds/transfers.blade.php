<x-layouts.portal role="admin" title="Bed Transfers & Movement History">
    <div class="container-fluid px-0">
        <!-- Page Header & Action Bar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('portal.beds.index') }}" class="text-decoration-none text-muted-hms">Beds</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Transfer History</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">Inpatient Bed Transfers & Movement Audit</h1>
                    <span class="badge bg-teal text-white font-monospace">{{ $transfers->total() }} Transfer Records</span>
                </div>
                <p class="text-muted-hms small mb-0 mt-1">
                    Traceable clinical history of patient relocations across General and Department wards.
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('portal.beds.occupancy') }}" class="btn btn-navy d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-grid-3x3-gap-fill text-teal"></i>
                    <span>Live Occupancy Dashboard</span>
                </a>
                <a href="{{ route('portal.admissions.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-journal-medical"></i>
                    <span>Admissions</span>
                </a>
            </div>
        </div>

        <!-- Navigation Tabs: Live Occupancy vs Directory vs Transfers -->
        <div class="card border-0 shadow-xs mb-4">
            <div class="card-body p-2">
                <ul class="nav nav-pills nav-fill gap-2" style="font-size: 0.88rem;">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold d-flex align-items-center justify-content-center gap-2 text-navy" href="{{ route('portal.beds.occupancy') }}">
                            <i class="bi bi-grid-3x3-gap-fill"></i>
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
                        <a class="nav-link active fw-semibold d-flex align-items-center justify-content-center gap-2 bg-navy text-white" href="{{ route('portal.beds.transfers') }}">
                            <i class="bi bi-arrow-left-right text-teal"></i>
                            <span>Transfer & Movement Audit</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Transfers Table -->
        <div class="card border-0 shadow-xs mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold text-navy mb-0">Bed Assignment & Transfer Ledger</h5>
                    <span class="text-muted-hms small">Complete historical log of all bed allocations</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light text-muted-hms text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-3">Patient</th>
                            <th>Allocated Bed</th>
                            <th>Admission #</th>
                            <th>Assigned Date</th>
                            <th>Released Date</th>
                            <th>Stay On Bed</th>
                            <th>Transfer Reason / Justification</th>
                            <th class="text-end pe-3">Assigned By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $assignment)
                            <tr class="{{ is_null($assignment->released_at) ? 'table-success bg-opacity-25' : '' }}">
                                <!-- Patient -->
                                <td class="ps-3">
                                    <div class="fw-semibold text-navy">
                                        {{ $assignment->admission?->patient?->name ?? 'Unknown Patient' }}
                                    </div>
                                    <span class="small text-muted-hms font-monospace">
                                        {{ $assignment->admission?->patient?->patient_code ?? 'MCNH' }}
                                    </span>
                                </td>

                                <!-- Bed Details -->
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge {{ is_null($assignment->released_at) ? 'bg-success' : 'bg-secondary' }} text-white font-monospace px-2 py-1">
                                            Bed {{ $assignment->bed?->bed_number ?? 'N/A' }}
                                        </span>
                                        <div>
                                            <span class="d-block fw-semibold text-navy small">{{ $assignment->bed?->room_number ?? 'Ward Room' }}</span>
                                            <span class="d-block text-muted-hms" style="font-size: 0.72rem;">{{ $assignment->bed?->department?->name ?? 'General Ward' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Admission Number -->
                                <td>
                                    @if($assignment->admission)
                                        <a href="{{ route('portal.admissions.show', $assignment->admission_id) }}" class="font-monospace text-teal text-decoration-none fw-semibold">
                                            {{ $assignment->admission->admission_number }}
                                        </a>
                                    @else
                                        <span class="text-muted-hms">N/A</span>
                                    @endif
                                </td>

                                <!-- Assigned At -->
                                <td>
                                    <span class="text-navy fw-medium">
                                        {{ $assignment->assigned_at ? $assignment->assigned_at->format('d M Y, h:i A') : '—' }}
                                    </span>
                                </td>

                                <!-- Released At -->
                                <td>
                                    @if($assignment->released_at)
                                        <span class="text-muted-hms">
                                            {{ $assignment->released_at->format('d M Y, h:i A') }}
                                        </span>
                                    @else
                                        <span class="badge bg-success text-white">Current Active</span>
                                    @endif
                                </td>

                                <!-- Stay Duration on Bed -->
                                <td>
                                    @php
                                        $end = $assignment->released_at ?? now();
                                        $hours = (int) $assignment->assigned_at->diffInHours($end);
                                        $days = intdiv($hours, 24);
                                        $remHours = $hours % 24;
                                    @endphp
                                    <span class="fw-medium text-navy">
                                        @if($days > 0) {{ $days }}d {{ $remHours }}h @else {{ $remHours }}h @endif
                                    </span>
                                </td>

                                <!-- Transfer Reason -->
                                <td>
                                    <span class="small text-navy">
                                        {{ $assignment->transfer_reason ?? 'Initial admission assignment' }}
                                    </span>
                                </td>

                                <!-- Assigned By Staff -->
                                <td class="text-end pe-3">
                                    <span class="small text-muted-hms">
                                        {{ $assignment->assignedBy?->name ?? 'System' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted-hms">
                                    <i class="bi bi-arrow-left-right fs-1 text-muted d-block mb-2"></i>
                                    <div class="fw-semibold">No bed transfers recorded.</div>
                                    <p class="small text-muted-hms">Bed assignment and patient transfer events will appear here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transfers->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $transfers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.portal>
