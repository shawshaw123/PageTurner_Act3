<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Sample sellers for the marketplace. All use the password "password".
 * Existing books without a seller are shared between the two approved stores.
 */
class SellerSeeder extends Seeder
{
    public function run(): void
    {
        $sellers = [
            ['email' => 'seller@example.com', 'name' => 'Maria Santos', 'store_name' => 'Santos Secondhand Books', 'store_description' => 'Pre-loved fiction and classics in good condition.', 'seller_status' => 'approved'],
            ['email' => 'bookstore@example.com', 'name' => 'Jose Reyes', 'store_name' => 'Kapitolyo Book Nook', 'store_description' => 'A small neighbourhood bookstore with new releases and non-fiction.', 'seller_status' => 'approved'],
            ['email' => 'pending.seller@example.com', 'name' => 'Ana Cruz', 'store_name' => 'Cruz Comics & Books', 'store_description' => 'Awaiting approval.', 'seller_status' => 'pending'],
        ];

        $approved = [];
        foreach ($sellers as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                $data + ['role' => 'seller', 'password' => Hash::make('password')]
            );
            $user->forceFill(['email_verified_at' => now()])->save();

            if ($data['seller_status'] === 'approved') {
                $approved[] = $user->id;
            }

            $this->command?->info("Seller: {$data['email']} / password ({$data['seller_status']})");
        }

        Book::whereNull('seller_id')->orderBy('id')->pluck('id')
            ->each(function (int $bookId, int $i) use ($approved) {
                Book::whereKey($bookId)->update(['seller_id' => $approved[$i % count($approved)]]);
            });
    }
}
