<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#f9fbf2] text-[#130e30]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'SDR JARVIS') }}</title>

    <!-- Favicon Oficial -->
    <link rel="icon" type="image/x-icon" href="{{ asset('img/logo.ico') }}">
    <link rel="shortcut icon" href="{{ asset('img/logo.ico') }}">

    <!-- Google Fonts: Hedvig Letters Serif + Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hedvig+Letters+Serif:opsz@12..24&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f9fbf2;
            color: #130e30;
        }
        .font-serif-brand {
            font-family: 'Hedvig Letters Serif', ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;
            letter-spacing: -0.01em;
        }
        /* Custom scrollbar matching soft meadow */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #eff2e5;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd3be;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #aab599;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="h-full bg-[#f9fbf2] text-[#130e30] antialiased selection:bg-[#ffe228] selection:text-[#130e30] overflow-hidden">
    @inertia
</body>
</html>
