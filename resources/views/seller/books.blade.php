@extends('layouts.app')

@section('title', 'My Listings - PageTurner')

@section('header')
<div class="flex flex-wrap items-center justify-between gap-4">
    <h1 class="text-3xl font-bold text-gray-900">My Listings</h1>
    <a href="{{ route('books.create') }}" class="bg-brand-darkgreen text-white px-4 py-2 rounded-md font-medium hover:bg-brand-amber hover:text-brand-darkgreen transition-colors">Add listing</a>
</div>
@endsection

@section('content')
<form method="GET" action="{{ route('seller.books') }}" class="mb-4 flex gap-2 max-w-md">
    <label for="search" class="sr-only">Search your listings</label>
    <input type="search" id="search" name="search" value="{{ request('search') }}" placeholder="Search by title or ISBN" class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen">
    <button type="submit" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300">Search</button>
</form>

@if($books->isEmpty())
    <x-alert type="info">
        @if(request('search'))
            No listings match "{{ request('search') }}".
        @else
            You have no listings yet. <a href="{{ route('books.create') }}" class="font-semibold underline">Add your first book</a>.
        @endif
    </x-alert>
@else
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ISBN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($books as $book)
                    <tr>
                        <td class="px-6 py-3">
                            <a href="{{ route('books.show', $book) }}" class="font-medium text-gray-900 hover:text-brand-darkgreen">{{ $book->title }}</a>
                            <span class="block text-xs text-gray-500">{{ $book->author }}</span>
                        </td>
                        <td class="px-6 py-3 text-gray-600 tabular-nums">{{ $book->isbn }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $book->category?->name }}</td>
                        <td class="px-6 py-3 text-right tabular-nums">₱{{ number_format($book->price, 2) }}</td>
                        <td class="px-6 py-3 text-right tabular-nums {{ $book->stock_quantity === 0 ? 'text-red-600 font-semibold' : ($book->stock_quantity <= 5 ? 'text-amber-600 font-semibold' : 'text-gray-700') }}">
                            {{ $book->stock_quantity }}
                        </td>
                        <td class="px-6 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('books.edit', $book) }}" class="text-brand-darkgreen hover:text-brand-amber font-medium">Edit</a>
                            <form action="{{ route('books.destroy', $book) }}" method="POST" class="inline ml-3" onsubmit="return confirm('Delete this listing?')">
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

    <div class="mt-6">{{ $books->links() }}</div>
@endif
@endsection
