@props([
    'status' => 'neutral',
    'dot' => true,
    'icon' => null,
])

@php
    $classMap = [
        'success' => 'hms-badge-success',
        'warning' => 'hms-badge-warning',
        'danger' => 'hms-badge-danger',
        'info' => 'hms-badge-info',
        'teal' => 'hms-badge-teal',
        'navy' => 'hms-badge-navy',
        'neutral' => 'hms-badge-neutral',
    ];

    $badgeClass = $classMap[$status] ?? 'hms-badge-neutral';
@endphp

<span {{ $attributes->merge(['class' => 'hms-badge ' . $badgeClass]) }}>
    @if($dot)
        <span class="hms-badge-dot" aria-hidden="true"></span>
    @elseif($icon)
        <i class="bi bi-{{ $icon }}" aria-hidden="true"></i>
    @endif
    {{ $slot }}
</span>
