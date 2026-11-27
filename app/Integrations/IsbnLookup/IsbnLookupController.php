<?php

namespace App\Integrations\IsbnLookup;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * GET /books/isbn-lookup?isbn=...  (sellers and admins, from the Add Listing form)
 * Returns the book details from Google Books as JSON for the form to fill in.
 */
class IsbnLookupController extends Controller
{
    public function __invoke(Request $request, IsbnService $isbnService): JsonResponse
    {
        Gate::authorize('create', Book::class);

        $isbn = (string) $request->validate(['isbn' => ['required', 'string', 'max:20']])['isbn'];

        try {
            $book = $isbnService->lookup($isbn);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (IsbnLookupException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        if ($book === null) {
            return response()->json(['message' => 'No book with this ISBN was found on Google Books. Please fill in the details manually.'], 404);
        }

        return response()->json([
            'data' => $book,
            // Each seller may list an ISBN once; tell them if they already have.
            'already_listed' => Book::where('isbn', $book['isbn'])
                ->where('seller_id', $request->user()->isSeller() ? $request->user()->id : null)
                ->exists(),
        ]);
    }
}
