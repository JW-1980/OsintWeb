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
        // Enable required PostgreSQL extensions
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pg_trgm"');

        Schema::create('actors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->default(DB::raw('uuid_generate_v4()'));

            // Basic Information
            $table->string('name', 500)->index();
            $table->string('short_name', 100)->nullable()->index();
            $table->text('alias_names')->nullable(); // Array stored as JSON

            // Actor Classification
            $table->enum('actor_type', [
                'STATE',
                'SEPARATIST',
                'INSURGENT',
                'TERRORIST',
                'MILITIA',
                'PMC',
                'CARTEL',
                'REBEL',
                'ETHNIC_MILITIA',
                'GOVERNMENT_FORCES',
                'COALITION',
                'PROXY'
            ])->index();

            // Geographic Information
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('primary_region', 100)->nullable()->index();
            $table->text('operational_areas')->nullable(); // Array stored as JSON

            // Classification Flags
            $table->boolean('is_state_actor')->default(false)->index();
            $table->boolean('is_designated_terrorist')->default(false)->index();
            $table->jsonb('designations')->nullable(); // {us: true, eu: false, un: true, etc.}

            // Activity Status
            $table->boolean('is_active_in_conflict')->default(false)->index();
            $table->enum('activity_level', ['high', 'medium', 'low', 'inactive'])->default('inactive')->index();
            $table->date('last_activity_date')->nullable()->index();

            // Autocomplete Priority
            $table->integer('autocomplete_priority')->default(0)->index();
            $table->decimal('priority_score', 5, 2)->default(0.0)->index();

            // Visual Elements
            $table->string('logo_url', 500)->nullable();
            $table->string('flag_url', 500)->nullable();
            $table->string('color_hex', 7)->nullable();
            $table->string('icon', 100)->nullable();

            // Metadata
            $table->text('description')->nullable();
            $table->date('founded_date')->nullable();
            $table->date('dissolved_date')->nullable();
            $table->foreignId('successor_id')->nullable()->constrained('actors')->nullOnDelete();
            $table->foreignId('parent_organization_id')->nullable()->constrained('actors')->nullOnDelete();

            // External Links
            $table->string('wikipedia_url', 500)->nullable();
            $table->string('official_website', 500)->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();
        });

        // Create trigram indexes for fuzzy search
        DB::statement('CREATE INDEX actors_name_trgm_idx ON actors USING GIN(name gin_trgm_ops)');
        DB::statement('CREATE INDEX actors_short_name_trgm_idx ON actors USING GIN(short_name gin_trgm_ops)');

        // Create full-text search column
        DB::statement('ALTER TABLE actors ADD COLUMN search_vector tsvector');
        DB::statement('CREATE INDEX actors_search_vector_idx ON actors USING GIN(search_vector)');

        // Create trigger to update search_vector
        DB::statement("
            CREATE OR REPLACE FUNCTION actors_search_vector_update() RETURNS trigger AS $$
            BEGIN
                NEW.search_vector :=
                    setweight(to_tsvector('english', coalesce(NEW.name,'')), 'A') ||
                    setweight(to_tsvector('english', coalesce(NEW.short_name,'')), 'A') ||
                    setweight(to_tsvector('english', coalesce(NEW.description,'')), 'B');
                RETURN NEW;
            END
            $$ LANGUAGE plpgsql;
        ");

        DB::statement("
            CREATE TRIGGER actors_search_vector_trigger
            BEFORE INSERT OR UPDATE ON actors
            FOR EACH ROW
            EXECUTE FUNCTION actors_search_vector_update();
        ");

        // Create composite indexes for common queries
        DB::statement('CREATE INDEX actors_active_priority_idx ON actors(is_active_in_conflict DESC, priority_score DESC, name ASC)');
        DB::statement('CREATE INDEX actors_type_active_idx ON actors(actor_type, is_active_in_conflict, activity_level)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS actors_search_vector_trigger ON actors');
        DB::statement('DROP FUNCTION IF EXISTS actors_search_vector_update()');
        Schema::dropIfExists('actors');
    }
};
