<?php

namespace App\Integrations\PayMongo;

use App\Models\Book;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\OrderPaidNotification;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * IP-2: PayMongo Checkout Sessions (create a session, map its status to our orders).
 *
 * One checkout (one or more orders, one per seller, sharing a checkout_reference)
 * becomes one Payment and one PayMongo Checkout Session. The buyer pays on
 * PayMongo's hosted page; the result comes back through the webhook (IP-3),
 * or is expired by the scheduler when the payment window closes.
 */
class PaymentService
{
    public function isConfigured(): bool
    {
        return filled(config('services.paymongo.secret_key'));
    }

    /**
     * Create the Checkout Session for these orders and return the Payment
     * (its checkout_url is where the buyer must be sent).
     *
     * @param  Collection<int, Order>  $orders
     *
     * @throws PaymentException
     */
    public function startCheckout(Collection $orders, User $buyer): Payment
    {
        if (!$this->isConfigured()) {
            throw new PaymentException('Online payment is not available right now. Please choose cash on delivery.');
        }

        $orders->each->loadMissing(['orderItems.book', 'seller']);
        $reference = $orders->first()->checkout_reference;

        $payment = Payment::create([
            'user_id' => $buyer->id,
            'checkout_reference' => $reference,
            'provider' => 'paymongo',
            'amount' => $orders->sum('total_amount'),
            'currency' => 'PHP',
            'status' => Payment::PENDING,
        ]);

        $lineItems = $orders->flatMap(fn (Order $order) => $order->orderItems->map(fn ($item) => [
            'name' => mb_strimwidth($item->book?->title ?? 'Book', 0, 100, '…'),
            'description' => 'Sold by ' . ($order->seller?->store_display_name ?? config('app.name')) . " (order #{$order->id})",
            'amount' => (int) round($item->price * 100), // centavos
            'currency' => 'PHP',
            'quantity' => (int) $item->quantity,
        ]))->values()->all();

        $response = $this->send(fn (PendingRequest $http) => $http->post('/checkout_sessions', [
            'data' => ['attributes' => [
                'line_items' => $lineItems,
                'payment_method_types' => array_values(config('services.paymongo.payment_methods')),
                'reference_number' => $reference,
                'description' => config('app.name') . ' order ' . $orders->pluck('id')->map(fn ($id) => "#{$id}")->implode(', '),
                'success_url' => route('payments.return', $reference),
                'cancel_url' => route('payments.cancelled', $reference),
                'send_email_receipt' => false,
                'show_description' => true,
                'show_line_items' => true,
                'metadata' => ['checkout_reference' => $reference, 'payment_id' => (string) $payment->id],
            ]],
        ]));

        if (!$response->successful() || !$response->json('data.attributes.checkout_url')) {
            $payment->delete();
            Log::error('PayMongo checkout session failed', ['reference' => $reference, 'status' => $response->status(), 'body' => mb_substr($response->body(), 0, 1000)]);
            throw new PaymentException('PayMongo could not start the payment. Please try again or choose cash on delivery.');
        }

        $payment->update([
            'checkout_session_id' => $response->json('data.id'),
            'payment_intent_id' => $response->json('data.attributes.payment_intent.id'),
            'checkout_url' => $response->json('data.attributes.checkout_url'),
        ]);

        return $payment;
    }

    /**
     * Ask PayMongo for the session's current state and apply it. Used when the
     * buyer comes back from the payment page, in case the webhook is late or
     * cannot reach this server (for example during local development).
     */
    public function syncFromProvider(Payment $payment): Payment
    {
        if (!$payment->isOpen() || !$payment->checkout_session_id || !$this->isConfigured()) {
            return $payment;
        }

        try {
            $response = $this->send(fn (PendingRequest $http) => $http->get("/checkout_sessions/{$payment->checkout_session_id}"));
        } catch (PaymentException) {
            return $payment;
        }

        if ($response->successful()) {
            $paid = collect($response->json('data.attributes.payments', []))
                ->first(fn ($p) => ($p['attributes']['status'] ?? null) === 'paid');

            if ($paid) {
                $this->markPaid($payment, $paid['id'] ?? null);
            }
        }

        return $payment->fresh();
    }

    /** Payment confirmed: the payment and all its orders become "paid"; buyer and sellers are emailed. */
    public function markPaid(Payment $payment, ?string $providerPaymentId = null): void
    {
        $orders = DB::transaction(function () use ($payment, $providerPaymentId) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if ($payment->status === Payment::PAID) {
                return collect(); // already processed (webhooks can arrive more than once)
            }

            if ($payment->status === Payment::EXPIRED) {
                // Money arrived after we released the stock: keep a record for the admin to resolve.
                Log::critical('PayMongo payment received after the order expired', ['payment_id' => $payment->id, 'reference' => $payment->checkout_reference]);
            }

            $payment->update([
                'status' => Payment::PAID,
                'paid_at' => now(),
                'provider_payment_id' => $providerPaymentId ?? $payment->provider_payment_id,
                'failure_reason' => null,
            ]);

            $orders = $payment->orders()->get();
            foreach ($orders as $order) {
                $order->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'notes' => $order->status === 'cancelled'
                        ? trim($order->notes . ' Paid after the payment window closed: refund the buyer or restore the order.')
                        : $order->notes,
                ]);
            }

