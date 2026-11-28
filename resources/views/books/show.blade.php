@extends('layouts.app')

@section('title', $book->title . ' — LEBROn')

@php
    use App\Services\ImageService;
    $imageUrl = ImageService::getImageUrl($book->cover_image, $book->title);
    $avgRating = round((float) ($book->average_rating ?? 0), 1);
@endphp

@section('header')
<x-page-header
    :title="$book->title"
    :subtitle="'by ' . $book->author"
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Books', 'url' => route('books.index')],
        ['label' => $book->category->name, 'url' => route('categories.show', $book->category)],
        ['label' => Str::limit($book->title, 40)],
    ]"
>
    @auth
        @can('update', $book)
            <x-slot:actions>
                <a href="{{ route('books.edit', $book) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-brand-border bg-white text-sm font-semibold text-brand-ink hover:bg-brand-cream transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <form action="{{ route('books.destroy', $book) }}" method="POST" data-confirm="Delete '{{ $book->title }}'? This action cannot be undone.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-red-50 text-red-700 border border-red-200 text-sm font-semibold hover:bg-red-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                </form>
            </x-slot:actions>
        @endcan
    @endauth
</x-page-header>
@endsection

@section('content')
{{-- Main Book Showcase --}}
<div class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
    <div class="grid grid-cols-1 lg:grid-cols-12">
        {{-- Cover Column --}}
        <div class="lg:col-span-5 bg-brand-cream/70 p-8 sm:p-10 flex items-center justify-center border-b lg:border-b-0 lg:border-r border-brand-border">
            <div class="relative max-w-xs w-full">
                <img src="{{ $imageUrl }}"
                     alt="Cover of {{ $book->title }}"
                     class="w-full max-h-[440px] object-contain rounded-lg shadow-lg bg-white p-2"
                     loading="lazy"
                     decoding="async">

                @if($book->stock_quantity <= 0)
                    <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-semibold bg-red-600 text-white shadow">Out of Stock</span>
                @elseif($book->stock_quantity <= 5)
                    <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500 text-white shadow">Only {{ $book->stock_quantity }} left</span>
                @endif
            </div>
        </div>

        {{-- Info & Purchase Column --}}
        <div class="lg:col-span-7 p-6 sm:p-8 lg:p-10 flex flex-col">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('categories.show', $book->category) }}" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-darkgreen/10 text-brand-darkgreen hover:bg-brand-darkgreen hover:text-white transition-colors">
                    {{ $book->category->name }}
                </a>
                @if($book->seller)
                    <span class="text-xs text-brand-muted">Sold by <strong class="text-brand-ink">{{ $book->seller->store_display_name }}</strong></span>
                @endif
            </div>

            <h2 class="font-serif text-2xl sm:text-3xl font-bold text-brand-ink mt-4 leading-tight">{{ $book->title }}</h2>
            <p class="text-base text-brand-muted mt-1">by <span class="font-medium text-brand-ink">{{ $book->author }}</span></p>

            {{-- Rating Summary --}}
            <div class="flex items-center gap-3 mt-4 pb-5 border-b border-brand-border">
                <div class="flex items-center" aria-label="Rated {{ $avgRating }} out of 5">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="h-5 w-5 {{ $i <= round($avgRating) ? 'text-brand-amber' : 'text-stone-200' }}" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                <span class="text-sm font-bold text-brand-ink tabular-nums">{{ number_format($avgRating, 1) }}</span>
                <a href="#reviews" class="text-sm text-brand-muted hover:text-brand-darkgreen underline underline-offset-4">
                    {{ $book->reviews->count() }} {{ Str::plural('review', $book->reviews->count()) }}
                </a>
            </div>

            {{-- Price & Availability --}}
            <div class="mt-6 flex flex-wrap items-baseline justify-between gap-4">
                <p class="text-3xl sm:text-4xl font-bold text-brand-darkgreen tabular-nums">₱{{ number_format($book->price, 2) }}</p>
                <x-badge :variant="$book->stock_quantity > 5 ? 'success' : ($book->stock_quantity > 0 ? 'warning' : 'danger')" dot>
                    {{ $book->stock_quantity > 0 ? $book->stock_quantity . ' in stock' : 'Out of stock' }}
                </x-badge>
            </div>

            {{-- Purchase Actions (Unified Quantity Selector) --}}
            <div class="mt-6">
                @auth
                    @if($book->stock_quantity > 0)
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <label for="shared_quantity" class="text-xs font-semibold uppercase tracking-wider text-brand-muted">Quantity</label>
                                <div class="inline-flex items-center rounded-lg border border-brand-border bg-brand-paper">
                                    <button type="button" onclick="stepBookQty(-1)" class="px-3 py-1.5 text-brand-ink hover:bg-brand-cream rounded-l-lg font-semibold" aria-label="Decrease quantity">&minus;</button>
                                    <input type="number" id="shared_quantity" value="1" min="1" max="{{ $book->stock_quantity }}"
                                           oninput="syncBookQty(this.value)"
                                           class="w-14 border-0 bg-transparent text-center text-sm font-bold text-brand-ink focus:ring-0">
                                    <button type="button" onclick="stepBookQty(1)" class="px-3 py-1.5 text-brand-ink hover:bg-brand-cream rounded-r-lg font-semibold" aria-label="Increase quantity">+</button>
                                </div>
                                <span class="text-xs text-brand-muted">Max {{ $book->stock_quantity }}</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <form action="{{ route('cart.add', $book) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="quantity" id="cart_quantity" value="1">
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-brand-darkgreen text-white font-semibold text-sm hover:bg-brand-forest transition-colors shadow-xs">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Add to Cart
                                    </button>
                                </form>

                                <form action="{{ route('cart.add', $book) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="buy_now" value="1">
                                    <input type="hidden" name="quantity" id="quantity" value="1">
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border-2 border-brand-darkgreen text-brand-darkgreen bg-white font-semibold text-sm hover:bg-brand-cream transition-colors">
                                        Buy Now
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <x-alert type="error" title="Currently Out of Stock">
                            This title is temporarily unavailable. Check back later or browse similar books in {{ $book->category->name }}.
                        </x-alert>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 rounded-xl bg-brand-darkgreen text-white font-semibold text-sm hover:bg-brand-forest transition-colors">
                        Sign in to Purchase
                    </a>
                @endauth
            </div>

            {{-- Metadata --}}
            <dl class="mt-8 pt-6 border-t border-brand-border grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-brand-muted">ISBN</dt>
                    <dd class="mt-1 font-mono font-medium text-brand-ink">{{ $book->isbn }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-brand-muted">Category</dt>
                    <dd class="mt-1 font-medium text-brand-ink">{{ $book->category->name }}</dd>
                </div>
                @if($book->publisher)
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-brand-muted">Publisher</dt>
                        <dd class="mt-1 font-medium text-brand-ink">{{ $book->publisher }}</dd>
                    </div>
                @endif
            </dl>

            {{-- Description --}}
            @if($book->description)
                <div class="mt-7 pt-6 border-t border-brand-border">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-brand-muted mb-2.5">About This Book</h3>
                    <div class="text-sm sm:text-base text-brand-ink/85 leading-relaxed bg-brand-paper rounded-xl p-5 border border-brand-border/80">
                        {{ $book->description }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Customer Reviews Section --}}
