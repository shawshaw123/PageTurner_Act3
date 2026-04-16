<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Key statistics
        $stats = [
            'total_users' => User::where('role', 'customer')->count(),
            'total_books' => Book::count(),
            'total_categories' => Category::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
        ];

        // Order status summary
        $orderStatuses = [
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        // Recent orders (latest 10)
        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Recent reviews (latest 5)
        $recentReviews = Review::with(['user', 'book'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Low stock books (less than 5)
        $lowStockBooks = Book::where('stock_quantity', '<', 5)
            ->orderBy('stock_quantity', 'asc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'orderStatuses',
            'recentOrders',
            'recentReviews',
            'lowStockBooks'
        ));
    }
}
