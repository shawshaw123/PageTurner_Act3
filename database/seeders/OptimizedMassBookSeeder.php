<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OptimizedMassBookSeeder extends Seeder
{
    /**
     * Smaller chunk size for memory efficiency
     */
    private const CHUNK_SIZE = 1000;
    
    /**
     * Total number of records to generate
     */
    private const TOTAL_RECORDS = 1000000;
    
    /**
     * Memory limit in MB for monitoring
     */
    private const MEMORY_LIMIT_MB = 512;

    public function run(): void
    {
        // Increase memory limit for this operation
        ini_set('memory_limit', '1024M');
        
        $this->command->info('🚀 Starting Optimized Mass Book Seeding...');
        $this->command->info("Target: " . number_format(self::TOTAL_RECORDS) . " records");
        $this->command->info("Chunk size: " . self::CHUNK_SIZE . " records per batch");
        $this->command->info("Memory limit: 1024M");
        
        $startTime = microtime(true);
        $inserted = 0;
        $batchCount = 0;
        
        // Clear existing books to start fresh
        $this->command->info('🗑️  Clearing existing books...');
        
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        try {
            DB::table('books')->truncate();
        } catch (\Exception $e) {
            // If truncate fails due to constraints, use delete instead
            $this->command->warn('Truncate failed, using delete instead...');
            DB::table('books')->delete();
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Disable query logging for performance
        DB::disableQueryLog();
        
        $this->command->info('📦 Generating books in optimized chunks...');
        
        // Pre-load data to avoid repeated queries
        $categoryIds = DB::table('categories')->pluck('id')->toArray();
        if (empty($categoryIds)) {
            $this->error('❌ No categories found. Please run CategorySeeder first.');
            return;
        }
        
        // Pre-generate data arrays for efficiency
        $publishers = $this->getPublishers();
        $formats = $this->getFormats();
        $languages = $this->getLanguages();
        $dimensions = $this->getDimensions();
        
        while ($inserted < self::TOTAL_RECORDS) {
            $batchStart = microtime(true);
            $batchSize = min(self::CHUNK_SIZE, self::TOTAL_RECORDS - $inserted);
            
            try {
                // Generate batch data directly with minimal memory usage
                $books = $this->generateOptimizedBatch($batchSize, $categoryIds, $publishers, $formats, $languages, $dimensions);
                
                // Raw batch insert for maximum throughput
                DB::table('books')->insert($books);
                
                // Clear memory immediately
                unset($books);
                
                $inserted += $batchSize;
                $batchCount++;
                
                // Calculate batch performance
                $batchTime = microtime(true) - $batchStart;
                $batchRate = $batchSize / $batchTime;
                $overallRate = $inserted / (microtime(true) - $startTime);
                $progress = ($inserted / self::TOTAL_RECORDS) * 100;
                
                // Memory monitoring
                $memoryUsage = memory_get_usage(true) / 1024 / 1024; // MB
                $memoryPercent = ($memoryUsage / self::MEMORY_LIMIT_MB) * 100;
                
                // Progress bar
                $this->command->line(
                    sprintf(
                        "[%s] Batch %d: %d records (%.1f%%) | %.0f rec/s | Memory: %.1f MB (%.0f%%)",
                        str_repeat('=', (int)($progress / 2)) . str_repeat(' ', 50 - (int)($progress / 2)),
                        $batchCount,
                        $inserted,
                        $progress,
                        $overallRate,
                        $memoryUsage,
                        $memoryPercent
                    )
                );
                
                // Force garbage collection more frequently
                if ($batchCount % 5 === 0) {
                    gc_collect_cycles();
                    
                    if ($memoryUsage > self::MEMORY_LIMIT_MB * 0.8) {
                        $this->command->warn("⚠️  Memory usage high: {$memoryUsage}MB - forcing GC");
                        // More aggressive cleanup
                        if (function_exists('gc_mem_caches')) {
                            gc_mem_caches();
                        }
                    }
                }
                
                // Brief pause every 50 batches to prevent database overload
                if ($batchCount % 50 === 0) {
                    usleep(50000); // 0.05 second pause
                    $this->command->info("⏸️  Brief pause for database recovery");
                }
                
            } catch (\Exception $e) {
                $this->command->error("❌ Batch {$batchCount} failed: " . $e->getMessage());
                
                // Log error but continue
                Log::error("MassBookSeeder batch failure", [
                    'batch' => $batchCount,
                    'inserted' => $inserted,
                    'error' => $e->getMessage(),
                    'memory' => memory_get_usage(true) / 1024 / 1024 . 'MB'
                ]);
                
                // Continue with next batch
                continue;
            }
        }
        
        $totalTime = microtime(true) - $startTime;
        $finalRate = self::TOTAL_RECORDS / $totalTime;
        $finalMemory = memory_get_peak_usage(true) / 1024 / 1024;
        
        // Final statistics
        $this->command->newLine();
        $this->command->info('🎉 Optimized Mass Book Seeding Complete!');
        $this->command->info('========================================');
        $this->command->info("📊 Total Records: " . number_format($inserted));
        $this->command->info("⏱️  Total Time: " . number_format($totalTime, 2) . " seconds");
        $this->command->info("🚀 Average Rate: " . number_format($finalRate, 0) . " records/second");
        $this->command->info("💾 Peak Memory: " . number_format($finalMemory, 2) . " MB");
        $this->command->info("📦 Total Batches: {$batchCount}");
        
        // Performance validation
        $this->validatePerformance($totalTime, $finalMemory);
        
        // Data integrity checks
        $this->validateDataIntegrity();
        
        // Re-enable query logging
        DB::enableQueryLog();
    }
    
    /**
     * Get publishers array
     */
    private function getPublishers(): array
    {
        return [
            'Penguin Random House', 'HarperCollins', 'Simon & Schuster', 'Hachette Book Group',
            'Macmillan Publishers', 'Scholastic Corporation', 'Pearson Education', 'Wiley',
            'Springer Nature', 'Oxford University Press', 'Cambridge University Press',
            'Harvard University Press', 'Yale University Press', 'Princeton University Press'
        ];
    }
    
    /**
     * Get formats array
     */
    private function getFormats(): array
    {
        return [
            'Hardcover' => [24.99, 49.99],
            'Paperback' => [9.99, 24.99],
            'Ebook' => [4.99, 19.99],
            'Audiobook' => [14.99, 34.99]
        ];
    }
    
    /**
     * Get languages array
     */
    private function getLanguages(): array
    {
        return ['English', 'Spanish', 'French', 'German', 'Italian'];
    }
    
    /**
     * Get dimensions array
     */
    private function getDimensions(): array
    {
        return ['6 x 9', '5.5 x 8.5', '7 x 10', '8.5 x 11'];
    }
    
    /**
     * Generate optimized batch with minimal memory usage
     */
    private function generateOptimizedBatch(int $batchSize, array $categoryIds, array $publishers, array $formats, array $languages, array $dimensions): array
    {
        $books = [];
        $categoryIdCount = count($categoryIds);
        $publisherCount = count($publishers);
        $languageCount = count($languages);
        $dimensionCount = count($dimensions);
        
        for ($i = 0; $i < $batchSize; $i++) {
            // Generate book type
            $rand = mt_rand(1, 100);
            
            if ($rand <= 10) {  // 10% bestsellers
                $stockQuantity = mt_rand(500, 1000);
                $isActive = 1;
                $priceRange = [19.99, 39.99];
            } elseif ($rand <= 15) {  // 5% rare books
                $stockQuantity = mt_rand(0, 10);
                $priceRange = [49.99, 199.99];
                $isActive = 1;
            } elseif ($rand <= 30) {  // 15% new releases
                $publishedAt = date('Y-m-d H:i:s', time() - mt_rand(0, 90 * 24 * 3600));
                $stockQuantity = mt_rand(100, 500);
                $isActive = 1;
                $priceRange = [14.99, 34.99];
            } elseif ($rand <= 38) {  // 8% academic books
                $stockQuantity = mt_rand(50, 300);
                $isActive = 1;
                $priceRange = [49.99, 149.99];
                $format = 'Hardcover';
                $pages = mt_rand(300, 1200);
            } else {  // 62% regular books
                $stockQuantity = mt_rand(0, 500);
                $isActive = mt_rand(1, 100) <= 85 ? 1 : 0;
                $publishedAt = date('Y-m-d H:i:s', time() - mt_rand(365 * 24 * 3600, 10 * 365 * 24 * 3600));
                $priceRange = [9.99, 49.99];
            }
            
            $format = $format ?? array_rand($formats);
            $actualPriceRange = $formats[$format] ?? $priceRange;
            $price = mt_rand($actualPriceRange[0] * 100, $actualPriceRange[1] * 100) / 100;
            
            $publishedAt = $publishedAt ?? date('Y-m-d H:i:s', time() - mt_rand(365 * 24 * 3600, 10 * 365 * 24 * 3600));
            $pages = $pages ?? mt_rand(100, 800);
            
            $books[] = [
                'isbn' => $this->generateValidIsbn13(),
                'title' => $this->generateSimpleTitle(),
                'author' => $this->generateSimpleAuthor(),
                'publisher' => $publishers[mt_rand(0, $publisherCount - 1)],
                'price' => $price,
                'stock_quantity' => $stockQuantity,
                'category_id' => $categoryIds[mt_rand(0, $categoryIdCount - 1)],
                'format' => $format,
                'description' => $this->generateSimpleDescription(),
                'cover_image' => null,
                'is_active' => $isActive,
                'published_at' => $publishedAt,
                'pages' => $pages,
                'language' => $languages[mt_rand(0, $languageCount - 1)],
                'dimensions' => $dimensions[mt_rand(0, $dimensionCount - 1)],
                'weight' => mt_rand(50, 200) / 10,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        
        return $books;
    }
    
    /**
     * Generate valid ISBN-13
     */
    private function generateValidIsbn13(): string
    {
        $isbn12 = '978';
        for ($i = 0; $i < 9; $i++) {
            $isbn12 .= mt_rand(0, 9);
        }
        
        $checksum = $this->calculateIsbn13Checksum($isbn12);
        return $isbn12 . $checksum;
    }
    
    /**
     * Calculate ISBN-13 checksum
     */
    private function calculateIsbn13Checksum(string $isbn12): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $isbn12[$i];
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }
        
        return (10 - ($sum % 10)) % 10;
    }
    
    /**
     * Generate simple title (less memory intensive)
     */
    private function generateSimpleTitle(): string
    {
        $prefixes = ['The', 'A', 'One', 'Secret', 'Lost', 'Found'];
        $mains = ['Journey', 'Story', 'Mystery', 'Quest', 'Path', 'Tale'];
        $suffixes = ['Time', 'Space', 'Hope', 'Dream', 'Love', 'Truth'];
        
        return $prefixes[mt_rand(0, 5)] . ' ' . $mains[mt_rand(0, 5)] . ' of ' . $suffixes[mt_rand(0, 5)];
    }
    
    /**
     * Generate simple author
     */
    private function generateSimpleAuthor(): string
    {
        $firstNames = ['John', 'Jane', 'Mike', 'Sarah', 'David', 'Lisa', 'Tom', 'Amy'];
        $lastNames = ['Smith', 'Johnson', 'Brown', 'Davis', 'Wilson', 'Moore', 'Taylor', 'Anderson'];
        
        return $firstNames[mt_rand(0, 7)] . ' ' . $lastNames[mt_rand(0, 7)];
    }
    
    /**
     * Generate simple description
     */
    private function generateSimpleDescription(): string
    {
        $descriptions = [
            'A compelling story that will keep you reading.',
            'An unforgettable journey through time and space.',
            'A masterpiece of modern literature.',
            'A thrilling adventure full of excitement.',
            'A heartwarming tale of love and hope.',
            'A thought-provoking exploration of life.',
            'A gripping mystery with unexpected twists.',
            'An inspiring story of courage and determination.',
            'A beautifully written narrative.',
            'A powerful story that will stay with you.'
        ];
        
        return $descriptions[mt_rand(0, 9)];
    }
    
    /**
     * Validate performance
     */
    private function validatePerformance(float $totalTime, float $peakMemory): void
    {
        $this->command->newLine();
        $this->command->info('🔍 Performance Validation:');
        $this->command->info('------------------------');
        
        $timeRequirement = 600; // seconds
        if ($totalTime <= $timeRequirement) {
            $this->command->info("✅ Time requirement met: " . number_format($totalTime, 2) . "s < {$timeRequirement}s");
        } else {
            $this->command->error("❌ Time requirement exceeded: " . number_format($totalTime, 2) . "s > {$timeRequirement}s");
        }
        
        $memoryRequirement = self::MEMORY_LIMIT_MB;
        if ($peakMemory <= $memoryRequirement) {
            $this->command->info("✅ Memory requirement met: " . number_format($peakMemory, 2) . "MB < {$memoryRequirement}MB");
        } else {
            $this->command->error("❌ Memory requirement exceeded: " . number_format($peakMemory, 2) . "MB > {$memoryRequirement}MB");
        }
    }
    
    /**
     * Validate data integrity
     */
    private function validateDataIntegrity(): void
    {
        $this->command->newLine();
        $this->command->info('🔍 Data Integrity Validation:');
        $this->command->info('---------------------------');
        
        $actualCount = DB::table('books')->count();
        $this->command->info("📊 Total books: " . number_format($actualCount));
        
        if ($actualCount === self::TOTAL_RECORDS) {
            $this->command->info("✅ Record count matches target");
        } else {
            $this->command->error("❌ Record count mismatch: expected " . self::TOTAL_RECORDS . ", got {$actualCount}");
        }
        
        $invalidIsbns = DB::table('books')
            ->whereRaw('LENGTH(isbn) != 13')
            ->orWhereRaw('isbn NOT REGEXP "^[0-9]{13}$"')
            ->count();
            
        if ($invalidIsbns === 0) {
            $this->command->info("✅ All ISBNs are valid");
        } else {
            $this->command->error("❌ Found {$invalidIsbns} invalid ISBNs");
        }
        
        $nullCategories = DB::table('books')->whereNull('category_id')->count();
        if ($nullCategories === 0) {
            $this->command->info("✅ All books have valid categories");
        } else {
            $this->command->error("❌ Found {$nullCategories} books with null category_id");
        }
        
        $activeBooks = DB::table('books')->where('is_active', 1)->count();
        $activePercent = ($activeBooks / $actualCount) * 100;
        $this->command->info("📈 Active books: " . number_format($activeBooks) . " (" . number_format($activePercent, 1) . "%)");
    }
}
