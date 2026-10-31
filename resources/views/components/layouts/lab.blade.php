@props([
    'title' => 'Diagnostic Laboratory Portal',
    'activeNav' => 'dashboard',
    'breadcrumbs' => [],
])

<x-layouts.portal role="lab" :title="$title" :activeNav="$activeNav" :breadcrumbs="$breadcrumbs">
    {{ $slot }}
</x-layouts.portal>
