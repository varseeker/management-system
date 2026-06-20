<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        foreach (['borrow_image', 'return_image'] as $column) {
            DB::table('borrowings')
                ->where($column, 'like', 'borrowings/%')
                ->update([
                    $column => DB::raw("CONCAT('uploads/', {$column})"),
                ]);
        }
    }

    public function down(): void
    {
        foreach (['borrow_image', 'return_image'] as $column) {
            DB::table('borrowings')
                ->where($column, 'like', 'uploads/borrowings/%')
                ->update([
                    $column => DB::raw("SUBSTRING({$column}, 9)"),
                ]);
        }
    }
};
