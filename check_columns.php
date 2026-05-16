<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Checking Database Status\n";
echo "==========================\n\n";

// Check if books table exists
echo "Books table: " . (Schema::hasTable('books') ? "✅ EXISTS" : "❌ MISSING") . "\n";

// Check all columns in books table
if (Schema::hasTable('books')) {
    $columns = Schema::getColumnListing('books');
    echo "\nAll columns in books table:\n";
    foreach ($columns as $column) {
        echo "  - $column\n";
    }
    
    // Check Lab 7 specific columns
    echo "\nLab 7 columns check:\n";
    $lab7Columns = ['publisher', 'format', 'is_active', 'published_at', 'pages', 'language', 'dimensions', 'weight'];
    $allExist = true;
    
    foreach ($lab7Columns as $column) {
        $exists = Schema::hasColumn('books', $column);
        echo "  $column: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
        if (!$exists) $allExist = false;
    }
    
    if ($allExist) {
        echo "\n✅ All Lab 7 columns are present!\n";
        echo "🚀 You can now run the 1M seeder!\n";
    } else {
        echo "\n❌ Some Lab 7 columns are missing.\n";
        echo "📋 Running migration to add missing columns...\n";
        
        // Try to run the migration
        try {
            Artisan::call('migrate', ['--path' => 'database/migrations/2026_04_25_110000_add_lab7_fields_to_books_table.php', '--force' => true]);
            echo "✅ Migration completed!\n";
        } catch (Exception $e) {
            echo "❌ Migration failed: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nCurrent book count: " . DB::table('books')->count() . "\n";
}
