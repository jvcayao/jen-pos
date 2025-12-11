<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders_items', function (Blueprint $table) {
            $table->id();

            // Order
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            // Customer
            $table->foreignId('account_id')->constrained('users')->onDelete('cascade');

            // Product
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');

            // Item Info
            $table->string('item')->nullable();
            $table->double('price')->default(0)->nullable();
            $table->double('discount')->default(0)->nullable();
            $table->double('vat')->default(0)->nullable();
            $table->double('total')->default(0)->nullable();
            $table->double('returned')->default(0)->nullable();

            // Qty
            $table->double('qty')->default(1)->nullable();
            $table->double('returned_qty')->default(0)->nullable();

            // Options
            $table->boolean('is_free')->default(0)->nullable();
            $table->boolean('is_returned')->default(0)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders_items');
    }
};
