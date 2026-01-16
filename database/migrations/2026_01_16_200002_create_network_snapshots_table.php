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
        Schema::create('network_snapshots', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Reference to the parent graph
            $table->foreignId('network_graph_id')
                ->constrained('network_graphs')
                ->onDelete('cascade');

            // Snapshot name/label (optional)
            $table->string('name', 255)->nullable();

            // Complete node data at capture time (JSON)
            // Structure: [{ id, uuid, type, name, attributes, position: {x, y}, ... }]
            $table->json('nodes_data');

            // Complete edge data at capture time (JSON)
            // Structure: [{ id, source_id, target_id, type, strength, attributes, ... }]
            $table->json('edges_data');

            // Graph statistics/metrics at capture time (JSON)
            // Structure: {
            //   density, clustering_coefficient, avg_degree, max_degree,
            //   diameter, connected_components, modularity,
            //   centrality_scores: { betweenness: {}, closeness: {}, degree: {} }
            // }
            $table->json('stats')->nullable();

            // When this snapshot was captured
            $table->timestamp('captured_at');

            // User notes about this snapshot
            $table->text('notes')->nullable();

            // Reason for creating snapshot (optional)
            $table->string('reason', 255)->nullable();

            // User who created the snapshot
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            // Comparison data (for diff between snapshots)
            $table->unsignedBigInteger('compared_to_snapshot_id')->nullable();
            $table->json('diff_data')->nullable();  // Stores diff if compared

            $table->timestamps();

            // Indexes
            $table->index('network_graph_id');
            $table->index('captured_at');
            $table->index('created_by');

            // Foreign key for comparison reference
            $table->foreign('compared_to_snapshot_id')
                ->references('id')
                ->on('network_snapshots')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_snapshots');
    }
};
