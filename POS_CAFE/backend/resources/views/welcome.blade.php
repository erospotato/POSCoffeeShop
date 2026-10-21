<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Veloura Café POS') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full font-sans antialiased bg-slate-50 text-slate-800 flex flex-col justify-center items-center py-12 px-4 sm:px-6 selection:bg-amber-500 selection:text-white">
        <div class="max-w-sm w-full mx-auto bg-white border border-slate-200/80 rounded-3xl p-8 shadow-xs text-center">
            <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="h-16 w-auto mx-auto mb-3">
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Veloura Café</h1>

            <div class="mt-8 space-y-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="block w-full py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-xs text-xs uppercase tracking-wider transition">
                        Dashboard &rarr;
                    </a>
                @else
                    <a href="{{ route('login') }}" class="block w-full py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-xs text-xs uppercase tracking-wider transition">
                        Log In &rarr;
                    </a>
                @endauth
            </div>
        </div>
    </body>
</html>
