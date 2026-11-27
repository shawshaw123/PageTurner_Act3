<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user, \App\Models\Book $book): bool
    {
        // User must have verified email to write reviews
        if (!$user->hasVerifiedEmail()) {
            return false;
        }

        // Only admins or purchasers can review
        if ($user->isAdmin()) {
            return true;
        }

        return \App\Models\OrderItem::whereHas('order', function($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'completed');
            })
            ->where('book_id', $book->id)
            ->exists();
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->isAdmin() || $user->id === $review->user_id;
    }
}
