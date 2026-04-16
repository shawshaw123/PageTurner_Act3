<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display user's orders or all orders for admin
     */
    public function index()
    {
        $query = Order::with(['user', 'orderItems.book'])->orderBy('created_at', 'desc');
        
        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
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
        
        if ($book->stock_quantity < $validated['quantity']) {
            return back()->with('error', 'Sorry, only ' . $book->stock_quantity . ' copies available.');
        }

        $totalAmount = $book->price * $validated['quantity'];

        $order = Order::create([
            'user_id' => auth()->id(),
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

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order placed successfully! Order #' . $order->id);
    }

    /**
     * Display order details
     */
    public function show(Order $order)
    {
        // Ensure user can only view their own orders
        if ($order->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $order->load(['user', 'orderItems.book']);
        
        return view('orders.show', compact('order'));
    }

    /**
     * Update order status (admin only)
     */
    public function updateStatus(Request $request, Order $order)
    {
        // Only admins can update order status
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldStatus = $order->status;
        $order->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Log status change
        \Log::info("Order #{$order->id} status changed from {$oldStatus} to {$validated['status']} by admin " . auth()->user()->email);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order status updated successfully!');
    }

    /**
     * Cancel order (customer only, only for pending orders)
     */
    public function cancel(Order $order)
    {
        // Only order owner can cancel
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

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
        if ($order->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

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
            'total_revenue' => Order::where('status', 'delivered')->sum('total_amount'),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
            'recent_orders' => Order::with('user')->orderBy('created_at', 'desc')->take(5)->get(),
        ];

        return view('orders.statistics', compact('stats'));
    }
}
