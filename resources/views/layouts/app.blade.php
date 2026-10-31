<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Hospital Management System' }} — Meghna Clinic & Nursing Home</title>
    
    <!-- Design System Assets via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <div id="app" class="min-vh-100 d-flex flex-column">
        {{ $slot }}
    </div>

    @stack('scripts')
</body>
</html>
