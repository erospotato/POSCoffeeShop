<x-guest-layout>
    <div class="max-w-md w-full mx-auto bg-[#151026] rounded-2xl shadow-xl p-8 sm:p-10 border border-[#281e42] text-slate-100">
        <div class="text-center mb-6">
            <div class="w-16 h-16 mx-auto mb-3 rounded-xl bg-[#1d1636] border border-[#37285c] flex items-center justify-center p-2.5">
                <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="h-full w-auto">
            </div>
            <h2 class="text-xl font-black text-white">Veloura Café</h2>
            <p class="text-xs text-purple-300/80 font-medium mt-0.5">Email Verification</p>
        </div>

        <div class="mb-4 text-xs text-purple-200/80 leading-relaxed">
            {{ __('Thanks for signing up! Please verify your email address by clicking on the link we just sent to your inbox.') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-semibold text-xs text-emerald-300 bg-emerald-950/60 p-3 rounded-xl border border-emerald-800/60">
                {{ __('A new verification link has been sent to your email address.') }}
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between pt-2">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="py-2.5 px-4 bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white font-bold rounded-xl text-xs transition">
                    {{ __('Resend Email') }}
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs font-semibold text-purple-400 hover:text-purple-300 hover:underline">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
