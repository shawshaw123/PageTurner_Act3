@extends('layouts.app')

@section('title', 'Data Portability — LEBROn')

@section('header')
<x-page-header
    title="Data Portability &amp; Privacy"
    subtitle="Export your personal data, order history, and reading activity in portable formats, or manage account deletion."
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Profile', 'url' => route('profile.edit')],
        ['label' => 'Data Portability'],
    ]"
/>
@endsection

@section('content')
<div class="max-w-5xl space-y-8">
    <x-alert type="info" title="Your Data Rights (GDPR)">
        You can download a machine-readable copy of your account profile, order history, and reading reviews at any time. Export files remain available for 7 days.
    </x-alert>

    {{-- Export Options Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Personal Data --}}
        <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-6 flex flex-col">
            <div class="w-10 h-10 rounded-xl bg-brand-darkgreen/10 text-brand-darkgreen flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.85" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <h2 class="font-serif text-lg font-bold text-brand-ink">Personal Data</h2>
            <p class="text-sm text-brand-muted mt-1 flex-1">Complete profile details, orders, and reviews packaged as structured JSON.</p>
            <form action="{{ route('user.data-portability.export-personal') }}" method="POST" class="mt-5">
                @csrf
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                    Export Personal Data (JSON)
                </button>
            </form>
        </div>

        {{-- Order History --}}
        <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-6 flex flex-col">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.85" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <h2 class="font-serif text-lg font-bold text-brand-ink">Order History</h2>
            <p class="text-sm text-brand-muted mt-1">Export all your purchases in spreadsheet or PDF format.</p>
            <form action="{{ route('user.data-portability.export-orders') }}" method="POST" class="mt-4 space-y-3 mt-auto">
                @csrf
                <div>
                    <label for="order_export_format" class="block text-xs font-semibold text-brand-muted mb-1">Format</label>
                    <select id="order_export_format" name="format" class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                        <option value="xlsx">Excel (XLSX)</option>
                        <option value="csv">CSV</option>
                        <option value="pdf">PDF Summary</option>
                    </select>
                </div>
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                    Export Orders
                </button>
            </form>
        </div>

        {{-- Reading History --}}
        <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-6 flex flex-col">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.85" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <h2 class="font-serif text-lg font-bold text-brand-ink">Reading History</h2>
            <p class="text-sm text-brand-muted mt-1 flex-1">Download a JSON archive of every book you have purchased and reviewed.</p>
            <form action="{{ route('user.data-portability.export-reading') }}" method="POST" class="mt-5">
                @csrf
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                    Export Reading History
                </button>
            </form>
        </div>
    </div>

    {{-- Recent Export History --}}
    <section class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border">
            <h2 class="font-serif text-lg font-bold text-brand-ink">Recent Export Requests</h2>
        </div>
        @if(isset($exportHistory) && $exportHistory->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-brand-border text-sm">
                    <thead class="bg-brand-cream/60">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Format</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Requested</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Download</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border/70">
                        @foreach($exportHistory as $export)
                            <tr>
                                <td class="px-6 py-3.5 font-medium text-brand-ink">{{ ucwords(str_replace('_', ' ', $export->model_type)) }}</td>
                                <td class="px-6 py-3.5"><x-badge variant="neutral">{{ strtoupper($export->format) }}</x-badge></td>
                                <td class="px-6 py-3.5">
                                    <x-badge :variant="$export->status === 'completed' ? 'success' : ($export->status === 'failed' ? 'danger' : 'warning')" dot>
                                        {{ ucfirst($export->status) }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-3.5 text-brand-muted">{{ $export->created_at->format('M j, Y H:i') }}</td>
                                <td class="px-6 py-3.5 text-right">
                                    @if($export->isCompleted() && !$export->isExpired())
                                        <a href="{{ route('user.data-portability.download', $export->id) }}" class="text-brand-darkgreen hover:text-brand-amber font-semibold">Download</a>
                                    @else
                                        <span class="text-xs text-brand-muted">{{ $export->isExpired() ? 'Expired' : 'Unavailable' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center text-sm text-brand-muted">No export requests yet. Choose an export option above to generate a file.</div>
        @endif
    </section>

    {{-- Account Deletion Danger Zone --}}
    <section class="bg-white rounded-2xl border border-red-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 bg-red-50 border-b border-red-200">
            <h2 class="font-serif text-lg font-bold text-red-900">Delete Account</h2>
            <p class="text-xs text-red-700 mt-0.5">This permanently anonymizes your account and cannot be undone.</p>
        </div>
        <div class="p-6">
            <form action="{{ route('user.data-portability.delete-account') }}" method="POST" data-confirm="Permanently delete and anonymize your account? This cannot be undone.">
                @csrf
                @method('DELETE')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="delete_dp_password" class="block text-sm font-medium text-brand-ink mb-1">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" id="delete_dp_password" required class="w-full rounded-lg border-brand-border text-sm focus:border-red-500 focus:ring-red-500">
                        @error('password')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="confirmation" class="block text-sm font-medium text-brand-ink mb-1">Type <code class="bg-red-50 text-red-700 px-1.5 py-0.5 rounded">DELETE</code> to confirm <span class="text-red-500">*</span></label>
                        <input type="text" name="confirmation" id="confirmation" required placeholder="DELETE" class="w-full rounded-lg border-brand-border text-sm focus:border-red-500 focus:ring-red-500">
                        @error('confirmation')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="mt-5">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-colors">
                        Permanently Delete My Account
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
