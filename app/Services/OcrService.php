<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentAnalysis;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service for OCR (Optical Character Recognition) and document analysis.
 *
 * Handles text extraction from images and PDFs using Tesseract CLI
 * with AI-powered fallback via OpenRouter for complex documents.
 */
class OcrService
{
    protected OpenRouterService $openRouter;
    protected array $config;

    public function __construct(OpenRouterService $openRouter)
    {
        $this->openRouter = $openRouter;
        $this->config = config('openrouter');
    }

    // =========================================================================
    // MAIN ANALYSIS METHODS
    // =========================================================================

    /**
     * Create a new document analysis record.
     *
     * @param string $filePath Path to uploaded file (relative to storage)
     * @param string $originalFilename Original filename
     * @param User $user User who uploaded the document
     * @param int|null $eventId Optional event ID to link
     * @return DocumentAnalysis
     */
    public function createAnalysis(
        string $filePath,
        string $originalFilename,
        User $user,
        ?int $eventId = null
    ): DocumentAnalysis {
        $fullPath = Storage::disk('public')->path($filePath);
        $fileSize = filesize($fullPath);
        $fileType = $this->determineFileType($fullPath, $originalFilename);

        $analysis = DocumentAnalysis::create([
            'uuid' => Str::uuid()->toString(),
            'original_filename' => $originalFilename,
            'stored_path' => $filePath,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'page_count' => $fileType === 'pdf' ? $this->getPdfPageCount($fullPath) : 1,
            'analysis_status' => DocumentAnalysis::STATUS_PENDING,
            'event_id' => $eventId,
            'created_by' => $user->id,
        ]);

        return $analysis;
    }

