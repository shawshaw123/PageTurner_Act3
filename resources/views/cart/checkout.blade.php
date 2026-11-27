@extends('layouts.app')

@section('title', 'Checkout — LEBROn')

@section('header')
<x-page-header
    title="Checkout"
    subtitle="Confirm your items, enter your shipping address, and choose a payment method."
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Cart', 'url' => route('cart.index')],
        ['label' => 'Checkout'],
    ]"
/>
@endsection

@section('content')
<form action="{{ route('cart.process') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    @csrf

    <div class="lg:col-span-8 space-y-6">
        <!-- Order Items -->
        <section class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-brand-border flex items-center justify-between">
                <h2 class="font-serif text-lg font-bold text-brand-ink">1. Order Items</h2>
                <a href="{{ route('cart.index') }}" class="text-xs font-semibold text-brand-darkgreen hover:text-brand-amber">Edit Cart</a>
            </div>
            <div class="divide-y divide-brand-border/70">
                @foreach($cartItems as $item)
                    <div class="px-6 py-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="shrink-0 w-12 h-16 bg-brand-cream rounded-md overflow-hidden border border-brand-border flex items-center justify-center">
                                @if($item['book']->cover_image)
                                    <img src="{{ asset('storage/' . $item['book']->cover_image) }}" alt="Cover of {{ $item['book']->title }}" class="w-12 h-16 object-cover">
                                @else
                                    <svg class="w-5 h-5 text-brand-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-brand-ink truncate">{{ $item['book']->title }}</h3>
                                <p class="text-xs text-brand-muted">{{ $item['book']->author }}</p>
                                <p class="text-xs text-brand-muted mt-0.5 tabular-nums">Qty {{ $item['quantity'] }} &times; ₱{{ number_format($item['book']->price, 2) }}</p>
                            </div>
                        </div>
                        <p class="text-sm font-bold text-brand-ink tabular-nums shrink-0">₱{{ number_format($item['subtotal'], 2) }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Shipping Address -->
        <section class="bg-white rounded-2xl border border-brand-border shadow-xs p-6">
            <h2 class="font-serif text-lg font-bold text-brand-ink mb-4">2. Shipping Information</h2>
            <div>
                <label for="shipping_address" class="block text-sm font-medium text-brand-ink mb-1.5">
                    Delivery Address <span class="text-red-500">*</span>
                </label>
                <textarea id="shipping_address"
                          name="shipping_address"
                          rows="3"
                          required
                          placeholder="Street address, barangay/unit, city, province, postal code"
                          class="w-full rounded-xl border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen @error('shipping_address') border-red-400 @enderror">{{ old('shipping_address') }}</textarea>
                <p class="mt-1.5 text-xs text-brand-muted">Include recipient name, complete street address, and contact instructions if needed.</p>
                @error('shipping_address')
                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </section>

        <!-- Payment Method -->
        <section class="bg-white rounded-2xl border border-brand-border shadow-xs p-6">
            <h2 class="font-serif text-lg font-bold text-brand-ink mb-4">3. Payment Method</h2>
            @php $onlinePayment = app(\App\Integrations\PayMongo\PaymentService::class)->isConfigured(); @endphp

            <div class="space-y-3">
                <label class="flex items-start gap-3 p-4 rounded-xl border border-brand-border {{ $onlinePayment ? 'cursor-pointer hover:bg-brand-paper has-[:checked]:border-brand-darkgreen has-[:checked]:bg-brand-cream/60' : 'opacity-60 cursor-not-allowed bg-stone-50' }} transition-colors">
                    <input type="radio" name="payment_method" value="paymongo" class="mt-1 text-brand-darkgreen focus:ring-brand-darkgreen" @checked($onlinePayment && old('payment_method', 'paymongo') === 'paymongo') @disabled(!$onlinePayment)>
                    <div>
                        <div class="text-sm font-semibold text-brand-ink">Pay online with PayMongo</div>
                        <div class="text-xs text-brand-muted mt-0.5">
                            @if($onlinePayment)
                                GCash, Maya, GrabPay, QR Ph, or credit/debit card on PayMongo's secure checkout page.
                            @else
                                Online payment is currently unavailable.
                            @endif
                        </div>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-4 rounded-xl border border-brand-border cursor-pointer hover:bg-brand-paper has-[:checked]:border-brand-darkgreen has-[:checked]:bg-brand-cream/60 transition-colors">
                    <input type="radio" name="payment_method" value="cash_on_delivery" class="mt-1 text-brand-darkgreen focus:ring-brand-darkgreen" @checked(!$onlinePayment || old('payment_method') === 'cash_on_delivery')>
                    <div>
                        <div class="text-sm font-semibold text-brand-ink">Cash on Delivery</div>
                        <div class="text-xs text-brand-muted mt-0.5">Pay in cash when your books arrive at your doorstep.</div>
                    </div>
                </label>
            </div>
            @error('payment_method')
                <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
            @enderror
        </section>
    </div>

    <!-- Summary Sidebar -->
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
                <dt class="text-base font-bold text-brand-ink">Total Due</dt>
                <dd class="text-xl font-bold text-brand-darkgreen tabular-nums">₱{{ number_format($total * 1.12, 2) }}</dd>
            </div>
        </dl>

        <button type="submit" class="mt-6 w-full inline-flex items-center justify-center px-5 py-3 rounded-xl bg-brand-darkgreen text-white font-semibold text-sm hover:bg-brand-forest transition-colors shadow-xs">
            Place Order
        </button>

        <div class="mt-4 flex items-center justify-center gap-1.5 text-xs text-brand-muted">
            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
            </svg>
            <span>Encrypted, secure checkout</span>
        </div>
    </aside>
</form>
@endsection
