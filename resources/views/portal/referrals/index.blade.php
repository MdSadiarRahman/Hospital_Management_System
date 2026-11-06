<x-layouts.portal title="Emergency Referrals & ICU/CCU Transfers — Meghna Clinic">
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
                    <strong>Action blocked:</strong>
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
                        <li class="breadcrumb-item active" aria-current="page">Emergency Referrals</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-navy mb-0">Emergency Referrals & Tertiary Transfers</h1>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">ICU / CCU Transfer Gateway</span>
                </div>
                <p class="text-muted small mb-0">Document emergency clinical assessments, track external ICU/CCU transfers, and generate medical handover slips.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if(auth()->user()->hasAnyRole(['doctor', 'administrator', 'super_admin']))
                    <a href="{{ route('portal.referrals.create') }}" class="btn btn-danger text-white">
                        <i class="bi bi-plus-circle me-1"></i> Initiate Emergency Referral
                    </a>
                @endif
            </div>
        </div>

        {{-- Critical Care Scope & Facility Notice --}}
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-start mb-4 rounded-3" role="alert">
            <i class="bi bi-info-circle-fill fs-4 me-3 text-warning"></i>
            <div>
                <strong class="d-block text-dark">Clinical Scope Notice — Tertiary Referral Policy:</strong>
                <span class="small text-muted">
                    Meghna Clinic is a secondary healthcare clinic with 10 inpatient beds and <strong>does not operate on-site Intensive Care (ICU) or Coronary Care (CCU) units</strong>. Patients requiring mechanical ventilation, emergency hemodialysis, or invasive hemodynamic support are stabilized immediately by our on-duty doctors and urgently transferred to <strong>M. Abdur Rahim Medical College Hospital</strong> or affiliated tertiary centers.
                </span>
            </div>
        </div>

        {{-- Bento Grid KPI Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-2">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">Today's Total</span>
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-navy tabular-nums">{{ $statistics['total_today'] }}</h3>
                    <span class="text-muted small" style="font-size: 0.75rem;">Referred today</span>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">In Transit</span>
                        <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-truck"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-warning tabular-nums">{{ $statistics['active_in_transit'] }}</h3>
                    <span class="text-muted small" style="font-size: 0.75rem;">En route via ambulance</span>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">External ICU/CCU</span>
                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-heart-pulse-fill"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-danger tabular-nums">{{ $statistics['icu_ccu_total'] }}</h3>
                    <span class="text-muted small" style="font-size: 0.75rem;">Intensive care transfers</span>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">Critical Patients</span>
                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-danger tabular-nums">{{ $statistics['critical_total'] }}</h3>
                    <span class="text-muted small" style="font-size: 0.75rem;">High-risk condition</span>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">Transferred</span>
                        <div class="rounded-circle bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-success tabular-nums">{{ $statistics['transferred_today'] }}</h3>
                    <span class="text-muted small" style="font-size: 0.75rem;">Handover confirmed</span>
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
                    <h3 class="fw-bold mb-0 text-secondary tabular-nums">{{ $statistics['cancelled_total'] }}</h3>
                    <span class="text-muted small" style="font-size: 0.75rem;">Total cancelled</span>
                </div>
            </div>
        </div>

        {{-- Filter & Search Toolbar --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('portal.referrals.index') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted mb-1">Search Patient / Facility / ID</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="REF-#, Name, Phone..." value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Referral Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="initiated" {{ ($filters['status'] ?? '') === 'initiated' ? 'selected' : '' }}>Initiated</option>
                            <option value="in_transit" {{ ($filters['status'] ?? '') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                            <option value="transferred" {{ ($filters['status'] ?? '') === 'transferred' ? 'selected' : '' }}>Transferred</option>
                            <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Classification</label>
                        <select name="referral_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="icu_ccu_emergency" {{ ($filters['referral_type'] ?? '') === 'icu_ccu_emergency' ? 'selected' : '' }}>ICU/CCU Emergency</option>
                            <option value="tertiary_transfer" {{ ($filters['referral_type'] ?? '') === 'tertiary_transfer' ? 'selected' : '' }}>Tertiary Medical College</option>
                            <option value="specialist" {{ ($filters['referral_type'] ?? '') === 'specialist' ? 'selected' : '' }}>Specialist Outsource</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Destination</label>
                        <select name="facility_name" class="form-select">
                            <option value="">All Facilities</option>
                            @foreach($destinations as $dest)
                                <option value="{{ $dest }}" {{ ($filters['facility_name'] ?? '') === $dest ? 'selected' : '' }}>
                                    {{ Str::limit($dest, 25) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Condition Filter</label>
                        <div class="d-flex align-items-center gap-2 pt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_critical" id="filter_is_critical" value="1" {{ !empty($filters['is_critical']) ? 'checked' : '' }}>
                                <label class="form-check-label small text-danger fw-semibold" for="filter_is_critical">Critical Only</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-1 d-flex gap-1">
                        <button type="submit" class="btn btn-primary w-100" title="Apply Filters">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <a href="{{ route('portal.referrals.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Referrals Table --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title fw-bold text-navy mb-0">
                    <i class="bi bi-box-arrow-up-right text-danger me-2"></i>Emergency Referrals Roster
                </h5>
                <span class="badge bg-light text-muted border">{{ $referrals->total() }} Total Referrals</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th scope="col" style="min-width: 140px;">Referral #</th>
                            <th scope="col" style="min-width: 180px;">Patient Info</th>
                            <th scope="col" style="min-width: 160px;">Destination & Dept</th>
                            <th scope="col" style="min-width: 200px;">Reason & Clinical Assessment</th>
                            <th scope="col" style="min-width: 150px;">Transport & Transit</th>
                            <th scope="col" class="text-center" style="width: 120px;">Condition</th>
                            <th scope="col" class="text-center" style="width: 110px;">Status</th>
                            <th scope="col" class="text-end" style="min-width: 130px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($referrals as $ref)
                            <tr>
                                <td>
                                    <span class="fw-bold text-navy font-monospace">{{ $ref->referral_number }}</span>
                                    <div class="text-muted small" style="font-size: 0.75rem;">
                                        <i class="bi bi-clock me-1"></i>{{ $ref->referred_at?->format('d M Y, h:i A') }}
                                    </div>
                                    <div class="text-muted small" style="font-size: 0.75rem;">
                                        By: Dr. {{ $ref->authorizedBy?->name ?? 'Attending Doctor' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $ref->patient?->name }}</div>
                                    <div class="small text-muted font-monospace">{{ $ref->patient?->patient_code }}</div>
                                    <div class="small text-muted" style="font-size: 0.75rem;">
                                        {{ $ref->patient?->formatted_age }}, {{ ucfirst($ref->patient?->gender ?? '') }} | 
                                        Blood: <strong class="text-danger">{{ $ref->patient?->blood_group ?? 'N/A' }}</strong>
                                        @if($ref->admission?->currentBedAssignment?->bed)
                                            <span class="badge bg-light text-dark border ms-1">
                                                <i class="bi bi-hospital me-1"></i>{{ $ref->admission->currentBedAssignment->bed->bed_number }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $ref->facility_name }}</div>
                                    <div class="text-muted small">{{ $ref->destination_department ?? 'Emergency & Trauma' }}</div>
                                    {!! $ref->type_badge !!}
                                </td>
                                <td>
                                    <div class="text-dark fw-semibold text-truncate" style="max-width: 250px;" title="{{ $ref->clinical_reason }}">
                                        {{ $ref->clinical_reason }}
                                    </div>
                                    @if($ref->emergency_assessment)
                                        <div class="text-muted small text-truncate" style="max-width: 250px;" title="{{ $ref->emergency_assessment }}">
                                            Assessment: {{ $ref->emergency_assessment }}
                                        </div>
                                    @endif
                                    @if($ref->requires_icu)
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger mt-1">
                                            <i class="bi bi-heart-pulse me-1"></i>Requires ICU/CCU
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($ref->transport_arranged)
                                        <div class="small text-dark fw-semibold">
                                            <i class="bi bi-truck text-teal me-1"></i>{{ $ref->transport_type ?: 'Ambulance Arranged' }}
                                        </div>
                                        @if($ref->oxygen_support_transit)
                                            <span class="badge bg-info bg-opacity-10 text-info border" style="font-size: 0.7rem;">
                                                <i class="bi bi-lungs me-1"></i>Oxygen En-Route
                                            </span>
                                        @endif
                                        @if($ref->accompanying_staff)
                                            <div class="text-muted small" style="font-size: 0.75rem;">Staff: {{ $ref->accompanying_staff }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted small"><i class="bi bi-exclamation-circle text-warning me-1"></i>Pending Transport</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($ref->is_critical)
                                        <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>CRITICAL</span>
                                    @else
                                        <span class="badge bg-light text-dark border">Standard Urgent</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {!! $ref->status_badge !!}
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('portal.referrals.show', $ref) }}" class="btn btn-outline-primary" title="View Dossier">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="{{ route('portal.referrals.print', $ref) }}" target="_blank" class="btn btn-outline-secondary" title="Print Handover Slip">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="py-3">
                                        <i class="bi bi-box-arrow-up-right text-muted" style="font-size: 2.5rem;"></i>
                                        <h6 class="fw-bold mt-2 text-dark">No Emergency Referrals Found</h6>
                                        <p class="text-muted small mb-3">No tertiary transfers or external ICU/CCU referrals match your criteria.</p>
                                        @if(auth()->user()->hasAnyRole(['doctor', 'administrator', 'super_admin']))
                                            <a href="{{ route('portal.referrals.create') }}" class="btn btn-danger btn-sm text-white">
                                                <i class="bi bi-plus-circle me-1"></i> Initiate Emergency Referral
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($referrals->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $referrals->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.portal>

