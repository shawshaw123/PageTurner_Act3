@extends('layouts.app')

@section('title', 'Payment - PageTurner')

@section('header')
<h1 class="text-3xl font-bold text-gray-900">
    @if($payment->status === 'paid') Payment received
    @elseif($payment->status === 'expired') Payment expired
    @elseif($cameBack === 'cancelled') Payment not completed
    @else Confirming your payment…
    @endif
</h1>
@endsection

@section('content')
<div class="max-w-2xl space-y-6">
    <div class="bg-white rounded-lg shadow p-6" role="status" aria-live="polite">
        @if($payment->status === 'paid')
            <p class="text-green-800 font-medium">Thank you! PayMongo confirmed your payment of ₱{{ number_format($payment->amount, 2) }}. The sellers have been notified.</p>
        @elseif($payment->status === 'expired')
            <p class="text-gray-700">The time to pay ran out, so the order was cancelled and the books were put back on sale.</p>
        @elseif($payment->status === 'failed')
            <p class="text-red-700">The last payment attempt failed{{ $payment->failure_reason ? ': ' . $payment->failure_reason : '' }}. You can try again.</p>
        @elseif($cameBack === 'cancelled')
            <p class="text-gray-700">You left the payment page before paying. Your order is kept for {{ config('services.paymongo.payment_window_minutes') }} minutes from checkout; you can pay any time before then.</p>
        @else
            <p class="text-gray-700">We're waiting for PayMongo to confirm your payment. This usually takes a few seconds; this page checks again automatically.</p>
        @endif

        @if($payment->isOpen() && $payment->checkout_url)
            <a href="{{ $payment->checkout_url }}" class="inline-block mt-4 bg-brand-darkgreen text-white px-5 py-2 rounded-md font-semibold hover:bg-brand-amber hover:text-brand-darkgreen transition-colors">
                {{ $payment->status === 'failed' || $cameBack === 'cancelled' ? 'Try paying again' : 'Back to the payment page' }}
            </a>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow">
        <h2 class="text-lg font-semibold text-gray-900 px-6 py-4 border-b">
            {{ $orders->count() === 1 ? 'Your order' : 'Your orders (one per seller)' }}
        </h2>
        <ul class="divide-y divide-gray-100">
            @foreach($orders as $order)
                <li class="px-6 py-3 flex items-center justify-between text-sm">
                    <div>
                        <a href="{{ route('orders.show', $order) }}" class="font-medium text-brand-darkgreen hover:text-brand-amber">Order #{{ $order->id }}</a>
                        <span class="block text-gray-500">{{ $order->seller?->store_display_name }}</span>
                    </div>
                    <div class="text-right">
                        <span class="block tabular-nums">₱{{ number_format($order->total_amount, 2) }}</span>
                        <span class="text-xs font-semibold {{ $order->payment_status === 'paid' ? 'text-green-700' : ($order->payment_status === 'failed' || $order->payment_status === 'expired' ? 'text-red-600' : 'text-amber-700') }}">{{ $order->payment_status_label }}</span>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>

@if($payment->isOpen() && $cameBack === 'return')
    {{-- Re-check every 5 seconds for up to a minute while the webhook arrives. --}}
    <script>
        (function () {
            var key = 'payment-checks-{{ $payment->id }}';
            var checks = parseInt(sessionStorage.getItem(key) || '0', 10);
            if (checks < 12) {
                sessionStorage.setItem(key, checks + 1);
                setTimeout(function () { location.reload(); }, 5000);
            }
        })();
    </script>
@endif
@endsection
