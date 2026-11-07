<x-layouts.portal :title="'Audit Record #' . $auditLog->id . ' — Meghna Clinic & Nursing Home'" :activeNav="'audit'">
    <div class="container-fluid px-0">

        {{-- Page Header --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('portal.admin') }}" class="text-teal text-decoration-none">Administration</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('portal.audit-logs.index') }}" class="text-teal text-decoration-none">Security Audit Logs</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Log Entry #{{ $auditLog->id }}</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold text-navy mb-0">
                    <i class="bi bi-shield-check text-teal me-2"></i>Audit Inspection: #{{ $auditLog->id }}
                </h2>
                <p class="text-muted small mb-0">Cryptographic audit snapshot recorded at {{ $auditLog->created_at->format('d M Y, h:i:s A') }}</p>
            </div>
            <div>
                <a href="{{ route('portal.audit-logs.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Audit Ledger</span>
                </a>
            </div>
        </div>

        {{-- Metadata Bento Grid --}}
        <div class="row g-4 mb-4">
            {{-- Summary Card --}}
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3">
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Event Action</span>
                        <span class="badge bg-{{ $auditLog->event_badge_class }} fs-6 font-monospace py-1.5 px-2.5">
                            {{ $auditLog->event }}
                        </span>
                        <div class="small text-muted mt-2">
                            Type: <strong class="text-navy">{{ $auditLog->formatted_auditable_name }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actor Card --}}
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3">
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Responsible User</span>
                        @if($auditLog->user)
                            <div class="fw-bold text-navy">{{ $auditLog->user->name }}</div>
                            <span class="text-muted small font-monospace d-block">{{ $auditLog->user->email }}</span>
                            <span class="badge bg-teal-subtle text-teal mt-1">ID: {{ $auditLog->user->id }}</span>
                        @else
                            <span class="badge bg-light text-muted border">System Automated</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Network Card --}}
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3">
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Network Context</span>
                        <div class="font-monospace fw-bold text-navy">{{ $auditLog->ip_address ?? '127.0.0.1' }}</div>
                        <span class="text-muted small d-block text-truncate" title="{{ $auditLog->user_agent }}">
                            {{ Str::limit($auditLog->user_agent ?? 'Unknown Agent', 30) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Timestamp Card --}}
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3">
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Timestamp</span>
                        <div class="fw-bold text-navy">{{ $auditLog->created_at->format('d M Y') }}</div>
                        <span class="text-teal font-monospace small">{{ $auditLog->created_at->format('h:i:s A (T)') }}</span>
                        <span class="text-muted small d-block">{{ $auditLog->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- State Diffs: Old vs New Values --}}
        <div class="row g-4">
            {{-- Prior State --}}
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-navy"><i class="bi bi-clock-history text-secondary me-2"></i>Prior State (Old Values)</span>
                        <span class="badge bg-light text-muted border font-monospace">Pre-Modification</span>
                    </div>
                    <div class="card-body p-3">
                        @if(!empty($auditLog->old_values))
                            <pre class="bg-light p-3 rounded-2 text-dark font-monospace small mb-0" style="max-height: 400px; overflow-y: auto;"><code>{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                        @else
                            <div class="text-muted text-center py-4 small">
                                <i class="bi bi-dash-circle fs-3 d-block mb-1 text-muted-hms"></i>
                                No prior state recorded (creation or initial action).
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Subsequent State --}}
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-navy"><i class="bi bi-check2-circle text-teal me-2"></i>Subsequent State (New Values)</span>
                        <span class="badge bg-teal-subtle text-teal font-monospace">Post-Modification</span>
                    </div>
                    <div class="card-body p-3">
                        @if(!empty($auditLog->new_values))
                            <pre class="bg-light p-3 rounded-2 text-dark font-monospace small mb-0" style="max-height: 400px; overflow-y: auto;"><code>{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                        @else
                            <div class="text-muted text-center py-4 small">
                                <i class="bi bi-dash-circle fs-3 d-block mb-1 text-muted-hms"></i>
                                No new state values recorded (deletion or status removal).
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.portal>

