<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conflict_parties', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('conflict_id')->constrained('conflicts')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('actors')->cascadeOnDelete();

            // Role in Conflict
            $table->string('side', 100)->nullable(); // Government, Opposition, Neutral, Coalition, etc.
            $table->string('role', 100)->nullable(); // Primary Combatant, Support, Mediator, etc.

            // Participation Details
            $table->date('joined_date')->nullable();
            $table->date('left_date')->nullable();
            $table->boolean('is_currently_active')->default(true)->index();

            // External Relationships (stored as JSON arrays of actor IDs)
            $table->text('supported_by')->nullable(); // Array of actor IDs providing support
            $table->text('opposing')->nullable(); // Array of actor IDs they're fighting

            // Notes
            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamps();

            // Unique constraint - one actor can only have one active role per conflict
            $table->unique(['conflict_id', 'actor_id']);
        });

        // Create indexes for common queries
        DB::statement('CREATE INDEX conflict_parties_conflict_active_idx ON conflict_parties(conflict_id, is_currently_active)');
        DB::statement('CREATE INDEX conflict_parties_actor_active_idx ON conflict_parties(actor_id, is_currently_active)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conflict_parties');
    }
};
