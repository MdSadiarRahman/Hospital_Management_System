@extends('layouts.portal', [
    'title' => 'Appointment #' . $appointment->id . ' Details',
    'activeNav' => 'appointments'
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
                    <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">#{{ $appointment->id }}</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-3">
                <h1 class="h3 fw-bold text-navy mb-0">
                    Appointment #{{ $appointment->id }}
                </h1>
                <span class="badge rounded-pill px-3 py-1.5 fs-6 {{ $appointment->status_badge_class }}">
                    {{ $appointment->status_label }}
                </span>
                @if($appointment->serial_number)
                <span class="badge bg-navy text-white px-3 py-1.5 fs-6">
                    Serial: #{{ $appointment->serial_number }}
                </span>
                @endif
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('portal.appointments.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-arrow-left"></i>
                <span>Back</span>
            </a>

            @can('print', $appointment)
            <a href="{{ route('portal.appointments.print-slip', $appointment) }}" target="_blank" class="btn btn-outline-primary d-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-printer"></i>
                <span>Print Slip</span>
            </a>
            @endcan

            @can('approve', $appointment)
                @if($appointment->canBeConfirmed())
                <button type="button" class="btn btn-success d-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#confirmModal{{ $appointment->id }}">
                    <i class="bi bi-check2-circle"></i>
                    <span>Approve & Confirm</span>
                </button>
                @endif
            @endcan

            @can('update', $appointment)
                @if($appointment->canBeRescheduled())
                <button type="button" class="btn btn-outline-info d-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#rescheduleModal{{ $appointment->id }}">
                    <i class="bi bi-arrow-repeat"></i>
                    <span>Reschedule</span>
                </button>
                @endif
            @endcan

            @can('delete', $appointment)
                @if($appointment->canBeCancelled())
                <button type="button" class="btn btn-outline-danger d-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#cancelModal{{ $appointment->id }}">
                    <i class="bi bi-x-circle"></i>
                    <span>Cancel</span>
                </button>
                @endif
            @endcan

            @can('update', $appointment)
                @if($appointment->canBeMarkedCompleted() || $appointment->canBeMarkedNoShow())
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Status Action
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        @if($appointment->canBeMarkedCompleted())
                        <li>
                            <form action="{{ route('portal.appointments.status', $appointment) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="dropdown-item text-primary" onclick="return confirm('Mark consultation as Completed?')">
                                    <i class="bi bi-check-all me-2"></i> Mark Completed
                                </button>
                            </form>
                        </li>
                        @endif
                        @if($appointment->canBeMarkedNoShow())
                        <li>
                            <form action="{{ route('portal.appointments.status', $appointment) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="no_show">
                                <button type="submit" class="dropdown-item text-secondary" onclick="return confirm('Mark patient as No Show?')">
                                    <i class="bi bi-person-x me-2"></i> Mark No Show
                                </button>
                            </form>
                        </li>
                        @endif
                    </ul>
                </div>
                @endif
            @endcan
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
            <div>{{ session('status') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Bento Grid Content -->
    <div class="row g-4 mb-4">
        <!-- Patient Information Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold text-navy mb-0 fs-6">
                        <i class="bi bi-person-vcard text-teal me-2"></i> Patient Information
                    </h5>
                    @if($appointment->patient)
                    <a href="{{ route('portal.patients.show', $appointment->patient) }}" class="btn btn-sm btn-link text-teal text-decoration-none p-0">
                        Full Record &rarr;
                    </a>
                    @endif
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-teal-subtle text-teal d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 50px; height: 50px;">
                            {{ strtoupper(substr($appointment->patient_display_name, 0, 1)) }}
                        </div>
                        <div>
                            <h5 class="fw-bold text-navy mb-0">{{ $appointment->patient_display_name }}</h5>
                            <span class="badge {{ $appointment->patient ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                {{ $appointment->patient ? 'Registered Patient' : 'Walk-in / Guest' }}
                            </span>
                        </div>
                    </div>

                    <table class="table table-sm table-borderless mb-0 small">
                        <tr>
                            <td class="text-muted ps-0">Mobile Phone:</td>
                            <td class="fw-semibold text-navy">{{ $appointment->patient_display_phone }}</td>
                        </tr>
                        @if($appointment->patient)
                        <tr>
                            <td class="text-muted ps-0">Patient Code:</td>
                            <td class="fw-semibold text-teal">{{ $appointment->patient->patient_code }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Gender / Age:</td>
                            <td class="fw-semibold text-navy">{{ ucfirst($appointment->patient->gender ?? 'N/A') }} ({{ $appointment->patient->age ?? '—' }} yrs)</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Blood Group:</td>
                            <td class="fw-semibold text-danger">{{ $appointment->patient->blood_group ?? 'Unknown' }}</td>
                        </tr>
                        @elseif($appointment->guest_email)
                        <tr>
                            <td class="text-muted ps-0">Email:</td>
                            <td class="fw-semibold text-navy">{{ $appointment->guest_email }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Consulting Doctor & Department Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold text-navy mb-0 fs-6">
                        <i class="bi bi-heart-pulse text-teal me-2"></i> Clinical Department & Doctor
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <span class="badge bg-teal-subtle text-teal px-3 py-1 rounded-pill fw-semibold mb-2">
                            {{ $appointment->department?->name }} ({{ $appointment->department?->code }})
                        </span>
                        <h5 class="fw-bold text-navy mb-1">{{ $appointment->doctor?->name }}</h5>
                        <p class="text-muted small mb-0">{{ $appointment->doctor?->staffProfile?->designation ?? 'Consultant' }}</p>
                    </div>

                    <table class="table table-sm table-borderless mb-0 small">
                        <tr>
                            <td class="text-muted ps-0">Specialization:</td>
                            <td class="fw-semibold text-navy">{{ $appointment->doctor?->staffProfile?->specialization ?? 'General Care' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">License No:</td>
                            <td class="fw-semibold text-navy">{{ $appointment->doctor?->staffProfile?->license_number ?? 'BMDC Verified' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Consultation Room:</td>
                            <td class="fw-semibold text-teal">{{ $appointment->doctor?->staffProfile?->room_number ?? 'OPD Room 101' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Clinic Phone:</td>
                            <td class="fw-semibold text-navy">+880 1711-000004</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Consultation Schedule & Slot Details -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold text-navy mb-0 fs-6">
                        <i class="bi bi-calendar-check text-teal me-2"></i> Consultation Schedule
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-3">
                        <div class="text-center px-2 border-end pe-3">
                            <span class="d-block text-uppercase fw-bold text-teal small">{{ $appointment->appointment_date->format('M') }}</span>
                            <span class="d-block fw-bold text-navy fs-3 lh-1">{{ $appointment->appointment_date->format('d') }}</span>
                            <span class="d-block text-muted small">{{ $appointment->appointment_date->format('Y') }}</span>
                        </div>
                        <div>
                            <div class="fw-bold text-navy fs-5">
                                {{ $appointment->appointment_time ? date('h:i A', strtotime($appointment->appointment_time)) : $appointment->appointment_date->format('h:i A') }}
                            </div>
                            <span class="text-muted small">
                                {{ $appointment->appointment_date->format('l') }} &bull; {{ $appointment->slot_duration_minutes }} min slot
                            </span>
                        </div>
                    </div>

                    <table class="table table-sm table-borderless mb-0 small">
                        <tr>
                            <td class="text-muted ps-0">Origin Type:</td>
                            <td class="fw-semibold text-capitalize text-navy">{{ str_replace('_', ' ', $appointment->type) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Serial Token:</td>
                            <td class="fw-bold text-navy">#{{ $appointment->serial_number ?: 'Not assigned' }}</td>
                        </tr>
                        @if($appointment->confirmedBy)
                        <tr>
                            <td class="text-muted ps-0">Confirmed By:</td>
                            <td class="fw-semibold text-success">{{ $appointment->confirmedBy->name }} ({{ $appointment->confirmed_at?->format('d M, h:i A') }})</td>
                        </tr>
                        @endif
                        @if($appointment->cancelledBy)
                        <tr>
                            <td class="text-muted ps-0">Cancelled By:</td>
                            <td class="fw-semibold text-danger">{{ $appointment->cancelledBy->name }} ({{ $appointment->cancelled_at?->format('d M, h:i A') }})</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Reason & Cancellation Notice (if cancelled) -->
    @if($appointment->reason || $appointment->cancellation_reason)
    <div class="row g-4 mb-4">
        @if($appointment->reason)
        <div class="col-md-{{ $appointment->cancellation_reason ? '6' : '12' }}">
            <div class="card border-0 shadow-sm rounded-3 p-4 bg-white">
                <h6 class="fw-bold text-navy mb-2"><i class="bi bi-chat-left-text text-teal me-2"></i> Chief Complaints / Consultation Reason</h6>
                <p class="text-muted mb-0 small">{{ $appointment->reason }}</p>
            </div>
        </div>
        @endif

        @if($appointment->cancellation_reason)
        <div class="col-md-{{ $appointment->reason ? '6' : '12' }}">
            <div class="card border-0 shadow-sm rounded-3 p-4 bg-danger-subtle border-start border-4 border-danger">
                <h6 class="fw-bold text-danger mb-2"><i class="bi bi-exclamation-octagon text-danger me-2"></i> Cancellation Reason</h6>
                <p class="text-danger-emphasis mb-0 small">{{ $appointment->cancellation_reason }}</p>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Audit History Timeline -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title fw-bold text-navy mb-0 fs-6">
                <i class="bi bi-clock-history text-teal me-2"></i> Appointment Audit & Activity History
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="timeline position-relative">
                @forelse($appointment->histories as $history)
                <div class="d-flex gap-3 mb-4 position-relative">
                    <div class="flex-shrink-0">
                        <span class="rounded-circle d-flex align-items-center justify-content-center bg-teal-subtle text-teal fw-bold" style="width: 36px; height: 36px;">
                            @if($history->action === 'requested') <i class="bi bi-calendar-plus"></i>
                            @elseif($history->action === 'confirmed') <i class="bi bi-check2-circle text-success"></i>
                            @elseif($history->action === 'rescheduled') <i class="bi bi-arrow-repeat text-info"></i>
                            @elseif($history->action === 'cancelled') <i class="bi bi-x-octagon text-danger"></i>
                            @elseif($history->action === 'completed') <i class="bi bi-clipboard-check text-primary"></i>
                            @else <i class="bi bi-dot fs-3"></i> @endif
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                            <span class="fw-bold text-navy text-capitalize">
                                {{ str_replace('_', ' ', $history->action) }}
                                <span class="badge bg-light text-muted border ms-1">{{ ucfirst($history->new_status) }}</span>
                            </span>
                            <small class="text-muted">{{ $history->created_at->format('d M Y, h:i:s A') }}</small>
                        </div>
                        <p class="text-muted small mb-1 mt-1">{{ $history->note }}</p>
                        @if($history->previous_date && $history->new_date && $history->previous_date != $history->new_date)
                            <div class="badge bg-light text-muted border small fw-normal">
                                Shifted: {{ $history->previous_date->format('d M, h:i A') }} &rarr; {{ $history->new_date->format('d M, h:i A') }}
                            </div>
                        @endif
                        <div class="text-muted small mt-1" style="font-size: 0.78rem;">
                            Actor: <strong>{{ $history->changedBy?->name ?? 'Online Visitor / System' }}</strong>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted small mb-0">No history records available.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Include Confirmation, Reschedule, and Cancellation Modals -->
@include('portal.appointments.partials.modals', ['appointment' => $appointment])
@endsection
