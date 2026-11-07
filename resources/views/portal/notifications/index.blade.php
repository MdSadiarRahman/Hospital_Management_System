<x-layouts.portal :title="'Notification Center — Meghna Clinic & Nursing Home'" :activeNav="'notifications'">
    <div class="container-fluid px-0">

        {{-- Page Header --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('portal.admin') }}" class="text-teal text-decoration-none">Portal</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Notifications Center</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold text-navy mb-0">
                    <i class="bi bi-bell-fill text-teal me-2"></i>Staff Notifications Center
                </h2>
                <p class="text-muted small mb-0">Live clinical alerts, inpatient bed updates, diagnostic reports, and financial notifications.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('portal.notifications.mark-all-read') }}">
                        @csrf
                        <button type="submit" class="btn btn-teal btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm">
                            <i class="bi bi-check2-all"></i>
                            <span>Mark All Read ({{ $unreadCount }})</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Filter Navigation Tabs --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-2">
                <ul class="nav nav-pills gap-1 flex-wrap">
                    <li class="nav-item">
                        <a href="{{ route('portal.notifications.index') }}" class="nav-link py-1.5 px-3 {{ empty($filters['type']) && !isset($filters['is_read']) ? 'active bg-teal' : 'text-navy' }}">
                            All Notifications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('portal.notifications.index', ['is_read' => '0']) }}" class="nav-link py-1.5 px-3 {{ isset($filters['is_read']) && $filters['is_read'] === '0' ? 'active bg-danger' : 'text-navy' }}">
                            <i class="bi bi-envelope-fill me-1"></i>Unread Only
                            @if($unreadCount > 0)
                                <span class="badge bg-white text-danger ms-1">{{ $unreadCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('portal.notifications.index', ['type' => 'appointment']) }}" class="nav-link py-1.5 px-3 {{ ($filters['type'] ?? '') === 'appointment' ? 'active bg-info' : 'text-navy' }}">
                            <i class="bi bi-calendar-check me-1"></i>Appointments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('portal.notifications.index', ['type' => 'admission']) }}" class="nav-link py-1.5 px-3 {{ ($filters['type'] ?? '') === 'admission' ? 'active bg-primary' : 'text-navy' }}">
                            <i class="bi bi-hospital me-1"></i>Admissions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('portal.notifications.index', ['type' => 'bed_alert']) }}" class="nav-link py-1.5 px-3 {{ ($filters['type'] ?? '') === 'bed_alert' ? 'active bg-warning text-dark' : 'text-navy' }}">
                            <i class="bi bi-door-open me-1"></i>Bed Alerts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('portal.notifications.index', ['type' => 'lab_ready']) }}" class="nav-link py-1.5 px-3 {{ ($filters['type'] ?? '') === 'lab_ready' ? 'active bg-teal' : 'text-navy' }}">
                            <i class="bi bi-clipboard2-pulse me-1"></i>Lab Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('portal.notifications.index', ['type' => 'billing']) }}" class="nav-link py-1.5 px-3 {{ ($filters['type'] ?? '') === 'billing' ? 'active bg-success' : 'text-navy' }}">
                            <i class="bi bi-cash-stack me-1"></i>Billing &amp; Payments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('portal.notifications.index', ['type' => 'discharge']) }}" class="nav-link py-1.5 px-3 {{ ($filters['type'] ?? '') === 'discharge' ? 'active bg-danger' : 'text-navy' }}">
                            <i class="bi bi-box-arrow-right me-1"></i>Discharges
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Notifications List --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <span class="fw-bold text-navy"><i class="bi bi-inbox text-teal me-2"></i>Notifications Stream ({{ $notifications->total() }} Total)</span>
                <span class="badge bg-light text-muted border font-monospace">Page {{ $notifications->currentPage() }} of {{ $notifications->lastPage() }}</span>
            </div>

            <div class="list-group list-group-flush">
                @forelse($notifications as $item)
                    <div class="list-group-item p-3 {{ $item->is_read ? '' : 'bg-light bg-opacity-75 border-start border-4 border-teal' }} transition-all">
                        <div class="d-flex flex-column flex-sm-row align-items-start gap-3">
                            {{-- Type Icon Badge --}}
                            <div class="rounded-circle bg-{{ $item->type_badge_class }}-subtle text-{{ $item->type_badge_class }} p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                <i class="bi bi-{{ $item->type_icon }} fs-5"></i>
                            </div>

                            {{-- Notification Content --}}
                            <div class="flex-grow-1">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-1 mb-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <h6 class="fw-bold text-navy mb-0">{{ $item->title }}</h6>
                                        <span class="badge bg-{{ $item->type_badge_class }} font-monospace text-uppercase" style="font-size: 0.65rem;">
                                            {{ str_replace('_', ' ', $item->type) }}
                                        </span>
                                        @if(!$item->is_read)
                                            <span class="badge bg-danger-subtle text-danger" style="font-size: 0.65rem;">New</span>
                                        @endif
                                    </div>
                                    <span class="text-muted small" title="{{ $item->created_at->format('d M Y, h:i A') }}">
                                        <i class="bi bi-clock me-1"></i>{{ $item->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                <p class="text-dark small mb-2">{{ $item->message }}</p>

                                {{-- Action Controls --}}
                                <div class="d-flex align-items-center gap-2">
                                    @if($item->action_url)
                                        <a href="{{ $item->action_url }}" class="btn btn-sm btn-outline-teal py-0 px-2 d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                            <i class="bi bi-box-arrow-up-right"></i> View Record
                                        </a>
                                    @endif

                                    @if(!$item->is_read)
                                        <form method="POST" action="{{ route('portal.notifications.read', $item->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light border py-0 px-2 text-muted" style="font-size: 0.75rem;">
                                                <i class="bi bi-check"></i> Mark as read
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small" style="font-size: 0.7rem;">
                                            <i class="bi bi-check2"></i> Read {{ $item->read_at?->diffForHumans() }}
                                        </span>
                                    @endif

                                    <form method="POST" action="{{ route('portal.notifications.destroy', $item->id) }}" class="d-inline ms-auto">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0 text-decoration-none" style="font-size: 0.75rem;" title="Dismiss notification">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-bell-slash fs-1 d-block mb-2 text-muted-hms"></i>
                        No notifications found for the selected view.
                    </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.portal>

