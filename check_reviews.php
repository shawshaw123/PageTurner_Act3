<?php
// Check review status in database
echo "=== REVIEW STATUS CHECK ===\n\n";

try {
    // Load Laravel
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    // Get all reviews with their status
    $reviews = \App\Models\Review::with('book', 'user')->get();
    
    echo "Total Reviews: " . $reviews->count() . "\n\n";
    
    foreach ($reviews as $review) {
        echo "Review ID: {$review->id}\n";
        echo "Book: {$review->book->title}\n";
        echo "Reviewer: {$review->user->name}\n";
        echo "Rating: {$review->rating} stars\n";
        echo "Status: " . ($review->status ?? 'NULL') . "\n";
        echo "Comment: " . ($review->comment ?? 'No comment') . "\n";
        echo "---\n";
    }
    
    // Status summary
    $approved = \App\Models\Review::where('status', 'approved')->count();
    $rejected = \App\Models\Review::where('status', 'rejected')->count();
    $nullStatus = \App\Models\Review::whereNull('status')->count();
    
    echo "\n📊 STATUS SUMMARY:\n";
    echo "Approved: $approved\n";
    echo "Rejected: $rejected\n";
    echo "No Status: $nullStatus\n";
    
    if ($nullStatus > 0) {
        echo "\n⚠️  Some reviews still have no status!\n";
    } else {
        echo "\n✅ All reviews have status!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
