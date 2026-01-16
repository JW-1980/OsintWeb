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
        Schema::create('network_graphs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();

            // Graph type - defines what kind of network this represents
            $table->enum('graph_type', [
                'actor_network',      // Connections between actors/people/organizations
                'communication',      // Communication patterns
                'supply_chain',       // Supply chain / logistics network
                'command_structure',  // Military/organizational hierarchy
                'financial',          // Financial transaction networks
                'influence',          // Influence/propaganda networks
            ])->default('actor_network');

            // Polymorphic root entity - the starting point for graph generation
            $table->string('root_entity_type', 100)->nullable();  // e.g., 'App\Models\Actor'
            $table->unsignedBigInteger('root_entity_id')->nullable();
            $table->index(['root_entity_type', 'root_entity_id'], 'network_graphs_root_entity_index');

            // Graph generation parameters
            $table->unsignedTinyInteger('depth_limit')->default(3);  // How many hops to include

            // Filters for graph generation (JSON)
            // Structure: { entity_types: [], relationship_types: [], min_strength: 0.5, date_range: {} }
            $table->json('filters')->nullable();

            // Layout configuration (JSON) - node positions, zoom level, etc.
            // Structure: { positions: { node_id: {x, y} }, zoom: 1.0, center: {x, y}, selected_nodes: [] }
            $table->json('layout_config')->nullable();

            // Visibility and sharing
            $table->boolean('is_public')->default(false);
            $table->boolean('is_template')->default(false);

            // Creator
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('cascade');

            // Cached graph data for quick retrieval
            $table->json('cached_nodes')->nullable();  // Cached node data
            $table->json('cached_edges')->nullable();  // Cached edge data
            $table->json('cached_metrics')->nullable(); // Cached graph metrics
            $table->timestamp('cache_expires_at')->nullable();

            // Statistics
            $table->unsignedInteger('node_count')->default(0);
            $table->unsignedInteger('edge_count')->default(0);
            $table->timestamp('last_generated_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index('graph_type');
            $table->index('is_public');
            $table->index('is_template');
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_graphs');
    }
};
