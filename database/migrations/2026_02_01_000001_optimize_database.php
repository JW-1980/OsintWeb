<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop redundant index on email (unique constraint already indexes it)
            $table->dropIndex(['email']);
        });

        Schema::table('countries', function (Blueprint $table) {
            // Drop redundant index on name (unique constraint already indexes it)
            $table->dropIndex(['name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('email');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->index('name');
        });
    }
};
