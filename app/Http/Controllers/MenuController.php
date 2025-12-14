<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\Request;
use Binafy\LaravelCart\Models\Cart;
use App\Http\Resources\CartResource;
use App\Http\Resources\ProductResource;
use App\Models\Taxonomy as TaxonomyModel;
use Aliziodev\LaravelTaxonomy\Models\Taxonomy as AliziodevTaxonomyModel;

class MenuController extends Controller
{
    public function index(Request $request, Store $store, ?TaxonomyModel $taxonomy = null): Response
    {
        // Set the store in app container for global scopes to work
        app()->instance('current.store', $store);

        // Get taxonomy IDs to filter by (including children)
        $taxonomyIds = $this->getTaxonomyIdsForFilter($store, $taxonomy);

        $products = Product::query()
            ->with('taxonomies')
            ->when(! empty($taxonomyIds), function ($query) use ($taxonomyIds) {
                $query->whereHas('taxonomies', fn ($q) => $q->whereIn('taxonomies.id', $taxonomyIds));
            })
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->get();

        $categories = $this->getSubCategories($store, $taxonomy);
        $cart = $this->getCartData();

        return Inertia::render('menu/index', [
            'products' => ProductResource::collection($products)->resolve(),
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
            'cart' => $cart,
            'store' => $store,
        ]);
    }

    private function getTaxonomyIdsForFilter(Store $store, ?TaxonomyModel $taxonomy): array
    {
        if (! $taxonomy?->exists) {
            // No taxonomy filter - return empty to show all products
            return [];
        }

        // Get the taxonomy and all its descendants
        $ids = [$taxonomy->id];

        // Add all children (subcategories) IDs
        $children = AliziodevTaxonomyModel::where('store_id', $store->id)
            ->where('parent_id', $taxonomy->id)
            ->pluck('id')
            ->toArray();

        return array_merge($ids, $children);
    }

    private function getSubCategories(Store $store, ?TaxonomyModel $taxonomy): mixed
    {
        if (! $taxonomy?->exists) {
            // Return root categories for this store
            return AliziodevTaxonomyModel::where('store_id', $store->id)
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get()
                ->map(fn ($subCategory) => [
                    'id' => $subCategory->id,
                    'name' => $subCategory->name,
                    'slug' => $subCategory->slug,
                ]);
        }

        // Return children of the selected category for this store
        return AliziodevTaxonomyModel::where('store_id', $store->id)
            ->where('parent_id', $taxonomy->id)
            ->orderBy('name')
            ->get()
            ->map(fn ($subCategory) => [
                'id' => $subCategory->id,
                'name' => $subCategory->name,
                'slug' => $subCategory->slug,
            ]);
    }

    private function getCartData(): array
    {
        $cartModel = Cart::query()->firstOrCreate(['user_id' => auth()->id()]);

        return CartResource::fromCart($cartModel);
    }
}
