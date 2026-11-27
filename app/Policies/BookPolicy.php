<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /** Admins and approved sellers can create listings. */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isApprovedSeller();
    }

    /** Admins can edit any listing; approved sellers only their own. */
    public function update(User $user, Book $book): bool
    {
        return $user->isAdmin() || $this->ownsListing($user, $book);
    }

    public function delete(User $user, Book $book): bool
    {
        return $user->isAdmin() || $this->ownsListing($user, $book);
    }

    private function ownsListing(User $user, Book $book): bool
    {
        return $user->isApprovedSeller() && $book->seller_id === $user->id;
    }
}
