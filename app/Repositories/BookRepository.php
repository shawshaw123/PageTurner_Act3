<?php

namespace App\Repositories;

use App\Models\Book;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BookRepository
{
    /**
     * Cache tags for book-related caching
     */
    private const CACHE_TAGS = ['books', 'catalog'];

    /**
     * Cache duration in seconds
     */
    private const CACHE_DURATION = 3600; // 1 hour

    /**
     * Get active books with cursor pagination for optimal performance
     * Target: < 100ms for catalog listing
     */
    public function getActiveCatalog(Request $request, int $perPage = 100): CursorPaginator
    {
        $cacheKey = $this->generateCacheKey('catalog', $request->all(), $perPage);

        return Cache::tags(self::CACHE_TAGS)
            ->remember($cacheKey, self::CACHE_DURATION, function () use ($request, $perPage) {
                // Column selection for covering index optimization
                $allowedFields = [
                    'books.id',
                    'books.isbn',
                    'books.title',
                    'books.author',
                    'books.publisher',
                    'books.price',
                    'books.stock_quantity',
                    'books.published_at',
                    'books.format',
                    'books.category_id',
                    'books.is_active'
                ];

                $query = Book::select($allowedFields)
                    ->with(['category:id,name']) // Only load necessary category fields
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->where('is_active', true);

                // Apply filters using indexed columns
                $this->applyFilters($query, $request);

                // Order by indexed columns for optimal performance
                $query->orderBy('published_at', 'desc')
                      ->orderBy('id', 'desc'); // Secondary sort for stable pagination

                return $query->cursorPaginate($perPage);
            });
    }

    /**
     * Find book by ISBN with caching - Target: < 50ms
     */
    public function findByIsbn(string $isbn): ?Book
    {
        $cacheKey = "book:isbn:{$isbn}";

        return Cache::tags(self::CACHE_TAGS)
            ->remember($cacheKey, self::CACHE_DURATION, function () use ($isbn) {
                return Book::with(['category:id,name,slug'])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->where('isbn', $isbn)
                    ->where('is_active', true)
                    ->first();
            });
    }

    /**
     * Get books by category with caching - Target: < 150ms
     */
    public function getByCategory(int $categoryId, Request $request, int $perPage = 100): CursorPaginator
    {
        $cacheKey = $this->generateCacheKey("category:{$categoryId}", $request->all(), $perPage);

        return Cache::tags(['books', "category:{$categoryId}"])
            ->remember($cacheKey, self::CACHE_DURATION, function () use ($categoryId, $request, $perPage) {
                $allowedFields = [
                    'books.id',
                    'books.isbn',
                    'books.title',
                    'books.author',
                    'books.publisher',
                    'books.price',
                    'books.stock_quantity',
                    'books.published_at',
                    'books.format',
                    'books.category_id'
                ];

                return Book::select($allowedFields)
                    ->with(['category:id,name'])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->where('category_id', $categoryId)
                    ->where('is_active', true)
                    ->applyFilters($request)
                    ->orderBy('published_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->cursorPaginate($perPage);
            });
    }

    /**
     * Search books using full-text search - Target: < 300ms
     */
    public function search(string $query, Request $request, int $perPage = 100): CursorPaginator
    {
        $cacheKey = $this->generateCacheKey('search', array_merge($request->all(), ['q' => $query]), $perPage);

        return Cache::tags(['books', 'search'])
            ->remember($cacheKey, self::CACHE_DURATION / 2, function () use ($query, $request, $perPage) {
                $allowedFields = [
                    'books.id',
                    'books.isbn',
                    'books.title',
                    'books.author',
                    'books.publisher',
                    'books.price',
                    'books.stock_quantity',
                    'books.published_at',
                    'books.format',
                    'books.category_id'
                ];

                // Use full-text search index
                return Book::select($allowedFields)
                    ->with(['category:id,name'])
                    ->where('is_active', true)
                    ->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$query])
                    ->applyFilters($request)
                    ->orderBy('published_at', 'desc')
                    ->cursorPaginate($perPage);
            });
    }

    /**
     * Get bestsellers (high stock, recent publications)
     */
    public function getBestsellers(int $limit = 50): Collection
    {
        $cacheKey = "books:bestsellers:{$limit}";

        return Cache::tags(['books', 'bestsellers'])
            ->remember($cacheKey, self::CACHE_DURATION, function () use ($limit) {
                return Book::select([
                        'books.id',
                        'books.title',
                        'books.author',
                        'books.price',
                        'books.stock_quantity',
                        'books.published_at',
                        'books.category_id'
                    ])
                    ->with(['category:id,name'])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->where('is_active', true)
                    ->where('stock_quantity', '>', 100)
                    ->where('published_at', '>=', now()->subYear())
                    ->orderBy('stock_quantity', 'desc')
                    ->orderBy('published_at', 'desc')
                    ->limit($limit)
                    ->get();
            });
    }

    /**
     * Get new releases
     */
    public function getNewReleases(int $limit = 50): Collection
    {
        $cacheKey = "books:new-releases:{$limit}";

        return Cache::tags(['books', 'new-releases'])
            ->remember($cacheKey, self::CACHE_DURATION / 4, function () use ($limit) {
                return Book::select([
                        'books.id',
                        'books.title',
                        'books.author',
                        'books.price',
                        'books.published_at',
                        'books.format',
                        'books.category_id'
                    ])
                    ->with(['category:id,name'])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->where('is_active', true)
                    ->where('published_at', '>=', now()->subMonths(3))
                    ->orderBy('published_at', 'desc')
                    ->limit($limit)
                    ->get();
            });
    }

    /**
     * Get similar books based on category and price range
     */
    public function getSimilarBooks(Book $book, int $limit = 10): Collection
    {
        $cacheKey = "books:similar:{$book->id}:{$limit}";

        return Cache::tags(['books', "book:{$book->id}"])
            ->remember($cacheKey, self::CACHE_DURATION, function () use ($book, $limit) {
                $priceRange = $book->price * 0.3; // 30% price variance

                return Book::select([
                        'books.id',
                        'books.title',
                        'books.author',
                        'books.price',
                        'books.cover_image',
                        'books.category_id'
                    ])
                    ->where('id', '!=', $book->id)
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->where('category_id', $book->category_id)
                    ->where('is_active', true)
                    ->whereBetween('price', [
                        $book->price - $priceRange,
                        $book->price + $priceRange
                    ])
                    ->inRandomOrder()
                    ->limit($limit)
                    ->get();
            });
    }

    /**
     * Get price range statistics for category
     */
    public function getPriceStats(int $categoryId): array
    {
        $cacheKey = "books:price-stats:{$categoryId}";

        return Cache::tags(['books', "category:{$categoryId}", 'stats'])
            ->remember($cacheKey, self::CACHE_DURATION * 2, function () use ($categoryId) {
                $stats = DB::table('books')
                    ->selectRaw('
                        COUNT(*) as total_books,
                        AVG(price) as avg_price,
                        MIN(price) as min_price,
                        MAX(price) as max_price,
                        PERCENTILE_CONT(0.5) WITHIN GROUP (ORDER BY price) as median_price
                    ')
                    ->where('category_id', $categoryId)
                    ->where('is_active', true)
                    ->first();

                return [
                    'total_books' => (int) $stats->total_books,
                    'avg_price' => (float) $stats->avg_price,
                    'min_price' => (float) $stats->min_price,
                    'max_price' => (float) $stats->max_price,
                    'median_price' => (float) $stats->median_price,
                ];
            });
    }

    /**
     * Apply filters to query using indexed columns
     */
    private function applyFilters(Builder $query, Request $request): void
    {
        // Category filter (uses composite index)
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Price range filter (uses covering index)
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Stock filter
        if ($request->filled('in_stock')) {
            $query->where('stock_quantity', '>', 0);
        }

        // Format filter
        if ($request->filled('format')) {
            $query->where('format', $request->input('format'));
        }

        // Author filter (uses author index)
        if ($request->filled('author')) {
            $query->where('author', 'like', '%' . $request->input('author') . '%');
        }

        // Publisher filter (uses publisher index)
        if ($request->filled('publisher')) {
            $query->where('publisher', $request->input('publisher'));
        }

        // Publication date range
        if ($request->filled('published_after')) {
            $query->where('published_at', '>=', $request->input('published_after'));
        }
        if ($request->filled('published_before')) {
            $query->where('published_at', '<=', $request->input('published_before'));
        }
    }

    /**
     * Generate cache key based on method and parameters
     */
    private function generateCacheKey(string $method, array $params, int $perPage): string
    {
        ksort($params); // Ensure consistent ordering
        $paramString = md5(json_encode($params));
        return "books:{$method}:{$perPage}:{$paramString}";
    }

    /**
     * Invalidate book-related caches
     */
    public function invalidateBookCache(?int $categoryId = null): void
    {
        if ($categoryId) {
            Cache::tags(['books', "category:{$categoryId}"])->flush();
        } else {
            Cache::tags(self::CACHE_TAGS)->flush();
        }
    }

    /**
     * Get popular categories with book counts
     */
    public function getPopularCategories(int $limit = 20): Collection
    {
        $cacheKey = "books:popular-categories:{$limit}";

        return Cache::tags(['books', 'categories', 'stats'])
            ->remember($cacheKey, self::CACHE_DURATION * 6, function () use ($limit) {
                return DB::table('books')
                    ->select([
                        'categories.id',
                        'categories.name',
                        DB::raw('COUNT(books.id) as book_count'),
                        DB::raw('AVG(books.price) as avg_price')
                    ])
                    ->join('categories', 'books.category_id', '=', 'categories.id')
                    ->where('books.is_active', true)
                    ->groupBy('categories.id', 'categories.name')
                    ->orderBy('book_count', 'desc')
                    ->limit($limit)
                    ->get();
            });
    }
}
