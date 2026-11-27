@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-brand-border px-6 py-12 text-center']) }}>
    <div class="mx-auto w-12 h-12 rounded-full bg-brand-cream text-brand-darkgreen flex items-center justify-center mb-4">
        @isset($icon)
            {{ $icon }}
        @else
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        @endisset
    </div>
    <h3 class="font-serif text-lg font-semibold text-brand-ink">{{ $title }}</h3>
    @if($description)
        <p class="mt-1.5 text-sm text-brand-muted max-w-md mx-auto">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-6 flex items-center justify-center gap-3">
            {{ $action }}
        </div>
    @endisset
</div>
