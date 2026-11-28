@extends('layouts.app')

@section('title', 'PayMongo Sandbox Checkout — PageTurner')

@section('header')
<x-page-header
    title="PayMongo Local Sandbox Checkout"
    :subtitle="'Checkout Reference: ' . $payment->checkout_reference"
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Orders', 'url' => route('orders.index')],
        ['label' => 'PayMongo Sandbox'],
    ]"
/>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-alert type="info" title="Local Sandbox Mode">
        This simulates PayMongo's hosted checkout page locally so you can test online payments without needing a live PayMongo account.
    </x-alert>

    <div class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-5 bg-brand-darkgreen text-white flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wider text-brand-gold font-semibold">PayMongo Test Checkout</p>
                <p class="font-mono text-xs text-white/75 mt-0.5">{{ $payment->checkout_session_id }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-white/75">Amount Due</p>
                <p class="text-2xl font-bold tabular-nums">₱{{ number_format($payment->amount, 2) }}</p>
            </div>
        </div>

        <div class="p-6 space-y-6">
            {{-- Line Items --}}
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-brand-muted mb-3">Order Summary</h2>
                <ul class="divide-y divide-brand-border/70 text-sm">
                    @foreach($orders as $order)
                        @foreach($order->orderItems as $item)
                            <li class="py-2.5 flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-brand-ink">{{ $item->book?->title ?? 'Book' }}</p>
                                    <p class="text-xs text-brand-muted">Order #{{ $order->id }} &middot; Qty {{ $item->quantity }}</p>
                                </div>
                                <span class="font-semibold tabular-nums text-brand-ink">₱{{ number_format($item->subtotal, 2) }}</span>
                            </li>
                        @endforeach
                    @endforeach
                </ul>
            </div>

            {{-- Simulated Payment Form --}}
            <form action="{{ route('payments.demo.complete', $payment->checkout_reference) }}" method="POST" class="space-y-5 pt-4 border-t border-brand-border">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-brand-muted mb-2.5">Select Payment Channel</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        @foreach($methods as $method)
                            <label class="flex items-center gap-2 p-3 rounded-xl border border-brand-border cursor-pointer hover:bg-brand-paper has-[:checked]:border-brand-darkgreen has-[:checked]:bg-brand-cream/70 transition-colors">
                                <input type="radio" name="channel" value="{{ $method }}" @checked($loop->first) class="text-brand-darkgreen focus:ring-brand-darkgreen">
                                <span class="text-xs font-semibold uppercase text-brand-ink">{{ str_replace('_', ' ', $method) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <button type="submit" name="action" value="pay" class="flex-1 px-5 py-3 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors shadow-xs">
                        Complete Payment (₱{{ number_format($payment->amount, 2) }})
                    </button>
                    <button type="submit" name="action" value="fail" class="px-4 py-3 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm font-semibold hover:bg-red-100 transition-colors">
                        Simulate Decline
                    </button>
                </div>
            </form>

            <div class="text-center pt-2">
                <a href="{{ route('payments.cancelled', $payment->checkout_reference) }}" class="text-xs font-medium text-brand-muted hover:text-brand-ink underline">
                    Cancel and return to PageTurner
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
