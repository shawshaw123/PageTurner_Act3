@extends('layouts.app')

@section('title', 'API Rate Limits — PageTurner Admin')

@section('header')
<x-page-header
    title="API Rate Limits"
    subtitle="Monitor endpoint consumption, inspect throttled traffic, and manage user or IP limits."
    :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'API Rate Limits'],
    ]"
>
    <x-slot:actions>
        <button type="button" onclick="location.reload()" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
            Refresh
        </button>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
{{-- Filters --}}
<section class="bg-white rounded-2xl border border-brand-border shadow-xs p-5 mb-6" aria-label="Filter API rate limits">
    <form method="GET" action="{{ route('admin.api-rate-limits.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3.5 items-end">
        <div>
            <label for="identifier_type" class="block text-xs font-semibold text-brand-muted mb-1">Identifier Type</label>
            <select name="identifier_type" id="identifier_type" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                <option value="">All Types</option>
                <option value="user" @selected(request('identifier_type') === 'user')>User</option>
                <option value="ip" @selected(request('identifier_type') === 'ip')>IP Address</option>
            </select>
        </div>

        <div>
            <label for="tier" class="block text-xs font-semibold text-brand-muted mb-1">Tier</label>
            <select name="tier" id="tier" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                <option value="">All Tiers</option>
                @foreach(['public', 'auth', 'standard', 'premium', 'admin'] as $tier)
                    <option value="{{ $tier }}" @selected(request('tier') === $tier)>{{ ucfirst($tier) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="endpoint" class="block text-xs font-semibold text-brand-muted mb-1">Endpoint</label>
            <input type="text" name="endpoint" id="endpoint" value="{{ request('endpoint') }}" placeholder="/api/books"
                   class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
        </div>

        <div>
            <label for="show_hits_only" class="block text-xs font-semibold text-brand-muted mb-1">Traffic Scope</label>
            <select name="show_hits_only" id="show_hits_only" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                <option value="">All Requests</option>
                <option value="1" @selected(request('show_hits_only') == '1')>Limit Hits Only</option>
            </select>
        </div>

        <div>
            <label for="date_from" class="block text-xs font-semibold text-brand-muted mb-1">From Date</label>
            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">Apply</button>
            @if(request()->anyFilled(['identifier_type', 'tier', 'endpoint', 'show_hits_only', 'date_from', 'date_to']))
                <a href="{{ route('admin.api-rate-limits.index') }}" class="px-3 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream">Clear</a>
            @endif
        </div>
    </form>
</section>

{{-- Rate Limits Table --}}
@if($rateLimits->count() > 0)
    <div class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-brand-border text-sm">
                <thead class="bg-brand-cream/70">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Identifier</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Endpoint</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Tier</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Usage</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Window</th>
                        <th scope="col" class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/70">
                    @foreach($rateLimits as $rateLimit)
                        @php
                            $usagePercent = $rateLimit->limit > 0 ? ($rateLimit->requests_count / $rateLimit->limit) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-brand-paper/60 transition-colors">
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="font-mono text-xs font-semibold text-brand-ink">{{ $rateLimit->identifier }}</span>
                                <x-badge variant="neutral" class="ml-1.5">{{ $rateLimit->identifier_type }}</x-badge>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-brand-muted">{{ $rateLimit->endpoint }}</td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <x-badge :variant="match($rateLimit->tier) { 'admin' => 'danger', 'premium' => 'warning', 'standard' => 'success', default => 'neutral' }">
                                    {{ ucfirst($rateLimit->tier) }}
                                </x-badge>
                            </td>
                            <td class="px-5 py-3.5 min-w-[170px]">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-mono text-brand-ink">{{ number_format($rateLimit->requests_count) }} / {{ number_format($rateLimit->limit) }}</span>
                                    <span class="font-semibold {{ $usagePercent >= 90 ? 'text-red-600' : ($usagePercent >= 70 ? 'text-amber-600' : 'text-emerald-700') }}">{{ round($usagePercent, 1) }}%</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-stone-100 overflow-hidden">
                                    <div class="h-full rounded-full {{ $usagePercent >= 90 ? 'bg-red-500' : ($usagePercent >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min($usagePercent, 100) }}%"></div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-brand-muted">{{ $rateLimit->window_start->format('M j, Y H:i') }}</td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-right space-x-1.5">
                                @if($rateLimit->identifier_type === 'user')
                                    <form action="{{ route('admin.api-rate-limits.clearUser', $rateLimit->identifier) }}" method="POST" class="inline" data-confirm="Clear the API rate limit for user #{{ $rateLimit->identifier }}?">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-md border border-brand-border text-xs font-semibold text-brand-ink hover:bg-brand-cream">Clear</button>
                                    </form>
                                    <form action="{{ route('admin.api-rate-limits.blockUser', $rateLimit->identifier) }}" method="POST" class="inline" data-confirm="Block user #{{ $rateLimit->identifier }} from API access for 60 minutes?">
                                        @csrf
                                        <input type="hidden" name="duration_minutes" value="60">
                                        <button type="submit" class="px-2.5 py-1 rounded-md bg-red-50 text-red-700 border border-red-200 text-xs font-semibold hover:bg-red-100">Block</button>
                                    </form>
                                @elseif($rateLimit->identifier_type === 'ip')
                                    <form action="{{ route('admin.api-rate-limits.clearIp', $rateLimit->identifier) }}" method="POST" class="inline" data-confirm="Clear the API rate limit for IP {{ $rateLimit->identifier }}?">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-md border border-brand-border text-xs font-semibold text-brand-ink hover:bg-brand-cream">Clear</button>
                                    </form>
                                    <form action="{{ route('admin.api-rate-limits.blockIp', $rateLimit->identifier) }}" method="POST" class="inline" data-confirm="Block IP {{ $rateLimit->identifier }} from API access for 60 minutes?">
                                        @csrf
                                        <input type="hidden" name="duration_minutes" value="60">
                                        <button type="submit" class="px-2.5 py-1 rounded-md bg-red-50 text-red-700 border border-red-200 text-xs font-semibold hover:bg-red-100">Block</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-6">{{ $rateLimits->withQueryString()->links() }}</div>
@else
    <x-empty-state title="No rate limit entries" description="No API rate limit records match your current filters." />
@endif
@endsection
