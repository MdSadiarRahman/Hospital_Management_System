<x-layouts.portal title="Operation Theatre (OT) Management — Meghna Clinic">
    <div class="container-fluid py-4">

        {{-- Flash Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <strong>Action blocked due to conflicts or validation:</strong>
                </div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none">Portal</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Operation Theatre (OT)</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold text-navy mb-0">Operation Theatre (OT) Management</h1>
                <p class="text-muted small mb-0">Schedule surgical procedures, coordinate OT suites, avoid conflicts, and track post-op recovery.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portal.operations.calendar') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-calendar3 me-1"></i> OT Timetable
                </a>
                @if(auth()->user()->hasAnyRole(['doctor', 'administrator', 'super_admin']))
                    <a href="{{ route('portal.operations.create') }}" class="btn btn-teal text-white">
                        <i class="bi bi-plus-circle me-1"></i> Schedule Operation
                    </a>
                @endif
            </div>
        </div>

        {{-- Bento Grid KPI Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-2">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">Today's OT</span>
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-navy tabular-nums">{{ $statistics['total_today'] }}</h3>
                    <span class="text-muted small" style="font-size: 0.75rem;">Scheduled today</span>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">Scheduled</span>
                        <div class="rounded-circle bg-info bg-opacity-10 text-info p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-info tabular-nums">{{ $statistics['scheduled'] }}</h3>
                    <span class="text-muted small" style="font-size: 0.75rem;">Awaiting procedure</span>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">In Progress</span>
                        <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-heart-pulse"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-warning tabular-nums">{{ $statistics['in_progress'] }}</h3>
                    <span class="text-muted small" style="font-size: 0.75rem;">Currently in OT</span>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">Completed</span>
                        <div class="rounded-circle bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-success tabular-nums">{{ $statistics['completed_today'] }}</h3>
                    <span class="text-muted small" style="font-size: 0.75rem;">Completed today</span>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">Emergency</span>
                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-danger tabular-nums">{{ $statistics['emergency'] }}</h3>
                    <span class="text-muted small" style="font-size: 0.75rem;">High-urgency OT</span>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">Cancelled</span>
                        <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-secondary tabular-nums">{{ $statistics['cancelled'] }}</h3>
                    <span class="text-muted small" style="font-size: 0.75rem;">Total cancelled</span>
                </div>
            </div>
        </div>

        {{-- Filter & Search Toolbar --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('portal.operations.index') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted mb-1">Search Procedure / Patient</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Operation #, Name, Code..." value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="scheduled" {{ ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="rescheduled" {{ ($filters['status'] ?? '') === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                            <option value="in_progress" {{ ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">OT Room</label>
                        <select name="ot_room" class="form-select">
                            <option value="">All Theatres</option>
                            @foreach($otRooms as $room)
                                <option value="{{ $room }}" {{ ($filters['ot_room'] ?? '') === $room ? 'selected' : '' }}>{{ $room }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ ($filters['department_id'] ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Primary Surgeon</label>
                        <select name="primary_surgeon_id" class="form-select">
                            <option value="">All Surgeons</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ ($filters['primary_surgeon_id'] ?? '') == $doc->id ? 'selected' : '' }}>Dr. {{ $doc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-1 d-flex gap-1">
                        <button type="submit" class="btn btn-primary w-100" title="Apply Filters">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <a href="{{ route('portal.operations.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Operations Table --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title fw-bold text-navy mb-0">
                    <i class="bi bi-scissors text-teal me-2"></i>Surgical Operations Roster
                </h5>
                <span class="badge bg-light text-muted border">{{ $operations->total() }} Total Procedures</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th scope="col" style="min-width: 140px;">Operation #</th>
                            <th scope="col" style="min-width: 180px;">Patient Info</th>
                            <th scope="col" style="min-width: 200px;">Procedure & Dept</th>
                            <th scope="col" style="min-width: 180px;">OT Schedule & Room</th>
                            <th scope="col" style="min-width: 160px;">Surgeon</th>
                            <th scope="col" class="text-center" style="width: 100px;">Urgency</th>
                            <th scope="col" class="text-center" style="width: 110px;">Status</th>
                            <th scope="col" class="text-end" style="min-width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($operations as $op)
                            <tr>
                                <td>
                                    <span class="fw-bold text-navy font-monospace">{{ $op->operation_number }}</span>
                                    <div class="text-muted small" style="font-size: 0.75rem;">
                                        Adm: {{ $op->admission?->admission_number ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $op->patient?->name }}</div>
                                    <div class="small text-muted font-monospace">{{ $op->patient?->patient_code }}</div>
                                    <div class="small text-muted" style="font-size: 0.75rem;">
                                        {{ $op->patient?->formatted_age }}, {{ ucfirst($op->patient?->gender ?? '') }}
                                        @if($op->admission?->currentBedAssignment?->bed)
                                            <span class="badge bg-light text-dark border ms-1">
                                                <i class="bi bi-hospital me-1"></i>{{ $op->admission->currentBedAssignment->bed->bed_number }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $op->procedure_name }}</div>
                                    <span class="badge bg-light text-teal border">{{ $op->department?->name ?? 'General Surgery' }}</span>
                                    @if($op->indication)
                                        <div class="text-muted small text-truncate" style="max-width: 220px;" title="{{ $op->indication }}">
                                            Ind: {{ $op->indication }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <i class="bi bi-clock text-muted me-1"></i>{{ $op->scheduled_at?->format('d M Y, h:i A') }}
                                    </div>
                                    <div class="small text-muted">{{ $op->estimated_duration_minutes }} mins duration</div>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border mt-1">
                                        <i class="bi bi-door-closed me-1"></i>{{ $op->ot_room }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">Dr. {{ $op->primarySurgeon?->name }}</div>
                                    @if($op->assistantSurgeon)
                                        <div class="text-muted small" style="font-size: 0.75rem;">Asst: Dr. {{ $op->assistantSurgeon->name }}</div>
                                    @endif
                                    @if($op->anesthetist_name)
                                        <div class="text-muted small" style="font-size: 0.75rem;">Anesth: {{ $op->anesthetist_name }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {!! $op->urgency_badge !!}
                                </td>
                                <td class="text-center">
                                    {!! $op->status_badge !!}
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('portal.operations.show', $op) }}" class="btn btn-outline-primary" title="View Dossier">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="{{ route('portal.operations.print', $op) }}" target="_blank" class="btn btn-outline-secondary" title="Print Surgical Slip">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="py-3">
                                        <i class="bi bi-calendar-x text-muted" style="font-size: 2.5rem;"></i>
                                        <h6 class="fw-bold mt-2 text-dark">No Operation Procedures Found</h6>
                                        <p class="text-muted small mb-3">No surgical operations match your search criteria or schedule filters.</p>
                                        @if(auth()->user()->hasAnyRole(['doctor', 'administrator', 'super_admin']))
                                            <a href="{{ route('portal.operations.create') }}" class="btn btn-teal btn-sm text-white">
                                                <i class="bi bi-plus-circle me-1"></i> Schedule First Operation
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($operations->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $operations->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.portal>

