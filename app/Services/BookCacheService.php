<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class BookCacheService
{
    /**
     * Cache tags for different cache groups
     */
    private const TAGS = [
        'books' => 'books',
        'catalog' => 'catalog',
        'search' => 'search',
        'bestsellers' => 'bestsellers',
        'categories' => 'categories',
    ];

    /**
     * Cache durations in seconds
     */
    private const DURATIONS = [
        'short' => 300,      // 5 minutes
        'medium' => 1800,    // 30 minutes
        'long' => 3600,      // 1 hour
        'extended' => 7200,  // 2 hours
        'daily' => 86400,    // 24 hours
    ];

    /**
     * Warm up cache for popular categories
     */
    public function warmPopularCategories(): void
    {
        $popularCategories = Book::select(['category_id'])
            ->where('is_active', true)
            ->groupBy('category_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(20)
            ->pluck('category_id');

        foreach ($popularCategories as $categoryId) {
            $this->warmCategoryCache($categoryId);
        }
    }

    /**
     * Warm cache for specific category
     */
    public function warmCategoryCache(int $categoryId): void
    {
        $cacheKey = "category:{$categoryId}:popular";
        
        $books = Book::select(['id', 'title', 'author', 'price', 'stock_quantity', 'published_at'])
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('published_at', 'desc')
            ->limit(1000)
            ->get()
            ->toArray();

        if ($this->supportsTags()) {
            Cache::tags([self::TAGS['books'], "category:{$categoryId}"])
                ->put($cacheKey, $books, self::DURATIONS['extended']);
        } else {
            Cache::put($cacheKey, $books, self::DURATIONS['extended']);
        }
    }

    /**
     * Invalidate book-related caches with smart tagging
     */
    public function invalidateBookCache(?int $categoryId = null, ?int $bookId = null): void
    {
        if (!$this->supportsTags()) {
            // Standard cache clearing for file/database drivers
            Cache::forget("book:{$bookId}");
            Cache::forget("catalog");
            return;
        }

        if ($bookId) {
            // Invalidate specific book cache
            Cache::tags([self::TAGS['books'], "book:{$bookId}"])->flush();
        }

        if ($categoryId) {
            // Invalidate category-specific caches
            Cache::tags([self::TAGS['books'], "category:{$categoryId}"])->flush();
        }

        // Always invalidate general caches
        Cache::tags([self::TAGS['catalog'], self::TAGS['bestsellers']])->flush();
    }

    /**
     * Get cache statistics from Redis
     */
    public function getCacheStats(): array
    {
        $redis = Redis::connection('cache');
        
        return [
            'memory_usage' => $this->formatBytes($redis->info('memory')['used_memory']),
            'total_keys' => $redis->dbSize(),
            'hit_rate' => $this->calculateHitRate($redis),
            'connected_clients' => $redis->info('clients')['connected_clients'],
        ];
    }

    /**
     * Get popular search queries from cache
     */
    public function getPopularSearches(int $limit = 10): array
    {
        $cacheKey = 'popular_searches';
        
        if ($this->supportsTags()) {
            return Cache::tags([self::TAGS['search']])
                ->remember($cacheKey, self::DURATIONS['medium'], function () use ($limit) {
                    return [
                        'fiction', 'mystery', 'romance', 'thriller', 'science fiction',
                        'fantasy', 'biography', 'history', 'self-help', 'cooking'
                    ];
                });
        }

        return Cache::remember($cacheKey, self::DURATIONS['medium'], function () use ($limit) {
            return [
                'fiction', 'mystery', 'romance', 'thriller', 'science fiction',
                'fantasy', 'biography', 'history', 'self-help', 'cooking'
            ];
        });
    }

    /**
     * Cache search results with intelligent tagging
     */
    public function cacheSearchResults(string $query, array $results, int $duration = self::DURATIONS['short']): void
    {
        $cacheKey = 'search:' . md5($query);
        
        if ($this->supportsTags()) {
            Cache::tags([self::TAGS['search'], self::TAGS['books']])
                ->put($cacheKey, $results, $duration);
        } else {
            Cache::put($cacheKey, $results, $duration);
        }

        // Track popular searches
        $this->trackSearchQuery($query);
    }

    /**
     * Get cached search results
     */
    public function getCachedSearchResults(string $query): ?array
    {
        $cacheKey = 'search:' . md5($query);
        
        $results = Cache::tags([self::TAGS['search'], self::TAGS['books']])
            ->get($cacheKey);

        return $results ? json_decode($results, true) : null;
    }

    /**
     * Cache bestseller list with automatic refresh
     */
    public function cacheBestsellers(): void
    {
        $cacheKey = 'bestsellers:active';
        
        $bestsellers = Book::select([
                'books.id',
                'books.title',
                'books.author',
                'books.price',
                'books.stock_quantity',
                'books.published_at',
                'books.category_id'
            ])
            ->with(['category:id,name'])
            ->where('is_active', true)
            ->where('stock_quantity', '>', 100)
            ->where('published_at', '>=', now()->subYear())
            ->orderBy('stock_quantity', 'desc')
            ->orderBy('published_at', 'desc')
            ->limit(50)
            ->get()
            ->toArray();

        Cache::tags([self::TAGS['bestsellers'], self::TAGS['books']])
            ->put($cacheKey, $bestsellers, self::DURATIONS['medium']);
    }

    /**
     * Get cached bestsellers
     */
    public function getCachedBestsellers(): ?array
    {
        $cacheKey = 'bestsellers:active';
        
        return Cache::tags([self::TAGS['bestsellers'], self::TAGS['books']])
            ->get($cacheKey);
    }

    /**
     * Preload book data for upcoming features
     */
    public function preloadBookData(): void
    {
        // Preload new releases
        $this->cacheNewReleases();
        
        // Preload bestsellers
        $this->cacheBestsellers();
        
        // Preload popular categories
        $this->warmPopularCategories();
    }

    /**
     * Cache new releases
     */
    private function cacheNewReleases(): void
    {
        $cacheKey = 'new_releases:active';
        
        $newReleases = Book::select([
                'books.id',
                'books.title',
                'books.author',
                'books.price',
                'books.published_at',
                'books.format',
                'books.category_id'
            ])
            ->with(['category:id,name'])
            ->where('is_active', true)
            ->where('published_at', '>=', now()->subMonths(3))
            ->orderBy('published_at', 'desc')
            ->limit(50)
            ->get()
            ->toArray();

        Cache::tags([self::TAGS['books'], 'new_releases'])
            ->put($cacheKey, $newReleases, self::DURATIONS['short']);
    }

    /**
     * Track search query for analytics
     */
    private function trackSearchQuery(string $query): void
    {
        $key = 'search_analytics:' . date('Y-m-d');
        
        // Increment search count in Redis
        Redis::connection('cache')->zincrby($key, 1, strtolower($query));
        
        // Set expiration to 30 days
        Redis::connection('cache')->expire($key, 30 * 24 * 3600);
    }

    /**
     * Calculate cache hit rate
     */
    private function calculateHitRate($redis): float
    {
        $stats = $redis->info('stats');
        
        if (!isset($stats['keyspace_hits']) || !isset($stats['keyspace_misses'])) {
            return 0.0;
        }
        
        $hits = (int) $stats['keyspace_hits'];
        $misses = (int) $stats['keyspace_misses'];
        $total = $hits + $misses;
        
        return $total > 0 ? ($hits / $total) * 100 : 0.0;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Clear all book-related caches
     */
    public function clearAllBookCaches(): void
    {
        Cache::tags([
            self::TAGS['books'],
            self::TAGS['catalog'],
            self::TAGS['search'],
            self::TAGS['bestsellers'],
            self::TAGS['categories']
        ])->flush();
    }

    /**
     * Get cache health metrics
     */
    public function getCacheHealth(): array
    {
        $redis = Redis::connection('cache');
        $info = $redis->info();
        
        return [
            'redis_connected' => $redis->ping() === '+PONG',
            'memory_usage_percent' => $this->getMemoryUsagePercent($info),
            'connected_clients' => $info['clients']['connected_clients'],
            'total_commands_processed' => $info['stats']['total_commands_processed'],
            'keyspace_hits' => $info['stats']['keyspace_hits'] ?? 0,
            'keyspace_misses' => $info['stats']['keyspace_misses'] ?? 0,
            'expired_keys' => $info['stats']['expired_keys'] ?? 0,
            'evicted_keys' => $info['stats']['evicted_keys'] ?? 0,
        ];
    }

    /**
     * Get memory usage percentage
     */
    private function getMemoryUsagePercent(array $info): float
    {
        if (!isset($info['memory']['maxmemory']) || $info['memory']['maxmemory'] == 0) {
            return 0.0;
        }
        
        $used = $info['memory']['used_memory'];
        $max = $info['memory']['maxmemory'];
        
        return ($used / $max) * 100;
    }

    /**
     * Check if current cache store supports tagging
     */
    private function supportsTags(): bool
    {
        try {
            return Cache::getStore() instanceof \Illuminate\Cache\TaggableStore;
        } catch (\Exception $e) {
            return false;
        }
    }
}
