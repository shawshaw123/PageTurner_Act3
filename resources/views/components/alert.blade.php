@props(['type' => 'info', 'title' => null, 'dismissible' => false])

@php
$styles = [
    'success' => [
        'wrap' => 'bg-emerald-50 border-emerald-200 text-emerald-900',
        'icon' => 'text-emerald-600',
        'path' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    ],
    'error' => [
        'wrap' => 'bg-red-50 border-red-200 text-red-900',
        'icon' => 'text-red-600',
        'path' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    ],
    'warning' => [
        'wrap' => 'bg-amber-50 border-amber-200 text-amber-900',
        'icon' => 'text-amber-600',
        'path' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    ],
    'info' => [
        'wrap' => 'bg-brand-cream border-brand-border text-brand-ink',
        'icon' => 'text-brand-darkgreen',
        'path' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    ],
];
$s = $styles[$type] ?? $styles['info'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xl border px-4 py-3.5 text-sm ' . $s['wrap']]) }} role="{{ $type === 'error' ? 'alert' : 'status' }}">
    <svg class="w-5 h-5 shrink-0 mt-0.5 {{ $s['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $s['path'] }}"/>
    </svg>
    <div class="flex-1 min-w-0">
        @if($title)
            <p class="font-semibold mb-0.5">{{ $title }}</p>
        @endif
        <div>{{ $slot }}</div>
    </div>
    @if($dismissible)
        <button type="button" onclick="this.closest('[role]').remove()" class="shrink-0 p-1 -mr-1 -mt-1 rounded-lg opacity-60 hover:opacity-100 transition-opacity" aria-label="Dismiss notification">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif
</div>
