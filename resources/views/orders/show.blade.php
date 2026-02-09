@extends('layouts.app')

@section('title', 'Order Details - PageTurner')

@section('header')
<h1 class="text-3xl font-bold text-gray-900">Order #{{ $order->id }}</h1>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="p-6 border-b">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-500">Ordered on {{ $order->created_at->format('M d, Y H:i') }}</p>
                <p class="mt-1">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                        @if($order->status === 'completed') bg-green-100 text-green-800 
                        @elseif($order->status === 'cancelled') bg-red-100 text-red-800 
                        @else bg-blue-100 text-blue-800 @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                </p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Total Amount</p>
                <p class="text-2xl font-bold text-brand-darkgreen">₱{{ number_format($order->total_amount, 2) }}</p>
            </div>
        </div>
    </div>

    <div class="p-6">
        <h2 class="text-lg font-semibold mb-4">Order Items</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($order->orderItems as $item)
                        <tr>
                            <td class="px-4 py-4">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <a href="{{ route('books.show', $item->book) }}" class="text-sm font-medium text-gray-900 hover:text-brand-darkgreen transition-colors">
                                            {{ $item->book->title }}
                                        </a>
                                        <p class="text-xs text-gray-500">by {{ $item->book->author }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center text-sm text-gray-500">
                                {{ $item->quantity }}
                            </td>
                            <td class="px-4 py-4 text-right text-sm text-gray-500">
                                ₱{{ number_format($item->unit_price, 2) }}
                            </td>
                            <td class="px-4 py-4 text-right text-sm font-medium text-gray-900">
                                ₱{{ number_format($item->subtotal, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @auth
        @if(auth()->user()->isAdmin())
            <div class="p-6 bg-gray-50 border-t">
                <h2 class="text-lg font-semibold mb-4 text-gray-800">Admin: Update Order Status</h2>
                <form action="{{ route('orders.updateStatus', $order) }}" method="POST" class="flex items-end space-x-4">
                    @csrf
                    @method('PATCH')
                    <div class="flex-1 max-w-xs">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-brand-darkgreen text-white px-6 py-2 rounded-md hover:bg-brand-amber hover:text-brand-darkgreen transition-colors">
                        Update Status
                    </button>
                </form>
            </div>
        @endif
    @endauth
</div>

<div class="mt-6">
    <a href="{{ route('orders.index') }}" class="text-brand-darkgreen hover:text-brand-amber font-medium transition-colors">
        &larr; Back to My Orders
    </a>
</div>
@endsection
