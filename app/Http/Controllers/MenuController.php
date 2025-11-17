<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Taxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index(Request $request, Taxonomy $taxonomy)
    {
        $query = Product::query();

        $query->withTaxonomyHierarchy($taxonomy->id);

        \Log::info($taxonomy->id);
        $products = $query->get()->map(function (Product $p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
                'price' => $p->price,
                'image_url' => $p->image_path ? Storage::url($p->image_path) : null,
                'category_id' => $p->category_id,
                'category_name' => $p->taxonomies->pluck('name')->toArray(),
            ];
        });

        // Fetch categories (main + sub) for filter
        $categories = \Aliziodev\LaravelTaxonomy\Models\Taxonomy::query()
            ->where('type', 'category')
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get()
            ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name]);

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
