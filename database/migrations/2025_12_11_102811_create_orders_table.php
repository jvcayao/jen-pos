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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('uuid')->unique();

            // Order Type
            $table->string('type')->default('system')->nullable();

            // User Log
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');

            $table->foreignId('account_id')->constrained('users')->onDelete('cascade');

            // Order
            $table->foreignId('cashier_id')->nullable()->constrained('users')->onDelete('cascade');

            // Sales From
            $table->string('source')->default('system');

            // Prices
            $table->double('total')->default(0)->nullable();
            $table->double('discount')->default(0)->nullable();
            $table->double('shipping')->default(0)->nullable();
            $table->double('vat')->default(0)->nullable();

            // Status
            $table->enum('status', ['confirm', 'pending', 'void'])->default('pending')->nullable();

            // Options

            // Notes
            $table->text('notes')->nullable();

            // Return
            $table->boolean('is_void')->default(0)->nullable();
            $table->double('return_total')->default(0)->nullable();
            $table->string('reason')->nullable();

            // Payments
            $table->boolean('is_payed')->default(0)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_vendor')->nullable();
            $table->string('payment_vendor_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
