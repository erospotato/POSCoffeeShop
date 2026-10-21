<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Veloura Café POS') }} - Staff Portal</title>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full font-sans antialiased bg-slate-950 text-slate-100 flex flex-col justify-center items-center py-10 px-4 sm:px-6 lg:px-8 relative selection:bg-amber-500 selection:text-white">
        <!-- Ambient Warm Lighting Backdrop -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 -right-32 w-96 h-96 bg-amber-600/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 left-1/3 w-96 h-96 bg-amber-700/10 rounded-full blur-3xl"></div>
        </div>

        <div class="w-full relative z-10 flex flex-col items-center justify-center">
            {{ $slot }}
        </div>
    </body>
</html>
