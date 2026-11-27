<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows only sellers. Sellers still awaiting approval (or suspended) may reach
 * the seller dashboard, which explains their status, but nothing else.
 */
class SellerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isSeller()) {
            abort(403, 'Only sellers can access this page.');
        }

        if (!$user->isApprovedSeller() && !$request->routeIs('seller.dashboard')) {
            return redirect()->route('seller.dashboard')
                ->with('error', 'Your seller account must be approved by an administrator first.');
        }

        return $next($request);
    }
}
