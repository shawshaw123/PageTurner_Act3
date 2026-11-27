@extends('layouts.app')

@section('title', 'My Dashboard — LEBROn')

@section('header')
<x-page-header
    :title="'Welcome back, ' . $user->name"
    subtitle="Track your recent orders, reviews, reading library, and account security."
>
    <x-slot:actions>
        <a href="{{ route('books.index') }}" class="px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
            Browse Catalog
        </a>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
@if(!$accountStatus['email_verified'])
    <x-alert type="warning" title="Verify your email address" class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <span>Please verify your email to place orders, manage your cart, and publish book reviews.</span>
            <form method="POST" action="{{ route('verification.send') }}" class="shrink-0">
                @csrf
                <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-amber-600 text-white text-xs font-semibold hover:bg-amber-700 transition-colors">
                    Resend Verification Email
                </button>
            </form>
        </div>
    </x-alert>
@endif

{{-- KPI Summary --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <x-stat-card label="Total Orders" :value="$orderSummary['total']" accent="brand" :href="route('orders.index')" />
    <x-stat-card label="Pending" :value="$orderSummary['pending']" accent="warning" />
    <x-stat-card label="Completed" :value="$orderSummary['completed']" accent="success" />
    <x-stat-card label="Total Spent" :value="'₱' . number_format($orderSummary['total_spent'], 2)" accent="amber" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    {{-- Recent Orders --}}
    <section class="lg:col-span-2 bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border flex items-center justify-between">
            <h2 class="font-serif text-lg font-bold text-brand-ink">Recent Orders</h2>
            <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-brand-darkgreen hover:text-brand-amber">View all &rarr;</a>
        </div>
        <div class="divide-y divide-brand-border/70">
            @forelse($recentOrders as $order)
                <div class="px-6 py-4 hover:bg-brand-paper/60 transition-colors flex items-center justify-between gap-4">
                    <div>
                        <a href="{{ route('orders.show', $order) }}" class="font-semibold text-sm text-brand-ink hover:text-brand-darkgreen">Order #{{ $order->id }}</a>
                        <p class="text-xs text-brand-muted mt-0.5">{{ $order->created_at->format('M d, Y') }} &middot; {{ $order->orderItems->count() }} {{ Str::plural('item', $order->orderItems->count()) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-brand-ink tabular-nums">₱{{ number_format($order->total_amount, 2) }}</p>
                        <x-badge class="mt-1" :variant="match($order->status) { 'completed' => 'success', 'processing' => 'info', 'cancelled' => 'danger', default => 'warning' }">
                            {{ ucfirst($order->status) }}
                        </x-badge>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-brand-muted">
                    No orders yet. <a href="{{ route('books.index') }}" class="font-semibold text-brand-darkgreen underline">Explore books</a> to get started.
                </div>
            @endforelse
        </div>
    </section>

    {{-- Account Security & Shortcuts --}}
    <aside class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border">
            <h2 class="font-serif text-lg font-bold text-brand-ink">Account &amp; Security</h2>
        </div>
        <div class="p-5 space-y-3">
            <div class="flex items-center justify-between p-3 rounded-xl {{ $accountStatus['email_verified'] ? 'bg-emerald-50 text-emerald-800' : 'bg-red-50 text-red-800' }} text-xs font-semibold">
                <span>{{ $accountStatus['email_verified'] ? 'Email Verified' : 'Email Not Verified' }}</span>
                <x-badge :variant="$accountStatus['email_verified'] ? 'success' : 'danger'">{{ $accountStatus['email_verified'] ? 'Active' : 'Action needed' }}</x-badge>
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl {{ $accountStatus['two_factor_enabled'] ? 'bg-emerald-50 text-emerald-800' : 'bg-amber-50 text-amber-800' }} text-xs font-semibold">
                <span>{{ $accountStatus['two_factor_enabled'] ? '2FA Enabled' : '2FA Disabled' }}</span>
                <a href="{{ route('two-factor.index') }}" class="underline hover:opacity-80">Manage</a>
            </div>

            <div class="pt-3 border-t border-brand-border space-y-1.5 text-sm">
                <a href="{{ route('orders.index') }}" class="block px-3.5 py-2.5 rounded-lg hover:bg-brand-cream font-medium text-brand-ink transition-colors">Order History</a>
                <a href="{{ route('profile.edit') }}" class="block px-3.5 py-2.5 rounded-lg hover:bg-brand-cream font-medium text-brand-ink transition-colors">Profile &amp; Password</a>
                <a href="{{ route('user.data-portability.index') }}" class="block px-3.5 py-2.5 rounded-lg hover:bg-brand-cream font-medium text-brand-ink transition-colors">Data Portability (GDPR)</a>
            </div>
        </div>
    </aside>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Recently Purchased Books --}}
    <section class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border">
            <h2 class="font-serif text-lg font-bold text-brand-ink">Recently Purchased Books</h2>
        </div>
        <div class="divide-y divide-brand-border/70">
            @forelse($recentBooks as $book)
                <div class="px-6 py-4 hover:bg-brand-paper/60 transition-colors flex items-center gap-4">
                    @if($book->cover_image)
                        <img src="{{ asset('storage/' . $book->cover_image) }}" alt="Cover of {{ $book->title }}" class="w-10 h-14 object-cover rounded shadow-xs border border-brand-border" loading="lazy">
                    @else
                        <div class="w-10 h-14 bg-brand-cream rounded border border-brand-border flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-brand-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                    @endif
                    <div class="min-w-0">
                        <a href="{{ route('books.show', $book) }}" class="font-semibold text-sm text-brand-ink hover:text-brand-darkgreen truncate block">{{ $book->title }}</a>
                        <p class="text-xs text-brand-muted">{{ $book->author }}</p>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-brand-muted">No completed purchases yet.</div>
            @endforelse
        </div>
    </section>

    {{-- My Reviews --}}
    <section class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border flex items-center justify-between">
            <h2 class="font-serif text-lg font-bold text-brand-ink">My Reviews</h2>
            <x-badge variant="brand">{{ $reviewCount }}</x-badge>
        </div>
        <div class="divide-y divide-brand-border/70">
            @forelse($reviews as $review)
                <div class="px-6 py-4 hover:bg-brand-paper/60 transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('books.show', $review->book) }}" class="font-semibold text-sm text-brand-ink hover:text-brand-darkgreen truncate block">{{ $review->book->title }}</a>
                            <p class="text-xs text-brand-muted">{{ $review->created_at->diffForHumans() }}</p>
                            @if($review->comment)
                                <p class="text-xs text-brand-ink/80 mt-1.5">{{ Str::limit($review->comment, 90) }}</p>
                            @endif
                        </div>
                        <div class="flex items-center shrink-0">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-brand-amber' : 'text-stone-200' }}" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                            @endfor
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-brand-muted">No reviews yet. Share your thoughts after your next read!</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
