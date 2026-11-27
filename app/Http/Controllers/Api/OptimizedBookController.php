<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookListResource;
use App\Http\Resources\BookResource;
use App\Repositories\BookRepository;
use App\Services\BookCacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OptimizedBookController extends Controller
{
    public function __construct(
        private BookRepository $bookRepository,
        private BookCacheService $cacheService
    ) {}

    /**
     * Get active books catalog with cursor pagination
     * Target: < 100ms response time
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min($request->get('per_page', 100), 100); // Max 100 per page
        
        // Use repository with caching and cursor pagination
        $books = $this->bookRepository->getActiveCatalog($request, $perPage);
        
        return BookListResource::collection($books);
    }

    /**
     * Get book by ISBN with caching
     * Target: < 50ms response time
     */
    public function showByIsbn(string $isbn): JsonResponse
    {
        $book = $this->bookRepository->findByIsbn($isbn);
        
        if (!$book) {
            return response()->json([
                'error' => 'Book not found',
                'message' => "No book found with ISBN: {$isbn}"
            ], 404);
        }
        
        return response()->json(new BookResource($book));
    }

    /**
     * Get books by category with filtering
     * Target: < 150ms response time
     */
    public function getByCategory(int $categoryId, Request $request): AnonymousResourceCollection
    {
        $perPage = min($request->get('per_page', 100), 100);
        
        $books = $this->bookRepository->getByCategory($categoryId, $request, $perPage);
        
        return BookListResource::collection($books);
    }

    /**
     * Search books with full-text search
     * Target: < 300ms response time
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100'
        ]);
        
        $query = $request->get('q');
        $perPage = min($request->get('per_page', 100), 100);
        
        // Try to get from cache first
        $cachedResults = $this->cacheService->getCachedSearchResults($query);
        
        if ($cachedResults) {
            return BookListResource::collection($cachedResults);
        }
        
        // Perform search and cache results
        $books = $this->bookRepository->search($query, $request, $perPage);
        
        // Cache search results for future requests
        $this->cacheService->cacheSearchResults($query, $books->items());
        
        return BookListResource::collection($books);
    }

    /**
     * Get bestsellers
     */
    public function bestsellers(): JsonResponse
    {
        $bestsellers = $this->cacheService->getCachedBestsellers();
        
        if (!$bestsellers) {
            $this->cacheService->cacheBestsellers();
            $bestsellers = $this->cacheService->getCachedBestsellers();
        }
        
        return response()->json([
            'data' => $bestsellers,
            'cached' => true,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Get new releases
     */
    public function newReleases(): JsonResponse
    {
        $cacheKey = 'new_releases:active';
        $newReleases = cache()->tags(['books', 'new_releases'])->get($cacheKey);
        
        if (!$newReleases) {
            $this->cacheService->preloadBookData();
            $newReleases = cache()->tags(['books', 'new_releases'])->get($cacheKey);
        }
        
        return response()->json([
            'data' => $newReleases,
            'cached' => true,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Get similar books
     */
    public function similar(int $bookId): JsonResponse
    {
        $book = $this->bookRepository->findByIsbn($bookId);
        
        if (!$book) {
            return response()->json([
                'error' => 'Book not found',
                'message' => "No book found with ID: {$bookId}"
            ], 404);
        }
        
        $similarBooks = $this->bookRepository->getSimilarBooks($book);
        
        return response()->json([
            'data' => BookListResource::collection($similarBooks),
            'based_on' => new BookResource($book)
        ]);
    }

    /**
     * Get price statistics for category
     */
    public function priceStats(int $categoryId): JsonResponse
    {
        $stats = $this->bookRepository->getPriceStats($categoryId);
        
        return response()->json([
            'category_id' => $categoryId,
            'price_statistics' => $stats,
            'cached' => true
        ]);
    }

    /**
     * Get popular categories with book counts
     */
    public function popularCategories(): JsonResponse
    {
        $categories = $this->bookRepository->getPopularCategories();
        
        return response()->json([
            'data' => $categories,
            'cached' => true
        ]);
    }

    /**
     * Get cache health statistics
     */
    public function cacheStats(): JsonResponse
    {
        $stats = $this->cacheService->getCacheStats();
        $health = $this->cacheService->getCacheHealth();
        
        return response()->json([
            'statistics' => $stats,
            'health' => $health,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Warm up cache for performance
     */
    public function warmCache(): JsonResponse
    {
        $this->cacheService->preloadBookData();
        
        return response()->json([
            'message' => 'Cache warmed successfully',
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Clear book-related caches
     */
    public function clearCache(Request $request): JsonResponse
    {
        $categoryId = $request->get('category_id');
        $bookId = $request->get('book_id');
        
        $this->cacheService->invalidateBookCache($categoryId, $bookId);
        
        return response()->json([
            'message' => 'Cache cleared successfully',
            'category_id' => $categoryId,
            'book_id' => $bookId,
            'timestamp' => now()->toISOString()
        ]);
    }
}
