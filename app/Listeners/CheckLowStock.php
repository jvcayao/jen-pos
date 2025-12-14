<?php

namespace App\Listeners;

use App\Events\StockDecremented;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckLowStock implements ShouldQueue
{
    public function handle(StockDecremented $event): void
    {
        if (!$event->isLowStock()) {
            return;
        }

        $product = $event->product;

        // Log the low stock alert
        Log::warning('Low stock alert', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'remaining_stock' => $event->remainingStock,
            'min_stock_alert' => $product->min_stock_alert,
            'store_id' => $product->store_id,
        ]);

        // Future: Send notification to admin
        // Notification::send($admins, new LowStockNotification($product));
    }
}
