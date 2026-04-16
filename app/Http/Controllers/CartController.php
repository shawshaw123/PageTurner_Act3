<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\NewOrderAdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Display the shopping cart
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        $cartItems = [];
        $total = 0;

        foreach ($cart as $bookId => $quantity) {
            $book = Book::find($bookId);
            if ($book && $book->stock_quantity >= $quantity) {
                $subtotal = $book->price * $quantity;
                $cartItems[] = [
                    'book' => $book,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal
                ];
                $total += $subtotal;
            }
        }

        return view('cart.index', compact('cartItems', 'total'));
    }

    /**
     * Add a book to the cart
     */
    public function add(Request $request, $bookId)
    {
        $request->validate([
            'quantity' => 'nullable|integer|min:1'
        ]);

        $quantity = (int) $request->input('quantity', 1);
        
        $book = Book::findOrFail($bookId);
        
        if ($book->stock_quantity < $quantity) {
            return back()->with('error', 'Sorry, only ' . $book->stock_quantity . ' copies available in stock.');
        }

        $cart = session()->get('cart', []);
        
        if (isset($cart[$bookId])) {
            $newQuantity = $cart[$bookId] + $quantity;
            if ($newQuantity > $book->stock_quantity) {
                return back()->with('error', 'Sorry, only ' . $book->stock_quantity . ' copies available in stock.');
            }
            $cart[$bookId] = $newQuantity;
        } else {
            $cart[$bookId] = $quantity;
        }

        session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', '"' . $book->title . '" added to cart!');
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $bookId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0'
        ]);

        $book = Book::findOrFail($bookId);
        $cart = session()->get('cart', []);

        if ($request->quantity == 0) {
            unset($cart[$bookId]);
        } else {
            if ($book->stock_quantity < $request->quantity) {
                return back()->with('error', 'Sorry, only ' . $book->stock_quantity . ' copies available in stock.');
            }
            $cart[$bookId] = (int) $request->quantity;
        }

        session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Cart updated successfully!');
    }

    /**
     * Remove item from cart
     */
    public function remove($bookId)
    {
        $cart = session()->get('cart', []);
        
        if (isset($cart[$bookId])) {
            unset($cart[$bookId]);
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Item removed from cart!');
    }

    /**
     * Clear entire cart
     */
    public function clear()
    {
        session()->forget('cart');
        return redirect()->route('cart.index')->with('success', 'Cart cleared!');
    }

    /**
     * Checkout - convert cart to order
     */
    public function checkout()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('info', 'Please login to checkout.');
        }

        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }

        $cartItems = [];
        $total = 0;

        // Validate stock and calculate total
        foreach ($cart as $bookId => $quantity) {
            $book = Book::find($bookId);
            if (!$book || $book->stock_quantity < $quantity) {
                return redirect()->route('cart.index')->with('error', 'Some items in your cart are no longer available. Please update your cart.');
            }
            
            $subtotal = $book->price * $quantity;
            $cartItems[] = [
                'book' => $book,
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
            $total += $subtotal;
        }

        return view('cart.checkout', compact('cartItems', 'total'));
    }

    /**
     * Process the order
     */
    public function processOrder(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('info', 'Please login to complete your order.');
        }

        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }

        $request->validate([
            'shipping_address' => 'required|string|min:10|max:255',
            'payment_method' => 'required|in:credit_card,paypal,cash_on_delivery'
        ]);

        try {
            // Create order
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_amount' => 0, // Will be calculated
                'status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'payment_method' => $request->payment_method,
            ]);

            $totalAmount = 0;

            // Create order items and update stock
            foreach ($cart as $bookId => $quantity) {
                $book = Book::findOrFail($bookId);
                
                if ($book->stock_quantity < $quantity) {
                    // Rollback order creation
                    $order->delete();
                    return redirect()->route('cart.index')->with('error', 'Some items are no longer in stock. Please update your cart.');
                }

                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $book->id,
                    'quantity' => $quantity,
                    'price' => $book->price,
                ]);

                $totalAmount += $book->price * $quantity;

                // Update book stock
                $book->decrement('stock_quantity', $quantity);
            }

            // Update order total
            $order->update(['total_amount' => $totalAmount]);

            // Clear cart
            session()->forget('cart');

            // Send notifications
            $order->load('user');
            Auth::user()->notify(new OrderPlacedNotification($order));

            // Notify all admins
            $admins = User::where('role', 'admin')->get();
            Notification::send($admins, new NewOrderAdminNotification($order));

            return redirect()->route('orders.show', $order)->with('success', 'Order placed successfully! Order #' . $order->id);

        } catch (\Exception $e) {
            return redirect()->route('cart.checkout')->with('error', 'There was an error processing your order. Please try again.');
        }
    }

    /**
     * Get cart count for navigation
     */
    public static function getCartCount()
    {
        $cart = session()->get('cart', []);
        return array_sum($cart);
    }

    /**
     * Get cart total for navigation
     */
    public static function getCartTotal()
    {
        $cart = session()->get('cart', []);
        $total = 0;

        foreach ($cart as $bookId => $quantity) {
            $book = Book::find($bookId);
            if ($book) {
                $total += $book->price * $quantity;
            }
        }

        return $total;
    }
}
