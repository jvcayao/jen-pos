<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\CacheService;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvalidateDashboardCache implements ShouldQueue
{
    public function __construct(
        protected CacheService $cacheService,
    ) {}

    public function handle(OrderCreated $event): void
    {
        $storeId = $event->order->store_id;

        $this->cacheService->invalidateDashboard($storeId);
    }
}
