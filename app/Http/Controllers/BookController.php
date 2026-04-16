<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Services\ImageService;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with('category');

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
            'min' => Book::min('price'),
            'max' => Book::max('price')
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
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Process cover image using ImageService
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = ImageService::processBookCover($request->file('cover_image'));
        }

        Book::create($validated);

        return redirect()->route('books.index')
            ->with('success', 'Book added successfully!');
    }

    public function show(Book $book)
    {
        $book->load(['category', 'reviews.user']);
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
            'isbn' => 'required|string|unique:books,isbn,' . $book->id,
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
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

        return redirect()->route('books.index')
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
}
