<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Document Analysis Model
 *
 * Represents an OCR analysis of a document including text extraction,
 * entity recognition, table detection, and language identification.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property string $uuid Unique public identifier
 * @property string $original_filename Original uploaded filename
 * @property string $stored_path Path to stored file
 * @property string $file_type File type: image, pdf, scan, screenshot
 * @property int $file_size File size in bytes
 * @property int|null $page_count Number of pages (for PDFs)
 * @property string|null $language_detected Primary detected language (ISO 639-1)
 * @property array|null $languages_present All detected languages with confidence scores
 * @property string|null $extracted_text Full extracted text content
 * @property int|null $text_confidence Overall OCR confidence (0-100)
 * @property array|null $text_blocks Positioned text blocks with individual confidence
 * @property array|null $detected_entities Named entities (names, dates, locations, organizations)
 * @property string $document_type_detected Classified document type
 * @property array|null $metadata Additional extracted metadata
 * @property array|null $tables_extracted Structured table data
 * @property bool $handwriting_detected Whether handwriting was detected
 * @property string $analysis_status Status: pending, processing, completed, failed
 * @property int|null $processing_time_ms Processing time in milliseconds
 * @property string|null $error_message Error message if analysis failed
 * @property int|null $event_id Foreign key to events table
 * @property int $created_by Foreign key to users table
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon $updated_at Record update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * Relationships:
 * @property-read Event|null $event The associated event (if any)
 * @property-read User $creator The user who uploaded the document
 * @property-read \Illuminate\Database\Eloquent\Collection|DocumentTranslation[] $translations Document translations
 */
