<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DisinformationAnalysis;
use App\Models\DisinformationPattern;
use App\Domains\Intelligence\Models\Event;
use App\Models\FlaggedContent;
use App\Models\SocialMediaPost;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Disinformation Detection Service
 *
 * Handles comprehensive disinformation analysis including:
 * - Content analysis for propaganda, fake accounts, and coordinated behavior
 * - Pattern matching against known disinformation tactics
 * - Temporal and network analysis for coordinated campaigns
 * - AI-powered text analysis using OpenRouter free models
 * - Source credibility assessment
 * - Flagging and review workflow management
 */
class DisinformationService
{
    /**
     * Supported analyzable content types.
     */
    protected const ANALYZABLE_TYPES = [
        'event' => Event::class,
        'post' => SocialMediaPost::class,
        'article' => Article::class,
    ];

    /**
     * OpenRouter API base URL.
     */
    protected const OPENROUTER_API_URL = 'https://openrouter.ai/api/v1/chat/completions';

    /**
     * Analyze content for disinformation.
     *
     * @param string $type Content type (event, post, article)
     * @param int $id Content ID
     * @param User $user User performing analysis
     * @param string|null $analysisType Specific analysis type or null for auto-detect
     * @return DisinformationAnalysis
     * @throws \InvalidArgumentException
     */
    public function analyzeContent(
        string $type,
        int $id,
        User $user,
        ?string $analysisType = null
    ): DisinformationAnalysis {
        // Validate content type
        if (!isset(self::ANALYZABLE_TYPES[$type])) {
            throw new \InvalidArgumentException("Invalid content type: {$type}");
        }

        $modelClass = self::ANALYZABLE_TYPES[$type];
        $content = $modelClass::findOrFail($id);

        // Create analysis record
        $analysis = DisinformationAnalysis::create([
            'uuid' => Str::uuid()->toString(),
            'analyzable_type' => $modelClass,
            'analyzable_id' => $id,
            'analysis_type' => $analysisType ?? DisinformationAnalysis::TYPE_PROPAGANDA,
            'threat_score' => 0,
            'confidence_score' => 0,
            'analysis_status' => DisinformationAnalysis::STATUS_PROCESSING,
            'manual_review_status' => DisinformationAnalysis::REVIEW_PENDING,
            'created_by' => $user->id,
        ]);

        try {
            // Extract text content for analysis
            $textContent = $this->extractTextContent($content);

            // Run various analysis methods
            $indicators = [];
            $threatScore = 0;
            $confidenceScore = 0;

            // Pattern matching
            $patternResults = $this->getPatternMatches($textContent);
            if (!empty($patternResults['matches'])) {
                $indicators['patterns'] = $patternResults['matches'];
                $threatScore += $patternResults['threat_contribution'];
                $confidenceScore += 20;

                // Record pattern matches
                foreach ($patternResults['matched_pattern_ids'] as $patternId => $matchData) {
                    $analysis->addPattern($patternId, $matchData, $matchData['confidence'] ?? 0);
                }
            }

            // Propaganda indicators
            $propagandaResult = $this->detectPropagandaIndicators($textContent);
            if (!empty($propagandaResult['indicators'])) {
                $indicators['propaganda'] = $propagandaResult['indicators'];
                $threatScore += $propagandaResult['threat_score'];
                $confidenceScore += $propagandaResult['confidence'];
            }

            // AI-powered analysis (if configured)
            if (config('disinformation.openrouter.enabled', false)) {
                $aiResult = $this->performAiAnalysis($textContent, $analysisType);
                if ($aiResult) {
                    $indicators['ai_analysis'] = $aiResult['indicators'];
                    $threatScore += $aiResult['threat_score'];
                    $confidenceScore += $aiResult['confidence'];
                    $analysis->update(['ai_analysis' => $aiResult]);
                }
            }

            // Content patterns (repeated phrases, hashtags)
            $contentPatterns = $this->analyzeContentPatterns($textContent);
            if (!empty($contentPatterns)) {
                $analysis->update(['content_patterns' => $contentPatterns]);
            }

            // Calculate final scores
            $threatScore = min(100, $threatScore);
            $confidenceScore = min(100, $confidenceScore);

            // Update analysis with results
            $analysis->markAsCompleted([
                'indicators' => $indicators,
                'threat_score' => $threatScore,
                'confidence_score' => $confidenceScore,
            ]);

            Log::info('Disinformation analysis completed', [
                'analysis_id' => $analysis->id,
                'content_type' => $type,
                'content_id' => $id,
                'threat_score' => $threatScore,
            ]);

            // Auto-flag high threat content
            if ($threatScore >= config('disinformation.auto_flag_threshold', 70)) {
                $this->flagContent($analysis->id, FlaggedContent::REASON_AUTOMATED_DETECTION, $user->id);
            }

            return $analysis->fresh(['patterns', 'flaggedContent']);

        } catch (\Exception $e) {
            $analysis->markAsFailed($e->getMessage());

            Log::error('Disinformation analysis failed', [
                'analysis_id' => $analysis->id,
                'error' => $e->getMessage(),
            ]);

            return $analysis->fresh();
        }
    }

