<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/** Admin review of seller accounts: approve new sellers, suspend or reinstate them. */
class SellerController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $sellers = User::where('role', 'seller')
            ->when(in_array($status, ['pending', 'approved', 'suspended'], true), fn ($q) => $q->where('seller_status', $status))
            ->withCount(['books', 'sellerOrders'])
            ->orderByRaw("CASE seller_status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = User::where('role', 'seller')
            ->selectRaw('seller_status, count(*) as total')
            ->groupBy('seller_status')
            ->pluck('total', 'seller_status');

        return view('admin.sellers.index', compact('sellers', 'counts', 'status'));
    }

    public function approve(User $seller)
    {
        return $this->setStatus($seller, 'approved', 'approved');
    }

    public function suspend(User $seller)
    {
        return $this->setStatus($seller, 'suspended', 'suspended; their listings are hidden');
    }

    private function setStatus(User $seller, string $status, string $message)
    {
        abort_unless($seller->isSeller(), 404);

        $seller->update(['seller_status' => $status]);

        return back()->with('success', $seller->store_display_name . ' has been ' . $message . '.');
    }
}