class DocumentAnalysis extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'uuid',
        'original_filename',
        'stored_path',
        'file_type',
        'file_size',
        'page_count',
        'language_detected',
        'languages_present',
        'extracted_text',
        'text_confidence',
        'text_blocks',
        'detected_entities',
        'document_type_detected',
        'metadata',
        'tables_extracted',
        'handwriting_detected',
        'analysis_status',
        'processing_time_ms',
        'error_message',
        'event_id',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
        'page_count' => 'integer',
        'languages_present' => 'array',
        'text_confidence' => 'integer',
        'text_blocks' => 'array',
        'detected_entities' => 'array',
        'metadata' => 'array',
        'tables_extracted' => 'array',
        'handwriting_detected' => 'boolean',
        'processing_time_ms' => 'integer',
    ];

    /**
     * Analysis status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    /**
     * File type constants.
     */
    public const TYPE_IMAGE = 'image';
    public const TYPE_PDF = 'pdf';
    public const TYPE_SCAN = 'scan';
    public const TYPE_SCREENSHOT = 'screenshot';

    /**
     * Document type constants.
     */
    public const DOC_INVOICE = 'invoice';
    public const DOC_LETTER = 'letter';
    public const DOC_REPORT = 'report';
    public const DOC_ID_DOCUMENT = 'id_document';
    public const DOC_CONTRACT = 'contract';
    public const DOC_RECEIPT = 'receipt';
    public const DOC_FORM = 'form';
    public const DOC_CERTIFICATE = 'certificate';
    public const DOC_NEWS_ARTICLE = 'news_article';
    public const DOC_SCREENSHOT = 'screenshot';
    public const DOC_HANDWRITTEN_NOTE = 'handwritten_note';
    public const DOC_UNKNOWN = 'unknown';

    /**
     * Confidence thresholds.
     */
    public const CONFIDENCE_HIGH = 80;
    public const CONFIDENCE_MEDIUM = 50;
    public const CONFIDENCE_LOW = 30;

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the associated event.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who created this analysis.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all translations for this document.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(DocumentTranslation::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope to filter by analysis status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('analysis_status', $status);
    }

    /**
     * Scope to get pending analyses.
     */
    public function scopePending($query)
    {
        return $query->where('analysis_status', self::STATUS_PENDING);
    }

    /**
     * Scope to get completed analyses.
     */
    public function scopeCompleted($query)
    {
        return $query->where('analysis_status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to get processing analyses.
     */
    public function scopeProcessing($query)
    {
        return $query->where('analysis_status', self::STATUS_PROCESSING);
    }

    /**
     * Scope to get failed analyses.
     */
    public function scopeFailed($query)
    {
        return $query->where('analysis_status', self::STATUS_FAILED);
    }

    /**
     * Scope to filter by file type.
     */
    public function scopeFileType($query, string $type)
    {
        return $query->where('file_type', $type);
    }

    /**
     * Scope to filter by document type.
     */
    public function scopeDocumentType($query, string $type)
    {
        return $query->where('document_type_detected', $type);
    }

    /**
     * Scope to filter by language.
     */
    public function scopeLanguage($query, string $language)
    {
        return $query->where('language_detected', $language);
    }

    /**
     * Scope to filter by minimum confidence.
     */
    public function scopeMinConfidence($query, int $confidence)
    {
        return $query->where('text_confidence', '>=', $confidence);
    }

    /**
     * Scope to filter analyses with handwriting.
     */
    public function scopeWithHandwriting($query)
    {
        return $query->where('handwriting_detected', true);
    }

    /**
     * Scope to filter analyses with tables.
     */
    public function scopeWithTables($query)
    {
        return $query->whereNotNull('tables_extracted')
            ->whereRaw("JSON_LENGTH(tables_extracted) > 0");
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    // =========================================================================
    // STATUS MANAGEMENT METHODS
    // =========================================================================

    /**
     * Mark analysis as started.
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'analysis_status' => self::STATUS_PROCESSING,
            'error_message' => null,
        ]);
    }

    /**
     * Mark analysis as completed.
     */
    public function markAsCompleted(int $processingTimeMs): void
    {
        $this->update([
            'analysis_status' => self::STATUS_COMPLETED,
            'processing_time_ms' => $processingTimeMs,
        ]);
    }

    /**
     * Mark analysis as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'analysis_status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Check if the analysis is complete.
     */
    public function isCompleted(): bool
    {
        return $this->analysis_status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the analysis is pending.
     */
    public function isPending(): bool
    {
        return $this->analysis_status === self::STATUS_PENDING;
    }

    /**
     * Check if the analysis is processing.
     */
    public function isProcessing(): bool
    {
        return $this->analysis_status === self::STATUS_PROCESSING;
    }

    /**
     * Check if the analysis failed.
     */
    public function hasFailed(): bool
    {
        return $this->analysis_status === self::STATUS_FAILED;
    }

    // =========================================================================
    // TEXT EXTRACTION METHODS
    // =========================================================================

    /**
     * Get the extracted text, optionally truncated.
     */
    public function getText(int $maxLength = null): ?string
    {
        if (!$this->extracted_text) {
            return null;
        }

        if ($maxLength && strlen($this->extracted_text) > $maxLength) {
            return substr($this->extracted_text, 0, $maxLength) . '...';
        }

        return $this->extracted_text;
    }

    /**
     * Get the word count of extracted text.
     */
    public function getWordCount(): int
    {
        if (!$this->extracted_text) {
            return 0;
        }

        return str_word_count($this->extracted_text);
    }

    /**
     * Get the character count of extracted text.
     */
    public function getCharacterCount(): int
    {
        if (!$this->extracted_text) {
            return 0;
        }

        return mb_strlen($this->extracted_text);
    }

    /**
     * Get the confidence level as a string.
     */
    public function getConfidenceLevel(): string
    {
        if ($this->text_confidence === null) {
            return 'unknown';
        }

        if ($this->text_confidence >= self::CONFIDENCE_HIGH) {
            return 'high';
        }

        if ($this->text_confidence >= self::CONFIDENCE_MEDIUM) {
            return 'medium';
        }

        return 'low';
    }

    // =========================================================================
    // ENTITY EXTRACTION METHODS
    // =========================================================================

    /**
     * Get detected names.
     *
     * @return array<string>
     */
    public function getNames(): array
    {
        return $this->detected_entities['names'] ?? [];
    }

    /**
     * Get detected dates.
     *
     * @return array<string>
     */
    public function getDates(): array
    {
        return $this->detected_entities['dates'] ?? [];
    }

    /**
     * Get detected locations.
     *
     * @return array<string>
     */
    public function getLocations(): array
    {
        return $this->detected_entities['locations'] ?? [];
    }

    /**
     * Get detected organizations.
     *
     * @return array<string>
     */
    public function getOrganizations(): array
    {
        return $this->detected_entities['organizations'] ?? [];
    }

    /**
     * Get all entities as a flat array.
     */
    public function getAllEntities(): array
    {
        return [
            'names' => $this->getNames(),
            'dates' => $this->getDates(),
            'locations' => $this->getLocations(),
            'organizations' => $this->getOrganizations(),
        ];
    }

    /**
     * Check if any entities were detected.
     */
    public function hasEntities(): bool
    {
        $entities = $this->detected_entities ?? [];

        return !empty($entities['names'] ?? [])
            || !empty($entities['dates'] ?? [])
            || !empty($entities['locations'] ?? [])
            || !empty($entities['organizations'] ?? []);
    }

    /**
     * Get total entity count.
     */
    public function getEntityCount(): int
    {
        return count($this->getNames())
            + count($this->getDates())
            + count($this->getLocations())
            + count($this->getOrganizations());
    }

    // =========================================================================
    // TABLE EXTRACTION METHODS
    // =========================================================================

    /**
     * Check if tables were extracted.
     */
    public function hasTables(): bool
    {
        return !empty($this->tables_extracted);
    }

    /**
     * Get the number of extracted tables.
     */
    public function getTableCount(): int
    {
        return count($this->tables_extracted ?? []);
    }

    /**
     * Get a specific table by index.
     */
    public function getTable(int $index): ?array
    {
        return $this->tables_extracted[$index] ?? null;
    }

    /**
     * Get all tables.
     */
    public function getTables(): array
    {
        return $this->tables_extracted ?? [];
    }

    // =========================================================================
    // LANGUAGE METHODS
    // =========================================================================

    /**
     * Get the primary detected language name.
     */
    public function getLanguageName(): ?string
    {
        if (!$this->language_detected) {
            return null;
        }

        $languages = config('openrouter.supported_languages', []);

        return $languages[$this->language_detected] ?? $this->language_detected;
    }

    /**
     * Check if multiple languages were detected.
     */
    public function isMultilingual(): bool
    {
        return count($this->languages_present ?? []) > 1;
    }

    /**
     * Get all detected languages with their confidence scores.
     */
    public function getLanguagesWithConfidence(): array
    {
        return $this->languages_present ?? [];
    }

    // =========================================================================
    // FILE METHODS
    // =========================================================================

    /**
     * Get the URL for the stored document.
     */
    public function getFileUrl(): ?string
    {
        if (!$this->stored_path) {
            return null;
        }

        return Storage::disk('public')->url($this->stored_path);
    }

    /**
     * Get the file size in a human-readable format.
     */
    public function getFormattedFileSize(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    /**
     * Get the file extension from the original filename.
     */
    public function getFileExtension(): string
    {
        return strtolower(pathinfo($this->original_filename, PATHINFO_EXTENSION));
    }

    /**
     * Check if the document is a PDF.
     */
    public function isPdf(): bool
    {
        return $this->file_type === self::TYPE_PDF;
    }

    /**
     * Check if the document is an image.
     */
    public function isImage(): bool
    {
        return in_array($this->file_type, [self::TYPE_IMAGE, self::TYPE_SCAN, self::TYPE_SCREENSHOT]);
    }

    // =========================================================================
    // TRANSLATION METHODS
    // =========================================================================

    /**
     * Get translation for a specific language.
     */
    public function getTranslation(string $targetLanguage): ?DocumentTranslation
    {
        return $this->translations()
            ->where('target_language', $targetLanguage)
            ->latest()
            ->first();
    }

    /**
     * Check if a translation exists for a language.
     */
    public function hasTranslation(string $targetLanguage): bool
    {
        return $this->translations()
            ->where('target_language', $targetLanguage)
            ->exists();
    }

    /**
     * Get all available translation languages.
     *
     * @return array<string>
     */
    public function getTranslatedLanguages(): array
    {
        return $this->translations()
            ->pluck('target_language')
            ->unique()
            ->values()
            ->toArray();
    }

    // =========================================================================
    // SUMMARY METHODS
    // =========================================================================

    /**
     * Get a comprehensive summary of the analysis.
     */
    public function getSummary(): array
    {
        return [
            'status' => $this->analysis_status,
            'file' => [
                'name' => $this->original_filename,
                'type' => $this->file_type,
                'size' => $this->getFormattedFileSize(),
                'pages' => $this->page_count,
            ],
            'text' => [
                'word_count' => $this->getWordCount(),
                'char_count' => $this->getCharacterCount(),
                'confidence' => $this->text_confidence,
                'confidence_level' => $this->getConfidenceLevel(),
            ],
            'document_type' => $this->document_type_detected,
            'language' => [
                'primary' => $this->language_detected,
                'is_multilingual' => $this->isMultilingual(),
            ],
            'entities' => [
                'total' => $this->getEntityCount(),
                'has_entities' => $this->hasEntities(),
            ],
            'tables' => [
                'count' => $this->getTableCount(),
                'has_tables' => $this->hasTables(),
            ],
            'handwriting_detected' => $this->handwriting_detected,
            'processing_time_ms' => $this->processing_time_ms,
            'translations_available' => $this->getTranslatedLanguages(),
        ];
    }

    /**
     * Get all available document types.
     *
     * @return array<string, string>
     */
    public static function getDocumentTypes(): array
    {
        return [
            self::DOC_INVOICE => 'Invoice',
            self::DOC_LETTER => 'Letter',
            self::DOC_REPORT => 'Report',
            self::DOC_ID_DOCUMENT => 'ID Document',
            self::DOC_CONTRACT => 'Contract',
            self::DOC_RECEIPT => 'Receipt',
            self::DOC_FORM => 'Form',
            self::DOC_CERTIFICATE => 'Certificate',
            self::DOC_NEWS_ARTICLE => 'News Article',
            self::DOC_SCREENSHOT => 'Screenshot',
            self::DOC_HANDWRITTEN_NOTE => 'Handwritten Note',
            self::DOC_UNKNOWN => 'Unknown',
        ];
    }

    /**
     * Get all available file types.
     *
     * @return array<string, string>
     */
    public static function getFileTypes(): array
    {
        return [
            self::TYPE_IMAGE => 'Image',
            self::TYPE_PDF => 'PDF',
            self::TYPE_SCAN => 'Scanned Document',
            self::TYPE_SCREENSHOT => 'Screenshot',
        ];
    }
}
