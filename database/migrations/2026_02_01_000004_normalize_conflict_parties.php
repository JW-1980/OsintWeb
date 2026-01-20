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
        Schema::create('conflict_party_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conflict_id')->constrained('conflicts')->cascadeOnDelete();
            // The actor who is the subject of the relationship (e.g., the one being supported)
            $table->foreignId('actor_id')->constrained('actors')->cascadeOnDelete();
            // The related actor (e.g., the supporter or opponent)
            $table->foreignId('related_actor_id')->constrained('actors')->cascadeOnDelete();

            $table->enum('type', ['support', 'oppose']);

            $table->timestamps();

            $table->unique(['conflict_id', 'actor_id', 'related_actor_id', 'type'], 'conflict_party_relations_unique');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conflict_party_relations');
    }
};
