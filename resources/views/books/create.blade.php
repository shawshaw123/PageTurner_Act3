@extends('layouts.app')

@section('title', 'Add New Book - PageTurner')

@section('header')
<h1 class="text-3xl font-bold text-gray-900">Add New Book</h1>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- IP-1: ISBN lookup (Google Books). Fills the fields below. --}}
            <input type="hidden" name="cover_url" id="cover_url" value="{{ old('cover_url') }}">
            <input type="hidden" name="isbn_lookup_used" id="isbn_lookup_used" value="{{ old('isbn_lookup_used', 0) }}">
            <input type="hidden" name="form_started_at" id="form_started_at" value="{{ old('form_started_at') }}">

            <div class="mb-6 rounded-lg border border-brand-darkgreen/30 bg-green-50/50 p-4">
                <label for="isbn" class="block text-gray-700 font-medium mb-2">ISBN *</label>
                <div class="flex gap-2">
                    <input type="text" name="isbn" id="isbn" value="{{ old('isbn') }}" inputmode="numeric" autocomplete="off" placeholder="e.g. 9780593135204" class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen @error('isbn') border-red-500 @enderror" required>
                    <button type="button" id="isbn-lookup" data-url="{{ route('books.isbn-lookup') }}" class="shrink-0 bg-brand-darkgreen text-white px-4 py-2 rounded-md font-medium hover:bg-brand-amber hover:text-brand-darkgreen transition-colors disabled:opacity-60">
                        Look up
                    </button>
                </div>
                @error('isbn')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p id="isbn-status" class="text-sm text-gray-600 mt-2" aria-live="polite">Enter the ISBN and click <strong>Look up</strong> to fill in the details from Google Books.</p>
                <div id="isbn-cover" class="mt-3 {{ old('cover_url') ? '' : 'hidden' }} flex items-center gap-3">
                    <img id="isbn-cover-img" src="{{ old('cover_url') }}" alt="Cover found by ISBN lookup" class="h-28 w-auto rounded shadow">
                    <p class="text-xs text-gray-500">This cover will be used unless you upload your own below.</p>
                </div>
            </div>

            <div class="mb-4">
                <label for="title" class="block text-gray-700 font-medium mb-2">Title *</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen @error('title') border-red-500 @enderror" required>
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="author" class="block text-gray-700 font-medium mb-2">Author *</label>
                <input type="text" name="author" id="author" value="{{ old('author') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" required>
            </div>

            <div class="mb-4">
                <label for="publisher" class="block text-gray-700 font-medium mb-2">Publisher</label>
                <input type="text" name="publisher" id="publisher" value="{{ old('publisher') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen">
            </div>

            <div class="mb-4">
                <label for="category_id" class="block text-gray-700 font-medium mb-2">Category *</label>
                <select name="category_id" id="category_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" required>
                    <option value="">Select a category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-4">
                <label for="price" class="block text-gray-700 font-medium mb-2">Price (₱) *</label>
                <input type="number" step="0.01" min="0" name="price" id="price" value="{{ old('price') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" required>
                @error('price')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="stock_quantity" class="block text-gray-700 font-medium mb-2">Stock Quantity *</label>
                <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', 0) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen" required>
            </div>
            
            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-medium mb-2">Description</label>
                <textarea name="description" id="description" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-darkgreen focus:border-brand-darkgreen">{{ old('description') }}</textarea>
            </div>
            
            <div class="mb-6">
                <label for="cover_image" class="block text-gray-700 font-medium mb-2">Cover Image</label>
                <input type="file" name="cover_image" id="cover_image" accept="image/*" class="w-full border-gray-300 rounded-md shadow-sm">
            </div>
            
            <div class="flex justify-end space-x-4">
                <a href="{{ route('books.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400 transition">
                    Cancel
                </a>
                <button type="submit" class="bg-brand-darkgreen text-white px-6 py-2 rounded hover:bg-brand-amber hover:text-brand-darkgreen transition-colors">
                    Add Book
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// IP-1: look the ISBN up on Google Books and fill in the form.
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var button = $('isbn-lookup'), status = $('isbn-status');

    // Start the listing timer when the form is first opened (kept across validation errors).
    if (!$('form_started_at').value) {
        $('form_started_at').value = Date.now();
    }

    function setStatus(text, tone) {
        status.textContent = text;
        status.className = 'text-sm mt-2 ' + (tone === 'ok' ? 'text-green-700' : tone === 'error' ? 'text-red-600' : 'text-gray-600');
    }

    function fill(id, value) {
        if (value && $(id)) $(id).value = value;
    }

    async function lookUp() {
        var isbn = $('isbn').value.trim();
        if (!isbn) { setStatus('Enter an ISBN first.', 'error'); $('isbn').focus(); return; }

        button.disabled = true;
        setStatus('Looking up ' + isbn + ' on Google Books…');
        try {
            var response = await fetch(button.dataset.url + '?isbn=' + encodeURIComponent(isbn), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            var json = await response.json().catch(function () { return {}; });

            if (!response.ok) {
                setStatus(json.message || 'The lookup failed. Please fill in the details manually.', 'error');
                return;
            }

            var book = json.data;
            fill('isbn', book.isbn);
            fill('title', book.title);
            fill('author', book.author);
            fill('publisher', book.publisher);
            fill('description', book.description);
            $('isbn_lookup_used').value = 1;

            if (book.cover_url) {
                $('cover_url').value = book.cover_url;
                $('isbn-cover-img').src = book.cover_url;
                $('isbn-cover').classList.remove('hidden');
            } else {
                $('cover_url').value = '';
                $('isbn-cover').classList.add('hidden');
            }

            var missing = ['title', 'author'].filter(function (f) { return !book[f]; });
            var text = 'Found on Google Books: "' + (book.title || isbn) + '". Check the details, then set the category, price and stock.';
            if (missing.length) text += ' Missing: ' + missing.join(', ') + '.';
            if (json.already_listed) text += ' Note: you already have a listing with this ISBN.';
            setStatus(text, json.already_listed ? 'error' : 'ok');
            $('category_id').focus();
        } catch (error) {
            setStatus('Could not reach the server. Please fill in the details manually.', 'error');
        } finally {
            button.disabled = false;
        }
    }

    button.addEventListener('click', lookUp);
    $('isbn').addEventListener('keydown', function (event) {
        if (event.key === 'Enter') { event.preventDefault(); lookUp(); }
    });
})();
</script>
@endsection
