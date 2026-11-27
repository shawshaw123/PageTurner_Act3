<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Partition Fix (Handle ALL unique indexes) ===\n";

try {
    // Step 1: Find all unique indexes on books table
    $indexes = DB::select("SHOW INDEX FROM books WHERE Non_unique = 0 AND Key_name != 'PRIMARY'");
    $uniqueIndexNames = array_unique(array_map(fn($i) => $i->Key_name, $indexes));
    
    echo "Unique indexes found: " . implode(', ', $uniqueIndexNames) . "\n";

    // Step 2: Drop all unique indexes (convert to regular indexes later)
    foreach ($uniqueIndexNames as $indexName) {
        echo "  Dropping unique index: $indexName\n";
        try {
            DB::statement("ALTER TABLE books DROP INDEX `$indexName`");
        } catch (\Exception $e) {
            echo "    (skipped: " . $e->getMessage() . ")\n";
        }
    }

    // Step 3: Apply partitioning
    echo "Applying partitioning...\n";
    DB::statement("
        ALTER TABLE books 
        PARTITION BY RANGE (YEAR(published_at)) (
            PARTITION p_old VALUES LESS THAN (2000),
            PARTITION p2000 VALUES LESS THAN (2005),
            PARTITION p2005 VALUES LESS THAN (2010),
            PARTITION p2010 VALUES LESS THAN (2015),
            PARTITION p2015 VALUES LESS THAN (2020),
            PARTITION p2020 VALUES LESS THAN (2025),
            PARTITION p_future VALUES LESS THAN MAXVALUE
        )
    ");
    echo "SUCCESS: Table partitioned!\n";

    // Step 4: Re-add isbn as regular (non-unique) index for performance
    echo "Re-adding isbn index (non-unique)...\n";
    try {
        DB::statement("ALTER TABLE books ADD INDEX idx_isbn (isbn)");
    } catch (\Exception $e) {
        echo "  (isbn index may already exist)\n";
    }

    // Verify
    $partitions = DB::select("
        SELECT PARTITION_NAME 
        FROM INFORMATION_SCHEMA.PARTITIONS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'books' 
        AND PARTITION_NAME IS NOT NULL
    ");
    echo "\nPartitions: " . count($partitions) . "\n";
    foreach ($partitions as $p) {
        echo "  - " . $p->PARTITION_NAME . "\n";
    }

    // Prove partition pruning
    echo "\n=== EXPLAIN Partition Pruning ===\n";
    $explain = DB::select("EXPLAIN SELECT * FROM books WHERE published_at BETWEEN '2024-01-01' AND '2024-12-31'");
    foreach ($explain as $row) {
        echo "  partitions: " . ($row->partitions ?? 'N/A') . "\n";
        echo "  rows: " . ($row->rows ?? 'N/A') . "\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
