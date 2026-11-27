@extends('layouts.app')

@section('title', 'AI Usage & Monitoring — PageTurner Admin')

@section('header')
<x-page-header
    title="AI Usage &amp; Monitoring"
    subtitle="Real-time request volume, token consumption, estimated costs, and provider latency."
    :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'AI Monitoring'],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('ai.chat') }}" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
            Open AI Concierge
        </a>
        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
            Admin Dashboard
        </a>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <x-stat-card
        label="Today's Requests"
        :value="number_format($stats['today_requests'])"
        :subtext="number_format($stats['today_tokens']) . ' tokens · Est. ₱' . number_format($stats['today_cost'] * 58, 2)"
        accent="brand"
    />
    <x-stat-card
        label="All-Time Requests"
        :value="number_format($stats['total_requests'])"
        :subtext="number_format($stats['total_tokens']) . ' tokens · Est. ₱' . number_format($stats['total_cost'] * 58, 2)"
        accent="amber"
    />
    <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-5">
        <p class="text-xs font-semibold uppercase tracking-wider text-brand-muted mb-3">Provider Breakdown</p>
        <div class="space-y-2">
            @forelse($stats['by_provider'] as $provider)
                <div class="flex items-center justify-between text-sm py-1 border-b border-brand-border/60 last:border-0">
                    <span class="font-semibold text-brand-ink uppercase text-xs flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $provider['provider'] === 'openai' ? 'bg-emerald-500' : ($provider['provider'] === 'gemini' ? 'bg-sky-500' : 'bg-brand-amber') }}"></span>
                        {{ $provider['provider'] }}
                    </span>
                    <span class="text-xs font-mono text-brand-muted">{{ number_format($provider['count']) }} reqs</span>
                </div>
            @empty
                <p class="text-xs text-brand-muted">No provider traffic recorded yet.</p>
            @endforelse
        </div>
    </div>
</div>

<section class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
    <div class="px-6 py-4 border-b border-brand-border flex items-center justify-between">
        <h2 class="font-serif text-lg font-bold text-brand-ink">Recent AI Transactions</h2>
        <x-badge variant="brand" dot>Live Audit</x-badge>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-brand-border text-sm">
            <thead class="bg-brand-cream/70">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Timestamp</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Provider / Model</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Feature</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Tokens</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Latency</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-border/70">
                @php $recentLogs = \App\Models\AiUsageLog::latest()->take(10)->get(); @endphp
                @forelse($recentLogs as $log)
                    <tr class="hover:bg-brand-paper/60 transition-colors">
                        <td class="px-6 py-3.5 whitespace-nowrap font-mono text-xs text-brand-muted">{{ $log->created_at->format('M d, H:i:s') }}</td>
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            <span class="font-semibold text-xs uppercase text-brand-ink">{{ $log->provider }}</span>
                            <span class="block text-xs font-mono text-brand-muted">{{ $log->model }}</span>
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap"><x-badge variant="neutral">{{ $log->feature }}</x-badge></td>
                        <td class="px-6 py-3.5 whitespace-nowrap text-right font-mono text-xs text-brand-ink">{{ number_format($log->total_tokens) }}</td>
                        <td class="px-6 py-3.5 whitespace-nowrap text-right font-mono text-xs text-brand-muted">{{ $log->response_time ? $log->response_time . 's' : 'N/A' }}</td>
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            <x-badge :variant="$log->success ? 'success' : 'danger'" dot :title="$log->error_message ?? ''">
                                {{ $log->success ? 'Success' : 'Failed' }}
                            </x-badge>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-brand-muted">No AI transactions logged yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
