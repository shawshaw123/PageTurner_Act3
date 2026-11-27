@extends('layouts.app')

@section('title', 'Order Statistics — PageTurner Admin')

@section('header')
<x-page-header
    title="Order Statistics"
    subtitle="Gross revenue summary, fulfilment volume distribution, and recent order activity."
    :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'Order Statistics'],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
            &larr; Dashboard
        </a>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <x-stat-card
        label="Gross Revenue"
        :value="'₱' . number_format($stats['total_revenue'], 2)"
        :subtext="'From ' . $stats['completed_orders'] . ' completed orders'"
        accent="brand"
    />

    <div class="lg:col-span-2 bg-white rounded-2xl border border-brand-border shadow-xs p-6">
        <h2 class="text-xs font-bold uppercase tracking-wider text-brand-muted mb-4">Order Volume by Status</h2>
        @php $tot = max(1, $stats['total_orders']); @endphp
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <div class="flex justify-between text-sm mb-1.5">
                    <span class="font-medium text-sky-800">Pending</span>
                    <span class="font-bold text-brand-ink tabular-nums">{{ $stats['pending_orders'] }}</span>
                </div>
                <div class="w-full bg-sky-100 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-sky-600 h-full rounded-full" style="width: {{ ($stats['pending_orders'] / $tot) * 100 }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1.5">
                    <span class="font-medium text-amber-800">Processing</span>
                    <span class="font-bold text-brand-ink tabular-nums">{{ $stats['processing_orders'] }}</span>
                </div>
                <div class="w-full bg-amber-100 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-amber-500 h-full rounded-full" style="width: {{ ($stats['processing_orders'] / $tot) * 100 }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1.5">
                    <span class="font-medium text-emerald-800">Completed</span>
                    <span class="font-bold text-brand-ink tabular-nums">{{ $stats['completed_orders'] }}</span>
                </div>
                <div class="w-full bg-emerald-100 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-emerald-600 h-full rounded-full" style="width: {{ ($stats['completed_orders'] / $tot) * 100 }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
    <div class="px-6 py-4 border-b border-brand-border flex items-center justify-between">
        <h2 class="font-serif text-lg font-bold text-brand-ink">Recent Order Activity</h2>
        <span class="text-xs text-brand-muted">Latest 5 orders</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-brand-border text-sm">
            <thead class="bg-brand-cream/70">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Customer</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Amount</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-border/70">
                @foreach($stats['recent_orders'] as $order)
                    <tr class="hover:bg-brand-paper/60 transition-colors">
                        <td class="px-6 py-3.5 font-mono font-semibold text-brand-ink">
                            <a href="{{ route('orders.show', $order) }}" class="hover:text-brand-darkgreen">#{{ $order->id }}</a>
                        </td>
                        <td class="px-6 py-3.5 text-brand-ink">{{ $order->user->name }}</td>
                        <td class="px-6 py-3.5 text-right font-semibold tabular-nums">₱{{ number_format($order->total_amount, 2) }}</td>
                        <td class="px-6 py-3.5">
                            <x-badge :variant="$order->status == 'completed' ? 'success' : 'info'">{{ ucfirst($order->status) }}</x-badge>
                        </td>
                        <td class="px-6 py-3.5 text-brand-muted">{{ $order->created_at->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
