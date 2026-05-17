<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RealisticMillionBookSeeder extends Seeder
{
    private const TOTAL_RECORDS = 1000000;
    private const CHUNK_SIZE = 1000;

    public function run(): void
    {
        ini_set('memory_limit', '1024M');
        
        $this->command->info('🚀 Lab 7 - Realistic 1 Million Book Seeding');
        $this->command->info('========================================');
        
        $startTime = microtime(true);
        $inserted = 0;
        $batchCount = 0;
        
        // Clear existing books
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            DB::table('books')->truncate();
        } catch (\Exception $e) {
            DB::table('books')->delete();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        DB::disableQueryLog();
        
        $categoryIds = DB::table('categories')->pluck('id')->toArray();
        if (empty($categoryIds)) {
            $this->command->error('❌ No categories found.');
            return;
        }
        
        // Generate realistic data pools
        $this->command->info('📚 Preparing realistic data pools...');
        $authors = $this->generateRealisticAuthors(1000); // 1000 unique authors
        $publishers = $this->getPublishers();
        $titleParts = $this->getTitleParts();
        
        while ($inserted < self::TOTAL_RECORDS) {
            $batchStart = microtime(true);
            $batchSize = min(self::CHUNK_SIZE, self::TOTAL_RECORDS - $inserted);
            
            $books = $this->generateRealisticBatch($batchSize, $categoryIds, $authors, $publishers, $titleParts, $inserted);
            
            DB::table('books')->insert($books);
            unset($books);
            
            $inserted += $batchSize;
            $batchCount++;
            
            $progress = ($inserted / self::TOTAL_RECORDS) * 100;
            $elapsed = microtime(true) - $startTime;
            $rate = $inserted / $elapsed;
            $memory = memory_get_usage(true) / 1024 / 1024;
            
            $this->command->line(
                sprintf(
                    "[%s] Batch %d: %d records (%.1f%%) | %.0f rec/s | Memory: %.1f MB",
                    str_repeat('=', (int)($progress / 2)) . str_repeat(' ', 50 - (int)($progress / 2)),
                    $batchCount,
                    $inserted,
                    $progress,
                    $rate,
                    $memory
                )
            );
            
            if ($batchCount % 10 === 0) {
                gc_collect_cycles();
            }
        }
        
        $totalTime = microtime(true) - $startTime;
        $peakMemory = memory_get_peak_usage(true) / 1024 / 1024;
        
        $this->command->newLine();
        $this->command->info('🎉 Realistic 1 Million Books Seeded!');
        $this->command->info('===================================');
        $this->command->info("📊 Total Records: " . number_format($inserted));
        $this->command->info("⏱️  Total Time: " . number_format($totalTime, 2) . " seconds");
        $this->command->info("💾 Peak Memory: " . number_format($peakMemory, 2) . " MB");
        
        $this->validateRealisticData();
    }
    
    private function generateRealisticAuthors(int $count): array
    {
        $firstNames = ['John', 'Jane', 'Michael', 'Sarah', 'Robert', 'Emily', 'David', 'Jessica', 'James', 'Jennifer', 'William', 'Lisa', 'Richard', 'Mary', 'Thomas', 'Patricia', 'Charles', 'Linda', 'Christopher', 'Barbara'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Wilson', 'Anderson', 'Taylor', 'Thomas', 'Moore', 'Jackson', 'Martin', 'Lee', 'White', 'Harris'];
        
        $authors = [];
        for ($i = 0; $i < $count; $i++) {
            $authors[] = $firstNames[mt_rand(0, count($firstNames) - 1)] . ' ' . $lastNames[mt_rand(0, count($lastNames) - 1)];
        }
        
        return array_unique($authors);
    }
    
    private function getPublishers(): array
    {
        return [
            'Penguin Random House', 'HarperCollins', 'Simon & Schuster', 'Hachette Book Group',
            'Macmillan Publishers', 'Scholastic Corporation', 'Pearson Education', 'Wiley',
            'Springer Nature', 'Oxford University Press', 'Cambridge University Press',
            'Harvard University Press', 'Yale University Press', 'Princeton University Press',
            'McGraw-Hill Education', 'Elsevier', 'Taylor & Francis', 'Wiley-Blackwell',
            'Routledge', 'Palgrave Macmillan', 'Bloomsbury Publishing'
        ];
    }
    
    private function getTitleParts(): array
    {
        return [
            'prefixes' => ['The', 'A', 'One', 'Two', 'Three', 'Secret', 'Hidden', 'Lost', 'Found', 'Forgotten', 'Ancient', 'Modern', 'Future', 'Past', 'Eternal', 'Infinite'],
            'mains' => ['Journey', 'Story', 'Quest', 'Adventure', 'Tale', 'Path', 'Way', 'Road', 'Book', 'Guide', 'Manual', 'Chronicle', 'History', 'Mystery', 'Secret', 'Discovery'],
            'suffixes' => ['Time', 'Space', 'Hope', 'Dream', 'Love', 'Truth', 'Light', 'Dark', 'Life', 'Death', 'War', 'Peace', 'Freedom', 'Justice', 'Power', 'Wisdom'],
            'connectors' => ['of', 'in', 'from', 'to', 'with', 'without', 'under', 'above', 'beyond', 'within', 'through', 'across', 'between', 'among']
        ];
    }
    
    private function generateRealisticBatch(int $batchSize, array $categoryIds, array $authors, array $publishers, array $titleParts, int $offset): array
    {
        $books = [];
        
        for ($i = 0; $i < $batchSize; $i++) {
            $bookIndex = $offset + $i + 1;
            
            // Generate valid ISBN-13
            $isbn = '978' . str_pad($bookIndex, 9, '0', STR_PAD_LEFT);
            $checksum = $this->calculateIsbnChecksum($isbn);
            $isbn .= $checksum;
            
            // Generate realistic title
            $title = $this->generateRealisticTitle($titleParts, $bookIndex);
            
            // Select realistic author
            $authorIndex = mt_rand(0, count($authors) - 1);
            $author = $authors[$authorIndex] ?? 'Unknown Author';
            
            // Realistic price based on publisher and book type
            $basePrice = mt_rand(999, 4999) / 100;
            
            // Realistic description
            $description = $this->generateRealisticDescription($title, $author);
            
            $books[] = [
                'isbn' => $isbn,
                'title' => $title,
                'author' => $author,
                'price' => $basePrice,
                'stock_quantity' => mt_rand(0, 1000),
                'category_id' => $categoryIds[mt_rand(0, count($categoryIds) - 1)],
                'description' => $description,
                'cover_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        return $books;
    }
    
    private function generateRealisticTitle(array $titleParts, int $seed): string
    {
        mt_srand($seed); // Use seed for variety
        
        $patterns = [
            '{prefix} {main} {connector} {suffix}',
            '{main} {connector} {suffix}',
            '{prefix} {main}',
            '{main} and {suffix}',
            'The {main} of {suffix}',
            '{prefix} {main}: {suffix}',
        ];
        
        $pattern = $patterns[mt_rand(0, count($patterns) - 1)];
        
        // Safe array access with fallbacks
        $prefixes = $titleParts['prefixes'] ?? ['The', 'A'];
        $mains = $titleParts['mains'] ?? ['Story', 'Journey'];
        $suffixes = $titleParts['suffixes'] ?? ['Time', 'Space'];
        $connectors = $titleParts['connectors'] ?? ['of', 'in'];
        
        return str_replace([
            '{prefix}', '{main}', '{suffix}', '{connector}'
        ], [
            $prefixes[mt_rand(0, count($prefixes) - 1)],
            $mains[mt_rand(0, count($mains) - 1)],
            $suffixes[mt_rand(0, count($suffixes) - 1)],
            $connectors[mt_rand(0, count($connectors) - 1)]
        ], $pattern);
    }
    
    private function generateRealisticDescription(string $title, string $author): string
    {
        $templates = [
            "In this compelling work by {$author}, readers will discover the fascinating world of {$title}. A masterpiece that challenges conventional thinking and offers new perspectives.",
            "{$author} presents an unforgettable journey through {$title}. This thought-provoking narrative will keep readers engaged from beginning to end.",
            "A groundbreaking exploration by {$author}, {$title} offers profound insights into the human condition and our place in the world.",
            "Experience the brilliance of {$author} in this extraordinary tale of {$title}. A story that will resonate with readers long after the final page.",
            "{$author} delivers a powerful narrative in {$title}. This beautifully written work combines literary excellence with deep emotional impact."
        ];
        
        return $templates[mt_rand(0, count($templates) - 1)];
    }
    
    private function calculateIsbnChecksum(string $isbn12): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $isbn12[$i];
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }
        return (10 - ($sum % 10)) % 10;
    }
    
    private function validateRealisticData(): void
    {
        $this->command->newLine();
        $this->command->info('🔍 Realistic Data Validation:');
        $this->command->info('============================');
        
        $uniqueTitles = DB::table('books')->distinct('title')->count('title');
        $uniqueAuthors = DB::table('books')->distinct('author')->count('author');
        $priceRange = DB::table('books')->selectRaw('MIN(price) as min, MAX(price) as max, AVG(price) as avg')->first();
        
        $this->command->info("📊 Total books: " . number_format(DB::table('books')->count()));
        $this->command->info("📚 Unique titles: " . number_format($uniqueTitles));
        $this->command->info("✍️  Unique authors: " . number_format($uniqueAuthors));
        $this->command->info("💰 Price range: $" . number_format($priceRange->min, 2) . " - $" . number_format($priceRange->max, 2));
        $this->command->info("💰 Average price: $" . number_format($priceRange->avg, 2));
        
        // Check data distribution quality
        $authorDistribution = DB::table('books')
            ->select('author', DB::raw('COUNT(*) as count'))
            ->groupBy('author')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
            
        $this->command->info("\n📈 Top 5 authors by book count:");
        foreach ($authorDistribution as $author) {
            $this->command->info("   {$author->author}: " . number_format($author->count) . " books");
        }
        
        // Validate realistic distribution (adjusted for available columns)
        $isRealistic = $uniqueTitles >= 500000 && $uniqueAuthors >= 500;
        
        if ($isRealistic) {
            $this->command->info("\n✅ Data distribution is realistic and varied!");
            $this->command->info("✅ Unique titles: " . number_format($uniqueTitles) . " (≥ 500,000)");
            $this->command->info("✅ Unique authors: " . number_format($uniqueAuthors) . " (≥ 500)");
            $this->command->info("🎊 Lab 7 Seeding Requirements: ✅ ALL COMPLETED!");
        } else {
            $this->command->error("\n❌ Data distribution needs improvement");
            $this->command->error("   Required: ≥ 500,000 unique titles, got: " . number_format($uniqueTitles));
            $this->command->error("   Required: ≥ 500 unique authors, got: " . number_format($uniqueAuthors));
        }
    }
}
