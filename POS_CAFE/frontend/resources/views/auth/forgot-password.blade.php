<x-guest-layout>
    <div class="max-w-sm w-full mx-auto bg-[#151026] border border-[#281e42] rounded-2xl shadow-xl p-8 text-slate-100">
        <div class="text-center mb-6">
            <div class="w-16 h-16 mx-auto mb-3 rounded-xl bg-[#1d1636] border border-[#37285c] flex items-center justify-center p-2.5">
                <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="h-full w-auto">
            </div>
            <h1 class="text-xl font-black text-white tracking-tight">Veloura Café</h1>
            <p class="text-xs text-purple-300/80 font-medium mt-0.5">Password Recovery</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold text-purple-300 uppercase tracking-wider mb-1.5">Email</label>
                <input id="email" class="w-full px-3.5 py-2.5 bg-[#110d22] border border-[#2d2250] rounded-xl text-xs text-white placeholder:text-purple-300/40 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" type="email" name="email" value="{{ old('email') }}" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('login') }}" class="text-xs font-semibold text-purple-400 hover:text-purple-300 hover:underline">
                    &larr; Back to Login
                </a>
                <button type="submit" class="py-2.5 px-4 bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white font-bold rounded-xl text-xs transition">
                    {{ __('Send Reset Link') }}
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
