<x-layouts.portal :title="'Security Audit Ledger — Meghna Clinic & Nursing Home'" :activeNav="'audit'">
    <div class="container-fluid px-0">

        {{-- Page Header --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('portal.admin') }}" class="text-teal text-decoration-none">Administration</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Security Audit Logs</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold text-navy mb-0">
                    <i class="bi bi-shield-lock-fill text-teal me-2"></i>Security Audit Ledger
                </h2>
                <p class="text-muted small mb-0">Immutable tracking of user authentication, role adjustments, clinical records, financials, and discharge approvals.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portal.audit-logs.print', request()->query()) }}" target="_blank" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm">
                    <i class="bi bi-printer"></i>
                    <span>Print Audit Ledger</span>
                </a>
            </div>
        </div>

        {{-- Filter & Search Form --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('portal.audit-logs.index') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label small fw-semibold text-navy mb-1">Audit Event</label>
                        <select name="event" class="form-select form-select-sm">
                            <option value="">All Events</option>
                            @foreach($events as $ev)
                                <option value="{{ $ev }}" @selected(($filters['event'] ?? '') === $ev)>{{ $ev }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label small fw-semibold text-navy mb-1">Responsible User</label>
                        <select name="user_id" class="form-select form-select-sm">
                            <option value="">All Staff / Users</option>
                            @foreach($staffUsers as $st)
                                <option value="{{ $st->id }}" @selected(($filters['user_id'] ?? '') == $st->id)>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label small fw-semibold text-navy mb-1">Target Entity</label>
                        <select name="auditable_type" class="form-select form-select-sm">
                            <option value="">All Models</option>
                            @foreach($auditableTypes as $at)
                                @php
                                    $parts = explode('\\', $at);
                                    $shortName = end($parts);
                                @endphp
                                <option value="{{ $at }}" @selected(($filters['auditable_type'] ?? '') === $at)>{{ $shortName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label small fw-semibold text-navy mb-1">Date From</label>
                        <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="form-select form-select-sm">
                    </div>

                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label small fw-semibold text-navy mb-1">Date To</label>
                        <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="form-select form-select-sm">
                    </div>

                    <div class="col-12 col-lg-2 d-flex gap-2">
                        <button type="submit" class="btn btn-teal btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('portal.audit-logs.index') }}" class="btn btn-light btn-sm border" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Audit Ledger Table --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <span class="fw-bold text-navy"><i class="bi bi-journal-text text-teal me-2"></i>Recorded Audit Events ({{ $logs->total() }} total entries)</span>
                <span class="badge bg-light text-navy border font-monospace">Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-navy small text-uppercase">
                        <tr>
                            <th class="ps-3 py-3" style="width: 170px;">Timestamp</th>
                            <th class="py-3">Event Action</th>
                            <th class="py-3">Responsible User</th>
                            <th class="py-3">Affected Record</th>
                            <th class="py-3">Client IP &amp; Device</th>
                            <th class="pe-3 py-3 text-end" style="width: 100px;">Inspection</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y font-monospace small">
                        @forelse($logs as $log)
                            <tr>
                                <td class="ps-3 py-3 text-muted">
                                    <span class="fw-bold text-navy d-block">{{ $log->created_at->format('d M Y') }}</span>
                                    <span>{{ $log->created_at->format('h:i:s A') }}</span>
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-{{ $log->event_badge_class }} py-1 px-2 font-monospace">
                                        {{ $log->event }}
                                    </span>
                                </td>
                                <td class="py-3 font-sans">
                                    @if($log->user)
                                        <div class="fw-semibold text-navy">{{ $log->user->name }}</div>
                                        <span class="text-muted small font-monospace">{{ $log->user->email }}</span>
                                    @else
                                        <span class="badge bg-light text-muted border">System / Unauthenticated</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <span class="fw-semibold text-teal">{{ $log->formatted_auditable_name }}</span>
                                    @if($log->auditable_id)
                                        <span class="badge bg-light text-navy border ms-1">ID: {{ $log->auditable_id }}</span>
                                    @endif
                                </td>
                                <td class="py-3 text-muted" style="max-width: 220px;">
                                    <span class="d-block font-monospace">{{ $log->ip_address ?? '127.0.0.1' }}</span>
                                    <span class="text-truncate d-block small text-muted" style="font-size: 0.7rem;" title="{{ $log->user_agent }}">
                                        {{ Str::limit($log->user_agent ?? 'Unknown Client', 35) }}
                                    </span>
                                </td>
                                <td class="pe-3 py-3 text-end">
                                    <a href="{{ route('portal.audit-logs.show', $log->id) }}" class="btn btn-sm btn-outline-teal d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-eye"></i> Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted font-sans">
                                    <i class="bi bi-shield-slash fs-1 d-block mb-2 text-muted-hms"></i>
                                    No audit entries match the selected filtering parameters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.portal>

