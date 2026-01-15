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
        Schema::create('actor_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->constrained('actors')->cascadeOnDelete();

            $table->string('alias', 500)->index();

            $table->enum('alias_type', [
                'ACRONYM',
                'TRANSLATION',
                'FORMER_NAME',
                'NICKNAME',
                'LOCAL_NAME',
                'MILITARY_DESIGNATION',
                'OTHER'
            ])->nullable();

            $table->char('language_code', 2)->nullable(); // ISO 639-1 code (en, ar, ru, etc.)
            $table->boolean('is_primary')->default(false);

            $table->timestamp('created_at')->useCurrent();

            // Unique constraint - prevent duplicate aliases for same actor
            $table->unique(['actor_id', 'alias']);
        });

        // Composite index for actor lookups (MySQL compatible)
        // Note: Trigram/GIN indexes not available in MySQL - use FULLTEXT for text search if needed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actor_aliases');
    }
};
