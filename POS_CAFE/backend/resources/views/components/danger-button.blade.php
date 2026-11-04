<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2.5 bg-rose-600 hover:bg-rose-500 active:bg-rose-700 text-white font-bold rounded-xl text-xs uppercase tracking-widest shadow-md shadow-rose-950/50 focus:outline-none focus:ring-2 focus:ring-rose-500 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
