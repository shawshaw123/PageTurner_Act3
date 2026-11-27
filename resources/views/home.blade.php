@extends('layouts.app')

@section('title', 'LEBROn — Online Book Marketplace')

@section('content')
{{-- Editorial Bookstore Hero --}}
<section class="relative overflow-hidden rounded-2xl bg-brand-darkgreen text-white border border-brand-darkgreen shadow-sm mb-12">
    <div class="px-6 py-10 sm:px-10 sm:py-14 lg:px-14 lg:py-16 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        <div class="lg:col-span-8">
            <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-brand-gold mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-gold" aria-hidden="true"></span>
                Curated Online Bookshop
            </p>
            <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight tracking-tight">
                Discover books worth lingering over.
            </h1>
            <p class="mt-4 text-base sm:text-lg text-white/80 max-w-2xl leading-relaxed">
                Explore handpicked fiction, technical guides, and timeless classics from independent sellers and trusted publishers.
            </p>

            {{-- Integrated Catalog Search --}}
            <form action="{{ route('books.index') }}" method="GET" class="mt-7 flex flex-col sm:flex-row gap-2.5 max-w-xl" role="search" aria-label="Search books">
                <label for="hero-search" class="sr-only">Search by title, author, or ISBN</label>
                <div class="relative flex-1">
                    <svg class="w-5 h-5 text-stone-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input id="hero-search"
                           type="search"
                           name="search"
                           placeholder="Search by title, author, or ISBN..."
                           class="w-full pl-11 pr-4 py-3 rounded-xl border-0 bg-white text-brand-ink placeholder:text-stone-400 text-sm focus:ring-2 focus:ring-brand-gold">
                </div>
                <button type="submit" class="px-6 py-3 rounded-xl bg-brand-gold text-brand-darkgreen font-semibold text-sm hover:bg-white transition-colors shrink-0">
                    Search Catalog
                </button>
            </form>

            <div class="mt-5 flex flex-wrap items-center gap-4 text-xs text-white/75">
                <a href="{{ route('books.index') }}" class="underline underline-offset-4 hover:text-brand-gold transition-colors">Browse all books</a>
                <span aria-hidden="true">&middot;</span>
                <a href="{{ route('ai.chat') }}" class="underline underline-offset-4 hover:text-brand-gold transition-colors">Get AI reading recommendations</a>
            </div>
        </div>

        <div class="hidden lg:flex lg:col-span-4 justify-end">
            <div class="w-full max-w-xs rounded-xl bg-white/10 border border-white/15 p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-lg bg-brand-gold/20 text-brand-gold flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-white">Verified Listings</p>
                        <p class="text-xs text-white/70">ISBN-checked titles &amp; real reviews</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-lg bg-brand-gold/20 text-brand-gold flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-white">Free Delivery</p>
                        <p class="text-xs text-white/70">Complimentary shipping on orders</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-lg bg-brand-gold/20 text-brand-gold flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-white">AI Book Concierge</p>
                        <p class="text-xs text-white/70">Instant tailored suggestions 24/7</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Browse by Category --}}
@if($categories->count() > 0)
<section class="mb-14" aria-labelledby="categories-heading">
    <div class="flex items-end justify-between gap-4 mb-6">
        <div>
            <h2 id="categories-heading" class="font-serif text-2xl font-bold text-brand-ink">Browse by Category</h2>
            <p class="text-sm text-brand-muted mt-1">Jump straight into the shelves that interest you most.</p>
        </div>
        <a href="{{ route('categories.index') }}" class="text-sm font-semibold text-brand-darkgreen hover:text-brand-amber transition-colors shrink-0">
            All categories &rarr;
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5">
        @foreach($categories as $category)
            <a href="{{ route('categories.show', $category) }}"
               class="group bg-white rounded-xl border border-brand-border p-4 hover:border-brand-darkgreen/40 hover:shadow-sm transition-all flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="font-serif font-semibold text-brand-ink group-hover:text-brand-darkgreen transition-colors truncate">{{ $category->name }}</h3>
                    <p class="text-xs text-brand-muted mt-0.5">{{ $category->books_count }} {{ Str::plural('book', $category->books_count) }}</p>
                </div>
                <span class="w-8 h-8 rounded-lg bg-brand-cream text-brand-darkgreen group-hover:bg-brand-darkgreen group-hover:text-white flex items-center justify-center shrink-0 transition-colors" aria-hidden="true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- Featured Books --}}
<section aria-labelledby="featured-heading">
    <div class="flex items-end justify-between gap-4 mb-6">
        <div>
            <h2 id="featured-heading" class="font-serif text-2xl font-bold text-brand-ink">New &amp; Featured Arrivals</h2>
            <p class="text-sm text-brand-muted mt-1">Recently added titles ready to ship from our shelves.</p>
        </div>
        <a href="{{ route('books.index') }}" class="text-sm font-semibold text-brand-darkgreen hover:text-brand-amber transition-colors shrink-0">
            View full catalog &rarr;
        </a>
    </div>

    @if($featuredBooks->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($featuredBooks as $book)
                <x-book-card :book="$book" />
            @endforeach
        </div>
    @else
        <x-empty-state title="No books on the shelves yet" description="New titles are being catalogued. Check back soon or explore our categories.">
            <x-slot:action>
                <a href="{{ route('categories.index') }}" class="px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                    Explore Categories
                </a>
            </x-slot:action>
        </x-empty-state>
    @endif
</section>
@endsection
