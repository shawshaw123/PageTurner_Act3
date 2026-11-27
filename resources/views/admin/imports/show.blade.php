@extends('layouts.app')

@section('title', 'Import #' . $import->id . ' — PageTurner Admin')

@section('header')
<x-page-header
    :title="'Import #' . $import->id"
    :subtitle="$import->original_filename ?? $import->filename"
    :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'Imports', 'url' => route('admin.import.index')],
        ['label' => 'Import #' . $import->id],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('admin.import.index') }}" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
            &larr; All Imports
        </a>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
@php
    $percentage = $import->total_rows > 0 ? ($import->processed_rows / $import->total_rows) * 100 : ($import->status === 'completed' ? 100 : 0);
@endphp

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="lg:col-span-8 space-y-6">
        <section class="bg-white rounded-2xl border border-brand-border shadow-xs p-6">
            <h2 class="font-serif text-lg font-bold text-brand-ink mb-4">Import Summary</h2>
            <dl class="divide-y divide-brand-border/70 text-sm">
                <div class="py-3 flex justify-between"><dt class="text-brand-muted">Dataset</dt><dd class="font-semibold">{{ ucfirst($import->model_type) }}</dd></div>
                <div class="py-3 flex justify-between"><dt class="text-brand-muted">Original File</dt><dd class="font-mono text-xs">{{ $import->original_filename }}</dd></div>
                <div class="py-3 flex justify-between">
                    <dt class="text-brand-muted">Status</dt>
                    <dd><x-badge :variant="match($import->status) { 'completed' => 'success', 'failed' => 'danger', 'processing' => 'warning', default => 'neutral' }" dot>{{ ucfirst($import->status) }}</x-badge></dd>
                </div>
                <div class="py-3 flex justify-between"><dt class="text-brand-muted">Total Rows</dt><dd class="tabular-nums">{{ number_format($import->total_rows) }}</dd></div>
                <div class="py-3 flex justify-between"><dt class="text-brand-muted">Successful</dt><dd class="font-semibold text-emerald-700 tabular-nums">{{ number_format($import->successful_rows) }}</dd></div>
                <div class="py-3 flex justify-between"><dt class="text-brand-muted">Failed</dt><dd class="font-semibold text-red-600 tabular-nums">{{ number_format($import->failed_rows) }}</dd></div>
                <div class="py-3 flex justify-between"><dt class="text-brand-muted">Requested</dt><dd>{{ $import->created_at->format('M j, Y H:i:s') }}</dd></div>
            </dl>
        </section>

        @if($import->errors && count($import->errors) > 0)
            <section class="bg-white rounded-2xl border border-red-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 bg-red-50 border-b border-red-200">
                    <h2 class="font-serif text-base font-bold text-red-900">Row Validation Errors</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-brand-border text-sm">
                        <thead class="bg-brand-cream/60">
                            <tr>
                                <th class="px-5 py-2.5 text-left text-xs font-semibold uppercase text-brand-muted">Row</th>
                                <th class="px-5 py-2.5 text-left text-xs font-semibold uppercase text-brand-muted">Message</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border/70">
                            @foreach($import->errors as $error)
                                <tr>
                                    <td class="px-5 py-3 font-mono text-xs text-brand-muted">{{ $error['row'] ?? 'N/A' }}</td>
                                    <td class="px-5 py-3 text-red-700 text-xs">{{ $error['message'] ?? ($error['general'] ?? json_encode($error)) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>

    <aside class="lg:col-span-4 space-y-6">
        <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-6">
            <h2 class="font-serif text-lg font-bold text-brand-ink mb-2">Completion Progress</h2>
            <p class="text-3xl font-bold text-brand-darkgreen tabular-nums">{{ round($percentage, 1) }}%</p>
            <div class="w-full h-2.5 rounded-full bg-stone-100 overflow-hidden mt-3">
                <div class="h-full rounded-full {{ $import->status === 'failed' ? 'bg-red-500' : 'bg-brand-darkgreen' }}" style="width: {{ min($percentage, 100) }}%"></div>
            </div>
            @if($import->status === 'processing')
                <button type="button" onclick="location.reload()" class="mt-4 w-full px-4 py-2 rounded-xl border border-brand-border text-sm font-medium text-brand-ink hover:bg-brand-cream">
                    Refresh Progress
                </button>
            @endif
        </div>

        @if($import->user)
            <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-6">
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-muted mb-2">Uploaded By</h3>
                <p class="font-semibold text-sm text-brand-ink">{{ $import->user->name }}</p>
                <p class="text-xs text-brand-muted">{{ $import->user->email }}</p>
            </div>
        @endif
    </aside>
</div>
@endsection
