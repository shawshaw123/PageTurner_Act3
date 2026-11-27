<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Starting Database Seeding...');
        
        // Basic seeders for core data
        $this->call([
            AdminSeeder::class,
            BookDataSeeder::class,
            SellerSeeder::class,
        ]);

        // Create customer users
        $customers = User::factory(10)->create(['role' => 'customer', 'email_verified_at' => now()]);

        // Create Snoppy D. Putoy as a special reviewer
        $snoppy = User::factory()->create([
            'name' => 'Snoppy D. Putoy',
            'email' => 'snoppy@example.com',
            'email_verified_at' => now(),
            'role' => 'customer',
        ]);

        // Reviews are for verified purchases only, so Snoppy first gets a completed,
        // paid order from each store containing every sample book.
        $books = Book::all();
        foreach ($books->groupBy('seller_id') as $sellerId => $sellerBooks) {
            $order = Order::create([
                'user_id' => $snoppy->id,
                'seller_id' => $sellerId ?: null,
                'checkout_reference' => 'SEED-' . ($sellerId ?: 0),
                'total_amount' => $sellerBooks->sum('price'),
                'status' => 'completed',
                'payment_method' => 'cash_on_delivery',
                'payment_status' => 'paid',
                'paid_at' => now(),
                'shipping_address' => '123 Katipunan Ave, Quezon City',
            ]);
            foreach ($sellerBooks as $book) {
                OrderItem::create(['order_id' => $order->id, 'book_id' => $book->id, 'quantity' => 1, 'price' => $book->price]);
            }
        }

        // Create reviews for the seeded books
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
