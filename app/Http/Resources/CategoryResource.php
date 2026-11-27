<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->when(
                $request->routeIs('categories.show'),
                $this->description
            ),
            
            // Book count only when needed
            'books_count' => $this->when(
                $this->relationLoaded('books') || 
                $request->routeIs('categories.show'),
                $this->whenLoaded('books', fn() => $this->books_count)
            ),
            
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
