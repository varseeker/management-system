<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_orders', function (Blueprint $table) {
            $table->id();
            $table->string('external_order_id')->unique();
            $table->unsignedBigInteger('pos_order_id')->nullable();
            $table->string('customer')->nullable();
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('amount_paid')->default(0);
            $table->unsignedInteger('amount_change')->default(0);
            $table->string('order_status')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('cashier_name')->nullable();
            $table->string('source')->nullable();
            $table->timestamp('ordered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pos_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_order_id')->constrained('pos_orders')->cascadeOnDelete();
            $table->string('menu_code')->nullable();
            $table->string('menu_name');
            $table->unsignedInteger('menu_price')->default(0);
            $table->unsignedInteger('quantity');
            $table->string('variant')->nullable();
            $table->string('size')->nullable();
            $table->string('ice')->nullable();
            $table->string('sugar')->nullable();
            $table->unsignedInteger('subtotal')->default(0);
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_order_items');
        Schema::dropIfExists('pos_orders');
    }
};
