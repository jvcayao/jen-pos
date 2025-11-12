<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('image_path')->nullable();
            $table->uuid('category_id')->nullable()->index();
            $table->timestamps();

            // No foreign key constraints to taxonomy table to keep package-decoupled
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
