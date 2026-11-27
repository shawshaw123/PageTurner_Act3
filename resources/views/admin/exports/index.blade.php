@extends('layouts.app')

@section('title', 'Export Management — PageTurner Admin')

@section('header')
<x-page-header
    title="Export Management"
    subtitle="Generate and download filtered bookstore datasets in Excel, CSV, or PDF."
    :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'Exports'],
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
<div class="space-y-8">
    {{-- Create New Export --}}
    <section class="bg-white rounded-2xl border border-brand-border shadow-xs p-6">
        <h2 class="font-serif text-lg font-bold text-brand-ink mb-4">Create New Export</h2>
        <form action="{{ route('admin.export.store') }}" method="POST" id="exportForm" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="model_type" class="block text-xs font-semibold text-brand-muted mb-1">Dataset <span class="text-red-500">*</span></label>
                    <select name="model_type" id="model_type" required onchange="updateColumnCheckboxes(this.value)" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                        <option value="books">Books</option>
                        <option value="orders">Orders</option>
                        <option value="users">Users</option>
                    </select>
                </div>

                <div>
                    <label for="format" class="block text-xs font-semibold text-brand-muted mb-1">Format <span class="text-red-500">*</span></label>
                    <select name="format" id="format" required class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                        <option value="xlsx">Excel (XLSX)</option>
                        <option value="csv">CSV</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>

                <div>
                    <label for="date_range" class="block text-xs font-semibold text-brand-muted mb-1">Date Range</label>
                    <select name="date_range" id="date_range" onchange="document.getElementById('customDateRange').classList.toggle('hidden', this.value !== 'custom')" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month">This Month</option>
                        <option value="this_year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
            </div>

            <div id="customDateRange" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="date_from" class="block text-xs font-semibold text-brand-muted mb-1">From Date</label>
                    <input type="date" name="date_from" id="date_from" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                </div>
                <div>
                    <label for="date_to" class="block text-xs font-semibold text-brand-muted mb-1">To Date</label>
                    <input type="date" name="date_to" id="date_to" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold text-brand-muted mb-2">Columns to Include <span class="text-red-500">*</span></p>
                <div id="columnCheckboxes" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5"></div>
            </div>

            <div class="pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                    Start Export
                </button>
            </div>
        </form>
    </section>

    {{-- Recent Exports Table --}}
    <section class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border">
            <h2 class="font-serif text-lg font-bold text-brand-ink">Export History</h2>
        </div>

        @if($exports->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-brand-border text-sm">
                    <thead class="bg-brand-cream/70">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">ID</th>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Requested By</th>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Dataset</th>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Format</th>
                            <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Records</th>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Status</th>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Created</th>
                            <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border/70">
                        @foreach($exports as $export)
                            <tr class="hover:bg-brand-paper/60 transition-colors">
                                <td class="px-5 py-3.5 font-mono text-xs text-brand-muted">#{{ $export->id }}</td>
                                <td class="px-5 py-3.5 text-brand-ink">{{ $export->user ? $export->user->name : 'System' }}</td>
                                <td class="px-5 py-3.5 font-medium text-brand-ink">{{ ucfirst($export->model_type) }}</td>
                                <td class="px-5 py-3.5"><x-badge variant="neutral">{{ strtoupper($export->format) }}</x-badge></td>
                                <td class="px-5 py-3.5 text-right tabular-nums">{{ number_format($export->total_records) }}</td>
                                <td class="px-5 py-3.5">
                                    <x-badge :variant="match($export->status) { 'completed' => 'success', 'failed' => 'danger', 'processing' => 'warning', default => 'neutral' }" dot>
                                        {{ ucfirst($export->status) }}
                                    </x-badge>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-brand-muted whitespace-nowrap">{{ $export->created_at->format('M j, H:i') }}</td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-2">
                                    <a href="{{ route('admin.export.show', $export->id) }}" class="text-xs font-semibold text-brand-darkgreen hover:text-brand-amber">Details</a>
                                    @if($export->isCompleted() && $export->download_url)
                                        <a href="{{ $export->download_url }}" class="text-xs font-semibold text-emerald-700 hover:underline">Download</a>
                                    @endif
                                    <form action="{{ route('admin.export.destroy', $export->id) }}" method="POST" class="inline" data-confirm="Delete export #{{ $export->id }}?">
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
            <div class="px-6 py-4 border-t border-brand-border">{{ $exports->links() }}</div>
        @else
            <div class="p-8 text-center text-sm text-brand-muted">No exports created yet. Use the form above to generate your first export.</div>
        @endif
    </section>
</div>

<script>
const exportColumns = {
    books: [
        { name: 'id', label: 'ID', checked: true },
        { name: 'title', label: 'Title', checked: true },
        { name: 'author', label: 'Author', checked: true },
        { name: 'isbn', label: 'ISBN', checked: true },
        { name: 'price', label: 'Price', checked: true },
        { name: 'stock_quantity', label: 'Stock', checked: true },
        { name: 'description', label: 'Description', checked: false },
        { name: 'created_at', label: 'Created At', checked: false }
    ],
    orders: [
        { name: 'id', label: 'Order ID', checked: true },
        { name: 'user_id', label: 'Customer', checked: true },
        { name: 'total_amount', label: 'Total Amount', checked: true },
        { name: 'status', label: 'Status', checked: true },
        { name: 'created_at', label: 'Order Date', checked: true }
    ],
    users: [
        { name: 'id', label: 'User ID', checked: true },
        { name: 'name', label: 'Name', checked: true },
        { name: 'email', label: 'Email', checked: true },
        { name: 'role', label: 'Role', checked: true },
        { name: 'created_at', label: 'Created At', checked: true }
    ]
};

function updateColumnCheckboxes(modelType) {
    const container = document.getElementById('columnCheckboxes');
    const cols = exportColumns[modelType] || [];
    container.innerHTML = cols.map(col => `
        <label class="inline-flex items-center gap-2 text-sm text-brand-ink cursor-pointer">
            <input type="checkbox" name="columns[]" value="${col.name}" ${col.checked ? 'checked' : ''} class="rounded border-brand-border text-brand-darkgreen focus:ring-brand-darkgreen">
            <span>${col.label}</span>
        </label>
    `).join('');
}

document.addEventListener('DOMContentLoaded', () => updateColumnCheckboxes(document.getElementById('model_type').value));
</script>
@endsection
