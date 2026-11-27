@props(['book'])

@php
    use App\Services\ImageService;
    $imageUrl = ImageService::getImageUrl($book->cover_image, $book->title);
    $avgRating = round((float) ($book->average_rating ?? 0), 1);
    $reviewsCount = $book->reviews_count ?? 0;
@endphp

<article class="group bg-white rounded-xl border border-brand-border shadow-xs hover:shadow-md hover:border-brand-darkgreen/30 transition-all flex flex-col overflow-hidden">
    <!-- Book Cover Frame -->
    <a href="{{ route('books.show', $book) }}" class="relative block aspect-[3/4] bg-brand-cream overflow-hidden p-4 flex items-center justify-center">
        <img src="{{ $imageUrl }}"
             alt="Cover of {{ $book->title }}"
             class="max-h-full max-w-full object-contain rounded shadow-sm group-hover:scale-[1.03] transition-transform duration-300"
             loading="lazy"
             decoding="async">

        <!-- Stock Badge -->
        @if($book->stock_quantity <= 0)
            <span class="absolute top-3 right-3 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-600 text-white shadow-xs">
                Out of Stock
            </span>
        @elseif($book->stock_quantity <= 5)
            <span class="absolute top-3 right-3 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-500 text-white shadow-xs">
                Only {{ $book->stock_quantity }} left
            </span>
        @endif
    </a>

    <!-- Book Body -->
    <div class="p-4 flex flex-col flex-1">
        @if($book->relationLoaded('category') && $book->category)
            <span class="text-[11px] font-semibold uppercase tracking-wider text-brand-amber mb-1">
                {{ $book->category->name }}
            </span>
        @endif

        <h3 class="font-serif font-bold text-brand-ink text-base leading-snug line-clamp-2">
            <a href="{{ route('books.show', $book) }}" class="hover:text-brand-darkgreen transition-colors">
                {{ $book->title }}
            </a>
        </h3>
        <p class="text-xs text-brand-muted mt-1 truncate">by {{ $book->author }}</p>

        <!-- Rating & Price -->
        <div class="mt-3 pt-3 border-t border-brand-border/70 flex items-center justify-between gap-2">
            <p class="text-lg font-bold text-brand-darkgreen tabular-nums">₱{{ number_format($book->price, 2) }}</p>

            <div class="flex items-center gap-1" aria-label="Rated {{ $avgRating }} out of 5 from {{ $reviewsCount }} reviews">
                <svg class="w-4 h-4 {{ $avgRating > 0 ? 'text-brand-amber' : 'text-stone-300' }}" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span class="text-xs font-semibold text-brand-ink tabular-nums">{{ number_format($avgRating, 1) }}</span>
                <span class="text-xs text-brand-muted">({{ $reviewsCount }})</span>
            </div>
        </div>

        <!-- Actions (Clear Primary / Secondary Hierarchy) -->
        <div class="mt-4 pt-1 flex items-center gap-2 mt-auto">
            @auth
                @if(!auth()->user()->isSeller())
                    @if($book->stock_quantity > 0)
                        <form action="{{ route('cart.add', $book) }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-brand-darkgreen text-white text-xs font-semibold hover:bg-brand-forest transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Add to Cart
                            </button>
                        </form>
                    @else
                        <button type="button" disabled class="flex-1 px-3 py-2 rounded-lg bg-stone-100 text-stone-400 text-xs font-semibold cursor-not-allowed">
                            Out of Stock
                        </button>
                    @endif
                    <a href="{{ route('books.show', $book) }}" class="px-3 py-2 rounded-lg border border-brand-border text-xs font-semibold text-brand-ink hover:bg-brand-cream transition-colors">
                        Details
                    </a>
                @else
                    <a href="{{ route('books.show', $book) }}" class="w-full text-center px-3 py-2 rounded-lg border border-brand-border text-xs font-semibold text-brand-ink hover:bg-brand-cream transition-colors">
                        View Details
                    </a>
                @endif
            @else
                <a href="{{ route('books.show', $book) }}" class="w-full text-center px-3 py-2 rounded-lg bg-brand-darkgreen text-white text-xs font-semibold hover:bg-brand-forest transition-colors">
                    View Book
                </a>
            @endauth
        </div>
    </div>
</article>
