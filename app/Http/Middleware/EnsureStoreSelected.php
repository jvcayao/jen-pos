<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Get accessible stores
        $stores = $user->getAccessibleStores();

        // If user has no stores, deny access
        if ($stores->isEmpty()) {
            abort(403, 'You do not have access to any stores.');
        }

        // If user has only one store, auto-select it
        if ($stores->count() === 1 && !session()->has('current_store_id')) {
            session()->put('current_store_id', $stores->first()->id);
        }

        // If no store selected and user has multiple stores, redirect to selection
        if (!session()->has('current_store_id')) {
            return redirect()->route('store.select');
        }

        // Verify user still has access to selected store
        $selectedStoreId = session()->get('current_store_id');
        if (!$user->canAccessStore($selectedStoreId)) {
            session()->forget('current_store_id');

            return redirect()->route('store.select');
        }

        return $next($request);
    }
}
