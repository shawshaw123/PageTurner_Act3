@extends('layouts.app')

@section('title', 'My Dashboard - PageTurner')

@section('header')
<h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ $user->name }}! 👋</h1>
@endsection

@section('content')
{{-- Email Verification Alert --}}
@if(!$accountStatus['email_verified'])
<div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex items-center justify-between">
    <div class="flex items-center">
        <svg class="w-5 h-5 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span class="text-yellow-800 font-medium">Please verify your email to access all features (orders, reviews, cart).</span>
    </div>
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="bg-yellow-500 text-white px-4 py-1.5 rounded-md text-sm font-semibold hover:bg-yellow-600 transition">Verify Now</button>
    </form>
</div>
@endif

{{-- Account Status & Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-brand-darkgreen">
        <p class="text-sm text-gray-500 uppercase tracking-wide">Total Orders</p>
        <p class="text-3xl font-bold text-gray-800">{{ $orderSummary['total'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
        <p class="text-sm text-gray-500 uppercase tracking-wide">Pending</p>
        <p class="text-3xl font-bold text-gray-800">{{ $orderSummary['pending'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
        <p class="text-sm text-gray-500 uppercase tracking-wide">Completed</p>
        <p class="text-3xl font-bold text-gray-800">{{ $orderSummary['completed'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-brand-amber">
        <p class="text-sm text-gray-500 uppercase tracking-wide">Total Spent</p>
        <p class="text-2xl font-bold text-gray-800">₱{{ number_format($orderSummary['total_spent'], 2) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    {{-- Recent Orders --}}
    <div class="lg:col-span-2 bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">Recent Orders</h2>
            <a href="{{ route('orders.index') }}" class="text-sm text-brand-darkgreen hover:text-brand-amber font-medium">View All →</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recentOrders as $order)
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex justify-between items-center">
                        <div>
                            <a href="{{ route('orders.show', $order) }}" class="font-semibold text-gray-800 hover:text-brand-darkgreen">Order #{{ $order->id }}</a>
                            <p class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }} · {{ $order->orderItems->count() }} item(s)</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-800">₱{{ number_format($order->total_amount, 2) }}</p>
                            <span class="text-xs px-2 py-1 rounded-full
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
                <div class="p-6 text-center text-gray-400">
                    No orders yet. <a href="{{ route('books.index') }}" class="text-brand-darkgreen hover:text-brand-amber font-medium underline">Browse books</a> to get started!
                </div>
            @endforelse
        </div>
    </div>

    {{-- Account Status --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Account Security</h2>
        </div>
        <div class="p-6 space-y-4">
            {{-- Email Verification --}}
            <div class="flex items-center justify-between p-3 rounded-lg {{ $accountStatus['email_verified'] ? 'bg-green-50' : 'bg-red-50' }}">
                <div class="flex items-center space-x-3">
                    @if($accountStatus['email_verified'])
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-sm font-medium text-green-700">Email Verified</span>
                    @else
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="text-sm font-medium text-red-700">Email Not Verified</span>
                    @endif
                </div>
            </div>

            {{-- 2FA Status --}}
            <div class="flex items-center justify-between p-3 rounded-lg {{ $accountStatus['two_factor_enabled'] ? 'bg-green-50' : 'bg-yellow-50' }}">
                <div class="flex items-center space-x-3">
                    @if($accountStatus['two_factor_enabled'])
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span class="text-sm font-medium text-green-700">2FA Enabled</span>
                    @else
                        <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span class="text-sm font-medium text-yellow-700">2FA Disabled</span>
                    @endif
                </div>
                <a href="{{ route('two-factor.index') }}" class="text-xs text-brand-darkgreen hover:text-brand-amber font-medium underline">Manage</a>
            </div>

            {{-- Quick Links --}}
            <div class="pt-4 border-t border-gray-100 space-y-2">
                <a href="{{ route('books.index') }}" class="block p-3 bg-gray-50 rounded-lg hover:bg-brand-darkgreen hover:text-white transition-all font-medium text-sm">
                    📚 Browse Books
                </a>
                <a href="{{ route('orders.index') }}" class="block p-3 bg-gray-50 rounded-lg hover:bg-brand-darkgreen hover:text-white transition-all font-medium text-sm">
                    📦 Order History
                </a>
                <a href="{{ route('profile.edit') }}" class="block p-3 bg-gray-50 rounded-lg hover:bg-brand-darkgreen hover:text-white transition-all font-medium text-sm">
                    ⚙️ Profile & Security
                </a>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    {{-- Recently Purchased Books --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Recently Purchased Books</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recentBooks as $book)
                <div class="p-4 hover:bg-gray-50 transition-colors flex items-center space-x-4">
                    @if($book->cover_image)
                        <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="w-10 h-14 object-cover rounded shadow" loading="lazy">
                    @else
                        <div class="w-10 h-14 bg-gray-200 rounded flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                    @endif
                    <div>
                        <a href="{{ route('books.show', $book) }}" class="font-medium text-gray-800 hover:text-brand-darkgreen">{{ $book->title }}</a>
                        <p class="text-sm text-gray-500">{{ $book->author }}</p>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-400">No completed purchases yet.</div>
            @endforelse
        </div>
    </div>

    {{-- Review Activity --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">My Reviews ({{ $reviewCount }})</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($reviews as $review)
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex justify-between items-start">
                        <div>
                            <a href="{{ route('books.show', $review->book) }}" class="font-medium text-gray-800 hover:text-brand-darkgreen">{{ $review->book->title }}</a>
                            <p class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</p>
                            @if($review->comment)
                                <p class="text-sm text-gray-600 mt-1">{{ Str::limit($review->comment, 60) }}</p>
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
                <div class="p-6 text-center text-gray-400">No reviews yet. Purchase and read a book to share your thoughts!</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
