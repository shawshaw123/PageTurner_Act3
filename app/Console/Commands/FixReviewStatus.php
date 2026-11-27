<?php

namespace App\Console\Commands;

use App\Models\Review;
use Illuminate\Console\Command;

class FixReviewStatus extends Command
{
    protected $signature = 'reviews:fix-status';
    protected $description = 'Fix review status for all reviews';

    public function handle()
    {
        $this->info('Fixing review status...');
        
        // Get all reviews without status
        $reviewsWithoutStatus = Review::whereNull('status')->get();
        
        if ($reviewsWithoutStatus->count() > 0) {
            $this->info("Found {$reviewsWithoutStatus->count()} reviews without status");
            
            foreach ($reviewsWithoutStatus as $review) {
                $review->update(['status' => 'approved']);
                $this->line("✅ Updated review ID: {$review->id} (Book: {$review->book->title})");
            }
            
            $this->info('All reviews updated successfully!');
        } else {
            $this->info('All reviews already have status');
        }
        
        // Show status summary
        $totalReviews = Review::count();
        $approvedReviews = Review::where('status', 'approved')->count();
        $rejectedReviews = Review::where('status', 'rejected')->count();
        $pendingReviews = Review::where('status', 'pending')->count();
        
        $this->info("\n📊 Current Review Status:");
        $this->info("Total Reviews: $totalReviews");
        $this->info("Approved: $approvedReviews");
        $this->info("Rejected: $rejectedReviews");
        $this->info("Pending: $pendingReviews");
        
        $this->info("\n✅ Review status fix completed!");
        
        return 0;
    }
}
