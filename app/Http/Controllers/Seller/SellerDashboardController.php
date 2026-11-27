<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use Illuminate\Http\Request;

class SellerDashboardController extends Controller
{
    /** Low-stock threshold shown on the dashboard. */
    private const LOW_STOCK = 5;

    public function index(Request $request)
    {
        $seller = $request->user();

        // Pending or suspended sellers only see their account status.
        if (!$seller->isApprovedSeller()) {
            return view('seller.dashboard', ['seller' => $seller, 'approved' => false]);
        }

        $orders = Order::where('seller_id', $seller->id);

        $stats = [
            'listings' => $seller->books()->count(),
            'low_stock' => $seller->books()->where('stock_quantity', '<=', self::LOW_STOCK)->count(),
            'pending_orders' => (clone $orders)->where('status', 'pending')->count(),
            'to_ship' => (clone $orders)->where('status', 'processing')->count(),
            'revenue' => (clone $orders)->where('status', 'completed')->sum('total_amount'),
        ];

        $recentOrders = (clone $orders)->with('user:id,name')->withCount('orderItems')
            ->latest()->take(5)->get();

        $lowStockBooks = $seller->books()->where('stock_quantity', '<=', self::LOW_STOCK)
            ->orderBy('stock_quantity')->take(5)->get(['id', 'title', 'stock_quantity']);

        return view('seller.dashboard', [
            'seller' => $seller,
            'approved' => true,
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'lowStockBooks' => $lowStockBooks,
        ]);
    }

    /** The seller's own listings, including inactive ones. */
    public function books(Request $request)
    {
        $books = Book::where('seller_id', $request->user()->id)
            ->with('category:id,name')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('isbn', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('seller.books', compact('books'));
    }
}
