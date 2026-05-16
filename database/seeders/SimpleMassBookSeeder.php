<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SimpleMassBookSeeder extends Seeder
{
    /**
     * Optimal chunk size for MySQL/PostgreSQL bulk inserts
     */
    private const CHUNK_SIZE = 5000;
    
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
        $this->command->info('🚀 Starting Simple Mass Book Seeding...');
        $this->command->info("Target: " . number_format(self::TOTAL_RECORDS) . " records");
        $this->command->info("Chunk size: " . self::CHUNK_SIZE . " records per batch");
        
        $startTime = microtime(true);
        $inserted = 0;
        $batchCount = 0;
        
        // Clear existing books to start fresh (handle foreign key constraints)
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
        
        $this->command->info('📦 Generating books in chunks...');
        
        // Get category IDs once
        $categoryIds = DB::table('categories')->pluck('id')->toArray();
        if (empty($categoryIds)) {
            $this->error('❌ No categories found. Please run CategorySeeder first.');
            return;
        }
        
        while ($inserted < self::TOTAL_RECORDS) {
            $batchStart = microtime(true);
            $batchSize = min(self::CHUNK_SIZE, self::TOTAL_RECORDS - $inserted);
            
            try {
                // Generate batch data directly
                $books = $this->generateBookBatch($batchSize, $categoryIds);
                
                // Raw batch insert for maximum throughput
                DB::table('books')->insert($books);
                
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
                
                // Force garbage collection every 10 chunks to prevent memory buildup
                if ($batchCount % 10 === 0) {
                    unset($books);
                    gc_collect_cycles();
                    
                    $this->command->info("🧹 Garbage collection triggered");
                    
                    // Check memory usage
                    if ($memoryUsage > self::MEMORY_LIMIT_MB * 0.9) {
                        $this->command->warn("⚠️  Memory usage high: {$memoryUsage}MB");
                    }
                }
                
                // Brief pause every 20 batches to prevent database overload
                if ($batchCount % 20 === 0) {
                    usleep(100000); // 0.1 second pause
                    $this->command->info("⏸️  Brief pause for database recovery");
                }
                
            } catch (\Exception $e) {
                $this->command->error("❌ Batch {$batchCount} failed: " . $e->getMessage());
                Log::error("MassBookSeeder batch failure", [
                    'batch' => $batchCount,
                    'inserted' => $inserted,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Continue with next batch instead of stopping completely
                continue;
            }
        }
        
        $totalTime = microtime(true) - $startTime;
        $finalRate = self::TOTAL_RECORDS / $totalTime;
        $finalMemory = memory_get_peak_usage(true) / 1024 / 1024;
        
        // Final statistics
        $this->command->newLine();
        $this->command->info('🎉 Mass Book Seeding Complete!');
        $this->command->info('================================');
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
     * Generate a batch of book data
     */
    private function generateBookBatch(int $batchSize, array $categoryIds): array
    {
        $books = [];
        $publishers = [
            'Penguin Random House', 'HarperCollins', 'Simon & Schuster', 'Hachette Book Group',
            'Macmillan Publishers', 'Scholastic Corporation', 'Pearson Education', 'Wiley',
            'Springer Nature', 'Oxford University Press', 'Cambridge University Press',
            'Harvard University Press', 'Yale University Press', 'Princeton University Press'
        ];
        
        $formats = ['Hardcover', 'Paperback', 'Ebook', 'Audiobook'];
        $formatPrices = [
            'Hardcover' => [24.99, 49.99],
            'Paperback' => [9.99, 24.99],
            'Ebook' => [4.99, 19.99],
            'Audiobook' => [14.99, 34.99]
        ];
        
        $languages = ['English', 'Spanish', 'French', 'German', 'Italian'];
        $dimensions = ['6 x 9', '5.5 x 8.5', '7 x 10', '8.5 x 11'];
        
        for ($i = 0; $i < $batchSize; $i++) {
            // Determine book type (similar to states)
            $rand = mt_rand(1, 100);
            
            if ($rand <= 10) {  // 10% bestsellers
                $stockQuantity = mt_rand(500, 1000);
                $isActive = true;
                $priceRange = [19.99, 39.99];
            } elseif ($rand <= 15) {  // 5% rare books
                $stockQuantity = mt_rand(0, 10);
                $priceRange = [49.99, 199.99];
                $isActive = true;
            } elseif ($rand <= 30) {  // 15% new releases
                $publishedAt = now()->subDays(mt_rand(0, 90));
                $stockQuantity = mt_rand(100, 500);
                $isActive = true;
                $priceRange = [14.99, 34.99];
            } elseif ($rand <= 38) {  // 8% academic books
                $stockQuantity = mt_rand(50, 300);
                $isActive = true;
                $priceRange = [49.99, 149.99];
                $format = 'Hardcover';
                $pages = mt_rand(300, 1200);
            } else {  // 62% regular books
                $stockQuantity = mt_rand(0, 500);
                $isActive = mt_rand(1, 100) <= 85; // 85% active
                $publishedAt = now()->subYears(mt_rand(1, 10));
                $priceRange = [9.99, 49.99];
            }
            
            // Generate basic book data
            $format = $format ?? $formats[mt_rand(0, 3)];
            $priceRange = $priceRange ?? $formatPrices[$format];
            $price = mt_rand($priceRange[0] * 100, $priceRange[1] * 100) / 100;
            
            $publishedAt = $publishedAt ?? now()->subYears(mt_rand(1, 10));
            $pages = $pages ?? mt_rand(100, 800);
            
            // Generate valid ISBN-13
            $isbn = $this->generateValidIsbn13();
            
            // Generate realistic title
            $title = $this->generateRealisticTitle();
            
            $books[] = [
                'isbn' => $isbn,
                'title' => $title,
                'author' => $this->generateRealisticAuthor(),
                'publisher' => $publishers[mt_rand(0, count($publishers) - 1)],
                'price' => $price,
                'stock_quantity' => $stockQuantity,
                'category_id' => $categoryIds[mt_rand(0, count($categoryIds) - 1)],
                'format' => $format,
                'description' => $this->generateDescription(),
                'cover_image' => null,
                'is_active' => $isActive ?? true,
                'published_at' => $publishedAt,
                'pages' => $pages,
                'language' => $languages[mt_rand(0, count($languages) - 1)],
                'dimensions' => $dimensions[mt_rand(0, count($dimensions) - 1)],
                'weight' => mt_rand(50, 200) / 10, // in pounds
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        return $books;
    }
    
    /**
     * Generate a valid ISBN-13 with proper checksum
     */
    private function generateValidIsbn13(): string
    {
        // Generate 12 random digits
        $isbn12 = '978'; // ISBN-13 book prefix
        for ($i = 0; $i < 9; $i++) {
            $isbn12 .= mt_rand(0, 9);
        }
        
        // Calculate checksum
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
     * Generate realistic book title
     */
    private function generateRealisticTitle(): string
    {
        $prefixes = ['The', 'A', 'An', 'One', 'Two', 'Three', 'Secret', 'Hidden', 'Lost', 'Found'];
        $mains = ['Journey', 'Adventure', 'Mystery', 'Discovery', 'Quest', 'Path', 'Way', 'Road', 'Story', 'Tale'];
        $suffixes = ['of', 'in', 'from', 'to', 'with', 'without', 'under', 'above', 'beyond', 'within'];
        $nouns = ['Time', 'Space', 'Light', 'Dark', 'Hope', 'Dream', 'Love', 'War', 'Peace', 'Truth'];
        
        $patterns = [
            '{prefix} {main} {suffix} {noun}',
            '{main} {suffix} {noun}',
            '{prefix} {main}',
            '{main} and {noun}',
            'The {main} of {noun}',
        ];
        
        $pattern = $patterns[mt_rand(0, count($patterns) - 1)];
        
        return str_replace([
            '{prefix}', '{main}', '{suffix}', '{noun}'
        ], [
            $prefixes[mt_rand(0, count($prefixes) - 1)],
            $mains[mt_rand(0, count($mains) - 1)],
            $suffixes[mt_rand(0, count($suffixes) - 1)],
            $nouns[mt_rand(0, count($nouns) - 1)]
        ], $pattern);
    }
    
    /**
     * Generate realistic author name
     */
    private function generateRealisticAuthor(): string
    {
        $firstNames = ['John', 'Jane', 'Michael', 'Sarah', 'Robert', 'Emily', 'David', 'Jessica', 'James', 'Jennifer'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez'];
        
        return $firstNames[mt_rand(0, count($firstNames) - 1)] . ' ' . $lastNames[mt_rand(0, count($lastNames) - 1)];
    }
    
    /**
     * Generate realistic description
     */
    private function generateDescription(): string
    {
        $descriptions = [
            'A compelling story that will keep you turning pages late into the night.',
            'An unforgettable journey through time and space.',
            'A masterpiece of modern literature with profound insights.',
            'A thrilling adventure that challenges the boundaries of imagination.',
            'A heartwarming tale of love, loss, and redemption.',
            'A thought-provoking exploration of human nature and society.',
            'A gripping mystery that will keep you guessing until the very end.',
            'An inspiring story of courage and determination against all odds.',
            'A beautifully written narrative that captures the essence of life.',
            'A powerful story that will stay with you long after you finish reading.'
        ];
        
        return $descriptions[mt_rand(0, count($descriptions) - 1)];
    }
    
    /**
     * Validate performance against Lab 7 requirements
     */
    private function validatePerformance(float $totalTime, float $peakMemory): void
    {
        $this->command->newLine();
        $this->command->info('🔍 Performance Validation:');
        $this->command->info('------------------------');
        
        // Time requirement: less than 10 minutes (600 seconds)
        $timeRequirement = 600; // seconds
        if ($totalTime <= $timeRequirement) {
            $this->command->info("✅ Time requirement met: " . number_format($totalTime, 2) . "s < {$timeRequirement}s");
        } else {
            $this->command->error("❌ Time requirement exceeded: " . number_format($totalTime, 2) . "s > {$timeRequirement}s");
        }
        
        // Memory requirement: less than 512 MB
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
        
        // Check total count
        $actualCount = DB::table('books')->count();
        $this->command->info("📊 Total books in database: " . number_format($actualCount));
        
        if ($actualCount === self::TOTAL_RECORDS) {
            $this->command->info("✅ Record count matches target");
        } else {
            $this->command->error("❌ Record count mismatch: expected " . self::TOTAL_RECORDS . ", got {$actualCount}");
        }
        
        // Check ISBN format validity
        $invalidIsbns = DB::table('books')
            ->whereRaw('LENGTH(isbn) != 13')
            ->orWhereRaw('isbn NOT REGEXP "^[0-9]{13}$"')
            ->count();
            
        if ($invalidIsbns === 0) {
            $this->command->info("✅ All ISBNs are valid 13-digit numbers");
        } else {
            $this->command->error("❌ Found {$invalidIsbns} invalid ISBNs");
        }
        
        // Check foreign key relationships
        $nullCategories = DB::table('books')->whereNull('category_id')->count();
        if ($nullCategories === 0) {
            $this->command->info("✅ All books have valid category relationships");
        } else {
            $this->command->error("❌ Found {$nullCategories} books with null category_id");
        }
        
        // Check data distribution
        $activeBooks = DB::table('books')->where('is_active', true)->count();
        $activePercent = ($activeBooks / $actualCount) * 100;
        $this->command->info("📈 Active books: " . number_format($activeBooks) . " (" . number_format($activePercent, 1) . "%)");
        
        // Check price distribution
        $priceStats = DB::table('books')->selectRaw('
            COUNT(*) as total,
            AVG(price) as avg_price,
            MIN(price) as min_price,
            MAX(price) as max_price
        ')->first();
        
        $this->command->info("💰 Price range: $" . $priceStats->min_price . " - $" . $priceStats->max_price);
        $this->command->info("💰 Average price: $" . $priceStats->avg_price);
        
        // Check format distribution
        $formatStats = DB::table('books')
            ->select('format', DB::raw('COUNT(*) as count'))
            ->groupBy('format')
            ->orderBy('count', 'desc')
            ->get();
            
        $this->command->info("📚 Format distribution:");
        foreach ($formatStats as $stat) {
            $percent = ($stat->count / $actualCount) * 100;
            $this->command->info("   " . $stat->format . ": " . number_format($stat->count) . " (" . number_format($percent, 1) . "%)");
        }
    }
}
