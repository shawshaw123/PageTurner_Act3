<?php

namespace App\Http\Controllers;

use App\Integrations\IsbnLookup\CoverDownloader;
use App\Models\Book;
use App\Models\Category;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with(['category', 'seller:id,name,store_name'])
            ->visible()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        // Advanced search - full-text search across multiple fields
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        // Filter by price range
        if ($request->has('min_price') && $request->min_price != '') {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price != '') {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by stock availability
        if ($request->has('in_stock') && $request->in_stock == '1') {
            $query->where('stock_quantity', '>', 0);
        }

        // Filter by rating
        if ($request->has('min_rating') && $request->min_rating != '') {
            $query->withCount(['reviews as average_rating' => function($q) {
                $q->select(\DB::raw('coalesce(avg(rating),0)'));
            }])->having('average_rating', '>=', $request->min_rating);
        }

        // Sorting options
        $sortBy = $request->get('sort_by', 'title');
        $sortOrder = $request->get('sort_order', 'asc');

        switch ($sortBy) {
            case 'price':
                $query->orderBy('price', $sortOrder);
                break;
            case 'rating':
                $query->withCount(['reviews as average_rating' => function($q) {
                    $q->select(\DB::raw('coalesce(avg(rating),0)'));
                }])->orderBy('average_rating', $sortOrder);
                break;
            case 'date':
                $query->orderBy('created_at', $sortOrder);
                break;
            case 'title':
            default:
                $query->orderBy('title', $sortOrder);
                break;
        }

        $books = $query->paginate(12)->appends($request->query());
        $categories = Category::orderBy('name')->get();
        
        // Get price range for filters
        $priceRange = [
            'min' => Book::visible()->min('price'),
            'max' => Book::visible()->max('price')
        ];

        return view('books.index', compact('books', 'categories', 'priceRange'));
    }

    public function create()
    {
        \Illuminate\Support\Facades\Gate::authorize('create', Book::class);
        $categories = Category::all();
        return view('books.create', compact('categories'));
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('create', Book::class);

        // Sellers own the listings they create; admin-created books are store stock.
        $sellerId = $request->user()->isSeller() ? $request->user()->id : null;

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'isbn' => ['required', 'string', 'max:20', $this->uniqueIsbnRule($sellerId)],
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            // Filled in by the ISBN lookup (IP-1) on the form:
            'cover_url' => 'nullable|string|max:2048',
            'isbn_lookup_used' => 'nullable|boolean',
            'form_started_at' => 'nullable|integer',
        ], [
            'isbn.unique' => 'You already have a listing with this ISBN.',
        ]);

        // An uploaded cover wins; otherwise use the cover found by the ISBN lookup.
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = ImageService::processBookCover($request->file('cover_image'));
        } elseif (!empty($validated['cover_url'])) {
            $validated['cover_image'] = app(CoverDownloader::class)->download($validated['cover_url']);
        }

        $validated['seller_id'] = $sellerId;
        $validated['isbn_lookup_used'] = $request->boolean('isbn_lookup_used');
        // How long the form took to fill in, to compare lookup and manual entry (Objective 2).
        $validated['listing_seconds'] = $this->secondsSince($validated['form_started_at'] ?? null);
        unset($validated['cover_url'], $validated['form_started_at']);

        Book::create($validated);

        return redirect()->route($request->user()->isSeller() ? 'seller.books' : 'books.index')
            ->with('success', 'Book added successfully!');
    }

    public function show(Book $book)
    {
        // Hidden listings (inactive, or seller not approved) are only visible to
        // the people who can manage them.
        if (!$book->isVisible() && !auth()->user()?->can('update', $book)) {
            abort(404);
        }

        $book->load(['category', 'seller:id,name,store_name', 'reviews.user']);
        return view('books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $book);
        $categories = Category::all();
        return view('books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $book);
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'isbn' => ['required', 'string', 'max:20', $this->uniqueIsbnRule($book->seller_id, $book->id)],
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ], [
            'isbn.unique' => 'This seller already has a listing with this ISBN.',
        ]);

        // Handle image update
        if ($request->hasFile('cover_image')) {
            // Delete old image
            ImageService::deleteImage($book->cover_image);
            
            // Process new image
            $validated['cover_image'] = ImageService::processBookCover($request->file('cover_image'));
        }

        $book->update($validated);

        return redirect()->route('books.show', $book)
            ->with('success', 'Book updated successfully!');
    }

    public function destroy(Book $book)
    {
        \Illuminate\Support\Facades\Gate::authorize('delete', $book);
        
        // Delete associated image
        ImageService::deleteImage($book->cover_image);
        
        $book->delete();

        return redirect()->route(auth()->user()->isSeller() ? 'seller.books' : 'books.index')
            ->with('success', 'Book deleted successfully!');
    }

    /**
     * Mark the book as out of stock (set stock_quantity to 0).
     */
    public function markOutOfStock(Book $book)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $book);

        $book->update(['stock_quantity' => 0]);

        return redirect()->back()->with('success', 'Book marked out of stock.');
    }

    /**
     * Restock a book to a given quantity.
     */
    public function restock(Request $request, Book $book)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $book);

        $validated = $request->validate([
            'stock_quantity' => 'required|integer|min:0',
        ]);

        $book->update(['stock_quantity' => $validated['stock_quantity']]);

        return redirect()->back()->with('success', 'Book restocked successfully.');
    }

    /** An ISBN may appear once per seller (several sellers can sell the same book). */
    private function uniqueIsbnRule(?int $sellerId, ?int $ignoreBookId = null)
    {
        return Rule::unique('books', 'isbn')
            ->where(fn ($q) => $sellerId === null ? $q->whereNull('seller_id') : $q->where('seller_id', $sellerId))
            ->ignore($ignoreBookId);
    }

    /** Seconds since the form was opened (milliseconds timestamp from the browser), within sane bounds. */
    private function secondsSince(?int $startedAtMs): ?int
    {
        if (!$startedAtMs) {
            return null;
        }

        $seconds = (int) round((now()->getTimestampMs() - $startedAtMs) / 1000);

        return $seconds > 0 && $seconds < 86400 ? $seconds : null;
    }
}
