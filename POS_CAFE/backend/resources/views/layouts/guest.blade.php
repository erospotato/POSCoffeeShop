<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#0b0914] dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Veloura Café POS') }} - Staff Portal</title>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full font-sans antialiased bg-[#0b0914] text-slate-100 flex flex-col justify-center items-center py-12 px-4 sm:px-6 selection:bg-purple-600 selection:text-white relative overflow-hidden">
        <!-- Ambient Purple Glows -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-purple-600/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full flex flex-col items-center justify-center relative z-10">
            {{ $slot }}
        </div>
    </body>
</html>
