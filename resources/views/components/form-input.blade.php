@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'hint' => null,
    'error' => null,
])

<div class="mb-3">
    @if($label)
        <label for="{{ $name }}" class="hms-form-label {{ $required ? 'required' : '' }}">
            {{ $label }}
        </label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'hms-form-control' . ($error || $errors->has($name) ? ' is-invalid' : '')]) }}
    >

    @if($hint && !$error && !$errors->has($name))
        <div class="hms-form-hint">{{ $hint }}</div>
    @endif

    @error($name)
        <div class="hms-form-error">
            <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
            {{ $message }}
        </div>
    @elseif($error)
        <div class="hms-form-error">
            <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
            {{ $error }}
        </div>
    @enderror
</div>
