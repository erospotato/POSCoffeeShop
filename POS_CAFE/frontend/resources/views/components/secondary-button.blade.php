<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2.5 bg-[#1a1435] border border-[#352758] rounded-xl font-bold text-xs text-purple-200 uppercase tracking-widest shadow-sm hover:bg-[#261d4a] hover:text-white focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
