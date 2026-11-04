<x-layouts.portal role="admin" title="Admission {{ $admission->admission_number }} Dossier">
    <div class="container-fluid px-0">
        <!-- Page Header & Action Bar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('portal.admissions.index') }}" class="text-decoration-none text-muted-hms">Admissions</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">{{ $admission->admission_number }}</li>
                    </ol>
                </nav>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">Admission Dossier: {{ $admission->admission_number }}</h1>
                    <span class="badge bg-{{ $admission->status_badge_class }} text-white font-monospace">
                        {{ $admission->status_label }}
                    </span>
                    @if($admission->currentBed)
                        <span class="badge bg-teal text-white font-monospace">
                            <i class="bi bi-door-open me-1"></i>Bed {{ $admission->currentBed->bed_number }} ({{ $admission->currentBed->room_number ?? 'Ward Room' }})
                        </span>
                    @endif
                </div>
                <p class="text-muted-hms small mb-0 mt-1">
                    Admitted on {{ $admission->admission_date ? $admission->admission_date->format('d M Y, h:i A') : 'N/A' }} · Stay Duration: <strong class="text-teal">{{ $admission->stay_duration }}</strong>
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                @if($admission->status === 'admitted')
                    @can('transferBed', $admission)
                        <button type="button" class="btn btn-teal d-inline-flex align-items-center gap-2 shadow-xs" data-bs-toggle="modal" data-bs-target="#transferBedModal">
                            <i class="bi bi-arrow-left-right"></i>
                            <span>Transfer Bed</span>
                        </button>
                    @endcan

                    @can('cancel', $admission)
                        <button type="button" class="btn btn-outline-danger d-inline-flex align-items-center gap-2 shadow-xs" data-bs-toggle="modal" data-bs-target="#cancelAdmissionModal">
                            <i class="bi bi-x-circle"></i>
                            <span>Cancel Admission</span>
                        </button>
                    @endcan
                @endif

                <a href="{{ route('portal.admissions.index') }}" class="btn btn-outline-navy d-inline-flex align-items-center gap-2 shadow-xs">
                    <i class="bi bi-arrow-left"></i>
                    <span>All Admissions</span>
                </a>
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
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Action could not be processed:</div>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Cancellation Notice (if cancelled) -->
        @if($admission->status === 'cancelled')
            <div class="alert alert-danger border-0 shadow-sm mb-4 p-3 d-flex align-items-start gap-3">
                <i class="bi bi-exclamation-octagon-fill fs-3 text-danger mt-1"></i>
                <div>
                    <h5 class="fw-bold text-danger mb-1">Admission Was Cancelled</h5>
                    <p class="mb-1 text-navy" style="font-size: 0.9rem;">
                        <strong>Reason:</strong> {{ $admission->cancellation_reason }}
                    </p>
                    <div class="small text-muted-hms">
                        Cancelled by: {{ $admission->cancelledBy?->name ?? 'System' }} on {{ $admission->cancelled_at ? $admission->cancelled_at->format('d M Y, h:i A') : 'N/A' }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Bento Grid: Details & Overview -->
        <div class="row g-4 mb-4">
            <!-- Patient Information Card -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-xs h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-teal bg-opacity-10 text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                <i class="bi bi-person-vcard"></i>
                            </span>
                            <h6 class="fw-bold text-navy mb-0">Patient Information</h6>
                        </div>
                        <a href="{{ route('portal.patients.show', $admission->patient_id) }}" class="btn btn-sm btn-link text-teal text-decoration-none p-0">
                            Full Profile <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <div class="card-body p-3 pt-0">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-navy text-white d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 48px; height: 48px;">
                                {{ substr($admission->patient->name, 0, 1) }}
                            </div>
                            <div>
                                <h5 class="fw-bold text-navy mb-0">{{ $admission->patient->name }}</h5>
                                <span class="font-monospace text-teal fw-semibold">{{ $admission->patient->patient_code }}</span>
                            </div>
                        </div>

                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item px-0 d-flex justify-content-between py-2 border-light">
                                <span class="text-muted-hms">Gender / Blood:</span>
                                <span class="fw-semibold text-navy">{{ ucfirst($admission->patient->gender) }} · {{ $admission->patient->blood_group ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between py-2 border-light">
                                <span class="text-muted-hms">Primary Phone:</span>
                                <span class="fw-semibold text-navy">{{ $admission->patient->phone ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between py-2 border-light">
                                <span class="text-muted-hms">Address:</span>
                                <span class="fw-semibold text-navy text-end">{{ $admission->patient->address ?? 'Dinajpur' }}</span>
                            </li>
                            @if($admission->patient->emergencyContacts->isNotEmpty())
                                <li class="list-group-item px-0 py-2 border-light">
                                    <span class="text-muted-hms d-block mb-1">Emergency Contact:</span>
                                    @php $ec = $admission->patient->emergencyContacts->first(); @endphp
                                    <span class="fw-semibold text-navy">{{ $ec->name }} ({{ $ec->relationship }}) · {{ $ec->phone }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Current Bed Location Card -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-xs h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-primary bg-opacity-10 text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                <i class="bi bi-door-open"></i>
                            </span>
                            <h6 class="fw-bold text-navy mb-0">Allocated Inpatient Bed</h6>
                        </div>
                        <a href="{{ route('portal.beds.index') }}" class="btn btn-sm btn-link text-navy text-decoration-none p-0">
                            Bed Map <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </div>
                    <div class="card-body p-3 pt-0">
                        @if($admission->status === 'admitted' && $admission->currentBed)
                            <div class="p-3 rounded bg-light border mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-teal text-white font-monospace fs-6 px-3 py-1">
                                        Bed {{ $admission->currentBed->bed_number }}
                                    </span>
                                    <span class="fw-bold text-teal">৳{{ number_format((float)$admission->currentBed->daily_charge, 2) }} / day</span>
                                </div>
                                <h6 class="fw-bold text-navy mb-1">{{ $admission->currentBed->room_number ?? 'Ward Room' }}</h6>
                                <div class="small text-muted-hms mb-2">
                                    Category: <strong>{{ $admission->currentBed->category_label }}</strong> · 
                                    Ward: <strong>{{ $admission->currentBed->department?->name ?? 'General Ward' }}</strong>
                                </div>
                                @if($admission->currentBed->description)
                                    <div class="small text-muted bg-white p-2 rounded border">
                                        <i class="bi bi-info-circle me-1"></i>{{ $admission->currentBed->description }}
                                    </div>
                                @endif
                            </div>

                            <div class="small text-muted-hms mb-3">
                                Bed assigned at: <strong>{{ $admission->currentBedAssignment?->assigned_at ? $admission->currentBedAssignment->assigned_at->format('d M Y, h:i A') : 'N/A' }}</strong>
                            </div>

                            @can('transferBed', $admission)
                                <button type="button" class="btn btn-outline-teal btn-sm w-100" data-bs-toggle="modal" data-bs-target="#transferBedModal">
                                    <i class="bi bi-arrow-left-right me-1"></i>Transfer to Another Bed
                                </button>
                            @endcan
                        @elseif($admission->status === 'discharged')
                            <div class="text-center py-4 text-muted-hms">
                                <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                                <div class="fw-bold text-navy">Patient Discharged</div>
                                <span class="small">Inpatient stay concluded and bed returned to ward.</span>
                            </div>
                        @else
                            <div class="text-center py-4 text-muted-hms">
                                <i class="bi bi-dash-circle fs-1 text-danger d-block mb-2"></i>
                                <div class="fw-bold text-navy">No Active Bed</div>
                                <span class="small">Admission is cancelled or bed has been released.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Clinical Responsibility & Reason Card -->
            <div class="col-12 col-md-12 col-xl-4">
                <div class="card border-0 shadow-xs h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-navy bg-opacity-10 text-navy p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                <i class="bi bi-clipboard2-pulse"></i>
                            </span>
                            <h6 class="fw-bold text-navy mb-0">Clinical Details</h6>
                        </div>
                    </div>
                    <div class="card-body p-3 pt-0">
                        <div class="mb-3">
                            <span class="text-muted-hms small d-block">Attending Doctor:</span>
                            <strong class="text-navy fs-6">
                                <i class="bi bi-person-badge text-teal me-1"></i>{{ $admission->attendingDoctor?->name ?? 'Unassigned' }}
                            </strong>
                        </div>

                        <div class="mb-3">
                            <span class="text-muted-hms small d-block">Assigned Department:</span>
                            <strong class="text-navy">
                                {{ $admission->department?->name ?? 'General Ward' }}
                            </strong>
                        </div>

                        <div class="mb-3">
                            <span class="text-muted-hms small d-block">Originating Visit:</span>
                            @if($admission->visit)
                                <a href="{{ route('portal.visits.show', $admission->visit_id) }}" class="fw-semibold text-teal text-decoration-none font-monospace">
                                    {{ $admission->visit->visit_number }}
                                </a>
                                <span class="small text-muted-hms">({{ ucfirst($admission->visit->visit_type) }})</span>
                            @else
                                <span class="text-muted-hms">Direct Intake</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <span class="text-muted-hms small d-block">Admission Reason & Indications:</span>
                            <div class="p-2 rounded bg-light border small text-navy mt-1">
                                {{ $admission->admission_reason }}
                            </div>
                        </div>

                        @if($admission->notes)
                            <div>
                                <span class="text-muted-hms small d-block">Nursing Intake Notes:</span>
                                <div class="p-2 rounded bg-light border small text-muted-hms mt-1">
                                    {{ $admission->notes }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Bed Allocation & Transfer History Timeline -->
        <div class="card border-0 shadow-xs mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold text-navy mb-0">Bed Allocation & Transfer History</h5>
                    <span class="text-muted-hms small">Complete chronological record of beds occupied during this hospital admission</span>
                </div>
                <span class="badge bg-light text-navy border font-monospace">
                    {{ $admission->bedAssignments->count() }} Total Bed Records
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light text-muted-hms text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-3">Bed & Room</th>
                            <th>Category / Ward</th>
                            <th>Daily Charge</th>
                            <th>Assigned At</th>
                            <th>Released At</th>
                            <th>Stay On Bed</th>
                            <th>Reason / Transfer Justification</th>
                            <th class="text-end pe-3">Assigned By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admission->bedAssignments as $assignment)
                            <tr class="{{ is_null($assignment->released_at) ? 'table-success bg-opacity-25' : '' }}">
                                <!-- Bed & Room -->
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge {{ is_null($assignment->released_at) ? 'bg-success' : 'bg-secondary' }} text-white font-monospace px-2 py-1">
                                            Bed {{ $assignment->bed?->bed_number ?? 'N/A' }}
                                        </span>
                                        <span class="fw-semibold text-navy">{{ $assignment->bed?->room_number ?? 'Room N/A' }}</span>
                                    </div>
                                </td>

                                <!-- Category & Ward -->
                                <td>
                                    <span class="d-block fw-semibold text-navy">{{ $assignment->bed?->category_label ?? 'Department' }}</span>
                                    <span class="d-block text-muted-hms small">{{ $assignment->bed?->department?->name ?? 'General Ward' }}</span>
                                </td>

                                <!-- Tariff -->
                                <td>
                                    <span class="fw-semibold text-teal">৳{{ number_format((float)($assignment->bed?->daily_charge ?? 0), 2) }}</span>
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
                                        <span class="badge bg-success text-white">Active Occupant</span>
                                    @endif
                                </td>

                                <!-- Stay Duration on this specific bed -->
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
                                    <span class="small text-muted-hms">
                                        {{ $assignment->transfer_reason ?? 'Initial admission' }}
                                    </span>
                                </td>

                                <!-- Assigned By -->
                                <td class="text-end pe-3">
                                    <span class="small text-muted-hms">
                                        {{ $assignment->assignedBy?->name ?? 'System' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted-hms">
                                    No bed assignment history recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal 1: Transfer Bed Modal -->
    @if($admission->status === 'admitted')
        <div class="modal fade" id="transferBedModal" tabindex="-1" aria-labelledby="transferBedModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form action="{{ route('portal.admissions.transfer-bed', $admission) }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 bg-teal text-white">
                            <h5 class="modal-title fw-bold" id="transferBedModalLabel">
                                <i class="bi bi-arrow-left-right me-2"></i>Transfer Patient to Another Bed
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3 p-3 rounded bg-light border">
                                <div class="small text-muted-hms">Current Occupied Bed:</div>
                                <div class="fw-bold text-navy fs-6">
                                    Bed {{ $admission->currentBed?->bed_number ?? 'N/A' }} ({{ $admission->currentBed?->room_number ?? 'Ward Room' }})
                                </div>
                                <div class="small text-teal">
                                    Ward: {{ $admission->currentBed?->department?->name ?? 'General Ward' }}
                                </div>
                            </div>

                            <!-- Target Bed Selection -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-navy">
                                    Target Bed (General or Department Ward) <span class="text-danger">*</span>
                                </label>
                                <select name="target_bed_id" class="form-select" required>
                                    <option value="">-- Choose an Available Bed --</option>
                                    @foreach($availableBeds as $bed)
                                        @if($admission->currentBed && $bed->id === $admission->currentBed->id)
                                            @continue
                                        @endif
                                        <option value="{{ $bed->id }}">
                                            Bed {{ $bed->bed_number }} ({{ $bed->room_number ?? 'Room N/A' }}) — {{ $bed->department?->name ?? 'General Ward' }} · ৳{{ number_format((float)$bed->daily_charge, 2) }}/day [{{ $bed->category_label }}]
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Transfer Justification -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-navy">
                                    Clinical / Administrative Transfer Reason <span class="text-danger">*</span>
                                </label>
                                <textarea name="reason" rows="3" class="form-control" placeholder="E.g., Transferred from Emergency to General Ward post-stabilization, patient requested private cabin..." required></textarea>
                            </div>

                            <div class="alert alert-info border-0 p-2 mb-0 small text-navy">
                                <i class="bi bi-info-circle me-1"></i>
                                Upon transfer, the previous bed will automatically be released and set to <strong>Cleaning</strong> status.
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-teal">Confirm Bed Transfer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal 2: Cancel Admission Modal -->
        <div class="modal fade" id="cancelAdmissionModal" tabindex="-1" aria-labelledby="cancelAdmissionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form action="{{ route('portal.admissions.cancel', $admission) }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 bg-danger text-white">
                            <h5 class="modal-title fw-bold" id="cancelAdmissionModalLabel">
                                <i class="bi bi-x-circle me-2"></i>Cancel Inpatient Admission
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="text-navy small">
                                Are you sure you want to cancel admission <strong>{{ $admission->admission_number }}</strong> for patient <strong>{{ $admission->patient->name }}</strong>?
                            </p>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-navy">
                                    Cancellation Reason <span class="text-danger">*</span>
                                </label>
                                <textarea name="cancellation_reason" rows="3" class="form-control" placeholder="E.g., Admitted in error, patient refused admission, transferred out before bed occupancy..." required></textarea>
                            </div>

                            <div class="alert alert-warning border-0 p-2 mb-0 small text-navy">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                                This will immediately cancel the admission and release Bed <strong>{{ $admission->currentBed?->bed_number ?? 'N/A' }}</strong> back to available inventory.
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</x-layouts.portal>

