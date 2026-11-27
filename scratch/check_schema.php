<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Checking Books table columns:\n";
$columns = Schema::getColumnListing('books');
print_r($columns);

echo "\nChecking Books table indexes:\n";
$indexes = DB::select('SHOW INDEX FROM books');
foreach ($indexes as $index) {
    echo "{$index->Key_name} ({$index->Column_name})\n";
}
