@props(['book'])

@php
    use App\Services\ImageService;
    $imageUrl = ImageService::getImageUrl($book->cover_image, $book->title);
    $placeholderUrl = ImageService::generatePlaceholder($book->title);
@endphp

<div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:border-brand-amber transition-colors isolate" style="contain: paint; transform: translateZ(0); -webkit-font-smoothing: antialiased;">
    <div class="h-48 bg-gray-100 flex items-center justify-center relative">
        <img src="{{ $imageUrl }}" alt="" class="h-full w-full object-cover" 
             loading="lazy"
             decoding="async">
        
        <!-- Stock Badge -->
        @if($book->stock_quantity <= 5 && $book->stock_quantity > 0)
            <div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded">
                Only {{ $book->stock_quantity }} left!
            </div>
        @elseif($book->stock_quantity == 0)
            <div class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                Out of Stock
            </div>
        @endif
    </div>
    
    <div class="p-4">
        <h3 class="font-bold text-gray-900 leading-tight" style="font-size: 1.125rem; height: 1.5rem; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;">{{ $book->title }}</h3>
        <p class="text-gray-600 text-sm">by {{ $book->author }}</p>
        <div class="flex justify-between items-center mt-3">
            <p class="text-brand-darkgreen font-bold text-xl">₱{{ number_format($book->price, 2) }}</p>
            
            {{-- Star Rating --}}
            <div class="flex items-center">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= round($book->average_rating))
                        <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @else
                        <svg class="h-4 w-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endif
                @endfor
                <span class="ml-1 text-xs text-gray-500">({{ $book->reviews->count() }})</span>
            </div>
        </div>
        
        <div class="mt-4 space-y-2">
            @auth
                @if($book->stock_quantity > 0)
                    <form action="{{ route('cart.add', $book) }}" method="POST">
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="w-full bg-brand-darkgreen text-white py-2 rounded hover:bg-brand-amber hover:text-brand-darkgreen transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Add to Cart
                        </button>
                    </form>
                @else
                    <button disabled class="w-full bg-gray-300 text-gray-500 py-2 rounded text-sm font-medium cursor-not-allowed">
                        Out of Stock
                    </button>
                @endif
            @endauth
            
            <a href="{{ route('books.show', $book) }}" class="block text-center bg-brand-darkgreen text-white py-2 rounded hover:bg-brand-amber hover:text-brand-darkgreen transition-colors">
                View Details
            </a>
        </div>
    </div>
</div>
