<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Aliziodev\LaravelTaxonomy\Models\Taxonomy;
use Aliziodev\LaravelTaxonomy\Enums\TaxonomyType;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $products = Product::query()
            ->with('taxonomies')
            ->when($request->filled('search'), fn ($query) =>
                $query->where('name', 'like', '%' . $request->string('search') . '%')
            )
            ->when($request->filled('category'), fn ($query) =>
                $query->withTaxonomySlug($request->query('category'))
            )
            ->latest()
            ->get();

        $categories = Taxonomy::tree(TaxonomyType::Category)
            ->flatMap(fn ($parent) =>
                $parent->children->map(fn ($child) => [
                    'id' => $child->id,
                    'name' => $child->name,
                    'slug' => $child->slug,
                ])
            );

        return Inertia::render('products/index', [
            'products' => ProductResource::collection($products)->resolve(),
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('products', 'public')
            : null;

        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'slug' => Str::slug($validated['name']),
            'price' => $validated['price'],
            'image_path' => $imagePath,
        ]);

        if (!empty($validated['category_id'])) {
            $product->attachTaxonomies($validated['category_id']);
        }

        return back()->with('flash', [
            'message' => 'Product created successfully',
            'type' => 'success',
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $product->image_path = $request->file('image')->store('products', 'public');
        }

        $product->fill([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
        ])->save();

        if (!empty($validated['category_id'])) {
            $product->syncTaxonomies($validated['category_id']);
        }

        return back()->with('flash', [
            'message' => 'Product updated successfully',
            'type' => 'success',
        ]);
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return back()->with('flash', [
            'message' => 'Product deleted successfully',
            'type' => 'success',
        ]);
    }
}
