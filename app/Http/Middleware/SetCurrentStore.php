<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentStore
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // Get the selected store from session
            $selectedStoreId = $request->session()->get('current_store_id');

            if ($selectedStoreId) {
                $store = Store::find($selectedStoreId);

                // Verify user has access to this store
                if ($store && $store->is_active && $user->canAccessStore($store->id)) {
                    app()->instance('current.store', $store);
                } else {
                    // Invalid store selection, clear it
                    $request->session()->forget('current_store_id');
                }
            }
            // If no store selected, no scope is applied (for store selection page)
        }

        return $next($request);
    }
}
