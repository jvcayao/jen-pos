<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'image_url' => $this->image_path ? Storage::url($this->image_path) : null,
            'category_id' => $this->whenLoaded('taxonomies', fn () => $this->taxonomies->pluck('id')->toArray()),
            'category_name' => $this->whenLoaded('taxonomies', fn () => $this->taxonomies->pluck('name')->toArray()),
            'category_parent_id' => $this->whenLoaded('taxonomies', fn () => $this->taxonomies->pluck('parent_id')->toArray()),
        ];
    }
}
