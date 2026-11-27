<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /** The buyer, the order's seller, and admins can view an order. */
    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin()
            || $user->id === $order->user_id
            || $this->isOrderSeller($user, $order);
    }

    /** Admins and the order's (approved) seller can change its status. */
    public function updateStatus(User $user, Order $order): bool
    {
        return $user->isAdmin() || ($user->isApprovedSeller() && $this->isOrderSeller($user, $order));
    }

    /** Buyers can cancel their own orders while still pending. */
    public function cancel(User $user, Order $order): bool
    {
        return $user->id === $order->user_id && $order->status === 'pending';
    }

    private function isOrderSeller(User $user, Order $order): bool
    {
        return $order->seller_id !== null && $order->seller_id === $user->id;
    }
}
