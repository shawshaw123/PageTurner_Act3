@extends('layouts.app')

@section('title', 'Import Management — LEBROn Admin')

@section('header')
<x-page-header
    title="Import Management"
    subtitle="Upload Excel or CSV files to bulk-import bookstore records with validation."
    :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'Imports'],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('admin.import.template', ['type' => 'books']) }}" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
            Download Template
        </a>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
<div class="space-y-8">
    {{-- Upload Form --}}
    <section class="bg-white rounded-2xl border border-brand-border shadow-xs p-6">
        <h2 class="font-serif text-lg font-bold text-brand-ink mb-4">Start New Import</h2>
        <form action="{{ route('admin.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="model_type" class="block text-xs font-semibold text-brand-muted mb-1">Dataset <span class="text-red-500">*</span></label>
                    <select name="model_type" id="model_type" required class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                        <option value="books">Books</option>
                        <option value="users">Users</option>
                    </select>
                </div>

                <div>
                    <label for="update_existing" class="block text-xs font-semibold text-brand-muted mb-1">Duplicate Handling</label>
                    <select name="update_existing" id="update_existing" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                        <option value="0">Skip existing records</option>
                        <option value="1">Update existing records</option>
                    </select>
                </div>

                <div>
                    <label for="file" class="block text-xs font-semibold text-brand-muted mb-1">Spreadsheet File <span class="text-red-500">*</span></label>
                    <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv" required
                           class="block w-full text-sm text-brand-muted file:mr-3 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-cream file:text-brand-darkgreen hover:file:bg-brand-border/60">
                </div>
            </div>

            <p class="text-xs text-brand-muted">Supported formats: XLSX, XLS, CSV (maximum 10MB).</p>

            <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                Upload &amp; Queue Import
            </button>
        </form>
    </section>

    {{-- Recent Imports Table --}}
    <section class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border">
            <h2 class="font-serif text-lg font-bold text-brand-ink">Import History</h2>
        </div>

        @if($imports->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-brand-border text-sm">
                    <thead class="bg-brand-cream/70">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">ID</th>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">File</th>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Model</th>
                            <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Processed / Total</th>
                            <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Failed</th>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Status</th>
                            <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border/70">
                        @foreach($imports as $import)
                            <tr class="hover:bg-brand-paper/60 transition-colors">
                                <td class="px-5 py-3.5 font-mono text-xs text-brand-muted">#{{ $import->id }}</td>
                                <td class="px-5 py-3.5 font-medium text-brand-ink max-w-xs truncate" title="{{ $import->original_filename ?? $import->filename }}">
                                    {{ $import->original_filename ?? $import->filename }}
                                </td>
                                <td class="px-5 py-3.5"><x-badge variant="neutral">{{ ucfirst($import->model_type) }}</x-badge></td>
                                <td class="px-5 py-3.5 text-right tabular-nums">{{ number_format($import->processed_rows) }} / {{ number_format($import->total_rows) }}</td>
                                <td class="px-5 py-3.5 text-right tabular-nums {{ $import->failed_rows > 0 ? 'text-red-600 font-semibold' : 'text-brand-muted' }}">{{ number_format($import->failed_rows) }}</td>
                                <td class="px-5 py-3.5">
                                    <x-badge :variant="match($import->status) { 'completed' => 'success', 'failed' => 'danger', 'processing' => 'warning', default => 'neutral' }" dot>
                                        {{ ucfirst($import->status) }}
                                    </x-badge>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.import.show', $import->id) }}" class="text-xs font-semibold text-brand-darkgreen hover:text-brand-amber">Inspect &rarr;</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-brand-border">{{ $imports->links() }}</div>
        @else
            <div class="p-8 text-center text-sm text-brand-muted">No imports recorded yet. Upload a file above to get started.</div>
        @endif
    </section>
</div>
@endsection
