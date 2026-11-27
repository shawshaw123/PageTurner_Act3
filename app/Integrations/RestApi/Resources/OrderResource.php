<?php

namespace App\Integrations\RestApi\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** An order (one seller's part of a checkout) as returned by the REST API. */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'checkout_reference' => $this->checkout_reference,
            'status' => $this->status,
            'total_amount' => (float) $this->total_amount,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'shipping_address' => $this->shipping_address,
            'notes' => $this->notes,
            'buyer' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'seller' => $this->whenLoaded('seller', fn () => $this->seller ? [
                'id' => $this->seller->id,
                'store_name' => $this->seller->store_display_name,
            ] : null),
            'items' => $this->whenLoaded('orderItems', fn () => $this->orderItems->map(fn ($item) => [
                'book_id' => $item->book_id,
                'title' => $item->book?->title,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->price,
                'subtotal' => (float) $item->subtotal,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
