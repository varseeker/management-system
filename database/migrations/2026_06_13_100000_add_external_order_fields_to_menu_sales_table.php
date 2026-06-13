<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_sales', function (Blueprint $table) {
            $table->string('external_order_id')->nullable()->after('note');
            $table->string('source')->nullable()->after('external_order_id');
            $table->string('payment_method')->nullable()->after('source');
            $table->string('customer')->nullable()->after('payment_method');

            $table->index('external_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('menu_sales', function (Blueprint $table) {
            $table->dropIndex(['external_order_id']);
            $table->dropColumn(['external_order_id', 'source', 'payment_method', 'customer']);
        });
    }
};
