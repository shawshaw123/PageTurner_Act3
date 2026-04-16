@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
        <p class="text-gray-600 mt-2">Review your order and provide shipping information</p>
    </div>

    <form action="{{ route('cart.process') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        @csrf

        <!-- Order Items & Shipping Form -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Items -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Items</h2>
                
                <div class="space-y-4">
                    @foreach($cartItems as $item)
                        <div class="flex items-center justify-between py-3 border-b border-gray-200 last:border-b-0">
                            <div class="flex items-center space-x-4">
                                <!-- Book Cover -->
                                <div class="flex-shrink-0">
                                    @if($item['book']->cover_image)
                                        <img src="{{ asset('storage/' . $item['book']->cover_image) }}" alt="{{ $item['book']->title }}" class="w-12 h-16 object-cover rounded">
                                    @else
                                        <div class="w-12 h-16 bg-gray-200 rounded flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Book Details -->
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $item['book']->title }}</h4>
                                    <p class="text-sm text-gray-500">{{ $item['book']->author }}</p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Qty: {{ $item['quantity'] }} × ₱{{ number_format($item['book']->price, 2) }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">₱{{ number_format($item['subtotal'], 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Shipping Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-2">
                            Shipping Address <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="shipping_address" 
                            name="shipping_address" 
                            rows="3" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-darkgreen focus:border-brand-darkgreen"
                            placeholder="123 Main St, Apt 4B, New York, NY 10001"
                            required
                        >{{ auth()->user()->name ?? '' }}</textarea>
                        @error('shipping_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Method</h2>
                
                <div class="space-y-3">
                    <label class="flex items-center p-3 border border-gray-200 rounded-md cursor-pointer hover:bg-gray-50 transition-colors">
                        <input type="radio" name="payment_method" value="credit_card" class="mr-3 text-brand-darkgreen focus:ring-brand-darkgreen" checked>
                        <div>
                            <div class="font-medium">Credit Card</div>
                            <div class="text-sm text-gray-500">Visa, Mastercard, American Express</div>
                        </div>
                    </label>
                    
                    <label class="flex items-center p-3 border border-gray-200 rounded-md cursor-pointer hover:bg-gray-50 transition-colors">
                        <input type="radio" name="payment_method" value="paypal" class="mr-3 text-brand-darkgreen focus:ring-brand-darkgreen">
                        <div>
                            <div class="font-medium">PayPal</div>
                            <div class="text-sm text-gray-500">Fast and secure payment</div>
                        </div>
                    </label>
                    
                    <label class="flex items-center p-3 border border-gray-200 rounded-md cursor-pointer hover:bg-gray-50 transition-colors">
                        <input type="radio" name="payment_method" value="cash_on_delivery" class="mr-3 text-brand-darkgreen focus:ring-brand-darkgreen">
                        <div>
                            <div class="font-medium">Cash on Delivery</div>
                            <div class="text-sm text-gray-500">Pay when you receive your order</div>
                        </div>
                    </label>
                </div>
                
                @error('payment_method')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
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
                        <span class="text-gray-600">Tax (12% VAT)</span>
                        <span class="font-medium">₱{{ number_format($total * 0.12, 2) }}</span>
                    </div>
                    
                    <div class="border-t pt-3">
                        <div class="flex justify-between">
                            <span class="text-lg font-semibold">Total</span>
                            <span class="text-lg font-bold text-brand-darkgreen">₱{{ number_format($total * 1.12, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-3 border border-transparent text-base font-medium rounded-md text-white bg-brand-darkgreen hover:bg-brand-amber hover:text-brand-darkgreen transition-colors">
                        Place Order
                    </button>
                    
                    <p class="mt-3 text-xs text-gray-500 text-center">
                        By placing this order, you agree to our Terms of Service and Privacy Policy
                    </p>
                </div>

                <!-- Security Badge -->
                <div class="mt-6 flex items-center justify-center">
                    <div class="flex items-center text-xs text-gray-500">
                        <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                        Secure Checkout
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
