<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentAnalysis;
use App\Models\DocumentTranslation;
use Illuminate\Support\Facades\Log;

/**
 * Service for document translation using OpenRouter.ai free models.
 *
 * Supports translating extracted document text between multiple languages
 * using LLMs like Llama, Mistral, and other free-tier models.
 */
class TranslationService
{
    protected OpenRouterService $openRouter;
    protected array $config;

    public function __construct(OpenRouterService $openRouter)
    {
        $this->openRouter = $openRouter;
        $this->config = config('openrouter');
    }

    // =========================================================================
    // MAIN TRANSLATION METHODS
    // =========================================================================

    /**
     * Translate text to a target language.
     *
     * @param string $text Text to translate
     * @param string $targetLanguage Target language code (ISO 639-1)
     * @param string|null $sourceLanguage Source language code (null for auto-detect)
     * @return array|null Translation result
     */
    public function translate(
        string $text,
        string $targetLanguage,
        ?string $sourceLanguage = null
    ): ?array {
        if (!$this->openRouter->isConfigured()) {
            Log::error('OpenRouter not configured for translation');
            return null;
        }

        // Validate target language
        if (!$this->isLanguageSupported($targetLanguage)) {
            Log::warning('Unsupported target language', ['language' => $targetLanguage]);
            return null;
        }

        // Auto-detect source language if not provided
        if (!$sourceLanguage) {
            $detected = $this->detectLanguage($text);
            $sourceLanguage = $detected['primary'] ?? 'en';
        }

        // Don't translate if source and target are the same
        if ($sourceLanguage === $targetLanguage) {
            return [
                'translated_text' => $text,
                'source_language' => $sourceLanguage,
                'target_language' => $targetLanguage,
                'translation_confidence' => 100,
                'translation_provider' => DocumentTranslation::PROVIDER_LOCAL,
                'model_used' => null,
                'processing_time_ms' => 0,
                'token_count' => 0,
                'note' => 'Source and target languages are the same, no translation needed.',
            ];
        }

        // For long texts, split into chunks
        $maxChunkSize = 4000; // Characters per chunk
        if (mb_strlen($text) > $maxChunkSize) {
            return $this->translateLongText($text, $targetLanguage, $sourceLanguage);
        }

        return $this->performTranslation($text, $targetLanguage, $sourceLanguage);
    }

    /**
     * Translate a document's extracted text.
     *
     * @param int $documentId Document analysis ID
     * @param string $targetLanguage Target language code
     * @return DocumentTranslation|null Created translation or null on failure
     */
    public function translateDocument(int $documentId, string $targetLanguage): ?DocumentTranslation
    {
        $analysis = DocumentAnalysis::find($documentId);

        if (!$analysis) {
            Log::error('Document not found for translation', ['id' => $documentId]);
            return null;
        }

        if (empty($analysis->extracted_text)) {
            Log::warning('No text to translate', ['document_id' => $documentId]);
            return null;
        }

        // Check if translation already exists
        $existing = $analysis->getTranslation($targetLanguage);
        if ($existing) {
            Log::info('Translation already exists', [
                'document_id' => $documentId,
                'target_language' => $targetLanguage,
            ]);
            return $existing;
        }

        $sourceLanguage = $analysis->language_detected ?? 'en';

        $result = $this->translate(
            $analysis->extracted_text,
            $targetLanguage,
            $sourceLanguage
        );

        if (!$result) {
            Log::error('Translation failed', [
                'document_id' => $documentId,
                'target_language' => $targetLanguage,
            ]);
            return null;
        }

        // Create translation record
        $translation = DocumentTranslation::create([
            'document_analysis_id' => $documentId,
            'source_language' => $result['source_language'],
            'target_language' => $result['target_language'],
            'translated_text' => $result['translated_text'],
            'translation_confidence' => $result['translation_confidence'] ?? null,
            'translation_provider' => $result['translation_provider'] ?? DocumentTranslation::PROVIDER_OPENROUTER,
            'model_used' => $result['model_used'] ?? null,
            'processing_time_ms' => $result['processing_time_ms'] ?? null,
            'token_count' => $result['token_count'] ?? null,
        ]);

        Log::info('Document translation created', [
            'document_id' => $documentId,
            'translation_id' => $translation->id,
            'source' => $sourceLanguage,
            'target' => $targetLanguage,
        ]);

        return $translation;
    }

    // =========================================================================
    // LANGUAGE DETECTION
    // =========================================================================

    /**
     * Detect the language(s) of a text.
     *
     * @param string $text Text to analyze
     * @return array{primary: string, all: array}
     */
    public function detectLanguage(string $text): array
    {
        // Use OpenRouter for detection
        $result = $this->openRouter->detectLanguage($text);

        if ($result && is_array($result) && !empty($result)) {
            // Sort by confidence
            usort($result, fn ($a, $b) => ($b['confidence'] ?? 0) <=> ($a['confidence'] ?? 0));

            return [
                'primary' => $result[0]['language'] ?? 'en',
                'all' => $result,
            ];
        }

        // Fallback: simple character-based detection
        return $this->detectLanguageBasic($text);
    }

