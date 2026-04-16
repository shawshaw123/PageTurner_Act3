@extends('layouts.app')

@section('title', 'Order Statistics - Admin Dashboard')

@section('header')
<h1 class="text-3xl font-bold text-gray-900">Detailed Order Statistics</h1>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    {{-- Total Volume --}}
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-sm font-medium text-gray-500 uppercase">Gross Revenue</h3>
        <p class="text-3xl font-bold text-brand-darkgreen mt-2">₱{{ number_format($stats['total_revenue'], 2) }}</p>
        <p class="text-xs text-gray-400 mt-1">From {{ $stats['completed_orders'] }} completed orders</p>
    </div>

    {{-- Order Breakdown --}}
    <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-2">
        <h3 class="text-sm font-medium text-gray-500 uppercase mb-4">Volume by Status</h3>
        <div class="flex items-center space-x-4">
            <div class="flex-1">
                <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium text-blue-700">Pending</span>
                    <span class="text-sm font-medium text-blue-700">{{ $stats['pending_orders'] }}</span>
                </div>
                <div class="w-full bg-blue-100 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $stats['total_orders'] > 0 ? ($stats['pending_orders'] / $stats['total_orders'] * 100) : 0 }}%"></div>
                </div>
            </div>
            <div class="flex-1">
                <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium text-yellow-700">Processing</span>
                    <span class="text-sm font-medium text-yellow-700">{{ $stats['processing_orders'] }}</span>
                </div>
                <div class="w-full bg-yellow-100 rounded-full h-2">
                    <div class="bg-yellow-600 h-2 rounded-full" style="width: {{ $stats['total_orders'] > 0 ? ($stats['processing_orders'] / $stats['total_orders'] * 100) : 0 }}%"></div>
                </div>
            </div>
            <div class="flex-1">
                <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium text-green-700">Completed</span>
                    <span class="text-sm font-medium text-green-700">{{ $stats['completed_orders'] }}</span>
                </div>
                <div class="w-full bg-green-100 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ $stats['total_orders'] > 0 ? ($stats['completed_orders'] / $stats['total_orders'] * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Order Activity Table --}}
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
        <h2 class="font-bold text-gray-800">Recent Order Activity</h2>
        <span class="text-xs text-gray-500">Showing last 5 entries</span>
    </div>
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase italic">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase italic">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase italic">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase italic">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase italic">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($stats['recent_orders'] as $order)
                <tr>
                    <td class="px-6 py-4 text-sm font-mono text-gray-900">#{{ $order->id }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $order->user->name }}</td>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">₱{{ number_format($order->total_amount, 2) }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 rounded text-xs {{ $order->status == 'completed' ? 'bg-green-50 text-green-700' : 'bg-blue-50 text-blue-700' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-8">
    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-brand-darkgreen hover:text-brand-amber font-bold">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Dashboard
    </a>
</div>
@endsection
