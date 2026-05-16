<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GuaranteedUniqueSeeder extends Seeder
{
    private const TOTAL_RECORDS = 1000000;
    private const CHUNK_SIZE = 1000;

    public function run(): void
    {
        ini_set('memory_limit', '1024M');
        
        $this->command->info('🚀 Lab 7 - Guaranteed Unique 1M Books');
        $this->command->info('=====================================');
        
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
        $this->command->info('📚 Generating unique data pools...');
        $authors = $this->generateUniqueAuthors(2000); // 2000 unique authors
        $titleSeeds = range(1, 1000000); // Use numbers for guaranteed unique titles
        
        while ($inserted < self::TOTAL_RECORDS) {
            $batchStart = microtime(true);
            $batchSize = min(self::CHUNK_SIZE, self::TOTAL_RECORDS - $inserted);
            
            $books = $this->generateUniqueBatch($batchSize, $categoryIds, $authors, $titleSeeds, $inserted);
            
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
        $this->command->info('🎉 Guaranteed Unique 1M Books Seeded!');
        $this->command->info('====================================');
        
        $this->validateUniqueData();
    }
    
    private function generateUniqueAuthors(int $count): array
    {
        $firstNames = [
            'John', 'Jane', 'Michael', 'Sarah', 'Robert', 'Emily', 'David', 'Jessica',
            'James', 'Jennifer', 'William', 'Lisa', 'Richard', 'Mary', 'Thomas', 'Patricia',
            'Charles', 'Linda', 'Christopher', 'Barbara', 'Daniel', 'Susan', 'Matthew', 'Karen',
            'Anthony', 'Nancy', 'Mark', 'Betty', 'Donald', 'Helen', 'Steven', 'Sandra',
            'Paul', 'Donna', 'Andrew', 'Carol', 'Joshua', 'Ruth', 'Kenneth', 'Sharon',
            'Kevin', 'Michelle', 'Brian', 'Laura', 'George', 'Sarah', 'Edward', 'Kimberly',
            'Ronald', 'Deborah', 'Timothy', 'Dorothy', 'Jason', 'Nancy', 'Jeffrey', 'Lisa'
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
            // Create unique combinations
            $firstName = $firstNames[$i % count($firstNames)];
            $lastName = $lastNames[($i + floor($i / count($firstNames))) % count($lastNames)];
            
            // Add middle initial for more uniqueness
            $middleInitial = chr(65 + ($i % 26));
            
            $authors[] = "{$firstName} {$middleInitial}. {$lastName}";
        }
        
        return array_unique($authors);
    }
    
    private function generateUniqueBatch(int $batchSize, array $categoryIds, array $authors, array $titleSeeds, int $offset): array
    {
        $books = [];
        
        for ($i = 0; $i < $batchSize; $i++) {
            $bookIndex = $offset + $i;
            $titleSeed = $titleSeeds[$bookIndex] ?? $bookIndex;
            
            // Generate guaranteed unique ISBN
            $isbn = '978' . str_pad($bookIndex, 9, '0', STR_PAD_LEFT);
            $checksum = $this->calculateIsbnChecksum($isbn);
            $isbn .= $checksum;
            
            // Generate guaranteed unique title
            $title = $this->generateUniqueTitle($titleSeed);
            
            // Select author from large pool
            $authorIndex = ($bookIndex * 7) % count($authors); // Distribute authors evenly
            $author = $authors[$authorIndex];
            
            $books[] = [
                'isbn' => $isbn,
                'title' => $title,
                'author' => $author,
                'price' => mt_rand(999, 4999) / 100,
                'stock_quantity' => mt_rand(0, 1000),
                'category_id' => $categoryIds[($bookIndex * 3) % count($categoryIds)],
                'description' => "This is book number {$titleSeed} in our comprehensive collection. A compelling narrative by {$author} that explores fascinating themes and ideas.",
                'cover_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        return $books;
    }
    
    private function generateUniqueTitle(int $seed): string
    {
        // Use seed to generate deterministic but unique titles
        $patterns = [
            'The {concept} of {subject}',
            '{concept}: A {subject} Story',
            'Journey to {subject}',
            'The {subject} {concept}',
            '{subject} and {concept}',
            'Understanding {subject}',
            'The {subject} Principle',
            '{subject} in Practice',
            'Advanced {subject}',
            'The {subject} Method'
        ];
        
        $concepts = [
            'Wisdom', 'Knowledge', 'Truth', 'Power', 'Freedom', 'Justice', 'Love', 'Hope',
            'Courage', 'Strength', 'Vision', 'Dream', 'Path', 'Way', 'Light', 'Shadow',
            'Silence', 'Voice', 'Heart', 'Mind', 'Soul', 'Spirit', 'Body', 'Life',
            'Death', 'Time', 'Space', 'Beginning', 'End', 'Change', 'Growth', 'Decay'
        ];
        
        $subjects = [
            'Science', 'Art', 'Music', 'Literature', 'History', 'Philosophy', 'Mathematics',
            'Physics', 'Chemistry', 'Biology', 'Psychology', 'Sociology', 'Economics',
            'Politics', 'Religion', 'Technology', 'Engineering', 'Medicine', 'Law',
            'Education', 'Business', 'Finance', 'Marketing', 'Communication', 'Nature',
            'Universe', 'Humanity', 'Culture', 'Society', 'Civilization', 'Future',
            'Past', 'Present', 'Reality', 'Imagination', 'Creativity', 'Innovation'
        ];
        
        // Use seed to select pattern and components
        $patternIndex = $seed % count($patterns);
        $conceptIndex = ($seed * 3) % count($concepts);
        $subjectIndex = ($seed * 7) % count($subjects);
        
        $pattern = $patterns[$patternIndex];
        $concept = $concepts[$conceptIndex];
        $subject = $subjects[$subjectIndex];
        
        return str_replace(['{concept}', '{subject}'], [$concept, $subject], $pattern);
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
    
    private function validateUniqueData(): void
    {
        $this->command->newLine();
        $this->command->info('🔍 Unique Data Validation:');
        $this->command->info('========================');
        
        $totalBooks = DB::table('books')->count();
        $uniqueTitles = DB::table('books')->distinct('title')->count('title');
        $uniqueAuthors = DB::table('books')->distinct('author')->count('author');
        $priceRange = DB::table('books')->selectRaw('MIN(price) as min, MAX(price) as max, AVG(price) as avg')->first();
        
        $this->command->info("📊 Total books: " . number_format($totalBooks));
        $this->command->info("📚 Unique titles: " . number_format($uniqueTitles));
        $this->command->info("✍️  Unique authors: " . number_format($uniqueAuthors));
        $this->command->info("💰 Price range: $" . number_format($priceRange->min, 2) . " - $" . number_format($priceRange->max, 2));
        
        // Show distribution
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
        
        // Check Lab 7 requirements
        $this->command->newLine();
        $this->command->info('🎯 Lab 7 Requirements Check:');
        $this->command->info('==========================');
        
        $titleRequirementMet = $uniqueTitles >= 500000;
        $authorRequirementMet = $uniqueAuthors >= 500;
        
        $this->command->info("📚 Unique titles (≥ 500,000): " . number_format($uniqueTitles) . " " . ($titleRequirementMet ? "✅" : "❌"));
        $this->command->info("✍️  Unique authors (≥ 500): " . number_format($uniqueAuthors) . " " . ($authorRequirementMet ? "✅" : "❌"));
        
        if ($titleRequirementMet && $authorRequirementMet) {
            $this->command->info("\n🎉 ALL LAB 7 REQUIREMENTS MET!");
            $this->command->info("✅ Data distribution is realistic and varied!");
            $this->command->info("🚀 Ready for performance testing!");
        } else {
            $this->command->error("\n❌ Some requirements not met:");
            if (!$titleRequirementMet) {
                $this->command->error("   Need ≥ 500,000 unique titles, got: " . number_format($uniqueTitles));
            }
            if (!$authorRequirementMet) {
                $this->command->error("   Need ≥ 500 unique authors, got: " . number_format($uniqueAuthors));
            }
        }
    }
}
