@extends('layouts.app')

@section('title', 'Orders — PageTurner')

@section('header')
<x-page-header
    :title="auth()->user()->isAdmin() ? 'All Orders' : (auth()->user()->isSeller() ? 'Orders to Fulfil' : 'My Orders')"
    :subtitle="auth()->user()->isAdmin() ? 'Monitor and manage customer orders across the store.' : (auth()->user()->isSeller() ? 'Track and update fulfilment status for your book sales.' : 'View your purchase history, payment status, and deliveries.')"
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Orders'],
    ]"
/>
@endsection

@section('content')
@if($orders->count() > 0)
    <div class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-brand-border text-sm">
                <thead class="bg-brand-cream/70">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Order</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Date</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Total</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Status &amp; Payment</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/70">
                    @foreach($orders as $order)
                        @php
                            $statusVariant = match($order->status) {
                                'completed' => 'success',
                                'shipped' => 'info',
                                'processing' => 'warning',
                                'cancelled' => 'danger',
                                default => 'neutral',
                            };
                        @endphp
                        <tr class="hover:bg-brand-paper/60 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('orders.show', $order) }}" class="font-mono font-semibold text-brand-ink hover:text-brand-darkgreen">#{{ $order->id }}</a>
                                <span class="block text-xs text-brand-muted mt-0.5">
                                    @if(auth()->user()->isSeller() || auth()->user()->isAdmin())
                                        Buyer: {{ $order->user->name ?? 'N/A' }}
                                    @elseif($order->seller)
                                        Seller: {{ $order->seller->store_display_name }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-brand-muted">
                                {{ $order->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-brand-ink tabular-nums">
                                ₱{{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge :variant="$statusVariant" dot>{{ ucfirst($order->status) }}</x-badge>
                                <span class="block text-xs mt-1 {{ $order->payment_status === 'paid' ? 'text-emerald-700 font-medium' : (in_array($order->payment_status, ['failed', 'expired']) ? 'text-red-600 font-medium' : 'text-brand-muted') }}">
                                    {{ $order->payment_status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium">
                                <div class="inline-flex items-center gap-2.5">
                                    <a href="{{ route('orders.show', $order) }}" class="text-brand-darkgreen hover:text-brand-amber font-semibold transition-colors">
                                        Details
                                    </a>
                                    @can('updateStatus', $order)
                                        <form action="{{ route('orders.updateStatus', $order) }}" method="POST" class="inline" data-no-loading>
                                            @csrf
                                            @method('PUT')
                                            <select name="status" onchange="this.form.submit()" aria-label="Update status for order #{{ $order->id }}" class="text-xs rounded-lg border-brand-border py-1 pl-2.5 pr-7 focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                                                @foreach(\App\Models\Order::STATUSES as $status)
                                                    <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @elsecan('cancel', $order)
                                        <form action="{{ route('orders.cancel', $order) }}" method="POST" class="inline" data-confirm="Cancel order #{{ $order->id }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800">Cancel</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8">
        {{ $orders->links() }}
    </div>
@else
    <x-empty-state
        title="No orders yet"
        description="Once an order is placed, you'll be able to track its status and payment details right here."
    >
        <x-slot:action>
            <a href="{{ route('books.index') }}" class="px-5 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                Browse Books
            </a>
        </x-slot:action>
    </x-empty-state>
@endif
@endsection
