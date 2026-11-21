@props([
    'size' => 'md',
    'theme' => 'light',
    'showText' => true,
    'class' => '',
])

@php
    $dimensions = match($size) {
        'sm' => ['box' => 34, 'rx' => 10, 'title' => '0.9rem', 'sub' => '0.62rem'],
        'lg' => ['box' => 50, 'rx' => 14, 'title' => '1.25rem', 'sub' => '0.75rem'],
        default => ['box' => 42, 'rx' => 12, 'title' => '1.05rem', 'sub' => '0.68rem'],
    };

    $isDark = $theme === 'dark';
    $titleColor = $isDark ? 'text-white' : 'text-navy';
    $subColor = $isDark ? 'text-white-50' : 'text-teal';
@endphp

<div class="hms-brand-logo d-inline-flex align-items-center gap-2.5 text-decoration-none {{ $class }}">
    <!-- Dynamic SVG Icon Badge -->
    <div class="hms-brand-icon-box flex-shrink-0" style="width: {{ $dimensions['box'] }}px; height: {{ $dimensions['box'] }}px;">
        <svg viewBox="0 0 48 48" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="brandLogoGrad-{{ $size }}-{{ $theme }}" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#0aa4b5" />
                    <stop offset="60%" stop-color="#087F8C" />
                    <stop offset="100%" stop-color="#123B5D" />
                </linearGradient>
            </defs>
            <rect x="1.5" y="1.5" width="45" height="45" rx="{{ $dimensions['rx'] }}" fill="url(#brandLogoGrad-{{ $size }}-{{ $theme }})" />
            <rect x="2" y="2" width="44" height="44" rx="{{ $dimensions['rx'] - 0.5 }}" stroke="rgba(255,255,255,0.3)" stroke-width="1.2" />
            <!-- Soft White Cross Base -->
            <rect x="20.5" y="10" width="7" height="28" rx="3.5" fill="#ffffff" fill-opacity="0.22" />
            <rect x="10" y="20.5" width="28" height="7" rx="3.5" fill="#ffffff" fill-opacity="0.22" />
            <!-- Solid Cross -->
            <path d="M 21.5 12 C 21.5 10.9 22.4 10 23.5 10 L 24.5 10 C 25.6 10 26.5 10.9 26.5 12 L 26.5 21.5 L 36 21.5 C 37.1 21.5 38 22.4 38 23.5 L 38 24.5 C 38 25.6 37.1 26.5 36 26.5 L 26.5 26.5 L 26.5 36 C 26.5 37.1 25.6 38 24.5 38 L 23.5 38 C 22.4 38 21.5 37.1 21.5 36 L 21.5 26.5 L 12 26.5 C 10.9 26.5 10 25.6 10 24.5 L 10 23.5 C 10 22.4 10.9 21.5 12 21.5 L 21.5 21.5 Z" fill="#ffffff" />
            <!-- ECG Pulse Wave -->
            <path d="M 10 24 L 17 24 L 20 18 L 23 29 L 26 15 L 29 27 L 31 22 L 33 24 L 38 24" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="34" cy="15" r="2.2" fill="#5eead4" />
        </svg>
    </div>

    @if($showText)
        <div class="hms-brand-text d-flex flex-column lh-1 overflow-hidden">
            <span class="fw-bold {{ $titleColor }} text-truncate" style="font-size: {{ $dimensions['title'] }}; letter-spacing: -0.02em;">
                Meghna Clinic
            </span>
            <span class="{{ $subColor }} text-truncate text-uppercase fw-semibold mt-1" style="font-size: {{ $dimensions['sub'] }}; letter-spacing: 0.06em;">
                &amp; Nursing Home, Dinajpur
            </span>
        </div>
    @endif
</div>
