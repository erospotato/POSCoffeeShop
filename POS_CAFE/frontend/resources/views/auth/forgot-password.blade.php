<x-guest-layout>
    <div class="max-w-md w-full mx-auto bg-white rounded-3xl shadow-2xl p-8 border border-slate-100 text-slate-900">
        <div class="text-center mb-6">
            <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="h-14 w-auto mx-auto mb-2">
            <h2 class="text-lg font-black text-slate-900">Veloura Café</h2>
            <p class="text-xs text-slate-500">Password Recovery</p>
        </div>

        <div class="mb-4 text-xs text-slate-600 leading-relaxed">
            {{ __('Forgot your password? Enter your registered email address and we will email you a password reset link.') }}
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address</label>
                <input id="email" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition" type="email" name="email" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('login') }}" class="text-xs font-semibold text-amber-600 hover:underline">
                    &larr; Back to login
                </a>
                <button type="submit" class="py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-md text-xs transition">
                    {{ __('Email Reset Link') }}
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
