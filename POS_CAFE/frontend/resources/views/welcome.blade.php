<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#0b0914] dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Veloura Café POS') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full font-sans antialiased bg-[#0b0914] text-slate-100 flex flex-col justify-center items-center py-12 px-4 sm:px-6 selection:bg-purple-600 selection:text-white relative overflow-hidden">
        <!-- Ambient Glows -->
        <div class="absolute -top-32 -left-32 w-80 h-80 bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-80 h-80 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-sm w-full mx-auto bg-[#151026] border border-[#281e42] rounded-3xl p-8 shadow-2xl text-center relative z-10">
            <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-[#1d1636] border border-[#37285c] flex items-center justify-center p-3 shadow-lg shadow-purple-950/40">
                <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="h-full w-auto">
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Veloura Café</h1>
            <p class="text-xs text-purple-300 font-medium mt-1">POS & Enterprise Management</p>

            <div class="mt-8 space-y-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="block w-full py-3.5 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-extrabold rounded-2xl shadow-lg shadow-purple-950/60 text-xs uppercase tracking-wider transition">
                        Go to Dashboard &rarr;
                    </a>
                @else
                    <a href="{{ route('login') }}" class="block w-full py-3.5 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-extrabold rounded-2xl shadow-lg shadow-purple-950/60 text-xs uppercase tracking-wider transition">
                        Sign In to POS Portal &rarr;
                    </a>
                @endauth
            </div>
        </div>
    </body>
</html>
