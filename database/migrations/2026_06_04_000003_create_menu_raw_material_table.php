<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_raw_material', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->unique(['menu_id', 'raw_material_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_raw_material');
    }
};
