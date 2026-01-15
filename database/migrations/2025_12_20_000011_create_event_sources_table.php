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
        Schema::create('event_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('url')->comment('Source URL');
            $table->enum('source_type', [
                'news',
                'social_media',
                'official',
                'witness',
                'satellite',
                'video',
                'photo',
                'document',
                'database',
                'other'
            ])->index();
            $table->string('source_name')->nullable()->comment('Publication or platform name');
            $table->timestamp('accessed_at')->nullable()->comment('When source was accessed');
            $table->string('archive_url')->nullable()->comment('Archive.org or similar');
            $table->enum('reliability', ['verified', 'credible', 'uncertain', 'questionable'])->default('uncertain');
            $table->timestamps();

            // Indexes
            $table->index('event_id');
            // Note: 'source_type' already indexed on line 29
            $table->index('reliability');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_sources');
    }
};
