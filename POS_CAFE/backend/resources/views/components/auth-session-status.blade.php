@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-semibold text-xs text-purple-200 bg-purple-950/60 p-3 rounded-xl border border-purple-800/60']) }}>
        {{ $status }}
    </div>
@endif
