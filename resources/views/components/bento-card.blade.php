@props([
    'span' => '4',
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'variant' => 'default',
    'kpiValue' => null,
    'kpiLabel' => null,
    'kpiIcon' => null,
    'kpiMeta' => null,
])

@php
    $spanClass = 'bento-col-' . $span;
@endphp

@if($variant === 'kpi' || $kpiValue !== null)
    <div class="{{ $spanClass }}">
        <div {{ $attributes->merge(['class' => 'kpi-card ' . ($variant !== 'kpi' ? $variant : '')]) }}>
            <div class="kpi-header">
                <span class="kpi-label">{{ $kpiLabel ?? $title }}</span>
                @if($kpiIcon || $icon)
                    <div class="kpi-icon-box {{ $variant }}">
                        <i class="bi bi-{{ $kpiIcon ?? $icon }}" aria-hidden="true"></i>
                    </div>
                @endif
            </div>
            <div class="kpi-value">{{ $kpiValue }}</div>
            @if($kpiMeta || $subtitle)
                <div class="kpi-meta">
                    {{ $kpiMeta ?? $subtitle }}
                </div>
            @endif
            {{ $slot }}
        </div>
    </div>
@else
    <div class="{{ $spanClass }}">
        <div {{ $attributes->merge(['class' => 'hms-card h-100']) }}>
            @if($title || isset($action))
                <div class="hms-card-header">
                    <div>
                        @if($title)
                            <h3 class="hms-card-title">
                                @if($icon)
                                    <i class="bi bi-{{ $icon }} text-teal" aria-hidden="true"></i>
                                @endif
                                {{ $title }}
                            </h3>
                        @endif
                        @if($subtitle)
                            <p class="hms-card-subtitle">{{ $subtitle }}</p>
                        @endif
                    </div>
                    @if(isset($action))
                        <div class="hms-card-action">
                            {{ $action }}
                        </div>
                    @endif
                </div>
            @endif

            <div class="hms-card-body">
                {{ $slot }}
            </div>

            @if(isset($footer))
                <div class="hms-card-footer">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
@endif
