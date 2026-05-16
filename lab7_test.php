<?php

/**
 * Laboratory Activity 7 - Quick Testing Script
 * 
 * This script provides quick verification of Lab 7 implementation.
 * Run: php lab7_test.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Laboratory Activity 7 - Implementation Test\n";
echo "==========================================\n\n";

// Helper function
function checkComponent($name, $check, $expected = true) {
    $result = $check();
    $status = $result === $expected ? "✅ PASS" : "❌ FAIL";
    echo sprintf("%-50s %s\n", $name, $status);
    if ($result !== $expected) {
        echo "    Expected: " . var_export($expected, true) . ", Got: " . var_export($result, true) . "\n";
    }
    return $result === $expected;
}

// Phase 1 Tests
echo "📦 Phase 1: Foundation and Factory Design\n";
echo "----------------------------------------\n";

$phase1Tests = [
    'Performance Indexes Migration' => function() {
        return Schema::hasColumn('books', 'is_active') && 
               Schema::hasColumn('books', 'publisher') &&
               Schema::hasColumn('books', 'format');
    },
    'BookFactory Enhanced' => function() {
        return class_exists('Database\Factories\BookFactory');
    },
    'MassBookSeeder Created' => function() {
        return class_exists('Database\Seeders\MassBookSeeder');
    },
    'Books Table Has Lab 7 Fields' => function() {
        return Schema::hasColumn('books', 'publisher') &&
               Schema::hasColumn('books', 'format') &&
               Schema::hasColumn('books', 'is_active') &&
               Schema::hasColumn('books', 'published_at') &&
               Schema::hasColumn('books', 'pages');
    },
];

$phase1Passed = true;
foreach ($phase1Tests as $name => $check) {
    if (!checkComponent($name, $check)) {
        $phase1Passed = false;
    }
}
echo "\n";

// Phase 2 Tests
echo "🚀 Phase 2: Query Performance Optimization\n";
echo "----------------------------------------\n";

$phase2Tests = [
    'BookRepository Exists' => function() {
        return class_exists('App\Repositories\BookRepository');
    },
    'BookCacheService Exists' => function() {
        return class_exists('App\Services\BookCacheService');
    },
    'OptimizedBookController Exists' => function() {
        return class_exists('App\Http\Controllers\Api\OptimizedBookController');
    },
    'BookResource Exists' => function() {
        return class_exists('App\Http\Resources\BookResource');
    },
    'BookListResource Exists' => function() {
        return class_exists('App\Http\Resources\BookListResource');
    },
    'CategoryResource Exists' => function() {
        return class_exists('App\Http\Resources\CategoryResource');
    },
    'DebugBar Installed' => function() {
        return class_exists('Barryvdh\Debugbar\LaravelDebugbar');
    },
    'Query Detector Installed' => function() {
        return class_exists('BeyondCode\QueryDetector\QueryDetectorMiddleware');
    },
];

$phase2Passed = true;
foreach ($phase2Tests as $name => $check) {
    if (!checkComponent($name, $check)) {
        $phase2Passed = false;
    }
}
echo "\n";

// Phase 3 Tests
echo "🗄️ Phase 3: Advanced Scalability Features\n";
echo "----------------------------------------\n";

$phase3Tests = [
    'Database Partitioning Migration' => function() {
        return file_exists(__DIR__.'/database/migrations/2026_04_25_120000_partition_books_by_year.php');
    },
    'Redis Configuration Updated' => function() {
        $config = include __DIR__.'/config/database.php';
        return isset($config['redis']['session']);
    },
];

$phase3Passed = true;
foreach ($phase3Tests as $name => $check) {
    if (!checkComponent($name, $check)) {
        $phase3Passed = false;
    }
}
echo "\n";

// Database Connection Tests
echo "🗃️ Database Connection Tests\n";
echo "---------------------------\n";

$dbTests = [
    'Books Table Exists' => function() {
        return Schema::hasTable('books');
    },
    'Categories Table Exists' => function() {
        return Schema::hasTable('categories');
    },
    'Performance Indexes Present' => function() {
        $indexes = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM books WHERE Key_name LIKE 'idx_books_%'");
        return count($indexes) >= 5; // Should have at least 5 performance indexes
    },
];

$dbPassed = true;
foreach ($dbTests as $name => $check) {
    if (!checkComponent($name, $check)) {
        $dbPassed = false;
    }
}
echo "\n";

// Data Integrity Tests
echo "📊 Data Integrity Tests\n";
echo "---------------------\n";

$currentBooksCount = \Illuminate\Support\Facades\DB::table('books')->count();
$categoriesCount = \Illuminate\Support\Facades\DB::table('categories')->count();

$dataTests = [
    'Categories Available' => function() use ($categoriesCount) {
        return $categoriesCount > 0;
    },
    'Books Table Has Data' => function() use ($currentBooksCount) {
        return $currentBooksCount > 0;
    },
    'ISBN Format Validation' => function() {
        $sampleBook = \Illuminate\Support\Facades\DB::table('books')->first();
        if (!$sampleBook) return false;
        return strlen($sampleBook->isbn) === 13 && is_numeric($sampleBook->isbn);
    },
];

$dataPassed = true;
foreach ($dataTests as $name => $check) {
    if (!checkComponent($name, $check)) {
        $dataPassed = false;
    }
}
echo "\n";

// Summary
echo "📋 Implementation Summary\n";
echo "========================\n";

$allPhases = [
    'Phase 1: Foundation' => $phase1Passed,
    'Phase 2: Performance' => $phase2Passed,
    'Phase 3: Scalability' => $phase3Passed,
    'Database Layer' => $dbPassed,
    'Data Integrity' => $dataPassed,
];

$totalPassed = 0;
$totalChecks = count($allPhases);

foreach ($allPhases as $name => $passed) {
    $status = $passed ? "✅" : "❌";
    echo sprintf("%-25s %s\n", $name, $status);
    if ($passed) $totalPassed++;
}

echo "\n";
echo "Overall Score: $totalPassed/$totalChecks phases completed\n";

if ($totalPassed === $totalChecks) {
    echo "🎉 SUCCESS: Laboratory Activity 7 implementation is complete!\n";
    echo "\n🚀 Ready for mass data seeding and performance testing!\n";
    echo "\n📋 Next Steps:\n";
    echo "1. Run mass seeder: php artisan db:seed --class=MassBookSeeder\n";
    echo "2. Test performance: php artisan serve and test API endpoints\n";
    echo "3. Monitor caching: Check Redis performance\n";
    echo "4. Validate benchmarks: Run performance tests\n";
} else {
    echo "⚠️  WARNING: Some components need attention.\n";
    echo "\n📝 Review failed components above and implement missing features.\n";
}

echo "\n";
echo "📚 For detailed instructions, see: LAB7_IMPLEMENTATION_GUIDE.md\n";
echo "🧪 For testing procedures, see the implementation guide\n";
