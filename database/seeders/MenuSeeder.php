<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Aliziodev\LaravelTaxonomy\Enums\TaxonomyType;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing taxonomies to allow re-seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('taxonomables')->truncate();
        DB::table('taxonomies')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = [
            'Cakes Breads & Pizza' => [
                'Bread with toppings',
                'Regular Bread',
                'Pizzas',
                'Cookies',
                'Cakes',
                'Muffins',
                'Waffles',
                'Donuts',
                'Brownies',
                'Baked',
            ],

            'Snacks & Dessert' => [
                'Rice Cakes',
                'Snacks',
                'Desserts',
            ],

            'Burger & Sandwiches' => [
                'Specialty Sandwich',
                'Burgers',
            ],

            'Breakfast Meal' => [
                'Congee / Porridge',
                'Silog Meal',
                'Regular Breakfast',
                'Pancakes',
            ],

            'Soups & Noodles' => [
                'Beef Noodles',
                'Chicken Noodles',
                'Pancit',
                'Spaghettis',
                'Korean Noodles',
                'Soups',
            ],

            'Main Dishes & Sides' => [
                'Chicken Dishes',
                'Pork Dishes',
                'Vegetable Dishes',
                'Grilled Dishes',
                'Ceviche Dishes',
                'Beef Dishes',
                'Rice Dishes',
                'Salads',
            ],

            'Fruits & Drinks' => [
                'Cold Drinks',
                'Hot Drinks',
                'Fruits',
            ],
        ];

        $stores = Store::all();

        foreach ($stores as $store) {
            $this->seedCategoriesForStore($store, $categories);
        }
    }

    /**
     * Seed categories for a specific store using bulk operations.
     *
     * @param  array<string, array<int, string>>  $categories
     */
    private function seedCategoriesForStore(Store $store, array $categories): void
    {
        $now = now();
        $type = TaxonomyType::Category->value;

        // First, bulk insert all parent categories
        $parentRecords = [];
        foreach ($categories as $category => $subcategories) {
            $parentRecords[] = [
                'store_id' => $store->id,
                'name' => $category,
                'slug' => Str::slug($category).'-'.$store->id,
                'type' => $type,
                'description' => $category,
                'parent_id' => null,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('taxonomies')->insert($parentRecords);

        // Fetch the newly created parent IDs
        $parentIds = DB::table('taxonomies')
            ->where('store_id', $store->id)
            ->whereNull('parent_id')
            ->pluck('id', 'slug')
            ->toArray();

        // Now bulk insert all subcategories
        $childRecords = [];
        foreach ($categories as $category => $subcategories) {
            $parentSlug = Str::slug($category).'-'.$store->id;
            $parentId = $parentIds[$parentSlug] ?? null;

            if (!$parentId) {
                continue;
            }

            foreach ($subcategories as $sub) {
                $childRecords[] = [
                    'store_id' => $store->id,
                    'name' => $sub,
                    'slug' => Str::slug($sub).'-'.$store->id,
                    'type' => $type,
                    'description' => $sub,
                    'parent_id' => $parentId,
                    'sort_order' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($childRecords)) {
            DB::table('taxonomies')->insert($childRecords);
        }
    }
}
