<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'items' => $this->resource['items'] ?? [],
            'total' => $this->resource['total'] ?? 0,
            'count' => $this->resource['count'] ?? 0,
        ];
    }

    public static function fromCart($cartModel): array
    {
        $items = $cartModel->items->map(fn ($item) => [
            'id' => $item->id,
            'name' => $item->itemable->name ?? '',
            'price' => $item->itemable->price ?? 0,
            'qty' => $item->quantity,
            'image' => $item->itemable->image_url ?? null,
        ])->toArray();

        return [
            'items' => $items,
            'total' => $cartModel->calculatedPriceByQuantity(),
            'count' => $cartModel->items()->count(),
        ];
    }
}
