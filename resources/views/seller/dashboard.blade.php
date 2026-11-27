@extends('layouts.app')

@section('title', 'Seller Dashboard — LEBROn')

@section('header')
<x-page-header
    :title="$seller->store_display_name"
    subtitle="Seller hub — manage your listings, inventory levels, and customer orders."
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Seller Hub'],
    ]"
>
    @if($approved)
        <x-slot:actions>
            <a href="{{ route('books.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Listing
            </a>
        </x-slot:actions>
    @endif
</x-page-header>
@endsection

@section('content')
@if(!$approved)
    <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-8 max-w-2xl">
        @if($seller->seller_status === 'suspended')
            <x-badge variant="danger" dot class="mb-3">Suspended</x-badge>
            <h2 class="font-serif text-xl font-bold text-red-800">Your seller account is suspended</h2>
            <p class="text-sm text-brand-muted mt-2">Your listings are hidden from buyers and you cannot publish new books. Please contact an administrator for assistance.</p>
        @else
            <x-badge variant="warning" dot class="mb-3">Pending Review</x-badge>
            <h2 class="font-serif text-xl font-bold text-brand-ink">Your seller account is awaiting approval</h2>
            <p class="text-sm text-brand-muted mt-2">An administrator reviews every new bookstore account. Once approved, you can add listings and receive orders.</p>
        @endif
        <dl class="mt-6 pt-5 border-t border-brand-border grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-brand-muted">Store Name</dt>
                <dd class="mt-1 font-semibold text-brand-ink">{{ $seller->store_display_name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-brand-muted">Status</dt>
                <dd class="mt-1 font-semibold text-brand-ink">{{ ucfirst($seller->seller_status ?? 'pending') }}</dd>
            </div>
        </dl>
    </div>
@else
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <x-stat-card label="Listings" :value="$stats['listings']" accent="brand" :href="route('seller.books')" />
        <x-stat-card label="Low Stock (≤ 5)" :value="$stats['low_stock']" accent="danger" />
        <x-stat-card label="New Orders" :value="$stats['pending_orders']" accent="warning" :href="route('orders.index')" />
        <x-stat-card label="To Ship" :value="$stats['to_ship']" accent="info" :href="route('orders.index')" />
        <x-stat-card label="Completed Sales" :value="'₱' . number_format($stats['revenue'], 2)" accent="amber" class="col-span-2 lg:col-span-1" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <section class="lg:col-span-2 bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-brand-border">
                <h2 class="font-serif text-lg font-bold text-brand-ink">Recent Orders</h2>
                <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-brand-darkgreen hover:text-brand-amber">View all &rarr;</a>
            </div>
            @if($recentOrders->isEmpty())
                <div class="p-8 text-center text-sm text-brand-muted">No orders yet. They will appear here as buyers purchase your books.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-brand-border text-sm">
                        <thead class="bg-brand-cream/60">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Order</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Buyer</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Total</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border/70">
                            @foreach($recentOrders as $order)
                                <tr class="hover:bg-brand-paper/60 transition-colors">
                                    <td class="px-6 py-3.5">
                                        <a href="{{ route('orders.show', $order) }}" class="font-mono font-semibold text-brand-darkgreen hover:text-brand-amber">#{{ $order->id }}</a>
                                        <span class="block text-xs text-brand-muted">{{ $order->created_at->format('M d, Y') }} &middot; {{ $order->order_items_count }} {{ Str::plural('item', $order->order_items_count) }}</span>
                                    </td>
                                    <td class="px-6 py-3.5 text-brand-ink">{{ $order->user->name }}</td>
                                    <td class="px-6 py-3.5 text-right font-semibold tabular-nums">₱{{ number_format($order->total_amount, 2) }}</td>
                                    <td class="px-6 py-3.5">
                                        <x-badge :variant="match($order->status) { 'completed' => 'success', 'shipped' => 'info', 'cancelled' => 'danger', default => 'warning' }">
                                            {{ ucfirst($order->status) }}
                                        </x-badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-brand-border">
                <h2 class="font-serif text-lg font-bold text-brand-ink">Low Stock</h2>
                <a href="{{ route('seller.books') }}" class="text-xs font-semibold text-brand-darkgreen hover:text-brand-amber">Manage &rarr;</a>
            </div>
            @if($lowStockBooks->isEmpty())
                <div class="p-8 text-center text-sm text-brand-muted">All listings have more than 5 copies in stock.</div>
            @else
                <ul class="divide-y divide-brand-border/70">
                    @foreach($lowStockBooks as $book)
                        <li class="px-6 py-3.5 flex items-center justify-between gap-3 text-sm hover:bg-brand-paper/60 transition-colors">
                            <a href="{{ route('books.edit', $book) }}" class="font-medium text-brand-ink hover:text-brand-darkgreen truncate">{{ $book->title }}</a>
                            <x-badge :variant="$book->stock_quantity === 0 ? 'danger' : 'warning'" class="shrink-0">
                                {{ $book->stock_quantity === 0 ? 'Out of stock' : $book->stock_quantity . ' left' }}
                            </x-badge>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endif
@endsection
