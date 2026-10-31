@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'rows' => 3,
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

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'hms-form-control' . ($error || $errors->has($name) ? ' is-invalid' : '')]) }}
    >{{ old($name, $value) }}</textarea>

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