@php
    $hasPurchased = auth()->check() ? auth()->user()->hasPurchased($book) : false;
@endphp
<section id="reviews" class="mt-12" aria-labelledby="reviews-heading">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h2 id="reviews-heading" class="font-serif text-2xl font-bold text-brand-ink">Customer Reviews</h2>
            <p class="text-sm text-brand-muted mt-0.5">{{ $book->reviews->count() }} verified reader {{ Str::plural('opinion', $book->reviews->count()) }}</p>
        </div>

        @auth
            @if($hasPurchased)
                <a href="#review-form" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                    Write a Review
                </a>
            @endif
        @endauth
    </div>

    {{-- Review Form --}}
    @auth
        @unless($hasPurchased)
            <x-alert type="info" class="mb-6">
                Only buyers who have received this book can leave a review. The review form unlocks once an order containing this book is shipped or completed.
            </x-alert>
        @else
            <div id="review-form" class="bg-white rounded-2xl border border-brand-border shadow-xs p-6 mb-8">
                <h3 class="font-serif text-lg font-bold text-brand-ink mb-4">Share Your Thoughts</h3>
                <form action="{{ route('reviews.store', $book) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <span class="block text-sm font-medium text-brand-ink mb-2">Your Rating <span class="text-red-500">*</span></span>
                        <div class="flex items-center space-x-2" role="radiogroup" aria-label="Star rating">
                            @for($i = 1; $i <= 5; $i++)
                                <label class="cursor-pointer p-0.5 rounded focus-within:ring-2 focus-within:ring-brand-darkgreen">
                                    <input type="radio" name="rating" value="{{ $i }}" class="sr-only" required>
                                    <svg class="h-7 w-7 text-stone-300 hover:text-brand-amber transition-colors" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span class="sr-only">{{ $i }} {{ Str::plural('star', $i) }}</span>
                                </label>
                            @endfor
                        </div>
                    </div>

                    <div>
                        <label for="review_comment" class="block text-sm font-medium text-brand-ink mb-1.5">Comment <span class="text-red-500">*</span></label>
                        <textarea id="review_comment" name="comment" rows="4" required placeholder="What stood out to you about this book?"
                                  class="w-full rounded-xl border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">{{ old('comment') }}</textarea>
                        @error('comment')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                        Submit Review
                    </button>
                </form>
            </div>
        @endunless
    @else
        <x-alert type="info" class="mb-6">
            Please <a href="{{ route('login') }}" class="font-semibold underline text-brand-darkgreen">sign in</a> to write a review.
        </x-alert>
    @endauth

    {{-- Reviews List --}}
    <div class="space-y-4">
        @forelse($book->reviews as $review)
            <article class="bg-white rounded-xl border border-brand-border p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-full bg-brand-cream text-brand-darkgreen text-xs font-bold flex items-center justify-center uppercase">
                                {{ substr($review->user->name, 0, 1) }}
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-brand-ink">{{ $review->user->name }}</p>
                                <div class="flex items-center gap-1 mt-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="h-3.5 w-3.5 {{ $i <= $review->rating ? 'text-brand-amber' : 'text-stone-200' }}" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                    @if(isset($review->status))
                                        <x-badge class="ml-2" :variant="$review->status === 'approved' ? 'success' : ($review->status === 'rejected' ? 'danger' : 'neutral')">
                                            {{ ucfirst($review->status) }}
                                        </x-badge>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-brand-muted">
                        <time>{{ $review->created_at->diffForHumans() }}</time>
                        @auth
                            @if(auth()->user()->isAdmin())
                                @if($review->status != 'approved')
                                    <form action="{{ route('reviews.approve', $review) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="px-2.5 py-1 rounded-md bg-emerald-600 text-white font-semibold hover:bg-emerald-700">Approve</button>
                                    </form>
                                @endif
                                @if($review->status != 'rejected')
                                    <form action="{{ route('reviews.reject', $review) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="px-2.5 py-1 rounded-md bg-amber-100 text-amber-800 font-semibold hover:bg-amber-200">Reject</button>
                                    </form>
                                @endif
                                <form action="{{ route('reviews.destroy', $review) }}" method="POST" data-confirm="Delete this review?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded-md text-red-600 hover:bg-red-50 font-semibold">Delete</button>
                                </form>
                            @elseif(auth()->id() === $review->user_id)
                                <form action="{{ route('reviews.destroy', $review) }}" method="POST" data-confirm="Delete your review?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline font-medium">Delete</button>
                                </form>
                            @endif
                        @endauth
                    </div>
                </div>

                @isset($review->comment)
                    <p class="mt-3 text-sm text-brand-ink/85 leading-relaxed">{{ $review->comment }}</p>
                @endisset
            </article>
        @empty
            <x-empty-state title="No reviews yet" description="Be the first verified reader to share your thoughts on this book." />
        @endforelse
    </div>
</section>

<script>
    function syncBookQty(val) {
        var max = parseInt(document.getElementById('shared_quantity')?.max || '1', 10);
        var n = Math.max(1, Math.min(max, parseInt(val || '1', 10)));
        if (document.getElementById('cart_quantity')) document.getElementById('cart_quantity').value = n;
        if (document.getElementById('quantity')) document.getElementById('quantity').value = n;
    }
    function stepBookQty(delta) {
        var input = document.getElementById('shared_quantity');
        if (!input) return;
        var max = parseInt(input.max || '1', 10);
        var next = Math.max(1, Math.min(max, (parseInt(input.value, 10) || 1) + delta));
        input.value = next;
        syncBookQty(next);
    }
</script>
@include('partials.star-rating-js')
@endsection
