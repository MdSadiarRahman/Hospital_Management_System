@props([
    'title' => 'Doctor Clinical Portal',
    'activeNav' => 'dashboard',
    'breadcrumbs' => [],
])

<x-layouts.portal role="doctor" :title="$title" :activeNav="$activeNav" :breadcrumbs="$breadcrumbs">
    {{ $slot }}
</x-layouts.portal>
