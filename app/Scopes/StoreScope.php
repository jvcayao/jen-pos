<?php

namespace App\Scopes;

use App\Models\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class StoreScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $store = $this->getCurrentStore();

        if ($store) {
            $builder->where($model->getTable().'.store_id', $store->id);
        }
    }

    protected function getCurrentStore(): ?Store
    {
        return app()->bound('current.store') ? app('current.store') : null;
    }
}
