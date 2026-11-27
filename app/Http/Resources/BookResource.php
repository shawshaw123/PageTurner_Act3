<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
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
            'is_active' => $this->is_active,
            'published_at' => $this->published_at?->format('Y-m-d'),
            
            // Only load description on detail route, not in list views
            'description' => $this->when(
                $request->routeIs('books.show') || $request->routeIs('api.books.show'),
                $this->description
            ),
            
            // Additional fields only for detail views
            'pages' => $this->when(
                $request->routeIs('books.show') || $request->routeIs('api.books.show'),
                $this->pages
            ),
            'language' => $this->when(
                $request->routeIs('books.show') || $request->routeIs('api.books.show'),
                $this->language
            ),
            'dimensions' => $this->when(
                $request->routeIs('books.show') || $request->routeIs('api.books.show'),
                $this->dimensions
            ),
            'weight' => $this->when(
                $request->routeIs('books.show') || $request->routeIs('api.books.show'),
                $this->weight
            ),
            
            // Category loaded safely - no query if not eager-loaded
            'category' => new CategoryResource($this->whenLoaded('category')),
            
            // Cover image only if it exists
            'cover_image' => $this->whenNotNull($this->cover_image),
            
            // Average rating (computed field - only when needed)
            'average_rating' => $this->when(
                $request->routeIs('books.show') || $request->routeIs('api.books.show'),
                $this->whenLoaded('reviews', fn() => $this->average_rating)
            ),
            
            // Reviews count only on detail pages
            'reviews_count' => $this->when(
                $request->routeIs('books.show') || $request->routeIs('api.books.show'),
                $this->whenLoaded('reviews', fn() => $this->reviews_count)
            ),
            
            // Timestamps
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
