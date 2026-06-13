<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('borrowing_image_files')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE borrowing_image_files MODIFY contents LONGBLOB NOT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('borrowing_image_files')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE borrowing_image_files MODIFY contents BLOB NOT NULL');
    }
};
