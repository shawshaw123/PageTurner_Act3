<?php
// Quick fix using Laravel's built-in database connection
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Bootstrap Laravel
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Fix reviews
$reviews = \App\Models\Review::whereNull('status')->get();

echo "Found " . $reviews->count() . " reviews without status\n";

foreach ($reviews as $review) {
    $review->update(['status' => 'approved']);
    echo "Updated review ID: " . $review->id . "\n";
}

echo "Done! All reviews now have status.\n";

// Show summary
$approved = \App\Models\Review::where('status', 'approved')->count();
echo "Total approved reviews: " . $approved . "\n";
?>
