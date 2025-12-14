<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\ProductUpdated;
use App\Events\StudentCreated;
use App\Events\WalletDeposited;
use App\Events\WalletWithdrawn;
use App\Events\StockDecremented;
use App\Listeners\CheckLowStock;
use App\Listeners\LogWalletTransaction;
use App\Listeners\InvalidateProductCache;
use App\Listeners\InvalidateStudentCache;
use App\Listeners\InvalidateDashboardCache;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Order events
        OrderCreated::class => [
            InvalidateDashboardCache::class,
        ],

        // Student events
        StudentCreated::class => [
            InvalidateStudentCache::class,
        ],

        // Wallet events
        WalletDeposited::class => [
            InvalidateStudentCache::class,
            LogWalletTransaction::class,
        ],

        WalletWithdrawn::class => [
            InvalidateStudentCache::class,
            LogWalletTransaction::class,
        ],

        // Inventory events
        StockDecremented::class => [
            InvalidateProductCache::class,
            CheckLowStock::class,
        ],

        ProductUpdated::class => [
            InvalidateProductCache::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
