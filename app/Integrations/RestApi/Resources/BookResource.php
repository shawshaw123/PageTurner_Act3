<?php

namespace App\Integrations\RestApi\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** A book listing as returned by the REST API. */
class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'isbn' => $this->isbn,
            'title' => $this->title,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'description' => $this->description,
            'price' => (float) $this->price,
            'stock_quantity' => (int) $this->stock_quantity,
            'in_stock' => $this->stock_quantity > 0,
            'format' => $this->format,
            'is_active' => (bool) $this->is_active,
            'cover_image_url' => $this->cover_image ? asset('storage/' . $this->cover_image) : null,
            'average_rating' => round($this->average_rating, 2),
            'reviews_count' => $this->reviews_count,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'seller' => $this->whenLoaded('seller', fn () => $this->seller ? [
                'id' => $this->seller->id,
                'store_name' => $this->seller->store_display_name,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
