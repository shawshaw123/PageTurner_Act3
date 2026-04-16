@extends('layouts.app')

@section('title', 'All Books - PageTurner')

@section('header')
<h1 class="text-3xl font-bold text-gray-900">All Books</h1>
@endsection

@section('content')
{{-- Advanced Search and Filter --}}
<div class="bg-white p-6 rounded-lg shadow mb-6">
    <form action="{{ route('books.index') }}" method="GET" class="space-y-4">
        <!-- Search Bar -->
        <div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title, author, description, or ISBN..." class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen">
        </div>
        
        <!-- Filters Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Category Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select name="category" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Price Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Min Price</label>
                <input type="number" name="min_price" value="{{ request('min_price') }}" min="{{ $priceRange['min'] }}" max="{{ $priceRange['max'] }}" step="0.01" placeholder="{{ $priceRange['min'] }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Max Price</label>
                <input type="number" name="max_price" value="{{ request('max_price') }}" min="{{ $priceRange['min'] }}" max="{{ $priceRange['max'] }}" step="0.01" placeholder="{{ $priceRange['max'] }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen">
            </div>
            
            <!-- Rating Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Min Rating</label>
                <select name="min_rating" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen">
                    <option value="">Any Rating</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ request('min_rating') == $i ? 'selected' : '' }}>
                            {{ $i }} {{ $i == 1 ? 'Star' : 'Stars' }} & Up
                        </option>
                    @endfor
                </select>
            </div>
        </div>
        
        <!-- Sort and Additional Options -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Sort By -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                <select name="sort_by" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen">
                    <option value="title" {{ request('sort_by') == 'title' ? 'selected' : '' }}>Title</option>
                    <option value="price" {{ request('sort_by') == 'price' ? 'selected' : '' }}>Price</option>
                    <option value="rating" {{ request('sort_by') == 'rating' ? 'selected' : '' }}>Rating</option>
                    <option value="date" {{ request('sort_by') == 'date' ? 'selected' : '' }}>Date Added</option>
                </select>
            </div>
            
            <!-- Sort Order -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Order</label>
                <select name="sort_order" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen">
                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Descending</option>
                </select>
            </div>
            
            <!-- In Stock Only -->
            <div class="flex items-end">
                <label class="flex items-center">
                    <input type="checkbox" name="in_stock" value="1" {{ request('in_stock') == '1' ? 'checked' : '' }} class="mr-2 border-gray-300 rounded shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen">
                    <span class="text-sm text-gray-700">In Stock Only</span>
                </label>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex gap-4">
            <button type="submit" class="bg-brand-darkgreen text-white px-6 py-2 rounded-md hover:bg-brand-amber hover:text-brand-darkgreen transition-colors">
                Apply Filters
            </button>
            <a href="{{ route('books.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-300 transition-colors">
                Clear Filters
            </a>
        </div>
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
