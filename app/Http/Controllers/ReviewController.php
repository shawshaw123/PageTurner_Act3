<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use App\Notifications\NewReviewSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ReviewController extends Controller
{
    public function store(Request $request, Book $book)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['book_id'] = $book->id;
        $validated['status'] = 'approved'; // Default status for new reviews

        // Check if user already reviewed this book
        $existingReview = Review::where('user_id', auth()->id())
            ->where('book_id', $book->id)
            ->first();

        if ($existingReview) {
            $existingReview->update($validated);
            $message = 'Review updated successfully!';
        } else {
            $review = Review::create($validated);
            $review->load(['user', 'book']);

            // Notify all admins about the new review
            $admins = User::where('role', 'admin')->get();
            Notification::send($admins, new NewReviewSubmitted($review));

            $message = 'Review submitted successfully!';
        }

        return redirect()->route('books.show', $book)
            ->with('success', $message);
    }

    public function destroy(Review $review)
    {
        // Only allow owner or admin to delete
        \Illuminate\Support\Facades\Gate::authorize('delete', $review);

        $book = $review->book;
        $review->delete();

        return redirect()->route('books.show', $book)
            ->with('success', 'Review deleted successfully!');
    }

    /**
     * Approve a review (admin only)
     */
    public function approve(Review $review)
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $review->update(['status' => 'approved']);
            
            // Notify the reviewer about approval
            $review->user->notify(new \App\Notifications\ReviewStatusUpdated($review, 'approved'));
            
            return back()->with('success', 'Review approved successfully!');
        } catch (\Illuminate\Database\QueryException $e) {
            // If status column doesn't exist, just return success
            if (strpos($e->getMessage(), 'Column not found') !== false) {
                // Status column doesn't exist, create it
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE reviews ADD COLUMN status VARCHAR(20) DEFAULT \'approved\' AFTER comment');
                
                // Try updating again
                $review->update(['status' => 'approved']);
                
                // Notify the reviewer about approval
                $review->user->notify(new \App\Notifications\ReviewStatusUpdated($review, 'approved'));
                
                return back()->with('success', 'Review approved successfully! (Status column added)');
            }
            
            throw $e;
        }
    }

    /**
     * Reject a review (admin only)
     */
    public function reject(Review $review)
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $review->update(['status' => 'rejected']);
            
            // Notify the reviewer about rejection
            $review->user->notify(new \App\Notifications\ReviewStatusUpdated($review, 'rejected'));
            
            return back()->with('success', 'Review rejected successfully!');
        } catch (\Illuminate\Database\QueryException $e) {
            // If status column doesn't exist, just return success
            if (strpos($e->getMessage(), 'Column not found') !== false) {
                // Status column doesn't exist, create it
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE reviews ADD COLUMN status VARCHAR(20) DEFAULT \'approved\' AFTER comment');
                
                // Try updating again
                $review->update(['status' => 'rejected']);
                
                // Notify the reviewer about rejection
                $review->user->notify(new \App\Notifications\ReviewStatusUpdated($review, 'rejected'));
                
                return back()->with('success', 'Review rejected successfully! (Status column added)');
            }
            
            throw $e;
        }
    }
}
