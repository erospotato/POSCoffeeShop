@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-purple-500 text-start text-base font-bold text-white bg-purple-950/60 focus:outline-none focus:text-white focus:bg-purple-900/60 focus:border-purple-400 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-purple-200/80 hover:text-white hover:bg-[#1a1435] hover:border-purple-400 focus:outline-none focus:text-white focus:bg-[#1a1435] focus:border-purple-400 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
