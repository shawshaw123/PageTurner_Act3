@extends('layouts.app')

@section('title', 'Notifications - PageTurner')

@section('header')
<h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    @if(auth()->user()->notifications->count() > 0)
        <div class="space-y-4">
            @foreach(auth()->user()->notifications->sortByDesc('created_at') as $notification)
                <div class="bg-white rounded-lg shadow p-6 border-l-4 
                    @if($notification->type === 'new_review') border-blue-500
                    @elseif($notification->type === 'review_status') border-green-500
                    @else border-gray-500 @endif">
                    
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                @if($notification->type === 'new_review')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        📝 New Review
                                    </span>
                                @elseif($notification->type === 'review_status')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✅ Review Status
                                    </span>
                                @endif
                                <span class="text-gray-500 text-sm">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                            
                            <p class="text-gray-800">{{ $notification->data['message'] ?? $notification->data['notification'] }}</p>
                            
                            @if($notification->type === 'new_review' && isset($notification->data['book_id']))
                                <div class="mt-3">
                                    <a href="{{ route('books.show', $notification->data['book_id']) }}" 
                                       class="inline-flex items-center px-3 py-1 bg-brand-darkgreen text-white rounded hover:bg-brand-amber transition-colors text-sm">
                                        View Book
                                    </a>
                                </div>
                            @endif
                        </div>
                        
                        <form action="{{ route('notifications.markAsRead', $notification) }}" method="POST" class="ml-4">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No notifications</h3>
            <p class="mt-2 text-gray-500">You don't have any notifications yet.</p>
        </div>
    @endif
</div>
@endsection
