<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrowing_image_files', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->string('mime_type', 64)->default('image/jpeg');
            $table->binary('contents');
            $table->timestamps();
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE borrowing_image_files MODIFY contents LONGBLOB NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('borrowing_image_files');
    }
};
