@props([
    'label',
    'value',
    'subtext' => null,
    'accent' => 'brand',
    'href' => null,
])

@php
$accentMap = [
    'brand'   => ['bar' => 'bg-brand-darkgreen', 'iconBg' => 'bg-brand-darkgreen/10 text-brand-darkgreen'],
    'amber'   => ['bar' => 'bg-brand-amber', 'iconBg' => 'bg-amber-50 text-amber-700'],
    'success' => ['bar' => 'bg-emerald-600', 'iconBg' => 'bg-emerald-50 text-emerald-700'],
    'warning' => ['bar' => 'bg-amber-500', 'iconBg' => 'bg-amber-50 text-amber-700'],
    'danger'  => ['bar' => 'bg-red-500', 'iconBg' => 'bg-red-50 text-red-700'],
    'info'    => ['bar' => 'bg-sky-600', 'iconBg' => 'bg-sky-50 text-sky-700'],
    'neutral' => ['bar' => 'bg-stone-500', 'iconBg' => 'bg-stone-100 text-stone-700'],
];
$a = $accentMap[$accent] ?? $accentMap['brand'];
$tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'relative bg-white rounded-xl border border-brand-border p-5 shadow-xs overflow-hidden block ' . ($href ? 'hover:border-brand-darkgreen/40 hover:shadow-sm transition-all group' : '')]) }}>
    <span class="absolute inset-y-0 left-0 w-1 {{ $a['bar'] }}" aria-hidden="true"></span>
    <div class="flex items-start justify-between gap-3 pl-1">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-muted">{{ $label }}</p>
            <p class="mt-2 text-2xl sm:text-3xl font-bold text-brand-ink tabular-nums tracking-tight">{{ $value }}</p>
            @if($subtext)
                <p class="mt-1 text-xs text-brand-muted">{{ $subtext }}</p>
            @endif
        </div>
        @isset($icon)
            <div class="w-10 h-10 rounded-lg {{ $a['iconBg'] }} flex items-center justify-center shrink-0">
                {{ $icon }}
            </div>
        @endisset
    </div>
</{{ $tag }}>
