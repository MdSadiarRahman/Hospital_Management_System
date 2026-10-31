@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'hms-card']) }}>
    @if($title || isset($action))
        <div class="hms-card-header">
            <div>
                @if($title)
                    <h3 class="hms-card-title">
                        @if($icon)
                            <i class="bi bi-{{ $icon }} text-teal" aria-hidden="true"></i>
                        @endif
                        {{ $title }}
                    </h3>
                @endif
                @if($subtitle)
                    <p class="hms-card-subtitle">{{ $subtitle }}</p>
                @endif
            </div>
            @if(isset($action))
                <div class="hms-card-action">
                    {{ $action }}
                </div>
            @endif
        </div>
    @endif

    <div class="hms-card-body">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="hms-card-footer">
            {{ $footer }}
        </div>
    @endif
</div>
