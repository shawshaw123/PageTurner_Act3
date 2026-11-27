@extends('layouts.app')

@section('title', 'Book Catalog — LEBROn')

@section('header')
<x-page-header
    title="Book Catalog"
    subtitle="Search, filter, and discover books across all genres and independent sellers."
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Books'],
    ]"
>
    @auth
        @can('create', \App\Models\Book::class)
            <x-slot:actions>
                <a href="{{ route('books.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Book
                </a>
            </x-slot:actions>
        @endcan
    @endauth
</x-page-header>
@endsection

@section('content')
@php
    $hasFilters = request()->anyFilled(['search', 'category', 'min_price', 'max_price', 'min_rating', 'in_stock', 'sort_by', 'sort_order']);
@endphp

{{-- Search & Filter Panel --}}
<section class="bg-white rounded-2xl border border-brand-border shadow-xs p-5 sm:p-6 mb-8" aria-label="Search and filter books">
    <form action="{{ route('books.index') }}" method="GET" class="space-y-4">
        {{-- Primary Search Bar --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <label for="search" class="sr-only">Search books</label>
                <svg class="w-5 h-5 text-stone-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="search"
                       id="search"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search by title, author, description, or ISBN..."
                       class="w-full pl-11 pr-4 py-2.5 rounded-xl border-brand-border bg-brand-paper/50 text-sm text-brand-ink placeholder:text-stone-400 focus:bg-white focus:border-brand-darkgreen focus:ring-brand-darkgreen">
            </div>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors shrink-0">
                Apply Filters
            </button>
            @if($hasFilters)
                <a href="{{ route('books.index') }}" class="px-4 py-2.5 rounded-xl border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors text-center shrink-0">
                    Reset
                </a>
            @endif
        </div>

        {{-- Filter Controls Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3.5 pt-2 border-t border-brand-border/70">
            <div>
                <label for="category" class="block text-xs font-semibold text-brand-muted mb-1">Category</label>
                <select id="category" name="category" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="min_price" class="block text-xs font-semibold text-brand-muted mb-1">Min Price (₱)</label>
                <input type="number" id="min_price" name="min_price" value="{{ request('min_price') }}" min="{{ $priceRange['min'] }}" max="{{ $priceRange['max'] }}" step="0.01" placeholder="{{ $priceRange['min'] }}" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
            </div>

            <div>
                <label for="max_price" class="block text-xs font-semibold text-brand-muted mb-1">Max Price (₱)</label>
                <input type="number" id="max_price" name="max_price" value="{{ request('max_price') }}" min="{{ $priceRange['min'] }}" max="{{ $priceRange['max'] }}" step="0.01" placeholder="{{ $priceRange['max'] }}" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
            </div>

            <div>
                <label for="min_rating" class="block text-xs font-semibold text-brand-muted mb-1">Min Rating</label>
                <select id="min_rating" name="min_rating" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                    <option value="">Any Rating</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" @selected(request('min_rating') == $i)>
                            {{ $i }} {{ $i == 1 ? 'Star' : 'Stars' }} &amp; Up
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <label for="sort_by" class="block text-xs font-semibold text-brand-muted mb-1">Sort By</label>
                <select id="sort_by" name="sort_by" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                    <option value="title" @selected(request('sort_by') == 'title')>Title</option>
                    <option value="price" @selected(request('sort_by') == 'price')>Price</option>
                    <option value="rating" @selected(request('sort_by') == 'rating')>Rating</option>
                    <option value="date" @selected(request('sort_by') == 'date')>Date Added</option>
                </select>
            </div>

            <div>
                <label for="sort_order" class="block text-xs font-semibold text-brand-muted mb-1">Order</label>
                <select id="sort_order" name="sort_order" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                    <option value="asc" @selected(request('sort_order') == 'asc')>Ascending</option>
                    <option value="desc" @selected(request('sort_order') == 'desc')>Descending</option>
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="in_stock" value="1" @checked(request('in_stock') == '1') class="rounded border-brand-border text-brand-darkgreen focus:ring-brand-darkgreen">
                <span class="text-sm text-brand-ink">In stock only</span>
            </label>

            <p class="text-xs text-brand-muted">
                @if($books->total() > 0)
                    Showing <span class="font-semibold text-brand-ink">{{ $books->firstItem() }}–{{ $books->lastItem() }}</span> of <span class="font-semibold text-brand-ink">{{ $books->total() }}</span> {{ Str::plural('book', $books->total()) }}
                @else
                    0 books found
                @endif
            </p>
        </div>
    </form>
</section>

{{-- Books Grid --}}
@if($books->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($books as $book)
            <x-book-card :book="$book" />
        @endforeach
    </div>

    <div class="mt-10">
        {{ $books->withQueryString()->links() }}
    </div>
@else
    <x-empty-state
        title="No matching books found"
        description="Try broadening your search terms or clearing one of your active filters."
    >
        <x-slot:action>
            <a href="{{ route('books.index') }}" class="px-5 py-2.5 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                Clear All Filters
            </a>
        </x-slot:action>
    </x-empty-state>
@endif
@endsection
