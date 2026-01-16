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
 * Service for interacting with OpenRouter.ai API.
 *
 * Provides methods for chat completion, vision analysis, and
 * access to free LLM models for OCR, translation, and entity extraction.
 */
class OpenRouterService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected array $config;
    protected ?string $lastError = null;

    public function __construct()
    {
        $this->config = config('openrouter');
        $this->apiKey = $this->config['api_key'] ?? '';
        $this->baseUrl = $this->config['base_url'] ?? 'https://openrouter.ai/api/v1';
    }

    // =========================================================================
    // CORE API METHODS
    // =========================================================================

    /**
     * Send a chat completion request.
     *
     * @param array $messages Array of message objects [{role: string, content: string}]
     * @param string|null $model Model to use (defaults to config)
     * @param array $params Additional parameters (temperature, max_tokens, etc.)
     * @return array|null Response data or null on failure
     */
    public function chat(array $messages, ?string $model = null, array $params = []): ?array
    {
        $model = $model ?? $this->config['models']['chat'];
        $defaultParams = $this->config['model_params']['default'] ?? [];

        return $this->completions($messages, $model, array_merge($defaultParams, $params));
    }

    /**
     * Send a vision analysis request with an image.
     *
     * @param string $imagePath Path to the image file or base64 encoded image
     * @param string $prompt The prompt/question about the image
     * @param string|null $model Vision model to use
     * @param array $params Additional parameters
     * @return array|null Response data or null on failure
     */
    public function vision(string $imagePath, string $prompt, ?string $model = null, array $params = []): ?array
    {
        $model = $model ?? $this->config['models']['vision'];
        $defaultParams = $this->config['model_params']['ocr'] ?? [];

        // Prepare the image content
        $imageContent = $this->prepareImageContent($imagePath);
        if (!$imageContent) {
            $this->lastError = 'Failed to prepare image content';
            return null;
        }

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

        return $this->completions($messages, $model, array_merge($defaultParams, $params));
    }

    /**
     * Core completions endpoint.
     *
     * @param array $messages Messages array
     * @param string $model Model identifier
     * @param array $params Model parameters
     * @return array|null Response or null on failure
     */
    protected function completions(array $messages, string $model, array $params = []): ?array
    {
        if (!$this->checkRateLimit()) {
            $this->lastError = 'Rate limit exceeded. Please wait before making more requests.';
            Log::warning('OpenRouter rate limit exceeded');
            return null;
        }

        $payload = array_merge([
            'model' => $model,
            'messages' => $messages,
        ], $params);

        try {
            $response = $this->makeRequest('POST', '/chat/completions', $payload);

            if (!$response->successful()) {
                $this->handleErrorResponse($response);
                return null;
            }

            $data = $response->json();

            $this->logUsage($model, $data);

            return [
                'success' => true,
                'content' => $data['choices'][0]['message']['content'] ?? null,
                'model' => $data['model'] ?? $model,
                'usage' => $data['usage'] ?? null,
                'finish_reason' => $data['choices'][0]['finish_reason'] ?? null,
            ];

        } catch (\Exception $e) {
            $this->lastError = 'API request failed: ' . $e->getMessage();
            Log::error('OpenRouter API error', [
                'error' => $e->getMessage(),
                'model' => $model,
            ]);
            return null;
        }
    }

    // =========================================================================
    // CONVENIENCE METHODS
    // =========================================================================

    /**
     * Extract text from an image using OCR.
     *
     * @param string $imagePath Path to image or base64 content
     * @param bool $isHandwritten Whether the image contains handwriting
     * @return string|null Extracted text or null on failure
     */
    public function extractTextFromImage(string $imagePath, bool $isHandwritten = false): ?string
    {
        $prompt = $isHandwritten
            ? $this->config['prompts']['ocr_handwriting']
            : $this->config['prompts']['ocr'];

        $result = $this->vision($imagePath, $prompt);

        if (!$result || !$result['success']) {
            return null;
        }

        return $result['content'];
    }

    /**
     * Extract named entities from text.
     *
     * @param string $text The text to analyze
     * @return array|null Entities array or null on failure
     */
    public function extractEntities(string $text): ?array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->config['prompts']['entity_extraction'],
            ],
            [
                'role' => 'user',
                'content' => $text,
            ],
        ];

        $result = $this->chat(
            $messages,
            $this->config['models']['ner'],
            $this->config['model_params']['ner'] ?? []
        );

        if (!$result || !$result['success']) {
            return null;
        }

        return $this->parseJsonResponse($result['content']);
    }

    /**
     * Classify a document based on its content.
     *
     * @param string $text Document text content
     * @return string|null Document type or null on failure
     */
    public function classifyDocument(string $text): ?string
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->config['prompts']['document_classification'],
            ],
            [
                'role' => 'user',
                'content' => mb_substr($text, 0, 4000), // Limit text length
            ],
        ];

        $result = $this->chat(
            $messages,
            $this->config['models']['classification'],
            $this->config['model_params']['classification'] ?? []
        );

        if (!$result || !$result['success']) {
            return null;
        }

        $classification = strtolower(trim($result['content']));

        // Validate the classification
        $validTypes = [
            'invoice', 'letter', 'report', 'id_document', 'contract',
            'receipt', 'form', 'certificate', 'news_article', 'screenshot',
            'handwritten_note', 'unknown'
        ];

        return in_array($classification, $validTypes) ? $classification : 'unknown';
    }

    /**
     * Detect languages in text.
     *
     * @param string $text The text to analyze
     * @return array|null Array of languages with confidence or null on failure
     */
    public function detectLanguage(string $text): ?array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->config['prompts']['language_detection'],
            ],
            [
                'role' => 'user',
                'content' => mb_substr($text, 0, 2000), // Sample of text
            ],
        ];

        $result = $this->chat(
            $messages,
            $this->config['models']['language_detection'],
            ['temperature' => 0.0, 'max_tokens' => 256]
        );

        if (!$result || !$result['success']) {
            return null;
        }

        return $this->parseJsonResponse($result['content']);
    }

    /**
     * Translate text between languages.
     *
     * @param string $text Text to translate
     * @param string $targetLanguage Target language code
     * @param string|null $sourceLanguage Source language code (auto-detect if null)
     * @return array|null Translation result or null on failure
     */
    public function translate(
        string $text,
        string $targetLanguage,
        ?string $sourceLanguage = null
    ): ?array {
        $supportedLanguages = $this->config['supported_languages'] ?? [];

        $sourceLanguageName = $sourceLanguage
            ? ($supportedLanguages[$sourceLanguage] ?? $sourceLanguage)
            : 'the source language (auto-detect)';
        $targetLanguageName = $supportedLanguages[$targetLanguage] ?? $targetLanguage;

        $prompt = str_replace(
            ['{source_language}', '{target_language}'],
            [$sourceLanguageName, $targetLanguageName],
            $this->config['prompts']['translation']
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
            $this->config['models']['translation'],
            $this->config['model_params']['translation'] ?? []
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
            'processing_time_ms' => $processingTime,
            'token_count' => $result['usage']['total_tokens'] ?? null,
        ];
    }

    /**
     * Extract tables from text or image.
     *
     * @param string $content Text content or image path
     * @param bool $isImage Whether the content is an image
     * @return array|null Extracted tables or null on failure
     */
    public function extractTables(string $content, bool $isImage = false): ?array
    {
        $prompt = $this->config['prompts']['table_extraction'];

        if ($isImage) {
            $result = $this->vision($content, $prompt);
        } else {
            $messages = [
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user', 'content' => $content],
            ];
            $result = $this->chat($messages);
        }

        if (!$result || !$result['success']) {
            return null;
        }

        return $this->parseJsonResponse($result['content']);
    }

    // =========================================================================
    // MODEL INFORMATION
    // =========================================================================

    /**
     * Get list of available free models.
     *
     * @return array List of free model identifiers
     */
    public function getFreeModels(): array
    {
        return $this->config['free_models'] ?? [];
    }

    /**
     * Get the default model for a specific task.
     *
     * @param string $task Task name (chat, vision, translation, ner, classification)
     * @return string Model identifier
     */
    public function getModelForTask(string $task): string
    {
        return $this->config['models'][$task] ?? $this->config['models']['chat'];
    }

    /**
     * Check if a model is available and free.
     *
     * @param string $model Model identifier
     * @return bool
     */
    public function isModelFree(string $model): bool
    {
        $freeModels = $this->getFreeModels();
        return in_array($model, $freeModels);
    }

    /**
     * Get supported languages for translation.
     *
     * @return array<string, string> Language code => name
     */
    public function getSupportedLanguages(): array
    {
        return $this->config['supported_languages'] ?? [];
    }

    // =========================================================================
    // UTILITY METHODS
    // =========================================================================

    /**
     * Get the last error message.
     *
     * @return string|null
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Check if the service is properly configured.
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Test the API connection.
     *
     * @return array Connection test result
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API key not configured',
            ];
        }

        $result = $this->chat([
            ['role' => 'user', 'content' => 'Hello, respond with just "OK".'],
        ], null, ['max_tokens' => 10]);

        if ($result && $result['success']) {
            return [
                'success' => true,
                'model' => $result['model'],
                'message' => 'Connection successful',
            ];
        }

        return [
            'success' => false,
            'error' => $this->lastError ?? 'Unknown error',
        ];
    }

    // =========================================================================
    // PRIVATE HELPER METHODS
    // =========================================================================

    /**
     * Create the HTTP client with proper configuration.
     */
    protected function getHttpClient(): PendingRequest
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        // Add custom headers from config
        if (isset($this->config['headers'])) {
            $headers = array_merge($headers, $this->config['headers']);
        }

        return Http::withHeaders($headers)
            ->timeout($this->config['http']['timeout'] ?? 120)
            ->connectTimeout($this->config['http']['connect_timeout'] ?? 10)
            ->retry(
                $this->config['http']['retry_attempts'] ?? 3,
                $this->config['http']['retry_delay_ms'] ?? 1000,
                fn ($exception, $request) => $this->shouldRetry($exception)
            );
    }

    /**
     * Make an API request.
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): Response
    {
        $client = $this->getHttpClient();
        $url = $this->baseUrl . $endpoint;

        if ($this->config['logging']['log_requests'] ?? false) {
            Log::debug('OpenRouter API request', [
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
            Log::debug('OpenRouter API response', [
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
            Log::error('OpenRouter: Image file not found', ['path' => $imagePath]);
            return null;
        }

        $imageData = file_get_contents($imagePath);
        if ($imageData === false) {
            Log::error('OpenRouter: Failed to read image file', ['path' => $imagePath]);
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
        // Try to extract JSON from the response
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
            Log::warning('OpenRouter: Failed to parse JSON response', [
                'error' => json_last_error_msg(),
                'content' => substr($content, 0, 500),
            ]);
            return null;
        }

        return $decoded;
    }

    /**
     * Check rate limit before making request.
     */
    protected function checkRateLimit(): bool
    {
        if (!($this->config['rate_limiting']['enabled'] ?? true)) {
            return true;
        }

        $key = 'openrouter_rate_limit';
        $maxAttempts = $this->config['rate_limiting']['requests_per_minute'] ?? 20;

        return RateLimiter::attempt(
            $key,
            $maxAttempts,
            fn () => true,
            60 // Reset after 60 seconds
        );
    }

    /**
     * Handle error response from API.
     */
    protected function handleErrorResponse(Response $response): void
    {
        $status = $response->status();
        $body = $response->json();

        $errorMessage = $body['error']['message'] ?? $response->body();

        $this->lastError = match ($status) {
            401 => 'Invalid API key. Please check your OpenRouter credentials.',
            402 => 'Insufficient credits. Please add credits to your OpenRouter account.',
            429 => 'Rate limit exceeded. Please wait before making more requests.',
            500, 502, 503 => 'OpenRouter service temporarily unavailable. Please try again later.',
            default => "API error ({$status}): {$errorMessage}",
        };

        Log::error('OpenRouter API error response', [
            'status' => $status,
            'error' => $this->lastError,
            'body' => $body,
        ]);
    }

    /**
     * Determine if request should be retried.
     */
    protected function shouldRetry(\Exception $exception): bool
    {
        // Retry on connection errors and 5xx errors
        return $exception instanceof \Illuminate\Http\Client\ConnectionException
            || (method_exists($exception, 'response')
                && $exception->response?->status() >= 500);
    }

    /**
     * Log API usage for monitoring.
     */
    protected function logUsage(string $model, array $response): void
    {
        if (!($this->config['logging']['enabled'] ?? true)) {
            return;
        }

        $usage = $response['usage'] ?? [];

        Log::info('OpenRouter API usage', [
            'model' => $model,
            'prompt_tokens' => $usage['prompt_tokens'] ?? 0,
            'completion_tokens' => $usage['completion_tokens'] ?? 0,
            'total_tokens' => $usage['total_tokens'] ?? 0,
        ]);
    }
}
