<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/** Reviews (verified purchase): only buyers who received the book may review it. */
class VerifiedReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Queue::fake();
        $category = Category::create(['name' => 'Fiction']);
        $this->buyer = User::factory()->create(['role' => 'customer', 'email_verified_at' => now()]);
        $this->book = Book::factory()->create(['category_id' => $category->id, 'is_active' => true, 'stock_quantity' => 5]);
    }

    private function orderWithBook(string $status): void
    {
        $order = Order::create(['user_id' => $this->buyer->id, 'total_amount' => 10, 'status' => $status]);
        OrderItem::create(['order_id' => $order->id, 'book_id' => $this->book->id, 'quantity' => 1, 'price' => 10]);
    }

    private function review()
    {
        return $this->actingAs($this->buyer)->post(route('reviews.store', $this->book), ['rating' => 5, 'comment' => 'Loved it.']);
    }

    public function test_buyer_who_has_not_bought_the_book_cannot_review_it(): void
    {
        $this->review()->assertSessionHas('error');
        $this->assertDatabaseMissing('reviews', ['user_id' => $this->buyer->id]);
    }

    public function test_pending_order_is_not_enough_to_review(): void
    {
        $this->orderWithBook('pending');

        $this->review()->assertSessionHas('error');
        $this->assertDatabaseMissing('reviews', ['user_id' => $this->buyer->id]);
    }

    public function test_buyer_can_review_after_the_order_is_shipped_or_completed(): void
    {
        $this->orderWithBook('completed');

        $this->review()->assertSessionHas('success');
        $this->assertDatabaseHas('reviews', ['user_id' => $this->buyer->id, 'book_id' => $this->book->id, 'rating' => 5]);
    }

    public function test_book_page_shows_the_form_only_to_verified_buyers(): void
    {
        $this->actingAs($this->buyer)->get(route('books.show', $this->book))->assertSee('Only buyers who received this book can review it');

        $this->orderWithBook('shipped');
        $this->actingAs($this->buyer)->get(route('books.show', $this->book))->assertSee('Submit Review');
    }
}
