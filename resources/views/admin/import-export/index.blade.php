@extends('layouts.app')

@section('title', 'Import & Export — PageTurner Admin')

@section('header')
<x-page-header
    title="Import &amp; Export Management"
    subtitle="Bulk import books from spreadsheets or export catalog data to Excel, CSV, and PDF."
    :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'Import & Export'],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('admin.import-export.books.import') }}" class="px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
            Import Books
        </a>
        <a href="{{ route('admin.import-export.books.export') }}" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
            Export Books
        </a>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
{{-- Summary KPIs --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <x-stat-card label="Successful Imports" :value="$recentImports->where('status', 'completed')->count()" accent="success" />
    <x-stat-card label="Completed Exports" :value="$recentExports->where('status', 'completed')->count()" accent="brand" />
    <x-stat-card label="In Progress" :value="$recentImports->where('status', 'processing')->count() + $recentExports->where('status', 'processing')->count()" accent="warning" />
    <x-stat-card label="Failed Operations" :value="$recentImports->where('status', 'failed')->count() + $recentExports->where('status', 'failed')->count()" accent="danger" />
</div>

{{-- Action Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-6 flex flex-col">
        <h2 class="font-serif text-xl font-bold text-brand-ink">Import Books</h2>
        <p class="text-sm text-brand-muted mt-1">Upload Excel (.xlsx) or CSV files with ISBN validation and chunked background processing.</p>
        <ul class="mt-4 space-y-1.5 text-sm text-brand-ink/85 flex-1">
            <li>&bull; Supports XLSX, XLS, and CSV formats (up to 10MB)</li>
            <li>&bull; Automatic ISBN validation and category matching</li>
            <li>&bull; Row-level error reporting and progress tracking</li>
        </ul>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('admin.import-export.books.import') }}" class="flex-1 text-center px-4 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                Start Import
            </a>
            <a href="{{ route('admin.import-export.template') }}" class="flex-1 text-center px-4 py-2.5 rounded-xl border border-brand-border bg-white text-brand-ink text-sm font-medium hover:bg-brand-cream transition-colors">
                Download Template
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-6 flex flex-col">
        <h2 class="font-serif text-xl font-bold text-brand-ink">Export Catalog</h2>
        <p class="text-sm text-brand-muted mt-1">Generate filtered bookstore reports in XLSX, CSV, or PDF with custom column selection.</p>
        <ul class="mt-4 space-y-1.5 text-sm text-brand-ink/85 flex-1">
            <li>&bull; Filter by category, price range, stock status, or date</li>
            <li>&bull; Choose exact columns to include in the output</li>
            <li>&bull; Downloadable archives with 7-day retention</li>
        </ul>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('admin.import-export.books.export') }}" class="flex-1 text-center px-4 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                Export Books
            </a>
            <a href="{{ route('admin.export.index') }}" class="flex-1 text-center px-4 py-2.5 rounded-xl border border-brand-border bg-white text-brand-ink text-sm font-medium hover:bg-brand-cream transition-colors">
                All Exports
            </a>
        </div>
    </div>
</div>

{{-- Recent Activity --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <section class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border flex items-center justify-between">
            <h2 class="font-serif text-lg font-bold text-brand-ink">Recent Imports</h2>
            <a href="{{ route('admin.import.index') }}" class="text-xs font-semibold text-brand-darkgreen hover:text-brand-amber">Manage &rarr;</a>
        </div>
        <div class="divide-y divide-brand-border/70">
            @forelse($recentImports as $import)
                <div class="px-6 py-3.5 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <a href="{{ route('admin.import-export.import.show', $import->id) }}" class="text-sm font-semibold text-brand-ink hover:text-brand-darkgreen truncate block">
                            {{ $import->original_filename }}
                        </a>
                        <p class="text-xs text-brand-muted">{{ $import->created_at->diffForHumans() }}</p>
                    </div>
                    <x-badge :variant="match($import->status) { 'completed' => 'success', 'failed' => 'danger', default => 'warning' }" dot>
                        {{ ucfirst($import->status) }}
                    </x-badge>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-brand-muted">No imports recorded yet.</div>
            @endforelse
        </div>
    </section>

    <section class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border flex items-center justify-between">
            <h2 class="font-serif text-lg font-bold text-brand-ink">Recent Exports</h2>
            <a href="{{ route('admin.export.index') }}" class="text-xs font-semibold text-brand-darkgreen hover:text-brand-amber">Manage &rarr;</a>
        </div>
        <div class="divide-y divide-brand-border/70">
            @forelse($recentExports as $export)
                <div class="px-6 py-3.5 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-brand-ink truncate">{{ ucfirst($export->model_type) }} Export ({{ strtoupper($export->format) }})</p>
                        <p class="text-xs text-brand-muted">{{ $export->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="shrink-0">
                        @if($export->isCompleted())
                            <a href="{{ route('admin.import-export.download', $export->id) }}" class="px-3 py-1 rounded-lg bg-brand-cream text-brand-darkgreen text-xs font-semibold hover:bg-brand-darkgreen hover:text-white transition-colors">
                                Download
                            </a>
                        @else
                            <x-badge :variant="$export->status === 'failed' ? 'danger' : 'warning'" dot>{{ ucfirst($export->status) }}</x-badge>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-brand-muted">No exports recorded yet.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