    /**
     * Perform full analysis on a document.
     *
     * @param DocumentAnalysis $analysis The analysis record
     * @return DocumentAnalysis Updated analysis
     */
    public function analyzeDocument(DocumentAnalysis $analysis): DocumentAnalysis
    {
        $analysis->markAsProcessing();
        $startTime = microtime(true);

        try {
            $fullPath = Storage::disk('public')->path($analysis->stored_path);

            if (!file_exists($fullPath)) {
                throw new \Exception("File not found: {$fullPath}");
            }

            // Step 1: Extract text
            $textResult = $this->extractText($fullPath, $analysis->isPdf());
            $analysis->extracted_text = $textResult['text'];
            $analysis->text_confidence = $textResult['confidence'];
            $analysis->text_blocks = $textResult['blocks'] ?? null;
            $analysis->handwriting_detected = $textResult['handwriting_detected'] ?? false;

            // Use AI if confidence is low or handwriting detected
            if ($this->shouldUseAiFallback($textResult)) {
                Log::info('Using AI fallback for OCR', ['analysis_id' => $analysis->id]);
                $aiResult = $this->extractTextWithAI($fullPath, $analysis->handwriting_detected);
                if ($aiResult) {
                    $analysis->extracted_text = $aiResult;
                    $analysis->text_confidence = max($analysis->text_confidence ?? 0, 70);
                }
            }

            $analysis->save();

            // Step 2: Detect language if text was extracted
            if (!empty($analysis->extracted_text)) {
                $langResult = $this->detectLanguage($analysis->extracted_text);
                if ($langResult) {
                    $analysis->language_detected = $langResult['primary'];
                    $analysis->languages_present = $langResult['all'];
                }
                $analysis->save();

                // Step 3: Extract entities
                $entities = $this->extractEntities($analysis->extracted_text);
                if ($entities) {
                    $analysis->detected_entities = $entities;
                }
                $analysis->save();

                // Step 4: Classify document
                $docType = $this->classifyDocument($analysis->extracted_text);
                if ($docType) {
                    $analysis->document_type_detected = $docType;
                }
                $analysis->save();
            }

            // Step 5: Extract tables (if present)
            $tables = $this->extractTables($fullPath, $analysis->extracted_text);
            if (!empty($tables)) {
                $analysis->tables_extracted = $tables;
            }

            // Calculate processing time and mark as completed
            $processingTime = (int) ((microtime(true) - $startTime) * 1000);
            $analysis->markAsCompleted($processingTime);

            Log::info('Document analysis completed', [
                'analysis_id' => $analysis->id,
                'word_count' => $analysis->getWordCount(),
                'confidence' => $analysis->text_confidence,
                'processing_time_ms' => $processingTime,
            ]);

            return $analysis->fresh();

        } catch (\Exception $e) {
            Log::error('Document analysis failed', [
                'analysis_id' => $analysis->id,
                'error' => $e->getMessage(),
            ]);

            $analysis->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    // =========================================================================
    // TEXT EXTRACTION METHODS
    // =========================================================================

    /**
     * Extract text from an image or PDF using Tesseract.
     *
     * @param string $filePath Full path to the file
     * @param bool $isPdf Whether the file is a PDF
     * @return array{text: string|null, confidence: int|null, blocks: array|null, handwriting_detected: bool}
     */
    public function extractText(string $filePath, bool $isPdf = false): array
    {
        $result = [
            'text' => null,
            'confidence' => null,
            'blocks' => null,
            'handwriting_detected' => false,
        ];

        if ($isPdf) {
            return $this->extractTextFromPdf($filePath);
        }

        // Check if Tesseract is available
        if (!$this->isTesseractAvailable()) {
            Log::warning('Tesseract not available, using AI fallback only');
            return $result;
        }

        // Preprocess image if enabled
        $processedPath = $filePath;
        if ($this->config['ocr']['preprocess']['enabled'] ?? true) {
            $processedPath = $this->preprocessImage($filePath);
        }

        try {
            // Run Tesseract with confidence data
            $tesseractPath = $this->config['ocr']['tesseract_path'] ?? 'tesseract';
            $languages = $this->config['ocr']['tesseract_languages'] ?? 'eng';

            // Get text with confidence data
            $tsvOutput = $this->runTesseract($processedPath, $languages, 'tsv');
            $textOutput = $this->runTesseract($processedPath, $languages, 'text');

            $result['text'] = trim($textOutput);
            $result['confidence'] = $this->calculateConfidence($tsvOutput);
            $result['blocks'] = $this->parseTextBlocks($tsvOutput);
            $result['handwriting_detected'] = $this->detectHandwriting($tsvOutput, $result['confidence']);

        } catch (\Exception $e) {
            Log::error('Tesseract OCR failed', [
                'path' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }

        // Clean up preprocessed file
        if ($processedPath !== $filePath && file_exists($processedPath)) {
            @unlink($processedPath);
        }

        return $result;
    }

    /**
     * Extract text using AI vision model.
     *
     * @param string $imagePath Path to the image
     * @param bool $isHandwritten Whether the document contains handwriting
     * @return string|null Extracted text
     */
    public function extractTextWithAI(string $imagePath, bool $isHandwritten = false): ?string
    {
        if (!$this->openRouter->isConfigured()) {
            Log::warning('OpenRouter not configured for AI OCR');
            return null;
        }

        return $this->openRouter->extractTextFromImage($imagePath, $isHandwritten);
    }

    /**
     * Extract text from a multi-page PDF.
     *
     * @param string $pdfPath Path to PDF file
     * @return array Text extraction result
     */
    public function extractTextFromPdf(string $pdfPath): array
    {
        $result = [
            'text' => null,
            'confidence' => null,
            'blocks' => null,
            'handwriting_detected' => false,
        ];

        // First try pdftotext for text-based PDFs
        $textFromPdf = $this->extractTextUsingPdftotext($pdfPath);
        if (!empty(trim($textFromPdf))) {
            $result['text'] = $textFromPdf;
            $result['confidence'] = 95; // High confidence for native PDF text
            return $result;
        }

        // PDF appears to be scanned, convert to images and OCR
        $pageImages = $this->convertPdfToImages($pdfPath);
        if (empty($pageImages)) {
            Log::error('Failed to convert PDF to images', ['path' => $pdfPath]);
            return $result;
        }

        $allText = [];
        $confidences = [];
        $allBlocks = [];

        foreach ($pageImages as $pageNumber => $imagePath) {
            $pageResult = $this->extractText($imagePath, false);
            if (!empty($pageResult['text'])) {
                $allText[] = "--- Page {$pageNumber} ---\n" . $pageResult['text'];
                $confidences[] = $pageResult['confidence'] ?? 0;
                if (!empty($pageResult['blocks'])) {
                    $allBlocks = array_merge($allBlocks, $pageResult['blocks']);
                }
                if ($pageResult['handwriting_detected']) {
                    $result['handwriting_detected'] = true;
                }
            }

            // Clean up page image
            @unlink($imagePath);
        }

        $result['text'] = implode("\n\n", $allText);
        $result['confidence'] = !empty($confidences) ? (int) (array_sum($confidences) / count($confidences)) : null;
        $result['blocks'] = $allBlocks;

        return $result;
    }

    /**
     * Process a multi-page PDF and return analysis for each page.
     *
     * @param string $pdfPath Path to PDF file
     * @return array<int, array> Page-by-page results
     */
    public function processMultiPage(string $pdfPath): array
    {
        $pageImages = $this->convertPdfToImages($pdfPath);
        $results = [];

        foreach ($pageImages as $pageNumber => $imagePath) {
            $results[$pageNumber] = [
                'page' => $pageNumber,
                'text_result' => $this->extractText($imagePath, false),
            ];

            // Clean up
            @unlink($imagePath);
        }

        return $results;
    }

    // =========================================================================
    // LANGUAGE DETECTION
    // =========================================================================

    /**
     * Detect languages in extracted text.
     *
     * @param string $text Text to analyze
     * @return array|null {primary: string, all: array}
     */
    public function detectLanguage(string $text): ?array
    {
        // First try Tesseract's language detection (fast, local)
        $tesseractResult = $this->detectLanguageWithTesseract($text);
        if ($tesseractResult) {
            return $tesseractResult;
        }

        // Fall back to AI detection
        if ($this->openRouter->isConfigured()) {
            $aiResult = $this->openRouter->detectLanguage($text);
            if ($aiResult && is_array($aiResult)) {
                // Sort by confidence and get primary
                usort($aiResult, fn ($a, $b) => ($b['confidence'] ?? 0) <=> ($a['confidence'] ?? 0));
                return [
                    'primary' => $aiResult[0]['language'] ?? 'en',
                    'all' => $aiResult,
                ];
            }
        }

        return null;
    }

    // =========================================================================
    // ENTITY EXTRACTION
    // =========================================================================

    /**
     * Extract named entities from text.
     *
     * @param string $text Text to analyze
     * @return array|null {names: [], dates: [], locations: [], organizations: []}
     */
    public function extractEntities(string $text): ?array
    {
        if (!$this->openRouter->isConfigured()) {
            // Basic regex-based extraction as fallback
            return $this->extractEntitiesWithRegex($text);
        }

        return $this->openRouter->extractEntities($text);
    }

    /**
     * Basic entity extraction using regex patterns.
     */
    protected function extractEntitiesWithRegex(string $text): array
    {
        $entities = [
            'names' => [],
            'dates' => [],
            'locations' => [],
            'organizations' => [],
        ];

        // Extract dates (various formats)
        $datePatterns = [
            '/\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4}/', // DD/MM/YYYY, MM-DD-YYYY, etc.
            '/\d{4}[\/\-\.]\d{1,2}[\/\-\.]\d{1,2}/', // YYYY-MM-DD
            '/(?:January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{1,2},?\s+\d{4}/i',
            '/\d{1,2}\s+(?:January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{4}/i',
        ];

        foreach ($datePatterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                $entities['dates'] = array_merge($entities['dates'], $matches[0]);
            }
        }

        $entities['dates'] = array_unique($entities['dates']);

        return $entities;
    }

    // =========================================================================
    // TABLE EXTRACTION
    // =========================================================================

    /**
     * Extract tables from document.
     *
     * @param string $filePath Path to file
     * @param string|null $extractedText Already extracted text
     * @return array|null Extracted tables
     */
    public function extractTables(string $filePath, ?string $extractedText = null): ?array
    {
        // Check if text contains table-like patterns
        if ($extractedText && !$this->mightContainTables($extractedText)) {
            return null;
        }

        // Try AI-based extraction
        if ($this->openRouter->isConfigured()) {
            $isImage = !str_ends_with(strtolower($filePath), '.pdf');
            return $this->openRouter->extractTables($isImage ? $filePath : ($extractedText ?? ''), $isImage);
        }

        // Basic markdown table parsing from text
        return $this->parseMarkdownTables($extractedText ?? '');
    }

    // =========================================================================
    // DOCUMENT CLASSIFICATION
    // =========================================================================

    /**
     * Classify the document type based on content.
     *
     * @param string $text Document text
     * @return string|null Document type
     */
    public function classifyDocument(string $text): ?string
    {
        if ($this->openRouter->isConfigured()) {
            return $this->openRouter->classifyDocument($text);
        }

        // Basic keyword-based classification
        return $this->classifyWithKeywords($text);
    }

    /**
     * Basic document classification using keywords.
     */
    protected function classifyWithKeywords(string $text): string
    {
        $text = strtolower($text);

        $patterns = [
            'invoice' => ['invoice', 'amount due', 'total:', 'subtotal', 'bill to', 'payment due'],
            'receipt' => ['receipt', 'total paid', 'thank you for your purchase', 'cash', 'card payment'],
            'contract' => ['agreement', 'hereby agree', 'terms and conditions', 'party', 'whereas', 'signatures'],
            'letter' => ['dear', 'sincerely', 'regards', 'yours truly', 'to whom it may concern'],
            'report' => ['report', 'summary', 'findings', 'conclusion', 'recommendations', 'analysis'],
            'id_document' => ['passport', 'driver license', 'date of birth', 'identification', 'id number'],
            'certificate' => ['certificate', 'certify', 'awarded', 'completed', 'achievement'],
            'form' => ['form', 'please fill', 'checkbox', 'date:', 'signature:'],
        ];

        $scores = [];
        foreach ($patterns as $type => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                $score += substr_count($text, $keyword);
            }
            $scores[$type] = $score;
        }

        arsort($scores);
        $topType = array_key_first($scores);

        return $scores[$topType] > 0 ? $topType : 'unknown';
    }

    // =========================================================================
    // PRIVATE HELPER METHODS
    // =========================================================================

    /**
     * Check if Tesseract is available.
     */
    protected function isTesseractAvailable(): bool
    {
        if (!($this->config['ocr']['use_tesseract'] ?? true)) {
            return false;
        }

        $tesseractPath = $this->config['ocr']['tesseract_path'] ?? 'tesseract';
        $result = Process::run([$tesseractPath, '--version']);

        return $result->successful();
    }

    /**
     * Run Tesseract OCR.
     */
    protected function runTesseract(string $imagePath, string $languages, string $outputType = 'text'): string
    {
        $tesseractPath = $this->config['ocr']['tesseract_path'] ?? 'tesseract';
        $outputFile = sys_get_temp_dir() . '/' . Str::uuid();

        $args = [
            $tesseractPath,
            $imagePath,
            $outputFile,
            '-l', $languages,
        ];

        if ($outputType === 'tsv') {
            $args[] = 'tsv';
        }

        $result = Process::run($args);

        if (!$result->successful()) {
            throw new \Exception('Tesseract failed: ' . $result->errorOutput());
        }

        $extension = $outputType === 'tsv' ? '.tsv' : '.txt';
        $output = file_get_contents($outputFile . $extension) ?: '';

        @unlink($outputFile . $extension);

        return $output;
    }

    /**
     * Calculate overall confidence from Tesseract TSV output.
     */
    protected function calculateConfidence(string $tsvOutput): int
    {
        $lines = explode("\n", $tsvOutput);
        $confidences = [];

        foreach ($lines as $line) {
            $parts = explode("\t", $line);
            if (count($parts) >= 12 && is_numeric($parts[10]) && !empty(trim($parts[11] ?? ''))) {
                $conf = (int) $parts[10];
                if ($conf > 0) {
                    $confidences[] = $conf;
                }
            }
        }

        if (empty($confidences)) {
            return 0;
        }

        return (int) (array_sum($confidences) / count($confidences));
    }

    /**
     * Parse text blocks from Tesseract TSV output.
     */
    protected function parseTextBlocks(string $tsvOutput): array
    {
        $lines = explode("\n", $tsvOutput);
        $blocks = [];

        foreach ($lines as $line) {
            $parts = explode("\t", $line);
            if (count($parts) >= 12 && !empty(trim($parts[11] ?? ''))) {
                $blocks[] = [
                    'x' => (int) ($parts[6] ?? 0),
                    'y' => (int) ($parts[7] ?? 0),
                    'width' => (int) ($parts[8] ?? 0),
                    'height' => (int) ($parts[9] ?? 0),
                    'text' => $parts[11] ?? '',
                    'confidence' => (int) ($parts[10] ?? 0),
                ];
            }
        }

        return $blocks;
    }

    /**
     * Detect if document contains handwriting.
     */
    protected function detectHandwriting(string $tsvOutput, ?int $confidence): bool
    {
        // Low confidence combined with irregular character sizes might indicate handwriting
        if ($confidence !== null && $confidence < 50) {
            return true;
        }

        // Check for high variance in character confidence
        $lines = explode("\n", $tsvOutput);
        $confidences = [];

        foreach ($lines as $line) {
            $parts = explode("\t", $line);
            if (count($parts) >= 12 && is_numeric($parts[10])) {
                $confidences[] = (int) $parts[10];
            }
        }

        if (count($confidences) >= 10) {
            $mean = array_sum($confidences) / count($confidences);
            $variance = array_sum(array_map(fn ($c) => pow($c - $mean, 2), $confidences)) / count($confidences);

            // High variance suggests inconsistent recognition (handwriting)
            if ($variance > 500) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if AI fallback should be used.
     */
    protected function shouldUseAiFallback(array $textResult): bool
    {
        if (!($this->config['ocr']['ai_fallback'] ?? true)) {
            return false;
        }

        $threshold = $this->config['ocr']['ai_fallback_threshold'] ?? 60;

        // Use AI if confidence is below threshold or handwriting detected
        return ($textResult['confidence'] ?? 0) < $threshold
            || ($textResult['handwriting_detected'] ?? false);
    }

    /**
     * Preprocess image for better OCR results.
     */
    protected function preprocessImage(string $imagePath): string
    {
        // Check if ImageMagick is available
        $result = Process::run(['convert', '-version']);
        if (!$result->successful()) {
            return $imagePath;
        }

        $outputPath = sys_get_temp_dir() . '/' . Str::uuid() . '.png';
        $operations = [];

        if ($this->config['ocr']['preprocess']['enhance_contrast'] ?? true) {
            $operations[] = '-contrast-stretch';
            $operations[] = '0.1%x0.1%';
        }

        if ($this->config['ocr']['preprocess']['denoise'] ?? true) {
            $operations[] = '-despeckle';
        }

        if ($this->config['ocr']['preprocess']['deskew'] ?? true) {
            $operations[] = '-deskew';
            $operations[] = '40%';
        }

        // Convert to grayscale for better OCR
        array_unshift($operations, '-colorspace', 'Gray');

        $args = array_merge(['convert', $imagePath], $operations, [$outputPath]);
        $result = Process::run($args);

        if (!$result->successful()) {
            Log::warning('Image preprocessing failed', ['error' => $result->errorOutput()]);
            return $imagePath;
        }

        return $outputPath;
    }

    /**
     * Extract text from PDF using pdftotext.
     */
    protected function extractTextUsingPdftotext(string $pdfPath): string
    {
        $result = Process::run(['pdftotext', '-layout', $pdfPath, '-']);

        if (!$result->successful()) {
            return '';
        }

        return $result->output();
    }

    /**
     * Convert PDF pages to images.
     *
     * @return array<int, string> Page number => image path
     */
    protected function convertPdfToImages(string $pdfPath): array
    {
        $maxPages = $this->config['documents']['max_pages'] ?? 50;
        $outputDir = sys_get_temp_dir() . '/pdf_' . Str::uuid();

        if (!mkdir($outputDir, 0755, true)) {
            return [];
        }

        // Use pdftoppm for conversion (poppler-utils)
        $result = Process::run([
            'pdftoppm',
            '-png',
            '-r', '300', // DPI
            '-l', (string) $maxPages, // Last page
            $pdfPath,
            $outputDir . '/page',
        ]);

        if (!$result->successful()) {
            // Try ImageMagick as fallback
            $result = Process::run([
                'convert',
                '-density', '300',
                "{$pdfPath}[0-" . ($maxPages - 1) . "]",
                $outputDir . '/page-%d.png',
            ]);
        }

        $images = [];
        $files = glob($outputDir . '/page*.png');

        foreach ($files as $index => $file) {
            $images[$index + 1] = $file;
        }

        return $images;
    }

    /**
     * Get page count from PDF.
     */
    protected function getPdfPageCount(string $pdfPath): ?int
    {
        $result = Process::run(['pdfinfo', $pdfPath]);

        if ($result->successful()) {
            if (preg_match('/Pages:\s*(\d+)/', $result->output(), $matches)) {
                return (int) $matches[1];
            }
        }

        return null;
    }

    /**
     * Determine file type from path and filename.
     */
    protected function determineFileType(string $filePath, string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeType = mime_content_type($filePath) ?: '';

        if ($extension === 'pdf' || $mimeType === 'application/pdf') {
            return DocumentAnalysis::TYPE_PDF;
        }

        // Check if it's a screenshot based on filename patterns
        if (preg_match('/screenshot|screen\s*shot|capture/i', $filename)) {
            return DocumentAnalysis::TYPE_SCREENSHOT;
        }

        return DocumentAnalysis::TYPE_IMAGE;
    }

    /**
     * Check if text might contain tables.
     */
    protected function mightContainTables(string $text): bool
    {
        // Check for table-like patterns
        $tablePatterns = [
            '/\|.*\|.*\|/', // Markdown table
            '/\t.*\t/', // Tab-separated values
            '/^\s*[\w\s]+\s{2,}[\w\s]+\s{2,}/m', // Multiple whitespace columns
        ];

        foreach ($tablePatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse markdown tables from text.
     */
    protected function parseMarkdownTables(string $text): array
    {
        $tables = [];

        // Match markdown table pattern
        if (preg_match_all('/^\|(.+)\|$(\n\|[-:| ]+\|$)?((\n\|(.+)\|$)+)/m', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $headerLine = $match[1] ?? '';
                $headers = array_map('trim', explode('|', $headerLine));

                $rows = [];
                if (isset($match[3])) {
                    $rowLines = explode("\n", trim($match[3]));
                    foreach ($rowLines as $rowLine) {
                        $rowLine = trim($rowLine, '|');
                        $rows[] = array_map('trim', explode('|', $rowLine));
                    }
                }

                $tables[] = [
                    'headers' => array_values(array_filter($headers)),
                    'rows' => $rows,
                ];
            }
        }

        return $tables;
    }

    /**
     * Detect language using Tesseract OSD.
     */
    protected function detectLanguageWithTesseract(string $text): ?array
    {
        // This is a simplified approach - write text to temp file and use tesseract osd
        // In practice, this might not be as accurate as AI-based detection
        return null;
    }
}
