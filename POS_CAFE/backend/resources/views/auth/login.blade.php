<x-guest-layout>
    <div class="max-w-sm w-full mx-auto bg-[#151026] border border-[#281e42] rounded-3xl shadow-2xl p-8 sm:p-9 text-slate-100 relative">
        <!-- Brand Logo & Name -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-[#1d1636] border border-[#37285c] flex items-center justify-center p-2.5 shadow-lg shadow-purple-950/40">
                <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="h-full w-auto">
            </div>
            <h1 class="text-xl font-black text-white tracking-tight">Veloura Café</h1>
            <p class="text-xs text-purple-300/80 font-medium mt-0.5">Staff & Management Access</p>
        </div>

        <!-- Session Status Alert -->
        @if (session('status'))
            <div class="mb-5 p-3 rounded-xl bg-purple-950/60 border border-purple-800/60 text-purple-200 text-xs font-semibold flex items-center">
                <svg class="w-4 h-4 text-purple-400 mr-2 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-bold text-purple-300 uppercase tracking-wider mb-1.5">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       placeholder="name@cafe.com"
                       class="w-full px-3.5 py-2.5 bg-[#110d22] border border-[#2d2250] rounded-xl text-xs text-white placeholder:text-purple-300/40 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">
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
                    <label for="password" class="block text-xs font-bold text-purple-300 uppercase tracking-wider">Password</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-[11px] font-semibold text-purple-400 hover:text-purple-300 hover:underline">
                            Forgot?
                        </a>
                    @endif
                </div>
                <div class="relative">
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           placeholder="••••••••"
                           class="w-full pl-3.5 pr-10 py-2.5 bg-[#110d22] border border-[#2d2250] rounded-xl text-xs text-white placeholder:text-purple-300/40 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition font-mono">
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-purple-400 hover:text-purple-300 focus:outline-none" title="Toggle password visibility">
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
                    <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded bg-[#110d22] border-[#2d2250] text-purple-600 focus:ring-purple-500 cursor-pointer">
                    <span class="ms-2 text-xs text-purple-300/80 select-none">Remember me</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" id="btnSignInSubmit" class="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 active:scale-[0.99] text-white font-extrabold rounded-xl shadow-lg shadow-purple-950/60 transition text-xs uppercase tracking-wider">
                    Sign In
                </button>
            </div>
        </form>
    </div>

    <!-- Client-side script for password reveal -->
    <script>
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
