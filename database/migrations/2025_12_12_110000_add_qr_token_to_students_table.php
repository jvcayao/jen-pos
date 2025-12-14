<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->uuid('qr_token')->nullable()->unique()->after('is_active');
        });

    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('qr_token');
        });
    }
};
