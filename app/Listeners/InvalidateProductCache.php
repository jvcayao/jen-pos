<?php

namespace App\Listeners;

use App\Events\ProductUpdated;
use App\Services\CacheService;
use App\Events\StockDecremented;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvalidateProductCache implements ShouldQueue
{
    public function __construct(
        protected CacheService $cacheService,
    ) {}

    public function handle(ProductUpdated|StockDecremented $event): void
    {
        $storeId = $event->product->store_id;

        $this->cacheService->invalidateProducts($storeId);
        $this->cacheService->invalidateMenu($storeId);
    }
}
