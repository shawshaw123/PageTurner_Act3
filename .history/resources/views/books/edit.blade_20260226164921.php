@extends('layouts.app')

@section('title', 'Edit Book - PageTurner')

@section('header')
<h1 class="text-3xl font-bold text-gray-900">Edit Book: {{ $book->title }}</h1>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('books.update', $book) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label for="title" class="block text-gray-700 font-medium mb-2">Title *</label>
                <input type="text" name="title" id="title" value="{{ old('title', $book->title) }}" 
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen @error('title') border-red-500 @enderror" required>
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="author" class="block text-gray-700 font-medium mb-2">Author *</label>
                <input type="text" name="author" id="author" value="{{ old('author', $book->author) }}" 
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" required>
            </div>

            <div class="mb-4">
                <label for="category_id" class="block text-gray-700 font-medium mb-2">Category *</label>
                <select name="category_id" id="category_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" required>
                    <option value="">Select a category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $book->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="isbn" class="block text-gray-700 font-medium mb-2">ISBN *</label>
                    <input type="text" name="isbn" id="isbn" value="{{ old('isbn', $book->isbn) }}" 
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="stock_quantity" class="block text-gray-700 font-medium mb-2">Stock Quantity *</label>
                <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', $book->stock_quantity) }}" 
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" required>
            </div>

            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-medium mb-2">Description</label>
                <textarea name="description" id="description" rows="4" 
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen">{{ old('description', $book->description) }}</textarea>
            </div>

            <div class="mb-6">
                <label for="cover_image" class="block text-gray-700 font-medium mb-2">Cover Image</label>
                @if($book->cover_image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $book->cover_image) }}" alt="Current cover" class="h-32 object-contain">
                        <p class="text-xs text-gray-500 mt-1">Current cover image</p>
                    </div>
                @endif
                <input type="file" name="cover_image" id="cover_image" accept="image/*" 
                    class="w-full border-gray-300 rounded-md shadow-sm">
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('books.show', $book) }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400 transition">
                    Cancel
                </a>
                <button type="submit" class="bg-brand-darkgreen text-white px-6 py-2 rounded hover:bg-brand-amber hover:text-brand-darkgreen transition-colors">
                    Update Book
                </button>
            </div>
        </form>
        
        @if(auth()->user() && auth()->user()->isAdmin())
            <div class="mt-6 border-t pt-4">
                <h3 class="text-lg font-semibold mb-3">Stock management</h3>

                <form action="{{ route('books.outOfStock', $book) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">Mark Out of Stock</button>
                </form>

                <form action="{{ route('books.restock', $book) }}" method="POST" class="inline-block ml-4">
                    @csrf
                    <label for="restock_quantity" class="sr-only">Quantity</label>
                    <input type="number" name="stock_quantity" id="restock_quantity" value="{{ old('stock_quantity', $book->stock_quantity) }}" min="0" class="w-24 border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen inline-block">
                    <button type="submit" class="ml-2 bg-brand-darkgreen text-white px-4 py-2 rounded hover:bg-brand-amber hover:text-brand-darkgreen transition">Restock</button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
