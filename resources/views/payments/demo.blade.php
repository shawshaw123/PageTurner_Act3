@extends('layouts.app')

@section('title', 'PayMongo Sandbox Checkout — LEBROn')

@section('header')
<x-page-header
    title="PayMongo Checkout"
    :subtitle="'Reference: ' . $payment->checkout_reference . ' · Local Sandbox Mode'"
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Orders', 'url' => route('orders.index')],
        ['label' => 'PayMongo Checkout'],
    ]"
/>
@endsection

@section('content')
<div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    {{-- Left: Order Summary --}}
    <aside class="lg:col-span-5 bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-5 bg-brand-darkgreen text-white">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-brand-gold">PayMongo Sandbox</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/15 text-white">TEST MODE</span>
            </div>
            <p class="mt-3 text-xs text-white/75">Total Amount Due</p>
            <p class="text-3xl font-bold tabular-nums mt-0.5">₱{{ number_format($payment->amount, 2) }}</p>
            <p class="font-mono text-[11px] text-white/65 mt-2">{{ $payment->checkout_session_id }}</p>
        </div>

        <div class="p-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-brand-muted mb-3">Items in This Payment</h2>
            <ul class="divide-y divide-brand-border/70 text-sm">
                @foreach($orders as $order)
                    @foreach($order->orderItems as $item)
                        <li class="py-2.5 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-brand-ink truncate">{{ $item->book?->title ?? 'Book' }}</p>
                                <p class="text-xs text-brand-muted">Order #{{ $order->id }} &middot; Qty {{ $item->quantity }}</p>
                            </div>
                            <span class="font-semibold tabular-nums text-brand-ink shrink-0">₱{{ number_format($item->subtotal, 2) }}</span>
                        </li>
                    @endforeach
                @endforeach
            </ul>

            <div class="mt-5 pt-4 border-t border-brand-border flex items-center justify-between text-xs text-brand-muted">
                <span>Merchant</span>
                <span class="font-semibold text-brand-ink">LEBROn Bookstore</span>
            </div>
        </div>
    </aside>

    {{-- Right: Payment Channel & Sample QR --}}
    <section class="lg:col-span-7 bg-white rounded-2xl border border-brand-border shadow-xs p-6 space-y-6">
        <form action="{{ route('payments.demo.complete', $payment->checkout_reference) }}" method="POST" id="paymongoDemoForm" data-no-loading class="space-y-6">
            @csrf
            <input type="hidden" name="action" id="demoActionInput" value="pay">

            <div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-serif text-lg font-bold text-brand-ink">Choose Payment Method</h2>
                    <button type="button" onclick="toggleQrPanel()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-brand-border bg-brand-paper text-xs font-semibold text-brand-darkgreen hover:bg-brand-cream transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        Sample QR Code
                    </button>
                </div>

                @php
                    $channels = [
                        'gcash' => ['label' => 'GCash', 'sub' => 'E-Wallet', 'bg' => '#007DFE', 'badge' => 'G'],
                        'paymaya' => ['label' => 'Maya', 'sub' => 'E-Wallet', 'bg' => '#00B462', 'badge' => 'M'],
                        'grab_pay' => ['label' => 'GrabPay', 'sub' => 'E-Wallet', 'bg' => '#00B14F', 'badge' => 'GP'],
                        'qrph' => ['label' => 'QR Ph', 'sub' => 'InstaPay QR', 'bg' => '#0038A8', 'badge' => 'QR'],
                        'card' => ['label' => 'Credit / Debit Card', 'sub' => 'Visa · Mastercard', 'bg' => '#1A1F71', 'badge' => 'CARD'],
                        'online_banking' => ['label' => 'Online Banking', 'sub' => 'BPI · BDO · UnionBank', 'bg' => '#B11116', 'badge' => 'BANK'],
                    ];
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($channels as $key => $ch)
                        <label class="flex items-center gap-3 p-3.5 rounded-xl border border-brand-border cursor-pointer hover:bg-brand-paper has-[:checked]:border-brand-darkgreen has-[:checked]:bg-brand-cream/70 transition-colors">
                            <input type="radio" name="channel" value="{{ $key }}" @checked($loop->first) onchange="onChannelChange(this.value)" class="text-brand-darkgreen focus:ring-brand-darkgreen">
                            <span class="w-10 h-10 rounded-lg text-white text-xs font-extrabold flex items-center justify-center shrink-0 shadow-xs" style="background-color: {{ $ch['bg'] }}">
                                {{ $ch['badge'] }}
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-brand-ink">{{ $ch['label'] }}</span>
                                <span class="block text-xs text-brand-muted">{{ $ch['sub'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Sample QR Panel --}}
            <div id="sampleQrPanel" class="rounded-2xl border border-brand-border bg-brand-paper p-5 text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-brand-border text-xs font-semibold text-brand-ink mb-3">
                    <span class="w-2 h-2 rounded-full bg-[#0038A8]"></span>
                    <span class="w-2 h-2 rounded-full bg-[#CE1126]"></span>
                    <span class="w-2 h-2 rounded-full bg-[#FCD116]"></span>
                    <span>QR Ph · Sample Sandbox QR</span>
                </div>

                {{-- Crisp Inline SVG Sample QR --}}
                <div class="mx-auto w-44 h-44 bg-white p-3 rounded-xl border border-brand-border shadow-xs flex items-center justify-center">
                    <svg viewBox="0 0 29 29" class="w-full h-full text-brand-ink" shape-rendering="crispEdges" role="img" aria-label="Sample PayMongo QR Ph Code">
                        <rect width="29" height="29" fill="#FFFFFF"/>
                        <!-- Top-left finder -->
                        <path fill="currentColor" d="M1 1h7v7H1zM2 2v5h5V2zM3 3h3v3H3z"/>
                        <!-- Top-right finder -->
                        <path fill="currentColor" d="M21 1h7v7h-7zM22 2v5h5V2zM23 3h3v3h-3z"/>
                        <!-- Bottom-left finder -->
                        <path fill="currentColor" d="M1 21h7v7H1zM2 22v5h5v-5zM3 23h3v3H3z"/>
                        <!-- Data modules -->
                        <path fill="currentColor" d="M10 1h2v2h-2zM14 1h3v2h-3zM18 2h2v3h-2zM10 4h4v2h-4zM15 5h2v4h-2zM10 7h3v2h-3zM18 7h2v2h-2zM1 10h3v2H1zM5 10h4v2H5zM11 10h2v3h-2zM15 10h5v2h-5zM22 10h3v2h-3zM26 10h2v3h-2zM2 13h2v3H2zM6 13h3v2H6zM13 13h3v3h-3zM18 13h2v4h-2zM21 13h4v2h-4zM1 17h4v2H1zM7 16h3v3H7zM12 17h2v3h-2zM16 17h3v2h-3zM21 16h2v4h-2zM25 15h3v3h-3zM10 21h3v2h-3zM15 20h4v2h-4zM20 21h3v3h-3zM25 20h3v2h-3zM10 24h2v4h-2zM14 23h3v3h-3zM18 25h4v3h-4zM24 24h4v4h-4z"/>
                    </svg>
                </div>

                <p class="mt-3 text-sm font-bold text-brand-ink">₱{{ number_format($payment->amount, 2) }}</p>
                <p class="text-xs text-brand-muted">Scan with GCash, Maya, BPI, BDO, or UnionBank app — or click <strong>Complete Payment</strong> below to simulate instant confirmation.</p>
            </div>

            {{-- Test Card Info Panel (shown when Card is chosen) --}}
            <div id="testCardPanel" class="hidden rounded-2xl border border-brand-border bg-brand-paper p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-brand-muted">PayMongo Test Card</span>
                    <div class="flex gap-1.5">
                        <span class="px-2 py-0.5 rounded bg-[#1A1F71] text-white text-[10px] font-bold">VISA</span>
                        <span class="px-2 py-0.5 rounded bg-[#EB001B] text-white text-[10px] font-bold">Mastercard</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="sm:col-span-3 bg-white rounded-lg border border-brand-border px-3 py-2 font-mono text-brand-ink">4343 4343 4343 4343</div>
                    <div class="bg-white rounded-lg border border-brand-border px-3 py-2 font-mono text-brand-ink">Exp: 12 / 28</div>
                    <div class="bg-white rounded-lg border border-brand-border px-3 py-2 font-mono text-brand-ink">CVC: 123</div>
                    <div class="bg-white rounded-lg border border-brand-border px-3 py-2 text-emerald-700 font-semibold">3DS Auto-Pass</div>
                </div>
            </div>

            {{-- Supported Banks Strip --}}
            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-brand-border text-xs text-brand-muted">
                <span>Supported Philippine Banks &amp; Wallets:</span>
                <div class="flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 rounded bg-[#007DFE]/10 text-[#007DFE] font-bold">GCash</span>
                    <span class="px-2 py-0.5 rounded bg-[#00B462]/10 text-[#00B462] font-bold">Maya</span>
                    <span class="px-2 py-0.5 rounded bg-[#B11116]/10 text-[#B11116] font-bold">BPI</span>
                    <span class="px-2 py-0.5 rounded bg-[#003A70]/10 text-[#003A70] font-bold">BDO</span>
                    <span class="px-2 py-0.5 rounded bg-[#FF6B00]/10 text-[#FF6B00] font-bold">UnionBank</span>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <button type="button" onclick="submitDemoPayment('pay')" class="w-full px-5 py-3 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors shadow-xs">
                    Complete Payment (₱{{ number_format($payment->amount, 2) }})
                </button>
            </div>

            <div class="text-center">
                <a href="{{ route('payments.cancelled', $payment->checkout_reference) }}" class="text-xs font-medium text-brand-muted hover:text-brand-ink underline">
                    Cancel and return to LEBROn
                </a>
            </div>
        </form>
    </section>
</div>

<script>
    function onChannelChange(channel) {
        const qrPanel = document.getElementById('sampleQrPanel');
        const cardPanel = document.getElementById('testCardPanel');
        if (channel === 'card') {
            qrPanel.classList.add('hidden');
            cardPanel.classList.remove('hidden');
        } else {
            cardPanel.classList.add('hidden');
            qrPanel.classList.remove('hidden');
        }
    }

    function toggleQrPanel() {
        document.getElementById('sampleQrPanel').classList.toggle('hidden');
    }

    function submitDemoPayment(action) {
        document.getElementById('demoActionInput').value = action;
        document.getElementById('paymongoDemoForm').submit();
    }
</script>
@endsection
