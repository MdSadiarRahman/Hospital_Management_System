@props([
    'icon',
    'title' => null,
    'variant' => 'default',
    'type' => 'button',
    'disabled' => false,
])

<button
    type="{{ $type }}"
    {{ $disabled ? 'disabled' : '' }}
    @if($title) title="{{ $title }}" aria-label="{{ $title }}" @endif
    {{ $attributes->merge(['class' => 'btn-hms-icon ' . ($variant !== 'default' ? $variant : '')]) }}
>
    <i class="bi bi-{{ $icon }}" aria-hidden="true"></i>
</button>
