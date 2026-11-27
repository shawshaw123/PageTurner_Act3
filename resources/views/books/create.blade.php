@extends('layouts.app')

@section('title', 'Add New Book — PageTurner')

@section('header')
<x-page-header
    title="Add New Book"
    subtitle="Look up an ISBN to auto-fill book metadata, or enter details manually."
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Books', 'url' => route('books.index')],
        ['label' => 'Add Book'],
    ]"
/>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl border border-brand-border shadow-xs p-6 sm:p-8">
        <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="cover_url" id="cover_url" value="{{ old('cover_url') }}">
            <input type="hidden" name="isbn_lookup_used" id="isbn_lookup_used" value="{{ old('isbn_lookup_used', 0) }}">
            <input type="hidden" name="form_started_at" id="form_started_at" value="{{ old('form_started_at') }}">

            {{-- ISBN Lookup Panel --}}
            <div class="rounded-xl border border-brand-darkgreen/20 bg-brand-cream/70 p-5">
                <label for="isbn" class="block text-sm font-semibold text-brand-ink mb-1.5">ISBN <span class="text-red-500">*</span></label>
                <div class="flex flex-col sm:flex-row gap-2.5">
                    <input type="text" name="isbn" id="isbn" value="{{ old('isbn') }}" inputmode="numeric" autocomplete="off" placeholder="e.g. 9780593135204"
                           class="flex-1 rounded-lg border-brand-border bg-white text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen @error('isbn') border-red-400 @enderror" required>
                    <button type="button" id="isbn-lookup" data-url="{{ route('books.isbn-lookup') }}"
                            class="shrink-0 px-4 py-2 rounded-lg bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors disabled:opacity-60">
                        Look up ISBN
                    </button>
                </div>
                @error('isbn')
                    <p class="text-red-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
                <p id="isbn-status" class="text-xs text-brand-muted mt-2" aria-live="polite">Enter an ISBN-10 or ISBN-13 and click <strong>Look up ISBN</strong> to prefill details from Google Books.</p>
                <div id="isbn-cover" class="mt-3 {{ old('cover_url') ? '' : 'hidden' }} flex items-center gap-3">
                    <img id="isbn-cover-img" src="{{ old('cover_url') }}" alt="Cover preview from ISBN lookup" class="h-24 w-auto rounded-md shadow-xs border border-brand-border bg-white p-1">
                    <p class="text-xs text-brand-muted">This cover will be used automatically unless you upload your own image below.</p>
                </div>
            </div>

            {{-- Book Identity --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label for="title" class="block text-sm font-medium text-brand-ink mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                           class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen @error('title') border-red-400 @enderror">
                    @error('title')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="author" class="block text-sm font-medium text-brand-ink mb-1">Author <span class="text-red-500">*</span></label>
                    <input type="text" name="author" id="author" value="{{ old('author') }}" required
                           class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen @error('author') border-red-400 @enderror">
                    @error('author')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="publisher" class="block text-sm font-medium text-brand-ink mb-1">Publisher</label>
                    <input type="text" name="publisher" id="publisher" value="{{ old('publisher') }}"
                           class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                </div>
            </div>

            {{-- Pricing & Inventory --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-brand-border/70">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-brand-ink mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" id="category_id" required class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-brand-ink mb-1">Price (₱) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="price" id="price" value="{{ old('price') }}" required placeholder="0.00"
                           class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen @error('price') border-red-400 @enderror">
                    @error('price')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="stock_quantity" class="block text-sm font-medium text-brand-ink mb-1">Stock Quantity <span class="text-red-500">*</span></label>
                    <input type="number" min="0" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', 0) }}" required
                           class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen @error('stock_quantity') border-red-400 @enderror">
                    @error('stock_quantity')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Description & Cover --}}
            <div class="space-y-4 pt-2 border-t border-brand-border/70">
                <div>
                    <label for="description" class="block text-sm font-medium text-brand-ink mb-1">Description</label>
                    <textarea name="description" id="description" rows="4" placeholder="Write a brief synopsis or edition notes..."
                              class="w-full rounded-lg border-brand-border text-sm focus:border-brand-darkgreen focus:ring-brand-darkgreen">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label for="cover_image" class="block text-sm font-medium text-brand-ink mb-1">Cover Image</label>
                    <input type="file" name="cover_image" id="cover_image" accept="image/*"
                           class="block w-full text-sm text-brand-muted file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-cream file:text-brand-darkgreen hover:file:bg-brand-border/60">
                    @error('cover_image')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-border">
                <a href="{{ route('books.index') }}" class="px-5 py-2.5 rounded-xl border border-brand-border bg-white text-sm font-medium text-brand-ink hover:bg-brand-cream transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-darkgreen text-white text-sm font-semibold hover:bg-brand-forest transition-colors">
                    Add Book
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var button = $('isbn-lookup'), status = $('isbn-status');

    if (!$('form_started_at').value) {
        $('form_started_at').value = Date.now();
    }

    function setStatus(text, tone) {
        status.textContent = text;
        status.className = 'text-xs mt-2 ' + (tone === 'ok' ? 'text-emerald-700 font-medium' : tone === 'error' ? 'text-red-600 font-medium' : 'text-brand-muted');
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
            var text = 'Found on Google Books: "' + (book.title || isbn) + '". Review the details, then set category, price, and stock.';
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
