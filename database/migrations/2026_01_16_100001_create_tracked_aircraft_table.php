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
        Schema::create('tracked_aircraft', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('icao_hex', 6)->unique()->comment('ICAO 24-bit aircraft address in hex');
            $table->string('registration', 20)->nullable()->index()->comment('Aircraft tail number/registration');
            $table->string('aircraft_type', 50)->nullable()->index()->comment('ICAO aircraft type designator (e.g., B738, A320)');
            $table->string('aircraft_model', 100)->nullable()->comment('Full aircraft model name');
            $table->string('operator', 100)->nullable()->index()->comment('Aircraft operator/airline');
            $table->string('owner', 100)->nullable()->comment('Registered owner');
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete()->comment('Country of registration');
            $table->string('military_designation', 50)->nullable()->comment('Military designation if applicable (e.g., F-16C, Su-35)');
            $table->boolean('is_military')->default(false)->index()->comment('Whether this is a military aircraft');
            $table->boolean('is_monitored')->default(true)->index()->comment('Whether to actively track this aircraft');
            $table->unsignedTinyInteger('monitoring_priority')->default(3)->index()->comment('Priority 1-5, where 1 is highest');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional aircraft data (serial number, images, etc.)');
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('first_seen_at')->nullable()->comment('First time we received data for this aircraft');
            $table->timestamp('last_seen_at')->nullable()->index()->comment('Most recent position data received');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['is_monitored', 'monitoring_priority']);
            $table->index(['is_military', 'country_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracked_aircraft');
    }
};
