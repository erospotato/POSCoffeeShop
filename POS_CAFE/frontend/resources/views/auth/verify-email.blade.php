<x-guest-layout>
    <div class="max-w-md w-full mx-auto bg-white rounded-3xl shadow-2xl p-8 sm:p-10 border border-slate-100 text-slate-900">
        <div class="text-center mb-6">
            <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="h-14 w-auto mx-auto mb-2">
            <h2 class="text-xl font-black text-slate-900">Veloura Café</h2>
            <p class="text-xs text-slate-500">Email Verification</p>
        </div>

        <div class="mb-4 text-xs text-slate-600 leading-relaxed">
            {{ __('Thanks for signing up! Please verify your email address by clicking on the link we just sent to your inbox.') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-semibold text-xs text-emerald-600 bg-emerald-50 p-3 rounded-xl border border-emerald-200">
                {{ __('A new verification link has been sent to your email address.') }}
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between pt-2">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-md text-xs transition">
                    {{ __('Resend Email') }}
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-slate-800 hover:underline">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
