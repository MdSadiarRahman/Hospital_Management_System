@php
    $statusColor = match($bed->status) {
        'available' => 'success',
        'occupied' => 'danger',
        'cleaning' => 'teal',
        'maintenance' => 'secondary',
        'reserved' => 'warning',
        default => 'dark'
    };
@endphp

<div class="card border-0 shadow-xs h-100 rounded-3 position-relative overflow-hidden" 
     style="border-top: 4px solid var(--bs-{{ $statusColor }}, #10B981) !important; background-color: #ffffff;">
    <div class="card-body p-3 d-flex flex-column justify-content-between">
        <!-- Bed Header -->
        <div>
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded bg-light text-navy p-2 d-inline-flex align-items-center justify-content-center fw-bold font-monospace" style="width: 38px; height: 38px;">
                        <i class="bi bi-door-open fs-5"></i>
                    </span>
                    <div>
                        <h4 class="h6 fw-bold text-navy mb-0 font-monospace">Bed {{ $bed->bed_number }}</h4>
                        <span class="text-muted-hms small" style="font-size: 0.75rem;">
                            {{ $bed->room_number ?? 'Ward Room' }} · ৳{{ number_format((float)$bed->daily_charge, 2) }}/day
                        </span>
                    </div>
                </div>

                <span class="badge bg-{{ $bed->status_badge_class }} text-white text-uppercase px-2 py-1" style="font-size: 0.7rem;">
                    {{ ucfirst($bed->status) }}
                </span>
            </div>

            <!-- Dynamic Body Content Based on Bed Status -->
            @if($bed->status === 'occupied' && $bed->currentAssignment)
                @php
                    $occupant = $bed->currentAssignment->admission?->patient;
                    $admission = $bed->currentAssignment->admission;
                    $doctor = $admission?->attendingDoctor;
                @endphp
                <div class="p-2.5 rounded bg-danger bg-opacity-10 border border-danger-subtle mb-3" style="font-size: 0.8rem;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="fw-bold text-danger text-truncate">
                            <i class="bi bi-person-fill me-1"></i>{{ $occupant?->name ?? 'Occupant' }}
                        </span>
                        <span class="badge bg-danger text-white font-monospace" style="font-size: 0.65rem;">
                            {{ $occupant?->patient_code ?? 'MCNH' }}
                        </span>
                    </div>

                    <div class="text-navy small mb-1">
                        <span class="text-muted-hms">Adm #:</span>
                        <a href="{{ $admission ? route('portal.admissions.show', $admission) : '#' }}" class="text-navy fw-semibold text-decoration-none font-monospace">
                            {{ $admission?->admission_number ?? 'N/A' }}
                        </a>
                    </div>

                    <div class="text-muted-hms small mb-1">
                        <i class="bi bi-person-badge text-teal me-1"></i>Dr. {{ $doctor?->name ?? 'Medical Officer' }}
                    </div>

                    <div class="d-flex justify-content-between text-muted-hms small pt-1 border-top border-danger-subtle">
                        <span>On Bed: <strong>{{ $bed->currentAssignment->assigned_at ? $bed->currentAssignment->assigned_at->diffForHumans() : 'Recently' }}</strong></span>
                    </div>
                </div>
            @elseif($bed->status === 'cleaning')
                <div class="p-2.5 rounded bg-teal bg-opacity-10 border border-teal-subtle text-teal mb-3" style="font-size: 0.8rem;">
                    <div class="fw-semibold mb-1">
                        <i class="bi bi-stars me-1"></i> Sanitization in Progress
                    </div>
                    <p class="mb-0 small text-muted-hms">
                        Mattress & frame disinfection underway. Bed cannot be assigned until cleaning is certified complete.
                    </p>
                </div>
            @elseif($bed->status === 'maintenance')
                <div class="p-2.5 rounded bg-secondary bg-opacity-10 border border-secondary-subtle text-secondary mb-3" style="font-size: 0.8rem;">
                    <div class="fw-semibold mb-1">
                        <i class="bi bi-tools me-1"></i> Engineering Maintenance
                    </div>
                    <p class="mb-0 small text-muted-hms">
                        Under repair / inspection. Out of clinical service.
                    </p>
                </div>
            @elseif($bed->status === 'reserved')
                <div class="p-2.5 rounded bg-warning bg-opacity-10 border border-warning-subtle text-warning-emphasis mb-3" style="font-size: 0.8rem;">
                    <div class="fw-semibold mb-1">
                        <i class="bi bi-clock-history me-1"></i> Reserved
                    </div>
                    <p class="mb-0 small text-muted-hms">
                        Reserved for scheduled OT surgical admission or planned patient transfer.
                    </p>
                </div>
            @else
                <div class="p-2.5 rounded bg-success bg-opacity-10 border border-success-subtle text-success mb-3" style="font-size: 0.8rem;">
                    <div class="fw-semibold mb-1">
                        <i class="bi bi-check-circle-fill me-1"></i> Clean & Ready for Admission
                    </div>
                    <p class="mb-0 small text-muted-hms">
                        Sanitized, fresh bed linen prepared. Immediate inpatient allocation available.
                    </p>
                </div>
            @endif
        </div>

        <!-- Footer Action Buttons -->
        <div class="pt-2 border-top d-flex align-items-center justify-content-between gap-1">
            <a href="{{ route('portal.beds.show', $bed) }}" class="btn btn-sm btn-outline-navy py-1 px-2" style="font-size: 0.75rem;">
                <i class="bi bi-eye"></i> Dossier
            </a>

            @if($bed->status === 'occupied' && $bed->currentAssignment)
                @can('assign', $bed)
                    <button type="button" class="btn btn-sm btn-teal py-1 px-2 d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;"
                            onclick="openTransferOccupantModal({{ $bed->id }}, '{{ $bed->bed_number }}', '{{ addslashes($bed->currentAssignment->admission?->patient?->name ?? 'Occupant') }}', '{{ $bed->currentAssignment->admission?->patient?->patient_code ?? '' }}')">
                        <i class="bi bi-arrow-left-right"></i> Transfer Bed
                    </button>
                @endcan
            @elseif($bed->status === 'cleaning')
                @can('updateStatus', $bed)
                    <button type="button" class="btn btn-sm btn-success py-1 px-2 d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;"
                            onclick="openCompleteCleaningModal({{ $bed->id }}, '{{ $bed->bed_number }}')">
                        <i class="bi bi-stars"></i> Certify Clean
                    </button>
                @endcan
            @elseif($bed->status === 'maintenance')
                @can('updateStatus', $bed)
                    <button type="button" class="btn btn-sm btn-secondary py-1 px-2 d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;"
                            onclick="openCompleteMaintenanceModal({{ $bed->id }}, '{{ $bed->bed_number }}')">
                        <i class="bi bi-tools"></i> Repair Done
                    </button>
                @endcan
            @elseif($bed->status === 'available')
                @can('create', App\Models\Admission::class)
                    <a href="{{ route('portal.admissions.create', ['bed_id' => $bed->id, 'department_id' => $bed->department_id]) }}" class="btn btn-sm btn-outline-success py-1 px-2" style="font-size: 0.75rem;">
                        <i class="bi bi-box-arrow-in-right"></i> Admit Patient
                    </a>
                @endcan
            @endif
        </div>
    </div>
</div>
