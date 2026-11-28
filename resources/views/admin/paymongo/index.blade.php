@extends('layouts.app')

@section('title', 'PayMongo Payments — LEBROn Admin')

@section('header')
<x-page-header
    title="PayMongo Payments Dashboard"
    subtitle="Monitor online checkout sessions, payment channels, webhook health, and settlement volume."
    :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'PayMongo Payments'],
    ]"
>
    <x-slot:actions>
        <button type="button" onclick="document.getElementById('sampleQrModal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-semibold text-brand-darkgreen hover:bg-brand-cream transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            Sample QR Preview
        </button>
        <a href="https://dashboard.paymongo.com/" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
            PayMongo Portal &nearr;
        </a>
        <a href="{{ route('orders.index') }}" class="px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
            View Orders
        </a>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
@if(!$config['configured'])
    <x-alert type="warning" title="PayMongo API keys are not configured yet" class="mb-6">
        Online checkout is currently disabled for buyers. Add <code class="bg-amber-100 px-1.5 py-0.5 rounded font-mono text-xs">PAYMONGO_SECRET_KEY=sk_test_...</code> or enable <code class="bg-amber-100 px-1.5 py-0.5 rounded font-mono text-xs">PAYMONGO_DEMO_MODE=true</code> in your <code class="bg-amber-100 px-1.5 py-0.5 rounded font-mono text-xs">.env</code> file, then restart <code class="bg-amber-100 px-1.5 py-0.5 rounded font-mono text-xs">php artisan serve</code>.
    </x-alert>
@endif

@php
    $totalTx = max(1, $stats['total_count']);
    $successRate = $stats['total_count'] > 0 ? round(($stats['paid_count'] / $stats['total_count']) * 100, 1) : 0;
@endphp

