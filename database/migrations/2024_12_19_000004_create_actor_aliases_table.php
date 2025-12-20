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

        // Create trigram index for fuzzy search on aliases
        DB::statement('CREATE INDEX actor_aliases_alias_trgm_idx ON actor_aliases USING GIN(alias gin_trgm_ops)');

        // Create composite index for actor lookups
        DB::statement('CREATE INDEX actor_aliases_actor_alias_idx ON actor_aliases(actor_id, alias)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actor_aliases');
    }
};
