@extends('layouts.app')

@section('title', 'Admin Dashboard — PageTurner')

@section('header')
<x-page-header
    title="Admin Dashboard"
    subtitle="Storefront performance, order activity, inventory alerts, and platform operations."
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Admin Dashboard'],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('books.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Book
        </a>
        <a href="{{ route('admin.import-export.index') }}" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
            Import / Export
        </a>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
{{-- Top KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <x-stat-card label="Customers" :value="number_format($stats['total_users'])" accent="info" />
    <x-stat-card label="Books" :value="number_format($stats['total_books'])" accent="success" :href="route('books.index')" />
    <x-stat-card label="Categories" :value="number_format($stats['total_categories'])" accent="neutral" :href="route('categories.index')" />
    <x-stat-card label="Total Orders" :value="number_format($stats['total_orders'])" accent="amber" :href="route('orders.index')" />
    <x-stat-card label="Completed Revenue" :value="'₱' . number_format($stats['total_revenue'], 2)" accent="brand" :href="route('orders.statistics')" />
</div>

{{-- Order Pipeline Breakdown --}}
@php
    $totalStatusCount = max(1, array_sum($orderStatuses));
@endphp
<section class="bg-white rounded-2xl border border-brand-border shadow-xs p-6 mb-8" aria-labelledby="pipeline-heading">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
        <div>
            <h2 id="pipeline-heading" class="font-serif text-lg font-bold text-brand-ink">Order Fulfilment Pipeline</h2>
            <p class="text-xs text-brand-muted">Distribution of all orders by current status</p>
        </div>
        <a href="{{ route('orders.statistics') }}" class="text-xs font-semibold text-brand-darkgreen hover:text-brand-amber">Detailed statistics &rarr;</a>
    </div>

    <div class="w-full h-2.5 rounded-full bg-stone-100 overflow-hidden flex mb-5" aria-hidden="true">
        <div class="bg-amber-400 h-full" style="width: {{ ($orderStatuses['pending'] / $totalStatusCount) * 100 }}%"></div>
        <div class="bg-sky-500 h-full" style="width: {{ ($orderStatuses['processing'] / $totalStatusCount) * 100 }}%"></div>
        <div class="bg-emerald-500 h-full" style="width: {{ ($orderStatuses['completed'] / $totalStatusCount) * 100 }}%"></div>
        <div class="bg-red-400 h-full" style="width: {{ ($orderStatuses['cancelled'] / $totalStatusCount) * 100 }}%"></div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl bg-amber-50/70 border border-amber-200/70 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-800">Pending</p>
            <p class="mt-1 text-2xl font-bold text-amber-900 tabular-nums">{{ $orderStatuses['pending'] }}</p>
        </div>
        <div class="rounded-xl bg-sky-50/70 border border-sky-200/70 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-sky-800">Processing</p>
            <p class="mt-1 text-2xl font-bold text-sky-900 tabular-nums">{{ $orderStatuses['processing'] }}</p>
        </div>
        <div class="rounded-xl bg-emerald-50/70 border border-emerald-200/70 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-800">Completed</p>
            <p class="mt-1 text-2xl font-bold text-emerald-900 tabular-nums">{{ $orderStatuses['completed'] }}</p>
        </div>
        <div class="rounded-xl bg-red-50/70 border border-red-200/70 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-red-800">Cancelled</p>
            <p class="mt-1 text-2xl font-bold text-red-900 tabular-nums">{{ $orderStatuses['cancelled'] }}</p>
        </div>
    </div>
</section>

{{-- Operations & Platform Tools --}}
<section class="bg-white rounded-2xl border border-brand-border shadow-xs p-6 mb-8" aria-labelledby="ops-heading">
    <h2 id="ops-heading" class="font-serif text-lg font-bold text-brand-ink mb-1">Operations &amp; Administration</h2>
    <p class="text-xs text-brand-muted mb-5">Direct access to catalog management, data portability, backups, security audits, and API tools.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <a href="{{ route('admin.sellers.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-brand-border hover:border-brand-darkgreen/40 hover:bg-brand-paper transition-colors">
            <span class="w-9 h-9 rounded-lg bg-brand-cream text-brand-darkgreen flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.85" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-brand-ink">Seller Approvals</p>
                <p class="text-xs text-brand-muted truncate">Review &amp; manage sellers</p>
            </div>
        </a>

        <a href="{{ route('admin.import-export.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-brand-border hover:border-brand-darkgreen/40 hover:bg-brand-paper transition-colors">
            <span class="w-9 h-9 rounded-lg bg-brand-cream text-brand-darkgreen flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.85" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-brand-ink">Import &amp; Export</p>
                <p class="text-xs text-brand-muted truncate">Excel, CSV &amp; PDF tools</p>
            </div>
        </a>

        <a href="{{ route('admin.backup.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-brand-border hover:border-brand-darkgreen/40 hover:bg-brand-paper transition-colors">
            <span class="w-9 h-9 rounded-lg bg-brand-cream text-brand-darkgreen flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.85" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-brand-ink">Database Backups</p>
                <p class="text-xs text-brand-muted truncate">Schedule &amp; snapshots</p>
            </div>
        </a>

        <a href="{{ route('admin.audit.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-brand-border hover:border-brand-darkgreen/40 hover:bg-brand-paper transition-colors">
            <span class="w-9 h-9 rounded-lg bg-brand-cream text-brand-darkgreen flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.85" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-brand-ink">Audit Logs</p>
                <p class="text-xs text-brand-muted truncate">Security &amp; change trail</p>
            </div>
        </a>

        <a href="{{ route('admin.api-rate-limits.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-brand-border hover:border-brand-darkgreen/40 hover:bg-brand-paper transition-colors">
            <span class="w-9 h-9 rounded-lg bg-brand-cream text-brand-darkgreen flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.85" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-brand-ink">API Rate Limits</p>
                <p class="text-xs text-brand-muted truncate">Traffic &amp; throttle monitor</p>
            </div>
        </a>

        <a href="{{ route('admin.ai.dashboard') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-brand-border hover:border-brand-darkgreen/40 hover:bg-brand-paper transition-colors">
            <span class="w-9 h-9 rounded-lg bg-brand-cream text-brand-darkgreen flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.85" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-brand-ink">AI Monitoring</p>
                <p class="text-xs text-brand-muted truncate">Usage, tokens &amp; latency</p>
            </div>
        </a>

        <a href="{{ route('admin.api-docs') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-brand-border hover:border-brand-darkgreen/40 hover:bg-brand-paper transition-colors">
            <span class="w-9 h-9 rounded-lg bg-brand-cream text-brand-darkgreen flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.85" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-brand-ink">REST API Docs</p>
                <p class="text-xs text-brand-muted truncate">OpenAPI / Swagger UI</p>
            </div>
        </a>

        <a href="{{ route('categories.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-brand-border hover:border-brand-darkgreen/40 hover:bg-brand-paper transition-colors">
            <span class="w-9 h-9 rounded-lg bg-brand-cream text-brand-darkgreen flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.85" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-brand-ink">Categories</p>
                <p class="text-xs text-brand-muted truncate">Organize catalog shelves</p>
            </div>
        </a>
    </div>
</section>

{{-- Activity & Inventory Grid --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Recent Orders --}}
    <section class="lg:col-span-2 bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border flex items-center justify-between">
            <h2 class="font-serif text-lg font-bold text-brand-ink">Recent Orders</h2>
            <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-brand-darkgreen hover:text-brand-amber">View all &rarr;</a>
        </div>
        <div class="divide-y divide-brand-border/70">
            @forelse($recentOrders as $order)
                <div class="px-6 py-3.5 hover:bg-brand-paper/60 transition-colors flex items-center justify-between gap-4">
                    <div>
                        <a href="{{ route('orders.show', $order) }}" class="font-mono text-sm font-semibold text-brand-ink hover:text-brand-darkgreen">#{{ $order->id }}</a>
                        <p class="text-xs text-brand-muted">{{ $order->user->name ?? 'N/A' }} &middot; {{ $order->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-brand-ink tabular-nums">₱{{ number_format($order->total_amount, 2) }}</p>
                        <x-badge class="mt-0.5" :variant="match($order->status) { 'completed' => 'success', 'processing' => 'info', 'cancelled' => 'danger', default => 'warning' }">
                            {{ ucfirst($order->status) }}
                        </x-badge>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-brand-muted">No orders recorded yet.</div>
            @endforelse
        </div>
    </section>

    {{-- Low Stock & Recent Reviews --}}
    <div class="space-y-6">
        <section class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-brand-border flex items-center justify-between">
                <h2 class="font-serif text-lg font-bold text-brand-ink">Low Stock Alerts</h2>
                <x-badge :variant="$lowStockBooks->count() > 0 ? 'warning' : 'success'">{{ $lowStockBooks->count() }}</x-badge>
            </div>
            <div class="divide-y divide-brand-border/70">
                @forelse($lowStockBooks as $book)
                    <div class="px-6 py-3.5 hover:bg-brand-paper/60 transition-colors flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('books.edit', $book) }}" class="text-sm font-semibold text-brand-ink hover:text-brand-darkgreen truncate block">{{ $book->title }}</a>
                            <p class="text-xs text-brand-muted truncate">{{ $book->author }}</p>
                        </div>
                        <x-badge :variant="$book->stock_quantity == 0 ? 'danger' : 'warning'">
                            {{ $book->stock_quantity == 0 ? 'Out' : $book->stock_quantity . ' left' }}
                        </x-badge>
                    </div>
                @empty
                    <div class="p-6 text-center text-sm text-brand-muted">All books are well stocked.</div>
                @endforelse
            </div>
        </section>

        <section class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-brand-border">
                <h2 class="font-serif text-lg font-bold text-brand-ink">Recent Reviews</h2>
            </div>
            <div class="divide-y divide-brand-border/70">
                @forelse($recentReviews as $review)
                    <div class="px-6 py-3.5 hover:bg-brand-paper/60 transition-colors">
                        <div class="flex items-start justify-between gap-2">
                            <a href="{{ route('books.show', $review->book) }}" class="text-sm font-semibold text-brand-ink hover:text-brand-darkgreen truncate">{{ $review->book->title }}</a>
                            <span class="text-xs font-bold text-brand-amber shrink-0">{{ $review->rating }}/5 ★</span>
                        </div>
                        <p class="text-xs text-brand-muted mt-0.5">{{ $review->user->name }} &middot; {{ $review->created_at->diffForHumans() }}</p>
                        @if($review->comment)
                            <p class="text-xs text-brand-ink/80 mt-1 line-clamp-2">{{ Str::limit($review->comment, 80) }}</p>
                        @endif
                    </div>
                @empty
                    <div class="p-6 text-center text-sm text-brand-muted">No reviews yet.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
