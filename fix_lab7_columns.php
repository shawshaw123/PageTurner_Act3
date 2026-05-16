<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 Fixing Lab 7 Columns\n";
echo "====================\n\n";

// Lab 7 columns to add
$lab7Columns = [
    'publisher' => "VARCHAR(255) AFTER author",
    'format' => "VARCHAR(50) AFTER price", 
    'is_active' => "BOOLEAN DEFAULT 1 AFTER stock_quantity",
    'published_at' => "TIMESTAMP NULL AFTER description",
    'pages' => "INT AFTER published_at",
    'language' => "VARCHAR(50) DEFAULT 'English' AFTER pages",
    'dimensions' => "VARCHAR(20) AFTER language",
    'weight' => "DECIMAL(4,2) AFTER dimensions"
];

echo "Checking and adding Lab 7 columns...\n";

foreach ($lab7Columns as $column => $definition) {
    if (!Schema::hasColumn('books', $column)) {
        echo "Adding column: $column...\n";
        try {
            DB::statement("ALTER TABLE books ADD COLUMN $column $definition");
            echo "✅ $column added successfully\n";
        } catch (Exception $e) {
            echo "❌ Failed to add $column: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✅ $column already exists\n";
    }
}

echo "\n🔍 Final column check:\n";
$allColumns = Schema::getColumnListing('books');
foreach ($allColumns as $column) {
    echo "  - $column\n";
}

echo "\n📊 Current book count: " . DB::table('books')->count() . "\n";

// Check if all Lab 7 columns exist now
$missingColumns = [];
foreach (array_keys($lab7Columns) as $column) {
    if (!Schema::hasColumn('books', $column)) {
        $missingColumns[] = $column;
    }
}

if (empty($missingColumns)) {
    echo "\n🎉 ALL LAB 7 COLUMNS ARE READY!\n";
    echo "🚀 You can now run the 1M seeder:\n";
    echo "   php artisan db:seed --class=OptimizedMassBookSeeder\n";
} else {
    echo "\n❌ Still missing columns: " . implode(', ', $missingColumns) . "\n";
}
