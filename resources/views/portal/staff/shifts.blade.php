<x-layouts.portal role="admin" title="24/7 Shift Scheduling & Roster Board" activeNav="staff">
    <div class="container-fluid p-0">
        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <x-breadcrumb :items="[
                    'Administration' => url('/previews/admin'),
                    'Staff Directory' => route('portal.staff.index'),
                    '24/7 Shift Roster Board' => null
                ]" class="mb-1" />
                <h1 class="h3 fw-bold text-navy mb-0">24/7 Hospital Shift Scheduling Board</h1>
                <p class="text-muted-hms mb-0" style="font-size: 0.875rem;">
                    Operational roster oversight for 8-hour nursing shifts and 24/7 continuous physician availability.
                </p>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                @can('scheduleShifts', App\Models\StaffProfile::class)
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#quickScheduleModal">
                        <i class="bi bi-calendar-plus-fill"></i>
                        <span>Assign Shift Duty</span>
                    </button>
                @endcan

                <a href="{{ route('portal.staff.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-people me-1"></i> Staff Directory
                </a>
            </div>
        </div>

        <!-- Flash Alerts -->
        @if(session('status'))
            <x-alert type="success" class="mb-4" dismissible>
                {{ session('status') }}
            </x-alert>
        @endif

        @if(session('error'))
            <x-alert type="danger" class="mb-4" dismissible>
                {{ session('error') }}
            </x-alert>
        @endif

        <!-- Date Selector Toolbar -->
        @php
            $currentDate = \Carbon\Carbon::parse($targetDate);
            $prevDate = $currentDate->copy()->subDay()->toDateString();
            $nextDate = $currentDate->copy()->addDay()->toDateString();
            $isToday = $currentDate->isToday();
        @endphp

        <div class="card border-0 shadow-sm rounded-3 bg-white p-3 mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('portal.staff.shifts', ['date' => $prevDate]) }}" class="btn btn-sm btn-light border" title="Previous Day">
                        <i class="bi bi-chevron-left"></i>
                    </a>

                    <div class="d-flex align-items-center gap-2 px-2">
                        <i class="bi bi-calendar-event text-teal fs-5"></i>
                        <span class="fw-bold text-navy fs-5">
                            {{ $currentDate->format('l, d F Y') }}
                        </span>
                        @if($isToday)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                Today (Live)
                            </span>
                        @endif
                    </div>

                    <a href="{{ route('portal.staff.shifts', ['date' => $nextDate]) }}" class="btn btn-sm btn-light border" title="Next Day">
                        <i class="bi bi-chevron-right"></i>
                    </a>

                    @if(!$isToday)
                        <a href="{{ route('portal.staff.shifts', ['date' => now()->toDateString()]) }}" class="btn btn-sm btn-outline-navy ms-2">
                            Today
                        </a>
                    @endif
                </div>

                <form action="{{ route('portal.staff.shifts') }}" method="GET" class="d-flex align-items-center gap-2">
                    <label for="date_picker" class="form-label mb-0 small text-muted fw-semibold">Select Date:</label>
                    <input type="date" name="date" id="date_picker" class="form-control form-control-sm" value="{{ $targetDate }}" onchange="this.form.submit()">
                </form>
            </div>
        </div>

        <!-- Clinic Operational Compliance Rules Status Banners -->
        <div class="row g-3 mb-4">
            <!-- Rule 1: 2 Doctors 24/7 -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 {{ $summary['meets_doctor_rule'] ? 'border-success' : 'border-danger' }}">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-1.5 mb-1">
                                <span class="badge {{ $summary['meets_doctor_rule'] ? 'bg-success' : 'bg-danger' }} rounded-circle p-1"></span>
                                <span class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                    Clinical Rule: 2 Doctors 24/7 Availability
                                </span>
                            </div>
                            <h4 class="fw-bold text-navy mb-0">
                                {{ $summary['doctors_count'] }} Doctors Scheduled
                            </h4>
                            <p class="text-muted small mb-0 mt-1">
                                @if($summary['meets_doctor_rule'])
                                    <span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Compliant:</span> Minimum 2 doctors available on clinical duty for emergency, inpatient rounds, and surgeries.
                                @else
                                    <span class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Action Required:</span> Less than 2 doctors scheduled on this date. Please assign on-call medical cover.
                                @endif
                            </p>
                        </div>
                        <div class="rounded-circle {{ $summary['meets_doctor_rule'] ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }} p-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-heart-pulse fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rule 2: 8-Hour Nursing Shifts (6 Nurses) -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 {{ $summary['meets_nurse_rule'] ? 'border-teal' : 'border-warning' }}">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-1.5 mb-1">
                                <span class="badge {{ $summary['meets_nurse_rule'] ? 'bg-teal' : 'bg-warning' }} rounded-circle p-1"></span>
                                <span class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                    Clinical Rule: 8-Hour Nursing Shift Coverage
                                </span>
                            </div>
                            <h4 class="fw-bold text-navy mb-0">
                                {{ $summary['nurses_count'] }} Nurses on Duty
                            </h4>
                            <p class="text-muted small mb-0 mt-1">
                                6 nurses rotate across 3 eight-hour shifts (Morning, Evening, Night) to ensure continuous bedside inpatient coverage.
                            </p>
                        </div>
                        <div class="rounded-circle bg-teal bg-opacity-10 text-teal p-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3-Column 8-Hour Shifts Roster Grid -->
        <div class="row g-4 mb-4">
            <!-- SHIFT 1: Morning (08:00 AM - 04:00 PM) -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 bg-white h-100 overflow-hidden">
                    <div class="card-header bg-primary bg-opacity-10 border-bottom border-primary-subtle p-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-primary text-white p-1.5 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                <i class="bi bi-sun-fill"></i>
                            </span>
                            <div>
                                <h2 class="h6 fw-bold text-navy mb-0">Morning Shift</h2>
                                <span class="text-muted font-monospace" style="font-size: 0.75rem;">08:00 AM - 04:00 PM (8h)</span>
                            </div>
                        </div>
                        <span class="badge bg-primary rounded-pill px-2.5 py-1">
                            {{ $summary['morning']->count() }} Assigned
                        </span>
                    </div>

                    <div class="card-body p-3">
                        @if($summary['morning']->isEmpty())
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-calendar-x fs-3 opacity-50 mb-1 d-block"></i>
                                <span class="small">No personnel scheduled for morning shift.</span>
                            </div>
                        @else
                            <div class="d-flex flex-column gap-2.5">
                                @foreach($summary['morning'] as $shift)
                                    @php
                                        $profile = $shift->staffProfile;
                                        $user = $profile->user;
                                    @endphp
                                    <div class="p-3 rounded-3 border bg-light position-relative">
                                        <div class="d-flex align-items-start justify-content-between gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle {{ $profile->isDoctor() ? 'bg-primary' : 'bg-teal' }} text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <a href="{{ route('portal.staff.show', $profile) }}" class="fw-bold text-navy text-decoration-none hover-teal d-block" style="font-size: 0.85rem;">
                                                        {{ $user->name }}
                                                    </a>
                                                    <span class="text-muted d-block" style="font-size: 0.75rem;">{{ $profile->designation }}</span>
                                                </div>
                                            </div>

                                            @can('scheduleShifts', App\Models\StaffProfile::class)
                                                <form action="{{ route('portal.staff.delete-shift', $shift) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Cancel shift for {{ $user->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Remove Shift">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top gap-1" style="font-size: 0.75rem;">
                                            <span class="badge bg-white text-navy border">
                                                <i class="bi bi-hospital me-1"></i> {{ $shift->department->name }}
                                            </span>
                                            @if($profile->isDoctor())
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Doctor</span>
                                            @elseif($profile->isNurse())
                                                <span class="badge bg-teal-subtle text-teal border border-teal-subtle rounded-pill">Nurse</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill">Staff</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- SHIFT 2: Evening (04:00 PM - 12:00 AM) -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 bg-white h-100 overflow-hidden">
                    <div class="card-header bg-warning bg-opacity-10 border-bottom border-warning-subtle p-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-warning text-dark p-1.5 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                <i class="bi bi-sunset-fill"></i>
                            </span>
                            <div>
                                <h2 class="h6 fw-bold text-navy mb-0">Evening Shift</h2>
                                <span class="text-muted font-monospace" style="font-size: 0.75rem;">04:00 PM - 12:00 AM (8h)</span>
                            </div>
                        </div>
                        <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1">
                            {{ $summary['evening']->count() }} Assigned
                        </span>
                    </div>

                    <div class="card-body p-3">
                        @if($summary['evening']->isEmpty())
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-calendar-x fs-3 opacity-50 mb-1 d-block"></i>
                                <span class="small">No personnel scheduled for evening shift.</span>
                            </div>
                        @else
                            <div class="d-flex flex-column gap-2.5">
                                @foreach($summary['evening'] as $shift)
                                    @php
                                        $profile = $shift->staffProfile;
                                        $user = $profile->user;
                                    @endphp
                                    <div class="p-3 rounded-3 border bg-light position-relative">
                                        <div class="d-flex align-items-start justify-content-between gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle {{ $profile->isDoctor() ? 'bg-primary' : 'bg-teal' }} text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <a href="{{ route('portal.staff.show', $profile) }}" class="fw-bold text-navy text-decoration-none hover-teal d-block" style="font-size: 0.85rem;">
                                                        {{ $user->name }}
                                                    </a>
                                                    <span class="text-muted d-block" style="font-size: 0.75rem;">{{ $profile->designation }}</span>
                                                </div>
                                            </div>

                                            @can('scheduleShifts', App\Models\StaffProfile::class)
                                                <form action="{{ route('portal.staff.delete-shift', $shift) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Cancel shift for {{ $user->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Remove Shift">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top gap-1" style="font-size: 0.75rem;">
                                            <span class="badge bg-white text-navy border">
                                                <i class="bi bi-hospital me-1"></i> {{ $shift->department->name }}
                                            </span>
                                            @if($profile->isDoctor())
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Doctor</span>
                                            @elseif($profile->isNurse())
                                                <span class="badge bg-teal-subtle text-teal border border-teal-subtle rounded-pill">Nurse</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill">Staff</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- SHIFT 3: Night (12:00 AM - 08:00 AM) -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 bg-white h-100 overflow-hidden">
                    <div class="card-header bg-dark bg-opacity-10 border-bottom border-dark-subtle p-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-navy text-white p-1.5 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                <i class="bi bi-moon-stars-fill"></i>
                            </span>
                            <div>
                                <h2 class="h6 fw-bold text-navy mb-0">Night Shift</h2>
                                <span class="text-muted font-monospace" style="font-size: 0.75rem;">12:00 AM - 08:00 AM (8h)</span>
                            </div>
                        </div>
                        <span class="badge bg-navy text-white rounded-pill px-2.5 py-1">
                            {{ $summary['night']->count() }} Assigned
                        </span>
                    </div>

                    <div class="card-body p-3">
                        @if($summary['night']->isEmpty())
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-calendar-x fs-3 opacity-50 mb-1 d-block"></i>
                                <span class="small">No personnel scheduled for night shift.</span>
                            </div>
                        @else
                            <div class="d-flex flex-column gap-2.5">
                                @foreach($summary['night'] as $shift)
                                    @php
                                        $profile = $shift->staffProfile;
                                        $user = $profile->user;
                                    @endphp
                                    <div class="p-3 rounded-3 border bg-light position-relative">
                                        <div class="d-flex align-items-start justify-content-between gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle {{ $profile->isDoctor() ? 'bg-primary' : 'bg-teal' }} text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <a href="{{ route('portal.staff.show', $profile) }}" class="fw-bold text-navy text-decoration-none hover-teal d-block" style="font-size: 0.85rem;">
                                                        {{ $user->name }}
                                                    </a>
                                                    <span class="text-muted d-block" style="font-size: 0.75rem;">{{ $profile->designation }}</span>
                                                </div>
                                            </div>

                                            @can('scheduleShifts', App\Models\StaffProfile::class)
                                                <form action="{{ route('portal.staff.delete-shift', $shift) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Cancel shift for {{ $user->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Remove Shift">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top gap-1" style="font-size: 0.75rem;">
                                            <span class="badge bg-white text-navy border">
                                                <i class="bi bi-hospital me-1"></i> {{ $shift->department->name }}
                                            </span>
                                            @if($profile->isDoctor())
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Doctor</span>
                                            @elseif($profile->isNurse())
                                                <span class="badge bg-teal-subtle text-teal border border-teal-subtle rounded-pill">Nurse</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill">Staff</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Quick Assign Duty Shift -->
    @can('scheduleShifts', App\Models\StaffProfile::class)
        <div class="modal fade" id="quickScheduleModal" tabindex="-1" aria-labelledby="quickScheduleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-navy text-white">
                        <h5 class="modal-title fs-6 fw-bold" id="quickScheduleModalLabel">
                            <i class="bi bi-calendar-plus text-teal me-1"></i> Assign 8-Hour Duty Shift
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('portal.staff.schedule-shift') }}" method="POST">
                        @csrf
                        <div class="modal-body p-4 text-start">
                            <div class="mb-3">
                                <label for="quick_staff_profile_id" class="form-label fw-semibold text-navy">Staff Member <span class="text-danger">*</span></label>
                                <select name="staff_profile_id" id="quick_staff_profile_id" class="form-select" required>
                                    <option value="">-- Choose Healthcare Personnel --</option>
                                    @foreach($activeStaff as $person)
                                        <option value="{{ $person->id }}">
                                            {{ $person->user->name }} &bull; {{ $person->designation }} ({{ $person->department?->name ?? 'General' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="quick_department_id" class="form-label fw-semibold text-navy">Station Ward / Department <span class="text-danger">*</span></label>
                                <select name="department_id" id="quick_department_id" class="form-select" required>
                                    <option value="">-- Select Ward Station --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">
                                            {{ $dept->name }} ({{ $dept->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="quick_shift_date" class="form-label fw-semibold text-navy">Shift Date <span class="text-danger">*</span></label>
                                <input type="date" name="shift_date" id="quick_shift_date" class="form-control" value="{{ $targetDate }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="quick_shift_type" class="form-label fw-semibold text-navy">8-Hour Shift Window <span class="text-danger">*</span></label>
                                <select name="shift_type" id="quick_shift_type" class="form-select" required>
                                    <option value="morning">Morning Shift (08:00 AM - 04:00 PM)</option>
                                    <option value="evening">Evening Shift (04:00 PM - 12:00 AM)</option>
                                    <option value="night">Night Shift (12:00 AM - 08:00 AM)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="quick_shift_notes" class="form-label fw-semibold text-navy">Handover Instructions / Notes</label>
                                <input type="text" name="notes" id="quick_shift_notes" class="form-control" placeholder="e.g. Ward Sister in charge / Emergency triage cover">
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle me-1"></i> Confirm Assignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
</x-layouts.portal>
