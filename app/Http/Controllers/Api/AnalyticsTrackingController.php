<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public Analytics Tracking Controller.
 *
 * Provides endpoints for frontend to track feature usage and search analytics.
 * All tracking is anonymous - no personal data is stored.
 */
class AnalyticsTrackingController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService
    ) {}

    /**
     * Track a feature usage event.
     */
    public function trackFeature(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'feature' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'action' => 'required|string|max:50',
            'metadata' => 'nullable|array',
        ]);

        try {
            $this->analyticsService->trackFeature(
                $request,
                $validated['feature'],
                $validated['category'],
                $validated['action'],
                $validated['metadata'] ?? null
            );

            return response()->json([
                'data' => [
                    'tracked' => true,
                ],
            ]);
        } catch (\Exception $e) {
            // Don't fail the request if tracking fails
            return response()->json([
                'data' => [
                    'tracked' => false,
                ],
            ]);
        }
    }

    /**
     * Track a search event.
     */
    public function trackSearch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'term' => 'required|string|max:500',
            'type' => 'required|string|max:50',
            'result_count' => 'required|integer|min:0',
        ]);

        try {
            $this->analyticsService->trackSearch(
                $request,
                $validated['term'],
                $validated['type'],
                $validated['result_count']
            );

            return response()->json([
                'data' => [
                    'tracked' => true,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [
                    'tracked' => false,
                ],
            ]);
        }
    }

    /**
     * Track multiple events in a batch.
     */
    public function trackBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'events' => 'required|array|max:50',
            'events.*.type' => 'required|string|in:feature,search',
            'events.*.feature' => 'required_if:events.*.type,feature|string|max:100',
            'events.*.category' => 'required_if:events.*.type,feature|string|max:50',
            'events.*.action' => 'required_if:events.*.type,feature|string|max:50',
            'events.*.metadata' => 'nullable|array',
            'events.*.term' => 'required_if:events.*.type,search|string|max:500',
            'events.*.search_type' => 'required_if:events.*.type,search|string|max:50',
            'events.*.result_count' => 'required_if:events.*.type,search|integer|min:0',
        ]);

        $tracked = 0;
        $failed = 0;

        foreach ($validated['events'] as $event) {
            try {
                if ($event['type'] === 'feature') {
                    $this->analyticsService->trackFeature(
                        $request,
                        $event['feature'],
                        $event['category'],
                        $event['action'],
                        $event['metadata'] ?? null
                    );
                } elseif ($event['type'] === 'search') {
                    $this->analyticsService->trackSearch(
                        $request,
                        $event['term'],
                        $event['search_type'],
                        $event['result_count']
                    );
                }
                $tracked++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return response()->json([
            'data' => [
                'tracked' => $tracked,
                'failed' => $failed,
            ],
        ]);
    }
}
