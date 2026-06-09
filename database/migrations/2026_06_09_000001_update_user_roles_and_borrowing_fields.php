<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'user')->update(['role' => 'staff']);
        DB::table('users')->where('role', 'approver')->update(['role' => 'owner']);

        Schema::table('borrowings', function (Blueprint $table) {
            $table->date('expected_return_date')->nullable()->after('borrow_date');
            $table->text('description')->nullable()->after('note');
            $table->text('approval_note')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropColumn(['expected_return_date', 'description', 'approval_note']);
        });

        DB::table('users')->where('role', 'staff')->update(['role' => 'user']);
        DB::table('users')->where('role', 'owner')->update(['role' => 'approver']);
    }
};
