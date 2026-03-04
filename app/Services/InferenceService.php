<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Multi-provider inference service with OpenAI-compatible endpoints.
 *
 * Supports 9 AI providers with automatic fallback, all offering free tiers:
 * - OpenRouter (multi-model gateway)
 * - Google Gemini (1M context, free tier)
 * - Groq (ultra-fast LPU inference)
 * - Cerebras (fastest wafer-scale inference)
 * - Mistral AI (European provider, multilingual)
 * - SambaNova (ultra-fast RDU inference)
 * - GitHub Models (free with GitHub account)
 * - Together AI (200+ models, free credits)
 * - Novita AI (free models, global CDN)
 */
class InferenceService
{
    protected array $config;
    protected string $currentProvider;
    protected ?string $lastError = null;
    protected ?array $lastUsage = null;

    /**
     * Provider capabilities map.
     */
    protected array $providerCapabilities = [
        'openrouter' => ['chat', 'vision', 'translation', 'ner', 'classification', 'language_detection'],
        'gemini' => ['chat', 'vision', 'translation', 'ner', 'classification', 'language_detection'],
        'groq' => ['chat', 'vision', 'translation', 'ner', 'classification', 'language_detection'],
        'cerebras' => ['chat', 'translation', 'ner', 'classification', 'language_detection'], // No vision
        'mistral' => ['chat', 'vision', 'translation', 'ner', 'classification', 'language_detection'],
        'sambanova' => ['chat', 'vision', 'translation', 'ner', 'classification', 'language_detection'],
        'github' => ['chat', 'vision', 'translation', 'ner', 'classification', 'language_detection'],
        'together' => ['chat', 'vision', 'translation', 'ner', 'classification', 'language_detection'],
        'novita' => ['chat', 'translation', 'ner', 'classification', 'language_detection'], // No free vision models
    ];

    public function __construct(?string $provider = null)
    {
        $this->config = config('inference');
        $this->currentProvider = $provider ?? $this->config['default_provider'] ?? 'openrouter';
    }

    // =========================================================================
    // PROVIDER MANAGEMENT
    // =========================================================================

    /**
     * Set the active provider.
     */
    public function setProvider(string $provider): self
    {
        if (!isset($this->config['providers'][$provider])) {
            throw new \InvalidArgumentException("Unknown provider: {$provider}");
        }

        $this->currentProvider = $provider;
        return $this;
    }

    /**
     * Get the current provider name.
     */
    public function getProvider(): string
    {
        return $this->currentProvider;
    }

    /**
     * Get all available providers.
     *
     * @return array<string, array>
     */
    public function getAvailableProviders(): array
    {
        $available = [];

        foreach ($this->config['providers'] as $name => $config) {
            if ($this->isProviderAvailable($name)) {
                $available[$name] = [
                    'name' => $config['name'],
                    'enabled' => $config['enabled'],
                    'configured' => !empty($config['api_key']),
                    'capabilities' => $this->providerCapabilities[$name] ?? [],
                    'free_models' => $config['free_models'] ?? [],
                ];
            }
        }

        return $available;
    }

    /**
     * Check if a provider is available (enabled and configured).
     */
    public function isProviderAvailable(string $provider): bool
    {
        $config = $this->config['providers'][$provider] ?? null;

        if (!$config) {
            return false;
        }

        return ($config['enabled'] ?? false) && !empty($config['api_key']);
    }

    /**
     * Check if a provider supports a specific task.
     */
    public function providerSupportsTask(string $provider, string $task): bool
    {
        $capabilities = $this->providerCapabilities[$provider] ?? [];
        $providerConfig = $this->config['providers'][$provider] ?? [];

        // Check if task is in capabilities
        if (!in_array($task, $capabilities)) {
            return false;
        }

        // Check if model is configured for this task
        $model = $providerConfig['models'][$task] ?? null;
        return $model !== null;
    }

