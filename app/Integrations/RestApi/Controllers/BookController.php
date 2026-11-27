<?php

namespace App\Integrations\RestApi\Controllers;

use App\Http\Controllers\Controller;
use App\Integrations\RestApi\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    /** GET /api/v1/books — public catalog (visible listings only). */
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer'],
            'seller_id' => ['nullable', 'integer'],
            'in_stock' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $books = Book::visible()
            ->with(['category:id,name', 'seller:id,name,store_name'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->search . '%';
                $q->where(fn ($q) => $q->where('title', 'like', $term)
                    ->orWhere('author', 'like', $term)
                    ->orWhere('isbn', 'like', $term));
            })
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('seller_id'), fn ($q) => $q->where('seller_id', $request->seller_id))
            ->when($request->boolean('in_stock'), fn ($q) => $q->where('stock_quantity', '>', 0))
            ->orderBy('title')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return BookResource::collection($books);
    }

    /** GET /api/v1/books/{book} */
    public function show(Request $request, Book $book): BookResource
    {
        // Hidden listings are only visible to the people who can manage them.
        if (!$book->isVisible() && !$request->user('sanctum')?->can('update', $book)) {
            abort(404);
        }

        return new BookResource($book->load(['category:id,name', 'seller:id,name,store_name']));
    }

    /** POST /api/v1/books — approved sellers (who own the new listing) and admins. */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Book::class);

        $sellerId = $request->user()->isSeller() ? $request->user()->id : null;
        $data = $request->validate($this->rules(sellerId: $sellerId));
        $data['seller_id'] = $sellerId;

        $book = Book::create($data);

        return (new BookResource($book->load(['category:id,name', 'seller:id,name,store_name'])))
            ->response()->setStatusCode(201);
    }

    /** PUT/PATCH /api/v1/books/{book} — the listing's seller or an admin. Send only the fields to change. */
    public function update(Request $request, Book $book): BookResource
    {
        Gate::authorize('update', $book);

        $book->update($request->validate($this->rules($book, partial: true, sellerId: $book->seller_id)));

        return new BookResource($book->fresh()->load(['category:id,name', 'seller:id,name,store_name']));
    }

    /** DELETE /api/v1/books/{book} — the listing's seller or an admin. */
    public function destroy(Book $book): JsonResponse
    {
        Gate::authorize('delete', $book);

        $book->delete();

        return response()->json(null, 204);
    }

    private function rules(?Book $book = null, bool $partial = false, ?int $sellerId = null): array
    {
        $required = $partial ? 'sometimes' : 'required';

        // An ISBN may appear once per seller (several sellers can sell the same book).
        $uniqueIsbn = Rule::unique('books', 'isbn')
            ->where(fn ($q) => $sellerId === null ? $q->whereNull('seller_id') : $q->where('seller_id', $sellerId))
            ->ignore($book?->id);

        return [
            'category_id' => [$required, 'integer', 'exists:categories,id'],
            'title' => [$required, 'string', 'max:255'],
            'author' => [$required, 'string', 'max:255'],
            'isbn' => [$required, 'string', 'max:20', $uniqueIsbn],
            'price' => [$required, 'numeric', 'min:0'],
            'stock_quantity' => [$required, 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'format' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
