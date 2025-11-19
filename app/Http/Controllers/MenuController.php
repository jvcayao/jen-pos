<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Taxonomy as TaxonomyModel;
use Aliziodev\LaravelTaxonomy\Facades\Taxonomy;
use Aliziodev\LaravelTaxonomy\Models\Taxonomy as AliziodevTaxonomyModel;

class MenuController extends Controller
{
    public function index(Request $request, TaxonomyModel $taxonomy)
    {
        $query = Product::query();

        if ($taxonomy->exists) {
            $query->withTaxonomyHierarchy($taxonomy->id);
        }

        $query->with(['taxonomies']);

        $query->when($request->has('search'), function ($query) use ($request) {

            $search = $request->string('search')->toString();

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
        $categories = $this->getSubCategories($taxonomy);

        return Inertia::render('menu/index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'search' => $request->get('search'),
                'category' => $request->get('category'),
            ],
        ]);

    }

    public function getSubCategories(TaxonomyModel $taxonomy)
    {
        if (!$taxonomy->exists) {
            return AliziodevTaxonomyModel::with('children')->get()->map(function ($subCategory) {
                return [
                    'id' => $subCategory->id,
                    'name' => $subCategory->name,
                    'slug' => $subCategory->slug,

                ];
            });
        }

        return Taxonomy::findBySlug($taxonomy->slug)->children->map(function ($subCategories) {

            return [
                'id' => $subCategories->id,
                'name' => $subCategories->name,
                'slug' => $subCategories->slug,

            ];
        });

    }
}
