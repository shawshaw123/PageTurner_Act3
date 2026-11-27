<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Book;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusChangedNotification;
use App\Notifications\NewOrderAdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class OrderController extends Controller
{
    /**
     * Display user's orders or all orders for admin
     */
    public function index()
    {
        $query = Order::with(['user', 'seller:id,name,store_name', 'orderItems.book'])->orderBy('created_at', 'desc');
        $user = auth()->user();

        if ($user->isSeller()) {
            // Sellers see the orders they have to fulfil.
            $query->where('seller_id', $user->id);
        } elseif (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $orders = $query->paginate(10);
        
        return view('orders.index', compact('orders'));
    }

    /**
     * Store a new order (legacy single book purchase)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $book = Book::findOrFail($validated['book_id']);

        if (!$book->isVisible()) {
            abort(404);
        }

        if ($book->seller_id !== null && $book->seller_id === auth()->id()) {
            return back()->with('error', 'You cannot buy your own listing.');
        }

        if ($book->stock_quantity < $validated['quantity']) {
            return back()->with('error', 'Sorry, only ' . $book->stock_quantity . ' copies available.');
        }

        $totalAmount = $book->price * $validated['quantity'];

        $order = Order::create([
            'user_id' => auth()->id(),
            'seller_id' => $book->seller_id,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'payment_method' => 'cash_on_delivery',
            'shipping_address' => auth()->user()->name,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $book->id,
            'quantity' => $validated['quantity'],
            'price' => $book->price,
        ]);

        // Update stock
        $book->decrement('stock_quantity', $validated['quantity']);

        // Send notifications
        $order->load('user');
        auth()->user()->notify(new OrderPlacedNotification($order));

        // Notify admins and the seller
        $recipients = User::where('role', 'admin')->get();
        if ($book->seller) {
            $recipients = $recipients->concat([$book->seller])->unique('id');
        }
        Notification::send($recipients, new NewOrderAdminNotification($order));

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order placed successfully! Order #' . $order->id);
    }

    /**
     * Display order details
     */
    public function show(Order $order)
    {
        // Ensure user can only view their own orders
        \Illuminate\Support\Facades\Gate::authorize('view', $order);

        $order->load(['user', 'seller:id,name,store_name', 'orderItems.book']);

        return view('orders.show', compact('order'));
    }

    /**
     * Update order status (admin only)
     */
    public function updateStatus(Request $request, Order $order)
    {
        // Admins, or the seller who owns this order
        \Illuminate\Support\Facades\Gate::authorize('updateStatus', $order);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', Order::STATUSES),
            'notes' => 'nullable|string|max:500',
        ]);

        if ($error = $order->statusChangeError($validated['status'])) {
            return back()->with('error', $error);
        }

        $oldStatus = $order->status;
        $order->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $order->notes,
        ] + $order->paymentChangesFor($validated['status']));

        // Send notification to order owner
        if ($oldStatus !== $order->status) {
            $order->user->notify(new OrderStatusChangedNotification($order, $oldStatus));
        }

        // Log status change
        \Log::info("Order #{$order->id} status changed from {$oldStatus} to {$validated['status']} by " . auth()->user()->role . " " . auth()->user()->email);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order status updated successfully!');
    }

    /**
     * Cancel order (customer only, only for pending orders)
     */
    public function cancel(Order $order)
    {
        // Only order owner can cancel
        \Illuminate\Support\Facades\Gate::authorize('cancel', $order);

        // Only pending orders can be cancelled
        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be cancelled.');
        }

        // Restore stock
        foreach ($order->orderItems as $item) {
            $item->book->increment('stock_quantity', $item->quantity);
        }

        $order->update(['status' => 'cancelled']);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order cancelled successfully. Stock has been restored.');
    }

    /**
     * Track order (public accessible with order number)
     */
    public function track(Request $request)
    {
        $request->validate([
            'order_number' => 'required|integer|exists:orders,id',
        ]);

        $order = Order::with(['user', 'orderItems.book'])
            ->findOrFail($request->order_number);

        // Allow tracking if user owns the order or is admin
        \Illuminate\Support\Facades\Gate::authorize('view', $order);

        return view('orders.track', compact('order'));
    }

    /**
     * Get order statistics (admin only)
     */
    public function statistics()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
            'recent_orders' => Order::with('user')->orderBy('created_at', 'desc')->take(5)->get(),
        ];

        return view('orders.statistics', compact('stats'));
    }
}
