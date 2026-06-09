<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {

            $table->string('return_condition')
                ->nullable();

            $table->text('return_note')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {

            $table->dropColumn([
                'return_condition',
                'return_note'
            ]);
        });
    }
};
