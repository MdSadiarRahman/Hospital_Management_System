@props([
    'label' => null,
    'title' => null,
    'value',
    'icon' => null,
    'variant' => 'primary',
    'color' => null,
    'meta' => null,
    'subtitle' => null,
    'trend' => null,
    'trendValue' => null,
    'trendUp' => null,
])

@php
    $displayLabel = $label ?? $title ?? '';
    $effectiveVariant = $color ?? $variant ?? 'primary';
    $effectiveMeta = $meta ?? $subtitle ?? null;
    $effectiveTrendValue = $trendValue ?? (is_string($trend) && !in_array($trend, ['up', 'down']) ? $trend : null);
    $effectiveTrend = ($trendUp === true || $trendUp === 'true' || $trend === 'up') ? 'up' : (($trendUp === false || $trendUp === 'false' || $trend === 'down') ? 'down' : null);
@endphp

<div {{ $attributes->merge(['class' => 'kpi-card ' . $effectiveVariant]) }}>
    <div class="kpi-header">
        <span class="kpi-label">{{ $displayLabel }}</span>
        @if($icon)
            <div class="kpi-icon-box {{ $effectiveVariant }}">
                <i class="bi bi-{{ $icon }}" aria-hidden="true"></i>
            </div>
        @endif
    </div>

    <div class="kpi-value">{{ $value }}</div>

    @if($effectiveMeta || $effectiveTrendValue)
        <div class="kpi-meta">
            @if($effectiveTrend === 'up')
                <span class="text-success fw-semibold"><i class="bi bi-arrow-up-right me-0.5"></i>{{ $effectiveTrendValue }}</span>
            @elseif($effectiveTrend === 'down')
                <span class="text-danger fw-semibold"><i class="bi bi-arrow-down-right me-0.5"></i>{{ $effectiveTrendValue }}</span>
            @elseif($effectiveTrendValue)
                <span class="text-teal fw-semibold">{{ $effectiveTrendValue }}</span>
            @endif

            @if($effectiveMeta)
                <span>{{ $effectiveMeta }}</span>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
