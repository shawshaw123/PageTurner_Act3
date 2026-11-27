@extends('layouts.app')

@section('title', 'Backup #' . $backup->id . ' — LEBROn Admin')

@section('header')
<x-page-header
    :title="'Backup #' . $backup->id"
    subtitle="Snapshot monitoring details, storage metadata, and download actions."
    :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'Backups', 'url' => route('admin.backup.index')],
        ['label' => 'Backup #' . $backup->id],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('admin.backup.index') }}" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
            &larr; Back to Backups
        </a>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="lg:col-span-8 space-y-6">
        <section class="bg-white rounded-2xl border border-brand-border shadow-xs p-6">
            <h2 class="font-serif text-lg font-bold text-brand-ink mb-4">Monitoring Information</h2>
            <dl class="divide-y divide-brand-border/70 text-sm">
                <div class="py-3 flex justify-between gap-4">
                    <dt class="text-brand-muted">Backup Type</dt>
                    <dd><x-badge variant="brand">{{ strtoupper($backup->backup_type) }}</x-badge></dd>
                </div>
                <div class="py-3 flex justify-between gap-4">
                    <dt class="text-brand-muted">Status</dt>
                    <dd>
                        <x-badge :variant="match($backup->status) { 'completed' => 'success', 'failed' => 'danger', 'started', 'running' => 'warning', default => 'neutral' }" dot>
                            {{ ucfirst($backup->status) }}
                        </x-badge>
                    </dd>
                </div>
                <div class="py-3 flex justify-between gap-4">
                    <dt class="text-brand-muted">Storage Disk</dt>
                    <dd class="font-mono text-xs text-brand-ink">{{ $backup->disk }}</dd>
                </div>
                <div class="py-3 flex justify-between gap-4">
                    <dt class="text-brand-muted">File Path</dt>
                    <dd class="font-mono text-xs text-brand-ink break-all text-right">{{ $backup->path ?? 'Not generated' }}</dd>
                </div>
                <div class="py-3 flex justify-between gap-4">
                    <dt class="text-brand-muted">File Size</dt>
                    <dd class="font-semibold text-brand-ink">{{ $backup->getSizeFormattedAttribute() }}</dd>
                </div>
                <div class="py-3 flex justify-between gap-4">
                    <dt class="text-brand-muted">Started At</dt>
                    <dd class="text-brand-ink">{{ $backup->started_at->format('M j, Y H:i:s') }}</dd>
                </div>
                @if($backup->completed_at)
                    <div class="py-3 flex justify-between gap-4">
                        <dt class="text-brand-muted">Completed At</dt>
                        <dd class="text-brand-ink">{{ $backup->completed_at->format('M j, Y H:i:s') }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        @if($backup->error_message)
            <section class="bg-white rounded-2xl border border-red-200 shadow-xs overflow-hidden">
                <div class="px-6 py-3.5 bg-red-50 border-b border-red-200">
                    <h2 class="font-serif text-base font-bold text-red-900">Error Output</h2>
                </div>
                <div class="p-5">
                    <pre class="bg-stone-900 text-stone-100 rounded-xl p-4 text-xs font-mono overflow-x-auto whitespace-pre-wrap">{{ $backup->error_message }}</pre>
                </div>
            </section>
        @endif
    </div>

    <aside class="lg:col-span-4 space-y-6">
        <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-6 space-y-3">
            <h2 class="font-serif text-lg font-bold text-brand-ink mb-2">Actions</h2>
            @if($backup->status === 'completed' && $backup->path)
                <a href="{{ route('admin.backup.download', $backup->id) }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                    Download Backup
                </a>
            @endif
            <form action="{{ route('admin.backup.destroy', $backup->id) }}" method="POST" data-confirm="Delete this backup record and its archive files?">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm font-semibold hover:bg-red-100 transition-colors">
                    Delete Record
                </button>
            </form>
        </div>

        @if($backup->metadata)
            <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-6">
                <h2 class="font-serif text-lg font-bold text-brand-ink mb-3">Metadata</h2>
                <dl class="divide-y divide-brand-border/70 text-xs">
                    @foreach($backup->metadata as $key => $value)
                        <div class="py-2.5">
                            <dt class="text-brand-muted uppercase tracking-wider">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                            <dd class="mt-0.5 font-semibold text-brand-ink break-all">{{ is_array($value) ? json_encode($value) : $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    </aside>
</div>
@endsection
