<?php
// Update existing reviews to have default 'approved' status
echo "=== UPDATING REVIEW STATUS ===\n\n";

try {
    // Load Laravel
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    // Get all reviews without status
    $reviewsWithoutStatus = \App\Models\Review::whereNull('status')->get();
    
    if ($reviewsWithoutStatus->count() > 0) {
        echo "Found {$reviewsWithoutStatus->count()} reviews without status\n";
        echo "Updating to 'approved' status...\n";
        
        foreach ($reviewsWithoutStatus as $review) {
            $review->update(['status' => 'approved']);
            echo "✅ Updated review ID: {$review->id} (Book: {$review->book->title})\n";
        }
        
        echo "\n🎉 All reviews updated successfully!\n";
    } else {
        echo "✅ All reviews already have status\n";
    }
    
    // Show current status counts
    $totalReviews = \App\Models\Review::count();
    $approvedReviews = \App\Models\Review::where('status', 'approved')->count();
    $rejectedReviews = \App\Models\Review::where('status', 'rejected')->count();
    $pendingReviews = \App\Models\Review::where('status', 'pending')->count();
    
    echo "\n📊 Current Review Status:\n";
    echo "Total Reviews: $totalReviews\n";
    echo "Approved: $approvedReviews\n";
    echo "Rejected: $rejectedReviews\n";
    echo "Pending: $pendingReviews\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
