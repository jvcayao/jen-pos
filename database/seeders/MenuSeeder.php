<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Aliziodev\LaravelTaxonomy\Facades\Taxonomy;
use Aliziodev\LaravelTaxonomy\Enums\TaxonomyType;

class MenuSeeder extends Seeder
{
    public function run()
    {
        /**
         * ------------------------------------------------------------
         * 1. DEFINE MENU CATEGORIES + SUBCATEGORIES
         * ------------------------------------------------------------
         */
        $categories = [
            'Appetizers' => [
                'Salads',
                'Soups',
                'Finger Foods',
                'Dips & Sides',
            ],

            'Main Course' => [
                'Beef Dishes',
                'Pork Dishes',
                'Chicken Dishes',
                'Seafood Dishes',
                'Vegetarian Dishes',
                'Rice Meals',
                'Pasta & Noodles',
            ],

            'Breakfast' => [
                'Classic Breakfast Meals',
                'Omelettes',
                'Pancakes & Waffles',
                'Sandwiches',
            ],

            'Burgers & Sandwiches' => [
                'Beef Burgers',
                'Chicken Burgers',
                'Specialty Sandwiches',
            ],

            'Pizza' => [
                'Classic Pizza',
                'Specialty Pizza',
                'Vegetarian Pizza',
            ],

            'Desserts' => [
                'Cakes',
                'Pastries',
                'Ice Cream',
                'Pies',
            ],

            'Beverages' => [
                'Coffee',
                'Tea',
                'Juices',
                'Soft Drinks',
                'Smoothies',
                'Alcoholic Drinks',
            ],

            'Sides' => [
                'Fries',
                'Mashed Potato',
                'Rice Variants',
                'Side Salads',
            ],

            'Kids Menu' => [
                'Kids Meals',
                'Kids Drinks',
                'Kids Desserts',
            ],

            'Combo Meals' => [
                'Solo Combos',
                'Family Sets',
            ],
        ];

        /**
         * ------------------------------------------------------------
         * 2. CREATE TAXONOMIES + TERMS
         * ------------------------------------------------------------
         */
        foreach ($categories as $category => $subcategories) {
            $taxonomy = Taxonomy::create([
                'name' => $category,
                'slug' => Str::slug($category),
                'type' => TaxonomyType::Category->value,
                'description' => $category,
            ]);

            foreach ($subcategories as $sub) {
                $subTaxonomy = Taxonomy::create([
                    'name' => $sub,
                    'slug' => Str::slug($sub),
                    'type' => TaxonomyType::Category->value,
                    'description' => $sub,
                    'parent_id' => $taxonomy->id,
                ]);
            }
        }

    }
}
