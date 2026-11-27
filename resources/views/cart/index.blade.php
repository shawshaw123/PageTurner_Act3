@extends('layouts.app')

@section('title', 'Shopping Cart — LEBROn')

@section('header')
<x-page-header
    title="Shopping Cart"
    subtitle="Review your selected books and quantities before checkout."
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Cart'],
    ]"
>
    @if(!empty($cartItems))
        <x-slot:actions>
            <form action="{{ route('cart.clear') }}" method="POST" data-confirm="Remove all items from your shopping cart?">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3.5 py-2 rounded-lg border border-red-200 bg-red-50 text-red-700 text-xs font-semibold hover:bg-red-100 transition-colors">
                    Clear Cart
                </button>
            </form>
        </x-slot:actions>
    @endif
</x-page-header>
@endsection

@section('content')
@if(empty($cartItems))
    <x-empty-state
        title="Your shopping cart is empty"
        description="Looks like you haven't added any books yet. Explore our catalog to find your next great read."
    >
        <x-slot:icon>
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </x-slot:icon>
        <x-slot:action>
            <a href="{{ route('books.index') }}" class="px-5 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                Browse Books
            </a>
        </x-slot:action>
    </x-empty-state>
@else
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Cart Items -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-brand-border shadow-xs divide-y divide-brand-border overflow-hidden">
            @foreach($cartItems as $item)
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <!-- Cover -->
                        <a href="{{ route('books.show', $item['book']) }}" class="shrink-0 w-16 h-22 bg-brand-cream rounded-lg overflow-hidden flex items-center justify-center border border-brand-border">
                            @if($item['book']->cover_image)
                                <img src="{{ asset('storage/' . $item['book']->cover_image) }}" alt="Cover of {{ $item['book']->title }}" class="w-16 h-22 object-cover">
                            @else
                                <svg class="w-7 h-7 text-brand-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            @endif
                        </a>

                        <!-- Details -->
                        <div class="flex-1 min-w-0">
                            <h3 class="font-serif text-base font-bold text-brand-ink truncate">
                                <a href="{{ route('books.show', $item['book']) }}" class="hover:text-brand-darkgreen transition-colors">
                                    {{ $item['book']->title }}
                                </a>
                            </h3>
                            <p class="text-xs text-brand-muted mt-0.5">{{ $item['book']->author }} &middot; {{ $item['book']->category->name }}</p>
                            <div class="mt-2 flex flex-wrap items-baseline gap-2">
                                <span class="text-sm font-semibold text-brand-darkgreen tabular-nums">₱{{ number_format($item['book']->price, 2) }}</span>
                                <span class="text-xs text-brand-muted">&times; {{ $item['quantity'] }} =</span>
                                <span class="text-base font-bold text-brand-ink tabular-nums">₱{{ number_format($item['subtotal'], 2) }}</span>
                            </div>
                        </div>

                        <!-- Quantity & Remove -->
                        <div class="flex items-center gap-3 self-end sm:self-center">
                            <form action="{{ route('cart.update', $item['book']) }}" method="POST" class="flex items-center update-cart-form" data-no-loading>
                                @csrf
                                @method('PUT')
                                <div class="inline-flex items-center rounded-lg border border-brand-border bg-brand-paper overflow-hidden">
                                    <button type="button" onclick="decrement(this)" class="px-3 py-1.5 text-brand-ink hover:bg-brand-cream transition-colors font-semibold" aria-label="Decrease quantity">&minus;</button>
                                    <input type="number"
                                           name="quantity"
                                           value="{{ $item['quantity'] }}"
                                           min="0"
                                           max="{{ $item['book']->stock_quantity }}"
                                           onchange="this.form.submit()"
                                           aria-label="Quantity for {{ $item['book']->title }}"
                                           class="w-12 py-1.5 border-0 bg-transparent text-center text-sm font-semibold text-brand-ink focus:ring-0">
                                    <button type="button" onclick="increment(this)" class="px-3 py-1.5 text-brand-ink hover:bg-brand-cream transition-colors font-semibold" aria-label="Increase quantity">+</button>
                                </div>
                                <button type="submit" class="hidden">Update</button>
                            </form>

                            <form action="{{ route('cart.remove', $item['book']) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg text-brand-muted hover:text-red-600 hover:bg-red-50 transition-colors" aria-label="Remove {{ $item['book']->title }} from cart">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.85" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    @if($item['quantity'] >= $item['book']->stock_quantity)
                        <p class="mt-3 text-xs font-medium text-amber-800 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-lg inline-block">
                            Maximum available stock reached ({{ $item['book']->stock_quantity }} copies in stock).
                        </p>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Order Summary -->
        <aside class="lg:col-span-4 bg-white rounded-2xl border border-brand-border shadow-xs p-6 lg:sticky lg:top-24">
            <h2 class="font-serif text-lg font-bold text-brand-ink mb-4">Order Summary</h2>

            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-brand-muted">Subtotal ({{ count($cartItems) }} {{ Str::plural('item', count($cartItems)) }})</dt>
                    <dd class="font-medium text-brand-ink tabular-nums">₱{{ number_format($total, 2) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-brand-muted">Shipping</dt>
                    <dd class="font-medium text-emerald-700">Free</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-brand-muted">VAT (12%)</dt>
                    <dd class="font-medium text-brand-ink tabular-nums">₱{{ number_format($total * 0.12, 2) }}</dd>
                </div>
                <div class="border-t border-brand-border pt-3 flex justify-between items-baseline">
                    <dt class="text-base font-bold text-brand-ink">Total</dt>
                    <dd class="text-xl font-bold text-brand-darkgreen tabular-nums">₱{{ number_format($total * 1.12, 2) }}</dd>
                </div>
            </dl>

            <div class="mt-6 space-y-2.5">
                <a href="{{ route('cart.checkout') }}" class="w-full inline-flex items-center justify-center px-5 py-3 rounded-xl bg-brand-darkgreen text-white font-semibold text-sm hover:bg-brand-forest transition-colors shadow-xs">
                    Proceed to Checkout
                </a>
                <a href="{{ route('books.index') }}" class="w-full inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-brand-border bg-white text-brand-ink font-medium text-sm hover:bg-brand-cream transition-colors">
                    Continue Shopping
                </a>
            </div>

            <ul class="mt-6 pt-5 border-t border-brand-border space-y-1.5 text-xs text-brand-muted">
                <li>&bull; Free delivery on all bookstore orders</li>
                <li>&bull; 12% VAT included in total</li>
                <li>&bull; Pay online via PayMongo or Cash on Delivery</li>
            </ul>
        </aside>
    </div>
@endif
@endsection

@push('scripts')
<script>
    function increment(btn) {
        const input = btn.previousElementSibling;
        const max = parseInt(input.getAttribute('max'), 10);
        const value = parseInt(input.value, 10);
        if (isNaN(max) || value < max) {
            input.value = value + 1;
            input.form.submit();
        }
    }

    function decrement(btn) {
        const input = btn.nextElementSibling;
        const value = parseInt(input.value, 10);
        if (value > 0) {
            input.value = value - 1;
            input.form.submit();
        }
    }
</script>
@endpush
