<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ theme: localStorage.getItem('theme') || 'dark' }"
      :class="{'dark': theme === 'dark'}"
      x-init="$watch('theme', val => localStorage.setItem('theme', val))">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

        <title>{{ config('app.name', 'IntalenTI BI') }}</title>

        <!-- Fonts (Ventriloc) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@400&display=swap" rel="stylesheet">

        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="/Globo_INTALENTI.ico">

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
        <!-- Alpine.js -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-main bg-canvas min-h-screen">
        @yield('content')
        
        @stack('scripts')
    </body>
</html>
