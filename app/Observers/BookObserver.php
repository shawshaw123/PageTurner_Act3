<?php

namespace App\Observers;

use App\Models\Book;
use App\Services\BookCacheService;

class BookObserver
{
    protected BookCacheService $cacheService;

    public function __construct(BookCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the Book "created" event.
     */
    public function created(Book $book): void
    {
        $this->invalidateCache($book);
        
        // Trigger Scout indexing if Scout is configured
        if (class_exists('\Laravel\Scout\Scout')) {
            $book->searchable();
        }
    }

    /**
     * Handle the Book "updated" event.
     */
    public function updated(Book $book): void
    {
        $this->invalidateCache($book);
        
        // Update Scout index
        if (class_exists('\Laravel\Scout\Scout')) {
            $book->searchable();
        }
    }

    /**
     * Handle the Book "deleted" event.
     */
    public function deleted(Book $book): void
    {
        $this->invalidateCache($book);
        
        // Remove from Scout index
        if (class_exists('\Laravel\Scout\Scout')) {
            $book->unsearchable();
        }
    }

    /**
     * Handle the Book "restored" event.
     */
    public function restored(Book $book): void
    {
        $this->invalidateCache($book);
        $book->searchable();
    }

    /**
     * Handle the Book "force deleted" event.
     */
    public function forceDeleted(Book $book): void
    {
        $this->invalidateCache($book);
        $book->unsearchable();
    }

    /**
     * Invalidate relevant cache entries
     */
    private function invalidateCache(Book $book): void
    {
        // Invalidate book-specific cache
        $this->cacheService->invalidateBookCache(
            categoryId: $book->category_id,
            bookId: $book->id
        );

        // Invalidate broader caches that might contain this book
        $this->cacheService->invalidateBookCache();
    }
}
