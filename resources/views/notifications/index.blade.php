@extends('layouts.app')

@section('title', 'Notifications — PageTurner')

@section('header')
<x-page-header
    title="Notifications"
    subtitle="Stay updated on new book reviews, moderation events, and order activity."
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Notifications'],
    ]"
>
    @if(auth()->user()->unreadNotifications->count() > 0)
        <x-slot:actions>
            <form action="{{ route('notifications.readAll') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-lg border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
                    Mark All as Read
                </button>
            </form>
        </x-slot:actions>
    @endif
</x-page-header>
@endsection

@section('content')
<div class="max-w-4xl space-y-4">
    @forelse(auth()->user()->notifications->sortByDesc('created_at') as $notification)
        <article class="bg-white rounded-xl border border-brand-border p-5 flex items-start justify-between gap-4 {{ is_null($notification->read_at) ? 'ring-1 ring-brand-darkgreen/20 bg-brand-paper/40' : '' }}">
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    @if($notification->type === 'new_review')
                        <x-badge variant="info">New Review</x-badge>
                    @elseif($notification->type === 'review_status')
                        <x-badge variant="success">Review Status</x-badge>
                    @else
                        <x-badge variant="neutral">Update</x-badge>
                    @endif
                    <span class="text-xs text-brand-muted">{{ $notification->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-sm text-brand-ink">{{ $notification->data['message'] ?? ($notification->data['notification'] ?? 'Notification update') }}</p>
                @if($notification->type === 'new_review' && isset($notification->data['book_id']))
                    <a href="{{ route('books.show', $notification->data['book_id']) }}" class="inline-block mt-2.5 text-xs font-semibold text-brand-darkgreen hover:text-brand-amber">
                        View Book &rarr;
                    </a>
                @endif
            </div>

            <form action="{{ route('notifications.markAsRead', $notification) }}" method="POST" class="shrink-0">
                @csrf
                @method('PATCH')
                <button type="submit" class="p-1.5 rounded-lg text-brand-muted hover:text-brand-ink hover:bg-brand-cream transition-colors" aria-label="Dismiss notification">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </form>
        </article>
    @empty
        <x-empty-state title="All caught up" description="You don't have any notifications right now." />
    @endforelse
</div>
@endsection
