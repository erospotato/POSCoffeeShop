<x-guest-layout>
    <div class="max-w-md w-full mx-auto bg-white rounded-3xl shadow-2xl p-8 sm:p-10 border border-slate-100 text-slate-900">
        <div class="text-center mb-6">
            <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="h-14 w-auto mx-auto mb-2">
            <h2 class="text-xl font-black text-slate-900">Veloura Café</h2>
            <p class="text-xs text-slate-500">Security Confirmation</p>
        </div>

        <div class="mb-4 text-xs text-slate-600 leading-relaxed">
            {{ __('This is a secure area of the terminal. Please confirm your password before continuing.') }}
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
            @csrf

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password</label>
                <input id="password" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition font-mono" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold rounded-xl shadow-md text-xs transition">
                    {{ __('Confirm Password') }}
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
