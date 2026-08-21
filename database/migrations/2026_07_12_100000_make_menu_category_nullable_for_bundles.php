<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus') || ! Schema::hasColumn('menus', 'category')) {
            return;
        }

        Schema::table('menus', function (Blueprint $table) {
            $table->string('category')->nullable()->default(null)->change();
        });

        DB::table('menus')->where('category', 'Snack')->update(['category' => 'Makanan']);
        DB::table('menus')->whereIn('category', ['Coffee', 'Non-coffee'])->update(['category' => 'Minuman']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('menus') || ! Schema::hasColumn('menus', 'category')) {
            return;
        }

        DB::table('menus')->whereNull('category')->update(['category' => 'Makanan']);

        Schema::table('menus', function (Blueprint $table) {
            $table->string('category')->default('Makanan')->nullable(false)->change();
        });
    }
};
