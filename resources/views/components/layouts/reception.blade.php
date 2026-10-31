@props([
    'title' => 'Reception & Intake Desk',
    'activeNav' => 'dashboard',
    'breadcrumbs' => [],
])

<x-layouts.portal role="reception" :title="$title" :activeNav="$activeNav" :breadcrumbs="$breadcrumbs">
    {{ $slot }}
</x-layouts.portal>
