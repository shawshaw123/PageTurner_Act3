<?php

namespace App\Integrations\RestApi\Controllers;

use App\Exceptions\CheckoutException;
use App\Http\Controllers\Controller;
use App\Integrations\PayMongo\PaymentException;
use App\Integrations\PayMongo\PaymentService;
use App\Integrations\RestApi\Resources\OrderResource;
use App\Models\Order;
use App\Notifications\OrderStatusChangedNotification;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/** All order endpoints require a token. */
class OrderController extends Controller
{
    private const RELATIONS = ['user:id,name', 'seller:id,name,store_name', 'orderItems.book:id,title'];

    /** GET /api/v1/orders — buyers see their orders, sellers the orders they fulfil, admins all. */
    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', Rule::in(Order::STATUSES)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $user = $request->user();

        $orders = Order::with(self::RELATIONS)
            ->when($user->isSeller(), fn ($q) => $q->where('seller_id', $user->id))
            ->when(!$user->isSeller() && !$user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return OrderResource::collection($orders);
    }

    /** GET /api/v1/orders/{order} — the buyer, the order's seller, or an admin. */
    public function show(Order $order): OrderResource
    {
        Gate::authorize('view', $order);

        return new OrderResource($order->load(self::RELATIONS));
    }

    /**
     * POST /api/v1/orders — place an order (buyers). Items from several sellers
     * become one order per seller, as on the website.
     */
    public function store(Request $request, CheckoutService $checkout, PaymentService $payments): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.book_id' => ['required', 'integer', 'distinct', 'exists:books,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'shipping_address' => ['required', 'string', 'min:10', 'max:255'],
            'payment_method' => ['required', Rule::in(Order::PAYMENT_METHODS)],
        ]);

        if ($data['payment_method'] === 'paymongo' && !$payments->isConfigured()) {
            return response()->json(['message' => 'Online payment is not available right now. Use cash_on_delivery.'], 422);
        }

        $items = collect($data['items'])->mapWithKeys(fn ($item) => [$item['book_id'] => $item['quantity']])->all();

        try {
            $orders = $checkout->placeOrders($request->user(), $items, $data['shipping_address'], $data['payment_method']);
        } catch (CheckoutException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        // IP-2: for online payment, the client sends the buyer to payment.checkout_url.
        $payment = null;
        if ($data['payment_method'] === 'paymongo') {
            try {
                $payment = $payments->startCheckout($orders, $request->user());
            } catch (PaymentException $e) {
                $payments->cancelOrders($orders, 'failed', 'Online payment could not be started.');
                return response()->json(['message' => $e->getMessage()], 502);
            }
        }

        return OrderResource::collection($orders->each->load(self::RELATIONS))
            ->additional($payment ? ['payment' => [
                'checkout_reference' => $payment->checkout_reference,
                'status' => $payment->status,
                'amount' => (float) $payment->amount,
                'checkout_url' => $payment->checkout_url,
            ]] : [])
            ->response()->setStatusCode(201);
    }

    /** PATCH /api/v1/orders/{order}/status — the order's seller or an admin. */
    public function updateStatus(Request $request, Order $order): OrderResource|JsonResponse
    {
        Gate::authorize('updateStatus', $order);

        $data = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($error = $order->statusChangeError($data['status'])) {
            return response()->json(['message' => $error, 'errors' => ['status' => [$error]]], 422);
        }

        $oldStatus = $order->status;
        $order->update(['status' => $data['status'], 'notes' => $data['notes'] ?? $order->notes] + $order->paymentChangesFor($data['status']));

        if ($oldStatus !== $order->status) {
            try {
                $order->user->notify(new OrderStatusChangedNotification($order, $oldStatus));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return new OrderResource($order->load(self::RELATIONS));
    }
}
