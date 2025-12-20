<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Services\EnvironmentWriter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Search configuration controller
 */
class SearchController extends Controller
{
    public function __construct(
        private readonly EnvironmentWriter $environmentWriter
    ) {
    }

    /**
     * Display search configuration page
     */
    public function index(): View
    {
        return view('install.search', [
            'scout_driver' => $this->environmentWriter->get('SCOUT_DRIVER') ?? 'database',
        ]);
    }

    /**
     * Test search service connection
     */
    public function test(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search_driver' => 'required|string|in:meilisearch,algolia',
            'meilisearch_host' => 'required_if:search_driver,meilisearch|nullable|url',
            'meilisearch_key' => 'nullable|string',
            'algolia_app_id' => 'required_if:search_driver,algolia|nullable|string',
            'algolia_secret' => 'required_if:search_driver,algolia|nullable|string',
        ]);

        try {
            if ($validated['search_driver'] === 'meilisearch') {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . ($validated['meilisearch_key'] ?? ''),
                ])->get($validated['meilisearch_host'] . '/health');

                if ($response->successful()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Successfully connected to Meilisearch',
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to Meilisearch: ' . $response->body(),
                ], 500);
            }

            if ($validated['search_driver'] === 'algolia') {
                $response = Http::withHeaders([
                    'X-Algolia-Application-Id' => $validated['algolia_app_id'] ?? '',
                    'X-Algolia-API-Key' => $validated['algolia_secret'] ?? '',
                ])->get("https://{$validated['algolia_app_id']}-dsn.algolia.net/1/indexes");

                if ($response->successful()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Successfully connected to Algolia',
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to Algolia: ' . $response->body(),
                ], 500);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid search driver',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save search configuration
     */
    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'search_driver' => 'required|string|in:database,meilisearch,algolia',
            'meilisearch_host' => 'required_if:search_driver,meilisearch|nullable|url',
            'meilisearch_key' => 'nullable|string',
            'algolia_app_id' => 'required_if:search_driver,algolia|nullable|string',
            'algolia_secret' => 'required_if:search_driver,algolia|nullable|string',
        ]);

        $envData = [
            'SCOUT_DRIVER' => $validated['search_driver'],
        ];

        if ($validated['search_driver'] === 'meilisearch') {
            $envData['MEILISEARCH_HOST'] = $validated['meilisearch_host'] ?? '';
            $envData['MEILISEARCH_KEY'] = $validated['meilisearch_key'] ?? '';
        } elseif ($validated['search_driver'] === 'algolia') {
            $envData['ALGOLIA_APP_ID'] = $validated['algolia_app_id'] ?? '';
            $envData['ALGOLIA_SECRET'] = $validated['algolia_secret'] ?? '';
        }

        $this->environmentWriter->update($envData);

        return redirect()->route('install.finish');
    }
}
