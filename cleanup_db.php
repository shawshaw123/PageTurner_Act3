<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // List databases
    $databases = Illuminate\Support\Facades\DB::select('SHOW DATABASES');
    $laravelExists = false;
    foreach ($databases as $db) {
        if ($db->Database === 'laravel') {
            $laravelExists = true;
            break;
        }
    }

    if ($laravelExists) {
        echo "Database 'laravel' exists." . PHP_EOL;
        // Drop it
        Illuminate\Support\Facades\DB::statement('DROP DATABASE laravel');
        echo "Database 'laravel' has been dropped." . PHP_EOL;
    } else {
        echo "Database 'laravel' does not exist." . PHP_EOL;
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
