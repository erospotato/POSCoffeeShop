<x-guest-layout>
    <div class="max-w-sm w-full mx-auto bg-white border border-slate-200/80 rounded-3xl shadow-xs p-8 sm:p-9 text-slate-800">
        <!-- Brand Logo & Name -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="h-16 w-auto mx-auto mb-2.5">
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Veloura Café</h1>
        </div>

        <!-- Session Status Alert -->
        @if (session('status'))
            <div class="mb-5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center">
                <svg class="w-4 h-4 text-emerald-600 mr-2 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       placeholder="name@cafe.com"
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                @if($errors->has('email'))
                    <p class="text-rose-600 text-xs mt-1.5 font-medium flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $errors->first('email') }}
                    </p>
                @endif
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-[11px] font-semibold text-amber-600 hover:text-amber-700 hover:underline">
                            Forgot?
                        </a>
                    @endif
                </div>
                <div class="relative">
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           placeholder="••••••••"
                           class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition font-mono">
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none" title="Toggle password visibility">
                        <svg id="eyeIconOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg id="eyeIconClosed" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                    </button>
                </div>
                @if($errors->has('password'))
                    <p class="text-rose-600 text-xs mt-1.5 font-medium flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $errors->first('password') }}
                    </p>
                @endif
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-amber-500 focus:ring-amber-400 cursor-pointer">
                    <span class="ms-2 text-xs text-slate-600 select-none">Remember me</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" id="btnSignInSubmit" class="w-full py-3 px-4 bg-slate-900 hover:bg-slate-800 active:scale-[0.99] text-white font-bold rounded-xl shadow-xs transition text-xs uppercase tracking-wider">
                    Log In
                </button>
            </div>
        </form>

        <!-- Quick Demo Role Switcher -->
        <div class="mt-6 pt-5 border-t border-slate-100">
            <div class="grid grid-cols-4 gap-1.5">
                <button type="button" onclick="fillCredentials('admin@cafe.com', 'password')" class="py-1.5 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition text-center text-[11px]">
                    Admin
                </button>
                <button type="button" onclick="fillCredentials('manager@cafe.com', 'password')" class="py-1.5 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition text-center text-[11px]">
                    Manager
                </button>
                <button type="button" onclick="fillCredentials('cashier@cafe.com', 'password')" class="py-1.5 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition text-center text-[11px]">
                    Cashier
                </button>
                <button type="button" onclick="fillCredentials('barista@cafe.com', 'password')" class="py-1.5 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition text-center text-[11px]">
                    Barista
                </button>
            </div>
        </div>
    </div>

    <!-- Client-side script for quick credentials & password reveal -->
    <script>
        function fillCredentials(email, password) {
            const emailInput = document.getElementById('email');
            const passInput = document.getElementById('password');

            emailInput.value = email;
            passInput.value = password;

            // Flash effect
            emailInput.classList.add('ring-2', 'ring-amber-400');
            passInput.classList.add('ring-2', 'ring-amber-400');

            setTimeout(() => {
                emailInput.classList.remove('ring-2', 'ring-amber-400');
                passInput.classList.remove('ring-2', 'ring-amber-400');
            }, 400);

            document.getElementById('btnSignInSubmit')?.focus();
        }

        function togglePasswordVisibility() {
            const input = document.getElementById('password');
            const openIcon = document.getElementById('eyeIconOpen');
            const closedIcon = document.getElementById('eyeIconClosed');

            if (input.type === 'password') {
                input.type = 'text';
                openIcon.classList.add('hidden');
                closedIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                openIcon.classList.remove('hidden');
                closedIcon.classList.add('hidden');
            }
        }
    </script>
</x-guest-layout>
