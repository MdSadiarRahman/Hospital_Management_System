@props([
    'name' => null,
    'message' => null,
])

@if($name && $errors->has($name))
    <div {{ $attributes->merge(['class' => 'hms-form-error']) }}>
        <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
        <span>{{ $errors->first($name) }}</span>
    </div>
@elseif($message)
    <div {{ $attributes->merge(['class' => 'hms-form-error']) }}>
        <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
        <span>{{ $message }}</span>
    </div>
@endif