    /**
     * Detect coordinated behavior across multiple posts.
     *
     * @param Collection $posts Collection of SocialMediaPost models
     * @return array Coordinated behavior analysis results
     */
    public function detectCoordinatedBehavior(Collection $posts): array
    {
        if ($posts->count() < 2) {
            return [
                'is_coordinated' => false,
                'confidence' => 0,
                'indicators' => [],
            ];
        }

        $indicators = [];
        $confidenceScore = 0;

        // Analyze posting time patterns
        $timePatterns = $this->analyzePostingTimePatterns($posts);
        if ($timePatterns['is_suspicious']) {
            $indicators['temporal_coordination'] = $timePatterns;
            $confidenceScore += $timePatterns['confidence'];
        }

        // Analyze content similarity
        $contentSimilarity = $this->analyzeContentSimilarity($posts);
        if ($contentSimilarity['has_duplicates'] || $contentSimilarity['similarity_score'] > 0.7) {
            $indicators['content_similarity'] = $contentSimilarity;
            $confidenceScore += 25;
        }

        // Analyze hashtag usage patterns
        $hashtagPatterns = $this->analyzeHashtagPatterns($posts);
        if ($hashtagPatterns['is_suspicious']) {
            $indicators['hashtag_coordination'] = $hashtagPatterns;
            $confidenceScore += 20;
        }

        // Analyze account relationships
        $networkAnalysis = $this->analyzeAccountNetwork($posts);
        if ($networkAnalysis['has_suspicious_connections']) {
            $indicators['network_coordination'] = $networkAnalysis;
            $confidenceScore += 30;
        }

        $isCoordinated = $confidenceScore >= 50;

        return [
            'is_coordinated' => $isCoordinated,
            'confidence' => min(100, $confidenceScore),
            'indicators' => $indicators,
            'posts_analyzed' => $posts->count(),
            'accounts_involved' => $posts->pluck('account_id')->unique()->count(),
        ];
    }

    /**
     * Analyze temporal patterns in content.
     *
     * @param Model $content Content to analyze
     * @return array Temporal pattern analysis
     */
    public function analyzeTemporalPatterns(Model $content): array
    {
        $patterns = [
            'posting_hour' => null,
            'posting_day' => null,
            'burst_detected' => false,
            'unusual_timing' => false,
            'timezone_inconsistency' => false,
        ];

        // Get creation timestamp
        $createdAt = $content->created_at ?? $content->posted_at ?? now();

        $patterns['posting_hour'] = $createdAt->hour;
        $patterns['posting_day'] = $createdAt->dayOfWeek;

        // Check for unusual posting times (e.g., 2-5 AM local time)
        if ($createdAt->hour >= 2 && $createdAt->hour <= 5) {
            $patterns['unusual_timing'] = true;
            $patterns['unusual_timing_note'] = 'Posted during unusual hours (2-5 AM)';
        }

        // If this is a social media post, check for burst posting
        if ($content instanceof SocialMediaPost && $content->account_id) {
            $recentPosts = SocialMediaPost::where('account_id', $content->account_id)
                ->where('posted_at', '>=', $createdAt->copy()->subHour())
                ->where('posted_at', '<=', $createdAt)
                ->count();

            if ($recentPosts > 10) {
                $patterns['burst_detected'] = true;
                $patterns['burst_count'] = $recentPosts;
            }
        }

        return $patterns;
    }

