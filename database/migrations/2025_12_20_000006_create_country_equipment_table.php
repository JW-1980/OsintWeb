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
        Schema::create('country_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('military_equipment')->cascadeOnDelete();
            $table->integer('quantity_active')->default(0);
            $table->integer('quantity_reserve')->default(0);
            $table->integer('quantity_ordered')->default(0);
            $table->date('data_as_of')->nullable()->comment('Date this data was accurate');
            $table->string('source_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->unique(['country_id', 'equipment_id']);
            $table->index('data_as_of');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_equipment');
    }
};
