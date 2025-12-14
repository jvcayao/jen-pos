<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreSelectionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $stores = $user->getAccessibleStores();

        // If user only has one store, auto-select it
        if ($stores->count() === 1) {
            $store = $stores->first();
            $request->session()->put('current_store_id', $store->id);

            return redirect()->route('menu.index', ['store' => $store->slug]);
        }

        return Inertia::render('store-selection', [
            'stores' => $stores,
        ]);
    }

    public function select(Request $request)
    {
        $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
        ]);

        $user = $request->user();
        $storeId = $request->input('store_id');

        // Verify user has access to this store
        if (!$user->canAccessStore($storeId)) {
            abort(403, 'You do not have access to this store.');
        }

        $store = Store::find($storeId);

        if (!$store || !$store->is_active) {
            return back()->withErrors(['store_id' => 'The selected store is not available.']);
        }

        $request->session()->put('current_store_id', $store->id);

        return redirect()->route('menu.index', ['store' => $store->slug]);
    }

    public function switch(Request $request)
    {
        // Clear current store selection and redirect to selection page
        $request->session()->forget('current_store_id');

        return redirect()->route('store.select');
    }
}
