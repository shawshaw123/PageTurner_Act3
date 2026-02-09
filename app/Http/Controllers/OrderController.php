<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $query = Order::with('orderItems.book')->orderBy('created_at', 'desc');
        
        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        $orders = $query->paginate(10);
        
        return view('orders.index', compact('orders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $book = \App\Models\Book::findOrFail($validated['book_id']);
        $totalAmount = $book->price * $validated['quantity'];

        $order = Order::create([
            'user_id' => auth()->id(),
            'total_amount' => $totalAmount,
            'status' => 'pending', // In a real app, this might be 'completed' after payment
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $book->id,
            'quantity' => $validated['quantity'],
            'unit_price' => $book->price,
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order placed successfully! Please note reviews are only allowed after order is completed.');
    }

    public function show(Order $order)
    {
        // Ensure user can only view their own orders
        if ($order->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $order->load(['user', 'orderItems.book']);
        
        return view('orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        // Only admins can update order status
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        $order->update($validated);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order status updated successfully!');
    }
}
