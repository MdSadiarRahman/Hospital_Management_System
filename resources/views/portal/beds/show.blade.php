<x-layouts.portal role="admin" title="Bed Dossier — {{ $bed->bed_number }}">
    <div class="container-fluid px-0">
        <!-- Breadcrumb & Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('portal.beds.index') }}" class="text-decoration-none text-muted-hms">Bed Management</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">{{ $bed->bed_number }}</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">Bed {{ $bed->bed_number }}</h1>
                    <span class="badge bg-{{ $bed->status_badge_class }} text-uppercase px-2.5 py-1.5" style="font-size: 0.8rem;">
                        {{ ucfirst($bed->status) }}
                    </span>
                    <span class="badge bg-light text-navy border font-monospace">Room: {{ $bed->room_number ?? 'General Ward' }}</span>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                @can('updateStatus', $bed)
                    <button type="button" class="btn btn-outline-teal d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#statusModal">
                        <i class="bi bi-arrow-repeat"></i> Update Status
                    </button>
                @endcan

                @can('update', $bed)
                    <a href="{{ route('portal.beds.edit', $bed) }}" class="btn btn-navy d-inline-flex align-items-center gap-1.5 shadow-xs">
                        <i class="bi bi-pencil"></i> Edit Parameters
                    </a>
                @endcan

                <a href="{{ route('portal.beds.index') }}" class="btn btn-light d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> All Beds
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
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- Left Column: Bed Specifications & Current Occupant -->
            <div class="col-lg-4">
                <!-- Specifications Card -->
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                    <h2 class="h6 fw-bold text-navy border-bottom pb-2 mb-3">
                        <i class="bi bi-hospital me-1 text-teal"></i> Bed Specifications
                    </h2>

                    <div class="d-flex flex-column gap-2.5" style="font-size: 0.85rem;">
                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">Bed Number:</span>
                            <span class="fw-bold font-monospace text-navy">{{ $bed->bed_number }}</span>
                        </div>

                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">Room Number:</span>
                            <span class="fw-semibold text-navy">{{ $bed->room_number ?? 'General Ward' }}</span>
                        </div>

                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">Department:</span>
                            <span class="fw-semibold text-navy">
                                @if($bed->department)
                                    {{ $bed->department->name }} ({{ $bed->department->code }})
                                @else
                                    <span class="badge bg-light text-dark border">General Ward (Common)</span>
                                @endif
                            </span>
                        </div>

                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">Category:</span>
                            <span class="text-navy">{{ $bed->category_label }}</span>
                        </div>

                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">Daily Charge:</span>
                            <span class="fw-bold font-monospace text-teal">৳ {{ number_format($bed->daily_charge, 2) }}</span>
                        </div>

                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted-hms">Current Status:</span>
                            <span class="badge bg-{{ $bed->status_badge_class }}">{{ ucfirst($bed->status) }}</span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted-hms">Last Updated:</span>
                            <span class="text-muted">{{ $bed->updated_at->format('d M Y, h:i A') }}</span>
                        </div>
                    </div>

                    @if($bed->description)
                        <div class="mt-3 pt-3 border-top">
                            <span class="text-muted-hms small fw-semibold d-block mb-1">Equipment & Notes:</span>
                            <p class="small text-navy mb-0 bg-light p-2.5 rounded border">{{ $bed->description }}</p>
                        </div>
                    @endif
                </div>

                <!-- Current Inpatient Occupant Card -->
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
                    <h2 class="h6 fw-bold text-navy border-bottom pb-2 mb-3">
                        <i class="bi bi-person-fill-lock me-1 text-danger"></i> Current Inpatient Occupant
                    </h2>

                    @if($bed->status === 'occupied' && $bed->currentAssignment)
                        @php $admission = $bed->currentAssignment->admission; @endphp
                        <div class="p-3 rounded bg-danger bg-opacity-10 border border-danger-subtle">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h3 class="h6 fw-bold text-danger mb-0">{{ $admission->patient?->name }}</h3>
                                <span class="badge bg-danger text-white font-monospace">{{ $admission->patient?->patient_code }}</span>
                            </div>

                            <div class="small d-flex flex-column gap-1.5 text-navy">
                                <div><strong>Admission No:</strong> <span class="font-monospace">{{ $admission->admission_number }}</span></div>
                                <div><strong>Admitted On:</strong> {{ $bed->currentAssignment->assigned_at?->format('d M Y, h:i A') }}</div>
                                <div><strong>Attending Doctor:</strong> {{ $admission->attendingDoctor?->name ?? 'Medical Officer' }}</div>
                                <div><strong>Diagnosis / Reason:</strong> {{ $admission->admission_reason }}</div>
                            </div>

                            <div class="mt-3 pt-2 border-top border-danger border-opacity-25 d-flex gap-2">
                                <a href="{{ route('portal.patients.show', $admission->patient_id) }}" class="btn btn-navy btn-sm py-1 px-2.5" style="font-size: 0.75rem;">
                                    <i class="bi bi-person-vcard"></i> View Patient Chart
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="p-4 text-center text-muted bg-light rounded border">
                            <i class="bi bi-check-circle fs-3 text-success d-block mb-1"></i>
                            <span class="small">No active patient assigned. Bed is currently {{ $bed->status }}.</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Status History Timeline & Assignment Logs -->
            <div class="col-lg-8">
                <!-- Bed Status History Timeline -->
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                        <div>
                            <h2 class="h5 fw-bold text-navy mb-0">Bed Status Audit History</h2>
                            <p class="text-muted small mb-0">Complete historical timeline of status transitions and reasons</p>
                        </div>
                        <span class="badge bg-light text-navy border">Tamper-Evident History</span>
                    </div>

                    @if($bed->statusHistories->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr class="text-uppercase text-muted-hms" style="font-size: 0.725rem;">
                                        <th>Timestamp</th>
                                        <th>Transition</th>
                                        <th>Changed By</th>
                                        <th>Reason / Clinical Indication</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bed->statusHistories as $history)
                                        <tr>
                                            <td class="text-muted" style="white-space: nowrap;">
                                                {{ $history->created_at->format('d M Y, h:i A') }}
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    @if($history->from_status)
                                                        <span class="badge bg-light text-dark border" style="font-size: 0.7rem;">
                                                            {{ ucfirst($history->from_status) }}
                                                        </span>
                                                        <i class="bi bi-arrow-right text-muted small"></i>
                                                    @endif
                                                    <span class="badge bg-{{ $history->to_status === 'available' ? 'success' : ($history->to_status === 'occupied' ? 'danger' : ($history->to_status === 'cleaning' ? 'info text-white' : ($history->to_status === 'reserved' ? 'warning text-dark' : 'secondary'))) }}" style="font-size: 0.7rem;">
                                                        {{ ucfirst($history->to_status) }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="fw-semibold text-navy">
                                                {{ $history->changedBy?->name ?? 'System Officer' }}
                                            </td>
                                            <td>
                                                <span class="fw-medium text-navy">{{ $history->reason ?? '—' }}</span>
                                            </td>
                                            <td class="text-muted small">
                                                {{ $history->notes ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-center text-muted bg-light rounded border small">
                            No status transition history recorded yet.
                        </div>
                    @endif
                </div>

                <!-- Past Patient Bed Assignments History -->
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
                    <div class="pb-3 mb-3 border-bottom">
                        <h2 class="h5 fw-bold text-navy mb-0">Inpatient Bed Assignment History</h2>
                        <p class="text-muted small mb-0">Historical patient admissions accommodated in this bed</p>
                    </div>

                    @if($bed->assignments->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr class="text-uppercase text-muted-hms" style="font-size: 0.725rem;">
                                        <th>Admission No</th>
                                        <th>Patient Name</th>
                                        <th>Assigned At</th>
                                        <th>Released At</th>
                                        <th>Assigned By</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bed->assignments()->latest('assigned_at')->get() as $asgn)
                                        <tr>
                                            <td class="font-monospace fw-bold text-navy">
                                                {{ $asgn->admission?->admission_number }}
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-navy">{{ $asgn->admission?->patient?->name }}</div>
                                                <div class="font-monospace text-muted small">{{ $asgn->admission?->patient?->patient_code }}</div>
                                            </td>
                                            <td>{{ $asgn->assigned_at ? $asgn->assigned_at->format('d M Y, h:i A') : '—' }}</td>
                                            <td>
                                                @if($asgn->released_at)
                                                    <span class="text-muted">{{ $asgn->released_at->format('d M Y, h:i A') }}</span>
                                                @else
                                                    <span class="badge bg-danger">Currently Active</span>
                                                @endif
                                            </td>
                                            <td>{{ $asgn->assignedBy?->name ?? 'Staff' }}</td>
                                            <td class="text-muted small">{{ $asgn->transfer_reason ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-center text-muted bg-light rounded border small">
                            No patient stay history recorded for this bed.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Status Change Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('portal.beds.update-status', $bed) }}">
                    @csrf
                    <div class="modal-header bg-navy text-white">
                        <h5 class="modal-title h6 fw-bold mb-0" id="statusModalLabel">
                            <i class="bi bi-arrow-repeat me-1 text-teal"></i> Update Bed Status
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-navy fw-semibold small" for="status_select">
                                New Status <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status_select" class="form-select form-select-sm" required>
                                <option value="available" {{ $bed->status === 'available' ? 'selected' : '' }}>Available (Clean & Ready)</option>
                                <option value="cleaning" {{ $bed->status === 'cleaning' ? 'selected' : '' }}>Cleaning / Sanitization</option>
                                <option value="maintenance" {{ $bed->status === 'maintenance' ? 'selected' : '' }}>Maintenance / Repair</option>
                                <option value="reserved" {{ $bed->status === 'reserved' ? 'selected' : '' }}>Reserved</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-navy fw-semibold small" for="status_reason">
                                Transition Reason
                            </label>
                            <input type="text" name="reason" id="status_reason" class="form-control form-control-sm" placeholder="e.g. Bed sanitized, linen changed, ready for next admission">
                        </div>

                        <div>
                            <label class="form-label text-navy fw-semibold small" for="status_notes">
                                Detailed Nursing/Maintenance Notes
                            </label>
                            <textarea name="notes" id="status_notes" class="form-control form-control-sm" rows="2" placeholder="Optional notes for shift handover..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-4">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-teal btn-sm">
                            <i class="bi bi-check2 me-1"></i> Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.portal>

