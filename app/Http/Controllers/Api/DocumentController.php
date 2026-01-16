<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessDocumentAnalysisJob;
use App\Models\DocumentAnalysis;
use App\Models\DocumentTranslation;
use App\Services\OcrService;
use App\Services\OpenRouterService;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

/**
 * Controller for document OCR analysis and translation API endpoints.
 *
 * Provides endpoints for uploading documents, extracting text via OCR,
 * detecting entities, classifying documents, and translating content.
 */
class DocumentController extends Controller
{
    public function __construct(
        protected OcrService $ocrService,
        protected TranslationService $translationService,
        protected OpenRouterService $openRouterService
    ) {}

    // =========================================================================
    // DOCUMENT UPLOAD & ANALYSIS
    // =========================================================================

    /**
     * List document analyses for the authenticated user.
     *
     * GET /api/documents
     *
     * Query Parameters:
     * - status: Filter by analysis status (pending, processing, completed, failed)
     * - file_type: Filter by file type (image, pdf, scan, screenshot)
     * - document_type: Filter by detected document type
     * - language: Filter by detected language
     * - has_tables: Filter to show only documents with tables
     * - has_handwriting: Filter to show only documents with handwriting
     * - per_page: Items per page (default: 15)
     */
    public function index(Request $request): JsonResponse
    {
        $query = DocumentAnalysis::with(['creator:id,name', 'event:id,uuid,title'])
            ->where('created_by', $request->user()->id);

        // Apply filters
        if ($request->has('status')) {
            $query->where('analysis_status', $request->status);
        }

        if ($request->has('file_type')) {
            $query->where('file_type', $request->file_type);
        }

        if ($request->has('document_type')) {
            $query->where('document_type_detected', $request->document_type);
        }

        if ($request->has('language')) {
            $query->where('language_detected', $request->language);
        }

        if ($request->boolean('has_tables')) {
            $query->withTables();
        }

        if ($request->boolean('has_handwriting')) {
            $query->withHandwriting();
        }

        if ($request->has('min_confidence')) {
            $query->minConfidence((int) $request->min_confidence);
        }

        $analyses = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $analyses->map(fn ($a) => $this->formatAnalysis($a)),
            'meta' => [
                'current_page' => $analyses->currentPage(),
                'last_page' => $analyses->lastPage(),
                'per_page' => $analyses->perPage(),
                'total' => $analyses->total(),
            ],
        ]);
    }

    /**
     * Upload a document for OCR analysis.
     *
     * POST /api/documents/upload
     *
     * Request Body (multipart/form-data):
     * - document: (required) The document file (image or PDF)
     * - event_id: (optional) ID of related event
     */
    public function upload(Request $request): JsonResponse
    {
        $config = config('openrouter.documents');

        $request->validate([
            'document' => [
                'required',
                'file',
                File::types($config['allowed_extensions'])
                    ->max($config['max_file_size'] / 1024), // Convert to KB
            ],
            'event_id' => 'nullable|integer|exists:events,id',
        ]);

        $file = $request->file('document');
        $originalFilename = $file->getClientOriginalName();

        // Generate unique filename
        $storagePath = $config['storage_path'] . '/' . date('Y/m');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Store the file
        $path = $file->storeAs($storagePath, $filename, $config['storage_disk']);

        // Create analysis record
        $analysis = $this->ocrService->createAnalysis(
            $path,
            $originalFilename,
            $request->user(),
            $request->event_id
        );

        // Dispatch background job
        ProcessDocumentAnalysisJob::dispatch($analysis);

        return response()->json([
            'message' => 'Document uploaded and queued for analysis.',
            'data' => $this->formatAnalysis($analysis),
        ], 202);
    }

    /**
     * Trigger or re-trigger analysis on a document.
     *
     * POST /api/documents/{uuid}/analyze
     */
    public function analyze(Request $request, string $uuid): JsonResponse
    {
        $analysis = DocumentAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        // Check if already processing
        if ($analysis->isProcessing()) {
            return response()->json([
                'message' => 'Analysis is already in progress.',
                'data' => $this->formatAnalysis($analysis),
            ], 409);
        }

        // Reset status and dispatch job
        $analysis->update([
            'analysis_status' => DocumentAnalysis::STATUS_PENDING,
            'error_message' => null,
        ]);

        ProcessDocumentAnalysisJob::dispatch($analysis);

        return response()->json([
            'message' => 'Document analysis has been queued.',
            'data' => $this->formatAnalysis($analysis->fresh()),
        ], 202);
    }

    /**
     * Get analysis results.
     *
     * GET /api/documents/{uuid}
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $analysis = DocumentAnalysis::where('uuid', $uuid)
            ->with(['creator:id,name', 'event:id,uuid,title', 'translations'])
            ->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        return response()->json([
            'data' => $this->formatAnalysis($analysis, true),
        ]);
    }

    /**
     * Get extracted text from a document.
     *
     * GET /api/documents/{uuid}/text
     */
    public function text(Request $request, string $uuid): JsonResponse
    {
        $analysis = DocumentAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        if (!$analysis->isCompleted()) {
            return response()->json([
                'message' => 'Analysis not yet completed.',
                'status' => $analysis->analysis_status,
            ], 202);
        }

        return response()->json([
            'data' => [
                'uuid' => $analysis->uuid,
                'text' => $analysis->extracted_text,
                'word_count' => $analysis->getWordCount(),
                'character_count' => $analysis->getCharacterCount(),
                'confidence' => $analysis->text_confidence,
                'confidence_level' => $analysis->getConfidenceLevel(),
                'language' => $analysis->language_detected,
                'handwriting_detected' => $analysis->handwriting_detected,
            ],
        ]);
    }

    /**
     * Get detected entities from a document.
     *
     * GET /api/documents/{uuid}/entities
     */
    public function entities(Request $request, string $uuid): JsonResponse
    {
        $analysis = DocumentAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        if (!$analysis->isCompleted()) {
            return response()->json([
                'message' => 'Analysis not yet completed.',
                'status' => $analysis->analysis_status,
            ], 202);
        }

        return response()->json([
            'data' => [
                'uuid' => $analysis->uuid,
                'has_entities' => $analysis->hasEntities(),
                'total_count' => $analysis->getEntityCount(),
                'entities' => [
                    'names' => $analysis->getNames(),
                    'dates' => $analysis->getDates(),
                    'locations' => $analysis->getLocations(),
                    'organizations' => $analysis->getOrganizations(),
                ],
            ],
        ]);
    }

    /**
     * Get extracted tables from a document.
     *
     * GET /api/documents/{uuid}/tables
     */
    public function tables(Request $request, string $uuid): JsonResponse
    {
        $analysis = DocumentAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        if (!$analysis->isCompleted()) {
            return response()->json([
                'message' => 'Analysis not yet completed.',
                'status' => $analysis->analysis_status,
            ], 202);
        }

        return response()->json([
            'data' => [
                'uuid' => $analysis->uuid,
                'has_tables' => $analysis->hasTables(),
                'table_count' => $analysis->getTableCount(),
                'tables' => $analysis->getTables(),
            ],
        ]);
    }

    // =========================================================================
    // TRANSLATION ENDPOINTS
    // =========================================================================

    /**
     * Translate a document to a target language.
     *
     * POST /api/documents/{uuid}/translate
     *
     * Request Body:
     * - target_language: (required) Target language code (ISO 639-1)
     */
    public function translate(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'target_language' => 'required|string|size:2',
        ]);

        $analysis = DocumentAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        if (!$analysis->isCompleted()) {
            return response()->json([
                'message' => 'Document analysis must be completed before translation.',
                'status' => $analysis->analysis_status,
            ], 400);
        }

        if (empty($analysis->extracted_text)) {
            return response()->json([
                'message' => 'No text available to translate.',
            ], 400);
        }

        $targetLanguage = strtolower($request->target_language);

        // Check if language is supported
        if (!$this->translationService->isLanguageSupported($targetLanguage)) {
            return response()->json([
                'message' => 'Target language not supported.',
                'supported_languages' => $this->translationService->getSupportedLanguages(),
            ], 400);
        }

        // Perform translation
        $translation = $this->translationService->translateDocument($analysis->id, $targetLanguage);

        if (!$translation) {
            return response()->json([
                'message' => 'Translation failed. Please try again.',
                'error' => $this->openRouterService->getLastError(),
            ], 500);
        }

        return response()->json([
            'message' => 'Document translated successfully.',
            'data' => $this->formatTranslation($translation),
        ]);
    }

    /**
     * List translations for a document.
     *
     * GET /api/documents/{uuid}/translations
     */
    public function translations(Request $request, string $uuid): JsonResponse
    {
        $analysis = DocumentAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $translations = $analysis->translations()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => [
                'uuid' => $analysis->uuid,
                'source_language' => $analysis->language_detected,
                'translations' => $translations->map(fn ($t) => $this->formatTranslation($t)),
            ],
        ]);
    }

    /**
     * Get a specific translation.
     *
     * GET /api/documents/{uuid}/translations/{language}
     */
    public function translation(Request $request, string $uuid, string $language): JsonResponse
    {
        $analysis = DocumentAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $translation = $analysis->getTranslation($language);

        if (!$translation) {
            return response()->json([
                'message' => 'Translation not found for this language.',
            ], 404);
        }

        return response()->json([
            'data' => $this->formatTranslation($translation, true),
        ]);
    }

    // =========================================================================
    // REFERENCE ENDPOINTS
    // =========================================================================

    /**
     * Get supported languages for translation.
     *
     * GET /api/documents/languages
     */
    public function languages(): JsonResponse
    {
        $languages = $this->translationService->getSupportedLanguages();
        $commonPairs = $this->translationService->getCommonLanguagePairs();

        return response()->json([
            'data' => [
                'languages' => $languages,
                'common_pairs' => $commonPairs,
            ],
        ]);
    }

    /**
     * Get available document types.
     *
     * GET /api/documents/types
     */
    public function documentTypes(): JsonResponse
    {
        return response()->json([
            'data' => [
                'document_types' => DocumentAnalysis::getDocumentTypes(),
                'file_types' => DocumentAnalysis::getFileTypes(),
            ],
        ]);
    }

    /**
     * Get statistics for user's document analyses.
     *
     * GET /api/documents/stats
     */
    public function stats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $stats = [
            'total_documents' => DocumentAnalysis::where('created_by', $userId)->count(),
            'completed' => DocumentAnalysis::where('created_by', $userId)->completed()->count(),
            'pending' => DocumentAnalysis::where('created_by', $userId)->pending()->count(),
            'processing' => DocumentAnalysis::where('created_by', $userId)->processing()->count(),
            'failed' => DocumentAnalysis::where('created_by', $userId)->failed()->count(),
            'with_handwriting' => DocumentAnalysis::where('created_by', $userId)->withHandwriting()->count(),
            'with_tables' => DocumentAnalysis::where('created_by', $userId)->withTables()->count(),
            'by_file_type' => [
                'image' => DocumentAnalysis::where('created_by', $userId)->fileType('image')->count(),
                'pdf' => DocumentAnalysis::where('created_by', $userId)->fileType('pdf')->count(),
                'scan' => DocumentAnalysis::where('created_by', $userId)->fileType('scan')->count(),
                'screenshot' => DocumentAnalysis::where('created_by', $userId)->fileType('screenshot')->count(),
            ],
            'average_confidence' => round(
                DocumentAnalysis::where('created_by', $userId)
                    ->whereNotNull('text_confidence')
                    ->avg('text_confidence') ?? 0,
                1
            ),
            'total_translations' => DocumentTranslation::whereHas('documentAnalysis', function ($q) use ($userId) {
                $q->where('created_by', $userId);
            })->count(),
        ];

        return response()->json(['data' => $stats]);
    }

    /**
     * Get OpenRouter service status and available models.
     *
     * GET /api/documents/ai-status
     */
    public function aiStatus(): JsonResponse
    {
        $isConfigured = $this->openRouterService->isConfigured();
        $connectionTest = null;

        if ($isConfigured) {
            $connectionTest = $this->openRouterService->testConnection();
        }

        return response()->json([
            'data' => [
                'configured' => $isConfigured,
                'connection_test' => $connectionTest,
                'free_models' => $this->openRouterService->getFreeModels(),
                'models' => [
                    'vision' => $this->openRouterService->getModelForTask('vision'),
                    'translation' => $this->openRouterService->getModelForTask('translation'),
                    'ner' => $this->openRouterService->getModelForTask('ner'),
                    'classification' => $this->openRouterService->getModelForTask('classification'),
                ],
            ],
        ]);
    }

    // =========================================================================
    // DOCUMENT MANAGEMENT
    // =========================================================================

    /**
     * Delete a document analysis.
     *
     * DELETE /api/documents/{uuid}
     */
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $analysis = DocumentAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        // Delete the stored file
        if ($analysis->stored_path) {
            Storage::disk('public')->delete($analysis->stored_path);
        }

        $analysis->delete();

        return response()->json([
            'message' => 'Document analysis deleted successfully.',
        ]);
    }

    /**
     * Retry a failed analysis.
     *
     * POST /api/documents/{uuid}/retry
     */
    public function retry(Request $request, string $uuid): JsonResponse
    {
        $analysis = DocumentAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        if (!$analysis->hasFailed()) {
            return response()->json([
                'message' => 'Only failed analyses can be retried.',
                'status' => $analysis->analysis_status,
            ], 400);
        }

        // Reset status and dispatch job
        $analysis->update([
            'analysis_status' => DocumentAnalysis::STATUS_PENDING,
            'error_message' => null,
        ]);

        ProcessDocumentAnalysisJob::dispatch($analysis);

        return response()->json([
            'message' => 'Analysis has been requeued.',
            'data' => $this->formatAnalysis($analysis->fresh()),
        ], 202);
    }

    /**
     * Download the original document.
     *
     * GET /api/documents/{uuid}/download
     */
    public function download(Request $request, string $uuid): mixed
    {
        $analysis = DocumentAnalysis::where('uuid', $uuid)->firstOrFail();

        // Check access
        if ($analysis->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        if (!Storage::disk('public')->exists($analysis->stored_path)) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        return Storage::disk('public')->download(
            $analysis->stored_path,
            $analysis->original_filename
        );
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Format an analysis for API response.
     */
    protected function formatAnalysis(DocumentAnalysis $analysis, bool $detailed = false): array
    {
        $data = [
            'uuid' => $analysis->uuid,
            'original_filename' => $analysis->original_filename,
            'file_type' => $analysis->file_type,
            'file_size' => $analysis->file_size,
            'file_size_formatted' => $analysis->getFormattedFileSize(),
            'page_count' => $analysis->page_count,
            'status' => $analysis->analysis_status,
            'document_type' => $analysis->document_type_detected,
            'language' => $analysis->language_detected,
            'language_name' => $analysis->getLanguageName(),
            'confidence' => $analysis->text_confidence,
            'confidence_level' => $analysis->getConfidenceLevel(),
            'word_count' => $analysis->getWordCount(),
            'handwriting_detected' => $analysis->handwriting_detected,
            'has_entities' => $analysis->hasEntities(),
            'has_tables' => $analysis->hasTables(),
            'processing_time_ms' => $analysis->processing_time_ms,
            'created_at' => $analysis->created_at,
            'updated_at' => $analysis->updated_at,
        ];

        // Add creator info if loaded
        if ($analysis->relationLoaded('creator') && $analysis->creator) {
            $data['creator'] = [
                'id' => $analysis->creator->id,
                'name' => $analysis->creator->name,
            ];
        }

        // Add event info if loaded
        if ($analysis->relationLoaded('event') && $analysis->event) {
            $data['event'] = [
                'uuid' => $analysis->event->uuid,
                'title' => $analysis->event->title,
            ];
        }

        if ($detailed) {
            // Add full text
            $data['extracted_text'] = $analysis->extracted_text;
            $data['text_blocks'] = $analysis->text_blocks;

            // Add entities
            $data['entities'] = [
                'names' => $analysis->getNames(),
                'dates' => $analysis->getDates(),
                'locations' => $analysis->getLocations(),
                'organizations' => $analysis->getOrganizations(),
            ];

            // Add tables
            $data['tables'] = $analysis->getTables();

            // Add languages
            $data['languages_present'] = $analysis->languages_present;
            $data['is_multilingual'] = $analysis->isMultilingual();

            // Add metadata
            $data['metadata'] = $analysis->metadata;

            // Add translations if loaded
            if ($analysis->relationLoaded('translations')) {
                $data['translations'] = $analysis->translations->map(fn ($t) => [
                    'target_language' => $t->target_language,
                    'target_language_name' => $t->getTargetLanguageName(),
                    'provider' => $t->translation_provider,
                    'created_at' => $t->created_at,
                ]);
            }

            // Add file URL
            $data['file_url'] = $analysis->getFileUrl();

            // Add error if failed
            if ($analysis->hasFailed()) {
                $data['error_message'] = $analysis->error_message;
            }
        }

        return $data;
    }

    /**
     * Format a translation for API response.
     */
    protected function formatTranslation(DocumentTranslation $translation, bool $includeText = false): array
    {
        $data = [
            'id' => $translation->id,
            'source_language' => $translation->source_language,
            'source_language_name' => $translation->getSourceLanguageName(),
            'target_language' => $translation->target_language,
            'target_language_name' => $translation->getTargetLanguageName(),
            'language_pair' => $translation->getLanguagePair(),
            'word_count' => $translation->getWordCount(),
            'confidence' => $translation->translation_confidence,
            'confidence_level' => $translation->getConfidenceLevel(),
            'provider' => $translation->translation_provider,
            'model' => $translation->model_used,
            'processing_time' => $translation->getFormattedProcessingTime(),
            'token_count' => $translation->token_count,
            'created_at' => $translation->created_at,
        ];

        if ($includeText) {
            $data['translated_text'] = $translation->translated_text;
        }

        return $data;
    }
}
