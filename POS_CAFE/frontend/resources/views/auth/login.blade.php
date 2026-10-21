<x-guest-layout>
    <div class="max-w-md w-full mx-auto bg-slate-900/95 border border-slate-800 rounded-3xl shadow-2xl p-8 sm:p-10 relative text-slate-100">
        <!-- Logo & Brand Header (No Descriptions) -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-950 border border-amber-500/30 p-2.5 flex items-center justify-center shadow-lg mb-3">
                <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="w-full h-full object-contain">
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Veloura Café</h1>
        </div>

        <!-- Session Status Alert -->
        @if (session('status'))
            <div class="mb-5 p-3 rounded-xl bg-emerald-950/60 border border-emerald-800 text-emerald-300 text-xs font-semibold flex items-center">
                <svg class="w-4 h-4 text-emerald-400 mr-2 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                    </div>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           placeholder="name@cafe.com"
                           class="w-full pl-10 pr-4 py-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs font-medium text-white placeholder:text-slate-600 focus:bg-slate-950 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                </div>
                @if($errors->has('email'))
                    <p class="text-rose-400 text-xs mt-1.5 font-medium flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $errors->first('email') }}
                    </p>
                @endif
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Password</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-[11px] font-semibold text-amber-400 hover:text-amber-300 hover:underline">
                            Forgot password?
                        </a>
                    @endif
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           placeholder="••••••••"
                           class="w-full pl-10 pr-10 py-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs font-medium text-white placeholder:text-slate-600 focus:bg-slate-950 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition font-mono">
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 focus:outline-none" title="Toggle password visibility">
                        <svg id="eyeIconOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg id="eyeIconClosed" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                    </button>
                </div>
                @if($errors->has('password'))
                    <p class="text-rose-400 text-xs mt-1.5 font-medium flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $errors->first('password') }}
                    </p>
                @endif
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-amber-500 focus:ring-amber-400 cursor-pointer">
                    <span class="ms-2 text-xs text-slate-400 font-medium select-none">Remember me</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" id="btnSignInSubmit" class="w-full py-3.5 px-4 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 active:scale-[0.99] text-white font-extrabold rounded-xl shadow-lg shadow-amber-500/25 transition text-xs uppercase tracking-wider flex items-center justify-center space-x-2">
                    <span>Log In</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
        </form>

        <!-- Quick Demo Role Switcher -->
        <div class="mt-8 pt-5 border-t border-slate-800">
            <div class="grid grid-cols-4 gap-1.5">
                <button type="button" onclick="fillCredentials('admin@cafe.com', 'password')" class="py-1.5 px-2 rounded-lg bg-slate-800/80 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-bold transition text-center border border-slate-700/60 text-[11px]">
                    Admin
                </button>
                <button type="button" onclick="fillCredentials('manager@cafe.com', 'password')" class="py-1.5 px-2 rounded-lg bg-slate-800/80 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-bold transition text-center border border-slate-700/60 text-[11px]">
                    Manager
                </button>
                <button type="button" onclick="fillCredentials('cashier@cafe.com', 'password')" class="py-1.5 px-2 rounded-lg bg-slate-800/80 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-bold transition text-center border border-slate-700/60 text-[11px]">
                    Cashier
                </button>
                <button type="button" onclick="fillCredentials('barista@cafe.com', 'password')" class="py-1.5 px-2 rounded-lg bg-slate-800/80 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-bold transition text-center border border-slate-700/60 text-[11px]">
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
            }, 500);

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
