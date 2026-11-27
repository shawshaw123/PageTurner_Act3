<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Notifications\NewOrderAdminNotification;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Turns a buyer's items into orders. Used by the website checkout and the REST API.
 *
 * Items from several sellers become one order per seller (so each seller can
 * fulfil and update their own order), all sharing one checkout_reference.
 */
class CheckoutService
{
    /**
     * @param  array<int, int>  $items  book id => quantity
     * @return Collection<int, Order>
     *
     * @throws CheckoutException when an item is unavailable or the buyer may not order
     */
    public function placeOrders(User $buyer, array $items, string $shippingAddress, string $paymentMethod): Collection
    {
        if ($buyer->isSeller()) {
            throw new CheckoutException('Seller accounts cannot place orders. Please use a buyer account to shop.');
        }

        $items = array_filter($items, fn ($quantity) => (int) $quantity > 0);
        if (empty($items)) {
            throw new CheckoutException('There are no items to order.');
        }

        $orders = DB::transaction(function () use ($buyer, $items, $shippingAddress, $paymentMethod) {
            $books = Book::whereIn('id', array_keys($items))->lockForUpdate()->get()->keyBy('id');

            if ($books->count() !== count($items)) {
                throw new CheckoutException('Some items are no longer available. Please update your cart.');
            }

            foreach ($items as $bookId => $quantity) {
                $book = $books[$bookId];
                if (!$book->isVisible() || $book->stock_quantity < $quantity) {
                    throw new CheckoutException("\"{$book->title}\" is no longer available in that quantity.");
                }
            }

            $reference = 'CHK-' . strtoupper(Str::random(10));
            $orders = collect();

            foreach ($books->groupBy(fn (Book $book) => $book->seller_id ?? 0) as $sellerBooks) {
                $order = Order::create([
                    'user_id' => $buyer->id,
                    'seller_id' => $sellerBooks->first()->seller_id,
                    'checkout_reference' => $reference,
                    'total_amount' => 0,
                    'status' => 'pending',
                    'shipping_address' => $shippingAddress,
                    'payment_method' => $paymentMethod,
                    // Online (PayMongo) orders wait for the payment; cash on delivery is paid later.
                    'payment_status' => $paymentMethod === 'paymongo' ? 'awaiting_payment' : 'unpaid',
                ]);

                $total = 0;
                foreach ($sellerBooks as $book) {
                    $quantity = (int) $items[$book->id];

                    OrderItem::create([
                        'order_id' => $order->id,
                        'book_id' => $book->id,
                        'quantity' => $quantity,
                        'price' => $book->price,
                    ]);

                    $total += $book->price * $quantity;
                    $book->decrement('stock_quantity', $quantity);
                }

                $order->update(['total_amount' => $total]);
                $orders->push($order);
            }

            return $orders;
        });

        $this->notify($buyer, $orders);

        return $orders;
    }

    /**
     * Notifications go out after the orders are saved, so a mail problem
     * cannot undo a successful checkout.
     */
    private function notify(User $buyer, Collection $orders): void
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($orders as $order) {
            $order->load(['user', 'seller']);
            try {
                $buyer->notify(new OrderPlacedNotification($order));
                $recipients = $order->seller ? $admins->concat([$order->seller])->unique('id') : $admins;
                Notification::send($recipients, new NewOrderAdminNotification($order));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
