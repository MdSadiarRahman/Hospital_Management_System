@props([
    'title' => 'Administrator Portal',
    'activeNav' => 'dashboard',
    'breadcrumbs' => [],
])

<x-layouts.portal role="admin" :title="$title" :activeNav="$activeNav" :breadcrumbs="$breadcrumbs">
    {{ $slot }}
</x-layouts.portal>
