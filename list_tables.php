<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Connected Database: " . Illuminate\Support\Facades\DB::connection()->getDatabaseName() . PHP_EOL;

$tables = Illuminate\Support\Facades\DB::select('SHOW TABLES');
foreach ($tables as $table) {
    foreach ($table as $key => $value) {
        echo $value . PHP_EOL;
    }
}
