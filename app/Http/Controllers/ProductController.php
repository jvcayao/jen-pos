<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;
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
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('category'), fn ($query) => $query->withTaxonomySlug($request->query('category')))
            ->latest()
            ->get();

        $categories = Taxonomy::tree(TaxonomyType::Category)
            ->flatMap(fn ($parent) => $parent->children->map(fn ($child) => [
                'id' => $child->id,
                'name' => $child->name,
                'slug' => $child->slug,
            ]));

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
            'slug' => Str::slug($validated['name']).'-'.Str::random(5),
            'sku' => $validated['sku'] ?? null,
            'barcode' => $validated['barcode'] ?? null,
            'price' => $validated['price'],
            'discount' => $validated['discount'] ?? 0,
            'discount_to' => $validated['discount_to'] ?? null,
            'vat' => $validated['vat'] ?? 0,
            'has_vat' => $validated['has_vat'] ?? true,
            'stock' => $validated['stock'] ?? 0,
            'track_inventory' => $validated['track_inventory'] ?? false,
            'is_activated' => $validated['is_activated'] ?? true,
            'has_unlimited_stock' => $validated['has_unlimited_stock'] ?? false,
            'has_max_cart' => $validated['has_max_cart'] ?? false,
            'min_cart' => $validated['min_cart'] ?? null,
            'max_cart' => $validated['max_cart'] ?? null,
            'has_stock_alert' => $validated['has_stock_alert'] ?? false,
            'min_stock_alert' => $validated['min_stock_alert'] ?? null,
            'max_stock_alert' => $validated['max_stock_alert'] ?? null,
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
            'sku' => $validated['sku'] ?? null,
            'barcode' => $validated['barcode'] ?? null,
            'price' => $validated['price'],
            'discount' => $validated['discount'] ?? 0,
            'discount_to' => $validated['discount_to'] ?? null,
            'vat' => $validated['vat'] ?? 0,
            'has_vat' => $validated['has_vat'] ?? true,
            'stock' => $validated['stock'] ?? 0,
            'track_inventory' => $validated['track_inventory'] ?? false,
            'is_activated' => $validated['is_activated'] ?? true,
            'has_unlimited_stock' => $validated['has_unlimited_stock'] ?? false,
            'has_max_cart' => $validated['has_max_cart'] ?? false,
            'min_cart' => $validated['min_cart'] ?? null,
            'max_cart' => $validated['max_cart'] ?? null,
            'has_stock_alert' => $validated['has_stock_alert'] ?? false,
            'min_stock_alert' => $validated['min_stock_alert'] ?? null,
            'max_stock_alert' => $validated['max_stock_alert'] ?? null,
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

    /**
     * Generate SKU and barcode for a new product
     */
    public function generateCodes(Request $request): JsonResponse
    {
        $categoryId = $request->input('category_id');
        $categoryName = null;

        if ($categoryId) {
            $category = Taxonomy::find($categoryId);
            $categoryName = $category?->name;
        }

        return response()->json([
            'sku' => Product::generateSku($categoryName),
            'barcode' => Product::generateBarcode(),
        ]);
    }
}
