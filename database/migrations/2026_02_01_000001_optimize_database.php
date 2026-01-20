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

        Schema::table('events', function (Blueprint $table) {
            // Add spatial index to location column (supported in MySQL 8.0+ even if nullable)
            $table->spatialIndex('location');
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

        Schema::table('events', function (Blueprint $table) {
            $table->dropSpatialIndex(['location']);
        });
    }
};