{{-- KPI Row --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <x-stat-card label="Paid Volume" :value="'₱' . number_format($stats['paid_volume'], 2)" :subtext="$successRate . '% completion rate'" accent="brand" />
    <x-stat-card label="Paid Sessions" :value="number_format($stats['paid_count'])" accent="success" />
    <x-stat-card label="Pending" :value="number_format($stats['pending_count'])" accent="warning" />
    <x-stat-card label="Failed / Expired" :value="number_format($stats['failed_count'])" accent="danger" />
</div>

{{-- Integration Health + Bank & E-Wallet Channels --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
    <section class="lg:col-span-7 bg-white rounded-2xl border border-brand-border shadow-xs p-6">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <h2 class="font-serif text-lg font-bold text-brand-ink">Gateway &amp; Webhook Status</h2>
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

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div class="rounded-xl bg-brand-paper border border-brand-border p-3.5">
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-muted">Webhook Signature</p>
                <div class="mt-1.5 flex items-center justify-between">
                    <x-badge :variant="$config['webhook_configured'] ? 'success' : 'neutral'" dot>
                        {{ $config['webhook_configured'] ? 'Secret Configured' : 'Not Set' }}
                    </x-badge>
                    <span class="text-xs text-brand-muted">{{ $config['window_minutes'] }}m window</span>
                </div>
            </div>

            <div class="rounded-xl bg-brand-paper border border-brand-border p-3.5">
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-muted">Webhook Endpoint</p>
                <div class="mt-1.5 flex items-center justify-between gap-2">
                    <span id="webhookUrlText" class="font-mono text-xs text-brand-ink truncate" title="{{ $config['webhook_url'] }}">{{ $config['webhook_url'] }}</span>
                    <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('webhookUrlText').textContent); this.textContent='Copied';" class="shrink-0 text-xs font-semibold text-brand-darkgreen hover:text-brand-amber">
                        Copy
                    </button>
                </div>
            </div>
        </div>

        {{-- Settlement Progress Bar --}}
        <div class="mt-5 pt-4 border-t border-brand-border">
            <div class="flex justify-between text-xs text-brand-muted mb-1.5">
                <span>Settlement Distribution ({{ $stats['total_count'] }} total sessions)</span>
                <span class="font-semibold text-brand-ink">{{ $successRate }}% Paid</span>
            </div>
            <div class="w-full h-2.5 rounded-full bg-stone-100 overflow-hidden flex">
                <div class="bg-emerald-500 h-full" style="width: {{ ($stats['paid_count'] / $totalTx) * 100 }}%"></div>
                <div class="bg-amber-400 h-full" style="width: {{ ($stats['pending_count'] / $totalTx) * 100 }}%"></div>
                <div class="bg-red-400 h-full" style="width: {{ ($stats['failed_count'] / $totalTx) * 100 }}%"></div>
            </div>
        </div>
    </section>

    {{-- Supported Banks & E-Wallet Logos --}}
    <section class="lg:col-span-5 bg-white rounded-2xl border border-brand-border shadow-xs p-6">
        <h2 class="font-serif text-lg font-bold text-brand-ink mb-1">Active Payment Channels</h2>
        <p class="text-xs text-brand-muted mb-4">Supported Philippine e-wallets, cards, QR Ph, and local banks.</p>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
            <div class="flex items-center gap-2 p-2.5 rounded-xl border border-brand-border bg-brand-paper/60">
                <span class="w-7 h-7 rounded-md bg-[#007DFE] text-white text-[11px] font-extrabold flex items-center justify-center shrink-0">G</span>
                <span class="text-xs font-semibold text-brand-ink">GCash</span>
            </div>
            <div class="flex items-center gap-2 p-2.5 rounded-xl border border-brand-border bg-brand-paper/60">
                <span class="w-7 h-7 rounded-md bg-[#00B462] text-white text-[11px] font-extrabold flex items-center justify-center shrink-0">M</span>
                <span class="text-xs font-semibold text-brand-ink">Maya</span>
            </div>
            <div class="flex items-center gap-2 p-2.5 rounded-xl border border-brand-border bg-brand-paper/60">
                <span class="w-7 h-7 rounded-md bg-[#00B14F] text-white text-[10px] font-extrabold flex items-center justify-center shrink-0">GP</span>
                <span class="text-xs font-semibold text-brand-ink">GrabPay</span>
            </div>
            <div class="flex items-center gap-2 p-2.5 rounded-xl border border-brand-border bg-brand-paper/60">
                <span class="w-7 h-7 rounded-md bg-[#0038A8] text-white text-[10px] font-extrabold flex items-center justify-center shrink-0">QR</span>
                <span class="text-xs font-semibold text-brand-ink">QR Ph</span>
            </div>
            <div class="flex items-center gap-2 p-2.5 rounded-xl border border-brand-border bg-brand-paper/60">
                <span class="w-7 h-7 rounded-md bg-[#1A1F71] text-white text-[9px] font-extrabold flex items-center justify-center shrink-0">VISA</span>
                <span class="text-xs font-semibold text-brand-ink">Visa / MC</span>
            </div>
            <div class="flex items-center gap-2 p-2.5 rounded-xl border border-brand-border bg-brand-paper/60">
                <span class="w-7 h-7 rounded-md bg-[#B11116] text-white text-[10px] font-extrabold flex items-center justify-center shrink-0">BPI</span>
                <span class="text-xs font-semibold text-brand-ink">BPI / BDO</span>
            </div>
        </div>
    </section>
</div>

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

{{-- Sample QR Ph Preview Modal --}}
<div id="sampleQrModal" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-4 bg-black/50 backdrop-blur-[1px]" role="dialog" aria-modal="true" aria-labelledby="sampleQrTitle" onclick="if(event.target === this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl border border-brand-border shadow-xl max-w-sm w-full p-6 text-center">
        <div class="flex items-center justify-between mb-4">
            <h3 id="sampleQrTitle" class="font-serif text-lg font-bold text-brand-ink">Sample QR Ph Code</h3>
            <button type="button" onclick="document.getElementById('sampleQrModal').classList.add('hidden')" class="p-1 rounded-lg text-brand-muted hover:bg-brand-cream" aria-label="Close QR preview">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-paper border border-brand-border text-xs font-semibold text-brand-ink mb-4">
            <span class="w-2 h-2 rounded-full bg-[#0038A8]"></span>
            <span class="w-2 h-2 rounded-full bg-[#CE1126]"></span>
            <span class="w-2 h-2 rounded-full bg-[#FCD116]"></span>
            <span>PayMongo QR Ph · LEBROn</span>
        </div>

        <div class="mx-auto w-48 h-48 bg-white p-3 rounded-xl border border-brand-border shadow-xs">
            <svg viewBox="0 0 29 29" class="w-full h-full text-brand-ink" shape-rendering="crispEdges" role="img" aria-label="Sample PayMongo QR Ph Code">
                <rect width="29" height="29" fill="#FFFFFF"/>
                <path fill="currentColor" d="M1 1h7v7H1zM2 2v5h5V2zM3 3h3v3H3z"/>
                <path fill="currentColor" d="M21 1h7v7h-7zM22 2v5h5V2zM23 3h3v3h-3z"/>
                <path fill="currentColor" d="M1 21h7v7H1zM2 22v5h5v-5zM3 23h3v3H3z"/>
                <path fill="currentColor" d="M10 1h2v2h-2zM14 1h3v2h-3zM18 2h2v3h-2zM10 4h4v2h-4zM15 5h2v4h-2zM10 7h3v2h-3zM18 7h2v2h-2zM1 10h3v2H1zM5 10h4v2H5zM11 10h2v3h-2zM15 10h5v2h-5zM22 10h3v2h-3zM26 10h2v3h-2zM2 13h2v3H2zM6 13h3v2H6zM13 13h3v3h-3zM18 13h2v4h-2zM21 13h4v2h-4zM1 17h4v2H1zM7 16h3v3H7zM12 17h2v3h-2zM16 17h3v2h-3zM21 16h2v4h-2zM25 15h3v3h-3zM10 21h3v2h-3zM15 20h4v2h-4zM20 21h3v3h-3zM25 20h3v2h-3zM10 24h2v4h-2zM14 23h3v3h-3zM18 25h4v3h-4zM24 24h4v4h-4z"/>
            </svg>
        </div>

        <p class="mt-4 text-xs text-brand-muted">Buyers see a live session QR code like this during PayMongo Sandbox checkout for GCash, Maya, GrabPay, BPI, and BDO.</p>
    </div>
</div>
@endsection
