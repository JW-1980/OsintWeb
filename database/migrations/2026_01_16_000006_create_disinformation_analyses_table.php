<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create disinformation_analyses table
 *
 * Stores comprehensive analysis results for detecting disinformation campaigns,
 * coordinated behavior, propaganda, and manipulation across various content types.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('disinformation_analyses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();

            // Polymorphic relationship to analyzable content
            $table->string('analyzable_type');
            $table->unsignedBigInteger('analyzable_id');
            $table->index(['analyzable_type', 'analyzable_id'], 'disinformation_analyzable_index');

            // Analysis type classification
            $table->enum('analysis_type', [
                'coordinated_behavior',
                'propaganda',
                'fake_account',
                'manipulated_media',
                'false_narrative',
            ])->index();

            // Scoring metrics (0-100)
            $table->unsignedTinyInteger('threat_score')->default(0)->index();
            $table->unsignedTinyInteger('confidence_score')->default(0);

            // Detected patterns and indicators
            $table->json('indicators')->nullable()->comment('JSON array of detected pattern indicators');
            $table->json('network_analysis')->nullable()->comment('Related accounts/sources if coordinated behavior detected');
            $table->json('temporal_patterns')->nullable()->comment('Posting time patterns and anomalies');
            $table->json('content_patterns')->nullable()->comment('Repeated phrases, hashtags, narratives');
            $table->json('source_credibility')->nullable()->comment('Source reliability and track record data');
            $table->json('ai_analysis')->nullable()->comment('AI model output and reasoning');

            // Manual review workflow
            $table->enum('manual_review_status', [
                'pending',
                'confirmed',
                'rejected',
                'needs_review',
            ])->default('pending')->index();
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            // Processing status
            $table->enum('analysis_status', [
                'pending',
                'processing',
                'completed',
                'failed',
            ])->default('pending')->index();
            $table->text('error_message')->nullable();

            // Audit trail
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Composite indexes for common queries
            $table->index(['analysis_status', 'threat_score'], 'disinformation_status_threat_index');
            $table->index(['analysis_type', 'manual_review_status'], 'disinformation_type_review_index');
            $table->index(['created_at', 'threat_score'], 'disinformation_recent_threats_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disinformation_analyses');
    }
};
