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
        Schema::create('actor_relationships', function (Blueprint $table) {
            $table->id();

            // Actors in relationship
            $table->foreignId('actor_id')->constrained('actors')->cascadeOnDelete();
            $table->foreignId('related_actor_id')->constrained('actors')->cascadeOnDelete();

            // Relationship Type
            $table->enum('relationship_type', [
                'ALLY',
                'ENEMY',
                'SUPPORTER',
                'SUPPORTED_BY',
                'PROXY',
                'PARENT',
                'CHILD',
                'COALITION_MEMBER',
                'RIVAL',
                'NEUTRAL'
            ])->index();

            // Relationship Strength
            $table->enum('strength', [
                'STRONG',
                'MODERATE',
                'WEAK',
                'UNKNOWN'
            ])->default('UNKNOWN');

            // Timeline
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true)->index();

            // Additional Information
            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamps();

            // Unique constraint
            $table->unique(['actor_id', 'related_actor_id', 'relationship_type'], 'actor_rel_unique');
        });

        // Create indexes for relationship queries
        DB::statement('CREATE INDEX actor_relationships_actor_type_idx ON actor_relationships(actor_id, relationship_type, is_active)');
        DB::statement('CREATE INDEX actor_relationships_related_type_idx ON actor_relationships(related_actor_id, relationship_type, is_active)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actor_relationships');
    }
};
