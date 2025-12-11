<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Product;
use Illuminate\Http\Request;
use Binafy\LaravelCart\Models\Cart;
use App\Http\Resources\CartResource;
use App\Http\Resources\ProductResource;
use App\Models\Taxonomy as TaxonomyModel;
use Aliziodev\LaravelTaxonomy\Models\Taxonomy as AliziodevTaxonomyModel;

class MenuController extends Controller
{
    public function index(Request $request, TaxonomyModel $taxonomy): Response
    {
        $products = Product::query()
            ->with('taxonomies')
            ->when($taxonomy->exists, fn ($query) => $query->withTaxonomyHierarchy($taxonomy->id)
            )
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%')
            )
            ->when($request->filled('category'), fn ($query) => $query->withTaxonomySlug($request->query('category'))
            )
            ->get();

        $categories = $this->getSubCategories($taxonomy);
        $cart = $this->getCartData();

        return Inertia::render('menu/index', [
            'products' => ProductResource::collection($products)->resolve(),
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
            'cart' => $cart,
        ]);
    }

    private function getSubCategories(TaxonomyModel $taxonomy): mixed
    {
        if (!$taxonomy->exists) {
            return AliziodevTaxonomyModel::with('children')
                ->get()
                ->map(fn ($subCategory) => [
                    'id' => $subCategory->id,
                    'name' => $subCategory->name,
                    'slug' => $subCategory->slug,
                ]);
        }

        return AliziodevTaxonomyModel::with('children')
            ->where('slug', $taxonomy->slug)
            ->first()
            ?->children
            ->map(fn ($subCategory) => [
                'id' => $subCategory->id,
                'name' => $subCategory->name,
                'slug' => $subCategory->slug,
            ]) ?? collect();
    }

    private function getCartData(): array
    {
        $cartModel = Cart::query()->firstOrCreate(['user_id' => auth()->id()]);

        return CartResource::fromCart($cartModel);
    }
}
