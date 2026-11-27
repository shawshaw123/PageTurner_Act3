<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One online payment (PayMongo Checkout Session) for all orders created in one
 * checkout. The orders share the payment's checkout_reference.
 */
class Payment extends Model
{
    public const PENDING = 'pending';
    public const PAID = 'paid';
    public const FAILED = 'failed';
    public const EXPIRED = 'expired';

    protected $fillable = [
        'user_id', 'checkout_reference', 'provider', 'checkout_session_id', 'payment_intent_id',
        'provider_payment_id', 'amount', 'currency', 'status', 'checkout_url', 'failure_reason',
        'paid_at', 'failed_at', 'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'checkout_reference', 'checkout_reference');
    }

    /** Still waiting for the buyer: no payment yet, or a failed attempt they can retry. */
    public function isOpen(): bool
    {
        return in_array($this->status, [self::PENDING, self::FAILED], true);
    }
}
