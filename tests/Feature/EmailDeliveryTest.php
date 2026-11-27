<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\User;
use App\Notifications\NewOrderAdminNotification;
use App\Notifications\OrderPaidNotification;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/** IP-4: order emails go through the queue worker and their delivery time is logged. */
class EmailDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_emails_are_queued(): void
    {
        foreach ([OrderPlacedNotification::class, NewOrderAdminNotification::class, OrderStatusChangedNotification::class, OrderPaidNotification::class] as $class) {
            $this->assertContains(ShouldQueue::class, class_implements($class), "{$class} should be queued");
        }
    }

    public function test_checkout_pushes_emails_to_the_queue_instead_of_sending_them(): void
    {
        Queue::fake();
        $category = Category::create(['name' => 'Fiction']);
        $seller = User::factory()->create(['role' => 'seller', 'seller_status' => 'approved', 'email_verified_at' => now()]);
        $buyer = User::factory()->create(['role' => 'customer', 'email_verified_at' => now()]);
        $book = Book::factory()->create(['seller_id' => $seller->id, 'category_id' => $category->id, 'is_active' => true, 'stock_quantity' => 5, 'price' => 100]);

        $this->actingAs($buyer)
            ->withSession(['cart' => [$book->id => 1]])
            ->post('/cart/checkout', ['shipping_address' => '123 Katipunan Ave, Quezon City', 'payment_method' => 'cash_on_delivery'])
            ->assertRedirect();

        Queue::assertPushed(SendQueuedNotifications::class, fn ($job) => $job->notification instanceof OrderPlacedNotification);
        Queue::assertPushed(SendQueuedNotifications::class, fn ($job) => $job->notification instanceof NewOrderAdminNotification);
    }

    public function test_sent_emails_are_logged_with_their_delivery_time(): void
    {
        config(['mail.default' => 'array']);
        $buyer = User::factory()->create(['role' => 'customer', 'email_verified_at' => now()]);
        $order = Order::create(['user_id' => $buyer->id, 'total_amount' => 100, 'status' => 'pending']);

        $notification = new OrderPlacedNotification($order);
        $notification->queuedAt = now()->subSeconds(7)->toIso8601String(); // waited 7 s in the queue
        $buyer->notify($notification); // the test queue runs jobs immediately

        $log = NotificationLog::firstOrFail();
        $this->assertSame('OrderPlacedNotification', $log->notification);
        $this->assertSame('sent', $log->status);
        $this->assertSame($buyer->email, $log->recipient);
        $this->assertSame($order->id, $log->order_id);
        // At least the 7 s it waited, plus however long sending took.
        $this->assertGreaterThanOrEqual(7, $log->delivery_seconds);
        $this->assertLessThan(30, $log->delivery_seconds);
    }

    public function test_delivery_report_shows_the_on_time_rate(): void
    {
        foreach ([5, 12, 30] as $seconds) {
            NotificationLog::create(['notification' => 'OrderPlacedNotification', 'channel' => 'mail', 'status' => 'sent', 'queued_at' => now(), 'sent_at' => now(), 'delivery_seconds' => $seconds]);
        }

        $this->artisan('mail:delivery-report')
            ->expectsOutputToContain('3 of 3 emails (100%) were sent within 60 seconds')
            ->assertSuccessful();
    }
}
