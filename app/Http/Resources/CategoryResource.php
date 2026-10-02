<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description' => $this->{'description_' . app()->getLocale()},
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'parent_id' => $this->parent_id,
            'products_count' => $this->whenCounted('products'),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
