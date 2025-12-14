<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Store;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Head office admin (no store_id) can access all stores
        if ($user->store_id === null) {
            return $next($request);
        }

        // Check if requesting a specific store via route parameter
        $storeId = $request->route('store') ?? $request->input('store_id');

        if ($storeId) {
            $store = $storeId instanceof Store ? $storeId : Store::find($storeId);

            if ($store && $store->id !== $user->store_id) {
                abort(403, 'You do not have access to this store.');
            }
        }

        return $next($request);
    }
}
