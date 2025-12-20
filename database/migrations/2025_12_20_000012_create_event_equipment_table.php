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
        Schema::create('event_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('military_equipment')->cascadeOnDelete();
            $table->integer('quantity')->default(1)->comment('Number of units observed');
            $table->enum('status', [
                'operational',
                'damaged',
                'destroyed',
                'captured',
                'abandoned',
                'unknown'
            ])->default('unknown');
            $table->foreignId('operator_faction_id')->nullable()->constrained('factions')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('event_id');
            $table->index('equipment_id');
            $table->index('status');
            $table->index('operator_faction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_equipment');
    }
};