    /**
     * Detect propaganda indicators in text.
     *
     * @param string $text Text to analyze
     * @return array Propaganda analysis results
     */
    public function detectPropagandaIndicators(string $text): array
    {
        $indicators = [];
        $threatScore = 0;
        $confidence = 0;

        if (empty($text)) {
            return [
                'indicators' => [],
                'threat_score' => 0,
                'confidence' => 0,
            ];
        }

        $textLower = strtolower($text);

        // Check for loaded language (emotionally charged words)
        $loadedLanguage = $this->detectLoadedLanguage($textLower);
        if (!empty($loadedLanguage)) {
            $indicators['loaded_language'] = $loadedLanguage;
            $threatScore += min(20, count($loadedLanguage) * 5);
            $confidence += 15;
        }

        // Check for appeal to fear
        $fearIndicators = $this->detectFearAppeal($textLower);
        if (!empty($fearIndicators)) {
            $indicators['fear_appeal'] = $fearIndicators;
            $threatScore += 15;
            $confidence += 10;
        }

        // Check for bandwagon effect language
        $bandwagonIndicators = $this->detectBandwagonLanguage($textLower);
        if (!empty($bandwagonIndicators)) {
            $indicators['bandwagon'] = $bandwagonIndicators;
            $threatScore += 10;
            $confidence += 10;
        }

        // Check for black-and-white thinking
        $binaryIndicators = $this->detectBinaryThinking($textLower);
        if (!empty($binaryIndicators)) {
            $indicators['binary_thinking'] = $binaryIndicators;
            $threatScore += 10;
            $confidence += 10;
        }

        // Check for excessive use of caps and exclamation marks
        $emphasisIndicators = $this->detectExcessiveEmphasis($text);
        if ($emphasisIndicators['is_excessive']) {
            $indicators['excessive_emphasis'] = $emphasisIndicators;
            $threatScore += 5;
            $confidence += 5;
        }

        // Check for whataboutism patterns
        $whataboutism = $this->detectWhataboutism($textLower);
        if (!empty($whataboutism)) {
            $indicators['whataboutism'] = $whataboutism;
            $threatScore += 15;
            $confidence += 15;
        }

        return [
            'indicators' => $indicators,
            'threat_score' => $threatScore,
            'confidence' => $confidence,
        ];
    }

    /**
     * Check source credibility.
     *
     * @param array $sources Array of source URLs or identifiers
     * @return array Source credibility assessment
     */
    public function checkSourceCredibility(array $sources): array
    {
        $results = [];

        foreach ($sources as $source) {
            $result = [
                'source' => $source,
                'credibility_score' => 50, // Default neutral
                'is_known_unreliable' => false,
                'is_known_reliable' => false,
                'flags' => [],
            ];

            // Check against known unreliable sources
            $unreliableDomains = config('disinformation.unreliable_domains', []);
            $reliableDomains = config('disinformation.reliable_domains', []);

            $domain = $this->extractDomain($source);

            if ($domain) {
                if (in_array($domain, $unreliableDomains)) {
                    $result['is_known_unreliable'] = true;
                    $result['credibility_score'] = 10;
                    $result['flags'][] = 'Known unreliable source';
                } elseif (in_array($domain, $reliableDomains)) {
                    $result['is_known_reliable'] = true;
                    $result['credibility_score'] = 90;
                }

                // Check for suspicious domain patterns
                if ($this->hasSuspiciousDomainPattern($domain)) {
                    $result['flags'][] = 'Suspicious domain pattern';
                    $result['credibility_score'] = max(0, $result['credibility_score'] - 20);
                }
            }

            $results[] = $result;
        }

        // Calculate overall credibility
        $averageCredibility = count($results) > 0
            ? array_sum(array_column($results, 'credibility_score')) / count($results)
            : 50;

        return [
            'sources' => $results,
            'average_credibility' => round($averageCredibility, 2),
            'has_unreliable_sources' => count(array_filter($results, fn($r) => $r['is_known_unreliable'])) > 0,
        ];
    }

    /**
     * Calculate threat score from indicators.
     *
     * @param array $indicators Array of detected indicators
     * @return int Threat score (0-100)
     */
    public function calculateThreatScore(array $indicators): int
    {
        $score = 0;

        // Pattern matches
        if (!empty($indicators['patterns'])) {
            $score += min(30, count($indicators['patterns']) * 10);
        }

        // Propaganda indicators
        if (!empty($indicators['propaganda'])) {
            $score += min(25, count($indicators['propaganda']) * 5);
        }

        // Coordinated behavior
        if (!empty($indicators['coordination'])) {
            $score += 20;
        }

        // AI detection
        if (!empty($indicators['ai_analysis']['is_disinformation'])) {
            $score += $indicators['ai_analysis']['threat_score'] ?? 20;
        }

        // Source credibility issues
        if (!empty($indicators['source_issues'])) {
            $score += 15;
        }

        return min(100, $score);
    }

