@extends('layouts.app')

@section('title', 'Audit #' . $audit->id . ' — PageTurner Admin')

@section('header')
<x-page-header
    :title="'Audit Event #' . $audit->id"
    :subtitle="ucfirst($audit->event) . ' on ' . class_basename($audit->auditable_type) . ' #' . ($audit->auditable_id ?? 'N/A')"
    :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'Audit Logs', 'url' => route('admin.audit.index')],
        ['label' => 'Event #' . $audit->id],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('admin.audit.index') }}" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
            &larr; Back to Logs
        </a>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <section class="lg:col-span-5 bg-white rounded-2xl border border-brand-border shadow-xs p-6">
        <h2 class="font-serif text-lg font-bold text-brand-ink mb-4">Event Context</h2>
        <dl class="divide-y divide-brand-border/70 text-sm">
            <div class="py-3 flex justify-between gap-4">
                <dt class="text-brand-muted">Event</dt>
                <dd>
                    <x-badge :variant="match($audit->event) { 'created' => 'success', 'updated' => 'warning', 'deleted' => 'danger', default => 'info' }">
                        {{ strtoupper($audit->event) }}
                    </x-badge>
                </dd>
            </div>
            <div class="py-3 flex justify-between gap-4">
                <dt class="text-brand-muted">Model</dt>
                <dd class="font-mono text-xs text-brand-ink">{{ class_basename($audit->auditable_type) }} (ID: {{ $audit->auditable_id }})</dd>
            </div>
            <div class="py-3 flex justify-between gap-4">
                <dt class="text-brand-muted">User</dt>
                <dd class="font-medium text-brand-ink">{{ $audit->user ? $audit->user->name : 'System' }}</dd>
            </div>
            <div class="py-3 flex justify-between gap-4">
                <dt class="text-brand-muted">IP Address</dt>
                <dd class="font-mono text-xs text-brand-ink">{{ $audit->ip_address }}</dd>
            </div>
            <div class="py-3">
                <dt class="text-brand-muted mb-1">URL</dt>
                <dd class="font-mono text-xs text-brand-ink break-all">{{ $audit->url }}</dd>
            </div>
            <div class="py-3">
                <dt class="text-brand-muted mb-1">User Agent</dt>
                <dd class="text-xs text-brand-muted break-all">{{ $audit->user_agent }}</dd>
            </div>
            <div class="py-3 flex justify-between gap-4">
                <dt class="text-brand-muted">Recorded At</dt>
                <dd class="text-xs text-brand-ink">{{ $audit->created_at->format('M j, Y H:i:s') }}</dd>
            </div>
        </dl>
    </section>

    <section class="lg:col-span-7 bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border">
            <h2 class="font-serif text-lg font-bold text-brand-ink">Changes Trail</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-brand-border text-sm">
                <thead class="bg-brand-cream/70">
                    <tr>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Field</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Previous</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">New</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/70">
                    @forelse($changes as $field => $change)
                        <tr>
                            <td class="px-5 py-3 font-medium text-brand-ink whitespace-nowrap">{{ ucfirst(str_replace('_', ' ', $field)) }}</td>
                            <td class="px-5 py-3 bg-red-50/50 text-red-700 font-mono text-xs line-through break-all">
                                {{ is_array($change['old']) ? json_encode($change['old']) : (is_null($change['old']) ? 'NULL' : $change['old']) }}
                            </td>
                            <td class="px-5 py-3 bg-emerald-50/50 text-emerald-800 font-mono text-xs break-all">
                                {{ is_array($change['new']) ? json_encode($change['new']) : (is_null($change['new']) ? 'NULL' : $change['new']) }}
                            </td>
                        </tr>
                    @empty
                        @if($audit->event === 'created')
                            @foreach($newValues as $field => $value)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-brand-ink">{{ ucfirst(str_replace('_', ' ', $field)) }}</td>
                                    <td class="px-5 py-3 text-brand-muted">&mdash;</td>
                                    <td class="px-5 py-3 text-emerald-800 font-mono text-xs break-all">{{ is_array($value) ? json_encode($value) : (is_null($value) ? 'NULL' : $value) }}</td>
                                </tr>
                            @endforeach
                        @elseif($audit->event === 'deleted')
                            @foreach($oldValues as $field => $value)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-brand-ink">{{ ucfirst(str_replace('_', ' ', $field)) }}</td>
                                    <td class="px-5 py-3 text-red-700 font-mono text-xs line-through break-all">{{ is_array($value) ? json_encode($value) : (is_null($value) ? 'NULL' : $value) }}</td>
                                    <td class="px-5 py-3 text-brand-muted">&mdash;</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center text-brand-muted">No attribute changes recorded for this event.</td>
                            </tr>
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
