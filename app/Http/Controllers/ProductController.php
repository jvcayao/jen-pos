<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Aliziodev\LaravelTaxonomy\Models\Taxonomy;
use Aliziodev\LaravelTaxonomy\Enums\TaxonomyType;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

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

        $products = $query->latest()->get()->map(function (Product $p) {
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

        // Fetch only subcategories for filter
        $categories = collect();
        $categories = Taxonomy::tree(TaxonomyType::Category)
            ->flatMap(fn ($parent) => $parent->children->map(fn ($child) => [
                'id' => $child->id,
                'name' => $child->name,
                'slug' => $child->slug,
            ])
            );

        return Inertia::render('products/index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'search' => $request->get('search'),
                'category' => $request->get('category'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'string'], // uuid string
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'slug' => Str::slug($data['name']),
            'price' => $data['price'],
            'image_path' => $imagePath,
        ]);

        $product->attachTaxonomies($data['category_id']);

        return redirect()->back()->with('success', 'Product created');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            // delete old if exists
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $product->image_path = $request->file('image')->store('products', 'public');
        }

        $product->fill([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'category_id' => $data['category_id'] ?? null,
        ])->save();

        return redirect()->back()->with('success', 'Product updated');
    }

    public function destroy(Product $product)
    {
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();

        return redirect()->back()->with('success', 'Product deleted');
    }
}
