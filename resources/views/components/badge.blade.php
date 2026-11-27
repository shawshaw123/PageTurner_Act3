@props(['variant' => 'neutral', 'dot' => false])

@php
$variants = [
    'success' => 'bg-emerald-50 text-emerald-800 ring-emerald-600/20',
    'warning' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
    'danger'  => 'bg-red-50 text-red-800 ring-red-600/20',
    'info'    => 'bg-sky-50 text-sky-800 ring-sky-600/20',
    'brand'   => 'bg-brand-darkgreen/10 text-brand-darkgreen ring-brand-darkgreen/20',
    'neutral' => 'bg-stone-100 text-stone-700 ring-stone-500/20',
];
$dots = [
    'success' => 'bg-emerald-500',
    'warning' => 'bg-amber-500',
    'danger'  => 'bg-red-500',
    'info'    => 'bg-sky-500',
    'brand'   => 'bg-brand-darkgreen',
    'neutral' => 'bg-stone-400',
];
$classes = $variants[$variant] ?? $variants['neutral'];
$dotClass = $dots[$variant] ?? $dots['neutral'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold ring-1 ring-inset ' . $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