    /**
     * Basic language detection using character patterns.
     */
    protected function detectLanguageBasic(string $text): array
    {
        // Cyrillic characters (Russian, Ukrainian, etc.)
        if (preg_match('/[\x{0400}-\x{04FF}]/u', $text)) {
            // Ukrainian-specific characters
            if (preg_match('/[\x{0404}\x{0406}\x{0407}\x{0454}\x{0456}\x{0457}\x{0490}\x{0491}]/u', $text)) {
                return ['primary' => 'uk', 'all' => [['language' => 'uk', 'confidence' => 70]]];
            }
            return ['primary' => 'ru', 'all' => [['language' => 'ru', 'confidence' => 70]]];
        }

        // Arabic script
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return ['primary' => 'ar', 'all' => [['language' => 'ar', 'confidence' => 70]]];
        }

        // Chinese characters
        if (preg_match('/[\x{4E00}-\x{9FFF}]/u', $text)) {
            return ['primary' => 'zh', 'all' => [['language' => 'zh', 'confidence' => 70]]];
        }

        // Japanese (Hiragana/Katakana)
        if (preg_match('/[\x{3040}-\x{30FF}]/u', $text)) {
            return ['primary' => 'ja', 'all' => [['language' => 'ja', 'confidence' => 70]]];
        }

        // Korean
        if (preg_match('/[\x{AC00}-\x{D7AF}]/u', $text)) {
            return ['primary' => 'ko', 'all' => [['language' => 'ko', 'confidence' => 70]]];
        }

