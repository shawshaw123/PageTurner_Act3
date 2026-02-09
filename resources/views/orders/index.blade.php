@extends('layouts.app')

@section('title', 'My Orders - PageTurner')

@section('header')
<h1 class="text-3xl font-bold text-gray-900">{{ auth()->user()->isAdmin() ? 'All Orders' : 'My Orders' }}</h1>
@endsection

@section('content')
@if($orders->count() > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Order ID
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Date
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Total Amount
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($orders as $order)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            #{{ $order->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $order->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            ₱{{ number_format($order->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($order->status == 'completed') bg-brand-amber/20 text-brand-darkgreen
                                @elseif($order->status == 'processing') bg-yellow-100 text-yellow-800
                                @elseif($order->status == 'cancelled') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('orders.show', $order) }}" class="text-brand-darkgreen hover:text-brand-amber font-medium transition-colors">
                                View Details
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    <div class="mt-8">
        {{ $orders->links() }}
    </div>
@else
    <x-alert type="info">
        You haven't placed any orders yet. <a href="{{ route('books.index') }}" class="text-brand-darkgreen hover:text-brand-amber font-semibold underline">Browse books</a> to get started!
    </x-alert>
@endif
@endsection
