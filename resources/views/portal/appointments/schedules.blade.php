@extends('layouts.portal', [
    'title' => 'Doctor Consultation Schedules',
    'activeNav' => 'doctor-schedules'
])

@section('content')
<div class="container-fluid py-4 px-md-4">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted" style="font-size: 0.85rem;">
                    <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted">Portal</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('portal.appointments.index') }}" class="text-decoration-none text-muted">Appointments</a></li>
                    <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Doctor Schedules</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-navy mb-0">Doctor Consultation Schedules</h1>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                Configure outpatient consultation days, shifts, slot durations, and daily room assignments.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('portal.appointments.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-calendar2-check"></i>
                <span>View Appointments</span>
            </a>
            <button type="button" class="btn btn-hms-teal d-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#createScheduleModal">
                <i class="bi bi-plus-circle"></i>
                <span class="fw-semibold">Add Consultation Schedule</span>
            </button>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
            <div>{{ session('status') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <div class="fw-bold d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-exclamation-triangle-fill text-danger"></i> Please check the required fields:
            </div>
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('portal.doctor-schedules.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <select name="doctor_id" class="form-select form-select-sm">
                        <option value="">All Consulting Doctors</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}" {{ (string) request('doctor_id') === (string) $doc->id ? 'selected' : '' }}>{{ $doc->name }} ({{ $doc->staffProfile?->department?->name ?? 'OPD' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ (string) request('department_id') === (string) $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="day_of_week" class="form-select form-select-sm">
                        <option value="">All Days of Week</option>
                        @foreach(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                            <option value="{{ $day }}" {{ request('day_of_week') === $day ? 'selected' : '' }}>{{ ucfirst($day) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-hms-primary w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                    @if(request()->hasAny(['doctor_id', 'department_id', 'day_of_week']))
                        <a href="{{ route('portal.doctor-schedules.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Schedules Table -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                <thead class="table-light text-navy text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-3 py-3">Consulting Doctor</th>
                        <th class="py-3">Clinical Department</th>
                        <th class="py-3">Day of Week</th>
                        <th class="py-3">Consultation Hours</th>
                        <th class="py-3">Slot Duration</th>
                        <th class="py-3">Room / Chamber</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="pe-3 py-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($schedules as $sched)
                    <tr>
                        <td class="ps-3 py-3">
                            <div class="fw-bold text-navy">{{ $sched->doctor?->name }}</div>
                            <div class="text-muted small">{{ $sched->doctor?->staffProfile?->designation ?? 'Consultant' }}</div>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-teal-subtle text-teal">
                                {{ $sched->department?->name }}
                            </span>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-light text-navy border fw-bold text-capitalize px-2.5 py-1">
                                {{ $sched->day_of_week }}
                            </span>
                        </td>
                        <td class="py-3">
                            <div class="fw-semibold text-navy">
                                <i class="bi bi-clock text-muted me-1"></i>
                                {{ date('h:i A', strtotime($sched->start_time)) }} - {{ date('h:i A', strtotime($sched->end_time)) }}
                            </div>
                        </td>
                        <td class="py-3">
                            <span class="fw-semibold text-teal">{{ $sched->slot_duration_minutes }} mins</span>
                            <span class="text-muted small">({{ $sched->max_patients ?: '20' }} max)</span>
                        </td>
                        <td class="py-3">
                            <span class="text-navy fw-semibold">{{ $sched->room_number ?? 'OPD Room' }}</span>
                        </td>
                        <td class="py-3 text-center">
                            @if($sched->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1">Inactive</span>
                            @endif
                        </td>
                        <td class="pe-3 py-3 text-end">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editScheduleModal{{ $sched->id }}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('portal.doctor-schedules.destroy', $sched) }}" method="POST" onsubmit="return confirm('Delete this consultation schedule?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Schedule Modal -->
                    <div class="modal fade" id="editScheduleModal{{ $sched->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <form action="{{ route('portal.doctor-schedules.update', $sched) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-navy text-white" style="background-color: #123B5D;">
                                        <h5 class="modal-title fs-6 fw-bold">Edit Consultation Schedule #{{ $sched->id }}</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4 text-start">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-semibold text-navy">Consulting Doctor <span class="text-danger">*</span></label>
                                                <select name="doctor_id" class="form-select form-select-sm" required>
                                                    @foreach($doctors as $doc)
                                                        <option value="{{ $doc->id }}" {{ $sched->doctor_id == $doc->id ? 'selected' : '' }}>{{ $doc->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-semibold text-navy">Department <span class="text-danger">*</span></label>
                                                <select name="department_id" class="form-select form-select-sm" required>
                                                    @foreach($departments as $dept)
                                                        <option value="{{ $dept->id }}" {{ $sched->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-semibold text-navy">Day of Week <span class="text-danger">*</span></label>
                                                <select name="day_of_week" class="form-select form-select-sm" required>
                                                    @foreach(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                                                        <option value="{{ $day }}" {{ $sched->day_of_week == $day ? 'selected' : '' }}>{{ ucfirst($day) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-semibold text-navy">Start Time <span class="text-danger">*</span></label>
                                                <input type="time" name="start_time" class="form-control form-control-sm" value="{{ date('H:i', strtotime($sched->start_time)) }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-semibold text-navy">End Time <span class="text-danger">*</span></label>
                                                <input type="time" name="end_time" class="form-control form-control-sm" value="{{ date('H:i', strtotime($sched->end_time)) }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-semibold text-navy">Slot Duration (Minutes) <span class="text-danger">*</span></label>
                                                <input type="number" name="slot_duration_minutes" class="form-control form-control-sm" min="5" max="120" value="{{ $sched->slot_duration_minutes }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-semibold text-navy">Max Patients per Day</label>
                                                <input type="number" name="max_patients" class="form-control form-control-sm" min="1" max="100" value="{{ $sched->max_patients }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-semibold text-navy">Room / Chamber Number</label>
                                                <input type="text" name="room_number" class="form-control form-control-sm" value="{{ $sched->room_number }}">
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check form-switch mt-1">
                                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_active_{{ $sched->id }}" {{ $sched->is_active ? 'checked' : '' }}>
                                                    <label class="form-check-label small fw-semibold text-navy" for="edit_active_{{ $sched->id }}">Active Schedule</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light py-2">
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-sm btn-hms-primary fw-semibold">Save Changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="py-5 text-center text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2 text-muted"></i>
                            <h5 class="fw-semibold text-navy">No Schedules Configured</h5>
                            <p class="small text-muted mb-3">Add consultation hours for clinic doctors to enable OPD booking.</p>
                            <button type="button" class="btn btn-sm btn-hms-teal" data-bs-toggle="modal" data-bs-target="#createScheduleModal">
                                <i class="bi bi-plus-circle me-1"></i> Add Schedule Now
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($schedules->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $schedules->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Create Schedule Modal -->
<div class="modal fade" id="createScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('portal.doctor-schedules.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-navy text-white" style="background-color: #123B5D;">
                    <h5 class="modal-title fs-6 fw-bold">
                        <i class="bi bi-calendar-plus me-1"></i> Add Doctor Consultation Schedule
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-navy">Consulting Doctor <span class="text-danger">*</span></label>
                            <select name="doctor_id" class="form-select form-select-sm" required>
                                <option value="">-- Choose Doctor --</option>
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}">{{ $doc->name }} ({{ $doc->staffProfile?->department?->name ?? 'OPD' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-navy">Department <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-select form-select-sm" required>
                                <option value="">-- Choose Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-navy">Day of Week <span class="text-danger">*</span></label>
                            <select name="day_of_week" class="form-select form-select-sm" required>
                                @foreach(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                                    <option value="{{ $day }}">{{ ucfirst($day) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-navy">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control form-control-sm" value="09:00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-navy">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control form-control-sm" value="13:00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-navy">Slot Duration (Minutes) <span class="text-danger">*</span></label>
                            <input type="number" name="slot_duration_minutes" class="form-control form-control-sm" min="5" max="120" value="20" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-navy">Max Patients per Day</label>
                            <input type="number" name="max_patients" class="form-control form-control-sm" min="1" max="100" value="15">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-navy">Room / Chamber Number</label>
                            <input type="text" name="room_number" class="form-control form-control-sm" placeholder="e.g. OPD Room 101" value="OPD Room 101">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="create_active" checked>
                                <label class="form-check-label small fw-semibold text-navy" for="create_active">Schedule Active Immediately</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-hms-teal fw-semibold">
                        <i class="bi bi-check2"></i> Save Schedule
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
