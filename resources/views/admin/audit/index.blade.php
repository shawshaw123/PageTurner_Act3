@extends('layouts.app')

@section('title', 'Audit Logs — PageTurner Admin')

@section('header')
<x-page-header
    title="Audit Logs"
    subtitle="Comprehensive security and model change trail across users, books, orders, and reviews."
    :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'Audit Logs'],
    ]"
>
    <x-slot:actions>
        <form action="{{ route('admin.audit.export') }}" method="GET" class="inline">
            <input type="hidden" name="format" value="csv">
            <button type="submit" class="px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                Export CSV
            </button>
        </form>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
@if(isset($stats))
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Total Events" :value="number_format($stats['total_audits'])" accent="brand" />
        <x-stat-card label="Today" :value="number_format($stats['today_audits'])" accent="info" />
        <x-stat-card label="Updates &amp; Deletes" :value="number_format($stats['critical_events'])" accent="warning" />
        <x-stat-card label="Unique Users" :value="number_format($stats['unique_users'])" accent="neutral" />
    </div>
@endif

{{-- Filter Form --}}
<section class="bg-white rounded-2xl border border-brand-border shadow-xs p-5 mb-6" aria-label="Filter audit logs">
    <form method="GET" action="{{ route('admin.audit.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3.5 items-end">
        <div class="lg:col-span-2">
            <label for="search" class="block text-xs font-semibold text-brand-muted mb-1">Search IP, URL, or Agent</label>
            <input type="search" name="search" id="search" value="{{ request('search') }}" placeholder="e.g. 127.0.0.1 or /books"
                   class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
        </div>

        <div>
            <label for="event" class="block text-xs font-semibold text-brand-muted mb-1">Event</label>
            <select name="event" id="event" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                <option value="">All Events</option>
                @foreach(['created', 'updated', 'deleted', 'login', 'logout'] as $ev)
                    <option value="{{ $ev }}" @selected(request('event') === $ev)>{{ ucfirst($ev) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="auditable_type" class="block text-xs font-semibold text-brand-muted mb-1">Model</label>
            <select name="auditable_type" id="auditable_type" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                <option value="">All Models</option>
                @foreach(['User', 'Book', 'Order', 'Review'] as $model)
                    <option value="{{ $model }}" @selected(request('auditable_type') === $model)>{{ $model }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="date_from" class="block text-xs font-semibold text-brand-muted mb-1">From</label>
            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">Filter</button>
            @if(request()->anyFilled(['search', 'event', 'auditable_type', 'date_from', 'date_to']))
                <a href="{{ route('admin.audit.index') }}" class="px-3 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream">Clear</a>
            @endif
        </div>
    </form>
</section>

{{-- Audit Table --}}
@if($audits->count() > 0)
    <div class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-brand-border text-sm">
                <thead class="bg-brand-cream/70">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">ID</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Actor</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Event</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Resource</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">IP Address</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Timestamp</th>
                        <th scope="col" class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/70">
                    @foreach($audits as $audit)
                        <tr class="hover:bg-brand-paper/60 transition-colors">
                            <td class="px-5 py-3.5 font-mono text-xs text-brand-muted">#{{ $audit->id }}</td>
                            <td class="px-5 py-3.5 font-medium text-brand-ink">{{ $audit->user ? $audit->user->name : 'System' }}</td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <x-badge :variant="match($audit->event) { 'created' => 'success', 'updated' => 'warning', 'deleted' => 'danger', default => 'info' }">
                                    {{ ucfirst($audit->event) }}
                                </x-badge>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-brand-ink">
                                {{ class_basename($audit->auditable_type) }} <span class="text-xs font-mono text-brand-muted">#{{ $audit->auditable_id ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-brand-muted">{{ $audit->ip_address }}</td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-brand-muted">{{ $audit->created_at->format('M j, Y H:i:s') }}</td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                <a href="{{ route('admin.audit.show', $audit->id) }}" class="text-xs font-semibold text-brand-darkgreen hover:text-brand-amber">Inspect &rarr;</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-6">{{ $audits->withQueryString()->links() }}</div>
@else
    <x-empty-state title="No audit logs found" description="No recorded events match your current filter selection." />
@endif
@endsection
