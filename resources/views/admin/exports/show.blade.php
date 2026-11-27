@extends('layouts.app')

@section('title', 'Export #' . $export->id . ' — LEBROn Admin')

@section('header')
<x-page-header
    :title="'Export #' . $export->id"
    :subtitle="ucfirst($export->model_type) . ' (' . strtoupper($export->format) . ')'"
    :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'Exports', 'url' => route('admin.export.index')],
        ['label' => 'Export #' . $export->id],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('admin.export.index') }}" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
            &larr; All Exports
        </a>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <section class="lg:col-span-8 bg-white rounded-2xl border border-brand-border shadow-xs p-6">
        <h2 class="font-serif text-lg font-bold text-brand-ink mb-4">Export Details</h2>
        <dl class="divide-y divide-brand-border/70 text-sm">
            <div class="py-3 flex justify-between"><dt class="text-brand-muted">Dataset</dt><dd class="font-semibold text-brand-ink">{{ ucfirst($export->model_type) }}</dd></div>
            <div class="py-3 flex justify-between"><dt class="text-brand-muted">Format</dt><dd><x-badge variant="brand">{{ strtoupper($export->format) }}</x-badge></dd></div>
            <div class="py-3 flex justify-between">
                <dt class="text-brand-muted">Status</dt>
                <dd><x-badge :variant="match($export->status) { 'completed' => 'success', 'failed' => 'danger', 'processing' => 'warning', default => 'neutral' }" dot>{{ ucfirst($export->status) }}</x-badge></dd>
            </div>
            <div class="py-3 flex justify-between"><dt class="text-brand-muted">Total Records</dt><dd class="font-semibold tabular-nums">{{ number_format($export->total_records) }}</dd></div>
            <div class="py-3 flex justify-between"><dt class="text-brand-muted">Requested</dt><dd class="text-brand-ink">{{ $export->created_at->format('M j, Y H:i:s') }}</dd></div>
            @if($export->expires_at)
                <div class="py-3 flex justify-between"><dt class="text-brand-muted">Expires</dt><dd class="text-brand-ink">{{ $export->expires_at->format('M j, Y H:i:s') }}</dd></div>
            @endif
        </dl>

        @if(!empty($export->columns))
            <div class="mt-6 pt-5 border-t border-brand-border">
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-muted mb-2.5">Included Columns</h3>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($export->columns as $column)
                        <x-badge variant="neutral">{{ $column }}</x-badge>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    <aside class="lg:col-span-4 space-y-6">
        <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-6 text-center">
            @if($export->status === 'completed')
                <h2 class="font-serif text-lg font-bold text-brand-ink">Ready for Download</h2>
                <p class="text-xs text-brand-muted mt-1 mb-5">Your export file has been generated.</p>
                <a href="{{ route('admin.export.download', $export->id) }}" class="w-full inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                    Download File
                </a>
            @elseif($export->status === 'failed')
                <h2 class="font-serif text-lg font-bold text-red-800">Export Failed</h2>
                <p class="text-xs text-brand-muted mt-1">An error occurred while generating this export.</p>
            @else
                <h2 class="font-serif text-lg font-bold text-brand-ink">Processing Export&hellip;</h2>
                <p class="text-xs text-brand-muted mt-1 mb-4">Refresh to check completion status.</p>
                <button type="button" onclick="location.reload()" class="w-full px-4 py-2 rounded-xl border border-brand-border text-sm font-medium text-brand-ink hover:bg-brand-cream">
                    Refresh Status
                </button>
            @endif
        </div>

        @if($export->user)
            <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-6">
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-muted mb-2">Requested By</h3>
                <p class="font-semibold text-sm text-brand-ink">{{ $export->user->name }}</p>
                <p class="text-xs text-brand-muted">{{ $export->user->email }}</p>
            </div>
        @endif
    </aside>
</div>
@endsection
