<?php

namespace App\Jobs;

use App\Services\BookCacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WarmCategoryCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 3;

    /**
     * The timeout for the job.
     */
    public int $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        private ?int $categoryId = null,
        private bool $force = false
    ) {
        // Set queue for background processing
        $this->onQueue('cache-warming');
    }

    /**
     * Execute the job.
     */
    public function handle(BookCacheService $cacheService): void
    {
        try {
            $startTime = microtime(true);
            
            if ($this->categoryId) {
                // Warm specific category cache
                $this->warmCategory($cacheService, $this->categoryId);
            } else {
                // Warm all popular categories
                $this->warmAllCategories($cacheService);
            }
            
            $duration = microtime(true) - $startTime;
            
            Log::info('Cache warming completed', [
                'category_id' => $this->categoryId,
                'duration' => round($duration, 2),
                'memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Cache warming failed', [
                'category_id' => $this->categoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Retry if this is not the last attempt
            if ($this->attempts() < $this->tries) {
                $this->release(30); // Release for 30 seconds
            }
            
            throw $e;
        }
    }

    /**
     * Warm cache for a specific category
     */
    private function warmCategory(BookCacheService $cacheService, int $categoryId): void
    {
        Log::info('Warming cache for category', ['category_id' => $categoryId]);
        
        $cacheService->warmCategoryCache($categoryId);
        
        Log::info('Category cache warmed', ['category_id' => $categoryId]);
    }

    /**
     * Warm cache for all popular categories
     */
    private function warmAllCategories(BookCacheService $cacheService): void
    {
        Log::info('Starting cache warming for all popular categories');
        
        $cacheService->warmPopularCategories();
        
        // Also warm bestsellers and new releases
        $cacheService->cacheBestsellers();
        
        Log::info('All category caches warmed successfully');
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['cache-warming', 'category:' . ($this->categoryId ?? 'all')];
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Cache warming job failed permanently', [
            'category_id' => $this->categoryId,
            'attempt' => $this->attempts(),
            'error' => $exception->getMessage()
        ]);
    }
}
