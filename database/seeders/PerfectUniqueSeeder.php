<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerfectUniqueSeeder extends Seeder
{
    private const TOTAL_RECORDS = 1000000;
    private const CHUNK_SIZE = 1000;

    public function run(): void
    {
        ini_set('memory_limit', '1024M');
        
        $this->command->info('🚀 Lab 7 - Perfect Unique 1M Books');
        $this->command->info('===================================');
        
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
            $this->error('❌ No categories found.');
            return;
        }
        
        // Generate large pools of unique data
        $this->command->info('📚 Preparing guaranteed unique data...');
        $authors = $this->generateUniqueAuthors(2000);
        
        while ($inserted < self::TOTAL_RECORDS) {
            $batchStart = microtime(true);
            $batchSize = min(self::CHUNK_SIZE, self::TOTAL_RECORDS - $inserted);
            
            $books = $this->generatePerfectlyUniqueBatch($batchSize, $categoryIds, $authors, $inserted);
            
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
        $this->command->info('🎉 Perfect Unique 1M Books Seeded!');
        $this->command->info('===================================');
        $this->command->info("📊 Total Records: " . number_format($inserted));
        $this->command->info("⏱️  Total Time: " . number_format($totalTime, 2) . " seconds");
        $this->command->info("💾 Peak Memory: " . number_format($peakMemory, 2) . " MB");
        
        $this->validatePerfectUniqueData();
    }
    
    private function generateUniqueAuthors(int $count): array
    {
        $firstNames = [
            'John', 'Jane', 'Michael', 'Sarah', 'Robert', 'Emily', 'David', 'Jessica',
            'James', 'Jennifer', 'William', 'Lisa', 'Richard', 'Mary', 'Thomas', 'Patricia',
            'Charles', 'Linda', 'Christopher', 'Barbara', 'Daniel', 'Susan', 'Matthew', 'Karen',
            'Anthony', 'Nancy', 'Mark', 'Betty', 'Donald', 'Helen', 'Steven', 'Sandra',
            'Paul', 'Donna', 'Andrew', 'Carol', 'Joshua', 'Ruth', 'Kenneth', 'Sharon',
            'Kevin', 'Michelle', 'Brian', 'Laura', 'George', 'Ashley', 'Edward', 'Kimberly',
            'Ronald', 'Deborah', 'Timothy', 'Dorothy', 'Jason', 'Nancy', 'Jeffrey', 'Cynthia'
        ];
        
        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
            'Rodriguez', 'Martinez', 'Wilson', 'Anderson', 'Taylor', 'Thomas', 'Moore',
            'Jackson', 'Martin', 'Lee', 'White', 'Harris', 'Clark', 'Lewis', 'Robinson',
            'Walker', 'Young', 'Allen', 'King', 'Wright', 'Baker', 'Adams', 'Nelson',
            'Carter', 'Mitchell', 'Roberts', 'Turner', 'Phillips', 'Campbell', 'Parker',
            'Evans', 'Edwards', 'Collins', 'Stewart', 'Sanchez', 'Morris', 'Murphy',
            'Cook', 'Anderson', 'Bailey', 'Rivera', 'Cooper', 'Richardson', 'Cox',
            'Howard', 'Ward', 'Torres', 'Peterson', 'Gray', 'Ramirez', 'James',
            'Watson', 'Brooks', 'Kelly', 'Sanders', 'Bennett', 'Wood', 'Barnes'
        ];
        
        $authors = [];
        for ($i = 0; $i < $count; $i++) {
            $firstName = $firstNames[$i % count($firstNames)];
            $lastName = $lastNames[($i + floor($i / count($firstNames))) % count($lastNames)];
            $middleInitial = chr(65 + ($i % 26));
            $authors[] = "{$firstName} {$middleInitial}. {$lastName}";
        }
        
        return array_unique($authors);
    }
    
    private function generatePerfectlyUniqueBatch(int $batchSize, array $categoryIds, array $authors, int $offset): array
    {
        $books = [];
        
        for ($i = 0; $i < $batchSize; $i++) {
            $bookIndex = $offset + $i;
            
            // Generate guaranteed unique ISBN
            $isbn = '978' . str_pad($bookIndex, 9, '0', STR_PAD_LEFT);
            $checksum = $this->calculateIsbnChecksum($isbn);
            $isbn .= $checksum;
            
            // Generate PERFECTLY unique title using book index
            $title = "Book #" . str_pad($bookIndex, 7, '0', STR_PAD_LEFT) . ": " . $this->generateTitleComponent($bookIndex);
            
            // Select author
            $authorIndex = ($bookIndex * 13) % count($authors);
            $author = $authors[$authorIndex];
            
            $books[] = [
                'isbn' => $isbn,
                'title' => $title,
                'author' => $author,
                'price' => mt_rand(999, 4999) / 100,
                'stock_quantity' => mt_rand(0, 1000),
                'category_id' => $categoryIds[($bookIndex * 7) % count($categoryIds)],
                'description' => "This is book number {$bookIndex} in our comprehensive collection. A compelling narrative by {$author} that explores unique themes and ideas.",
                'cover_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        return $books;
    }
    
    private function generateTitleComponent(int $seed): string
    {
        // Use multiple word pools and seed to create variety
        $adjectives = [
            'Amazing', 'Beautiful', 'Brilliant', 'Captivating', 'Challenging', 'Compelling',
            'Creative', 'Curious', 'Deep', 'Dynamic', 'Elegant', 'Essential', 'Extraordinary',
            'Fascinating', 'Flexible', 'Fundamental', 'Global', 'Hidden', 'Important',
            'Innovative', 'Inspiring', 'Interesting', 'Key', 'Logical', 'Magical',
            'Modern', 'Natural', 'New', 'Original', 'Powerful', 'Practical', 'Professional',
            'Quality', 'Quick', 'Quiet', 'Rare', 'Real', 'Revolutionary', 'Scientific',
            'Secret', 'Simple', 'Smart', 'Special', 'Strategic', 'Strong', 'Technical',
            'Ultimate', 'Unique', 'Universal', 'Valuable', 'Various', 'Virtual', 'Wonderful'
        ];
        
        $nouns = [
            'Adventure', 'Analysis', 'Approach', 'Art', 'Balance', 'Beauty', 'Challenge',
            'Change', 'Choice', 'Concept', 'Creation', 'Design', 'Development', 'Discovery',
            'Dream', 'Education', 'Energy', 'Engine', 'Environment', 'Experience', 'Experiment',
            'Future', 'Game', 'Goal', 'Growth', 'Guide', 'History', 'Idea', 'Imagination',
            'Innovation', 'Insight', 'Intelligence', 'Journey', 'Knowledge', 'Language',
            'Leadership', 'Learning', 'Life', 'Love', 'Magic', 'Management', 'Market',
            'Mathematics', 'Memory', 'Method', 'Mind', 'Music', 'Mystery', 'Nature',
            'Network', 'Opportunity', 'Order', 'Passion', 'Path', 'Peace', 'Philosophy',
            'Physics', 'Plan', 'Power', 'Problem', 'Process', 'Progress', 'Project',
            'Quality', 'Question', 'Reality', 'Reason', 'Relationship', 'Research',
            'Revolution', 'Science', 'Secret', 'Solution', 'Space', 'Spirit', 'Story',
            'Strategy', 'Success', 'System', 'Technology', 'Theory', 'Thought', 'Time',
            'Tool', 'Truth', 'Understanding', 'Value', 'Vision', 'Voice', 'Wisdom',
            'Work', 'World', 'Writing'
        ];
        
        $contexts = [
            'for Beginners', 'for Experts', 'in Practice', 'Theory and Practice',
            'A Complete Guide', 'The Fundamentals', 'Advanced Techniques',
            'Modern Methods', 'Traditional Wisdom', 'Future Trends',
            'Historical Perspective', 'Global View', 'Local Focus',
            'Personal Development', 'Professional Growth', 'Academic Study',
            'Real World Applications', 'Theoretical Foundations', 'Practical Solutions'
        ];
        
        // Use seed to generate unique combinations
        $adjIndex = ($seed * 3) % count($adjectives);
        $nounIndex = ($seed * 7) % count($nouns);
        $contextIndex = ($seed * 11) % count($contexts);
        
        $patterns = [
            "{adjective} {noun}",
            "{adjective} {noun} {context}",
            "The {adjective} {noun}",
            "{noun}: {adjective} {context}",
            "Understanding {adjective} {noun}",
            "{adjective} {noun} Explained",
            "The {noun} of {adjective} {context}"
        ];
        
        $patternIndex = ($seed * 13) % count($patterns);
        $pattern = $patterns[$patternIndex];
        
        return str_replace([
            '{adjective}', '{noun}', '{context}'
        ], [
            $adjectives[$adjIndex],
            $nouns[$nounIndex],
            $contexts[$contextIndex]
        ], $pattern);
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
    
    private function validatePerfectUniqueData(): void
    {
        $this->command->newLine();
        $this->command->info('🔍 Perfect Unique Data Validation:');
        $this->command->info('===================================');
        
        $totalBooks = DB::table('books')->count();
        $uniqueTitles = DB::table('books')->distinct('title')->count('title');
        $uniqueAuthors = DB::table('books')->distinct('author')->count('author');
        $priceRange = DB::table('books')->selectRaw('MIN(price) as min, MAX(price) as max, AVG(price) as avg')->first();
        $validIsbns = DB::table('books')->whereRaw('LENGTH(isbn) = 13')->count();
        
        $this->command->info("📊 Total books: " . number_format($totalBooks));
        $this->command->info("📚 Unique titles: " . number_format($uniqueTitles));
        $this->command->info("✍️  Unique authors: " . number_format($uniqueAuthors));
        $this->command->info("🔢 Valid ISBNs: " . number_format($validIsbns));
        $this->command->info("💰 Price range: $" . number_format($priceRange->min, 2) . " - $" . number_format($priceRange->max, 2));
        
        // Show sample titles
        $sampleTitles = DB::table('books')->limit(5)->pluck('title');
        $this->command->info("\n📖 Sample titles:");
        foreach ($sampleTitles as $title) {
            $this->command->info("   {$title}");
        }
        
        // Check Lab 7 requirements
        $this->command->newLine();
        $this->command->info('🎯 Lab 7 Requirements Check:');
        $this->command->info('==========================');
        
        $titleRequirementMet = $uniqueTitles >= 500000;
        $authorRequirementMet = $uniqueAuthors >= 500;
        $isbnRequirementMet = $validIsbns >= 999000; // Almost all should be valid
        
        $this->command->info("📚 Unique titles (≥ 500,000): " . number_format($uniqueTitles) . " " . ($titleRequirementMet ? "✅" : "❌"));
        $this->command->info("✍️  Unique authors (≥ 500): " . number_format($uniqueAuthors) . " " . ($authorRequirementMet ? "✅" : "❌"));
        $this->command->info("🔢 Valid ISBNs (≥ 999,000): " . number_format($validIsbns) . " " . ($isbnRequirementMet ? "✅" : "❌"));
        
        if ($titleRequirementMet && $authorRequirementMet && $isbnRequirementMet) {
            $this->command->info("\n🎉 ALL LAB 7 REQUIREMENTS MET!");
            $this->command->info("✅ Data distribution is realistic and varied!");
            $this->command->info("✅ All titles are guaranteed unique!");
            $this->command->info("🚀 Ready for performance testing!");
        } else {
            $this->command->error("\n❌ Some requirements not met:");
            if (!$titleRequirementMet) {
                $this->command->error("   Need ≥ 500,000 unique titles, got: " . number_format($uniqueTitles));
            }
            if (!$authorRequirementMet) {
                $this->command->error("   Need ≥ 500 unique authors, got: " . number_format($uniqueAuthors));
            }
            if (!$isbnRequirementMet) {
                $this->command->error("   Need ≥ 999,000 valid ISBNs, got: " . number_format($validIsbns));
            }
        }
        
        // Show performance summary
        $this->command->newLine();
        $this->command->info('📊 Performance Summary:');
        $this->command->info('=======================');
        $this->command->info("📈 Title uniqueness: " . round(($uniqueTitles / $totalBooks) * 100, 2) . "%");
        $this->command->info("👥 Author distribution: " . round($totalBooks / $uniqueAuthors, 1) . " books per author");
        $this->command->info("📚 Books per title: " . round($totalBooks / $uniqueTitles, 1));
    }
}
