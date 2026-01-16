<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SourceVerificationController extends Controller
{
    /**
     * Reliability ratings for sources.
     */
    private const RELIABILITY_RATINGS = [
        'A' => 'Completely reliable',
        'B' => 'Usually reliable',
        'C' => 'Fairly reliable',
        'D' => 'Not usually reliable',
        'E' => 'Unreliable',
        'F' => 'Reliability cannot be judged',
    ];

    /**
     * Information credibility ratings.
     */
    private const CREDIBILITY_RATINGS = [
        '1' => 'Confirmed by other sources',
        '2' => 'Probably true',
        '3' => 'Possibly true',
        '4' => 'Doubtful',
        '5' => 'Improbable',
        '6' => 'Truth cannot be judged',
    ];

    /**
     * Get known trusted sources.
     */
    public function trustedSources(): JsonResponse
    {
        $sources = DB::table('verified_sources')
            ->where('is_trusted', true)
            ->orderBy('reliability_rating', 'asc')
            ->get();

        return response()->json([
            'data' => $sources,
            'reliability_ratings' => self::RELIABILITY_RATINGS,
        ]);
    }

    /**
     * Check a source's reliability.
     */
    public function checkSource(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required_without:name|url',
            'name' => 'required_without:url|string',
        ]);

        // Check if source exists in database
        $source = null;
        if (isset($validated['url'])) {
            $domain = parse_url($validated['url'], PHP_URL_HOST);
            $source = DB::table('verified_sources')
                ->where('domain', $domain)
                ->orWhere('url', $validated['url'])
                ->first();
        } elseif (isset($validated['name'])) {
            $source = DB::table('verified_sources')
                ->where('name', 'like', '%' . $validated['name'] . '%')
                ->first();
        }

        if ($source) {
            return response()->json([
                'found' => true,
                'source' => $source,
                'reliability_description' => self::RELIABILITY_RATINGS[$source->reliability_rating] ?? 'Unknown',
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'Source not in database. Consider submitting for review.',
            'reliability_ratings' => self::RELIABILITY_RATINGS,
        ]);
    }

    /**
     * Submit a source for review.
     */
    public function submitSource(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'type' => 'required|in:news_agency,government,ngo,social_media,think_tank,military,academic,other',
            'country' => 'nullable|string|max:2',
            'description' => 'nullable|string',
            'evidence' => 'nullable|array',
        ]);

        $domain = parse_url($validated['url'], PHP_URL_HOST);

        // Check if already submitted
        $existing = DB::table('source_submissions')
            ->where('domain', $domain)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'This source has already been submitted for review',
                'submission' => $existing,
            ], 409);
        }

        $submissionId = DB::table('source_submissions')->insertGetId([
            'uuid' => Str::uuid(),
            'name' => $validated['name'],
            'url' => $validated['url'],
            'domain' => $domain,
            'type' => $validated['type'],
            'country' => $validated['country'] ?? null,
            'description' => $validated['description'] ?? null,
            'evidence' => json_encode($validated['evidence'] ?? []),
            'status' => 'pending',
            'submitted_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Source submitted for review',
            'submission_id' => $submissionId,
        ], 201);
    }

    /**
     * Grade information based on NATO Admiralty System.
     */
    public function gradeInformation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source_reliability' => 'required|in:A,B,C,D,E,F',
            'information_credibility' => 'required|in:1,2,3,4,5,6',
            'content' => 'nullable|string',
            'corroborating_sources' => 'nullable|integer|min:0',
        ]);

        $grade = $validated['source_reliability'] . $validated['information_credibility'];

        $confidence = $this->calculateConfidence(
            $validated['source_reliability'],
            $validated['information_credibility'],
            $validated['corroborating_sources'] ?? 0
        );

        return response()->json([
            'grade' => $grade,
            'source_reliability' => [
                'rating' => $validated['source_reliability'],
                'description' => self::RELIABILITY_RATINGS[$validated['source_reliability']],
            ],
            'information_credibility' => [
                'rating' => $validated['information_credibility'],
                'description' => self::CREDIBILITY_RATINGS[$validated['information_credibility']],
            ],
            'confidence_score' => $confidence,
            'recommendation' => $this->getRecommendation($grade, $confidence),
        ]);
    }

    /**
     * Cross-reference information against multiple sources.
     */
    public function crossReference(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'claim' => 'required|string',
            'sources' => 'required|array|min:1',
            'sources.*.url' => 'required|url',
            'sources.*.excerpt' => 'nullable|string',
            'sources.*.date' => 'nullable|date',
        ]);

        $sourceAnalysis = [];
        $confirmedCount = 0;
        $contradictedCount = 0;

        foreach ($validated['sources'] as $source) {
            $domain = parse_url($source['url'], PHP_URL_HOST);

            // Check source reliability
            $knownSource = DB::table('verified_sources')
                ->where('domain', $domain)
                ->first();

            $analysis = [
                'url' => $source['url'],
                'domain' => $domain,
                'known_source' => $knownSource ? true : false,
                'reliability' => $knownSource->reliability_rating ?? 'F',
                'status' => 'supporting', // In real impl, would use NLP to determine
            ];

            if ($analysis['reliability'] <= 'C') {
                $confirmedCount++;
            }

            $sourceAnalysis[] = $analysis;
        }

        $overallConfidence = $this->calculateCrossReferenceConfidence(
            count($validated['sources']),
            $confirmedCount
        );

        return response()->json([
            'claim' => $validated['claim'],
            'sources_analyzed' => count($validated['sources']),
            'source_analysis' => $sourceAnalysis,
            'confirmed_by' => $confirmedCount,
            'overall_confidence' => $overallConfidence,
            'assessment' => $this->getCrossReferenceAssessment($overallConfidence),
        ]);
    }

    /**
     * Get source type categories.
     */
    public function sourceTypes(): JsonResponse
    {
        return response()->json([
            'types' => [
                'news_agency' => 'News Agency (AP, Reuters, AFP)',
                'government' => 'Government/Official Source',
                'ngo' => 'NGO/Humanitarian Organization',
                'social_media' => 'Social Media Account',
                'think_tank' => 'Think Tank/Research Institute',
                'military' => 'Military/Defense Source',
                'academic' => 'Academic/University',
                'other' => 'Other',
            ],
            'reliability_ratings' => self::RELIABILITY_RATINGS,
            'credibility_ratings' => self::CREDIBILITY_RATINGS,
        ]);
    }

    /**
     * Calculate confidence score.
     */
    private function calculateConfidence(string $reliability, string $credibility, int $corroborating): float
    {
        $reliabilityScores = ['A' => 1.0, 'B' => 0.8, 'C' => 0.6, 'D' => 0.4, 'E' => 0.2, 'F' => 0.3];
        $credibilityScores = ['1' => 1.0, '2' => 0.8, '3' => 0.6, '4' => 0.4, '5' => 0.2, '6' => 0.3];

        $baseScore = ($reliabilityScores[$reliability] + $credibilityScores[$credibility]) / 2;

        // Boost for corroborating sources
        $boost = min($corroborating * 0.1, 0.3);

        return round(min($baseScore + $boost, 1.0), 2);
    }

    /**
     * Get recommendation based on grade.
     */
    private function getRecommendation(string $grade, float $confidence): string
    {
        if ($confidence >= 0.8) {
            return 'High confidence - Can be used with minimal additional verification';
        } elseif ($confidence >= 0.6) {
            return 'Moderate confidence - Recommend additional source verification';
        } elseif ($confidence >= 0.4) {
            return 'Low confidence - Requires multiple independent sources for verification';
        } else {
            return 'Very low confidence - Do not use without extensive verification';
        }
    }

    /**
     * Calculate cross-reference confidence.
     */
    private function calculateCrossReferenceConfidence(int $totalSources, int $confirmedCount): float
    {
        if ($totalSources === 0) {
            return 0;
        }

        $ratio = $confirmedCount / $totalSources;
        $sourceBonus = min($totalSources * 0.05, 0.2);

        return round(min($ratio + $sourceBonus, 1.0), 2);
    }

    /**
     * Get cross-reference assessment.
     */
    private function getCrossReferenceAssessment(float $confidence): string
    {
        if ($confidence >= 0.8) {
            return 'Well corroborated - Multiple reliable sources confirm';
        } elseif ($confidence >= 0.6) {
            return 'Moderately corroborated - Some reliable sources confirm';
        } elseif ($confidence >= 0.4) {
            return 'Weakly corroborated - Limited reliable confirmation';
        } else {
            return 'Poorly corroborated - Insufficient reliable sources';
        }
    }
}
