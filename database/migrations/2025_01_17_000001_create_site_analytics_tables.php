<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Database Schema Documentation:
     *
     * page_views - Aggregated anonymous page view statistics
     * - Stores hourly/daily aggregated counts per page path
     * - No individual user tracking, fully anonymous
     * - Used for understanding which pages are most visited
     *
     * feature_usage - Tracks which features/functionality are used
     * - Aggregated counts of feature interactions
     * - Categories: navigation, content, tools, export, social
     * - Helps understand which features provide value
     *
     * session_analytics - Anonymous session-level aggregates
     * - Daily aggregates of session metrics
     * - Bounce rates, duration distributions, device types
     * - No individual session tracking
     */
    public function up(): void
    {
        // Aggregated page views (hourly buckets)
        Schema::create('page_view_stats', function (Blueprint $table) {
            $table->id();
            $table->string('page_path', 500)->index();
            $table->string('page_group', 100)->nullable()->index(); // e.g., 'map', 'events', 'equipment'
            $table->date('date')->index();
            $table->tinyInteger('hour')->unsigned(); // 0-23
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('unique_visitors')->default(0); // Based on anonymous session hash
            $table->string('referrer_domain', 255)->nullable();
            $table->string('device_type', 20)->nullable(); // desktop, mobile, tablet
            $table->string('country_code', 2)->nullable();
            $table->timestamps();

            $table->unique(['page_path', 'date', 'hour', 'device_type', 'country_code'], 'page_view_unique');
            $table->index(['date', 'page_group']);
        });

        // Feature usage tracking
        Schema::create('feature_usage_stats', function (Blueprint $table) {
            $table->id();
            $table->string('feature_name', 100)->index(); // e.g., 'map_zoom', 'event_filter', 'export_csv'
            $table->string('feature_category', 50)->index(); // navigation, content, tools, export, social
            $table->string('feature_action', 50)->nullable(); // click, view, submit, toggle
            $table->date('date')->index();
            $table->tinyInteger('hour')->unsigned();
            $table->unsignedInteger('usage_count')->default(0);
            $table->unsignedInteger('unique_users')->default(0);
            $table->json('metadata')->nullable(); // Additional context (e.g., which filter was applied)
            $table->timestamps();

            $table->unique(['feature_name', 'feature_action', 'date', 'hour'], 'feature_usage_unique');
            $table->index(['date', 'feature_category']);
        });

        // Session analytics (daily aggregates)
        Schema::create('session_analytics', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->unsignedInteger('total_sessions')->default(0);
            $table->unsignedInteger('new_sessions')->default(0);
            $table->unsignedInteger('returning_sessions')->default(0);
            $table->unsignedInteger('bounced_sessions')->default(0); // Single page visits
            $table->unsignedInteger('avg_duration_seconds')->default(0);
            $table->unsignedInteger('avg_pages_per_session')->default(0);

            // Device breakdown
            $table->unsignedInteger('desktop_sessions')->default(0);
            $table->unsignedInteger('mobile_sessions')->default(0);
            $table->unsignedInteger('tablet_sessions')->default(0);

            // Geographic breakdown (top countries stored as JSON)
            $table->json('countries')->nullable();

            // Referrer breakdown
            $table->json('referrers')->nullable();

            // Browser breakdown
            $table->json('browsers')->nullable();

            $table->timestamps();

            $table->unique('date');
        });

        // User flow tracking (aggregated paths)
        Schema::create('user_flow_stats', function (Blueprint $table) {
            $table->id();
            $table->string('from_page', 255)->index();
            $table->string('to_page', 255)->index();
            $table->date('date')->index();
            $table->unsignedInteger('transition_count')->default(0);
            $table->unsignedInteger('exit_count')->default(0); // Users who left after from_page
            $table->timestamps();

            $table->unique(['from_page', 'to_page', 'date']);
        });

        // Search analytics
        Schema::create('search_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('search_term', 255)->index();
            $table->string('search_type', 50)->nullable(); // events, equipment, actors, global
            $table->date('date')->index();
            $table->unsignedInteger('search_count')->default(0);
            $table->unsignedInteger('result_clicks')->default(0);
            $table->unsignedInteger('zero_results')->default(0); // Searches with no results
            $table->float('avg_results_count')->default(0);
            $table->timestamps();

            $table->unique(['search_term', 'search_type', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_analytics');
        Schema::dropIfExists('user_flow_stats');
        Schema::dropIfExists('session_analytics');
        Schema::dropIfExists('feature_usage_stats');
        Schema::dropIfExists('page_view_stats');
    }
};
