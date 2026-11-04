<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 active:scale-[0.99] text-white font-bold rounded-xl text-xs uppercase tracking-widest shadow-md shadow-purple-950/60 focus:outline-none focus:ring-2 focus:ring-purple-500 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
