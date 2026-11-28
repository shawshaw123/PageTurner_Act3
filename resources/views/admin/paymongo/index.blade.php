@extends('layouts.app')

@section('title', 'PayMongo Payments — PageTurner Admin')

@section('header')
<x-page-header
    title="PayMongo Payments Dashboard"
    subtitle="Monitor online checkout sessions, payment status, webhook configuration, and settlement volume."
    :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'PayMongo Payments'],
    ]"
>
    <x-slot:actions>
        <a href="https://dashboard.paymongo.com/" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
            Open PayMongo Portal &nearr;
        </a>
        <a href="{{ route('orders.index') }}" class="px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
            View Orders
        </a>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
{{-- Configuration Notice --}}
@if(!$config['configured'])
    <x-alert type="warning" title="PayMongo API keys are not configured yet" class="mb-6">
        Online checkout is currently disabled for buyers. Add <code class="bg-amber-100 px-1.5 py-0.5 rounded font-mono text-xs">PAYMONGO_SECRET_KEY=sk_test_...</code> and <code class="bg-amber-100 px-1.5 py-0.5 rounded font-mono text-xs">PAYMONGO_WEBHOOK_SECRET=whsk_...</code> to your <code class="bg-amber-100 px-1.5 py-0.5 rounded font-mono text-xs">.env</code> file, then restart <code class="bg-amber-100 px-1.5 py-0.5 rounded font-mono text-xs">php artisan serve</code>.
    </x-alert>
@endif

{{-- KPI Row --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <x-stat-card label="Paid Volume" :value="'₱' . number_format($stats['paid_volume'], 2)" accent="brand" />
    <x-stat-card label="Paid Sessions" :value="number_format($stats['paid_count'])" accent="success" />
    <x-stat-card label="Pending" :value="number_format($stats['pending_count'])" accent="warning" />
    <x-stat-card label="Failed / Expired" :value="number_format($stats['failed_count'])" accent="danger" />
</div>

{{-- Integration Health Bar --}}
<section class="bg-white rounded-2xl border border-brand-border shadow-xs p-5 mb-8">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-muted">API Connection</p>
            <div class="mt-1.5 flex items-center gap-2">
                <x-badge :variant="$config['configured'] ? 'success' : 'warning'" dot>
                    @if($config['demo_mode'] ?? false)
                        Local Sandbox (Demo Mode)
                    @elseif($config['configured'])
                        {{ $config['is_test_mode'] ? 'Configured (Test Mode)' : 'Configured (Live)' }}
                    @else
                        Not Configured
                    @endif
                </x-badge>
            </div>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-muted">Webhook Secret</p>
            <div class="mt-1.5">
                <x-badge :variant="$config['webhook_configured'] ? 'success' : 'neutral'" dot>
                    {{ $config['webhook_configured'] ? 'Verified' : 'Not Set' }}
                </x-badge>
            </div>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-muted">Enabled Methods</p>
            <p class="mt-1.5 text-xs font-medium text-brand-ink">
                {{ !empty($config['payment_methods']) ? strtoupper(implode(', ', $config['payment_methods'])) : 'None' }}
            </p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-muted">Webhook Endpoint</p>
            <p class="mt-1.5 font-mono text-xs text-brand-ink truncate" title="{{ $config['webhook_url'] }}">{{ $config['webhook_url'] }}</p>
        </div>
    </div>
</section>

{{-- Filter Tabs & Search --}}
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <nav class="flex flex-wrap gap-2" aria-label="Filter payments by status">
        @foreach([null => 'All', 'paid' => 'Paid', 'pending' => 'Pending', 'failed' => 'Failed', 'expired' => 'Expired'] as $key => $label)
            @php $active = ($status ?: null) === ($key ?: null); @endphp
            <a href="{{ route('admin.paymongo.index', array_filter(['status' => $key, 'search' => request('search')])) }}"
               class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-sm font-medium border transition-colors {{ $active ? 'bg-brand-darkgreen text-white border-brand-darkgreen' : 'bg-white text-brand-ink border-brand-border hover:bg-brand-cream' }}"
               @if($active) aria-current="page" @endif>
                <span>{{ $label }}</span>
                <span class="text-xs {{ $active ? 'text-white/80' : 'text-brand-muted' }} tabular-nums">{{ $key ? ($counts[$key] ?? 0) : $counts->sum() }}</span>
            </a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('admin.paymongo.index') }}" class="flex gap-2 w-full md:w-80">
        @if($status)
            <input type="hidden" name="status" value="{{ $status }}">
        @endif
        <label for="paymongo-search" class="sr-only">Search payments</label>
        <input id="paymongo-search" type="search" name="search" value="{{ request('search') }}" placeholder="Reference, session ID, or buyer..."
               class="flex-1 rounded-xl border-brand-border bg-white text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
        <button type="submit" class="px-4 py-2 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">Search</button>
    </form>
</div>

{{-- Payments Table --}}
@if($payments->count() > 0)
    <div class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-brand-border text-sm">
                <thead class="bg-brand-cream/70">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Reference</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Buyer</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Orders</th>
                        <th scope="col" class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Amount</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Status</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Timestamp</th>
                        <th scope="col" class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/70">
                    @foreach($payments as $payment)
                        <tr class="hover:bg-brand-paper/60 transition-colors">
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="font-mono text-xs font-semibold text-brand-ink">{{ $payment->checkout_reference }}</span>
                                @if($payment->checkout_session_id)
                                    <span class="block font-mono text-[11px] text-brand-muted">{{ Str::limit($payment->checkout_session_id, 24) }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-brand-ink">{{ $payment->user->name ?? 'N/A' }}</span>
                                @if($payment->user?->email)
                                    <span class="block text-xs text-brand-muted">{{ $payment->user->email }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @forelse($payment->orders as $order)
                                    <a href="{{ route('orders.show', $order) }}" class="inline-block font-mono text-xs font-semibold text-brand-darkgreen hover:text-brand-amber mr-1.5">#{{ $order->id }}</a>
                                @empty
                                    <span class="text-xs text-brand-muted">&mdash;</span>
                                @endforelse
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-right font-semibold tabular-nums text-brand-ink">
                                ₱{{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <x-badge :variant="match($payment->status) { 'paid' => 'success', 'pending' => 'warning', 'failed', 'expired' => 'danger', default => 'neutral' }" dot>
                                    {{ ucfirst($payment->status) }}
                                </x-badge>
                                @if($payment->failure_reason)
                                    <span class="block text-[11px] text-red-600 mt-0.5 max-w-[180px] truncate" title="{{ $payment->failure_reason }}">{{ $payment->failure_reason }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-brand-muted">
                                {{ ($payment->paid_at ?? $payment->created_at)->format('M j, Y H:i') }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-right space-x-1.5">
                                @if($payment->isOpen())
                                    <form action="{{ route('admin.paymongo.sync', $payment) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-md border border-brand-border text-xs font-semibold text-brand-ink hover:bg-brand-cream">Sync</button>
                                    </form>
                                    <form action="{{ route('admin.paymongo.expire', $payment) }}" method="POST" class="inline" data-confirm="Expire payment {{ $payment->checkout_reference }} and return reserved stock?">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-md bg-red-50 text-red-700 border border-red-200 text-xs font-semibold hover:bg-red-100">Expire</button>
                                    </form>
                                @else
                                    <span class="text-xs text-brand-muted">Settled</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $payments->links() }}</div>
@else
    <x-empty-state
        title="No PayMongo transactions yet"
        description="Online payments initiated through PayMongo checkout will appear here with real-time status and settlement details."
    />
@endif
@endsection
