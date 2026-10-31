@props([
    'icon' => 'inbox',
    'title' => 'No records found',
    'description' => 'There are no active records in this section right now.',
    'actionText' => null,
    'actionUrl' => null,
    'actionIcon' => 'plus-lg',
])

<div {{ $attributes->merge(['class' => 'hms-empty-state']) }}>
    <div class="hms-empty-icon-wrap">
        <i class="bi bi-{{ $icon }}" aria-hidden="true"></i>
    </div>

    <h3 class="hms-empty-title">{{ $title }}</h3>
    <p class="hms-empty-description">{{ $description }}</p>

    @if($actionText && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-hms-teal btn-hms-sm">
            <i class="bi bi-{{ $actionIcon }}"></i> {{ $actionText }}
        </a>
    @elseif(isset($action))
        {{ $action }}
    @endif

    {{ $slot }}
</div>
