<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Document Translation Model
 *
 * Represents a translation of a document's extracted text into another language.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property int $document_analysis_id Foreign key to document_analyses table
 * @property string $source_language Source language (ISO 639-1 code)
 * @property string $target_language Target language (ISO 639-1 code)
 * @property string $translated_text The translated text content
 * @property int|null $translation_confidence Confidence score (0-100)
 * @property string $translation_provider Provider: openrouter, google, deepl, local
 * @property string|null $model_used The model used for translation
 * @property int|null $processing_time_ms Processing time in milliseconds
 * @property int|null $token_count Number of tokens processed
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon $updated_at Record update timestamp
 *
 * Relationships:
 * @property-read DocumentAnalysis $documentAnalysis The parent document analysis
 */
class DocumentTranslation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'document_analysis_id',
        'source_language',
        'target_language',
        'translated_text',
        'translation_confidence',
        'translation_provider',
        'model_used',
        'processing_time_ms',
        'token_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'translation_confidence' => 'integer',
        'processing_time_ms' => 'integer',
        'token_count' => 'integer',
    ];

    /**
     * Translation provider constants.
     */
    public const PROVIDER_OPENROUTER = 'openrouter';
    public const PROVIDER_GOOGLE = 'google';
    public const PROVIDER_DEEPL = 'deepl';
    public const PROVIDER_LOCAL = 'local';

    /**
     * Confidence thresholds.
     */
    public const CONFIDENCE_HIGH = 80;
    public const CONFIDENCE_MEDIUM = 50;

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the document analysis this translation belongs to.
     */
    public function documentAnalysis(): BelongsTo
    {
        return $this->belongsTo(DocumentAnalysis::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope to filter by source language.
     */
    public function scopeFromLanguage($query, string $language)
    {
        return $query->where('source_language', $language);
    }

    /**
     * Scope to filter by target language.
     */
    public function scopeToLanguage($query, string $language)
    {
        return $query->where('target_language', $language);
    }

    /**
     * Scope to filter by provider.
     */
    public function scopeProvider($query, string $provider)
    {
        return $query->where('translation_provider', $provider);
    }

    /**
     * Scope to filter by minimum confidence.
     */
    public function scopeMinConfidence($query, int $confidence)
    {
        return $query->where('translation_confidence', '>=', $confidence);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Get the translated text, optionally truncated.
     */
    public function getText(int $maxLength = null): string
    {
        if ($maxLength && strlen($this->translated_text) > $maxLength) {
            return substr($this->translated_text, 0, $maxLength) . '...';
        }

        return $this->translated_text;
    }

    /**
     * Get the word count of translated text.
     */
    public function getWordCount(): int
    {
        return str_word_count($this->translated_text);
    }

    /**
     * Get the confidence level as a string.
     */
    public function getConfidenceLevel(): string
    {
        if ($this->translation_confidence === null) {
            return 'unknown';
        }

        if ($this->translation_confidence >= self::CONFIDENCE_HIGH) {
            return 'high';
        }

        if ($this->translation_confidence >= self::CONFIDENCE_MEDIUM) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Get the source language name.
     */
    public function getSourceLanguageName(): string
    {
        $languages = config('openrouter.supported_languages', []);

        return $languages[$this->source_language] ?? $this->source_language;
    }

    /**
     * Get the target language name.
     */
    public function getTargetLanguageName(): string
    {
        $languages = config('openrouter.supported_languages', []);

        return $languages[$this->target_language] ?? $this->target_language;
    }

    /**
     * Get a formatted processing time string.
     */
    public function getFormattedProcessingTime(): string
    {
        if (!$this->processing_time_ms) {
            return 'N/A';
        }

        if ($this->processing_time_ms >= 1000) {
            return number_format($this->processing_time_ms / 1000, 2) . 's';
        }

        return $this->processing_time_ms . 'ms';
    }

    /**
     * Get the translation language pair as a string.
     */
    public function getLanguagePair(): string
    {
        return "{$this->source_language} -> {$this->target_language}";
    }

    /**
     * Get available translation providers.
     *
     * @return array<string, string>
     */
    public static function getProviders(): array
    {
        return [
            self::PROVIDER_OPENROUTER => 'OpenRouter',
            self::PROVIDER_GOOGLE => 'Google Translate',
            self::PROVIDER_DEEPL => 'DeepL',
            self::PROVIDER_LOCAL => 'Local Model',
        ];
    }
}
