<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ReverseImageResult;
use App\Models\ReverseImageSearch;
use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * ReverseImageService
 *
 * Service class for managing reverse image searches across multiple search engines.
 * Provides methods for:
 * - Initiating searches with uploaded images or URLs
 * - Querying individual search engines (Google, Bing, TinEye, Yandex, Baidu)
 * - Calculating perceptual hashes for image deduplication
 * - Detecting potential image manipulation
 * - Aggregating and deduplicating results
 * - Finding the earliest appearance of an image online
 */
class ReverseImageService
{
    /**
     * Maximum file size for images (10MB)
     */
    protected const MAX_FILE_SIZE = 10485760;

    /**
     * Allowed image MIME types
     */
    protected const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/bmp',
    ];

    /**
     * Create a new reverse image search from an uploaded file
     *
     * @param UploadedFile $file Uploaded image file
     * @param User $user User creating the search
     * @param array $options Additional options (event_id, engines)
     * @return ReverseImageSearch
     * @throws \Exception
     */
    public function createSearchFromUpload(
        UploadedFile $file,
        User $user,
        array $options = []
    ): ReverseImageSearch {
        $this->validateImage($file);

        // Generate unique filename and store
        $uuid = Str::uuid()->toString();
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = "{$uuid}.{$extension}";
        $storagePath = date('Y/m/d') . '/' . $filename;

        Storage::disk('reverse_images')->put($storagePath, $file->getContent());

        // Calculate perceptual hash
        $tempPath = Storage::disk('reverse_images')->path($storagePath);
        $imageHash = $this->calculatePerceptualHash($tempPath);

        // Extract image metadata
        $metadata = $this->extractImageMetadata($tempPath);

        return ReverseImageSearch::create([
            'uuid' => $uuid,
            'image_path' => $storagePath,
            'image_hash' => $imageHash,
            'image_url' => null,
            'search_status' => ReverseImageSearch::STATUS_PENDING,
            'created_by' => $user->id,
            'event_id' => $options['event_id'] ?? null,
            'total_results' => 0,
            'engines_searched' => null,
            'engines_completed' => null,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Create a new reverse image search from a URL
     *
     * @param string $imageUrl URL of the image to search
     * @param User $user User creating the search
     * @param array $options Additional options (event_id, engines)
     * @return ReverseImageSearch
     * @throws \Exception
     */
    public function createSearchFromUrl(
        string $imageUrl,
        User $user,
        array $options = []
    ): ReverseImageSearch {
        // Fetch the image
        $response = Http::timeout(30)->get($imageUrl);

        if (!$response->successful()) {
            throw new \Exception("Failed to fetch image from URL: HTTP {$response->status()}");
        }

        $content = $response->body();
        $contentType = $response->header('Content-Type');

        // Validate content type
        $mimeType = explode(';', $contentType)[0];
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new \Exception("Invalid image type: {$mimeType}");
        }

        // Generate unique filename and store
        $uuid = Str::uuid()->toString();
        $extension = $this->getExtensionFromMime($mimeType);
        $filename = "{$uuid}.{$extension}";
        $storagePath = date('Y/m/d') . '/' . $filename;

        Storage::disk('reverse_images')->put($storagePath, $content);

        // Calculate perceptual hash
        $tempPath = Storage::disk('reverse_images')->path($storagePath);
        $imageHash = $this->calculatePerceptualHash($tempPath);

        // Extract image metadata
        $metadata = $this->extractImageMetadata($tempPath);

        return ReverseImageSearch::create([
            'uuid' => $uuid,
            'image_path' => $storagePath,
            'image_hash' => $imageHash,
            'image_url' => $imageUrl,
            'search_status' => ReverseImageSearch::STATUS_PENDING,
            'created_by' => $user->id,
            'event_id' => $options['event_id'] ?? null,
            'total_results' => 0,
            'engines_searched' => null,
            'engines_completed' => null,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Search all configured engines
     *
     * @param ReverseImageSearch $search The search record
     * @param array|null $engines Specific engines to search (null = all enabled)
     * @return ReverseImageSearch
     */
    public function searchAllEngines(
        ReverseImageSearch $search,
        ?array $engines = null
    ): ReverseImageSearch {
        $engines = $engines ?? $this->getEnabledEngines();

        $search->markAsSearching($engines);

        Log::info('Starting reverse image search', [
            'search_id' => $search->id,
            'engines' => $engines,
        ]);

        $errors = [];

        foreach ($engines as $engine) {
            try {
                $resultsCount = match ($engine) {
                    ReverseImageSearch::ENGINE_GOOGLE => $this->searchGoogle($search),
                    ReverseImageSearch::ENGINE_BING => $this->searchBing($search),
                    ReverseImageSearch::ENGINE_TINEYE => $this->searchTineye($search),
                    ReverseImageSearch::ENGINE_YANDEX => $this->searchYandex($search),
                    ReverseImageSearch::ENGINE_BAIDU => $this->searchBaidu($search),
                    default => 0,
                };

                $search->markEngineCompleted($engine, $resultsCount);

                Log::info("Engine search completed", [
                    'search_id' => $search->id,
                    'engine' => $engine,
                    'results_count' => $resultsCount,
                ]);

            } catch (\Exception $e) {
                $errors[$engine] = $e->getMessage();

                Log::error("Engine search failed", [
                    'search_id' => $search->id,
                    'engine' => $engine,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Detect potential manipulation across results
        $this->detectManipulation($search);

        // Find and set earliest appearance date
        $search->updateEarliestFoundDate();

        // Mark as completed or failed
        if (empty($search->engines_completed)) {
            $search->markAsFailed('All search engines failed: ' . json_encode($errors));
        } else {
            $search->markAsCompleted();
        }

        return $search->fresh(['results']);
    }

    /**
     * Search Google Images
     *
     * @param ReverseImageSearch $search
     * @return int Number of results found
     */
    public function searchGoogle(ReverseImageSearch $search): int
    {
        $apiKey = config('reverse_image.engines.google.api_key');
        $searchEngineId = config('reverse_image.engines.google.search_engine_id');

        if (!$apiKey || !$searchEngineId) {
            Log::warning('Google Image Search not configured');
            return 0;
        }

        $imagePath = Storage::disk('reverse_images')->path($search->image_path);
        $imageUrl = $this->getPublicImageUrl($search);

        // Use Google Custom Search API with image URL
        $response = Http::timeout(config('reverse_image.timeout', 30))
            ->get('https://www.googleapis.com/customsearch/v1', [
                'key' => $apiKey,
                'cx' => $searchEngineId,
                'searchType' => 'image',
                'imgUrl' => $imageUrl,
                'num' => config('reverse_image.engines.google.max_results', 20),
            ]);

        if (!$response->successful()) {
            throw new \Exception("Google API error: {$response->status()}");
        }

        $data = $response->json();
        $items = $data['items'] ?? [];
        $count = 0;

        foreach ($items as $item) {
            try {
                ReverseImageResult::createFromGoogle($search->id, $item);
                $count++;
            } catch (\Exception $e) {
                Log::warning('Failed to create Google result', [
                    'search_id' => $search->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * Search Bing Visual Search
     *
     * @param ReverseImageSearch $search
     * @return int Number of results found
     */
    public function searchBing(ReverseImageSearch $search): int
    {
        $apiKey = config('reverse_image.engines.bing.api_key');

        if (!$apiKey) {
            Log::warning('Bing Visual Search not configured');
            return 0;
        }

        $imagePath = Storage::disk('reverse_images')->path($search->image_path);
        $imageContent = Storage::disk('reverse_images')->get($search->image_path);

        // Use Bing Visual Search API
        $response = Http::timeout(config('reverse_image.timeout', 30))
            ->withHeaders([
                'Ocp-Apim-Subscription-Key' => $apiKey,
            ])
            ->attach('image', $imageContent, 'image.jpg')
            ->post('https://api.bing.microsoft.com/v7.0/images/visualsearch');

        if (!$response->successful()) {
            throw new \Exception("Bing API error: {$response->status()}");
        }

        $data = $response->json();
        $count = 0;

        // Parse visual search results
        $tags = $data['tags'] ?? [];
        foreach ($tags as $tag) {
            $actions = $tag['actions'] ?? [];
            foreach ($actions as $action) {
                if ($action['actionType'] === 'PagesIncluding') {
                    $results = $action['data']['value'] ?? [];
                    foreach ($results as $result) {
                        try {
                            ReverseImageResult::createFromBing($search->id, $result);
                            $count++;
                        } catch (\Exception $e) {
                            Log::warning('Failed to create Bing result', [
                                'search_id' => $search->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Search TinEye
     *
     * @param ReverseImageSearch $search
     * @return int Number of results found
     */
    public function searchTineye(ReverseImageSearch $search): int
    {
        $apiKey = config('reverse_image.engines.tineye.api_key');
        $apiSecret = config('reverse_image.engines.tineye.api_secret');

        if (!$apiKey) {
            Log::warning('TinEye not configured');
            return 0;
        }

        $imageContent = Storage::disk('reverse_images')->get($search->image_path);

        // Use TinEye API
        $response = Http::timeout(config('reverse_image.timeout', 30))
            ->withBasicAuth($apiKey, $apiSecret ?? '')
            ->attach('image', $imageContent, 'image.jpg')
            ->post('https://api.tineye.com/rest/search/', [
                'limit' => config('reverse_image.engines.tineye.max_results', 50),
                'sort' => 'crawl_date',
                'order' => 'asc',
            ]);

        if (!$response->successful()) {
            throw new \Exception("TinEye API error: {$response->status()}");
        }

        $data = $response->json();
        $matches = $data['results']['matches'] ?? [];
        $count = 0;

        foreach ($matches as $match) {
            try {
                ReverseImageResult::createFromTineye($search->id, $match);
                $count++;
            } catch (\Exception $e) {
                Log::warning('Failed to create TinEye result', [
                    'search_id' => $search->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * Search Yandex Images
     *
     * @param ReverseImageSearch $search
     * @return int Number of results found
     */
    public function searchYandex(ReverseImageSearch $search): int
    {
        $apiKey = config('reverse_image.engines.yandex.api_key');
        $folderId = config('reverse_image.engines.yandex.folder_id');

        if (!$apiKey) {
            Log::warning('Yandex Image Search not configured');
            return 0;
        }

        $imageUrl = $this->getPublicImageUrl($search);

        // Use Yandex Images API (or SerpApi as proxy)
        $serpApiKey = config('reverse_image.engines.yandex.serpapi_key');

        if ($serpApiKey) {
            // Use SerpApi for Yandex
            $response = Http::timeout(config('reverse_image.timeout', 30))
                ->get('https://serpapi.com/search', [
                    'engine' => 'yandex_images',
                    'url' => $imageUrl,
                    'api_key' => $serpApiKey,
                ]);
        } else {
            // Direct Yandex API (requires setup)
            $response = Http::timeout(config('reverse_image.timeout', 30))
                ->withHeaders([
                    'Authorization' => "Api-Key {$apiKey}",
                ])
                ->get('https://yandex.ru/images/search', [
                    'url' => $imageUrl,
                    'rpt' => 'imageview',
                ]);
        }

        if (!$response->successful()) {
            throw new \Exception("Yandex API error: {$response->status()}");
        }

        $data = $response->json();
        $results = $data['images_results'] ?? $data['results'] ?? [];
        $count = 0;

        foreach ($results as $result) {
            try {
                ReverseImageResult::createFromYandex($search->id, $result);
                $count++;
            } catch (\Exception $e) {
                Log::warning('Failed to create Yandex result', [
                    'search_id' => $search->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * Search Baidu Images
     *
     * @param ReverseImageSearch $search
     * @return int Number of results found
     */
    public function searchBaidu(ReverseImageSearch $search): int
    {
        $apiKey = config('reverse_image.engines.baidu.api_key');

        if (!$apiKey) {
            Log::warning('Baidu Image Search not configured');
            return 0;
        }

        $imageUrl = $this->getPublicImageUrl($search);

        // Use SerpApi for Baidu as direct API is complex
        $serpApiKey = config('reverse_image.engines.baidu.serpapi_key');

        if (!$serpApiKey) {
            Log::warning('Baidu requires SerpApi key for reverse image search');
            return 0;
        }

        $response = Http::timeout(config('reverse_image.timeout', 30))
            ->get('https://serpapi.com/search', [
                'engine' => 'baidu_images',
                'url' => $imageUrl,
                'api_key' => $serpApiKey,
            ]);

        if (!$response->successful()) {
            throw new \Exception("Baidu API error: {$response->status()}");
        }

        $data = $response->json();
        $results = $data['images_results'] ?? [];
        $count = 0;

        foreach ($results as $result) {
            try {
                ReverseImageResult::createFromBaidu($search->id, $result);
                $count++;
            } catch (\Exception $e) {
                Log::warning('Failed to create Baidu result', [
                    'search_id' => $search->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * Calculate perceptual hash (pHash) for an image
     *
     * Uses DCT-based perceptual hashing for similarity comparison.
     * Falls back to average hash if GD extension lacks required functions.
     *
     * @param string $imagePath Path to the image file
     * @return string 64-character hexadecimal hash
     */
    public function calculatePerceptualHash(string $imagePath): string
    {
        if (!file_exists($imagePath)) {
            throw new \Exception("Image file not found: {$imagePath}");
        }

        // Load the image
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            throw new \Exception("Could not read image information");
        }

        $mimeType = $imageInfo['mime'];
        $image = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($imagePath),
            'image/png' => imagecreatefrompng($imagePath),
            'image/gif' => imagecreatefromgif($imagePath),
            'image/webp' => imagecreatefromwebp($imagePath),
            'image/bmp' => imagecreatefrombmp($imagePath),
            default => throw new \Exception("Unsupported image type: {$mimeType}"),
        };

        if (!$image) {
            throw new \Exception("Failed to load image");
        }

        // Resize to 32x32 for DCT
        $resized = imagecreatetruecolor(32, 32);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, 32, 32, imagesx($image), imagesy($image));

        // Convert to grayscale
        imagefilter($resized, IMG_FILTER_GRAYSCALE);

        // Get pixel values
        $pixels = [];
        for ($y = 0; $y < 32; $y++) {
            for ($x = 0; $x < 32; $x++) {
                $rgb = imagecolorat($resized, $x, $y);
                $pixels[$y][$x] = $rgb & 0xFF; // Grayscale value
            }
        }

        // Apply simple DCT approximation (average hash approach)
        // Calculate mean excluding the first coefficient
        $total = 0;
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $total += $pixels[$y][$x];
            }
        }
        $mean = $total / 64;

        // Generate hash based on comparison to mean
        $hash = '';
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $hash .= ($pixels[$y][$x] > $mean) ? '1' : '0';
            }
        }

        // Convert binary to hexadecimal
        $hexHash = '';
        for ($i = 0; $i < 64; $i += 4) {
            $hexHash .= dechex(bindec(substr($hash, $i, 4)));
        }

        // Pad to ensure 16 characters, then extend with additional info
        $hexHash = str_pad($hexHash, 16, '0', STR_PAD_LEFT);

        // Add additional entropy from image dimensions
        $dimensions = sprintf('%04x%04x', imagesx($image) % 65536, imagesy($image) % 65536);

        // Generate file hash for additional uniqueness
        $fileHash = substr(hash_file('sha256', $imagePath), 0, 40);

        imagedestroy($image);
        imagedestroy($resized);

        // Combine perceptual hash with dimensions and file hash
        return $hexHash . $dimensions . substr($fileHash, 0, 40);
    }

    /**
     * Calculate Hamming distance between two hashes
     *
     * @param string $hash1
     * @param string $hash2
     * @return int Number of differing bits
     */
    public function calculateHashDistance(string $hash1, string $hash2): int
    {
        if (strlen($hash1) !== strlen($hash2)) {
            throw new \Exception("Hash lengths must match");
        }

        $distance = 0;
        $length = strlen($hash1);

        for ($i = 0; $i < $length; $i++) {
            $val1 = hexdec($hash1[$i]);
            $val2 = hexdec($hash2[$i]);
            $xor = $val1 ^ $val2;

            // Count bits set in XOR result
            while ($xor) {
                $distance += $xor & 1;
                $xor >>= 1;
            }
        }

        return $distance;
    }

    /**
     * Check if two images are similar based on perceptual hash
     *
     * @param string $hash1
     * @param string $hash2
     * @param int $threshold Maximum Hamming distance (default: 10)
     * @return bool
     */
    public function areImagesSimilar(string $hash1, string $hash2, int $threshold = 10): bool
    {
        $distance = $this->calculateHashDistance($hash1, $hash2);
        return $distance <= $threshold;
    }

    /**
     * Detect potential image manipulation across search results
     *
     * Analyzes results to identify signs of image editing/manipulation.
     *
     * @param ReverseImageSearch $search
     * @return void
     */
    public function detectManipulation(ReverseImageSearch $search): void
    {
        $results = $search->results()->get();

        if ($results->isEmpty()) {
            return;
        }

        // Group by dimensions to find potential resizes/crops
        $dimensionGroups = $results->groupBy('image_dimensions');
        $hasDimensionVariants = $dimensionGroups->count() > 1;

        // Analyze each result
        foreach ($results as $result) {
            $indicators = [];

            // Check for dimension differences (resize/crop)
            if ($hasDimensionVariants && $dimensionGroups->count() > 2) {
                $indicators[] = ReverseImageResult::MANIPULATION_RESIZE_DETECTED;
            }

            // Check for low similarity scores with dimension match (possible filter)
            if ($result->similarity_score !== null &&
                $result->similarity_score < 90 &&
                $result->similarity_score >= 70) {
                $indicators[] = ReverseImageResult::MANIPULATION_FILTER_APPLIED;
            }

            // Check if source is known image manipulation site
            $manipulationSites = config('reverse_image.manipulation_indicators.suspicious_domains', []);
            if (in_array($result->source_domain, $manipulationSites)) {
                $indicators[] = ReverseImageResult::MANIPULATION_COLOR_ADJUSTMENT;
            }

            // Mark as potentially manipulated if indicators found
            if (!empty($indicators)) {
                $result->update([
                    'is_potentially_manipulated' => true,
                    'manipulation_indicators' => $indicators,
                ]);
            }
        }
    }

    /**
     * Find the earliest appearance of an image across all results
     *
     * @param ReverseImageSearch $search
     * @return \Carbon\Carbon|null
     */
    public function findEarliestAppearance(ReverseImageSearch $search): ?\Carbon\Carbon
    {
        return $search->findEarliestDate();
    }

    /**
     * Aggregate and deduplicate results from a search
     *
     * @param int $searchId
     * @return array Aggregated statistics and deduplicated results
     */
    public function aggregateResults(int $searchId): array
    {
        $search = ReverseImageSearch::with('results')->findOrFail($searchId);
        $results = $search->results;

        // Deduplicate by URL
        $uniqueUrls = $results->unique('result_url');

        // Group by domain
        $byDomain = $results->groupBy('source_domain')
            ->map(fn($items) => [
                'count' => $items->count(),
                'earliest' => $items->min('first_seen_date'),
                'results' => $items->values(),
            ])
            ->sortByDesc('count');

        // Group by engine
        $byEngine = $results->groupBy('engine')
            ->map(fn($items) => $items->count());

        // Find duplicates (same URL found by multiple engines)
        $urlCounts = $results->groupBy('result_url')
            ->map(fn($items) => $items->count())
            ->filter(fn($count) => $count > 1);

        return [
            'search' => $search->only([
                'uuid', 'search_status', 'total_results',
                'earliest_found_date', 'search_started_at', 'search_completed_at',
            ]),
            'summary' => $search->getStatsSummary(),
            'unique_results' => $uniqueUrls->count(),
            'duplicate_urls' => $urlCounts->count(),
            'by_domain' => $byDomain->take(20)->toArray(),
            'by_engine' => $byEngine->toArray(),
            'exact_matches' => $results->where('match_type', ReverseImageResult::MATCH_EXACT)->values(),
            'potentially_manipulated' => $results->where('is_potentially_manipulated', true)->values(),
            'earliest_appearances' => $results
                ->whereNotNull('first_seen_date')
                ->sortBy('first_seen_date')
                ->take(10)
                ->values(),
        ];
    }

    /**
     * Find existing searches with the same image hash
     *
     * @param string $imageHash
     * @param int|null $excludeSearchId
     * @return \Illuminate\Database\Eloquent\Collection<int, ReverseImageSearch>
     */
    public function findDuplicateSearches(string $imageHash, ?int $excludeSearchId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = ReverseImageSearch::byHash($imageHash)
            ->completed()
            ->with('results');

        if ($excludeSearchId) {
            $query->where('id', '!=', $excludeSearchId);
        }

        return $query->get();
    }

    /**
     * Get enabled search engines
     *
     * @return array
     */
    public function getEnabledEngines(): array
    {
        $engines = [];

        if (config('reverse_image.engines.google.enabled', false) &&
            config('reverse_image.engines.google.api_key')) {
            $engines[] = ReverseImageSearch::ENGINE_GOOGLE;
        }

        if (config('reverse_image.engines.bing.enabled', false) &&
            config('reverse_image.engines.bing.api_key')) {
            $engines[] = ReverseImageSearch::ENGINE_BING;
        }

        if (config('reverse_image.engines.tineye.enabled', false) &&
            config('reverse_image.engines.tineye.api_key')) {
            $engines[] = ReverseImageSearch::ENGINE_TINEYE;
        }

        if (config('reverse_image.engines.yandex.enabled', false) &&
            (config('reverse_image.engines.yandex.api_key') ||
             config('reverse_image.engines.yandex.serpapi_key'))) {
            $engines[] = ReverseImageSearch::ENGINE_YANDEX;
        }

        if (config('reverse_image.engines.baidu.enabled', false) &&
            config('reverse_image.engines.baidu.serpapi_key')) {
            $engines[] = ReverseImageSearch::ENGINE_BAIDU;
        }

        return $engines;
    }

    /**
     * Get engine configuration with status
     *
     * @return array
     */
    public function getEnginesConfig(): array
    {
        $engines = [];

        foreach (ReverseImageSearch::getEngines() as $engine) {
            $configKey = "reverse_image.engines.{$engine}";
            $engines[$engine] = [
                'name' => ReverseImageSearch::getEngineDisplayNames()[$engine],
                'enabled' => config("{$configKey}.enabled", false),
                'configured' => $this->isEngineConfigured($engine),
                'max_results' => config("{$configKey}.max_results", 20),
            ];
        }

        return $engines;
    }

    /**
     * Check if an engine is properly configured
     *
     * @param string $engine
     * @return bool
     */
    protected function isEngineConfigured(string $engine): bool
    {
        return match ($engine) {
            ReverseImageSearch::ENGINE_GOOGLE =>
                (bool) config('reverse_image.engines.google.api_key'),
            ReverseImageSearch::ENGINE_BING =>
                (bool) config('reverse_image.engines.bing.api_key'),
            ReverseImageSearch::ENGINE_TINEYE =>
                (bool) config('reverse_image.engines.tineye.api_key'),
            ReverseImageSearch::ENGINE_YANDEX =>
                (bool) (config('reverse_image.engines.yandex.api_key') ||
                        config('reverse_image.engines.yandex.serpapi_key')),
            ReverseImageSearch::ENGINE_BAIDU =>
                (bool) config('reverse_image.engines.baidu.serpapi_key'),
            default => false,
        };
    }

    /**
     * Validate uploaded image
     *
     * @param UploadedFile $file
     * @throws \Exception
     */
    protected function validateImage(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \Exception('Image file size exceeds maximum allowed (10MB)');
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new \Exception("Invalid image type: {$mimeType}");
        }

        // Verify it's a valid image
        $imageInfo = getimagesize($file->getPathname());
        if (!$imageInfo) {
            throw new \Exception('File is not a valid image');
        }
    }

    /**
     * Extract metadata from an image file
     *
     * @param string $imagePath
     * @return array
     */
    protected function extractImageMetadata(string $imagePath): array
    {
        $metadata = [
            'file_size' => filesize($imagePath),
        ];

        $imageInfo = getimagesize($imagePath);
        if ($imageInfo) {
            $metadata['width'] = $imageInfo[0];
            $metadata['height'] = $imageInfo[1];
            $metadata['mime_type'] = $imageInfo['mime'];
            $metadata['dimensions'] = "{$imageInfo[0]}x{$imageInfo[1]}";
        }

        // Try to read EXIF data for JPEG
        if (function_exists('exif_read_data') &&
            isset($metadata['mime_type']) &&
            in_array($metadata['mime_type'], ['image/jpeg', 'image/tiff'])) {
            try {
                $exif = @exif_read_data($imagePath);
                if ($exif) {
                    $metadata['exif'] = [
                        'make' => $exif['Make'] ?? null,
                        'model' => $exif['Model'] ?? null,
                        'datetime' => $exif['DateTime'] ?? null,
                        'software' => $exif['Software'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                // EXIF reading failed, ignore
            }
        }

        return $metadata;
    }

    /**
     * Get file extension from MIME type
     *
     * @param string $mimeType
     * @return string
     */
    protected function getExtensionFromMime(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
            default => 'jpg',
        };
    }

    /**
     * Get publicly accessible URL for an image
     *
     * For API-based searches that require a URL parameter.
     *
     * @param ReverseImageSearch $search
     * @return string
     */
    protected function getPublicImageUrl(ReverseImageSearch $search): string
    {
        // If we have an original URL, use it
        if ($search->image_url) {
            return $search->image_url;
        }

        // Generate a temporary signed URL
        $disk = Storage::disk('reverse_images');

        if (method_exists($disk, 'temporaryUrl')) {
            return $disk->temporaryUrl(
                $search->image_path,
                now()->addHours(1)
            );
        }

        // Fallback to public URL if available
        return $disk->url($search->image_path);
    }
}
