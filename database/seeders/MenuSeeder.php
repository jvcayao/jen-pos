<?php

namespace Database\Seeders;

use Aliziodev\LaravelTaxonomy\Enums\TaxonomyType;
use Aliziodev\LaravelTaxonomy\Facades\Taxonomy;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    public function run()
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

        // Get all stores and create categories for each
        $stores = Store::all();

        foreach ($stores as $store) {
            foreach ($categories as $category => $subcategories) {
                // Use store_id in slug to ensure uniqueness across stores
                // This allows filtering by store_id while maintaining unique slugs
                $categorySlug = Str::slug($category).'-'.$store->id;

                $taxonomy = Taxonomy::create([
                    'name' => $category,
                    'slug' => $categorySlug,
                    'type' => TaxonomyType::Category->value,
                    'description' => $category,
                    'store_id' => $store->id,
                ]);

                foreach ($subcategories as $sub) {
                    $subSlug = Str::slug($sub).'-'.$store->id;

                    Taxonomy::create([
                        'name' => $sub,
                        'slug' => $subSlug,
                        'type' => TaxonomyType::Category->value,
                        'description' => $sub,
                        'parent_id' => $taxonomy->id,
                        'store_id' => $store->id,
                    ]);
                }
            }
        }
    }
}
