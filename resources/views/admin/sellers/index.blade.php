@extends('layouts.app')

@section('title', 'Sellers - PageTurner Admin')

@section('header')
<h1 class="text-3xl font-bold text-gray-900">Sellers</h1>
<p class="text-gray-500 mt-1">Approve new sellers, or suspend a seller to hide their listings.</p>
@endsection

@section('content')
{{-- Status filter --}}
<nav class="flex flex-wrap gap-2 mb-6" aria-label="Filter sellers by status">
    @foreach([null => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'suspended' => 'Suspended'] as $key => $label)
        @php $active = ($status ?: null) === ($key ?: null); @endphp
        <a href="{{ route('admin.sellers.index', $key ? ['status' => $key] : []) }}"
           class="px-3 py-1.5 rounded-full text-sm font-medium {{ $active ? 'bg-brand-darkgreen text-white' : 'bg-white text-gray-700 shadow hover:bg-gray-50' }}"
           @if($active) aria-current="page" @endif>
            {{ $label }}
            <span class="ml-1 {{ $active ? 'text-white/80' : 'text-gray-400' }}">{{ $key ? ($counts[$key] ?? 0) : $counts->sum() }}</span>
        </a>
    @endforeach
</nav>

@if($sellers->isEmpty())
    <x-alert type="info">No sellers {{ $status ? 'with status "' . $status . '"' : 'yet' }}.</x-alert>
@else
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Store</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Owner</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Listings</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Orders</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($sellers as $seller)
                    <tr>
                        <td class="px-6 py-3">
                            <span class="font-medium text-gray-900">{{ $seller->store_display_name }}</span>
                            @if($seller->store_description)
                                <span class="block text-xs text-gray-500 max-w-xs truncate" title="{{ $seller->store_description }}">{{ $seller->store_description }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-700">
                            {{ $seller->name }}
                            <span class="block text-xs text-gray-500">{{ $seller->email }}</span>
                        </td>
                        <td class="px-6 py-3 text-right tabular-nums">{{ $seller->books_count }}</td>
                        <td class="px-6 py-3 text-right tabular-nums">{{ $seller->seller_orders_count }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $seller->created_at?->format('M d, Y') }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ match($seller->seller_status) { 'approved' => 'bg-green-100 text-green-800', 'suspended' => 'bg-red-100 text-red-800', default => 'bg-yellow-100 text-yellow-800' } }}">
                                {{ ucfirst($seller->seller_status ?? 'pending') }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right whitespace-nowrap">
                            @if($seller->seller_status !== 'approved')
                                <form action="{{ route('admin.sellers.approve', $seller) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-green-700">
                                        {{ $seller->seller_status === 'suspended' ? 'Reinstate' : 'Approve' }}
                                    </button>
                                </form>
                            @endif
                            @if($seller->seller_status !== 'suspended')
                                <form action="{{ route('admin.sellers.suspend', $seller) }}" method="POST" class="inline ml-1" onsubmit="return confirm('Suspend {{ addslashes($seller->store_display_name) }}? Their listings will be hidden from buyers.')">
                                    @csrf
                                    <button type="submit" class="bg-red-100 text-red-700 px-3 py-1 rounded text-xs font-semibold hover:bg-red-200">Suspend</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $sellers->links() }}</div>
@endif
@endsection
