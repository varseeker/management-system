<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus') || Schema::hasColumn('menus', 'is_bundle')) {
            return;
        }

        Schema::table('menus', function (Blueprint $table) {
            $table->boolean('is_bundle')->default(false)->after('most_ordered');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('menus') || ! Schema::hasColumn('menus', 'is_bundle')) {
            return;
        }

        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('is_bundle');
        });
    }
};
