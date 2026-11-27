@extends('layouts.app')

@section('title', 'Database Backups — LEBROn Admin')

@section('header')
<x-page-header
    title="Database Backups"
    subtitle="Monitor automated backup health, trigger manual snapshots, and manage retention."
    :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'Backups'],
    ]"
>
    <x-slot:actions>
        <form action="{{ route('admin.backup.store') }}" method="POST" class="inline">
            @csrf
            <input type="hidden" name="backup_type" value="manual">
            <input type="hidden" name="disks[]" value="local">
            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                Run Backup Now
            </button>
        </form>
        <form action="{{ route('admin.backup.clean') }}" method="POST" class="inline" data-confirm="Clean up old backups according to the retention policy?">
            @csrf
            <button type="submit" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
                Clean Old Backups
            </button>
        </form>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
{{-- Backup Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <x-stat-card label="Total Backups" :value="$backupStats['total_backups']" accent="brand" />
    <x-stat-card label="Successful" :value="$backupStats['successful_backups']" accent="success" />
    <x-stat-card label="Failed" :value="$backupStats['failed_backups']" accent="danger" />
    <x-stat-card label="Success Rate" :value="$backupStats['success_rate'] . '%'" accent="amber" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    {{-- Recent Backups Table --}}
    <section class="lg:col-span-8 bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border">
            <h2 class="font-serif text-lg font-bold text-brand-ink">Backup History</h2>
        </div>

        @if($backups->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-brand-border text-sm">
                    <thead class="bg-brand-cream/70">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Type</th>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Status</th>
                            <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Size</th>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Started</th>
                            <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border/70">
                        @foreach($backups as $backup)
                            <tr class="hover:bg-brand-paper/60 transition-colors">
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <x-badge variant="brand">{{ strtoupper($backup->backup_type) }}</x-badge>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <x-badge :variant="match($backup->status) { 'completed' => 'success', 'failed' => 'danger', 'running', 'started' => 'warning', default => 'neutral' }" dot>
                                        {{ ucfirst($backup->status) }}
                                    </x-badge>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-right font-mono text-xs text-brand-ink">
                                    {{ $backup->getSizeFormattedAttribute() }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs text-brand-muted">
                                    {{ $backup->started_at->format('M j, Y H:i') }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-right space-x-2">
                                    @if($backup->status === 'completed' && ($backup->path || $backup->file_path))
                                        <a href="{{ route('admin.backup.download', $backup->id) }}" class="text-xs font-semibold text-brand-darkgreen hover:text-brand-amber">Download</a>
                                    @endif
                                    <form action="{{ route('admin.backup.destroy', $backup->id) }}" method="POST" class="inline" data-confirm="Delete this backup record and its archive file?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-brand-border">{{ $backups->links() }}</div>
        @else
            <div class="p-8 text-center text-sm text-brand-muted">
                No backups recorded yet. Click <strong>Run Backup Now</strong> to create your first snapshot.
            </div>
        @endif
    </section>

    {{-- Schedule & Retention Sidebar --}}
    <aside class="lg:col-span-4 space-y-6">
        <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-6">
            <h2 class="font-serif text-lg font-bold text-brand-ink mb-4">Last Backup Status</h2>
            @if($lastBackup)
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-brand-muted">Status</span>
                        <x-badge :variant="$lastBackup->status === 'completed' ? 'success' : 'danger'" dot>{{ ucfirst($lastBackup->status) }}</x-badge>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-brand-muted">Started</span>
                        <span class="font-medium text-brand-ink">{{ $lastBackup->started_at->format('M j, Y H:i') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-brand-muted">Size</span>
                        <span class="font-mono text-xs text-brand-ink">{{ $lastBackup->getSizeFormattedAttribute() }}</span>
                    </div>
                </div>
            @else
                <p class="text-sm text-brand-muted">No backups have been executed yet.</p>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-6 space-y-5">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-muted mb-2.5">Automated Schedule</h3>
                <ul class="space-y-1.5 text-sm text-brand-ink">
                    <li>&bull; Daily at 02:00 AM</li>
                    <li>&bull; Weekly on Sunday at 02:00 AM</li>
                    <li>&bull; Monthly on the 1st at 02:00 AM</li>
                </ul>
            </div>
            <div class="pt-4 border-t border-brand-border">
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-muted mb-2.5">Retention Policy</h3>
                <ul class="space-y-1.5 text-sm text-brand-ink">
                    <li>&bull; Keep 7 daily snapshots</li>
                    <li>&bull; Keep 4 weekly snapshots</li>
                    <li>&bull; Keep 12 monthly snapshots</li>
                </ul>
            </div>
        </div>
    </aside>
</div>
@endsection
