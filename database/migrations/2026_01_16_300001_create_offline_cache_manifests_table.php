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
        Schema::create('offline_cache_manifests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('cache_version', 50)->default('1.0.0');
            $table->json('cached_routes')->nullable()->comment('JSON array of cached API routes');
            $table->json('cached_entities')->nullable()->comment('JSON of entity types and IDs cached');
            $table->timestamp('last_sync_at')->nullable();
            $table->enum('sync_status', ['synced', 'pending', 'error'])->default('synced');
            $table->json('pending_changes')->nullable()->comment('JSON of changes made offline');
            $table->unsignedBigInteger('storage_used_bytes')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'sync_status']);
            $table->index('last_sync_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_cache_manifests');
    }
};
