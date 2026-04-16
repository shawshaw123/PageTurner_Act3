@extends('layouts.app')

@section('title', $book->title . ' - PageTurner')

@php
    use App\Services\ImageService;
    $imageUrl = ImageService::getImageUrl($book->cover_image, $book->title);
    $placeholderUrl = ImageService::generatePlaceholder($book->title);
@endphp

@section('content')
<div class="bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden">
    <div class="md:flex">
        {{-- Left: Book Cover --}}
        <div class="md:w-1/3 bg-gray-50 p-10 flex items-center justify-center border-b md:border-b-0 md:border-r border-gray-100">
            <div class="relative bg-white rounded-lg p-4 shadow-inner">
                <img src="{{ $imageUrl }}" alt="" class="max-h-[500px] shadow-2xl rounded-lg object-contain text-transparent transition-opacity duration-300" 
                     onload="this.style.opacity = '1'"
                     style="opacity: 0;"
                     onerror="this.src='{{ asset('storage/' . $placeholderUrl) }}'; this.style.opacity = '1'"
                     decoding="async">
                
                <!-- Stock Badge -->
                @if($book->stock_quantity <= 5 && $book->stock_quantity > 0)
                    <div class="absolute top-4 right-4 bg-yellow-500 text-white text-sm px-3 py-2 rounded-lg shadow-lg">
                        ⚠️ Only {{ $book->stock_quantity }} left!
                    </div>
                @elseif($book->stock_quantity == 0)
                    <div class="absolute top-4 right-4 bg-red-500 text-white text-sm px-3 py-2 rounded-lg shadow-lg">
                        ❌ Out of Stock
                    </div>
                @endif
            </div>
        </div>
        
        {{-- Right: Book Info --}}
        <div class="md:w-2/3 p-8 lg:p-12">
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1 bg-brand-darkgreen/10 text-brand-darkgreen rounded-full text-xs font-bold uppercase tracking-widest">
                    {{ $book->category->name }}
                </span>
            </div>
            
            <h1 class="text-4xl font-extrabold text-gray-900 mt-4 leading-tight">{{ $book->title }}</h1>
            <p class="text-2xl text-gray-500 mt-2 italic font-serif">by {{ $book->author }}</p>
            
            {{-- Rating Summary --}}
            <div class="flex items-center mt-6 py-2 border-y border-gray-50">
                <div class="flex items-center">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="h-5 w-5 {{ $i <= round($book->average_rating) ? 'text-brand-amber' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                <span class="ml-3 text-sm font-bold text-gray-900">{{ number_format($book->average_rating, 1) }}</span>
                <span class="mx-2 text-gray-300">|</span>
                <span class="text-sm text-gray-500 underline decoration-brand-amber/30">{{ $book->reviews->count() }} Reviews</span>
            </div>

            <div class="mt-8 flex items-baseline space-x-4">
                <p class="text-5xl font-black text-brand-darkgreen">₱{{ number_format($book->price, 2) }}</p>
            </div>

            {{-- Purchase Section --}}
            <div class="mt-10">
                @auth
                    @if($book->stock_quantity > 0)
                        <div class="space-y-4">
                            <!-- Add to Cart Form -->
                            <form action="{{ route('cart.add', $book) }}" method="POST" class="flex flex-col sm:flex-row gap-4">
                                @csrf
                                <div class="flex items-center border border-gray-300 rounded-lg p-1 bg-gray-50">
                                    <label for="cart_quantity" class="px-3 text-sm font-bold text-gray-500 uppercase">Qty</label>
                                    <input type="number" name="quantity" id="cart_quantity" value="1" min="1" max="{{ $book->stock_quantity }}" 
                                        class="w-16 border-none bg-transparent focus:ring-0 text-center font-bold text-gray-900">
                                </div>
                                <button type="submit" 
                                    class="flex-grow bg-brand-darkgreen text-white px-8 py-4 rounded-lg font-bold text-lg hover:bg-brand-amber hover:text-brand-darkgreen transition-all transform hover:-translate-y-1 shadow-lg">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Add to Cart
                                </button>
                            </form>
                            
                            <!-- Buy Now Form -->
                            <form action="{{ route('orders.store') }}" method="POST" class="flex flex-col sm:flex-row gap-4">
                                @csrf
                                <input type="hidden" name="book_id" value="{{ $book->id }}">
                                <div class="flex items-center border border-gray-300 rounded-lg p-1 bg-gray-50">
                                    <label for="quantity" class="px-3 text-sm font-bold text-gray-500 uppercase">Qty</label>
                                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="{{ $book->stock_quantity }}" 
                                        class="w-16 border-none bg-transparent focus:ring-0 text-center font-bold text-gray-900">
                                </div>
                                <button type="submit" 
                                    class="flex-grow bg-brand-darkgreen text-white px-8 py-4 rounded-lg font-bold text-lg hover:bg-brand-amber hover:text-brand-darkgreen transition-all transform hover:-translate-y-1 shadow-lg">
                                    Buy Now
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                            <svg class="w-12 h-12 text-red-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-red-800 font-bold text-lg">Out of Stock</p>
                            <p class="text-red-600 text-sm mt-1">This book is currently unavailable</p>
                        </div>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="block text-center bg-gray-900 text-white px-8 py-4 rounded-lg font-bold text-lg hover:bg-brand-amber hover:text-brand-darkgreen transition-all shadow-lg">
                        Login to Purchase
                    </a>
                @endauth
            </div>

            {{-- Metadata Grid --}}
            <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 gap-8 border-t border-gray-100 pt-8">
                <div class="flex flex-col">
                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">ISBN Identifier</span>
                    <span class="text-base font-mono font-bold text-gray-800">{{ $book->isbn }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Stock Availability</span>
                    <span class="text-base font-bold select-none {{ $book->stock_quantity > 0 ? 'text-green-600' : 'text-red-500' }}">
                        {{ $book->stock_quantity }} units in stock
                    </span>
                </div>
            </div>

            {{-- Description --}}
            <div class="mt-12">
                <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-4">Book Description</h3>
                <p class="text-gray-600 leading-relaxed text-lg italic bg-gray-50 p-6 rounded-xl border-l-4 border-brand-amber font-serif">
                    "{{ $book->description }}"
                </p>
            </div>
            
            {{-- Admin Actions --}}
            @auth
                @if(auth()->user()->isAdmin())
                    <div class="mt-10 flex flex-wrap gap-4 pt-8 border-t border-gray-100">
                        <a href="{{ route('books.edit', $book) }}" class="flex items-center px-4 py-2 bg-yellow-400 text-yellow-900 rounded-md font-bold hover:bg-yellow-500 transition shadow-sm">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Edit
                        </a>
                        <form action="{{ route('books.destroy', $book) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex items-center px-4 py-2 bg-red-100 text-red-600 rounded-md font-bold hover:bg-red-200 transition shadow-sm">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Delete
                            </button>
                        </form>
                    </div>
                @endif
            @endauth
        </div>
    </div>
</div>

{{-- Reviews Section --}}
<div class="mt-8">
    <h2 class="text-2xl font-bold mb-6">Customer Reviews</h2>
    
    {{-- Review Form (for authenticated users) --}}
    @auth
        @php
            $hasPurchased = \App\Models\OrderItem::whereHas('order', function($q) {
                $q->where('user_id', auth()->id())->where('status', 'completed');
            })->where('book_id', $book->id)->exists();
        @endphp

        @if($hasPurchased || auth()->user()->isAdmin())
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="font-semibold text-lg mb-4">Write a Review</h3>
                <form action="{{ route('reviews.store', $book) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Rating</label>
                        <select name="rating" class="border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" required>
                            <option value="">Select rating</option>
                            @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Comment</label>
                        <textarea name="comment" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" placeholder="Share your thoughts about this book..."></textarea>
                    </div>
                    <button type="submit" class="bg-brand-darkgreen text-white px-6 py-2 rounded hover:bg-brand-amber hover:text-brand-darkgreen transition-colors">
                        Submit Review
                    </button>
                </form>
            </div>
        @else
            <x-alert type="info" class="mb-6">
                You can only review books you have purchased. Browse our catalog to find your next read!
            </x-alert>
        @endif
    @else
        <x-alert type="info" class="mb-6">
            <a href="{{ route('login') }}" class="text-brand-darkgreen hover:text-brand-amber font-semibold underline">Login</a> to write a review.
        </x-alert>
    @endauth
    
    {{-- Display Reviews --}}
    @forelse($book->reviews as $review)
        <div class="bg-white rounded-lg shadow p-6 mb-4">
            <div class="flex justify-between items-start">
                <div>
                    <p class="font-semibold">{{ $review->user->name }}</p>
                    <div class="flex items-center mt-1">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="h-4 w-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-gray-500 text-sm">{{ $review->created_at->diffForHumans() }}</span>
                    @auth
                        @if(auth()->id() === $review->user_id || auth()->user()->isAdmin())
                            <form action="{{ route('reviews.destroy', $review) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Delete</button>
                            </form>
                        @endif
                    @endauth
                </div>
            </div>
            @isset($review->comment)
                <p class="text-gray-600 mt-3">{{ $review->comment }}</p>
            @endisset
        </div>
    @empty
        <x-alert type="info">
            No reviews yet. Be the first to review this book!
        </x-alert>
    @endforelse
</div>
@endsection
