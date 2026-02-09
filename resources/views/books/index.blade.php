@extends('layouts.app')

@section('title', 'All Books - PageTurner')

@section('header')
<h1 class="text-3xl font-bold text-gray-900">All Books</h1>
@endsection

@section('content')
{{-- Search and Filter --}}
<div class="bg-white p-4 rounded-lg shadow mb-6">
    <form action="{{ route('books.index') }}" method="GET" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title or author..." class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen">
        </div>
        <div class="w-48">
            <select name="category" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-brand-darkgreen text-white px-6 py-2 rounded-md hover:bg-brand-amber hover:text-brand-darkgreen transition-colors">
            Search
        </button>
    </form>
</div>

{{-- Books Grid --}}
@if($books->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($books as $book)
            <x-book-card :book="$book" />
        @endforeach
    </div>
    
    {{-- Pagination --}}
    <div class="mt-8">
        {{ $books->withQueryString()->links() }}
    </div>
@else
    <x-alert type="info">
        No books found matching your criteria.
    </x-alert>
@endif
@endsection
