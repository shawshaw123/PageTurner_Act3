<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            BookDataSeeder::class,
        ]);

        // Create customer users
        $customers = User::factory(10)->create(['role' => 'customer']);

        // Create Snoppy D. Putoy as a special reviewer
        $snoppy = User::factory()->create([
            'name' => 'Snoppy D. Putoy',
            'email' => 'snoppy@example.com',
            'role' => 'customer',
        ]);

        // Create reviews for the seeded books
        $books = Book::all();
        if ($books->count() > 0) {
            // Ensure Snoppy D. Putoy reviews every book
            $books->each(function ($book) use ($snoppy) {
                Review::factory()->create([
                    'user_id' => $snoppy->id,
                    'book_id' => $book->id,
                    'rating' => 5,
                    'comment' => 'Nice book',
                ]);
            });
        }
    }
}
