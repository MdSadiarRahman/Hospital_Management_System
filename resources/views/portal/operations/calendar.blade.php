<x-layouts.portal title="OT Timetable & Schedule — Meghna Clinic">
    <div class="container-fluid py-4">

        {{-- Breadcrumb & Header --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none">Portal</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('portal.operations.index') }}" class="text-decoration-none">Operation Theatre</a></li>
                        <li class="breadcrumb-item active" aria-current="page">OT Timetable</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold text-navy mb-0">Operating Theatre (OT) Timetable</h1>
                <p class="text-muted small mb-0">Daily theatre room occupancy, surgeon allocations, and schedule conflict matrix.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portal.operations.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-list-ul me-1"></i> Roster View
                </a>
                @if(auth()->user()->hasAnyRole(['doctor', 'administrator', 'super_admin']))
                    <a href="{{ route('portal.operations.create') }}" class="btn btn-teal text-white">
                        <i class="bi bi-plus-circle me-1"></i> Schedule Operation
                    </a>
                @endif
            </div>
        </div>

        {{-- Date Navigation Bar --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('portal.operations.calendar', ['date' => $selectedDate->copy()->subDay()->toDateString()]) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-chevron-left"></i> Previous Day
                    </a>
                    <a href="{{ route('portal.operations.calendar', ['date' => now()->toDateString()]) }}" class="btn btn-outline-primary btn-sm {{ $selectedDate->isToday() ? 'active' : '' }}">
                        Today
                    </a>
                    <a href="{{ route('portal.operations.calendar', ['date' => $selectedDate->copy()->addDay()->toDateString()]) }}" class="btn btn-outline-secondary btn-sm">
                        Next Day <i class="bi bi-chevron-right"></i>
                    </a>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-navy fs-5">
                        <i class="bi bi-calendar-event text-teal me-2"></i>{{ $selectedDate->format('l, d F Y') }}
                    </span>
                    @if($selectedDate->isToday())
                        <span class="badge bg-success">Today</span>
                    @endif
                </div>

                <form method="GET" action="{{ route('portal.operations.calendar') }}" class="d-flex align-items-center gap-2">
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ $selectedDate->toDateString() }}">
                    <button type="submit" class="btn btn-secondary btn-sm">Go</button>
                </form>
            </div>
        </div>

        {{-- Rooms Columns Grid --}}
        <div class="row g-4">
            @foreach($rooms as $room)
                @php
                    $roomOperations = $operations->where('ot_room', $room)->sortBy('scheduled_at');
                @endphp
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                        <div class="card-header bg-light border-bottom py-3 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold text-navy mb-0">{{ $room }}</h6>
                                <span class="text-muted small">{{ $roomOperations->count() }} operation(s)</span>
                            </div>
                            <span class="badge {{ $roomOperations->count() > 0 ? 'bg-primary' : 'bg-success bg-opacity-25 text-success' }}">
                                {{ $roomOperations->count() > 0 ? 'Booked' : 'Available' }}
                            </span>
                        </div>

                        <div class="card-body p-3">
                            @forelse($roomOperations as $op)
                                <div class="card border rounded-3 p-3 mb-3 shadow-sm bg-light">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="font-monospace small fw-bold text-teal">{{ $op->operation_number }}</span>
                                        {!! $op->status_badge !!}
                                    </div>
                                    <h6 class="fw-bold text-navy mb-1">{{ $op->procedure_name }}</h6>
                                    <div class="small text-muted mb-2">
                                        <i class="bi bi-person me-1"></i>{{ $op->patient?->name }} ({{ $op->patient?->patient_code }})
                                    </div>
                                    <div class="p-2 bg-white rounded border small mb-2">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="text-muted"><i class="bi bi-clock me-1"></i>Slot:</span>
                                            <strong class="text-dark">{{ $op->scheduled_at->format('h:i A') }} - {{ $op->scheduled_end_at ? $op->scheduled_end_at->format('h:i A') : '--' }}</strong>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                            <span class="text-muted"><i class="bi bi-hourglass-split me-1"></i>Duration:</span>
                                            <span>{{ $op->estimated_duration_minutes }} mins</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="small text-muted text-truncate" style="max-width: 140px;">
                                            Dr. {{ $op->primarySurgeon?->name }}
                                        </span>
                                        <a href="{{ route('portal.operations.show', $op) }}" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size: 0.75rem;">
                                            Dossier &rarr;
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2"></i>
                                    <span class="small">No surgeries booked for this theatre today.</span>
                                    <div class="mt-3">
                                        @if(auth()->user()->hasAnyRole(['doctor', 'administrator', 'super_admin']))
                                            <a href="{{ route('portal.operations.create') }}" class="btn btn-outline-teal btn-sm">
                                                <i class="bi bi-plus-circle me-1"></i> Book Slot
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</x-layouts.portal>

