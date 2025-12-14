<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Store;
use Inertia\Response;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\CacheService;
use Binafy\LaravelCart\Models\Cart;
use App\Http\Resources\CartResource;
use App\Http\Resources\ProductResource;
use App\Models\Taxonomy as TaxonomyModel;
use Aliziodev\LaravelTaxonomy\Models\Taxonomy as AliziodevTaxonomyModel;

class MenuController extends Controller
{
    public function __construct(
        protected CacheService $cacheService,
    ) {}

    public function index(Request $request, Store $store, ?TaxonomyModel $taxonomy = null): Response
    {
        // Set the store in app container for global scopes to work
        app()->instance('current.store', $store);

        $categoryId = $taxonomy?->id;
        $search = $request->string('search')->toString();

        // Get products (cached when no search, fresh when searching)
        if (empty($search)) {
            $products = $this->cacheService->remember(
                $this->cacheService->getProductsKey($store->id, $categoryId),
                CacheService::TTL_LONG,
                fn () => $this->getProducts($store, $taxonomy, null)
            );
        } else {
            // Don't cache search results - they're too varied
            $products = $this->getProducts($store, $taxonomy, $search);
        }

        // Get categories (cached)
        $categories = $this->cacheService->remember(
            $this->cacheService->getCategoryChildrenKey($store->id, $categoryId),
            CacheService::TTL_VERY_LONG,
            fn () => $this->getSubCategories($store, $taxonomy)
        );

        $cart = $this->getCartData();

        return Inertia::render('menu/index', [
            'products' => ProductResource::collection($products)->resolve(),
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
            'cart' => $cart,
            'store' => $store,
        ]);
    }

    private function getProducts(Store $store, ?TaxonomyModel $taxonomy, ?string $search)
    {
        $taxonomyIds = $this->getTaxonomyIdsForFilter($store, $taxonomy);

        return Product::query()
            ->with('taxonomies')
            ->when(!empty($taxonomyIds), function ($query) use ($taxonomyIds) {
                $query->whereHas('taxonomies', fn ($q) => $q->whereIn('taxonomies.id', $taxonomyIds));
            })
            ->when(!empty($search), fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->get();
    }

    private function getTaxonomyIdsForFilter(Store $store, ?TaxonomyModel $taxonomy): array
    {
        if (!$taxonomy?->exists) {
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
        if (!$taxonomy?->exists) {
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
