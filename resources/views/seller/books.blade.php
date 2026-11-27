@extends('layouts.app')

@section('title', 'My Listings — LEBROn')

@section('header')
<x-page-header
    title="My Listings"
    subtitle="Search, edit, restock, or remove books from your storefront."
    :breadcrumbs="[
        ['label' => 'Seller Hub', 'url' => route('seller.dashboard')],
        ['label' => 'My Listings'],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('books.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Listing
        </a>
    </x-slot:actions>
</x-page-header>
@endsection

@section('content')
<form method="GET" action="{{ route('seller.books') }}" class="mb-6 flex flex-col sm:flex-row gap-2.5 max-w-lg">
    <label for="search" class="sr-only">Search your listings</label>
    <input type="search" id="search" name="search" value="{{ request('search') }}" placeholder="Search by title or ISBN..."
           class="flex-1 rounded-xl border-brand-border bg-white text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
    <button type="submit" class="px-4 py-2 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">Search</button>
    @if(request('search'))
        <a href="{{ route('seller.books') }}" class="px-4 py-2 rounded-xl border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream text-center">Clear</a>
    @endif
</form>

@if($books->isEmpty())
    @php
        $emptyTitle = request('search') ? 'No matching listings' : 'No book listings yet';
        $emptyDescription = request('search')
            ? 'No listings match "' . request('search') . '".'
            : 'Add your first book listing to start selling on LEBROn.';
    @endphp
    <x-empty-state :title="$emptyTitle" :description="$emptyDescription">
        <x-slot:action>
            <a href="{{ route('books.create') }}" class="px-5 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                Add First Book
            </a>
        </x-slot:action>
    </x-empty-state>
@else
    <div class="bg-white rounded-2xl border border-brand-border shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-brand-border text-sm">
                <thead class="bg-brand-cream/70">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Book</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">ISBN</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-brand-muted">Category</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Price</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Stock</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-brand-muted">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/70">
                    @foreach($books as $book)
                        <tr class="hover:bg-brand-paper/60 transition-colors">
                            <td class="px-6 py-4">
                                <a href="{{ route('books.show', $book) }}" class="font-semibold text-brand-ink hover:text-brand-darkgreen">{{ $book->title }}</a>
                                <span class="block text-xs text-brand-muted">{{ $book->author }}</span>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-brand-muted">{{ $book->isbn }}</td>
                            <td class="px-6 py-4 text-brand-muted">{{ $book->category?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-right font-semibold tabular-nums">₱{{ number_format($book->price, 2) }}</td>
                            <td class="px-6 py-4 text-right">
                                <x-badge :variant="$book->stock_quantity === 0 ? 'danger' : ($book->stock_quantity <= 5 ? 'warning' : 'success')">
                                    {{ $book->stock_quantity }}
                                </x-badge>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-3 font-medium">
                                <a href="{{ route('books.edit', $book) }}" class="text-brand-darkgreen hover:text-brand-amber">Edit</a>
                                <form action="{{ route('books.destroy', $book) }}" method="POST" class="inline" data-confirm="Delete listing '{{ $book->title }}'?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $books->links() }}</div>
@endif
@endsection
