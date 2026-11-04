<x-guest-layout>
    <div class="max-w-sm w-full mx-auto bg-[#151026] border border-[#281e42] rounded-3xl shadow-2xl p-8 text-slate-100">
        <div class="text-center mb-6">
            <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-[#1d1636] border border-[#37285c] flex items-center justify-center p-2.5 shadow-lg shadow-purple-950/40">
                <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="h-full w-auto">
            </div>
            <h1 class="text-xl font-black text-white tracking-tight">Veloura Café</h1>
            <p class="text-xs text-purple-300/80 font-medium mt-0.5">Reset Account Password</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-bold text-purple-300 uppercase tracking-wider mb-1.5">Email</label>
                <input id="email" class="w-full px-3.5 py-2.5 bg-[#110d22] border border-[#2d2250] rounded-xl text-xs text-white placeholder:text-purple-300/40 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-bold text-purple-300 uppercase tracking-wider mb-1.5">New Password</label>
                <input id="password" class="w-full px-3.5 py-2.5 bg-[#110d22] border border-[#2d2250] rounded-xl text-xs text-white placeholder:text-purple-300/40 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition font-mono" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-purple-300 uppercase tracking-wider mb-1.5">Confirm Password</label>
                <input id="password_confirmation" class="w-full px-3.5 py-2.5 bg-[#110d22] border border-[#2d2250] rounded-xl text-xs text-white placeholder:text-purple-300/40 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition font-mono" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-bold rounded-xl shadow-lg shadow-purple-950/60 text-xs transition">
                    {{ __('Reset Password') }}
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
