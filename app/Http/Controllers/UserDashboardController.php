<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Order summary
        $orderSummary = [
            'total' => $user->orders()->count(),
            'pending' => $user->orders()->where('status', 'pending')->count(),
            'processing' => $user->orders()->where('status', 'processing')->count(),
            'completed' => $user->orders()->where('status', 'completed')->count(),
            'total_spent' => $user->orders()->where('status', 'completed')->sum('total_amount'),
        ];

        // Recent orders (latest 5)
        $recentOrders = $user->orders()
            ->with('orderItems.book')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Recently purchased books (from completed orders)
        $recentBooks = OrderItem::whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'completed');
            })
            ->with('book')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get()
            ->pluck('book')
            ->unique('id');

        // Review activity
        $reviews = $user->reviews()
            ->with('book')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $reviewCount = $user->reviews()->count();

        // Account status
        $accountStatus = [
            'email_verified' => $user->hasVerifiedEmail(),
            'two_factor_enabled' => $user->hasTwoFactorEnabled(),
        ];

        return view('dashboard', compact(
            'user',
            'orderSummary',
            'recentOrders',
            'recentBooks',
            'reviews',
            'reviewCount',
            'accountStatus'
        ));
    }
}
