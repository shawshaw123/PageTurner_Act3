@props(['title', 'subtitle' => null, 'breadcrumbs' => []])

<div class="space-y-2">
    @if(!empty($breadcrumbs))
        <nav aria-label="Breadcrumb" class="flex items-center flex-wrap gap-1.5 text-xs font-medium text-brand-muted">
            @foreach($breadcrumbs as $crumb)
                @if(!$loop->first)
                    <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                @endif
                @if(!empty($crumb['url']) && !$loop->last)
                    <a href="{{ $crumb['url'] }}" class="hover:text-brand-darkgreen transition-colors">{{ $crumb['label'] }}</a>
                @else
                    <span class="text-brand-ink font-semibold" @if($loop->last) aria-current="page" @endif>{{ $crumb['label'] }}</span>
                @endif
            @endforeach
        </nav>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="font-serif text-2xl sm:text-3xl font-bold text-brand-ink tracking-tight">{{ $title }}</h1>
            @if($subtitle)
                <p class="mt-1 text-sm sm:text-base text-brand-muted max-w-2xl">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
