@extends('layouts.app')

@section('title', 'Order #' . $order->id . ' — LEBROn')

@section('header')
<x-page-header
    :title="'Order #' . $order->id"
    :subtitle="'Placed on ' . $order->created_at->format('M d, Y \a\t H:i')"
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Orders', 'url' => route('orders.index')],
        ['label' => 'Order #' . $order->id],
    ]"
>
    <x-slot:actions>
        @if($order->awaitsOnlinePayment() && auth()->id() === $order->user_id)
            <a href="{{ route('orders.pay', $order) }}" class="px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                Pay Now
            </a>
        @endif
        <a href="{{ route('orders.index') }}" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
            &larr; All Orders
        </a>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
@php
    $statusVariant = match($order->status) {
        'completed' => 'success',
        'shipped' => 'info',
        'processing' => 'warning',
        'cancelled' => 'danger',
        default => 'neutral',
    };
@endphp

<div class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
    {{-- Order Meta --}}
    <div class="p-6 border-b border-brand-border grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="space-y-1.5 text-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-brand-muted">Parties &amp; Delivery</p>
            <p class="text-brand-ink"><span class="text-brand-muted">Buyer:</span> <strong class="font-semibold">{{ $order->user->name }}</strong></p>
            @if($order->seller)
                <p class="text-brand-ink"><span class="text-brand-muted">Seller:</span> <strong class="font-semibold">{{ $order->seller->store_display_name }}</strong></p>
            @endif
            @if($order->shipping_address)
                <p class="text-brand-ink"><span class="text-brand-muted">Ship to:</span> {{ $order->shipping_address }}</p>
            @endif
        </div>

        <div class="space-y-2 text-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-brand-muted">Status &amp; Payment</p>
            <div class="flex flex-wrap items-center gap-2">
                <x-badge :variant="$statusVariant" dot>{{ ucfirst($order->status) }}</x-badge>
                <x-badge :variant="$order->payment_status === 'paid' ? 'success' : (in_array($order->payment_status, ['failed', 'expired']) ? 'danger' : 'warning')">
                    {{ $order->payment_status_label }}
                </x-badge>
            </div>
            <p class="text-xs text-brand-muted">
                Method: {{ $order->payment_method === 'paymongo' ? 'PayMongo Online' : 'Cash on Delivery' }}
                @if($order->paid_at)
                    &middot; Paid {{ $order->paid_at->format('M d, Y H:i') }}
                @endif
            </p>
        </div>

        <div class="md:text-right">
            <p class="text-xs font-bold uppercase tracking-wider text-brand-muted">Order Total</p>
            <p class="mt-1 text-3xl font-bold text-brand-darkgreen tabular-nums">₱{{ number_format($order->total_amount, 2) }}</p>
        </div>
    </div>

    {{-- Items Table --}}
    <div class="p-6">
        <h2 class="font-serif text-lg font-bold text-brand-ink mb-4">Order Items</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-brand-border text-sm">
                <thead class="bg-brand-cream/60">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Book</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-brand-muted">Qty</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Unit Price</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/70">
                    @foreach($order->orderItems as $item)
                        <tr>
                            <td class="px-4 py-3.5">
                                <a href="{{ route('books.show', $item->book) }}" class="font-semibold text-brand-ink hover:text-brand-darkgreen transition-colors">
                                    {{ $item->book->title }}
                                </a>
                                <p class="text-xs text-brand-muted">by {{ $item->book->author }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-center text-brand-muted tabular-nums">{{ $item->quantity }}</td>
                            <td class="px-4 py-3.5 text-right text-brand-muted tabular-nums">₱{{ number_format($item->price, 2) }}</td>
                            <td class="px-4 py-3.5 text-right font-semibold text-brand-ink tabular-nums">₱{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Admin / Seller Status Update --}}
    @auth
        @can('updateStatus', $order)
            <div class="p-6 bg-brand-paper border-t border-brand-border">
                <h2 class="font-serif text-base font-bold text-brand-ink mb-3">Update Fulfilment Status</h2>
                <form action="{{ route('orders.updateStatus', $order) }}" method="POST" class="flex flex-wrap items-end gap-3">
                    @csrf
                    @method('PATCH')
                    <div class="w-full sm:w-64">
                        <label for="status" class="block text-xs font-semibold text-brand-muted mb-1">Order Status</label>
                        <select name="status" id="status" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                            @foreach(\App\Models\Order::STATUSES as $status)
                                <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                        Save Status
                    </button>
                </form>
            </div>
        @endcan
    @endauth
</div>
@endsection
