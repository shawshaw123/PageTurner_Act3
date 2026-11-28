<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Integrations\PayMongo\PaymentException;
use App\Integrations\PayMongo\PaymentService;
use App\Models\Book;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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

        if (!empty($cart)) {
            $bookIds = array_keys($cart);
            $books = Book::with(['category:id,name'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->whereIn('id', $bookIds)
                ->get()
                ->keyBy('id');

            foreach ($cart as $bookId => $quantity) {
                if (isset($books[$bookId])) {
                    $book = $books[$bookId];
                    if ($book->stock_quantity >= $quantity) {
                        $subtotal = $book->price * $quantity;
                        $cartItems[] = [
                            'book' => $book,
                            'quantity' => $quantity,
                            'subtotal' => $subtotal
                        ];
                        $total += $subtotal;
                    }
                }
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

        if (!$book->isVisible()) {
            abort(404);
        }

        if (Auth::user()->isSeller()) {
            return back()->with('error', 'Seller accounts cannot place orders. Please use a buyer account to shop.');
        }

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

        if ($request->boolean('buy_now')) {
            return redirect()->route('cart.checkout');
        }

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

        $bookIds = array_keys($cart);
        $books = Book::with(['category:id,name'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->whereIn('id', $bookIds)
            ->get()
            ->keyBy('id');

        // Validate stock and calculate total
        foreach ($cart as $bookId => $quantity) {
            if (!isset($books[$bookId])) {
                 continue;
            }
            
            $book = $books[$bookId];
            if ($book->stock_quantity < $quantity) {
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
    public function processOrder(Request $request, CheckoutService $checkout, PaymentService $payments)
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
            'payment_method' => ['required', Rule::in(Order::PAYMENT_METHODS)],
        ]);

        if ($request->payment_method === 'paymongo' && !$payments->isConfigured()) {
            return back()->withInput()->with('error', 'Online payment is not available right now. Please choose cash on delivery.');
        }

        // One order per seller; see CheckoutService.
        try {
            $orders = $checkout->placeOrders(Auth::user(), $cart, $request->shipping_address, $request->payment_method);
        } catch (CheckoutException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('cart.checkout')->with('error', 'There was an error processing your order. Please try again.');
        }

        // IP-2: online payment. Send the buyer to PayMongo's payment page.
        if ($request->payment_method === 'paymongo') {
            try {
                $payment = $payments->startCheckout($orders, Auth::user());
            } catch (PaymentException $e) {
                // Nothing was charged: release the stock and keep the cart so the buyer can try again.
                $payments->cancelOrders($orders, 'failed', 'Online payment could not be started.');
                return redirect()->route('cart.checkout')->with('error', $e->getMessage());
            }

            session()->forget('cart');

            return redirect()->away($payment->checkout_url);
        }

        session()->forget('cart');

        if ($orders->count() === 1) {
            return redirect()->route('orders.show', $orders->first())
                ->with('success', 'Order placed successfully! Order #' . $orders->first()->id);
        }

        return redirect()->route('orders.index')
            ->with('success', 'Your checkout was split into ' . $orders->count() . ' orders, one per seller: #' . $orders->pluck('id')->implode(', #'));
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
