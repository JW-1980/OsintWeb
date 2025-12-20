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
        Schema::create('control_zones', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->geometry('geometry', 'polygon', 4326)->comment('Geographic polygon area');
            $table->foreignId('controller_id')->constrained('factions')->cascadeOnDelete();
            $table->enum('control_type', ['full', 'partial', 'contested', 'disputed', 'claimed'])->default('full');
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable()->comment('NULL means current');
            $table->string('source_url')->nullable();
            $table->enum('confidence', ['high', 'medium', 'low'])->default('medium');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('controller_id');
            $table->index('control_type');
            $table->index('valid_from');
            $table->index('valid_to');
            $table->index('confidence');
        });

        // Add spatial index on geometry
        DB::statement('ALTER TABLE control_zones ADD SPATIAL INDEX control_zones_geometry_spatial(geometry)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_zones');
    }
};