    /**
     * Flag content based on analysis.
     *
     * @param int $analysisId Analysis ID
     * @param string $reason Flag reason
     * @param int $userId User ID flagging the content
     * @param string|null $notes Additional notes
     * @return FlaggedContent
     */
    public function flagContent(
        int $analysisId,
        string $reason,
        int $userId,
        ?string $notes = null
    ): FlaggedContent {
        $analysis = DisinformationAnalysis::findOrFail($analysisId);

        // Determine priority based on threat score
        $priority = match (true) {
            $analysis->threat_score >= 75 => FlaggedContent::PRIORITY_CRITICAL,
            $analysis->threat_score >= 50 => FlaggedContent::PRIORITY_HIGH,
            $analysis->threat_score >= 25 => FlaggedContent::PRIORITY_MEDIUM,
            default => FlaggedContent::PRIORITY_LOW,
        };

        $flaggedContent = FlaggedContent::create([
            'uuid' => Str::uuid()->toString(),
            'disinformation_analysis_id' => $analysisId,
            'content_type' => $analysis->analyzable_type,
            'content_id' => $analysis->analyzable_id,
            'flag_reason' => $reason,
            'flag_notes' => $notes,
            'flagged_by' => $userId,
            'flagged_at' => now(),
            'resolution_status' => FlaggedContent::STATUS_PENDING,
            'priority' => $priority,
        ]);

        Log::info('Content flagged for disinformation', [
            'flagged_content_id' => $flaggedContent->id,
            'analysis_id' => $analysisId,
            'reason' => $reason,
            'priority' => $priority,
        ]);

        return $flaggedContent;
    }

    /**
     * Get pattern matches for content.
     *
     * @param string $content Content to check
     * @return array Pattern match results
     */
    public function getPatternMatches(string $content): array
    {
        $patterns = DisinformationPattern::active()->get();
        $matches = [];
        $threatContribution = 0;
        $matchedPatternIds = [];

        foreach ($patterns as $pattern) {
            $matchResult = $pattern->matchContent($content);

            if ($matchResult) {
                $matches[] = $matchResult;
                $threatContribution += $matchResult['threat_weight'];
                $matchedPatternIds[$pattern->id] = [
                    'confidence' => $matchResult['confidence'],
                    'matches' => $matchResult['matches'],
                ];

                // Record the match
                $pattern->recordMatch();
            }
        }

        return [
            'matches' => $matches,
            'threat_contribution' => $threatContribution,
            'matched_pattern_ids' => $matchedPatternIds,
            'patterns_checked' => $patterns->count(),
        ];
    }

    /**
     * Get disinformation statistics.
     *
     * @return array Statistics
     */
    public function getStatistics(): array
    {
        $totalAnalyses = DisinformationAnalysis::count();
        $completedAnalyses = DisinformationAnalysis::completed()->count();
        $highThreatAnalyses = DisinformationAnalysis::highThreat()->count();
        $confirmedDisinformation = DisinformationAnalysis::confirmed()->count();

        $pendingFlags = FlaggedContent::pending()->count();
        $totalFlags = FlaggedContent::count();

        // Analysis by type
        $byType = DisinformationAnalysis::selectRaw('analysis_type, COUNT(*) as count')
            ->groupBy('analysis_type')
            ->pluck('count', 'analysis_type')
            ->toArray();

        // Recent high-threat analyses
        $recentHighThreat = DisinformationAnalysis::highThreat()
            ->recent(7)
            ->count();

        // Most matched patterns
        $topPatterns = DisinformationPattern::active()
            ->mostUsed(5)
            ->get(['id', 'name', 'match_count']);

        // Average threat score
        $avgThreatScore = DisinformationAnalysis::completed()
            ->avg('threat_score') ?? 0;

        return [
            'analyses' => [
                'total' => $totalAnalyses,
                'completed' => $completedAnalyses,
                'high_threat' => $highThreatAnalyses,
                'confirmed_disinformation' => $confirmedDisinformation,
                'recent_high_threat_7d' => $recentHighThreat,
            ],
            'flags' => [
                'total' => $totalFlags,
                'pending' => $pendingFlags,
            ],
            'by_type' => $byType,
            'top_patterns' => $topPatterns,
            'average_threat_score' => round($avgThreatScore, 2),
        ];
    }

