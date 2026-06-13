<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->unsignedInteger('price')->default(18000)->after('description');
            $table->string('category')->default('Snack')->after('price');
            $table->string('image_path')->nullable()->after('category');
            $table->boolean('most_ordered')->default(false)->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn(['price', 'category', 'image_path', 'most_ordered']);
        });
    }
};
