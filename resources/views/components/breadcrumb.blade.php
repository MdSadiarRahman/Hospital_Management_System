@props([
    'items' => [],
])

<nav aria-label="breadcrumb">
    <ol {{ $attributes->merge(['class' => 'hms-breadcrumb']) }}>
        <li class="hms-breadcrumb-item">
            <a href="{{ route('home') }}">
                <i class="bi bi-house-door" aria-hidden="true"></i>
                <span class="visually-hidden">Home</span>
            </a>
        </li>
        @foreach($items as $label => $url)
            @if(is_numeric($label))
                <li class="hms-breadcrumb-item active" aria-current="page">{{ $url }}</li>
            @elseif($loop->last || is_null($url))
                <li class="hms-breadcrumb-item active" aria-current="page">{{ $label }}</li>
            @else
                <li class="hms-breadcrumb-item"><a href="{{ $url }}">{{ $label }}</a></li>
            @endif
        @endforeach
    </ol>
</nav>
