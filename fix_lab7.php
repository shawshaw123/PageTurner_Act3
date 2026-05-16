<?php

/**
 * Lab 7 Fix Script
 * Run: php fix_lab7.php
 */

echo "🔧 Lab 7 Fix Script\n";
echo "==================\n\n";

// Step 1: Run migrations
echo "📋 Step 1: Running Migrations\n";
echo "----------------------------\n";

// Check if migrations table exists
try {
    $migrations = DB::table('migrations')->pluck('migration')->toArray();
    echo "✅ Migrations table accessible\n";
    
    // Check for Lab 7 migrations
    $lab7Migrations = [
        '2026_04_25_110000_add_lab7_fields_to_books_table',
        '2026_04_25_100000_optimize_books_table_indexes',
        '2026_04_25_120000_partition_books_by_year'
    ];
    
    foreach ($lab7Migrations as $migration) {
        $exists = in_array($migration, $migrations);
        echo "  $migration: " . ($exists ? "✅ RUN" : "❌ PENDING") . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Cannot access migrations: " . $e->getMessage() . "\n";
}

echo "\n";

// Step 2: Check database schema
echo "📋 Step 2: Database Schema Check\n";
echo "------------------------------\n";

$fields = ['publisher', 'format', 'is_active', 'published_at', 'pages', 'language', 'dimensions', 'weight'];
echo "Lab 7 Fields:\n";
foreach ($fields as $field) {
    $exists = Schema::hasColumn('books', $field);
    echo "  $field: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
}

echo "\n";

// Step 3: Check indexes
echo "📋 Step 3: Performance Indexes\n";
echo "------------------------------\n";

try {
    $indexes = DB::select("SHOW INDEX FROM books WHERE Key_name LIKE 'idx_books_%'");
    echo "Found " . count($indexes) . " performance indexes:\n";
    foreach ($indexes as $index) {
        echo "  ✅ {$index->Key_name}\n";
    }
} catch (Exception $e) {
    echo "❌ Cannot check indexes: " . $e->getMessage() . "\n";
}

echo "\n";

// Step 4: Check data
echo "📋 Step 4: Data Status\n";
echo "---------------------\n";

try {
    $totalBooks = DB::table('books')->count();
    echo "Total books: $totalBooks\n";
    
    if ($totalBooks > 0) {
        $activeBooks = DB::table('books')->where('is_active', true)->count();
        $validIsbns = DB::table('books')->whereRaw('LENGTH(isbn) = 13')->whereRaw('isbn REGEXP \"^[0-9]{13}$\"')->count();
        
        echo "Active books: $activeBooks\n";
        echo "Valid ISBNs: $validIsbns\n";
        
        // Check if we need to fix data
        if ($validIsbns < $totalBooks) {
            echo "⚠️  Need to fix ISBNs\n";
        }
        
        if (Schema::hasColumn('books', 'is_active') && $activeBooks == 0) {
            echo "⚠️  Need to set is_active flag\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Cannot check data: " . $e->getMessage() . "\n";
}

echo "\n";

// Step 5: Check packages
echo "📋 Step 5: Package Check\n";
echo "------------------------\n";

$packages = [
    'DebugBar' => 'Barryvdh\Debugbar\LaravelDebugbar',
    'Query Detector' => 'BeyondCode\QueryDetector\QueryDetectorMiddleware',
    'BookRepository' => 'App\Repositories\BookRepository',
    'BookCacheService' => 'App\Services\BookCacheService',
    'OptimizedBookController' => 'App\Http\Controllers\Api\OptimizedBookController',
];

foreach ($packages as $name => $class) {
    $exists = class_exists($class);
    echo "  $name: " . ($exists ? "✅ INSTALLED" : "❌ MISSING") . "\n";
}

echo "\n";

// Step 6: Recommendations
echo "🎯 Step 6: Recommendations\n";
echo "---------------------------\n";

if (!Schema::hasColumn('books', 'is_active')) {
    echo "📦 Run: php artisan migrate --force\n";
}

if (class_exists('App\Repositories\BookRepository') && Schema::hasColumn('books', 'is_active')) {
    echo "🚀 Run: php artisan serve\n";
    echo "🧪 Then test: curl \"http://127.0.0.1:8000/api/books\"\n";
}

echo "📊 Run: php lab7_test.php (when ready)\n";
echo "📚 See: LAB7_TESTING_PLAN.md for detailed testing\n";

echo "\n🎉 Fix script complete!\n";
