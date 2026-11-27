<?php
// Run this script to fix review status
echo "Starting review status fix...\n";

// Include Laravel
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Initialize Laravel
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Fix the reviews
try {
    $reviews = \App\Models\Review::whereNull('status')->get();
    
    echo "Found " . $reviews->count() . " reviews without status\n";
    
    foreach ($reviews as $review) {
        $review->update(['status' => 'approved']);
        echo "Updated review ID: " . $review->id . "\n";
    }
    
    echo "\nAll reviews have been updated!\n";
    
    // Show final status
    $approved = \App\Models\Review::where('status', 'approved')->count();
    $total = \App\Models\Review::count();
    
    echo "\nSummary:\n";
    echo "Total reviews: $total\n";
    echo "Approved: $approved\n";
    
    echo "\n✅ Fix completed! Admin should now see approve/reject buttons.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
