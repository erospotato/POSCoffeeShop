<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Artisan Café POS') }} - {{ $title ?? 'Management System' }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts compiled with Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50">
    <div class="min-h-screen flex">
        <!-- Sidebar Navigation -->
        @include('layouts.navigation')

        <!-- Main Wrapper -->
        <div class="flex-1 ml-64 flex flex-col min-h-screen">
            <!-- Top Header Bar -->
            <header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-20 flex items-center justify-between px-8 shadow-sm">
                <div class="flex items-center space-x-4">
                    <h2 class="text-lg font-bold text-slate-900 tracking-tight">
                        {{ $header ?? 'Artisan Café' }}
                    </h2>
                </div>

                <!-- Right Quick Controls -->
                <div class="flex items-center space-x-3 text-xs">
                    @php
                        $currentUser = Auth::user();
                    @endphp

                    @if($currentUser && ($currentUser->isAdmin() || $currentUser->isManager() || $currentUser->isCashier()))
                        <a href="{{ route('pos.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold shadow-sm transition">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            Launch POS
                        </a>
                    @endif

                    @if($currentUser && ($currentUser->isAdmin() || $currentUser->isManager() || $currentUser->isBarista()))
                        <a href="{{ route('kds.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold shadow-sm transition">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Kitchen KDS
                        </a>
                    @endif

                    <div class="h-6 w-px bg-slate-200"></div>

                    <!-- Role Pill -->
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $currentUser?->role?->name ?? 'Staff' }}
                    </span>
                </div>
            </header>

            <!-- Alerts Banner -->
            <div class="px-8 pt-4">
                @if(session('success'))
                    <div class="mb-4 flex items-center p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm shadow-sm">
                        <svg class="w-5 h-5 mr-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-4 flex items-center p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm shadow-sm">
                        <svg class="w-5 h-5 mr-3 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span class="font-medium">{{ session('warning') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-sm">
                        <div class="flex items-center mb-1">
                            <svg class="w-5 h-5 mr-2 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            <span class="font-semibold">Please check the following errors:</span>
                        </div>
                        <ul class="list-disc list-inside ml-6 text-xs space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Page Body -->
            <main class="flex-1 px-8 py-4">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="h-12 border-t border-slate-200 px-8 flex items-center justify-between text-xs text-slate-500 bg-white">
                <div>&copy; {{ date('Y') }} Artisan Café Management System. University Project.</div>
                <div class="flex space-x-4">
                    <span>Tax: {{ \App\Models\SystemSetting::get('tax_rate', '10') }}% ({{ \App\Models\SystemSetting::get('tax_mode', 'exclusive') }})</span>
                    <span>•</span>
                    <span>System Time: {{ now()->format('M d, Y H:i') }}</span>
                </div>
            </footer>
        </div>
    </div>
</body>
</html>
