<?php

namespace App\Http\Controllers;

use Aliziodev\LaravelTaxonomy\Enums\TaxonomyType;
use Aliziodev\LaravelTaxonomy\Models\Taxonomy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {

        $categories = Taxonomy::tree(TaxonomyType::Category)
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'children' => $category->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                        ];
                    }),
                ];
            });

        return Inertia::render('categories/index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:taxonomies,id'],
        ]);

        DB::transaction(function () use ($data) {
            $category = new Taxonomy;
            $category->type = 'category';
            $category->name = $data['name'];
            if (! empty($data['slug'])) {
                $category->slug = $data['slug'];
            }
            if (! empty($data['parent_id'])) {
                $category->parent_id = $data['parent_id'];
            }
            $category->save();
        });

        return back()->with('success', 'Category created');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Taxonomy $category): RedirectResponse
    {
        // Ensure only categories type can be updated via this controller
        if ($category->type !== 'category') {
            abort(404);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($category, $data) {
            $category->name = $data['name'];
            if (array_key_exists('slug', $data)) {
                $category->slug = $data['slug'] ?: null;
            }
            $category->save();
        });

        return back()->with('success', 'Category updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Taxonomy $category): RedirectResponse
    {
        if ($category->type !== 'category') {
            abort(404);
        }

        DB::transaction(function () use ($category) {
            // Deleting a parent should cascade to its children if FK is set; otherwise, delete recursively.
            // We'll attempt a simple delete, and rely on package/model to handle nested set updates.
            $category->delete();
        });

        return back()->with('success', 'Category deleted');
    }
}
