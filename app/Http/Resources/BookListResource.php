<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookListResource extends JsonResource
{
    /**
     * Lightweight resource for list views - prevents N+1 queries
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'isbn' => $this->isbn,
            'title' => $this->title,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'price' => (float) $this->price,
            'stock_quantity' => $this->stock_quantity,
            'format' => $this->format,
            'published_at' => $this->published_at?->format('Y-m-d'),
            
            // Category only if eager-loaded (prevents N+1)
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),
            
            // Cover image only if it exists
            'cover_image' => $this->whenNotNull($this->cover_image),
            
            // Stock status for quick UI decisions
            'in_stock' => $this->stock_quantity > 0,
            'is_bestseller' => $this->stock_quantity > 100 && 
                             $this->published_at && 
                             $this->published_at->greaterThan(now()->subYear()),
            
            // Minimal timestamp for ordering
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