    /**
     * Get trending disinformation patterns.
     *
     * @param int $days Number of days to look back
     * @param int $limit Maximum number of trends
     * @return array Trending patterns
     */
    public function getTrends(int $days = 7, int $limit = 10): array
    {
        // Get recently matched patterns
        $recentPatterns = DisinformationPattern::recentlyMatched($days)
            ->orderBy('match_count', 'desc')
            ->limit($limit)
            ->get(['id', 'name', 'pattern_type', 'severity', 'match_count', 'last_matched_at']);

        // Get trend in threat scores
        $threatTrend = DisinformationAnalysis::completed()
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, AVG(threat_score) as avg_threat')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->pluck('avg_threat', 'date')
            ->toArray();

        // Get analysis type distribution
        $typeDistribution = DisinformationAnalysis::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('analysis_type, COUNT(*) as count')
            ->groupBy('analysis_type')
            ->pluck('count', 'analysis_type')
            ->toArray();

        // Get most common indicators
        $commonIndicators = $this->getCommonIndicators($days);

        return [
            'period_days' => $days,
            'trending_patterns' => $recentPatterns,
            'threat_trend' => $threatTrend,
            'type_distribution' => $typeDistribution,
            'common_indicators' => $commonIndicators,
        ];
    }

    /**
     * Perform AI-powered analysis using OpenRouter.
     *
     * @param string $text Text to analyze
     * @param string|null $focusType Specific analysis type to focus on
     * @return array|null AI analysis results or null if disabled/failed
     */
    protected function performAiAnalysis(string $text, ?string $focusType = null): ?array
    {
        $apiKey = config('disinformation.openrouter.api_key');

        if (empty($apiKey)) {
            return null;
        }

        $model = config('disinformation.openrouter.model', 'meta-llama/llama-3.2-3b-instruct:free');
        $prompt = $this->buildAiPrompt($text, $focusType);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => 'OsintWeb Disinformation Detection',
                ])
                ->post(self::OPENROUTER_API_URL, [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an expert disinformation analyst. Analyze the provided text for signs of disinformation, propaganda, or manipulation. Respond in JSON format with the following fields: is_disinformation (boolean), confidence (0-100), threat_score (0-100), indicators (array of detected patterns), reasoning (brief explanation).',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'max_tokens' => 500,
                    'temperature' => 0.3,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                $parsed = $this->parseAiResponse($content);

                if ($parsed) {
                    $parsed['model_used'] = $model;
                    $parsed['analyzed_at'] = now()->toIso8601String();
                    return $parsed;
                }
            }

            Log::warning('OpenRouter API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

        } catch (\Exception $e) {
            Log::error('OpenRouter API error', [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Build AI analysis prompt.
     *
     * @param string $text Text to analyze
     * @param string|null $focusType Focus area
     * @return string Prompt
     */
    protected function buildAiPrompt(string $text, ?string $focusType): string
    {
        $truncatedText = Str::limit($text, 2000);

        $focusInstruction = match ($focusType) {
            DisinformationAnalysis::TYPE_COORDINATED_BEHAVIOR => 'Focus on signs of coordinated inauthentic behavior and bot-like patterns.',
            DisinformationAnalysis::TYPE_PROPAGANDA => 'Focus on propaganda techniques, emotional manipulation, and loaded language.',
            DisinformationAnalysis::TYPE_FAKE_ACCOUNT => 'Focus on signs of fake or inauthentic account behavior.',
            DisinformationAnalysis::TYPE_MANIPULATED_MEDIA => 'Focus on signs of manipulated or doctored media descriptions.',
            DisinformationAnalysis::TYPE_FALSE_NARRATIVE => 'Focus on false narratives, conspiracy theories, and misinformation patterns.',
            default => 'Perform a comprehensive analysis for all types of disinformation.',
        };

        return "Analyze the following text for disinformation:\n\n{$truncatedText}\n\n{$focusInstruction}";
    }

    /**
     * Parse AI response JSON.
     *
     * @param string|null $content Response content
     * @return array|null Parsed response
     */
    protected function parseAiResponse(?string $content): ?array
    {
        if (empty($content)) {
            return null;
        }

        // Try to extract JSON from response
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            try {
                $data = json_decode($matches[0], true, 512, JSON_THROW_ON_ERROR);

                return [
                    'is_disinformation' => $data['is_disinformation'] ?? false,
                    'confidence' => min(100, max(0, $data['confidence'] ?? 0)),
                    'threat_score' => min(100, max(0, $data['threat_score'] ?? 0)),
                    'indicators' => $data['indicators'] ?? [],
                    'reasoning' => $data['reasoning'] ?? null,
                ];
            } catch (\JsonException $e) {
                Log::warning('Failed to parse AI response JSON', [
                    'error' => $e->getMessage(),
                    'content' => $content,
                ]);
            }
        }

        return null;
    }

    /**
     * Extract text content from various models.
     *
     * @param Model $content Content model
     * @return string Extracted text
     */
    protected function extractTextContent(Model $content): string
    {
        if ($content instanceof Event) {
            return implode(' ', array_filter([
                $content->title ?? '',
                $content->description ?? '',
                $content->notes ?? '',
            ]));
        }

        if ($content instanceof SocialMediaPost) {
            return $content->content_text ?? '';
        }

        if ($content instanceof Article) {
            return implode(' ', array_filter([
                $content->title ?? '',
                $content->excerpt ?? '',
                $content->content ?? '',
            ]));
        }

        return '';
    }

    /**
     * Analyze posting time patterns.
     *
     * @param Collection $posts Posts to analyze
     * @return array Time pattern analysis
     */
    protected function analyzePostingTimePatterns(Collection $posts): array
    {
        $timestamps = $posts->pluck('posted_at')->filter()->sort()->values();

        if ($timestamps->count() < 2) {
            return ['is_suspicious' => false, 'confidence' => 0];
        }

        // Calculate intervals between posts
        $intervals = [];
        for ($i = 1; $i < $timestamps->count(); $i++) {
            $intervals[] = $timestamps[$i]->diffInSeconds($timestamps[$i - 1]);
        }

        // Check for regular intervals (potential bot behavior)
        $avgInterval = array_sum($intervals) / count($intervals);
        $variance = array_sum(array_map(fn($i) => pow($i - $avgInterval, 2), $intervals)) / count($intervals);
        $stdDev = sqrt($variance);

        // Low standard deviation with short intervals = suspicious
        $isSuspicious = $avgInterval < 60 && $stdDev < 10;
        $confidence = $isSuspicious ? min(80, 100 - ($stdDev * 5)) : 0;

        return [
            'is_suspicious' => $isSuspicious,
            'confidence' => (int) $confidence,
            'avg_interval_seconds' => round($avgInterval, 2),
            'interval_std_dev' => round($stdDev, 2),
            'posts_analyzed' => $timestamps->count(),
        ];
    }

    /**
     * Analyze content similarity across posts.
     *
     * @param Collection $posts Posts to analyze
     * @return array Similarity analysis
     */
    protected function analyzeContentSimilarity(Collection $posts): array
    {
        $contents = $posts->pluck('content_text')->filter();

        if ($contents->count() < 2) {
            return ['has_duplicates' => false, 'similarity_score' => 0];
        }

        $duplicates = 0;
        $totalComparisons = 0;
        $similaritySum = 0;

        $contentArray = $contents->values()->toArray();

        for ($i = 0; $i < count($contentArray); $i++) {
            for ($j = $i + 1; $j < count($contentArray); $j++) {
                $similarity = similar_text(
                    strtolower($contentArray[$i]),
                    strtolower($contentArray[$j]),
                    $percent
                );

                $similaritySum += $percent;
                $totalComparisons++;

                if ($percent > 90) {
                    $duplicates++;
                }
            }
        }

        return [
            'has_duplicates' => $duplicates > 0,
            'duplicate_count' => $duplicates,
            'similarity_score' => $totalComparisons > 0 ? round($similaritySum / $totalComparisons / 100, 2) : 0,
            'comparisons_made' => $totalComparisons,
        ];
    }

    /**
     * Analyze hashtag patterns.
     *
     * @param Collection $posts Posts to analyze
     * @return array Hashtag pattern analysis
     */
    protected function analyzeHashtagPatterns(Collection $posts): array
    {
        $allHashtags = [];

        foreach ($posts as $post) {
            $hashtags = $post->hashtags ?? [];
            foreach ($hashtags as $tag) {
                $tagLower = strtolower($tag);
                $allHashtags[$tagLower] = ($allHashtags[$tagLower] ?? 0) + 1;
            }
        }

        if (empty($allHashtags)) {
            return ['is_suspicious' => false];
        }

        arsort($allHashtags);

        // Check if same hashtags appear in majority of posts
        $postCount = $posts->count();
        $highFrequencyTags = array_filter($allHashtags, fn($count) => $count > $postCount * 0.7);

        return [
            'is_suspicious' => count($highFrequencyTags) > 0,
            'high_frequency_hashtags' => array_keys($highFrequencyTags),
            'hashtag_distribution' => array_slice($allHashtags, 0, 10, true),
        ];
    }

    /**
     * Analyze account network connections.
     *
     * @param Collection $posts Posts to analyze
     * @return array Network analysis
     */
    protected function analyzeAccountNetwork(Collection $posts): array
    {
        $accountIds = $posts->pluck('account_id')->unique();

        if ($accountIds->count() < 2) {
            return ['has_suspicious_connections' => false];
        }

        // Check for accounts created around the same time
        // Check for similar usernames
        // Check for mutual interactions
        // This is a simplified implementation

        return [
            'has_suspicious_connections' => false,
            'accounts_analyzed' => $accountIds->count(),
            'note' => 'Full network analysis requires additional data',
        ];
    }

    /**
     * Analyze content patterns (phrases, hashtags, narratives).
     *
     * @param string $text Text to analyze
     * @return array Content patterns
     */
    protected function analyzeContentPatterns(string $text): array
    {
        $patterns = [];

        // Extract and count repeated phrases (3+ words)
        $words = preg_split('/\s+/', strtolower($text));
        $phrases = [];

        for ($i = 0; $i < count($words) - 2; $i++) {
            $phrase = implode(' ', array_slice($words, $i, 3));
            if (strlen($phrase) > 10) {
                $phrases[$phrase] = ($phrases[$phrase] ?? 0) + 1;
            }
        }

        $repeatedPhrases = array_filter($phrases, fn($count) => $count > 1);
        if (!empty($repeatedPhrases)) {
            arsort($repeatedPhrases);
            $patterns['repeated_phrases'] = array_slice($repeatedPhrases, 0, 5, true);
        }

        // Extract hashtags
        preg_match_all('/#(\w+)/u', $text, $hashtagMatches);
        if (!empty($hashtagMatches[1])) {
            $patterns['hashtags'] = array_unique($hashtagMatches[1]);
        }

        // Extract URLs
        preg_match_all('/https?:\/\/[^\s]+/i', $text, $urlMatches);
        if (!empty($urlMatches[0])) {
            $patterns['urls'] = array_unique($urlMatches[0]);
        }

        // Extract mentions
        preg_match_all('/@(\w+)/u', $text, $mentionMatches);
        if (!empty($mentionMatches[1])) {
            $patterns['mentions'] = array_unique($mentionMatches[1]);
        }

        return $patterns;
    }

    /**
     * Detect loaded/emotional language.
     *
     * @param string $text Lowercase text
     * @return array Detected loaded language
     */
    protected function detectLoadedLanguage(string $text): array
    {
        $loadedTerms = config('disinformation.loaded_language', [
            'shocking', 'outrageous', 'unbelievable', 'must see', 'they don\'t want you to know',
            'mainstream media lies', 'wake up', 'sheep', 'brainwashed', 'truth bomb',
            'explosive', 'bombshell', 'breaking', 'urgent', 'critical',
        ]);

        return $this->findMatchingTerms($text, $loadedTerms, true);
    }

    /**
     * Detect fear appeal language.
     *
     * @param string $text Lowercase text
     * @return array Detected fear indicators
     */
    protected function detectFearAppeal(string $text): array
    {
        $fearTerms = config('disinformation.fear_language', [
            'danger', 'threat', 'warning', 'urgent', 'crisis',
            'catastrophe', 'disaster', 'emergency', 'terrifying',
            'deadly', 'fatal', 'collapse', 'destruction',
        ]);

        return $this->findMatchingTerms($text, $fearTerms);
    }

    /**
     * Detect bandwagon language.
     *
     * @param string $text Lowercase text
     * @return array Detected bandwagon indicators
     */
    protected function detectBandwagonLanguage(string $text): array
    {
        $bandwagonTerms = [
            'everyone knows', 'everybody agrees', 'most people',
            'join millions', 'don\'t be left behind', 'trending',
            'going viral', 'the majority', 'common knowledge',
        ];

        return $this->findMatchingTerms($text, $bandwagonTerms);
    }

    /**
     * Detect black-and-white/binary thinking.
     *
     * @param string $text Lowercase text
     * @return array Detected binary thinking indicators
     */
    protected function detectBinaryThinking(string $text): array
    {
        $binaryTerms = [
            'always', 'never', 'all of them', 'none of them',
            'either...or', 'you\'re either with us or against us',
            'only choice', 'no other option', 'completely',
            'absolutely', 'totally', '100%', 'zero',
        ];

        return $this->findMatchingTerms($text, $binaryTerms);
    }

    /**
     * Detect excessive emphasis (caps, exclamation marks).
     *
     * @param string $text Original text (not lowercase)
     * @return array Emphasis analysis
     */
    protected function detectExcessiveEmphasis(string $text): array
    {
        $words = preg_split('/\s+/', $text);
        $capsWords = 0;
        $totalWords = count($words);

        foreach ($words as $word) {
            if (preg_match('/^[A-Z]{3,}$/', $word)) {
                $capsWords++;
            }
        }

        $capsRatio = $totalWords > 0 ? $capsWords / $totalWords : 0;
        $exclamationCount = substr_count($text, '!');

        return [
            'is_excessive' => $capsRatio > 0.1 || $exclamationCount > 5,
            'caps_word_ratio' => round($capsRatio, 2),
            'exclamation_count' => $exclamationCount,
        ];
    }

    /**
     * Detect whataboutism patterns.
     *
     * @param string $text Lowercase text
     * @return array Detected whataboutism
     */
    protected function detectWhataboutism(string $text): array
    {
        $whataboutPatterns = [
            'what about', 'but what about', 'why don\'t they talk about',
            'you never mention', 'why isn\'t anyone talking about',
            'but they did', 'both sides', 'equally bad',
        ];

        return $this->findMatchingTerms($text, $whataboutPatterns);
    }

    /**
     * Find matching terms in text.
     *
     * @param string $text Text to search
     * @param array $terms Terms to search for
     * @param bool $normalizeTerms Whether to lowercase terms before searching
     * @return array Found terms
     */
    protected function findMatchingTerms(string $text, array $terms, bool $normalizeTerms = false): array
    {
        $found = [];
        foreach ($terms as $term) {
            $checkTerm = $normalizeTerms ? strtolower($term) : $term;
            if (str_contains($text, $checkTerm)) {
                $found[] = $term;
            }
        }

        return $found;
    }

    /**
     * Extract domain from URL or source.
     *
     * @param string $source Source URL or identifier
     * @return string|null Domain name
     */
    protected function extractDomain(string $source): ?string
    {
        if (filter_var($source, FILTER_VALIDATE_URL)) {
            $parsed = parse_url($source);
            return $parsed['host'] ?? null;
        }

        return null;
    }

    /**
     * Check for suspicious domain patterns.
     *
     * @param string $domain Domain to check
     * @return bool Is suspicious
     */
    protected function hasSuspiciousDomainPattern(string $domain): bool
    {
        // Check for typosquatting patterns
        $legitDomains = ['twitter.com', 'facebook.com', 'youtube.com', 'instagram.com'];

        foreach ($legitDomains as $legit) {
            similar_text($domain, $legit, $percent);
            if ($percent > 80 && $percent < 100) {
                return true;
            }
        }

        // Check for excessive hyphens or numbers
        if (substr_count($domain, '-') > 3 || preg_match('/\d{4,}/', $domain)) {
            return true;
        }

        return false;
    }

    /**
     * Get common indicators from recent analyses.
     *
     * @param int $days Days to look back
     * @return array Common indicators
     */
    protected function getCommonIndicators(int $days): array
    {
        $analyses = DisinformationAnalysis::completed()
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('indicators')
            ->get(['indicators']);

        $allIndicators = [];

        foreach ($analyses as $analysis) {
            if (is_array($analysis->indicators)) {
                foreach ($analysis->indicators as $category => $items) {
                    if (!isset($allIndicators[$category])) {
                        $allIndicators[$category] = 0;
                    }
                    $allIndicators[$category]++;
                }
            }
        }

        arsort($allIndicators);

        return array_slice($allIndicators, 0, 10, true);
    }
}
