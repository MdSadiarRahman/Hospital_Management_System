@props([
    'name' => 'search',
    'id' => null,
    'value' => null,
    'placeholder' => 'Search patients, receipts, test records...',
])

@php
    $inputId = $id ?? $name;
@endphp

<div class="hms-search-container">
    <i class="bi bi-search hms-search-icon" aria-hidden="true"></i>
    <input
        type="search"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'hms-search-input']) }}
    >
</div>
