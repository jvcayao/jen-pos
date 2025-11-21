<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug')->unique()->index();
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();

            $table->decimal('price', 10, 2)->default(0);
            $table->double('discount')->default(0)->nullable();
            $table->datetime('discount_to')->nullable();
            $table->double('vat')->default(0)->nullable();
            $table->boolean('is_activated')->default(1)->nullable();
            $table->boolean('is_in_stock')->default(1)->nullable();
            $table->boolean('has_unlimited_stock')->default(0)->nullable();
            $table->boolean('has_max_cart')->default(0)->nullable();
            $table->bigInteger('min_cart')->nullable()->unsigned();
            $table->bigInteger('max_cart')->nullable()->unsigned();
            $table->boolean('has_stock_alert')->default(0)->nullable();
            $table->bigInteger('min_stock_alert')->nullable()->unsigned();
            $table->bigInteger('max_stock_alert')->nullable()->unsigned();
            $table->string('image_path')->nullable();
            $table->timestamps();

            // No foreign key constraints to taxonomy table to keep package-decoupled
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
