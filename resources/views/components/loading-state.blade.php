@props([
    'type' => 'skeleton',
    'rows' => 3,
    'message' => 'Loading clinical data...',
])

@if($type === 'spinner')
    <div {{ $attributes->merge(['class' => 'd-flex flex-column align-items-center justify-content-center p-4']) }}>
        <div class="hms-spinner mb-2" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        @if($message)
            <span class="text-muted-hms" style="font-size: 0.8rem;">{{ $message }}</span>
        @endif
    </div>
@elseif($type === 'skeleton-table')
    <div {{ $attributes->merge(['class' => 'p-3']) }}>
        <div class="hms-skeleton hms-skeleton-title mb-3" style="width: 30%;"></div>
        @for($i = 0; $i < $rows; $i++)
            <div class="d-flex gap-3 mb-2">
                <div class="hms-skeleton hms-skeleton-text" style="width: 20%;"></div>
                <div class="hms-skeleton hms-skeleton-text" style="width: 35%;"></div>
                <div class="hms-skeleton hms-skeleton-text" style="width: 25%;"></div>
                <div class="hms-skeleton hms-skeleton-text" style="width: 20%;"></div>
            </div>
        @endfor
    </div>
@else
    <div {{ $attributes->merge(['class' => 'p-3']) }}>
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="hms-skeleton hms-skeleton-circle"></div>
            <div class="flex-grow-1">
                <div class="hms-skeleton hms-skeleton-title"></div>
                <div class="hms-skeleton hms-skeleton-text" style="width: 40%;"></div>
            </div>
        </div>
        @for($i = 0; $i < $rows; $i++)
            <div class="hms-skeleton hms-skeleton-text" style="width: {{ 100 - ($i * 15) }}%;"></div>
        @endfor
    </div>
@endif
