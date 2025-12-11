<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'price' => $this->price,
            'discount' => $this->discount ?? 0,
            'discount_to' => $this->discount_to,
            'vat' => $this->vat ?? 0,
            'has_vat' => (bool) ($this->has_vat ?? true),
            'stock' => $this->stock ?? 0,
            'track_inventory' => $this->track_inventory ?? false,
            'is_activated' => (bool) ($this->is_activated ?? true),
            'has_unlimited_stock' => $this->has_unlimited_stock ?? false,
            'has_max_cart' => $this->has_max_cart ?? false,
            'min_cart' => $this->min_cart,
            'max_cart' => $this->max_cart,
            'has_stock_alert' => $this->has_stock_alert ?? false,
            'min_stock_alert' => $this->min_stock_alert,
            'max_stock_alert' => $this->max_stock_alert,
            'image_url' => $this->image_path ? Storage::url($this->image_path) : null,
            'category_id' => $this->whenLoaded('taxonomies', fn () => $this->taxonomies->pluck('id')->toArray()),
            'category_name' => $this->whenLoaded('taxonomies', fn () => $this->taxonomies->pluck('name')->toArray()),
            'category_parent_id' => $this->whenLoaded('taxonomies', fn () => $this->taxonomies->pluck('parent_id')->toArray()),
        ];
    }
}
