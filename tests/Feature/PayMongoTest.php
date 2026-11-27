<?php

namespace Tests\Feature;

use App\Integrations\PayMongo\WebhookSignature;
use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\WebhookEvent;
use App\Notifications\OrderPaidNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** IP-2 (PayMongo checkout), IP-3 (signed webhook) and the expiry scheduler. */
class PayMongoTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'whsk_test_secret';

    private User $buyer;
    private User $seller;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        config([
            'services.paymongo.secret_key' => 'sk_test_123',
            'services.paymongo.webhook_secret' => self::SECRET,
            'services.paymongo.url' => 'https://api.paymongo.com/v1',
            'services.paymongo.payment_window_minutes' => 60,
        ]);

        $category = Category::create(['name' => 'Fiction']);
        $this->seller = User::factory()->create(['role' => 'seller', 'seller_status' => 'approved', 'store_name' => 'Pay Store', 'email_verified_at' => now()]);
        $this->buyer = User::factory()->create(['role' => 'customer', 'email_verified_at' => now()]);
        $this->book = Book::factory()->create([
            'seller_id' => $this->seller->id, 'category_id' => $category->id, 'is_active' => true,
            'stock_quantity' => 10, 'price' => 250, 'title' => 'Paid Book',
        ]);
    }

    private function fakeSessionCreated(string $sessionId = 'cs_test_1'): void
    {
        Http::fake([
            'api.paymongo.com/v1/checkout_sessions' => Http::response(['data' => [
                'id' => $sessionId,
                'type' => 'checkout_session',
                'attributes' => [
                    'checkout_url' => "https://checkout.paymongo.com/{$sessionId}",
                    'payment_intent' => ['id' => 'pi_test_1'],
                    'status' => 'active',
                ],
            ]], 200),
        ]);
    }

    /** Check out 2 copies with PayMongo through the website; returns the Payment. */
    private function checkoutOnline(): Payment
    {
        $this->fakeSessionCreated();

        $this->actingAs($this->buyer)
            ->withSession(['cart' => [$this->book->id => 2]])
            ->post('/cart/checkout', ['shipping_address' => '123 Katipunan Ave, Quezon City', 'payment_method' => 'paymongo'])
            ->assertRedirect('https://checkout.paymongo.com/cs_test_1');

        return Payment::firstOrFail();
    }

    private function sendWebhook(array $event, ?string $signature = null)
    {
        $raw = json_encode($event);

        return $this->call('POST', '/webhooks/paymongo', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_PAYMONGO_SIGNATURE' => $signature ?? WebhookSignature::sign($raw, self::SECRET),
        ], $raw);
    }

    private function paidEvent(string $eventId = 'evt_paid_1'): array
    {
        return ['data' => ['id' => $eventId, 'type' => 'event', 'attributes' => [
            'type' => 'checkout_session.payment.paid',
            'data' => ['id' => 'cs_test_1', 'type' => 'checkout_session', 'attributes' => [
                'reference_number' => Payment::first()->checkout_reference,
                'payments' => [['id' => 'pay_test_1', 'attributes' => ['status' => 'paid']]],
            ]],
        ]]];
    }

    // ─── IP-2: checkout session ─────────────────────────────────

    public function test_online_checkout_creates_paymongo_session_and_redirects_buyer(): void
    {
        $payment = $this->checkoutOnline();

        $this->assertSame('pending', $payment->status);
        $this->assertSame('cs_test_1', $payment->checkout_session_id);
        $this->assertSame('pi_test_1', $payment->payment_intent_id);
        $this->assertEquals(500, $payment->amount);

        $order = Order::firstOrFail();
        $this->assertSame('awaiting_payment', $order->payment_status);
        $this->assertSame($payment->checkout_reference, $order->checkout_reference);
        $this->assertSame(8, $this->book->fresh()->stock_quantity); // reserved

        Http::assertSent(function (HttpRequest $request) use ($payment) {
            return $request->url() === 'https://api.paymongo.com/v1/checkout_sessions'
                && $request->hasHeader('Authorization', 'Basic ' . base64_encode('sk_test_123:'))
                && $request['data']['attributes']['reference_number'] === $payment->checkout_reference
                && $request['data']['attributes']['line_items'][0]['amount'] === 25000 // centavos
                && $request['data']['attributes']['line_items'][0]['quantity'] === 2;
        });
    }

    public function test_online_payment_is_refused_when_paymongo_is_not_configured(): void
    {
        config(['services.paymongo.secret_key' => null]);

        $this->actingAs($this->buyer)
            ->withSession(['cart' => [$this->book->id => 1]])
            ->post('/cart/checkout', ['shipping_address' => '123 Katipunan Ave, Quezon City', 'payment_method' => 'paymongo'])
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
    }

    public function test_paymongo_error_cancels_orders_and_returns_stock(): void
    {
        Http::fake(['api.paymongo.com/*' => Http::response(['errors' => [['detail' => 'boom']]], 500)]);

        $this->actingAs($this->buyer)
            ->withSession(['cart' => [$this->book->id => 3]])
            ->post('/cart/checkout', ['shipping_address' => '123 Katipunan Ave, Quezon City', 'payment_method' => 'paymongo'])
            ->assertRedirect(route('cart.checkout'))
            ->assertSessionHas('error');

        $this->assertSame('cancelled', Order::firstOrFail()->status);
        $this->assertSame(10, $this->book->fresh()->stock_quantity);
        $this->assertSame(0, Payment::count());
    }

    public function test_api_order_with_paymongo_returns_checkout_url(): void
    {
        $this->fakeSessionCreated();
        Sanctum::actingAs($this->buyer);

        $this->postJson('/api/v1/orders', [
            'items' => [['book_id' => $this->book->id, 'quantity' => 1]],
            'shipping_address' => '123 Katipunan Ave, Quezon City',
            'payment_method' => 'paymongo',
        ])->assertCreated()
            ->assertJsonPath('payment.checkout_url', 'https://checkout.paymongo.com/cs_test_1')
            ->assertJsonPath('data.0.payment_status', 'awaiting_payment');
    }

    // ─── IP-3: webhook ──────────────────────────────────────────

    public function test_signed_paid_webhook_marks_orders_paid_and_emails_buyer_and_seller(): void
    {
        $this->checkoutOnline();

        $this->sendWebhook($this->paidEvent())->assertOk()->assertJson(['status' => 'processed']);

        $payment = Payment::first();
        $this->assertSame('paid', $payment->status);
        $this->assertSame('pay_test_1', $payment->provider_payment_id);
        $order = Order::first();
        $this->assertSame('paid', $order->payment_status);
        $this->assertNotNull($order->paid_at);
        $this->assertSame('processed', WebhookEvent::where('event_id', 'evt_paid_1')->value('status'));

        Notification::assertSentTo($this->buyer, OrderPaidNotification::class);
        Notification::assertSentTo($this->seller, OrderPaidNotification::class);
    }

    public function test_webhook_with_bad_signature_is_rejected(): void
    {
        $this->checkoutOnline();

        $this->sendWebhook($this->paidEvent(), 't=' . time() . ',te=' . str_repeat('0', 64) . ',li=')->assertStatus(401);

        $this->assertSame('pending', Payment::first()->status);
        $this->assertSame('awaiting_payment', Order::first()->payment_status);
        $this->assertFalse(WebhookEvent::first()->signature_valid);
    }

    public function test_webhook_without_signature_is_rejected(): void
    {
        $this->checkoutOnline();
        $raw = json_encode($this->paidEvent());

        $this->call('POST', '/webhooks/paymongo', [], [], [], ['CONTENT_TYPE' => 'application/json'], $raw)->assertStatus(401);
        $this->assertSame('pending', Payment::first()->status);
    }

    public function test_repeated_webhook_is_processed_once(): void
    {
        $this->checkoutOnline();

        $this->sendWebhook($this->paidEvent())->assertOk();
        $this->sendWebhook($this->paidEvent())->assertOk()->assertJson(['duplicate' => true]);

        $this->assertSame(1, WebhookEvent::where('event_id', 'evt_paid_1')->count());
        Notification::assertSentToTimes($this->buyer, OrderPaidNotification::class, 1);
    }

    public function test_failed_payment_webhook_marks_payment_failed(): void
    {
        $this->checkoutOnline();

        $this->sendWebhook(['data' => ['id' => 'evt_fail_1', 'type' => 'event', 'attributes' => [
            'type' => 'payment.failed',
            'data' => ['id' => 'pay_x', 'type' => 'payment', 'attributes' => [
                'status' => 'failed', 'payment_intent_id' => 'pi_test_1', 'failed_message' => 'Card declined',
            ]],
        ]]])->assertOk();

        $payment = Payment::first();
        $this->assertSame('failed', $payment->status);
        $this->assertSame('Card declined', $payment->failure_reason);
        $this->assertSame('failed', Order::first()->payment_status);
        $this->assertSame('pending', Order::first()->status); // the buyer can still retry
    }

    public function test_signature_accepts_both_documented_header_forms(): void
    {
        $raw = '{"data":{"id":"evt_1"}}';

        $this->assertTrue(WebhookSignature::isValid($raw, WebhookSignature::sign($raw, 'k'), 'k'));
        $this->assertTrue(WebhookSignature::isValid($raw, WebhookSignature::sign($raw, 'k', live: true), 'k'));
        $this->assertTrue(WebhookSignature::isValid($raw, hash_hmac('sha256', $raw, 'k'), 'k'));
        $this->assertFalse(WebhookSignature::isValid($raw, WebhookSignature::sign($raw, 'wrong'), 'k'));
        $this->assertFalse(WebhookSignature::isValid($raw . ' ', WebhookSignature::sign($raw, 'k'), 'k'));
        $this->assertFalse(WebhookSignature::isValid($raw, WebhookSignature::sign($raw, 'k'), null));
    }

    // ─── Return page ────────────────────────────────────────────

    public function test_return_page_confirms_payment_with_paymongo_not_the_redirect(): void
    {
        $payment = $this->checkoutOnline();

        Http::fake(['api.paymongo.com/v1/checkout_sessions/cs_test_1' => Http::sequence()
            // 1st check: PayMongo shows no payment yet.
            ->push(['data' => ['id' => 'cs_test_1', 'attributes' => ['status' => 'active', 'payments' => []]]])
            // 2nd check: PayMongo shows the payment.
            ->push(['data' => ['id' => 'cs_test_1', 'attributes' => [
                'status' => 'active', 'payments' => [['id' => 'pay_2', 'attributes' => ['status' => 'paid']]],
            ]]]),
        ]);

        // Coming back alone is not proof of payment.
        $this->actingAs($this->buyer)->get(route('payments.return', $payment->checkout_reference))->assertOk()->assertSee('Confirming your payment');
        $this->assertSame('pending', $payment->fresh()->status);

        // PayMongo's own record shows the payment: now it counts.
        $this->actingAs($this->buyer)->get(route('payments.return', $payment->checkout_reference))->assertOk()->assertSee('Payment received');
        $this->assertSame('paid', $payment->fresh()->status);
    }

    public function test_other_users_cannot_open_a_payment_page(): void
    {
        $payment = $this->checkoutOnline();
        $stranger = User::factory()->create(['role' => 'customer', 'email_verified_at' => now()]);

        $this->actingAs($stranger)->get(route('payments.return', $payment->checkout_reference))->assertForbidden();
    }

    // ─── Order rules ────────────────────────────────────────────

    public function test_seller_cannot_ship_an_unpaid_online_order(): void
    {
        $this->checkoutOnline();
        $order = Order::first();

        $this->actingAs($this->seller)->patch("/orders/{$order->id}/status", ['status' => 'shipped'])->assertSessionHas('error');
        $this->assertSame('pending', $order->fresh()->status);

        $this->sendWebhook($this->paidEvent())->assertOk();
        $this->actingAs($this->seller)->patch("/orders/{$order->id}/status", ['status' => 'shipped'])->assertSessionHas('success');
        $this->assertSame('shipped', $order->fresh()->status);
    }

    // ─── Scheduler: expire unpaid orders ────────────────────────

    public function test_scheduler_expires_unpaid_orders_and_returns_stock(): void
    {
        $this->checkoutOnline();
        Http::fake(['api.paymongo.com/v1/checkout_sessions/cs_test_1/expire' => Http::response(['data' => ['id' => 'cs_test_1', 'attributes' => ['status' => 'expired']]])]);

        $this->travel(61)->minutes();
        $this->artisan('payments:expire-unpaid')->assertSuccessful();

        $this->assertSame('expired', Payment::first()->status);
        $order = Order::first();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('expired', $order->payment_status);
        $this->assertSame(10, $this->book->fresh()->stock_quantity);
        Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/checkout_sessions/cs_test_1/expire'));
    }

    public function test_scheduler_leaves_recent_and_paid_payments_alone(): void
    {
        $this->checkoutOnline();

        $this->travel(30)->minutes();
        $this->artisan('payments:expire-unpaid')->assertSuccessful();
        $this->assertSame('pending', Payment::first()->status);

        $this->sendWebhook($this->paidEvent())->assertOk();
        $this->travel(60)->minutes();
        $this->artisan('payments:expire-unpaid')->assertSuccessful();
        $this->assertSame('paid', Payment::first()->status);
        $this->assertSame(8, $this->book->fresh()->stock_quantity);
    }

    public function test_scheduler_is_registered(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('payments:expire-unpaid')->assertSuccessful();
    }
}
