@extends('layouts.portal', [
    'title' => 'Doctor Appointments Management',
    'activeNav' => 'appointments'
])

@section('content')
<div class="container-fluid py-4 px-md-4">
    <!-- Page Header & Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted" style="font-size: 0.85rem;">
                    <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted">Portal</a></li>
                    <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Appointments</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-navy mb-0">Doctor Appointments Management</h1>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                Schedule, confirm, reschedule, and manage OPD patient consultations across clinical departments.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('portal.doctor-schedules.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-calendar-week"></i>
                <span>Doctor Schedules</span>
            </a>
            @can('create', App\Models\Appointment::class)
            <a href="{{ route('portal.appointments.create') }}" class="btn btn-hms-teal d-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-calendar-plus"></i>
                <span class="fw-semibold">Book Appointment</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Status Alerts -->
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
            <div>{{ session('status') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Status Metric Cards (Bento Style) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('portal.appointments.index') }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm rounded-3 p-3 transition-hover {{ !request('status') ? 'border-start border-4 border-primary bg-primary-subtle' : 'bg-white' }}">
                    <span class="text-muted fw-semibold small text-uppercase">Total Bookings</span>
                    <h3 class="fw-bold text-navy mt-1 mb-0">{{ $counts['total'] }}</h3>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('portal.appointments.index', array_merge(request()->except('page'), ['status' => 'pending'])) }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm rounded-3 p-3 transition-hover {{ request('status') === 'pending' ? 'border-start border-4 border-warning bg-warning-subtle' : 'bg-white' }}">
                    <span class="text-warning-emphasis fw-semibold small text-uppercase">Pending Approval</span>
                    <h3 class="fw-bold text-warning-emphasis mt-1 mb-0">{{ $counts['pending'] }}</h3>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('portal.appointments.index', array_merge(request()->except('page'), ['status' => 'confirmed'])) }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm rounded-3 p-3 transition-hover {{ request('status') === 'confirmed' ? 'border-start border-4 border-success bg-success-subtle' : 'bg-white' }}">
                    <span class="text-success fw-semibold small text-uppercase">Confirmed</span>
                    <h3 class="fw-bold text-success mt-1 mb-0">{{ $counts['confirmed'] }}</h3>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('portal.appointments.index', array_merge(request()->except('page'), ['status' => 'rescheduled'])) }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm rounded-3 p-3 transition-hover {{ request('status') === 'rescheduled' ? 'border-start border-4 border-info bg-info-subtle' : 'bg-white' }}">
                    <span class="text-info fw-semibold small text-uppercase">Rescheduled</span>
                    <h3 class="fw-bold text-info mt-1 mb-0">{{ $counts['rescheduled'] }}</h3>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('portal.appointments.index', array_merge(request()->except('page'), ['status' => 'completed'])) }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm rounded-3 p-3 transition-hover {{ request('status') === 'completed' ? 'border-start border-4 border-primary bg-primary-subtle' : 'bg-white' }}">
                    <span class="text-primary fw-semibold small text-uppercase">Completed</span>
                    <h3 class="fw-bold text-primary mt-1 mb-0">{{ $counts['completed'] }}</h3>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('portal.appointments.index', array_merge(request()->except('page'), ['status' => 'cancelled'])) }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm rounded-3 p-3 transition-hover {{ request('status') === 'cancelled' ? 'border-start border-4 border-danger bg-danger-subtle' : 'bg-white' }}">
                    <span class="text-danger fw-semibold small text-uppercase">Cancelled / No Show</span>
                    <h3 class="fw-bold text-danger mt-1 mb-0">{{ $counts['cancelled'] + $counts['no_show'] }}</h3>
                </div>
            </a>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('portal.appointments.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Search patient, phone, PID..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        @foreach(App\Models\Appointment::STATUSES as $val => $label)
                            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ (string) request('department_id') === (string) $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="doctor_id" class="form-select form-select-sm">
                        <option value="">All Doctors</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}" {{ (string) request('doctor_id') === (string) $doc->id ? 'selected' : '' }}>{{ $doc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}" placeholder="Appointment Date">
                </div>
                <div class="col-12 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-hms-primary w-100" title="Filter"><i class="bi bi-funnel"></i></button>
                    @if(request()->hasAny(['search', 'status', 'department_id', 'doctor_id', 'date']))
                        <a href="{{ route('portal.appointments.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                <thead class="table-light text-navy text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-3 py-3">Serial & Ref</th>
                        <th class="py-3">Patient</th>
                        <th class="py-3">Consulting Doctor & Dept</th>
                        <th class="py-3">Date & Time Slot</th>
                        <th class="py-3">Type</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="pe-3 py-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($appointments as $appointment)
                    <tr>
                        <td class="ps-3 py-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-navy border fw-bold fs-6 px-2.5 py-1">
                                    #{{ $appointment->serial_number ?: '—' }}
                                </span>
                                <small class="text-muted">Ref: {{ $appointment->id }}</small>
                            </div>
                        </td>
                        <td class="py-3">
                            @if($appointment->patient)
                                <a href="{{ route('portal.patients.show', $appointment->patient) }}" class="fw-bold text-navy text-decoration-none">
                                    {{ $appointment->patient->name }}
                                </a>
                                <div class="text-muted small">
                                    <span class="badge bg-light text-muted border me-1">{{ $appointment->patient->patient_code }}</span>
                                    <span>{{ $appointment->patient->phone }}</span>
                                </div>
                            @else
                                <span class="fw-bold text-navy">{{ $appointment->guest_name }}</span>
                                <div class="text-muted small">
                                    <span class="badge bg-secondary-subtle text-secondary me-1">Guest / Online</span>
                                    <span>{{ $appointment->guest_phone }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="py-3">
                            <div class="fw-semibold text-navy">
                                {{ $appointment->doctor?->name ?? 'Any Available Medical Officer' }}
                            </div>
                            <span class="badge bg-teal-subtle text-teal" style="font-size: 0.75rem;">
                                {{ $appointment->department?->name }}
                            </span>
                        </td>
                        <td class="py-3">
                            <div class="fw-semibold text-navy">
                                <i class="bi bi-calendar3 text-muted me-1"></i>
                                {{ $appointment->appointment_date->format('d M Y') }}
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-clock text-muted me-1"></i>
                                {{ $appointment->appointment_time ? date('h:i A', strtotime($appointment->appointment_time)) : $appointment->appointment_date->format('h:i A') }}
                                <span class="badge bg-light text-muted ms-1">{{ $appointment->slot_duration_minutes }}m</span>
                            </div>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-light text-secondary border text-capitalize">
                                {{ str_replace('_', ' ', $appointment->type) }}
                            </span>
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge rounded-pill px-2.5 py-1.5 {{ $appointment->status_badge_class }}">
                                {{ $appointment->status_label }}
                            </span>
                        </td>
                        <td class="pe-3 py-3 text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('portal.appointments.show', $appointment) }}" class="btn btn-outline-secondary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @can('print', $appointment)
                                <a href="{{ route('portal.appointments.print-slip', $appointment) }}" target="_blank" class="btn btn-outline-secondary" title="Print Slip">
                                    <i class="bi bi-printer"></i>
                                </a>
                                @endcan

                                @can('approve', $appointment)
                                    @if($appointment->canBeConfirmed())
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal{{ $appointment->id }}" title="Confirm Appointment">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                    @endif
                                @endcan

                                @can('update', $appointment)
                                    @if($appointment->canBeRescheduled())
                                    <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#rescheduleModal{{ $appointment->id }}" title="Reschedule">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                    @endif
                                @endcan

                                @can('delete', $appointment)
                                    @if($appointment->canBeCancelled())
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal{{ $appointment->id }}" title="Cancel">
                                        <i class="bi bi-x"></i>
                                    </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>

                    <!-- Modals for this Appointment -->
                    @include('portal.appointments.partials.modals', ['appointment' => $appointment])

                    @empty
                    <tr>
                        <td colspan="7" class="py-5 text-center text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2 text-muted"></i>
                            <h5 class="fw-semibold text-navy">No Appointments Found</h5>
                            <p class="small text-muted mb-3">No appointment records match your selected criteria or search term.</p>
                            @can('create', App\Models\Appointment::class)
                            <a href="{{ route('portal.appointments.create') }}" class="btn btn-sm btn-hms-teal">
                                <i class="bi bi-plus-circle me-1"></i> Book New Appointment
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($appointments->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $appointments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
