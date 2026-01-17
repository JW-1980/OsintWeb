<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ABTestingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public A/B Testing Controller.
 *
 * Provides endpoints for anonymous visitors to get experiment variants
 * and track conversions.
 */
class ABTestingPublicController extends Controller
{
    public function __construct(
        private readonly ABTestingService $abTestingService
    ) {}

    /**
     * Get variant for a specific location.
     */
    public function getVariant(Request $request, string $location): JsonResponse
    {
        $variant = $this->abTestingService->getVariant($request, $location);

        if (!$variant) {
            return response()->json([
                'data' => null,
                'message' => 'No active experiment at this location',
            ]);
        }

        return response()->json([
            'data' => $variant,
        ]);
    }

    /**
     * Get variants for multiple locations at once.
     */
    public function getVariants(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'locations' => 'required|array',
            'locations.*' => 'string|max:255',
        ]);

        $variants = $this->abTestingService->getVariants($request, $validated['locations']);

        return response()->json([
            'data' => $variants,
        ]);
    }

    /**
     * Track a conversion for an experiment.
     */
    public function trackConversion(Request $request, string $experimentSlug): JsonResponse
    {
        $validated = $request->validate([
            'goal' => 'required|string|max:100',
        ]);

        $tracked = $this->abTestingService->trackConversion(
            $request,
            $experimentSlug,
            $validated['goal']
        );

        return response()->json([
            'data' => [
                'tracked' => $tracked,
            ],
        ]);
    }

    /**
     * Track a conversion by location.
     */
    public function trackConversionByLocation(Request $request, string $location): JsonResponse
    {
        $validated = $request->validate([
            'goal' => 'nullable|string|max:100',
        ]);

        $tracked = $this->abTestingService->trackConversionByLocation(
            $request,
            $location,
            $validated['goal'] ?? null
        );

        return response()->json([
            'data' => [
                'tracked' => $tracked,
            ],
        ]);
    }
}
