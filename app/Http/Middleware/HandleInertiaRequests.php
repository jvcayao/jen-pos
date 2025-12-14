<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Inertia\Middleware;
use Illuminate\Http\Request;
use Aliziodev\LaravelTaxonomy\Models\Taxonomy;
use Aliziodev\LaravelTaxonomy\Enums\TaxonomyType;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $currentStoreId = session('current_store_id');
        $currentStore = $currentStoreId ? Store::find($currentStoreId) : null;

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'permissions' => $user ? $user->getAllPermissions()->pluck('name')->values()->all() : [],
            ],
            'currentStore' => $currentStore ? [
                'id' => $currentStore->id,
                'name' => $currentStore->name,
                'slug' => $currentStore->slug,
                'code' => $currentStore->code,
            ] : null,
            'canSwitchStore' => $user && $user->hasMultipleStores(),
            'sidebarOpen' => !$request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'navigation' => $currentStore
                ? Taxonomy::where('store_id', $currentStore->id)
                    ->where('type', TaxonomyType::Category->value)
                    ->whereNull('parent_id')
                    ->orderBy('name')
                    ->get()
                    ->map(fn ($category) => [
                        'id' => $category->id,
                        'title' => $category->name,
                        'href' => route('menu.index', ['store' => $currentStore->slug, 'taxonomy' => $category->slug]),
                        'slug' => $category->slug,
                    ])
                : collect(),
        ];
    }
}
