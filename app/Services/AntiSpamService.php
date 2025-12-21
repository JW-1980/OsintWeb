<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CommentRateLimit;
use App\Models\Setting;
use App\Models\SpamPattern;
use Illuminate\Http\Request;

class AntiSpamService
{
    protected Request $request;
    protected ?int $userId;
    protected ?string $ipAddress;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->userId = auth()->id();
        $this->ipAddress = $request->ip();
    }

    /**
     * Check if the request passes anti-spam checks.
     */
    public function check(string $content): array
    {
        $result = [
            'passed' => true,
            'spam_score' => 0.0,
            'flags' => [],
            'errors' => [],
        ];

        // Check honeypot
        if (!$this->checkHoneypot()) {
            $result['passed'] = false;
            $result['errors'][] = 'Bot detected';
            $result['flags'][] = ['type' => 'honeypot_triggered', 'weight' => 10];
            $result['spam_score'] = 10;
            return $result;
        }

        // Check rate limiting
        $rateLimit = $this->checkRateLimit();
        if (!$rateLimit['passed']) {
            $result['passed'] = false;
            $result['errors'][] = $rateLimit['message'];
            return $result;
        }

        // Calculate spam score
        $spamCheck = $this->calculateSpamScore($content);
        $result['spam_score'] = $spamCheck['score'];
        $result['flags'] = $spamCheck['flags'];

        // Check spam threshold
        $threshold = (float) Setting::get('comment_spam_threshold', 5.0);
        if ($result['spam_score'] >= $threshold) {
            $result['passed'] = false;
            $result['errors'][] = 'Comment flagged as potential spam';
        }

        return $result;
    }

    /**
     * Check honeypot field.
     */
    protected function checkHoneypot(): bool
    {
        if (!Setting::get('comment_honeypot_enabled', true)) {
            return true;
        }

        // Check if honeypot field is filled (should be empty)
        $honeypotField = $this->request->input('website_url'); // Common honeypot field name
        if (!empty($honeypotField)) {
            return false;
        }

        // Check timing (too fast = bot)
        $formLoadedAt = $this->request->input('_form_token');
        if ($formLoadedAt) {
            $loadedAt = (int) base64_decode($formLoadedAt);
            $timeTaken = time() - $loadedAt;

            // Less than 3 seconds = likely bot
            if ($timeTaken < 3) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check rate limiting.
     */
    protected function checkRateLimit(): array
    {
        $rateLimit = CommentRateLimit::findOrCreateForRequest(
            $this->userId,
            $this->ipAddress,
            $this->request->input('_fingerprint')
        );

        if ($rateLimit->isRateLimited()) {
            $seconds = $rateLimit->getSecondsUntilAllowed();

            if ($rateLimit->is_blocked) {
                return [
                    'passed' => false,
                    'message' => "You are temporarily blocked. Please try again later.",
                ];
            }

            if ($seconds > 0) {
                return [
                    'passed' => false,
                    'message' => "Please wait {$seconds} seconds before posting another comment.",
                ];
            }

            return [
                'passed' => false,
                'message' => "You have reached the maximum number of comments. Please try again later.",
            ];
        }

        return ['passed' => true];
    }

    /**
     * Calculate spam score for content.
     */
    protected function calculateSpamScore(string $content): array
    {
        $score = 0.0;
        $flags = [];
        $lowerContent = strtolower($content);

        // Check against spam patterns
        $patterns = SpamPattern::active()->get();

        foreach ($patterns as $pattern) {
            if ($pattern->matches($content)) {
                $score += $pattern->weight;
                $flags[] = [
                    'type' => $pattern->type,
                    'pattern' => $pattern->pattern,
                    'weight' => $pattern->weight,
                ];
                $pattern->increment('hit_count');
            }
        }

        // Built-in heuristics

        // All caps (shouting)
        if (strlen($content) > 20) {
            $upperRatio = strlen(preg_replace('/[^A-Z]/', '', $content)) / strlen($content);
            if ($upperRatio > 0.7) {
                $score += 0.5;
                $flags[] = ['type' => 'excessive_caps', 'weight' => 0.5];
            }
        }

        // Excessive links
        $linkCount = preg_match_all('/https?:\/\//', $lowerContent);
        if ($linkCount > 3) {
            $addScore = 0.3 * ($linkCount - 3);
            $score += $addScore;
            $flags[] = ['type' => 'excessive_links', 'count' => $linkCount, 'weight' => $addScore];
        }

        // Excessive punctuation
        $punctCount = preg_match_all('/[!?]{2,}/', $content);
        if ($punctCount > 2) {
            $score += 0.2;
            $flags[] = ['type' => 'excessive_punctuation', 'weight' => 0.2];
        }

        // Very short content
        if (strlen(trim($content)) < 10) {
            $score += 0.2;
            $flags[] = ['type' => 'too_short', 'weight' => 0.2];
        }

        // Repeated characters
        if (preg_match('/(.)\1{5,}/', $content)) {
            $score += 0.3;
            $flags[] = ['type' => 'repeated_characters', 'weight' => 0.3];
        }

        // Common spam phrases
        $spamPhrases = [
            'click here', 'buy now', 'limited offer', 'act now',
            'free money', 'make money fast', 'work from home',
            'casino', 'viagra', 'cryptocurrency investment',
        ];

        foreach ($spamPhrases as $phrase) {
            if (str_contains($lowerContent, $phrase)) {
                $score += 0.5;
                $flags[] = ['type' => 'spam_phrase', 'phrase' => $phrase, 'weight' => 0.5];
            }
        }

        return [
            'score' => min($score, 10.0),
            'flags' => $flags,
        ];
    }

    /**
     * Record successful comment submission.
     */
    public function recordComment(): void
    {
        $rateLimit = CommentRateLimit::findOrCreateForRequest(
            $this->userId,
            $this->ipAddress,
            $this->request->input('_fingerprint')
        );

        $rateLimit->recordComment();
    }

    /**
     * Generate honeypot token.
     */
    public static function generateFormToken(): string
    {
        return base64_encode((string) time());
    }
}
