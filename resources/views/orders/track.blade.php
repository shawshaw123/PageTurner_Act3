@extends('layouts.app')

@section('title', 'Track Order - PageTurner')

@section('header')
<h1 class="text-3xl font-bold text-gray-900">Order Tracking: #{{ $order->id }}</h1>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    {{-- Tracking Progress --}}
    <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <div class="relative flex items-center justify-between mb-12">
            <div class="absolute left-0 top-1/2 w-full h-1 bg-gray-200 -z-10 -translate-y-1/2"></div>
            <div class="absolute left-0 top-1/2 h-1 bg-brand-darkgreen -z-10 -translate-y-1/2 transition-all duration-1000" 
                 style="width: @if($order->status == 'pending') 0% @elseif($order->status == 'processing') 33% @elseif($order->status == 'shipped') 66% @else 100% @endif"></div>
            
            {{-- Pending --}}
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 {{ in_array($order->status, ['pending', 'processing', 'shipped', 'completed']) ? 'bg-brand-darkgreen border-brand-darkgreen text-white' : 'bg-white border-gray-300 text-gray-400' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <span class="mt-2 text-xs font-semibold text-gray-600">Pending</span>
            </div>

            {{-- Processing --}}
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 {{ in_array($order->status, ['processing', 'shipped', 'completed']) ? 'bg-brand-darkgreen border-brand-darkgreen text-white' : 'bg-white border-gray-300 text-gray-400' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <span class="mt-2 text-xs font-semibold text-gray-600">Processing</span>
            </div>

            {{-- Shipped --}}
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 {{ in_array($order->status, ['shipped', 'completed']) ? 'bg-brand-darkgreen border-brand-darkgreen text-white' : 'bg-white border-gray-300 text-gray-400' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <span class="mt-2 text-xs font-semibold text-gray-600">Shipped</span>
            </div>

            {{-- Completed --}}
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 {{ $order->status == 'completed' ? 'bg-brand-darkgreen border-brand-darkgreen text-white' : 'bg-white border-gray-300 text-gray-400' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="mt-2 text-xs font-semibold text-gray-600">Delivered</span>
            </div>
        </div>

        @if($order->status == 'cancelled')
            <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg text-center mb-6">
                <p class="font-bold">Order Cancelled</p>
                <p class="text-sm">This order has been cancelled and is no longer being processed.</p>
            </div>
        @endif

        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Tracking Details</h3>
            <div class="space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-500">Scheduled Delivery</span>
                    <span class="font-medium text-gray-800">{{ $order->status == 'completed' ? 'Delivered' : 'Expected soon' }}</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-4">
                    <span class="text-gray-500">Shipping Service</span>
                    <span class="font-medium text-gray-800">PageTurner Express</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-4">
                    <span class="text-gray-500">Shipping Address</span>
                    <span class="font-medium text-gray-800 text-right">{{ $order->shipping_address }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center">
        <a href="{{ route('orders.show', $order) }}" class="text-brand-darkgreen hover:text-brand-amber font-semibold transition-colors">
            ← View Order Details
        </a>
    </div>
</div>
@endsection
