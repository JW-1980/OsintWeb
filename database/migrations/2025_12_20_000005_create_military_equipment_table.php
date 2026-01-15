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
        Schema::create('military_equipment', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('designation')->comment('Official designation');
            $table->string('nato_designation')->nullable()->comment('NATO reporting name');
            $table->string('common_name')->nullable();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('equipment_categories')->cascadeOnDelete();
            $table->json('specifications')->nullable()->comment('Technical specifications');
            $table->year('introduced_year')->nullable();
            $table->integer('estimated_produced')->nullable()->comment('Estimated total produced');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('designation');
            $table->index('common_name');
            $table->index('country_id');
            $table->index('category_id');
            $table->index('introduced_year');
            $table->fullText(['designation', 'nato_designation', 'common_name', 'description'], 'equipment_search_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('military_equipment');
    }
};
