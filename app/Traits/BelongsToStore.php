<?php

namespace App\Traits;

use App\Models\Store;
use App\Scopes\StoreScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToStore
{
    public static function bootBelongsToStore(): void
    {
        static::addGlobalScope(new StoreScope);

        static::creating(function ($model) {
            if (empty($model->store_id) && app()->bound('current.store')) {
                $store = app('current.store');
                if ($store) {
                    $model->store_id = $store->id;
                }
            }
        });
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeForStore($query, $storeId)
    {
        return $query->withoutGlobalScope(StoreScope::class)->where('store_id', $storeId);
    }

    public function scopeForAllStores($query)
    {
        return $query->withoutGlobalScope(StoreScope::class);
    }
}
