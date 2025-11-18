<?php

namespace App\Http\Controllers;

use Aliziodev\LaravelTaxonomy\Facades\Taxonomy;
use App\Models\Product;
use App\Models\Taxonomy as TaxonomyModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index(Request $request, TaxonomyModel $taxonomy)
    {
        $query = Product::query();

        $query->withTaxonomyHierarchy($taxonomy->id);
        $query->with(['taxonomies']);
        $query->when($request->has('search'), function ($query) use ($request) {

            $search = $request->query('search');

            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%');
            });

        });

        $query->when($request->has('category'), function ($query) use ($request) {
            $subCategory = $request->query('category');

            $query->withTaxonomySlug($subCategory);
        });

        $products = $query->get()->map(function (Product $p) {

            return [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
                'price' => $p->price,
                'image_url' => $p->image_path ? Storage::url($p->image_path) : null,
                'category_parent_id' => $p->taxonomies->pluck('parent_id')->toArray(),
                'category_id' => $p->taxonomies->pluck('id')->toArray(),
                'category_name' => $p->taxonomies->pluck('name')->toArray(),

            ];
        });

        // Fetch categories (main + sub) for filter
        $categories = Taxonomy::findBySlug($taxonomy->slug)->children->map(function ($subCategories) {

            return [
                'id' => $subCategories->id,
                'name' => $subCategories->name,
                'slug' => $subCategories->slug,

            ];
        });

        return Inertia::render('menu/index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'search' => $request->get('search'),
                'category' => $request->get('category'),
            ],
        ]);

    }
}
