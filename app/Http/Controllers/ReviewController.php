<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;

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

        // Check if user has purchased the book
        $hasPurchased = \App\Models\OrderItem::whereHas('order', function($q) {
                $q->where('user_id', auth()->id())->where('status', 'completed');
            })
            ->where('book_id', $book->id)
            ->exists();

        if (!$hasPurchased && !auth()->user()->isAdmin()) {
            return redirect()->route('books.show', $book)
                ->with('error', 'You can only review books you have purchased and completed.');
        }

        // Check if user already reviewed this book
        $existingReview = Review::where('user_id', auth()->id())
            ->where('book_id', $book->id)
            ->first();

        if ($existingReview) {
            $existingReview->update($validated);
            $message = 'Review updated successfully!';
        } else {
            Review::create($validated);
            $message = 'Review submitted successfully!';
        }

        return redirect()->route('books.show', $book)
            ->with('success', $message);
    }

    public function destroy(Review $review)
    {
        // Only allow owner or admin to delete
        if (auth()->id() !== $review->user_id && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $book = $review->book;
        $review->delete();

        return redirect()->route('books.show', $book)
            ->with('success', 'Review deleted successfully!');
    }
}
