@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'icon' => null,
    'disabled' => false,
])

@php
    $classMap = [
        'primary' => 'btn-hms-primary',
        'secondary' => 'btn-hms-teal',
        'teal' => 'btn-hms-teal',
        'outline-primary' => 'btn-hms-outline-primary',
        'outline-teal' => 'btn-hms-outline-teal',
        'ghost' => 'btn-hms-ghost',
        'danger' => 'btn btn-danger',
    ];

    $btnClass = $classMap[$variant] ?? 'btn-hms-primary';
    if ($size === 'sm') {
        $btnClass .= ' btn-hms-sm';
    } elseif ($size === 'lg') {
        $btnClass .= ' btn-lg';
    }
@endphp

@if($attributes->has('href'))
<a
    {{ $attributes->merge(['class' => $btnClass . ($disabled ? ' disabled' : '')]) }}
    @if($disabled) tabindex="-1" aria-disabled="true" @endif
>
    @if($icon)
        <i class="bi bi-{{ $icon }}" aria-hidden="true"></i>
    @endif
    {{ $slot }}
</a>
@else
<button
    type="{{ $type }}"
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => $btnClass]) }}
>
    @if($icon)
        <i class="bi bi-{{ $icon }}" aria-hidden="true"></i>
    @endif
    {{ $slot }}
</button>
@endif
