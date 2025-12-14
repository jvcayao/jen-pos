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
            'subtotal' => $this->resource['subtotal'] ?? 0,
            'vat_amount' => $this->resource['vat_amount'] ?? 0,
            'total' => $this->resource['total'] ?? 0,
            'count' => $this->resource['count'] ?? 0,
            'tax_rate' => $this->resource['tax_rate'] ?? 0.12,
        ];
    }

    public static function fromCart($cartModel): array
    {
        $taxRate = (float) config('payment.tax_rate', 0.12);

        if (is_object($cartModel) && method_exists($cartModel, 'loadMissing')) {
            $cartModel->loadMissing(['items', 'items.itemable']);
        }

        $items = $cartModel->items
            ->filter(fn ($item) => $item->itemable !== null)
            ->map(fn ($item) => [
                'id' => $item->itemable->id,
                'name' => $item->itemable->name ?? '',
                'price' => $item->itemable->price ?? 0,
                'qty' => $item->quantity,
                'image' => $item->itemable->image_url ?? null,
                'has_vat' => (bool) ($item->itemable->has_vat ?? true),
            ])->values()->toArray();

        // VAT-INCLUSIVE PRICING
        // Total = sum of all item prices (prices already include VAT for VATable items)
        $total = collect($items)->sum(fn ($item) => $item['price'] * $item['qty']);

        // Calculate VAT amount from VATable items: VAT = Total × (12/112)
        $vatableTotal = collect($items)
            ->filter(fn ($item) => $item['has_vat'])
            ->sum(fn ($item) => $item['price'] * $item['qty']);
        $vatAmount = $vatableTotal * ($taxRate / (1 + $taxRate));

        // Subtotal (Net of VAT) = Total - VAT Amount
        $subtotal = $total - $vatAmount;

        // Total quantity of all items in cart
        $totalQuantity = collect($items)->sum('qty');

        return [
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'vat_amount' => round($vatAmount, 2),
            'total' => round($total, 2),
            'count' => $totalQuantity,
            'tax_rate' => $taxRate,
        ];
    }
}
