<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Veloura Café POS') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full font-sans antialiased bg-slate-950 text-slate-100 flex flex-col justify-center items-center py-10 px-4 sm:px-6 relative selection:bg-amber-500 selection:text-white">
        <div class="fixed inset-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 -right-32 w-96 h-96 bg-amber-600/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 left-1/3 w-96 h-96 bg-amber-700/10 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-md w-full mx-auto bg-slate-900 border border-slate-800 rounded-3xl p-8 sm:p-10 shadow-2xl text-center relative z-10">
            <div class="w-20 h-20 mx-auto rounded-3xl bg-slate-950 border border-amber-500/30 p-3 flex items-center justify-center shadow-xl mb-5">
                <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="w-full h-full object-contain">
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Veloura Café</h1>
            <p class="text-xs text-amber-400 font-semibold tracking-wide uppercase mt-1">POS & Management System</p>
            <p class="text-xs text-slate-400 mt-3 leading-relaxed">
                Specialty handcrafted coffee, streamlined cashier register, kitchen display system, and automated inventory.
            </p>

            <div class="mt-8 space-y-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="block w-full py-3.5 px-4 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-extrabold rounded-xl shadow-lg shadow-amber-500/20 text-xs uppercase tracking-wider transition">
                        Open Dashboard &rarr;
                    </a>
                @else
                    <a href="{{ route('login') }}" class="block w-full py-3.5 px-4 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-extrabold rounded-xl shadow-lg shadow-amber-500/20 text-xs uppercase tracking-wider transition">
                        Employee Sign In &rarr;
                    </a>
                @endauth
            </div>
        </div>
    </body>
</html>
