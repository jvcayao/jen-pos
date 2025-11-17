<?php

namespace Database\Seeders;

use Aliziodev\LaravelTaxonomy\Facades\Taxonomy;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // APPETIZERS
            ['Caesar Salad', 150, 'Appetizers', 'Salads'],
            ['Garden Salad', 140, 'Appetizers', 'Salads'],

            ['Cream of Mushroom Soup', 120, 'Appetizers', 'Soups'],
            ['Chicken Noodle Soup', 135, 'Appetizers', 'Soups'],

            ['Chicken Wings', 180, 'Appetizers', 'Finger Foods'],
            ['Calamari', 190, 'Appetizers', 'Finger Foods'],
            ['Mozzarella Sticks', 160, 'Appetizers', 'Finger Foods'],

            ['Nachos & Cheese Dip', 170, 'Appetizers', 'Dips & Sides'],
            ['Garlic Bread', 110, 'Appetizers', 'Dips & Sides'],

            // MAIN COURSE
            ['Beef Steak', 350, 'Main Course', 'Beef Dishes'],
            ['Beef Stroganoff', 320, 'Main Course', 'Beef Dishes'],

            ['Pork BBQ', 220, 'Main Course', 'Pork Dishes'],
            ['Pork Tonkatsu', 250, 'Main Course', 'Pork Dishes'],

            ['Grilled Chicken', 230, 'Main Course', 'Chicken Dishes'],
            ['Fried Chicken', 200, 'Main Course', 'Chicken Dishes'],

            ['Grilled Salmon', 380, 'Main Course', 'Seafood Dishes'],
            ['Fish and Chips', 260, 'Main Course', 'Seafood Dishes'],
            ['Garlic Butter Shrimp', 300, 'Main Course', 'Seafood Dishes'],

            ['Vegetable Stir Fry', 180, 'Main Course', 'Vegetarian Dishes'],
            ['Tofu Teriyaki', 170, 'Main Course', 'Vegetarian Dishes'],

            ['Chicken Teriyaki Rice Bowl', 180, 'Main Course', 'Rice Meals'],
            ['Beef Gyudon', 190, 'Main Course', 'Rice Meals'],
            ['Pork Char Siu Rice', 185, 'Main Course', 'Rice Meals'],

            ['Spaghetti Bolognese', 190, 'Main Course', 'Pasta & Noodles'],
            ['Carbonara', 185, 'Main Course', 'Pasta & Noodles'],
            ['Pad Thai', 200, 'Main Course', 'Pasta & Noodles'],

            // BREAKFAST
            ['Bacon and Eggs', 160, 'Breakfast', 'Classic Breakfast Meals'],
            ['Longganisa Meal', 150, 'Breakfast', 'Classic Breakfast Meals'],

            ['Ham and Cheese Omelette', 140, 'Breakfast', 'Omelettes'],
            ['Veggie Omelette', 130, 'Breakfast', 'Omelettes'],

            ['Buttermilk Pancakes', 160, 'Breakfast', 'Pancakes & Waffles'],
            ['Belgian Waffles', 170, 'Breakfast', 'Pancakes & Waffles'],

            ['Tuna Sandwich', 120, 'Breakfast', 'Sandwiches'],
            ['BLT Sandwich', 130, 'Breakfast', 'Sandwiches'],

            // BURGERS & SANDWICHES
            ['Classic Cheeseburger', 180, 'Burgers & Sandwiches', 'Beef Burgers'],
            ['Double Beef Burger', 220, 'Burgers & Sandwiches', 'Beef Burgers'],

            ['Crispy Chicken Burger', 170, 'Burgers & Sandwiches', 'Chicken Burgers'],
            ['Grilled Chicken Burger', 180, 'Burgers & Sandwiches', 'Chicken Burgers'],

            ['Clubhouse Sandwich', 160, 'Burgers & Sandwiches', 'Specialty Sandwiches'],
            ['Philly Cheesesteak', 210, 'Burgers & Sandwiches', 'Specialty Sandwiches'],

            // PIZZA
            ['Margherita Pizza', 250, 'Pizza', 'Classic Pizza'],
            ['Pepperoni Pizza', 260, 'Pizza', 'Classic Pizza'],

            ['Hawaiian Pizza', 270, 'Pizza', 'Specialty Pizza'],
            ['Meat Lovers Pizza', 295, 'Pizza', 'Specialty Pizza'],

            ['Veggie Supreme Pizza', 260, 'Pizza', 'Vegetarian Pizza'],

            // DESSERTS
            ['Chocolate Cake', 140, 'Desserts', 'Cakes'],
            ['Cheesecake', 160, 'Desserts', 'Cakes'],

            ['Croissant', 90, 'Desserts', 'Pastries'],
            ['Cinnamon Roll', 110, 'Desserts', 'Pastries'],

            ['Vanilla Ice Cream', 70, 'Desserts', 'Ice Cream'],
            ['Chocolate Ice Cream', 70, 'Desserts', 'Ice Cream'],

            ['Apple Pie', 130, 'Desserts', 'Pies'],
            ['Blueberry Pie', 140, 'Desserts', 'Pies'],

            // BEVERAGES
            ['Americano', 80, 'Beverages', 'Coffee'],
            ['Latte', 110, 'Beverages', 'Coffee'],
            ['Cappuccino', 110, 'Beverages', 'Coffee'],

            ['Iced Tea', 55, 'Beverages', 'Tea'],
            ['Green Tea', 60, 'Beverages', 'Tea'],

            ['Orange Juice', 70, 'Beverages', 'Juices'],
            ['Apple Juice', 70, 'Beverages', 'Juices'],

            ['Coke', 40, 'Beverages', 'Soft Drinks'],
            ['Sprite', 40, 'Beverages', 'Soft Drinks'],

            ['Strawberry Smoothie', 120, 'Beverages', 'Smoothies'],
            ['Mango Smoothie', 120, 'Beverages', 'Smoothies'],

            ['Beer', 90, 'Beverages', 'Alcoholic Drinks'],
            ['Red Wine', 150, 'Beverages', 'Alcoholic Drinks'],
            ['White Wine', 150, 'Beverages', 'Alcoholic Drinks'],

            // SIDES
            ['Regular Fries', 60, 'Sides', 'Fries'],
            ['Cheese Fries', 80, 'Sides', 'Fries'],

            ['Classic Mashed Potato', 70, 'Sides', 'Mashed Potato'],

            ['Steamed Rice', 25, 'Sides', 'Rice Variants'],
            ['Garlic Rice', 35, 'Sides', 'Rice Variants'],

            ['Coleslaw', 45, 'Sides', 'Side Salads'],
            ['Potato Salad', 50, 'Sides', 'Side Salads'],

            // KIDS MENU
            ['Kids Spaghetti', 110, 'Kids Menu', 'Kids Meals'],
            ['Mini Chicken Nuggets', 120, 'Kids Menu', 'Kids Meals'],

            ['Chocolate Milk', 45, 'Kids Menu', 'Kids Drinks'],
            ['Kids Apple Juice', 45, 'Kids Menu', 'Kids Drinks'],

            ['Ice Cream Cup', 50, 'Kids Menu', 'Kids Desserts'],

            // COMBO MEALS
            ['Burger Combo Meal', 220, 'Combo Meals', 'Solo Combos'],
            ['Chicken Rice Combo Meal', 210, 'Combo Meals', 'Solo Combos'],

            ['Family Chicken Bucket Set', 699, 'Combo Meals', 'Family Sets'],
            ['Family Pizza & Pasta Set', 799, 'Combo Meals', 'Family Sets'],
        ];

        /**
         * ------------------------------------------------------------
         * 4. CREATE PRODUCTS + ATTACH TAXONOMIES
         * ------------------------------------------------------------
         */
        foreach ($products as [$name, $price, $category, $subcategory]) {

            $cat = Taxonomy::findBySlug(Str::slug($category));
            $sub = Taxonomy::findBySlug(Str::slug($subcategory));

            if (! $sub) {
                continue;
            }

            $product = Product::create([
                'name' => $name,
                'price' => $price,
                'slug' => Str::slug($name),
                'description' => $name,
            ]);

            $product->attachTaxonomies($sub);
        }
    }
}
