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
        // Create pivot table for conflicts and countries
        Schema::create('conflict_countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conflict_id')->constrained('conflicts')->cascadeOnDelete();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['conflict_id', 'country_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the pivot table
        Schema::dropIfExists('conflict_countries');
    }
};
