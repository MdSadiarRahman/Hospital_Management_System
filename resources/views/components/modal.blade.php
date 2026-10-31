@props([
    'id',
    'title' => null,
    'icon' => null,
    'size' => 'md',
])

@php
    $modalSizeClass = match($size) {
        'sm' => 'modal-sm',
        'lg' => 'modal-lg',
        'xl' => 'modal-xl',
        default => '',
    };
@endphp

<div
    class="modal fade"
    id="{{ $id }}"
    tabindex="-1"
    aria-labelledby="{{ $id }}Label"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered {{ $modalSizeClass }}">
        <div class="modal-content hms-modal">
            @if($title)
                <div class="modal-header hms-modal-header">
                    <h5 class="modal-title hms-modal-title" id="{{ $id }}Label">
                        @if($icon)
                            <i class="bi bi-{{ $icon }} text-teal me-2" aria-hidden="true"></i>
                        @endif
                        {{ $title }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            @endif

            <div class="modal-body hms-modal-body">
                {{ $slot }}
            </div>

            @if(isset($footer))
                <div class="modal-footer hms-modal-footer">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