    /**
     * Get the best provider for a specific task.
     */
    public function getBestProviderForTask(string $task): string
    {
        // Check task-specific preference
        $taskProvider = $this->config['task_providers'][$task] ?? null;
        if ($taskProvider && $this->isProviderAvailable($taskProvider) && $this->providerSupportsTask($taskProvider, $task)) {
            return $taskProvider;
        }

        // Check default provider
        if ($this->isProviderAvailable($this->currentProvider) && $this->providerSupportsTask($this->currentProvider, $task)) {
            return $this->currentProvider;
        }

        // Try fallback chain
        foreach ($this->config['fallback_chain'] ?? [] as $provider) {
            if ($this->isProviderAvailable($provider) && $this->providerSupportsTask($provider, $task)) {
                return $provider;
            }
        }

        // Return default even if not fully configured
        return $this->currentProvider;
    }

    // =========================================================================
    // CORE API METHODS
    // =========================================================================

    /**
     * Send a chat completion request.
     *
     * @param array $messages Array of message objects [{role: string, content: string}]
     * @param string|null $model Model to use (defaults to provider config)
     * @param array $params Additional parameters (temperature, max_tokens, etc.)
     * @param string|null $provider Override provider for this request
     * @return array|null Response data or null on failure
     */
    public function chat(
        array $messages,
        ?string $model = null,
        array $params = [],
        ?string $provider = null
    ): ?array {
        $provider = $provider ?? $this->getBestProviderForTask('chat');
        $providerConfig = $this->getProviderConfig($provider);

        $model = $model ?? $providerConfig['models']['chat'] ?? null;
        if (!$model) {
            $this->lastError = "No chat model configured for provider: {$provider}";
            return null;
        }

        $defaultParams = $this->config['model_params']['default'] ?? [];

        return $this->completions($messages, $model, array_merge($defaultParams, $params), $provider);
    }

