<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\Product;
use App\Scopes\StoreScope;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Aliziodev\LaravelTaxonomy\Models\Taxonomy;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Cakes Breads & Pizza'
            ['Cakes Breads & Pizza', 'Bread with toppings', 'Pan de Sal with Kesong Puti'],
            ['Cakes Breads & Pizza', 'Bread with toppings', 'Pan de Sal with Liver Spread'],
            ['Cakes Breads & Pizza', 'Bread with toppings', 'Pandesal with Cheese'],
            ['Cakes Breads & Pizza', 'Bread with toppings', 'Pandesal with Peanut Butter'],
            ['Cakes Breads & Pizza', 'Regular Bread', 'Cheese Bread'],
            ['Cakes Breads & Pizza', 'Regular Bread', 'Spanish Bread'],
            ['Cakes Breads & Pizza', 'Regular Bread', 'Ensaymada'],
            ['Cakes Breads & Pizza', 'Regular Bread', 'Ube Cheese Roll'],
            ['Cakes Breads & Pizza', 'Pizzas', 'Mini Pizzas (Filipino Style)'],
            ['Cakes Breads & Pizza', 'Regular Bread', 'Pandecoco'],
            ['Cakes Breads & Pizza', 'Regular Bread', 'Crossini'],
            ['Cakes Breads & Pizza', 'Cookies', 'Chocolate Crinkles'],
            ['Cakes Breads & Pizza', 'Regular Bread', 'Choco Buns'],
            ['Cakes Breads & Pizza', 'Regular Bread', 'Kabayan'],
            ['Cakes Breads & Pizza', 'Regular Bread', 'Mongo Bread'],
            ['Cakes Breads & Pizza', 'Cakes', 'Banana Cake'],
            ['Cakes Breads & Pizza', 'Cakes', 'Carrot Cake'],
            ['Cakes Breads & Pizza', 'Cakes', 'Chocolate Cake'],
            ['Cakes Breads & Pizza', 'Cookies', 'Regular Cookies'],
            ['Cakes Breads & Pizza', 'Muffins', 'Muffins'],
            ['Cakes Breads & Pizza', 'Cookies', 'Cupcakes'],
            ['Cakes Breads & Pizza', 'Waffles', 'Waffle with banana choco filling'],
            ['Cakes Breads & Pizza', 'Donuts', 'Bavarian'],
            ['Cakes Breads & Pizza', 'Donuts', 'Mini Donuts'],
            ['Cakes Breads & Pizza', 'Brownies', 'Banana Brownie'],
            ['Cakes Breads & Pizza', 'Muffins', 'Banana muffins'],
            ['Cakes Breads & Pizza', 'Brownies', 'Chocolate Brownie'],
            ['Cakes Breads & Pizza', 'Pizzas', 'Baked Pizzas'],
            ['Cakes Breads & Pizza', 'Baked', 'Baked Garlic Bread'],
            ['Cakes Breads & Pizza', 'Baked', 'Cinnamon Rolls'],

            // Snacks & Desserts
            ['Snacks & Dessert', 'Rice Cakes', 'Puto'],
            ['Snacks & Dessert', 'Rice Cakes', 'Puto Maya and Sikwate'],
            ['Snacks & Dessert', 'Rice Cakes', 'Suman'],
            ['Snacks & Dessert', 'Snacks', 'Taho'],
            ['Snacks & Dessert', 'Rice Cakes', 'Kutsinta'],
            ['Snacks & Dessert', 'Rice Cakes', 'Puto Cheese'],
            ['Snacks & Dessert', 'Rice Cakes', 'Puto Ube'],
            ['Snacks & Dessert', 'Rice Cakes', 'Biko'],
            ['Snacks & Dessert', 'Rice Cakes', 'Bibingka'],
            ['Snacks & Dessert', 'Rice Cakes', 'Cassava Cake'],
            ['Snacks & Dessert', 'Rice Cakes', 'Palitaw'],
            ['Snacks & Dessert', 'Snacks', 'Lumpiang Togue (fried)'],
            ['Snacks & Dessert', 'Snacks', 'Lumpiang Sariwa'],
            ['Snacks & Dessert', 'Snacks', 'Lumpiang Ubod'],
            ['Snacks & Dessert', 'Snacks', 'Empanada'],
            ['Snacks & Dessert', 'Snacks', 'Ukoy (Kalabasa Fritters)'],
            ['Snacks & Dessert', 'Snacks', 'Turon'],
            ['Snacks & Dessert', 'Snacks', 'Banana Cue'],
            ['Snacks & Dessert', 'Snacks', 'Camote Cue'],
            ['Snacks & Dessert', 'Snacks', 'Maruya'],
            ['Snacks & Dessert', 'Desserts', 'Binignit'],
            ['Snacks & Dessert', 'Desserts', 'Fruit Salad in a Cup'],
            ['Snacks & Dessert', 'Desserts', 'Mais con Yelo'],
            ['Snacks & Dessert', 'Desserts', 'Buko Pandan in a Cup'],
            ['Snacks & Dessert', 'Desserts', 'Leche Flan'],
            ['Snacks & Dessert', 'Snacks', 'Mini Cheese Sticks'],
            ['Snacks & Dessert', 'Snacks', 'Mini Turon with Langka'],
            ['Snacks & Dessert', 'Desserts', 'Sweet Corn'],
            ['Snacks & Dessert', 'Desserts', 'Ube Halaya'],
            ['Snacks & Dessert', 'Snacks', 'Mini Hopia'],
            ['Snacks & Dessert', 'Snacks', 'Chichirya Packs'],
            ['Snacks & Dessert', 'Snacks', 'Peanut Brittle'],
            ['Snacks & Dessert', 'Desserts', 'Kalamay'],
            ['Snacks & Dessert', 'Desserts', 'Daral'],
            ['Snacks & Dessert', 'Snacks', 'Kamote Cue'],
            ['Snacks & Dessert', 'Snacks', 'Ginataan Bilo-bilo'],
            ['Snacks & Dessert', 'Snacks', 'Kalabasa Ball'],
            ['Snacks & Dessert', 'Snacks', 'Pinoy Pancake'],
            ['Snacks & Dessert', 'Snacks', 'Pinoy Corndog'],
            ['Snacks & Dessert', 'Snacks', 'Ordinary Waffle'],
            ['Snacks & Dessert', 'Desserts', 'Maja Blanca'],
            ['Snacks & Dessert', 'Snacks', 'Pork Siomai'],
            ['Snacks & Dessert', 'Snacks', 'Japanese Siomai'],
            ['Snacks & Dessert', 'Snacks', 'Beef Siomai'],
            ['Snacks & Dessert', 'Snacks', 'Chicken Siomai'],
            ['Snacks & Dessert', 'Snacks', 'Dumplings'],
            ['Snacks & Dessert', 'Snacks', 'Hotdog Blanket'],
            ['Snacks & Dessert', 'Snacks', 'Baked Macaroni'],
            ['Snacks & Dessert', 'Snacks', 'Kwek-Kwek'],
            ['Snacks & Dessert', 'Snacks', 'Churros'],
            ['Snacks & Dessert', 'Snacks', 'Mojos'],
            ['Snacks & Dessert', 'Desserts', 'Ice cream style banana cake'],
            ['Snacks & Dessert', 'Snacks', 'Shawarma'],
            ['Snacks & Dessert', 'Snacks', 'Siopao'],
            ['Snacks & Dessert', 'Snacks', 'Baked Hotdog in a blanket'],
            ['Snacks & Dessert', 'Snacks', 'Potato Hash Browns'],
            ['Snacks & Dessert', 'Snacks', 'Regular Corndog'],

            // Burger & Sandwiches
            ['Burger & Sandwiches', 'Specialty Sandwich', 'Chicken Salad Sandwich'],
            ['Burger & Sandwiches', 'Specialty Sandwich', 'Clubhouse Sandwich'],
            ['Burger & Sandwiches', 'Specialty Sandwich', 'Egg Sandwich'],
            ['Burger & Sandwiches', 'Specialty Sandwich', 'Tuna Salad Sandwich'],
            ['Burger & Sandwiches', 'Specialty Sandwich', 'Tuna Sandwich'],
            ['Burger & Sandwiches', 'Specialty Sandwich', 'Ham and Cheese Sandwich'],
            ['Burger & Sandwiches', 'Specialty Sandwich', 'Hotdog Sandwich'],
            ['Burger & Sandwiches', 'Burgers', 'Mini Burger (with cheese)'],

            // Breakfast Meal'
            ['Breakfast Meal', 'Congee / Porridge', 'Arroz a la Cubana'],
            ['Breakfast Meal', 'Congee / Porridge', 'Arroz Caldo'],
            ['Breakfast Meal', 'Silog Meal', 'Bangsilog'],
            ['Breakfast Meal', 'Silog Meal', 'Beef Tapa with Egg'],
            ['Breakfast Meal', 'Congee / Porridge', 'Champorado'],
            ['Breakfast Meal', 'Congee / Porridge', 'Goto'],
            ['Breakfast Meal', 'Silog Meal', 'Chicken Tocino'],
            ['Breakfast Meal', 'Silog Meal', 'Chicksilog'],
            ['Breakfast Meal', 'Congee / Porridge', 'Lugaw with Egg'],
            ['Breakfast Meal', 'Regular Breakfast', 'Corned Beef Hash'],
            ['Breakfast Meal', 'Regular Breakfast', 'Corned Beef Omelet'],
            ['Breakfast Meal', 'Regular Breakfast', 'Cornsilog'],
            ['Breakfast Meal', 'Regular Breakfast', 'Daing'],
            ['Breakfast Meal', 'Silog Meal', 'Daingsilog'],
            ['Breakfast Meal', 'Regular Breakfast', 'Ginataang Mais'],
            ['Breakfast Meal', 'Silog Meal', 'Hotsilog'],
            ['Breakfast Meal', 'Regular Breakfast', 'Kinilaw na Isda'],
            ['Breakfast Meal', 'Silog Meal', 'Longsilog'],
            ['Breakfast Meal', 'Congee / Porridge', 'Lugaw'],
            ['Breakfast Meal', 'Regular Breakfast', "'Poqui-Poqui (Eggplant, Tomato, and Egg dish)'"],
            ['Breakfast Meal', 'Silog Meal', 'Porksilog'],
            ['Breakfast Meal', 'Regular Breakfast', 'Scrambled Eggs'],
            ['Breakfast Meal', 'Silog Meal', 'Sinangag'],
            ['Breakfast Meal', 'Regular Breakfast', 'Tinapa'],
            ['Breakfast Meal', 'Silog Meal', 'Tocilog'],
            ['Breakfast Meal', 'Regular Breakfast', 'Tortang Talong (Eggplant Omelette)'],
            ['Breakfast Meal', 'Regular Breakfast', 'Tuyo and Egg'],
            ['Breakfast Meal', 'Pancakes', 'Mini Pancakes with Choco Syrup'],
            ['Breakfast Meal', 'Regular Breakfast', 'Pastil Rice Wrap'],
            ['Breakfast Meal', 'Regular Breakfast', 'Mac and Cheese'],

            // Soups & Noodles
            ['Soups & Noodles', 'Beef Noodles', 'Beef Mami'],
            ['Soups & Noodles', 'Chicken Noodles', 'Chicken Mami'],
            ['Soups & Noodles', 'Pancit', 'Pancit Bihon'],
            ['Soups & Noodles', 'Pancit', 'Pancit Canton'],
            ['Soups & Noodles', 'Pancit', 'Pancit Bihon Canton Combination'],
            ['Soups & Noodles', 'Pancit', 'Pancit Sotanghon'],
            ['Soups & Noodles', 'Pancit', 'Palabok'],
            ['Soups & Noodles', 'Spaghettis', 'Spaghetti'],
            ['Soups & Noodles', 'Spaghettis', 'Carbonarra'],
            ['Soups & Noodles', 'Soups', 'Chicken Sopas'],
            ['Soups & Noodles', 'Korean Noodles', 'Korean Japchae'],
            ['Soups & Noodles', 'Chicken Noodles', 'Chicken Lapaz Bachoy'],
            ['Soups & Noodles', 'Soups', 'Molo Soup'],
            ['Soups & Noodles', 'Chicken Noodles', 'Chicken Sotanghon'],
            ['Soups & Noodles', 'Soups', 'Cream of Mushroom Soup'],
            ['Soups & Noodles', 'Soups', 'Cream of Corn Soup'],
            ['Soups & Noodles', 'Soups', 'Tomato Soup'],
            ['Soups & Noodles', 'Soups', 'Pumpkin Soup (Mild and Sweet)'],
            ['Soups & Noodles', 'Soups', 'Chicken and Vegetable Soup'],
            ['Soups & Noodles', 'Soups', 'Cream of Broccoli Soup'],
            ['Soups & Noodles', 'Soups', 'Minestrone (Vegetable Pasta Soup)'],
            ['Soups & Noodles', 'Soups', 'Vegetable Noodle Soup'],
            ['Soups & Noodles', 'Soups', 'Cheesy Potato Soup'],
            ['Soups & Noodles', 'Soups', 'Carrot and Squash Soup'],
            ['Soups & Noodles', 'Soups', 'Egg Drop Soup'],
            ['Soups & Noodles', 'Soups', 'Wonton Soup (Mild)'],
            ['Soups & Noodles', 'Soups', "'French Onion Soup (Sweet, Kid-Version)'"],
            ['Soups & Noodles', 'Soups', 'Macaroni Chicken Soup'],
            ['Soups & Noodles', 'Soups', 'Vegetable Chowder'],
            ['Soups & Noodles', 'Soups', 'Sweet Corn and Egg Soup'],
            ['Soups & Noodles', 'Soups', 'Lentil Soup (Mild)'],
            ['Soups & Noodles', 'Soups', 'Clear Veggie Soup with Pasta Shells'],
            ['Soups & Noodles', 'Soups', 'Zucchini Cream Soup'],
            ['Soups & Noodles', 'Soups', 'Asian Vegetable Soup with Tofu'],
            ['Soups & Noodles', 'Soups', 'Cream of Spinach Soup'],
            ['Soups & Noodles', 'Soups', 'Cauliflower and Cheese Soup'],
            ['Soups & Noodles', 'Soups', 'Rice Soup with Vegetables (Congee Style)'],
            ['Soups & Noodles', 'Soups', 'Broccoli and Pea Soup'],
            ['Soups & Noodles', 'Soups', 'Butternut Squash and Apple Soup (Sweetened)'],

            // Main Dishes & Sides
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork Adobo'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork with Mushroom'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork BBQ'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Lumpia Shanghai'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork Menudo'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork Humba'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork Caldereta'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork Guisado'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Lechon Kawali'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork Sisig'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork Tocino'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Paksiw na Lechon'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pata Tim'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pininyahang Baboy'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork Estofado'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Kinamatisang Baboy'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Torta'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Spicy Pork Laing'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Bagnet'],
            ['Main Dishes & Sides', 'Ceviche Dishes', 'Pork Kilawin'],
            ['Main Dishes & Sides', 'Grilled Dishes', 'Inihaw na Liempo'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Embutido'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Hamónado'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Longganisa'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pochero'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork Sarsiado'],
            ['Main Dishes & Sides', 'Grilled Dishes', 'Inasal na Baboy'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Adobo Chicken'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Tinola chicken'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Sinampalukang Manok'],
            ['Main Dishes & Sides', 'Grilled Dishes', 'Chicken Inasal'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Pininyahang Manok'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Lechon Manok'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Kalderetang Manok'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Olongapo Chicken'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Paksiw na Manok'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Tausug Chicken Piyanggang'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Afritada manok'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Estofadong Manok'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Binakol manok'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Suam na Mais with Chicken'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Fried Chicken'],
            ['Main Dishes & Sides', 'Grilled Dishes', 'Chicken Barbecue'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Chicken Kansi'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Chicken Arroz Valenciana'],
            ['Main Dishes & Sides', 'Mix Dishes', 'Bringhe'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Kulma'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Piaparan'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Sinigang sa Miso with Chicken'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Tinolang Papaya with Chicken'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Ginataang Manok'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Chicken Pastel'],
            ['Main Dishes & Sides', 'Mix Dishes', 'Morcon'],
            ['Main Dishes & Sides', 'Mix Dishes', 'Embutido'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Beef Sinigang'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Kare-Kare beef'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Beef Caldereta'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Pinoy Beef Steak (Bistek Tagalog)'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Bulalo beef'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Mechado beef'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Adobong Baka'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Pochero beef'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Nilagang Baka'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Paksiw na Baka'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Sinampalukang Baka'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Suam na Baka'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Linagang Mais na may Baka'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Beef Pares'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Sinigang na Isda'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Kinilaw na Isda'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Pinangat na Isda'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Escabeche'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Relyenong Bangus'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Inihaw na Bangus'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Pakfry'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Sarciadong Isda'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Pesang Isda'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Ginataang Isda'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Paksiw na Bangus'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Laing with Fish'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Fish Fillet with Tartar Sauce'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Sweet and Sour Fish'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Fried Tilapia'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Steamed Fish with Ginger and Soy Sauce'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Pinakbet with Fish'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Ukoy (Shrimp and Vegetable Fritters)'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Bola-Bola (Fish Balls)'],
            ['Main Dishes & Sides', 'Grilled Dishes', 'Tuna Belly Inihaw'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Chicken Adobo with Sitaw (string beans)'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Tinola with Sayote and Malunggay'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Sinampalukang Manok with Gabi (taro) and other vegetables'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Ginataang Manok with Papaya and Spinach'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Suam na Mais with Chicken and Leafy Greens'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Chicken Curry'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Ginisang Baboy with Patola: (Pork sauteed with sponge gourd)'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork with Sayote'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork with Sitaw'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork with Kangkong'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork with Repolyo'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork with Pechay'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork with Mustasa'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork and Bamboo Shoots'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork and Okra Stew'],
            ['Main Dishes & Sides', 'Mix Dishes', 'Pork with Mixed Root Vegetables'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork with Monggo'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Beef with Broccoli'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Ginisang Repolyo with Beef'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Beef and Mushroom Stir-fry'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Pinakbet with Beef'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Beef Stroganoff Filipino Style'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Sinigang na Salmon sa Miso'],
            ['Main Dishes & Sides', 'Mix Dishes', 'Ginataang Gulay with Shrimp and Fish'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Pinakbet Tagalog with Fried Fish'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Bulanglang with Fried Fish'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Laswa with Fried Fish'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Ensaladang Lato with Grilled Fish'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Chop Suey with Fish Fillet'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Sayote Guisado with Flaked Fish'],
            ['Main Dishes & Sides', 'Mix Dishes', 'Tortang Talong with Sardines'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Utan Bisaya with Grilled Fish'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Chopsuey with Chicken'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Beef with Ampalaya'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork Sinigang'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Burger Steak'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Picadillo'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork Steak'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Nilagang Pork'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Meatballs'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Chicken Asado'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Beef Salpicao'],
            ['Main Dishes & Sides', 'Seafood Dishes', 'Adobong Pusit'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Giniling with Beans'],
            ['Main Dishes & Sides', 'Pork Dishes', 'KBL'],
            ['Main Dishes & Sides', 'Chicken Dishes', 'Chicken Turbo'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Igado'],
            ['Main Dishes & Sides', 'Salads', 'Filipino Caesar Salad'],
            ['Main Dishes & Sides', 'Salads', 'Potato Salad with Hard-Boiled Eggs and Sweet Pickle Relish'],
            ['Main Dishes & Sides', 'Salads', 'Filipino Coleslaw with Pineapple or Green Mango'],
            ['Main Dishes & Sides', 'Salads', 'Broccoli and Cauliflower Salad with Crispy Chicharon and Sweet Vinegar Dressing'],
            ['Main Dishes & Sides', 'Salads', 'Green Bean Salad with Calamansi Vinaigrette'],
            ['Main Dishes & Sides', 'Salads', 'Beetroot Salad with Kesong Puti'],
            ['Main Dishes & Sides', 'Salads', 'Mediterranean Salad with Local Greens and Cane Vinegar Dressing'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Tortang Pork Giniling'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork and Okra Stew: (Pork stew with okra)'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Pork with Mixed Root Vegetables'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Sayote Guisado with Beef'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Laing with Beef'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Chopsuey with Beef'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Water Spinach (Kangkong) with Beef in Oyster Sauce'],
            ['Main Dishes & Sides', 'Beef Dishes', 'String Beans (Ginisang Sitaw) with Beef'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Monggo Guisado with Beef'],
            ['Main Dishes & Sides', 'Pork Dishes', 'Chopsuey with pork'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Ginisang Sayote'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Ginisang Repolyo (Cabbage)'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Ginisang Pechay'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Ginisang Sitaw at Kalabasa'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Ginisang Baguio Beans'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Ginisang Talong at Kamatis'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Ginisang Ampalaya'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Chopsuey'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Pinakbet'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Adobong Sitaw'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Adobong Kangkong'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Dinengdeng'],
            ['Main Dishes & Sides', 'Vegetable Dishes', "'Laing (mild, less spicy)'"],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Lumpiang Gulay'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Mixed Buttered Vegetables'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Ginataang Kalabasa at Sitaw'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Stir-fried Tofu with Vegetables'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Nilagang Gulay'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Tortang Talong'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Tortang Kalabasa'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Tortang Sayote'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Munggo Guisado with Malunggay'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Sautéed Malunggay with Egg'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Sautéed Kangkong with Garlic'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Ensaladang Talong (mild, mashed style)'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Fried Tokwa with Kangkong'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Boiled Okra with Bagoong (optional dip)'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Vegetable Curry (mild and creamy)'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Utan Bisaya (boiled local veggies)'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Ginataang Langka (softened for kids)'],
            ['Main Dishes & Sides', 'Beef Dishes', 'Repolyo with Corned Beef'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Miswa with Patola'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Sayote with Egg'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Vegetable Tempura (Kalabasa, Carrot, Talong)'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Sweet & Sour Tofu with Veggies'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Upo with Shrimp (Mild)'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Stir-Fried Broccoli with Garlic'],
            ['Main Dishes & Sides', 'Rice Dishes', 'Vegetable Fried Rice'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Vegetable Spring Rolls'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Mashed Potatoes with Gravy'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Creamed Corn'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Baked Macaroni with Veggies'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Grilled Zucchini with Parmesan'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Carrot and Corn Fritters'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Broccoli and Cheese Casserole'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Roasted Mixed Vegetables (Carrot, Potato, Bell Pepper)'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Vegetable Lasagna (Mild)'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Cabbage Stir Fry (Asian Style)'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Sautéed Spinach with Garlic'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Cheesy Cauliflower Bake'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Baked Eggplant Parmesan'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Vegetable Stir Fry with Oyster Sauce'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Zucchini and Carrot Fritters'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Baked Potato Wedges with Herbs'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Buttered Corn and Carrots'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Tofu and Vegetable Stir Fry'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Vegetable Pasta in Tomato Sauce'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Coleslaw (Kid-Friendly, Less Vinegar)'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Steamed Vegetables with Cheese Dip'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Cheesy Broccoli and Rice Bake'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Hash Browns with Veggies'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Mac & Cheese with Hidden Veggies'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Corn and Carrot Medley'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Sweet Potato Fries (Baked)'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Vegetable Patties'],
            ['Main Dishes & Sides', 'Vegetable Dishes', 'Cauliflower Nuggets'],

            // Fruits & Drinks
            ['Fruits & Drinks', 'Fruits', 'Mango'],
            ['Fruits & Drinks', 'Fruits', 'Pineapple'],
            ['Fruits & Drinks', 'Fruits', 'Banana'],
            ['Fruits & Drinks', 'Fruits', 'Avocado'],
            ['Fruits & Drinks', 'Fruits', 'Watermelon'],
            ['Fruits & Drinks', 'Fruits', 'Orange'],
            ['Fruits & Drinks', 'Cold Drinks', 'Sagot Gulaman'],
            ['Fruits & Drinks', 'Cold Drinks', 'Yakult'],
            ['Fruits & Drinks', 'Cold Drinks', 'Red Iced Tea'],
            ['Fruits & Drinks', 'Fruits', 'Lychee'],
            ['Fruits & Drinks', 'Cold Drinks', 'Blue Lemonade'],
            ['Fruits & Drinks', 'Fruits', 'Apple'],
            ['Fruits & Drinks', 'Fruits', 'Pear'],
            ['Fruits & Drinks', 'Fruits', 'Papaya'],
            ['Fruits & Drinks', 'Fruits', 'Chico'],
            ['Fruits & Drinks', 'Fruits', 'Pomelo'],
            ['Fruits & Drinks', 'Fruits', 'Dragon Fruit'],
            ['Fruits & Drinks', 'Cold Drinks', 'Gulaman Juice'],
            ['Fruits & Drinks', 'Cold Drinks', 'Mango Sago'],

        ];

        // Get all stores
        $stores = Store::all();

        foreach ($stores as $store) {
            $this->seedProductsForStore($store, $products);
        }
    }

    /**
     * Seed products for a specific store using bulk operations.
     *
     * @param  array<int, array{0: string, 1: string, 2: string}>  $products
     */
    private function seedProductsForStore(Store $store, array $products): void
    {
        // Load all taxonomies for this store upfront (keyed by slug)
        $taxonomies = Taxonomy::where('store_id', $store->id)
            ->pluck('id', 'slug')
            ->toArray();

        // Load existing product slugs for this store
        $existingSlugs = Product::withoutGlobalScope(StoreScope::class)
            ->where('store_id', $store->id)
            ->pluck('slug')
            ->flip()
            ->toArray();

        $productsToInsert = [];
        $taxonomyAttachments = [];
        $now = now();

        foreach ($products as [$category, $subcategory, $name]) {
            $subSlug = Str::slug($subcategory).'-'.$store->id;
            $productSlug = Str::slug($name).'-'.$store->id;

            // Skip if taxonomy doesn't exist or product already exists
            if (!isset($taxonomies[$subSlug]) || isset($existingSlugs[$productSlug])) {
                continue;
            }

            $productsToInsert[] = [
                'store_id' => $store->id,
                'name' => $name,
                'price' => rand(50, 200),
                'slug' => $productSlug,
                'description' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Store taxonomy ID for later attachment
            $taxonomyAttachments[$productSlug] = $taxonomies[$subSlug];
        }

        if (empty($productsToInsert)) {
            return;
        }

        // Bulk insert products in chunks to avoid memory issues
        foreach (array_chunk($productsToInsert, 100) as $chunk) {
            DB::table('products')->insert($chunk);
        }

        // Fetch the newly created products to get their IDs
        $newProducts = Product::withoutGlobalScope(StoreScope::class)
            ->where('store_id', $store->id)
            ->whereIn('slug', array_keys($taxonomyAttachments))
            ->pluck('id', 'slug')
            ->toArray();

        // Build taxonomy attachment records
        $taxonomableRecords = [];
        foreach ($newProducts as $slug => $productId) {
            if (isset($taxonomyAttachments[$slug])) {
                $taxonomableRecords[] = [
                    'taxonomy_id' => $taxonomyAttachments[$slug],
                    'taxonomable_type' => Product::class,
                    'taxonomable_id' => $productId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Bulk insert taxonomy attachments
        if (!empty($taxonomableRecords)) {
            foreach (array_chunk($taxonomableRecords, 100) as $chunk) {
                DB::table('taxonomables')->insert($chunk);
            }
        }
    }
}
