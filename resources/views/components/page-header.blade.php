@props([
    'title',
    'subtitle' => null,
    'icon' => null,
    'breadcrumbs' => [],
])

<div {{ $attributes->merge(['class' => 'hms-page-header']) }}>
    <div class="hms-page-title-box">
        @if(!empty($breadcrumbs))
            <x-breadcrumb :items="$breadcrumbs" />
        @endif

        <h1 class="hms-page-title">
            @if($icon)
                <i class="bi bi-{{ $icon }} text-teal" aria-hidden="true"></i>
            @endif
            {{ $title }}
        </h1>

        @if($subtitle)
            <p class="hms-page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @if(isset($actions) || $slot->isNotEmpty())
        <div class="d-flex align-items-center gap-2">
            {{ $actions ?? $slot }}
        </div>
    @endif
</div>
