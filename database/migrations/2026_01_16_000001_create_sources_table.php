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
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->comment('Source publication or outlet name');
            $table->string('url')->nullable()->comment('Primary website URL');
            $table->text('description')->nullable()->comment('Description of the source');

            // Reliability metrics
            $table->unsignedTinyInteger('reliability_score')->default(50)->comment('0-100 reliability score');
            $table->enum('verification_status', [
                'unverified',
                'verified',
                'trusted',
                'unreliable'
            ])->default('unverified')->comment('Current verification status');

            // Source classification
            $table->enum('source_type', [
                'news_outlet',
                'social_media',
                'official',
                'eyewitness',
                'satellite',
                'document'
            ])->default('news_outlet')->comment('Type of source');

            // Location association
            $table->foreignId('country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete()
                ->comment('Country of origin');

            // Verification tracking
            $table->timestamp('last_verified_at')->nullable()->comment('Last verification timestamp');
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('User who last verified');

            // Report statistics for reliability calculation
            $table->unsignedInteger('total_reports')->default(0)->comment('Total reports from this source');
            $table->unsignedInteger('accurate_reports')->default(0)->comment('Reports verified as accurate');
            $table->unsignedInteger('disputed_reports')->default(0)->comment('Reports that were disputed');

            // Additional data
            $table->json('metadata')->nullable()->comment('Additional structured data');
            $table->json('contact_info')->nullable()->comment('Contact information');
            $table->json('known_biases')->nullable()->comment('Known biases or affiliations');
            $table->string('language', 10)->nullable()->comment('Primary language (ISO 639-1)');
            $table->string('logo_url')->nullable()->comment('Source logo image URL');
            $table->boolean('is_active')->default(true)->comment('Whether source is actively tracked');

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index('name');
            $table->index('reliability_score');
            $table->index('verification_status');
            $table->index('source_type');
            $table->index('country_id');
            $table->index('is_active');
            $table->index(['verification_status', 'reliability_score']);
            $table->index(['source_type', 'verification_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
