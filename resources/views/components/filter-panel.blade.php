@props([
    'title' => 'Filter Records',
    'action' => '#',
    'resetUrl' => '#',
])

<div {{ $attributes->merge(['class' => 'hms-filter-panel']) }}>
    <form method="GET" action="{{ $action }}">
        <div class="hms-filter-header">
            <span class="hms-filter-title">
                <i class="bi bi-funnel text-teal"></i> {{ $title }}
            </span>
            <div class="d-flex align-items-center gap-2">
                @if($resetUrl)
                    <a href="{{ $resetUrl }}" class="btn btn-hms-ghost btn-hms-sm text-muted-hms">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                @endif
                <button type="submit" class="btn btn-hms-teal btn-hms-sm">
                    <i class="bi bi-check-lg"></i> Apply Filters
                </button>
            </div>
        </div>

        <div class="row g-3">
            {{ $slot }}
        </div>
    </form>
</div>
