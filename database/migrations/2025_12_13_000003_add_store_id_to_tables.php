<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add store_id to users (nullable for head office admin)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->nullOnDelete();
        });

        // Add store_id to products
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->cascadeOnDelete();
        });

        // Add store_id to taxonomies (categories)
        Schema::table('taxonomies', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->cascadeOnDelete();
        });

        // Add store_id to students
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->cascadeOnDelete();
        });

        // Add store_id to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->cascadeOnDelete();
        });

        // Add store_id to orders_items
        Schema::table('orders_items', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->cascadeOnDelete();
        });

        // Add store_id to discount_codes
        Schema::table('discount_codes', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->cascadeOnDelete();
        });

        // Add store_id to carts
        if (Schema::hasTable('carts')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->cascadeOnDelete();
            });
        }

        // Add store_id to cart_items
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });

        Schema::table('taxonomies', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });

        Schema::table('orders_items', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });

        Schema::table('discount_codes', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });

        if (Schema::hasTable('carts') && Schema::hasColumn('carts', 'store_id')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            });
        }

        if (Schema::hasTable('cart_items') && Schema::hasColumn('cart_items', 'store_id')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            });
        }
    }
};
