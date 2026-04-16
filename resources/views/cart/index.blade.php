@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Shopping Cart</h1>
        @if(!empty($cartItems))
            <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear your entire cart?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Clear Cart</button>
            </form>
        @endif
    </div>

    @if(empty($cartItems))
        <div class="text-center py-16 bg-white rounded-lg shadow-sm">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h3>
            <p class="text-gray-500 mb-6">Looks like you haven't added any books to your cart yet.</p>
            <a href="{{ route('books.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
            <a href="{{ route('books.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-darkgreen hover:bg-brand-amber hover:text-brand-darkgreen transition-colors">
                Browse Books
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    @foreach($cartItems as $item)
                        <div class="p-6 border-b border-gray-200 last:border-b-0">
                            <div class="flex items-center space-x-4">
                                <!-- Book Cover -->
                                <div class="flex-shrink-0">
                                    @if($item['book']->cover_image)
                                        <img src="{{ asset('storage/' . $item['book']->cover_image) }}" alt="{{ $item['book']->title }}" class="w-16 h-20 object-cover rounded">
                                    @else
                                        <div class="w-16 h-20 bg-gray-200 rounded flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Book Details -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-medium text-gray-900 truncate">
                                        <a href="{{ route('books.show', $item['book']) }}" class="hover:text-brand-darkgreen transition-colors">
                                            {{ $item['book']->title }}
                                        </a>
                                    </h3>
                                    <p class="text-sm text-gray-500">{{ $item['book']->author }}</p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ $item['book']->category->name }} • {{ $item['book']->isbn }}
                                    </p>
                                    <div class="flex items-center space-x-4 mt-2">
                                        <p class="text-lg font-semibold text-brand-darkgreen">
                                            ₱{{ number_format($item['book']->price, 2) }}
                                        </p>
                                        <span class="text-gray-400 text-sm">× {{ $item['quantity'] }} =</span>
                                        <p class="text-lg font-bold text-gray-900">
                                            ₱{{ number_format($item['subtotal'], 2) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Quantity Controls -->
                                <div class="flex items-center space-x-3">
                                    <form action="{{ route('cart.update', $item['book']) }}" method="POST" class="flex items-center update-cart-form">
                                        @csrf
                                        @method('PUT')
                                        <div class="flex border border-gray-300 rounded-md overflow-hidden">
                                            <button type="button" onclick="decrement(this)" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-600 transition-colors">-</button>
                                            <input type="number" 
                                                   name="quantity" 
                                                   value="{{ $item['quantity'] }}" 
                                                   min="0" 
                                                   max="{{ $item['book']->stock_quantity }}" 
                                                   onchange="this.form.submit()"
                                                   class="w-12 px-1 py-1 border-none text-center focus:ring-0 text-gray-900 font-medium">
                                            <button type="button" onclick="increment(this)" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-600 transition-colors">+</button>
                                        </div>
                                        <button type="submit" class="hidden">Update</button>
                                    </form>
                                    
                                    <form action="{{ route('cart.remove', $item['book']) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-full transition-colors" title="Remove Item">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Stock Warning -->
                            @if($item['quantity'] >= $item['book']->stock_quantity)
                                <div class="mt-3 text-sm text-amber-600 bg-amber-50 px-3 py-1 rounded">
                                    ⚠️ Only {{ $item['book']->stock_quantity }} copies available in stock
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal ({{ count($cartItems) }} items)</span>
                            <span class="font-medium">₱{{ number_format($total, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-medium">Free</span>
                        </div>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax</span>
                            <span class="font-medium">₱{{ number_format($total * 0.12, 2) }}</span>
                        </div>
                        
                        <div class="border-t pt-3">
                            <div class="flex justify-between">
                                <span class="text-lg font-semibold">Total</span>
                                <span class="text-lg font-bold text-brand-darkgreen">₱{{ number_format($total * 1.12, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <a href="{{ route('cart.checkout') }}" class="w-full flex items-center justify-center px-4 py-3 border border-transparent text-base font-medium rounded-md text-white bg-brand-darkgreen hover:bg-brand-amber hover:text-brand-darkgreen transition-colors">
                            Proceed to Checkout
                        </a>
                        
                        <a href="{{ route('books.index') }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            Continue Shopping
                        </a>
                    </div>

                    <div class="mt-6 text-xs text-gray-500">
                        <p>• Free shipping on all orders</p>
                        <p>• 12% VAT applied</p>
                        <p>• Secure checkout process</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function increment(btn) {
        const input = btn.previousElementSibling;
        const max = parseInt(input.getAttribute('max'));
        const value = parseInt(input.value);
        if (isNaN(max) || value < max) {
            input.value = value + 1;
            input.form.submit();
        }
    }

    function decrement(btn) {
        const input = btn.nextElementSibling;
        const value = parseInt(input.value);
        if (value > 0) {
            input.value = value - 1;
            input.form.submit();
        }
    }
</script>
@endpush
