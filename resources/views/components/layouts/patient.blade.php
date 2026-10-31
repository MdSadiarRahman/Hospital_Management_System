@props([
    'title' => 'Patient Self-Care Portal',
    'activeNav' => 'dashboard',
    'breadcrumbs' => [],
])

<x-layouts.portal role="patient" :title="$title" :activeNav="$activeNav" :breadcrumbs="$breadcrumbs">
    {{ $slot }}
</x-layouts.portal>