            return $orders;
        });

        foreach ($orders as $order) {
            $order->load(['user', 'seller']);
            $recipients = collect([$order->user, $order->seller])->filter()->unique('id');
            $this->notify($recipients, new OrderPaidNotification($order));
        }
    }

    /** A payment attempt failed. The buyer can try again on the same PayMongo page until the window closes. */
    public function markFailed(Payment $payment, ?string $reason = null): void
    {
        DB::transaction(function () use ($payment, $reason) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if (!$payment->isOpen()) {
                return; // a paid or expired payment is final
            }

            $payment->update(['status' => Payment::FAILED, 'failed_at' => now(), 'failure_reason' => $reason ? mb_substr($reason, 0, 250) : null]);
            $payment->orders()->update(['payment_status' => 'failed']);
        });
    }

    /**
     * Close an unpaid payment: expire the PayMongo session, cancel the orders and
     * return their stock. Returns false when PayMongo says a payment is under way
     * or already made (the webhook will settle it instead).
     */
    public function expire(Payment $payment): bool
    {
        if (!$payment->isOpen()) {
            return false;
        }

        if ($payment->checkout_session_id && $this->isConfigured()) {
            $response = $this->send(fn (PendingRequest $http) => $http->post("/checkout_sessions/{$payment->checkout_session_id}/expire"));

            if ($response->status() === 400) {
                // Already expired, paid, or a payment is in progress: check which.
                $payment = $this->syncFromProvider($payment);
                if (!$payment->isOpen()) {
                    return false;
                }
                $status = $this->send(fn (PendingRequest $http) => $http->get("/checkout_sessions/{$payment->checkout_session_id}"))
                    ->json('data.attributes.status');
                if ($status !== 'expired') {
                    return false; // payment in progress; try again on the next run
                }
            } elseif (!$response->successful() && $response->status() !== 404) {
                throw new PaymentException("PayMongo could not expire session {$payment->checkout_session_id} (HTTP {$response->status()}).");
            }
        }

        $cancelled = DB::transaction(function () use ($payment) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();
            if (!$payment->isOpen()) {
                return collect();
            }

            $payment->update(['status' => Payment::EXPIRED, 'expired_at' => now()]);

            return $this->cancelOrders($payment->orders()->with('orderItems')->get(), 'expired', 'Payment window closed without payment.');
        });

        foreach ($cancelled as $order) {
            $order->load('user');
            $this->notify(collect([$order->user]), new OrderStatusChangedNotification($order, 'pending'));
        }

        return true;
    }

    /**
     * Cancel orders that were never paid and put their books back in stock.
     * Used when a payment expires, and when PayMongo could not even start the payment.
     *
     * @param  Collection<int, Order>  $orders
     * @return Collection<int, Order>  the orders that were cancelled
     */
    public function cancelOrders(Collection $orders, string $paymentStatus, string $note): Collection
    {
        return DB::transaction(function () use ($orders, $paymentStatus, $note) {
            $cancelled = collect();

            foreach ($orders as $order) {
                $order = Order::whereKey($order->id)->lockForUpdate()->with('orderItems')->first();
                if ($order->status === 'cancelled' || $order->payment_status === 'paid') {
                    continue;
                }

                foreach ($order->orderItems as $item) {
                    Book::withoutGlobalScopes()->whereKey($item->book_id)->increment('stock_quantity', $item->quantity);
                }

                $order->update(['status' => 'cancelled', 'payment_status' => $paymentStatus, 'notes' => trim($order->notes . ' ' . $note)]);
                $cancelled->push($order);
            }

            return $cancelled;
        });
    }

    /** Map a PayMongo resource to our Payment, by session id, payment intent or our reference. */
    public function findPayment(?string $checkoutSessionId, ?string $paymentIntentId, ?string $reference): ?Payment
    {
        return Payment::query()
            ->where(function ($q) use ($checkoutSessionId, $paymentIntentId, $reference) {
                $q->whereRaw('1 = 0');
                if ($checkoutSessionId) {
                    $q->orWhere('checkout_session_id', $checkoutSessionId);
                }
                if ($paymentIntentId) {
                    $q->orWhere('payment_intent_id', $paymentIntentId);
                }
                if ($reference) {
                    $q->orWhere('checkout_reference', $reference);
                }
            })
            ->first();
    }

    /** @param callable(PendingRequest): Response $call */
    private function send(callable $call): Response
    {
        $http = Http::baseUrl(rtrim(config('services.paymongo.url'), '/'))
            ->withBasicAuth((string) config('services.paymongo.secret_key'), '')
            ->acceptJson()
            ->asJson()
            ->timeout(15);

        try {
            return $call($http);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('PayMongo unreachable', ['error' => $e->getMessage()]);
            throw new PaymentException('PayMongo could not be reached. Please try again in a moment.');
        }
    }

    private function notify(Collection $recipients, $notification): void
    {
        try {
            Notification::send($recipients, $notification);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
