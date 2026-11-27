@extends('layouts.app')

@section('title', 'Sellers — LEBROn Admin')

@section('header')
<x-page-header
    title="Seller Management"
    subtitle="Approve new independent booksellers or suspend accounts to hide their listings."
    :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'Sellers'],
    ]"
/>
@endsection

@section('content')
<nav class="flex flex-wrap gap-2 mb-6" aria-label="Filter sellers by status">
    @foreach([null => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'suspended' => 'Suspended'] as $key => $label)
        @php $active = ($status ?: null) === ($key ?: null); @endphp
        <a href="{{ route('admin.sellers.index', $key ? ['status' => $key] : []) }}"
           class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-sm font-medium border transition-colors {{ $active ? 'bg-brand-darkgreen text-white border-brand-darkgreen' : 'bg-white text-brand-ink border-brand-border hover:bg-brand-cream' }}"
           @if($active) aria-current="page" @endif>
            <span>{{ $label }}</span>
            <span class="text-xs {{ $active ? 'text-white/80' : 'text-brand-muted' }} tabular-nums">{{ $key ? ($counts[$key] ?? 0) : $counts->sum() }}</span>
        </a>
    @endforeach
</nav>

@if($sellers->isEmpty())
    <x-empty-state title="No sellers found" :description="'There are no seller accounts' . ($status ? ' with status ' . $status : '') . ' yet.'" />
@else
    <div class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-brand-border text-sm">
                <thead class="bg-brand-cream/70">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Store</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Owner</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Listings</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Orders</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Joined</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Status</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/70">
                    @foreach($sellers as $seller)
                        <tr class="hover:bg-brand-paper/60 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-semibold text-brand-ink">{{ $seller->store_display_name }}</span>
                                @if($seller->store_description)
                                    <span class="block text-xs text-brand-muted max-w-xs truncate" title="{{ $seller->store_description }}">{{ $seller->store_description }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-brand-ink font-medium">{{ $seller->name }}</span>
                                <span class="block text-xs text-brand-muted">{{ $seller->email }}</span>
                            </td>
                            <td class="px-6 py-4 text-right tabular-nums text-brand-ink">{{ $seller->books_count }}</td>
                            <td class="px-6 py-4 text-right tabular-nums text-brand-ink">{{ $seller->seller_orders_count }}</td>
                            <td class="px-6 py-4 text-brand-muted whitespace-nowrap">{{ $seller->created_at?->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge :variant="match($seller->seller_status) { 'approved' => 'success', 'suspended' => 'danger', default => 'warning' }" dot>
                                    {{ ucfirst($seller->seller_status ?? 'pending') }}
                                </x-badge>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-1.5">
                                @if($seller->seller_status !== 'approved')
                                    <form action="{{ route('admin.sellers.approve', $seller) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition-colors">
                                            {{ $seller->seller_status === 'suspended' ? 'Reinstate' : 'Approve' }}
                                        </button>
                                    </form>
                                @endif
                                @if($seller->seller_status !== 'suspended')
                                    <form action="{{ route('admin.sellers.suspend', $seller) }}" method="POST" class="inline" data-confirm="Suspend {{ $seller->store_display_name }}? Their listings will be hidden from buyers.">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 border border-red-200 text-xs font-semibold hover:bg-red-100 transition-colors">
                                            Suspend
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $sellers->links() }}</div>
@endif
@endsection
