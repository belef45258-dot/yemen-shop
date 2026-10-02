<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'final_price' => $this->final_price,
            'discount_percentage' => $this->discount_percentage,
            'is_on_sale' => $this->is_on_sale,
            'currency' => $this->currency,
            'stock' => $this->stock,
            'in_stock' => $this->is_in_stock,
            'is_featured' => $this->is_featured,
            'rating' => $this->average_rating,
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
