<?php

declare(strict_types=1);

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
        Schema::create('source_cross_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')
                ->constrained('sources')
                ->cascadeOnDelete()
                ->comment('Primary source');
            $table->foreignId('referenced_source_id')
                ->constrained('sources')
                ->cascadeOnDelete()
                ->comment('Source being referenced');

            // Relationship details
            $table->enum('relationship_type', [
                'corroborates',        // Sources confirm each other
                'contradicts',         // Sources contradict each other
                'cites',               // One source cites another
                'republishes',         // One republishes the other
                'related',             // Generally related coverage
                'parent_organization', // Organizational relationship
                'subsidiary',          // Subsidiary organization
                'partner',             // Partner organizations
            ])->default('related')->comment('Type of relationship between sources');

            // Confidence and verification
            $table->unsignedTinyInteger('confidence_score')
                ->default(50)
                ->comment('0-100 confidence in this relationship');
            $table->text('notes')->nullable()->comment('Additional notes about the relationship');

            // Tracking
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('User who created this reference');
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('User who verified this reference');
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('source_id');
            $table->index('referenced_source_id');
            $table->index('relationship_type');
            $table->index('confidence_score');

            // Unique constraint to prevent duplicate relationships
            $table->unique(
                ['source_id', 'referenced_source_id', 'relationship_type'],
                'source_cross_ref_unique'
            );
        });

        // Add pivot table for sources and events
        Schema::create('event_source_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();
            $table->foreignId('source_id')
                ->constrained('sources')
                ->cascadeOnDelete();
            $table->string('source_url')->nullable()->comment('Specific URL for this event');
            $table->string('archive_url')->nullable()->comment('Archived URL (archive.org, etc.)');
            $table->timestamp('accessed_at')->nullable()->comment('When source was accessed');
            $table->enum('reliability', [
                'verified',
                'credible',
                'uncertain',
                'questionable'
            ])->default('uncertain');
            $table->text('excerpt')->nullable()->comment('Relevant excerpt from source');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->timestamps();

            $table->unique(['event_id', 'source_id', 'source_url'], 'event_source_url_unique');
            $table->index('event_id');
            $table->index('source_id');
            $table->index('reliability');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_source_links');
        Schema::dropIfExists('source_cross_references');
    }
};
