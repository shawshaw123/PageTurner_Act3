@extends('layouts.app')

@section('title', 'Admin Dashboard - PageTurner')

@section('header')
<div class="flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
    <span class="bg-brand-amber text-brand-darkgreen px-3 py-1 rounded-full text-sm font-semibold">Administrator</span>
</div>
@endsection

@section('content')
{{-- Stats Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    {{-- Total Users --}}
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 uppercase tracking-wide">Users</p>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['total_users']) }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
        </div>
    </div>

    {{-- Total Books --}}
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 uppercase tracking-wide">Books</p>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['total_books']) }}</p>
            </div>
            <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
        </div>
    </div>

    {{-- Total Categories --}}
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 uppercase tracking-wide">Categories</p>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['total_categories']) }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-50 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
        </div>
    </div>

    {{-- Total Orders --}}
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-brand-amber">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 uppercase tracking-wide">Orders</p>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['total_orders']) }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-50 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-brand-amber" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
        </div>
    </div>

    {{-- Revenue --}}
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-brand-darkgreen">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 uppercase tracking-wide">Revenue</p>
                <p class="text-2xl font-bold text-gray-800">₱{{ number_format($stats['total_revenue'], 2) }}</p>
            </div>
            <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-brand-darkgreen" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
</div>

{{-- Order Status Summary --}}
<div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
        <p class="text-2xl font-bold text-yellow-700">{{ $orderStatuses['pending'] }}</p>
        <p class="text-sm text-yellow-600">Pending</p>
    </div>
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
        <p class="text-2xl font-bold text-blue-700">{{ $orderStatuses['processing'] }}</p>
        <p class="text-sm text-blue-600">Processing</p>
    </div>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
        <p class="text-2xl font-bold text-green-700">{{ $orderStatuses['completed'] }}</p>
        <p class="text-sm text-green-600">Completed</p>
    </div>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
        <p class="text-2xl font-bold text-red-700">{{ $orderStatuses['cancelled'] }}</p>
        <p class="text-sm text-red-600">Cancelled</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    {{-- Recent Orders --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">Recent Orders</h2>
            <a href="{{ route('orders.index') }}" class="text-sm text-brand-darkgreen hover:text-brand-amber font-medium">View All →</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recentOrders as $order)
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex justify-between items-center">
                        <div>
                            <a href="{{ route('orders.show', $order) }}" class="font-semibold text-gray-800 hover:text-brand-darkgreen">#{{ $order->id }}</a>
                            <p class="text-sm text-gray-500">{{ $order->user->name ?? 'N/A' }} · {{ $order->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-800">₱{{ number_format($order->total_amount, 2) }}</p>
                            <span class="text-xs px-2 py-1 rounded-full inline-block
                                @if($order->status == 'completed') bg-green-100 text-green-700
                                @elseif($order->status == 'processing') bg-blue-100 text-blue-700
                                @elseif($order->status == 'cancelled') bg-red-100 text-red-700
                                @else bg-yellow-100 text-yellow-700 @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-400">No orders yet.</div>
            @endforelse
        </div>
    </div>

    {{-- Recent Reviews --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Recent Reviews</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recentReviews as $review)
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex justify-between items-start">
                        <div>
                            <a href="{{ route('books.show', $review->book) }}" class="font-medium text-gray-800 hover:text-brand-darkgreen">{{ $review->book->title }}</a>
                            <p class="text-sm text-gray-500">by {{ $review->user->name }} · {{ $review->created_at->diffForHumans() }}</p>
                            @if($review->comment)
                                <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ Str::limit($review->comment, 80) }}</p>
                            @endif
                        </div>
                        <div class="flex items-center text-brand-amber">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'fill-current' : 'text-gray-300' }}" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                            @endfor
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-400">No reviews yet.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Quick Navigation & Low Stock --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    {{-- Quick Links --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Quick Actions</h2>
        </div>
        <div class="p-6 grid grid-cols-2 gap-4">
            <a href="{{ route('books.index') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-brand-darkgreen hover:text-white transition-all group">
                <svg class="w-5 h-5 mr-3 text-brand-darkgreen group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span class="font-medium">Manage Books</span>
            </a>
            <a href="{{ route('categories.index') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-brand-darkgreen hover:text-white transition-all group">
                <svg class="w-5 h-5 mr-3 text-brand-darkgreen group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                <span class="font-medium">Categories</span>
            </a>
            <a href="{{ route('orders.index') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-brand-darkgreen hover:text-white transition-all group">
                <svg class="w-5 h-5 mr-3 text-brand-darkgreen group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span class="font-medium">Manage Orders</span>
            </a>
            <a href="{{ route('books.create') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-brand-amber hover:text-brand-darkgreen transition-all group">
                <svg class="w-5 h-5 mr-3 text-brand-darkgreen" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="font-medium">Add New Book</span>
            </a>
            <a href="{{ route('admin.import-export.index') }}" class="flex items-center p-4 bg-gradient-to-r from-green-50 to-teal-50 rounded-lg hover:from-green-600 hover:to-teal-600 hover:text-white transition-all group border-t-2 border-green-500 sm:border-t-0 lg:border-t-0 shadow-sm">
                <svg class="w-5 h-5 mr-3 text-green-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                <span class="font-medium">Import Books</span>
            </a>
            <a href="{{ route('admin.import-export.index') }}" class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg hover:from-blue-600 hover:to-indigo-600 hover:text-white transition-all group shadow-sm">
                <svg class="w-5 h-5 mr-3 text-blue-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="font-medium">Export Data</span>
            </a>
            <a href="{{ route('admin.backup.index') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-brand-darkgreen hover:text-white transition-all group">
                <svg class="w-5 h-5 mr-3 text-brand-darkgreen group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                <span class="font-medium">Backup Management</span>
            </a>
            <a href="{{ route('admin.audit.index') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-brand-darkgreen hover:text-white transition-all group">
                <svg class="w-5 h-5 mr-3 text-brand-darkgreen group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <span class="font-medium">Audit Logs</span>
            </a>
        </div>
    </div>

    {{-- Low Stock Alert --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">⚠️ Low Stock Alert</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($lowStockBooks as $book)
                <div class="p-4 hover:bg-gray-50 transition-colors flex justify-between items-center">
                    <div>
                        <a href="{{ route('books.show', $book) }}" class="font-medium text-gray-800 hover:text-brand-darkgreen">{{ $book->title }}</a>
                        <p class="text-sm text-gray-500">{{ $book->author }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $book->stock_quantity == 0 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $book->stock_quantity }} left
                    </span>
                </div>
            @empty
                <div class="p-6 text-center text-gray-400">All books are well stocked! 👍</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