        // Default to English
        return ['primary' => 'en', 'all' => [['language' => 'en', 'confidence' => 50]]];
    }

    // =========================================================================
    // LANGUAGE SUPPORT
    // =========================================================================

    /**
     * Get list of supported languages.
     *
     * @return array<string, string> Language code => name
     */
    public function getSupportedLanguages(): array
    {
        return $this->config['supported_languages'] ?? [];
    }

    /**
     * Check if a language is supported.
     *
     * @param string $languageCode ISO 639-1 language code
     * @return bool
     */
    public function isLanguageSupported(string $languageCode): bool
    {
        $supported = $this->getSupportedLanguages();
        return isset($supported[$languageCode]);
    }

    /**
     * Get the name of a language by its code.
     *
     * @param string $languageCode ISO 639-1 language code
     * @return string|null Language name or null if not found
     */
    public function getLanguageName(string $languageCode): ?string
    {
        $supported = $this->getSupportedLanguages();
        return $supported[$languageCode] ?? null;
    }

    /**
     * Get common translation pairs (frequently used language combinations).
     *
     * @return array<array{source: string, target: string}>
     */
    public function getCommonLanguagePairs(): array
    {
        return [
            ['source' => 'uk', 'target' => 'en'],
            ['source' => 'ru', 'target' => 'en'],
            ['source' => 'en', 'target' => 'uk'],
            ['source' => 'en', 'target' => 'ru'],
            ['source' => 'de', 'target' => 'en'],
            ['source' => 'fr', 'target' => 'en'],
            ['source' => 'ar', 'target' => 'en'],
            ['source' => 'zh', 'target' => 'en'],
        ];
    }

    // =========================================================================
    // BATCH TRANSLATION
    // =========================================================================

    /**
     * Translate multiple texts at once.
     *
     * @param array<string> $texts Array of texts to translate
     * @param string $targetLanguage Target language code
     * @param string|null $sourceLanguage Source language code
     * @return array<array> Array of translation results
     */
    public function translateBatch(
        array $texts,
        string $targetLanguage,
        ?string $sourceLanguage = null
    ): array {
        $results = [];

        foreach ($texts as $index => $text) {
            $results[$index] = $this->translate($text, $targetLanguage, $sourceLanguage);
        }

        return $results;
    }

    // =========================================================================
    // PRIVATE HELPER METHODS
    // =========================================================================

    /**
     * Perform the actual translation.
     */
    protected function performTranslation(
        string $text,
        string $targetLanguage,
        string $sourceLanguage
    ): ?array {
        $result = $this->openRouter->translate($text, $targetLanguage, $sourceLanguage);

        if (!$result) {
            Log::error('OpenRouter translation failed', [
                'error' => $this->openRouter->getLastError(),
            ]);
            return null;
        }

        return [
            'translated_text' => $result['translated_text'],
            'source_language' => $result['source_language'] ?? $sourceLanguage,
            'target_language' => $result['target_language'] ?? $targetLanguage,
            'translation_confidence' => $this->estimateConfidence($text, $result['translated_text']),
            'translation_provider' => DocumentTranslation::PROVIDER_OPENROUTER,
            'model_used' => $result['model'] ?? null,
            'processing_time_ms' => $result['processing_time_ms'] ?? null,
            'token_count' => $result['token_count'] ?? null,
        ];
    }

    /**
     * Translate long text by splitting into chunks.
     */
    protected function translateLongText(
        string $text,
        string $targetLanguage,
        string $sourceLanguage
    ): ?array {
        $chunks = $this->splitTextIntoChunks($text);
        $translatedChunks = [];
        $totalProcessingTime = 0;
        $totalTokens = 0;
        $model = null;

        foreach ($chunks as $chunk) {
            $result = $this->performTranslation($chunk, $targetLanguage, $sourceLanguage);

            if (!$result) {
                Log::error('Chunk translation failed');
                return null;
            }

            $translatedChunks[] = $result['translated_text'];
            $totalProcessingTime += $result['processing_time_ms'] ?? 0;
            $totalTokens += $result['token_count'] ?? 0;
            $model = $result['model_used'] ?? $model;

            // Small delay between chunks to avoid rate limiting
            usleep(200000); // 200ms
        }

        $fullTranslation = implode("\n\n", $translatedChunks);

        return [
            'translated_text' => $fullTranslation,
            'source_language' => $sourceLanguage,
            'target_language' => $targetLanguage,
            'translation_confidence' => $this->estimateConfidence($text, $fullTranslation),
            'translation_provider' => DocumentTranslation::PROVIDER_OPENROUTER,
            'model_used' => $model,
            'processing_time_ms' => $totalProcessingTime,
            'token_count' => $totalTokens,
            'chunks_processed' => count($chunks),
        ];
    }

    /**
     * Split text into chunks for translation.
     */
    protected function splitTextIntoChunks(string $text, int $maxChunkSize = 4000): array
    {
        $chunks = [];
        $paragraphs = preg_split('/\n\s*\n/', $text);
        $currentChunk = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);

            if (empty($paragraph)) {
                continue;
            }

            // If paragraph itself is too long, split by sentences
            if (mb_strlen($paragraph) > $maxChunkSize) {
                if (!empty($currentChunk)) {
                    $chunks[] = $currentChunk;
                    $currentChunk = '';
                }
                $chunks = array_merge($chunks, $this->splitParagraphBySentences($paragraph, $maxChunkSize));
                continue;
            }

            // Check if adding this paragraph would exceed limit
            if (mb_strlen($currentChunk) + mb_strlen($paragraph) + 2 > $maxChunkSize) {
                if (!empty($currentChunk)) {
                    $chunks[] = $currentChunk;
                }
                $currentChunk = $paragraph;
            } else {
                $currentChunk .= (empty($currentChunk) ? '' : "\n\n") . $paragraph;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }

    /**
     * Split a paragraph by sentences.
     */
    protected function splitParagraphBySentences(string $paragraph, int $maxSize): array
    {
        $chunks = [];
        $sentences = preg_split('/(?<=[.!?])\s+/', $paragraph);
        $currentChunk = '';

        foreach ($sentences as $sentence) {
            if (mb_strlen($currentChunk) + mb_strlen($sentence) + 1 > $maxSize) {
                if (!empty($currentChunk)) {
                    $chunks[] = $currentChunk;
                }
                // If single sentence is too long, split by words
                if (mb_strlen($sentence) > $maxSize) {
                    $chunks = array_merge($chunks, $this->splitByWords($sentence, $maxSize));
                    $currentChunk = '';
                } else {
                    $currentChunk = $sentence;
                }
            } else {
                $currentChunk .= (empty($currentChunk) ? '' : ' ') . $sentence;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }

    /**
     * Split text by words as last resort.
     */
    protected function splitByWords(string $text, int $maxSize): array
    {
        $chunks = [];
        $words = explode(' ', $text);
        $currentChunk = '';

        foreach ($words as $word) {
            if (mb_strlen($currentChunk) + mb_strlen($word) + 1 > $maxSize) {
                if (!empty($currentChunk)) {
                    $chunks[] = $currentChunk;
                }
                $currentChunk = $word;
            } else {
                $currentChunk .= (empty($currentChunk) ? '' : ' ') . $word;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }

    /**
     * Estimate translation confidence based on text comparison.
     */
    protected function estimateConfidence(string $original, string $translation): int
    {
        // Simple heuristics for confidence estimation
        $originalWords = str_word_count($original);
        $translatedWords = str_word_count($translation);

        // If translation is too short or too long compared to original, lower confidence
        if ($originalWords > 0) {
            $ratio = $translatedWords / $originalWords;

            // Typical translation ratio should be between 0.5 and 2.0
            if ($ratio < 0.3 || $ratio > 3.0) {
                return 50; // Suspicious ratio
            }

            if ($ratio < 0.5 || $ratio > 2.0) {
                return 70; // Borderline ratio
            }
        }

        // Check if translation contains source language text (failed translation)
        if ($translation === $original) {
            return 30; // No translation occurred
        }

        // Base confidence
        return 85;
    }
}
