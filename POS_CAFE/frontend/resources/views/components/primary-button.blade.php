<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2.5 bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white font-bold rounded-xl text-xs uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-purple-500 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
