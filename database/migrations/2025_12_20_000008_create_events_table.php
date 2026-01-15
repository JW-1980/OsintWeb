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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('event_type_id')->constrained('event_types')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->geometry('location', 'point', 4326)->nullable()->comment('Geographic point location');
            $table->string('location_name')->nullable()->comment('Human-readable location name');
            $table->enum('location_accuracy', ['exact', 'approximate', 'general', 'unknown'])->default('unknown');
            $table->dateTime('occurred_at')->nullable();
            $table->dateTime('occurred_at_end')->nullable()->comment('For events with duration');
            $table->enum('date_accuracy', ['exact', 'date', 'month', 'year', 'approximate'])->default('exact');
            $table->enum('status', ['draft', 'pending', 'verified', 'disputed', 'archived'])->default('draft');
            $table->decimal('verification_score', 3, 2)->default(0.00)->comment('0.00 to 1.00');
            $table->json('custom_fields')->nullable()->comment('Event type specific fields');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('event_type_id');
            $table->index('user_id');
            $table->index('occurred_at');
            $table->index('status');
            $table->index('verification_score');
            $table->index('created_at');
            $table->fullText(['title', 'description', 'location_name'], 'events_search_idx');
        });

        // Add spatial index on location
        DB::statement('ALTER TABLE events ADD SPATIAL INDEX events_location_spatial(location)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
