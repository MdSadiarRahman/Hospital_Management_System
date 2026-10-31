@props([
    'type' => 'info',
    'dismissible' => false,
])

@php
    $iconMap = [
        'success' => 'check-circle-fill',
        'warning' => 'exclamation-triangle-fill',
        'danger' => 'exclamation-octagon-fill',
        'info' => 'info-circle-fill',
    ];

    $icon = $iconMap[$type] ?? 'info-circle-fill';
@endphp

<div {{ $attributes->merge(['class' => 'hms-alert hms-alert-' . $type . ($dismissible ? ' alert-dismissible' : '')]) }} role="alert">
    <i class="bi bi-{{ $icon }} hms-alert-icon" aria-hidden="true"></i>
    <div class="flex-grow-1">
        {{ $slot }}
    </div>
    @if($dismissible)
        <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
