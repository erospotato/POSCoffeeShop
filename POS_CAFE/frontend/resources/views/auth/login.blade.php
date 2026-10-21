<x-guest-layout>
    <div class="max-w-4xl w-full mx-auto bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-12">
        <!-- Left Brand Banner (Eye-catching Café Presentation) -->
        <div class="md:col-span-5 bg-gradient-to-br from-slate-900 via-slate-900 to-amber-950/40 p-8 sm:p-10 flex flex-col justify-between border-b md:border-b-0 md:border-r border-slate-800 relative">
            <div>
                <!-- Brand Logo & Name -->
                <div class="flex items-center space-x-3.5 mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-slate-950/90 border border-amber-500/30 p-2 flex items-center justify-center shadow-lg">
                        <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h1 class="text-xl font-black text-white tracking-tight">Veloura Café</h1>
                        <p class="text-xs text-amber-400 font-semibold tracking-wide">POS & Management</p>
                    </div>
                </div>

                <p class="text-xs text-slate-300 leading-relaxed">
                    Unified terminal for handcrafted specialty coffee. Fast point of sale, live barista kitchen queue, automated inventory deductions, and customer loyalty rewards.
                </p>

                <!-- Key Capability Badges -->
                <div class="mt-6 space-y-2.5">
                    <div class="flex items-center space-x-2.5 text-xs text-slate-300 bg-slate-800/50 px-3 py-2 rounded-xl border border-slate-800">
                        <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Fast Touchscreen POS & Orders</span>
                    </div>
                    <div class="flex items-center space-x-2.5 text-xs text-slate-300 bg-slate-800/50 px-3 py-2 rounded-xl border border-slate-800">
                        <svg class="w-4 h-4 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Real-time Barista Kitchen Queue</span>
                    </div>
                    <div class="flex items-center space-x-2.5 text-xs text-slate-300 bg-slate-800/50 px-3 py-2 rounded-xl border border-slate-800">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>Inventory & Smart Recipe Recipes</span>
                    </div>
                </div>
            </div>

            <!-- Quick Demo Role Switcher -->
            <div class="mt-8 pt-6 border-t border-slate-800/80">
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block mb-2.5">Quick Demo Logins (Click to autofill):</span>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" onclick="fillCredentials('admin@cafe.com', 'password')" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-slate-200 font-semibold transition text-left border border-slate-700/60 shadow-xs">
                        <span class="block text-[11px] font-black">Admin</span>
                        <span class="text-[9px] opacity-75 font-mono">admin@cafe.com</span>
                    </button>
                    <button type="button" onclick="fillCredentials('manager@cafe.com', 'password')" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-slate-200 font-semibold transition text-left border border-slate-700/60 shadow-xs">
                        <span class="block text-[11px] font-black">Manager</span>
                        <span class="text-[9px] opacity-75 font-mono">manager@cafe.com</span>
                    </button>
                    <button type="button" onclick="fillCredentials('cashier@cafe.com', 'password')" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-slate-200 font-semibold transition text-left border border-slate-700/60 shadow-xs">
                        <span class="block text-[11px] font-black">Cashier</span>
                        <span class="text-[9px] opacity-75 font-mono">cashier@cafe.com</span>
                    </button>
                    <button type="button" onclick="fillCredentials('barista@cafe.com', 'password')" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-slate-200 font-semibold transition text-left border border-slate-700/60 shadow-xs">
                        <span class="block text-[11px] font-black">Barista</span>
                        <span class="text-[9px] opacity-75 font-mono">barista@cafe.com</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Side: Clean & Elegant Sign In Form -->
        <div class="md:col-span-7 bg-white text-slate-900 p-8 sm:p-12 flex flex-col justify-between">
            <div>
                <!-- Mobile Only Brand Header -->
                <div class="md:hidden flex items-center space-x-3 mb-6 pb-4 border-b border-slate-100">
                    <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="h-10 w-auto">
                    <div>
                        <h2 class="text-base font-black text-slate-900 leading-tight">Veloura Café</h2>
                        <span class="text-[10px] text-amber-600 font-bold uppercase tracking-wider">Employee Portal</span>
                    </div>
                </div>

                <div class="mb-6">
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Staff Sign In</h2>
                    <p class="text-xs text-slate-500 mt-1">Please enter your café employee credentials to access your station.</p>
                </div>

                <!-- Session Status / Flash Alert -->
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
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                            </div>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                   placeholder="employee@cafe.com"
                                   class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition shadow-2xs">
                        </div>
                        @if($errors->has('email'))
                            <p class="text-rose-600 text-xs mt-1.5 font-semibold flex items-center">
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
                                    Forgot password?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                   placeholder="••••••••"
                                   class="w-full pl-10 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition shadow-2xs font-mono">
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none" title="Toggle password visibility">
                                <svg id="eyeIconOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg id="eyeIconClosed" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                        @if($errors->has('password'))
                            <p class="text-rose-600 text-xs mt-1.5 font-semibold flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $errors->first('password') }}
                            </p>
                        @endif
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer">
                            <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-amber-500 focus:ring-amber-400 cursor-pointer">
                            <span class="ms-2 text-xs text-slate-600 font-medium select-none">Remember this station</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" id="btnSignInSubmit" class="w-full py-3.5 px-4 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 active:scale-[0.99] text-white font-extrabold rounded-xl shadow-lg shadow-amber-500/25 transition text-xs uppercase tracking-wider flex items-center justify-center space-x-2">
                            <span>Sign In to Terminal</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Terminal Security Footer -->
            <div class="mt-8 pt-4 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                <div class="flex items-center space-x-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Secure Encrypted Session</span>
                </div>
                <span>Veloura Café v2.0</span>
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

            // Flash effect for neat feedback
            emailInput.classList.add('ring-2', 'ring-amber-400', 'bg-amber-50');
            passInput.classList.add('ring-2', 'ring-amber-400', 'bg-amber-50');

            setTimeout(() => {
                emailInput.classList.remove('ring-2', 'ring-amber-400', 'bg-amber-50');
                passInput.classList.remove('ring-2', 'ring-amber-400', 'bg-amber-50');
            }, 600);

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
