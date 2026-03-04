<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\OpenRouterService;
use Illuminate\Http\JsonResponse;

class AISettingsController extends Controller
{
    public function __construct(
        protected OpenRouterService $aiService
    ) {}

    /**
     * Get AI provider configuration and status.
     *
     * GET /api/admin/ai-settings
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'active_provider' => $this->aiService->getActiveProvider(),
                'provider_name' => $this->aiService->getProviderName(),
                'configured' => $this->aiService->isConfigured(),
                'providers' => $this->aiService->getAvailableProviders(),
                'models' => [
                    'chat' => $this->aiService->getModelForTask('chat'),
                    'vision' => $this->aiService->getModelForTask('vision'),
                    'translation' => $this->aiService->getModelForTask('translation'),
                    'ner' => $this->aiService->getModelForTask('ner'),
                    'classification' => $this->aiService->getModelForTask('classification'),
                    'language_detection' => $this->aiService->getModelForTask('language_detection'),
                ],
                'free_models' => $this->aiService->getFreeModels(),
            ],
        ]);
    }

    /**
     * Test the active provider connection.
     *
     * POST /api/admin/ai-settings/test-connection
     */
    public function testConnection(): JsonResponse
    {
        $result = $this->aiService->testConnection();

        return response()->json([
            'data' => array_merge($result, [
                'provider' => $this->aiService->getActiveProvider(),
                'provider_name' => $this->aiService->getProviderName(),
            ]),
        ]);
    }
}
