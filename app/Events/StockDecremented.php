<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class StockDecremented
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Product $product,
        public int $quantity,
        public int $remainingStock,
        public ?int $orderId = null,
    ) {}

    /**
     * Check if stock is below alert threshold
     */
    public function isLowStock(): bool
    {
        if (!$this->product->has_stock_alert) {
            return false;
        }

        return $this->remainingStock <= ($this->product->min_stock_alert ?? 0);
    }
}
