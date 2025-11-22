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
                'Baked'
            ],

            'Snacks & Dessert' => [
                'Rice Cakes',
                'Snacks',
                'Desserts',

            ],

            'Burger & Sandwiches' => [
                'Specialty Sandwich',
                'Burgers'
            ],

            'Breakfast Meal' => [
                'Congee / Porridge',
                'Silog Meal',
                'Regular Breakfast',
                'Pancakes'

            ],

            'Soups & Noodles' => [
                'Beef Noodles',
                'Chicken Noodles',
                'Pancit',
                'Spaghettis',
                'Korean Noodles',
                'Soups'

            ],

            'Main Dishes & Sides' => [
                'Chicken Dishes',
                'Pork Dishes',
                'Vegetable Dishes',
                'Grilled Dishes',
                'Ceviche Dishes',
                'Beef Dishes',
                'Rice Dishes',
                'Salads'
            ],

            'Fruits & Drinks' => [
                'Cold Drinks',
                'Hot Drinks',
                'Fruits'
            ]
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
