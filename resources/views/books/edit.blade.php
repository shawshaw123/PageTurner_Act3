@extends('layouts.app')

@section('title', 'Edit Book — LEBROn')

@section('header')
<x-page-header
    :title="'Edit Book'"
    :subtitle="$book->title"
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Books', 'url' => route('books.index')],
        ['label' => Str::limit($book->title, 30), 'url' => route('books.show', $book)],
        ['label' => 'Edit'],
    ]"
/>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-6 sm:p-8">
        <form action="{{ route('books.update', $book) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label for="title" class="block text-sm font-medium text-brand-ink mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title', $book->title) }}" required
                           class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen @error('title') border-red-400 @enderror">
                    @error('title')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="author" class="block text-sm font-medium text-brand-ink mb-1">Author <span class="text-red-500">*</span></label>
                    <input type="text" name="author" id="author" value="{{ old('author', $book->author) }}" required
                           class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-brand-ink mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" id="category_id" required class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $book->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="isbn" class="block text-sm font-medium text-brand-ink mb-1">ISBN <span class="text-red-500">*</span></label>
                    <input type="text" name="isbn" id="isbn" value="{{ old('isbn', $book->isbn) }}" required
                           class="w-full rounded-lg border-brand-border text-sm font-mono focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-brand-ink mb-1">Price (₱) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="price" id="price" value="{{ old('price', $book->price) }}" required
                           class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                </div>
                <div>
                    <label for="stock_quantity" class="block text-sm font-medium text-brand-ink mb-1">Stock Quantity <span class="text-red-500">*</span></label>
                    <input type="number" min="0" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', $book->stock_quantity) }}" required
                           class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-brand-ink mb-1">Description</label>
                <textarea name="description" id="description" rows="4"
                          class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">{{ old('description', $book->description) }}</textarea>
            </div>

            <div>
                <label for="cover_image" class="block text-sm font-medium text-brand-ink mb-1.5">Cover Image</label>
                @if($book->cover_image)
                    <div class="mb-3 flex items-center gap-3">
                        <img src="{{ asset('storage/' . $book->cover_image) }}" alt="Current cover of {{ $book->title }}" class="h-24 w-auto rounded-md border border-brand-border bg-brand-cream p-1">
                        <p class="text-xs text-brand-muted">Current cover image. Upload a new image to replace it.</p>
                    </div>
                @endif
                <input type="file" name="cover_image" id="cover_image" accept="image/*"
                       class="block w-full text-sm text-brand-muted file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-cream file:text-brand-darkgreen hover:file:bg-brand-border/60">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-border">
                <a href="{{ route('books.show', $book) }}" class="px-5 py-2.5 rounded-xl border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                    Update Book
                </button>
            </div>
        </form>
    </div>

    @can('update', $book)
        <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-6">
            <h2 class="font-serif text-lg font-bold text-brand-ink mb-1">Quick Stock Management</h2>
            <p class="text-xs text-brand-muted mb-4">Adjust availability without editing the rest of the listing.</p>
            <div class="flex flex-wrap items-center gap-4">
                <form action="{{ route('books.outOfStock', $book) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg bg-red-50 text-red-700 border border-red-200 text-sm font-semibold hover:bg-red-100 transition-colors">
                        Mark Out of Stock
                    </button>
                </form>

                <form action="{{ route('books.restock', $book) }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <label for="restock_quantity" class="sr-only">New stock quantity</label>
                    <input type="number" name="stock_quantity" id="restock_quantity" value="{{ old('stock_quantity', $book->stock_quantity) }}" min="0"
                           class="w-24 rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                        Update Stock
                    </button>
                </form>
            </div>
        </div>
    @endcan
</div>
@endsection
