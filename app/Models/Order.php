<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Order extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /** Statuses a seller or admin can set on an order. */
    public const STATUSES = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

    /** How the buyer pays. "paymongo" is the online payment gateway (IP-2). */
    public const PAYMENT_METHODS = ['paymongo', 'cash_on_delivery'];

    /**
     * payment_status values:
     *  unpaid            cash on delivery, paid when delivered
     *  awaiting_payment  sent to PayMongo, no payment yet
     *  paid / failed / expired  set by the PayMongo webhook (IP-3) or the scheduler
     */
    public const PAYMENT_STATUSES = ['unpaid', 'awaiting_payment', 'paid', 'failed', 'expired'];

    protected $fillable = [
        'user_id', 'seller_id', 'checkout_reference', 'total_amount', 'status', 'shipping_address',
        'payment_method', 'payment_status', 'paid_at', 'notes',
    ];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }

    /** The buyer. */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** The seller who fulfils this order (null for legacy store orders). */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /** The online payment covering this order's checkout (PayMongo orders only). */
    public function payment()
    {
        return $this->hasOne(Payment::class, 'checkout_reference', 'checkout_reference');
    }

    /** Online order the buyer still has to pay (or retry after a failed attempt). */
    public function awaitsOnlinePayment(): bool
    {
        return $this->payment_method === 'paymongo'
            && in_array($this->payment_status, ['awaiting_payment', 'failed'], true)
            && $this->status !== 'cancelled';
    }

    /** Why the order may not move to this status, or null if it may. */
    public function statusChangeError(string $newStatus): ?string
    {
        if ($this->status === 'cancelled' && $newStatus !== 'cancelled') {
            return 'A cancelled order cannot be reopened.';
        }

        if ($this->payment_method === 'paymongo' && $this->payment_status !== 'paid'
            && in_array($newStatus, ['processing', 'shipped', 'completed'], true)) {
            return 'This order is paid online and has not been paid yet. Wait for the payment before processing it.';
        }

        return null;
    }

    /** Extra changes that come with a status change: cash on delivery is paid on completion. */
    public function paymentChangesFor(string $newStatus): array
    {
        if ($newStatus === 'completed' && $this->payment_method === 'cash_on_delivery' && $this->payment_status !== 'paid') {
            return ['payment_status' => 'paid', 'paid_at' => now()];
        }

        return [];
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'unpaid' => 'Cash on delivery',
            'awaiting_payment' => 'Awaiting payment',
            'paid' => 'Paid',
            'failed' => 'Payment failed',
            'expired' => 'Payment expired',
            default => ucfirst((string) $this->payment_status),
        };
    }
}
