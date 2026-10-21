<x-guest-layout>
    <div class="max-w-sm w-full mx-auto bg-white border border-slate-200/80 rounded-3xl shadow-xs p-8 text-slate-800">
        <div class="text-center mb-6">
            <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="h-16 w-auto mx-auto mb-2.5">
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Veloura Café</h1>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
            @csrf

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                <input id="password" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition font-mono" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-xs text-xs transition">
                    {{ __('Confirm') }}
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
