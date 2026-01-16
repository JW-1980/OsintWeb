<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EvidenceSnapshot;
use App\Models\PreservedEvidence;
use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EvidencePreservationService
{
    /**
     * Preserve a URL and create an evidence record
     *
     * @param string $url URL to preserve
     * @param User $user User creating the preservation
     * @param array $options Additional options
     * @return PreservedEvidence
     */
    public function preserveUrl(
        string $url,
        User $user,
        array $options = []
    ): PreservedEvidence {
        $contentType = $options['content_type'] ?? $this->detectContentType($url);
        $eventId = $options['event_id'] ?? null;
        $expiresAt = $options['expires_at'] ?? null;

        $evidence = PreservedEvidence::create([
            'uuid' => Str::uuid()->toString(),
            'original_url' => $url,
            'content_type' => $contentType,
            'capture_status' => PreservedEvidence::STATUS_PENDING,
            'event_id' => $eventId,
            'created_by' => $user->id,
            'expires_at' => $expiresAt,
            'metadata' => [
                'user_agent' => $options['user_agent'] ?? config('evidence.default_user_agent'),
                'requested_at' => now()->toIso8601String(),
            ],
            'content_hash' => '', // Will be set after capture
        ]);

        return $evidence;
    }

    /**
     * Capture and save URL content to local storage
     *
     * @param PreservedEvidence $evidence
     * @return PreservedEvidence
     * @throws \Exception
     */
    public function preserveToLocal(PreservedEvidence $evidence): PreservedEvidence
    {
        $evidence->markAsCapturing();

        try {
            $response = $this->fetchUrl($evidence->original_url);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch URL: HTTP {$response->status()}");
            }

            $content = $response->body();
            $contentHash = hash('sha256', $content);
            $fileSize = strlen($content);

            // Generate storage path
            $extension = $this->getFileExtension($evidence->content_type, $response);
            $fileName = $evidence->uuid . '.' . $extension;
            $storagePath = date('Y/m/d') . '/' . $fileName;

            // Store the content
            Storage::disk('evidence')->put($storagePath, $content);

            // Extract metadata
            $metadata = $this->extractMetadata($content, $response, $evidence->content_type);
            $existingMetadata = $evidence->metadata ?? [];

            $evidence->markAsCaptured($contentHash, $fileSize, $storagePath);
            $evidence->update([
                'metadata' => array_merge($existingMetadata, $metadata),
            ]);

            // Create initial snapshot
            $this->createSnapshot($evidence);

            Log::info('Evidence preserved locally', [
                'evidence_id' => $evidence->id,
                'url' => $evidence->original_url,
                'size' => $fileSize,
            ]);

            return $evidence->fresh();

        } catch (\Exception $e) {
            $evidence->markAsFailed($e->getMessage());

            Log::error('Evidence preservation failed', [
                'evidence_id' => $evidence->id,
                'url' => $evidence->original_url,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Upload content to IPFS (using Pinata or Infura)
     *
     * @param PreservedEvidence $evidence
     * @return string|null IPFS hash (CID)
     */
    public function preserveToIpfs(PreservedEvidence $evidence): ?string
    {
        if (!$evidence->local_path || !Storage::disk('evidence')->exists($evidence->local_path)) {
            Log::warning('Cannot upload to IPFS: no local content', [
                'evidence_id' => $evidence->id,
            ]);
            return null;
        }

        $provider = config('evidence.ipfs.provider', 'pinata');

        try {
            $content = Storage::disk('evidence')->get($evidence->local_path);

            $ipfsHash = match ($provider) {
                'pinata' => $this->uploadToPinata($content, $evidence),
                'infura' => $this->uploadToInfura($content, $evidence),
                default => throw new \Exception("Unknown IPFS provider: {$provider}"),
            };

            if ($ipfsHash) {
                $evidence->update(['ipfs_hash' => $ipfsHash]);

                Log::info('Evidence uploaded to IPFS', [
                    'evidence_id' => $evidence->id,
                    'ipfs_hash' => $ipfsHash,
                    'provider' => $provider,
                ]);
            }

            return $ipfsHash;

        } catch (\Exception $e) {
            Log::error('IPFS upload failed', [
                'evidence_id' => $evidence->id,
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Submit URL to Wayback Machine for archival
     *
     * @param PreservedEvidence $evidence
     * @return string|null Archived URL
     */
    public function submitToWaybackMachine(PreservedEvidence $evidence): ?string
    {
        if (!config('evidence.wayback.enabled', true)) {
            return null;
        }

        try {
            // Submit to Save Page Now API
            $response = Http::timeout(config('evidence.wayback.timeout', 60))
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->post('https://web.archive.org/save/' . $evidence->original_url);

            if ($response->successful()) {
                // Get the archived URL from response headers
                $archivedUrl = $response->header('Content-Location')
                    ?? $response->header('Location');

                if (!$archivedUrl) {
                    // Try to construct from response
                    $data = $response->json();
                    $timestamp = $data['timestamp'] ?? date('YmdHis');
                    $archivedUrl = "https://web.archive.org/web/{$timestamp}/{$evidence->original_url}";
                }

                $evidence->update(['archived_url' => $archivedUrl]);

                Log::info('Evidence submitted to Wayback Machine', [
                    'evidence_id' => $evidence->id,
                    'archived_url' => $archivedUrl,
                ]);

                return $archivedUrl;
            }

            Log::warning('Wayback Machine submission returned non-success', [
                'evidence_id' => $evidence->id,
                'status' => $response->status(),
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Wayback Machine submission failed', [
                'evidence_id' => $evidence->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Capture a screenshot of a webpage
     *
     * @param PreservedEvidence $evidence
     * @return string|null Path to screenshot
     */
    public function captureScreenshot(PreservedEvidence $evidence): ?string
    {
        if ($evidence->content_type !== PreservedEvidence::TYPE_WEBPAGE) {
            return null;
        }

        $screenshotService = config('evidence.screenshot.service', 'none');

        if ($screenshotService === 'none') {
            return null;
        }

        try {
            $screenshotPath = match ($screenshotService) {
                'screenshotone' => $this->captureWithScreenshotOne($evidence),
                'browserless' => $this->captureWithBrowserless($evidence),
                default => null,
            };

            if ($screenshotPath) {
                $metadata = $evidence->metadata ?? [];
                $metadata['screenshot_path'] = $screenshotPath;
                $evidence->update(['metadata' => $metadata]);

                Log::info('Screenshot captured', [
                    'evidence_id' => $evidence->id,
                    'path' => $screenshotPath,
                ]);
            }

            return $screenshotPath;

        } catch (\Exception $e) {
            Log::error('Screenshot capture failed', [
                'evidence_id' => $evidence->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Extract metadata from content
     *
     * @param string $content
     * @param \Illuminate\Http\Client\Response $response
     * @param string $contentType
     * @return array
     */
    public function extractMetadata(
        string $content,
        \Illuminate\Http\Client\Response $response,
        string $contentType
    ): array {
        $metadata = [
            'content_length' => strlen($content),
            'response_headers' => $this->sanitizeHeaders($response->headers()),
            'captured_at' => now()->toIso8601String(),
        ];

        if ($contentType === PreservedEvidence::TYPE_WEBPAGE) {
            $metadata = array_merge($metadata, $this->extractHtmlMetadata($content));
        }

        return $metadata;
    }

    /**
     * Verify the hash of stored evidence
     *
     * @param int $evidenceId
     * @return array Verification result
     */
    public function verifyHash(int $evidenceId): array
    {
        $evidence = PreservedEvidence::findOrFail($evidenceId);

        $result = [
            'evidence_id' => $evidence->id,
            'original_hash' => $evidence->content_hash,
            'verified_at' => now()->toIso8601String(),
        ];

        if (!$evidence->local_path || !Storage::disk('evidence')->exists($evidence->local_path)) {
            $result['status'] = 'missing';
            $result['message'] = 'Local file not found';
            return $result;
        }

        $content = Storage::disk('evidence')->get($evidence->local_path);
        $currentHash = hash('sha256', $content);

        $isValid = hash_equals($evidence->content_hash, $currentHash);

        $evidence->update([
            'is_verified' => $isValid,
            'last_verified_at' => now(),
        ]);

        $result['current_hash'] = $currentHash;
        $result['status'] = $isValid ? 'valid' : 'tampered';
        $result['message'] = $isValid
            ? 'Content integrity verified'
            : 'Content hash mismatch - possible tampering detected';

        return $result;
    }

    /**
     * Create a new snapshot of evidence
     *
     * @param PreservedEvidence $evidence
     * @return EvidenceSnapshot
     */
    public function createSnapshot(PreservedEvidence $evidence): EvidenceSnapshot
    {
        if (!$evidence->local_path || !Storage::disk('evidence')->exists($evidence->local_path)) {
            throw new \Exception('Cannot create snapshot: no local content');
        }

        $content = Storage::disk('evidence')->get($evidence->local_path);
        $snapshotHash = hash('sha256', $content);
        $fileSize = strlen($content);

        // Generate snapshot path
        $extension = pathinfo($evidence->local_path, PATHINFO_EXTENSION);
        $snapshotFileName = $evidence->uuid . '_' . now()->format('YmdHis') . '.' . $extension;
        $snapshotPath = 'snapshots/' . date('Y/m/d') . '/' . $snapshotFileName;

        // Copy content to snapshot location
        Storage::disk('evidence')->put($snapshotPath, $content);

        // Mark all existing snapshots as non-primary
        EvidenceSnapshot::where('preserved_evidence_id', $evidence->id)
            ->update(['is_primary' => false]);

        // Create new primary snapshot
        $snapshot = EvidenceSnapshot::create([
            'preserved_evidence_id' => $evidence->id,
            'snapshot_hash' => $snapshotHash,
            'snapshot_path' => $snapshotPath,
            'file_size' => $fileSize,
            'is_primary' => true,
            'captured_at' => now(),
            'metadata' => [
                'created_from' => $evidence->local_path,
                'original_hash' => $evidence->content_hash,
            ],
        ]);

        Log::info('Evidence snapshot created', [
            'evidence_id' => $evidence->id,
            'snapshot_id' => $snapshot->id,
            'hash' => $snapshotHash,
        ]);

        return $snapshot;
    }

    /**
     * Detect content type from URL
     *
     * @param string $url
     * @return string
     */
    protected function detectContentType(string $url): string
    {
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $host = strtolower($parsedUrl['host'] ?? '');

        // Check for social media platforms
        $socialPatterns = [
            'twitter.com' => PreservedEvidence::TYPE_SOCIAL_POST,
            'x.com' => PreservedEvidence::TYPE_SOCIAL_POST,
            'facebook.com' => PreservedEvidence::TYPE_SOCIAL_POST,
            'instagram.com' => PreservedEvidence::TYPE_SOCIAL_POST,
            'tiktok.com' => PreservedEvidence::TYPE_SOCIAL_POST,
            'telegram.org' => PreservedEvidence::TYPE_SOCIAL_POST,
            't.me' => PreservedEvidence::TYPE_SOCIAL_POST,
            'vk.com' => PreservedEvidence::TYPE_SOCIAL_POST,
        ];

        foreach ($socialPatterns as $pattern => $type) {
            if (str_contains($host, $pattern)) {
                return $type;
            }
        }

        // Check by file extension
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
        $videoExtensions = ['mp4', 'webm', 'avi', 'mov', 'mkv', 'flv'];
        $audioExtensions = ['mp3', 'wav', 'ogg', 'flac', 'm4a'];
        $documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
        $archiveExtensions = ['zip', 'rar', '7z', 'tar', 'gz'];

        if (in_array($extension, $imageExtensions)) {
            return PreservedEvidence::TYPE_IMAGE;
        }
        if (in_array($extension, $videoExtensions)) {
            return PreservedEvidence::TYPE_VIDEO;
        }
        if (in_array($extension, $audioExtensions)) {
            return PreservedEvidence::TYPE_AUDIO;
        }
        if (in_array($extension, $documentExtensions)) {
            return PreservedEvidence::TYPE_DOCUMENT;
        }
        if (in_array($extension, $archiveExtensions)) {
            return PreservedEvidence::TYPE_ARCHIVE;
        }

        return PreservedEvidence::TYPE_WEBPAGE;
    }

    /**
     * Fetch URL content with retry logic
     *
     * @param string $url
     * @return \Illuminate\Http\Client\Response
     */
    protected function fetchUrl(string $url): \Illuminate\Http\Client\Response
    {
        $maxRetries = config('evidence.fetch.max_retries', 3);
        $timeout = config('evidence.fetch.timeout', 30);
        $userAgent = config('evidence.default_user_agent', 'OsintWeb Evidence Archiver/1.0');

        $lastException = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => $userAgent,
                    'Accept' => '*/*',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                    ->timeout($timeout)
                    ->withOptions([
                        'verify' => config('evidence.fetch.verify_ssl', true),
                        'allow_redirects' => [
                            'max' => config('evidence.fetch.max_redirects', 5),
                            'track_redirects' => true,
                        ],
                    ])
                    ->get($url);

                return $response;

            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning("Fetch attempt {$attempt} failed for URL", [
                    'url' => $url,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt < $maxRetries) {
                    // Exponential backoff
                    sleep(pow(2, $attempt - 1));
                }
            }
        }

        throw $lastException ?? new \Exception('Failed to fetch URL after all retries');
    }

    /**
     * Get file extension based on content type
     *
     * @param string $contentType
     * @param \Illuminate\Http\Client\Response $response
     * @return string
     */
    protected function getFileExtension(string $contentType, \Illuminate\Http\Client\Response $response): string
    {
        $mimeType = $response->header('Content-Type');

        $mimeToExtension = [
            'text/html' => 'html',
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            'audio/mpeg' => 'mp3',
            'audio/wav' => 'wav',
            'application/json' => 'json',
            'text/plain' => 'txt',
        ];

        if ($mimeType) {
            $baseMime = explode(';', $mimeType)[0];
            if (isset($mimeToExtension[$baseMime])) {
                return $mimeToExtension[$baseMime];
            }
        }

        return match ($contentType) {
            PreservedEvidence::TYPE_WEBPAGE => 'html',
            PreservedEvidence::TYPE_IMAGE => 'jpg',
            PreservedEvidence::TYPE_VIDEO => 'mp4',
            PreservedEvidence::TYPE_AUDIO => 'mp3',
            PreservedEvidence::TYPE_DOCUMENT => 'pdf',
            default => 'bin',
        };
    }

    /**
     * Extract metadata from HTML content
     *
     * @param string $html
     * @return array
     */
    protected function extractHtmlMetadata(string $html): array
    {
        $metadata = [];

        // Extract title
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            $metadata['title'] = html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
        }

        // Extract meta description
        if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/is', $html, $matches)) {
            $metadata['description'] = html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
        }

        // Extract Open Graph data
        $ogTags = ['og:title', 'og:description', 'og:image', 'og:type', 'og:site_name'];
        foreach ($ogTags as $tag) {
            if (preg_match('/<meta[^>]+property=["\']' . preg_quote($tag, '/') . '["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/is', $html, $matches)) {
                $key = str_replace('og:', 'og_', $tag);
                $metadata[$key] = html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
            }
        }

        // Extract Twitter Card data
        $twitterTags = ['twitter:title', 'twitter:description', 'twitter:image', 'twitter:card'];
        foreach ($twitterTags as $tag) {
            if (preg_match('/<meta[^>]+name=["\']' . preg_quote($tag, '/') . '["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/is', $html, $matches)) {
                $key = str_replace('twitter:', 'twitter_', $tag);
                $metadata[$key] = html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
            }
        }

        // Extract canonical URL
        if (preg_match('/<link[^>]+rel=["\']canonical["\'][^>]+href=["\']([^"\']+)["\'][^>]*>/is', $html, $matches)) {
            $metadata['canonical_url'] = $matches[1];
        }

        // Count links
        preg_match_all('/<a[^>]+href=/i', $html, $linkMatches);
        $metadata['link_count'] = count($linkMatches[0]);

        // Count images
        preg_match_all('/<img[^>]+>/i', $html, $imageMatches);
        $metadata['image_count'] = count($imageMatches[0]);

        return $metadata;
    }

    /**
     * Sanitize response headers for storage
     *
     * @param array $headers
     * @return array
     */
    protected function sanitizeHeaders(array $headers): array
    {
        $safeHeaders = [
            'Content-Type',
            'Content-Length',
            'Last-Modified',
            'ETag',
            'Cache-Control',
            'Date',
            'Server',
            'X-Frame-Options',
            'Content-Security-Policy',
        ];

        $sanitized = [];
        foreach ($safeHeaders as $header) {
            if (isset($headers[$header])) {
                $sanitized[$header] = is_array($headers[$header])
                    ? $headers[$header][0]
                    : $headers[$header];
            }
        }

        return $sanitized;
    }

    /**
     * Upload content to Pinata IPFS service
     *
     * @param string $content
     * @param PreservedEvidence $evidence
     * @return string|null IPFS CID
     */
    protected function uploadToPinata(string $content, PreservedEvidence $evidence): ?string
    {
        $apiKey = config('evidence.ipfs.pinata_api_key');
        $apiSecret = config('evidence.ipfs.pinata_api_secret');

        if (!$apiKey || !$apiSecret) {
            Log::warning('Pinata API credentials not configured');
            return null;
        }

        $response = Http::withHeaders([
            'pinata_api_key' => $apiKey,
            'pinata_secret_api_key' => $apiSecret,
        ])
            ->timeout(120)
            ->attach('file', $content, $evidence->uuid . '.html')
            ->post('https://api.pinata.cloud/pinning/pinFileToIPFS', [
                'pinataMetadata' => json_encode([
                    'name' => "Evidence-{$evidence->uuid}",
                    'keyvalues' => [
                        'evidence_id' => $evidence->id,
                        'original_url' => substr($evidence->original_url, 0, 200),
                    ],
                ]),
            ]);

        if ($response->successful()) {
            return $response->json('IpfsHash');
        }

        Log::error('Pinata upload failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    /**
     * Upload content to Infura IPFS service
     *
     * @param string $content
     * @param PreservedEvidence $evidence
     * @return string|null IPFS CID
     */
    protected function uploadToInfura(string $content, PreservedEvidence $evidence): ?string
    {
        $projectId = config('evidence.ipfs.infura_project_id');
        $projectSecret = config('evidence.ipfs.infura_project_secret');

        if (!$projectId || !$projectSecret) {
            Log::warning('Infura IPFS credentials not configured');
            return null;
        }

        $response = Http::withBasicAuth($projectId, $projectSecret)
            ->timeout(120)
            ->attach('file', $content, $evidence->uuid . '.html')
            ->post('https://ipfs.infura.io:5001/api/v0/add');

        if ($response->successful()) {
            return $response->json('Hash');
        }

        Log::error('Infura IPFS upload failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    /**
     * Capture screenshot using ScreenshotOne API
     *
     * @param PreservedEvidence $evidence
     * @return string|null Path to saved screenshot
     */
    protected function captureWithScreenshotOne(PreservedEvidence $evidence): ?string
    {
        $apiKey = config('evidence.screenshot.screenshotone_api_key');

        if (!$apiKey) {
            return null;
        }

        $response = Http::timeout(60)
            ->get('https://api.screenshotone.com/take', [
                'access_key' => $apiKey,
                'url' => $evidence->original_url,
                'full_page' => 'true',
                'format' => 'png',
                'viewport_width' => 1920,
                'viewport_height' => 1080,
            ]);

        if ($response->successful()) {
            $screenshotPath = 'screenshots/' . date('Y/m/d') . '/' . $evidence->uuid . '.png';
            Storage::disk('evidence')->put($screenshotPath, $response->body());
            return $screenshotPath;
        }

        return null;
    }

    /**
     * Capture screenshot using Browserless service
     *
     * @param PreservedEvidence $evidence
     * @return string|null Path to saved screenshot
     */
    protected function captureWithBrowserless(PreservedEvidence $evidence): ?string
    {
        $apiUrl = config('evidence.screenshot.browserless_url');
        $apiToken = config('evidence.screenshot.browserless_token');

        if (!$apiUrl || !$apiToken) {
            return null;
        }

        $response = Http::timeout(60)
            ->withToken($apiToken)
            ->post("{$apiUrl}/screenshot", [
                'url' => $evidence->original_url,
                'options' => [
                    'fullPage' => true,
                    'type' => 'png',
                ],
            ]);

        if ($response->successful()) {
            $screenshotPath = 'screenshots/' . date('Y/m/d') . '/' . $evidence->uuid . '.png';
            Storage::disk('evidence')->put($screenshotPath, $response->body());
            return $screenshotPath;
        }

        return null;
    }
}