    /**
     * Send a vision analysis request with an image.
     *
     * @param string $imagePath Path to the image file or base64 encoded image
     * @param string $prompt The prompt/question about the image
     * @param string|null $model Vision model to use
     * @param array $params Additional parameters
     * @param string|null $provider Override provider for this request
     * @return array|null Response data or null on failure
     */
    public function vision(
        string $imagePath,
        string $prompt,
        ?string $model = null,
        array $params = [],
        ?string $provider = null
    ): ?array {
        $provider = $provider ?? $this->getBestProviderForTask('vision');
        $providerConfig = $this->getProviderConfig($provider);

        // Check if provider supports vision
        if (!$this->providerSupportsTask($provider, 'vision')) {
            $this->lastError = "Provider {$provider} does not support vision tasks";
            return null;
        }

        $model = $model ?? $providerConfig['models']['vision'] ?? null;
        if (!$model) {
            $this->lastError = "No vision model configured for provider: {$provider}";
            return null;
        }

        // Prepare the image content
        $imageContent = $this->prepareImageContent($imagePath);
        if (!$imageContent) {
            $this->lastError = 'Failed to prepare image content';
            return null;
        }

        $defaultParams = $this->config['model_params']['ocr'] ?? [];

        $messages = [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => $imageContent,
                        ],
                    ],
                    [
                        'type' => 'text',
                        'text' => $prompt,
                    ],
                ],
            ],
        ];

        return $this->completions($messages, $model, array_merge($defaultParams, $params), $provider);
    }

    /**
     * Core completions endpoint with provider support.
     */
    protected function completions(
        array $messages,
        string $model,
        array $params = [],
        ?string $provider = null
    ): ?array {
        $provider = $provider ?? $this->currentProvider;

        if (!$this->checkRateLimit($provider)) {
            // Try fallback providers
            $result = $this->tryFallbackProviders($messages, $model, $params, 'chat');
            if ($result) {
                return $result;
            }

            $this->lastError = 'Rate limit exceeded for all providers.';
            return null;
        }

        $payload = array_merge([
            'model' => $model,
            'messages' => $messages,
        ], $params);

        try {
            $response = $this->makeRequest('POST', '/chat/completions', $payload, $provider);

            if (!$response->successful()) {
                // Try fallback on error
                $result = $this->tryFallbackProviders($messages, $model, $params, 'chat');
                if ($result) {
                    return $result;
                }

                $this->handleErrorResponse($response, $provider);
                return null;
            }

            $data = $response->json();

            $this->logUsage($provider, $model, $data);

            return [
                'success' => true,
                'content' => $data['choices'][0]['message']['content'] ?? null,
                'model' => $data['model'] ?? $model,
                'provider' => $provider,
                'usage' => $data['usage'] ?? null,
                'finish_reason' => $data['choices'][0]['finish_reason'] ?? null,
            ];

        } catch (\Exception $e) {
            // Try fallback on exception
            $result = $this->tryFallbackProviders($messages, $model, $params, 'chat');
            if ($result) {
                return $result;
            }

            $this->lastError = "API request failed ({$provider}): " . $e->getMessage();
            Log::error('Inference API error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'model' => $model,
            ]);
            return null;
        }
    }

    /**
     * Try fallback providers when the primary fails.
     */
    protected function tryFallbackProviders(
        array $messages,
        string $originalModel,
        array $params,
        string $task
    ): ?array {
        $fallbackChain = $this->config['fallback_chain'] ?? [];
        $triedProviders = [$this->currentProvider];

        foreach ($fallbackChain as $provider) {
            if (in_array($provider, $triedProviders)) {
                continue;
            }

            if (!$this->isProviderAvailable($provider) || !$this->providerSupportsTask($provider, $task)) {
                continue;
            }

            if (!$this->checkRateLimit($provider)) {
                continue;
            }

            $triedProviders[] = $provider;
            $providerConfig = $this->getProviderConfig($provider);
            $model = $providerConfig['models'][$task] ?? null;

            if (!$model) {
                continue;
            }

            Log::info("Falling back to provider: {$provider}", ['from' => $this->currentProvider]);

            try {
                $payload = array_merge([
                    'model' => $model,
                    'messages' => $messages,
                ], $params);

                $response = $this->makeRequest('POST', '/chat/completions', $payload, $provider);

                if ($response->successful()) {
                    $data = $response->json();
                    $this->logUsage($provider, $model, $data);

                    return [
                        'success' => true,
                        'content' => $data['choices'][0]['message']['content'] ?? null,
                        'model' => $data['model'] ?? $model,
                        'provider' => $provider,
                        'usage' => $data['usage'] ?? null,
                        'finish_reason' => $data['choices'][0]['finish_reason'] ?? null,
                        'fallback' => true,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("Fallback provider {$provider} failed", ['error' => $e->getMessage()]);
                continue;
            }
        }

        return null;
    }

    // =========================================================================
    // CONVENIENCE METHODS
    // =========================================================================

    /**
     * Extract text from an image using OCR.
     */
    public function extractTextFromImage(string $imagePath, bool $isHandwritten = false): ?string
    {
        $prompt = $isHandwritten
            ? config('openrouter.prompts.ocr_handwriting')
            : config('openrouter.prompts.ocr');

        $result = $this->vision($imagePath, $prompt);

        if (!$result || !$result['success']) {
            return null;
        }

        return $result['content'];
    }

    /**
     * Extract named entities from text.
     */
    public function extractEntities(string $text, ?string $provider = null): ?array
    {
        $provider = $provider ?? $this->getBestProviderForTask('ner');
        $providerConfig = $this->getProviderConfig($provider);
        $model = $providerConfig['models']['ner'] ?? null;

        $messages = [
            [
                'role' => 'system',
                'content' => config('openrouter.prompts.entity_extraction'),
            ],
            [
                'role' => 'user',
                'content' => $text,
            ],
        ];

        $result = $this->chat(
            $messages,
            $model,
            $this->config['model_params']['ner'] ?? [],
            $provider
        );

        if (!$result || !$result['success']) {
            return null;
        }

        return $this->parseJsonResponse($result['content']);
    }

    /**
     * Classify a document based on its content.
     */
    public function classifyDocument(string $text, ?string $provider = null): ?string
    {
        $provider = $provider ?? $this->getBestProviderForTask('classification');
        $providerConfig = $this->getProviderConfig($provider);
        $model = $providerConfig['models']['classification'] ?? null;

        $messages = [
            [
                'role' => 'system',
                'content' => config('openrouter.prompts.document_classification'),
            ],
            [
                'role' => 'user',
                'content' => mb_substr($text, 0, 4000),
            ],
        ];

        $result = $this->chat(
            $messages,
            $model,
            $this->config['model_params']['classification'] ?? [],
            $provider
        );

        if (!$result || !$result['success']) {
            return null;
        }

        $classification = strtolower(trim($result['content']));

        $validTypes = [
            'invoice', 'letter', 'report', 'id_document', 'contract',
            'receipt', 'form', 'certificate', 'news_article', 'screenshot',
            'handwritten_note', 'unknown'
        ];

        return in_array($classification, $validTypes) ? $classification : 'unknown';
    }

    /**
     * Detect languages in text.
     */
    public function detectLanguage(string $text, ?string $provider = null): ?array
    {
        $provider = $provider ?? $this->getBestProviderForTask('language_detection');
        $providerConfig = $this->getProviderConfig($provider);
        $model = $providerConfig['models']['language_detection'] ?? null;

        $messages = [
            [
                'role' => 'system',
                'content' => config('openrouter.prompts.language_detection'),
            ],
            [
                'role' => 'user',
                'content' => mb_substr($text, 0, 2000),
            ],
        ];

        $result = $this->chat(
            $messages,
            $model,
            ['temperature' => 0.0, 'max_tokens' => 256],
            $provider
        );

        if (!$result || !$result['success']) {
            return null;
        }

        return $this->parseJsonResponse($result['content']);
    }

    /**
     * Translate text between languages.
     */
    public function translate(
        string $text,
        string $targetLanguage,
        ?string $sourceLanguage = null,
        ?string $provider = null
    ): ?array {
        $provider = $provider ?? $this->getBestProviderForTask('translation');
        $providerConfig = $this->getProviderConfig($provider);
        $model = $providerConfig['models']['translation'] ?? null;

        $supportedLanguages = config('openrouter.supported_languages', []);

        $sourceLanguageName = $sourceLanguage
            ? ($supportedLanguages[$sourceLanguage] ?? $sourceLanguage)
            : 'the source language (auto-detect)';
        $targetLanguageName = $supportedLanguages[$targetLanguage] ?? $targetLanguage;

        $prompt = str_replace(
            ['{source_language}', '{target_language}'],
            [$sourceLanguageName, $targetLanguageName],
            config('openrouter.prompts.translation')
        );

        $messages = [
            [
                'role' => 'system',
                'content' => $prompt,
            ],
            [
                'role' => 'user',
                'content' => $text,
            ],
        ];

        $startTime = microtime(true);

        $result = $this->chat(
            $messages,
            $model,
            $this->config['model_params']['translation'] ?? [],
            $provider
        );

        $processingTime = (int) ((microtime(true) - $startTime) * 1000);

        if (!$result || !$result['success']) {
            return null;
        }

        return [
            'translated_text' => $result['content'],
            'source_language' => $sourceLanguage,
            'target_language' => $targetLanguage,
            'model' => $result['model'],
            'provider' => $result['provider'],
            'processing_time_ms' => $processingTime,
            'token_count' => $result['usage']['total_tokens'] ?? null,
        ];
    }

    // =========================================================================
    // PROVIDER INFORMATION
    // =========================================================================

    /**
     * Get provider configuration.
     */
    public function getProviderConfig(string $provider): array
    {
        return $this->config['providers'][$provider] ?? [];
    }

    /**
     * Get the default model for a task on a provider.
     */
    public function getModelForTask(string $task, ?string $provider = null): ?string
    {
        $provider = $provider ?? $this->currentProvider;
        $providerConfig = $this->getProviderConfig($provider);
        return $providerConfig['models'][$task] ?? null;
    }

    /**
     * Get free models for a provider.
     */
    public function getFreeModels(?string $provider = null): array
    {
        $provider = $provider ?? $this->currentProvider;
        $providerConfig = $this->getProviderConfig($provider);
        return $providerConfig['free_models'] ?? [];
    }

    /**
     * Test connection to a provider.
     */
    public function testConnection(?string $provider = null): array
    {
        $provider = $provider ?? $this->currentProvider;

        if (!$this->isProviderAvailable($provider)) {
            return [
                'success' => false,
                'provider' => $provider,
                'error' => 'Provider not configured or disabled',
            ];
        }

        $result = $this->chat(
            [['role' => 'user', 'content' => 'Hello, respond with just "OK".']],
            null,
            ['max_tokens' => 10],
            $provider
        );

        if ($result && $result['success']) {
            return [
                'success' => true,
                'provider' => $provider,
                'model' => $result['model'],
                'message' => 'Connection successful',
            ];
        }

        return [
            'success' => false,
            'provider' => $provider,
            'error' => $this->lastError ?? 'Unknown error',
        ];
    }

    /**
     * Test all configured providers.
     */
    public function testAllProviders(): array
    {
        $results = [];

        foreach (array_keys($this->config['providers']) as $provider) {
            $results[$provider] = $this->testConnection($provider);
        }

        return $results;
    }

    // =========================================================================
    // UTILITY METHODS
    // =========================================================================

    /**
     * Get the last error message.
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Get the last usage stats.
     */
    public function getLastUsage(): ?array
    {
        return $this->lastUsage;
    }

    // =========================================================================
    // PRIVATE HELPER METHODS
    // =========================================================================

    /**
     * Create the HTTP client for a provider.
     */
    protected function getHttpClient(string $provider): PendingRequest
    {
        $providerConfig = $this->getProviderConfig($provider);
        $httpConfig = $this->config['http'] ?? [];

        $headers = [
            'Authorization' => 'Bearer ' . ($providerConfig['api_key'] ?? ''),
            'Content-Type' => 'application/json',
        ];

        // Add provider-specific headers
        if (isset($providerConfig['headers'])) {
            $headers = array_merge($headers, $providerConfig['headers']);
        }

        return Http::withHeaders($headers)
            ->timeout($httpConfig['timeout'] ?? 120)
            ->connectTimeout($httpConfig['connect_timeout'] ?? 10)
            ->retry(
                $httpConfig['retry_attempts'] ?? 3,
                $httpConfig['retry_delay_ms'] ?? 1000,
                fn ($exception, $request) => $this->shouldRetry($exception)
            );
    }

    /**
     * Make an API request to a provider.
     */
    protected function makeRequest(
        string $method,
        string $endpoint,
        array $data = [],
        ?string $provider = null
    ): Response {
        $provider = $provider ?? $this->currentProvider;
        $providerConfig = $this->getProviderConfig($provider);

        $client = $this->getHttpClient($provider);
        $baseUrl = $providerConfig['base_url'] ?? '';
        $url = $baseUrl . $endpoint;

        if ($this->config['logging']['log_requests'] ?? false) {
            Log::debug('Inference API request', [
                'provider' => $provider,
                'method' => $method,
                'endpoint' => $endpoint,
                'model' => $data['model'] ?? 'N/A',
            ]);
        }

        $response = match (strtoupper($method)) {
            'GET' => $client->get($url, $data),
            'POST' => $client->post($url, $data),
            'PUT' => $client->put($url, $data),
            'DELETE' => $client->delete($url),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };

        if ($this->config['logging']['log_responses'] ?? false) {
            Log::debug('Inference API response', [
                'provider' => $provider,
                'status' => $response->status(),
                'success' => $response->successful(),
            ]);
        }

        return $response;
    }

    /**
     * Prepare image content for API request.
     */
    protected function prepareImageContent(string $imagePath): ?string
    {
        // Check if already base64 data URL
        if (str_starts_with($imagePath, 'data:image/')) {
            return $imagePath;
        }

        // Check if URL
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return $imagePath;
        }

        // Read file and encode
        if (!file_exists($imagePath)) {
            Log::error('Inference: Image file not found', ['path' => $imagePath]);
            return null;
        }

        $imageData = file_get_contents($imagePath);
        if ($imageData === false) {
            Log::error('Inference: Failed to read image file', ['path' => $imagePath]);
            return null;
        }

        $mimeType = mime_content_type($imagePath) ?: 'image/png';
        return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    }

    /**
     * Parse JSON response from model output.
     */
    protected function parseJsonResponse(string $content): ?array
    {
        $content = trim($content);

        // Remove markdown code blocks if present
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $content, $matches)) {
            $content = trim($matches[1]);
        }

        // Try to find JSON array or object
        if (preg_match('/(\[[\s\S]*\]|\{[\s\S]*\})/', $content, $matches)) {
            $content = $matches[1];
        }

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Inference: Failed to parse JSON response', [
                'error' => json_last_error_msg(),
                'content' => substr($content, 0, 500),
            ]);
            return null;
        }

        return $decoded;
    }

    /**
     * Check rate limit for a provider.
     */
    protected function checkRateLimit(string $provider): bool
    {
        if (!($this->config['rate_limiting']['enabled'] ?? true)) {
            return true;
        }

        $providerConfig = $this->getProviderConfig($provider);
        $maxAttempts = $providerConfig['rate_limits']['requests_per_minute'] ?? 30;

        $key = "inference_rate_limit_{$provider}";

        return RateLimiter::attempt(
            $key,
            $maxAttempts,
            fn () => true,
            60
        );
    }

    /**
     * Handle error response from API.
     */
    protected function handleErrorResponse(Response $response, string $provider): void
    {
        $status = $response->status();
        $body = $response->json();

        $errorMessage = $body['error']['message'] ?? $response->body();

        $this->lastError = match ($status) {
            401 => "Invalid API key for {$provider}. Please check your credentials.",
            402 => "Insufficient credits for {$provider}.",
            429 => "Rate limit exceeded for {$provider}. Please wait.",
            500, 502, 503 => "{$provider} service temporarily unavailable.",
            default => "{$provider} API error ({$status}): {$errorMessage}",
        };

        Log::error('Inference API error response', [
            'provider' => $provider,
            'status' => $status,
            'error' => $this->lastError,
        ]);
    }

    /**
     * Determine if request should be retried.
     */
    protected function shouldRetry(\Exception $exception): bool
    {
        return $exception instanceof \Illuminate\Http\Client\ConnectionException
            || (method_exists($exception, 'response')
                && $exception->response?->status() >= 500);
    }

    /**
     * Log API usage for monitoring.
     */
    protected function logUsage(string $provider, string $model, array $response): void
    {
        if (!($this->config['logging']['enabled'] ?? true)) {
            return;
        }

        $usage = $response['usage'] ?? [];

        $this->lastUsage = [
            'provider' => $provider,
            'model' => $model,
            'prompt_tokens' => $usage['prompt_tokens'] ?? 0,
            'completion_tokens' => $usage['completion_tokens'] ?? 0,
            'total_tokens' => $usage['total_tokens'] ?? 0,
        ];

        Log::info('Inference API usage', $this->lastUsage);
    }
}
