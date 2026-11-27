@extends('layouts.app')

@section('title', 'Seller Dashboard - PageTurner')

@section('header')
<h1 class="text-3xl font-bold text-gray-900">{{ $seller->store_display_name }}</h1>
<p class="text-gray-500 mt-1">Seller dashboard</p>
@endsection

@section('content')
@if(!$approved)
    {{-- Pending or suspended seller --}}
    <div class="bg-white rounded-lg shadow p-8 max-w-2xl">
        @if($seller->seller_status === 'suspended')
            <h2 class="text-xl font-semibold text-red-700">Your seller account is suspended</h2>
            <p class="text-gray-600 mt-2">Your listings are hidden from buyers and you cannot add new ones. Please contact an administrator.</p>
        @else
            <h2 class="text-xl font-semibold text-gray-900">Your seller account is awaiting approval</h2>
            <p class="text-gray-600 mt-2">An administrator reviews every new seller. Once approved, you can add book listings and receive orders. You don't need to do anything else for now.</p>
        @endif
        <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Store name</dt>
                <dd class="font-medium text-gray-900">{{ $seller->store_display_name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Status</dt>
                <dd class="font-medium text-gray-900">{{ ucfirst($seller->seller_status ?? 'pending') }}</dd>
            </div>
        </dl>
    </div>
@else
    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <a href="{{ route('seller.books') }}" class="bg-white rounded-lg shadow p-5 border-l-4 border-brand-darkgreen hover:shadow-md transition">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Listings</p>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['listings'] }}</p>
        </a>
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-red-400">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Low stock (≤ 5)</p>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['low_stock'] }}</p>
        </div>
        <a href="{{ route('orders.index') }}" class="bg-white rounded-lg shadow p-5 border-l-4 border-yellow-500 hover:shadow-md transition">
            <p class="text-xs text-gray-500 uppercase tracking-wide">New orders</p>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['pending_orders'] }}</p>
        </a>
        <a href="{{ route('orders.index') }}" class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500 hover:shadow-md transition">
            <p class="text-xs text-gray-500 uppercase tracking-wide">To ship</p>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['to_ship'] }}</p>
        </a>
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-brand-amber col-span-2 lg:col-span-1">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Completed sales</p>
            <p class="text-2xl font-bold text-gray-800">₱{{ number_format($stats['revenue'], 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent orders --}}
        <div class="lg:col-span-2 bg-white rounded-lg shadow">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Recent orders</h2>
                <a href="{{ route('orders.index') }}" class="text-sm text-brand-darkgreen hover:text-brand-amber font-medium">View all</a>
            </div>
            @if($recentOrders->isEmpty())
                <p class="px-6 py-8 text-gray-500 text-sm">No orders yet. They will appear here when buyers purchase your books.</p>
            @else
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                            <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase">Buyer</th>
                            <th class="px-6 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentOrders as $order)
                            <tr>
                                <td class="px-6 py-3">
                                    <a href="{{ route('orders.show', $order) }}" class="text-brand-darkgreen hover:text-brand-amber font-medium">#{{ $order->id }}</a>
                                    <span class="block text-xs text-gray-500">{{ $order->created_at->format('M d, Y') }} · {{ $order->order_items_count }} {{ Str::plural('item', $order->order_items_count) }}</span>
                                </td>
                                <td class="px-6 py-3 text-gray-700">{{ $order->user->name }}</td>
                                <td class="px-6 py-3 text-right tabular-nums">₱{{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">{{ ucfirst($order->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Low stock --}}
        <div class="bg-white rounded-lg shadow">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Low stock</h2>
                <a href="{{ route('books.create') }}" class="text-sm bg-brand-darkgreen text-white px-3 py-1.5 rounded-md hover:bg-brand-amber hover:text-brand-darkgreen transition-colors">Add listing</a>
            </div>
            @if($lowStockBooks->isEmpty())
                <p class="px-6 py-8 text-gray-500 text-sm">All your listings have more than 5 copies in stock.</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($lowStockBooks as $book)
                        <li class="px-6 py-3 flex items-center justify-between text-sm">
                            <a href="{{ route('books.edit', $book) }}" class="text-gray-800 hover:text-brand-darkgreen truncate mr-3">{{ $book->title }}</a>
                            <span class="shrink-0 font-semibold {{ $book->stock_quantity === 0 ? 'text-red-600' : 'text-amber-600' }}">{{ $book->stock_quantity === 0 ? 'Out of stock' : $book->stock_quantity . ' left' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endif
@endsection
