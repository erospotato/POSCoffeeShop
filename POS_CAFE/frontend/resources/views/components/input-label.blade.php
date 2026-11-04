@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-bold text-xs uppercase tracking-wider text-purple-300 mb-1']) }}>
    {{ $value ?? $slot }}
</label>
