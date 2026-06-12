<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['borrow_image', 'return_image'] as $column) {
            DB::table('borrowings')
                ->where($column, 'like', 'uploads/borrowings/%')
                ->update([
                    $column => DB::raw("REPLACE({$column}, 'uploads/', '')"),
                ]);
        }
    }

    public function down(): void
    {
        foreach (['borrow_image', 'return_image'] as $column) {
            DB::table('borrowings')
                ->where($column, 'like', 'borrowings/%')
                ->where($column, 'not like', 'uploads/%')
                ->update([
                    $column => DB::raw("CONCAT('uploads/', {$column})"),
                ]);
        }
    }
};
