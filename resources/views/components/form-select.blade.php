@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Please select...',
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

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'hms-form-select' . ($error || $errors->has($name) ? ' is-invalid' : '')]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @if(!empty($options))
            @foreach($options as $val => $text)
                <option value="{{ $val }}" {{ (string)old($name, $selected) === (string)$val ? 'selected' : '' }}>
                    {{ $text }}
                </option>
            @endforeach
        @endif

        {{ $slot }}
    </select>

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
