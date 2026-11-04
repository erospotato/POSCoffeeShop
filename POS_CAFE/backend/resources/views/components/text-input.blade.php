@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-[#110d22] border border-[#2d2250] text-white rounded-xl shadow-sm focus:border-purple-500 focus:ring-purple-500 placeholder:text-purple-300/40 text-xs px-3.5 py-2.5']) }}>
