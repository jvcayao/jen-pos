<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Aliziodev\LaravelTaxonomy\Models\Taxonomy;
use Aliziodev\LaravelTaxonomy\Enums\TaxonomyType;
use App\Http\Controllers\Traits\FlashesSessionData;

class CategoryController extends Controller
{
    use FlashesSessionData;

    public function index(): Response
    {
        $categories = Taxonomy::tree(TaxonomyType::Category)
            ->load('children');

        return Inertia::render('categories/index', [
            'categories' => CategoryResource::collection($categories)->resolve(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $category = new Taxonomy;
            $category->type = 'category';
            $category->name = $validated['name'];

            if (!empty($validated['slug'])) {
                $category->slug = $validated['slug'];
            }

            if (!empty($validated['parent_id'])) {
                $category->parent_id = $validated['parent_id'];
            }

            $category->save();
        });

        return back()->with('flash', [
            'message' => 'Category created successfully',
            'type' => 'success',
        ]);
    }

    public function update(UpdateCategoryRequest $request, Taxonomy $category): RedirectResponse
    {
        abort_unless($category->type === 'category', 404);

        $validated = $request->validated();

        DB::transaction(function () use ($category, $validated) {
            $category->name = $validated['name'];

            if (array_key_exists('slug', $validated)) {
                $category->slug = $validated['slug'] ?: null;
            }

            $category->save();
        });

        return back()->with('flash', [
            'message' => 'Category updated successfully',
            'type' => 'success',
        ]);
    }

    public function destroy(Taxonomy $category): RedirectResponse
    {
        abort_unless($category->type === 'category', 404);

        DB::transaction(fn () => $category->delete());

        return back()->with('flash', [
            'message' => 'Category deleted successfully',
            'type' => 'success',
        ]);
    }
}
