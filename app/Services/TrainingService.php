<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TrainingAchievement;
use App\Models\TrainingEnvironment;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * TrainingService
 *
 * Handles all business logic for the Training & Simulation Mode (Sandbox).
 * Manages training environments, sessions, achievements, and sandbox data isolation.
 */
class TrainingService
{
    /**
     * Passing score threshold for certifications (percentage).
     */
    private const CERTIFICATION_PASSING_SCORE = 80.0;

    /**
     * High score threshold (percentage).
     */
    private const HIGH_SCORE_THRESHOLD = 90.0;

    /**
     * Fast completion threshold (percentage of time limit used).
     */
    private const FAST_COMPLETION_THRESHOLD = 50.0;

    /**
     * Create a new training environment.
     *
     * @param array $data Environment data
     * @return TrainingEnvironment
     */
    public function createEnvironment(array $data): TrainingEnvironment
    {
        return DB::transaction(function () use ($data): TrainingEnvironment {
            $environment = TrainingEnvironment::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'environment_type' => $data['environment_type'] ?? TrainingEnvironment::TYPE_SANDBOX,
                'base_scenario' => $data['base_scenario'] ?? null,
                'objectives' => $data['objectives'] ?? null,
                'success_criteria' => $data['success_criteria'] ?? null,
                'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
                'difficulty' => $data['difficulty'] ?? TrainingEnvironment::DIFFICULTY_BEGINNER,
                'skills_covered' => $data['skills_covered'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'is_public' => $data['is_public'] ?? false,
                'created_by' => $data['created_by'] ?? null,
            ]);

            Log::info('Training environment created', [
                'environment_id' => $environment->id,
                'environment_uuid' => $environment->uuid,
                'name' => $environment->name,
                'type' => $environment->environment_type,
            ]);

            return $environment;
        });
    }

    /**
     * Update an existing training environment.
     *
     * @param TrainingEnvironment $environment
     * @param array $data
     * @return TrainingEnvironment
     */
    public function updateEnvironment(TrainingEnvironment $environment, array $data): TrainingEnvironment
    {
        $environment->update($data);

        Log::info('Training environment updated', [
            'environment_id' => $environment->id,
            'environment_uuid' => $environment->uuid,
        ]);

        return $environment->fresh();
    }

    /**
     * Start a new training session.
     *
     * @param int $environmentId
     * @param int $userId
     * @return TrainingSession
     * @throws \InvalidArgumentException
     */
    public function startSession(int $environmentId, int $userId): TrainingSession
    {
        $environment = TrainingEnvironment::findOrFail($environmentId);

        if (!$environment->is_active) {
            throw new \InvalidArgumentException('This training environment is not active.');
        }

        // Check for existing active session in this environment
        $existingSession = TrainingSession::where('environment_id', $environmentId)
            ->where('user_id', $userId)
            ->inProgress()
            ->first();

        if ($existingSession) {
            throw new \InvalidArgumentException('You already have an active session in this environment.');
        }

        return DB::transaction(function () use ($environment, $userId): TrainingSession {
            // Initialize objectives tracking from environment
            $objectivesCompleted = [];
            if ($environment->objectives) {
                foreach ($environment->objectives as $index => $objective) {
                    $objectiveId = $objective['id'] ?? "obj_{$index}";
                    $objectivesCompleted[$objectiveId] = [
                        'completed' => false,
                        'completed_at' => null,
                        'data' => [],
                    ];
                }
            }

            // Calculate max score from objectives
            $maxScore = 100.0;
            if ($environment->success_criteria && isset($environment->success_criteria['max_score'])) {
                $maxScore = (float) $environment->success_criteria['max_score'];
            }

            $session = TrainingSession::create([
                'environment_id' => $environment->id,
                'user_id' => $userId,
                'status' => TrainingSession::STATUS_ACTIVE,
                'started_at' => now(),
                'time_spent_seconds' => 0,
                'score' => 0,
                'max_score' => $maxScore,
                'objectives_completed' => $objectivesCompleted,
                'actions_log' => [],
                'feedback' => null,
                'sandbox_data_prefix' => 'sandbox_' . Str::random(16),
            ]);

            // Log the session start
            $session->logAction('session_started', [
                'environment_name' => $environment->name,
                'environment_type' => $environment->environment_type,
                'difficulty' => $environment->difficulty,
            ]);

            Log::info('Training session started', [
                'session_id' => $session->id,
                'session_uuid' => $session->uuid,
                'environment_id' => $environment->id,
                'user_id' => $userId,
            ]);

            // Award first attempt achievement if this is the user's first session in this environment
            $previousSessions = TrainingSession::where('environment_id', $environment->id)
                ->where('user_id', $userId)
                ->where('id', '!=', $session->id)
                ->count();

            if ($previousSessions === 0) {
                $this->awardAchievement($session, TrainingAchievement::TYPE_FIRST_ATTEMPT, [
                    'environment_name' => $environment->name,
                ]);
            }

            return $session;
        });
    }

    /**
     * Pause a training session.
     *
     * @param int $sessionId
     * @return TrainingSession
     */
    public function pauseSession(int $sessionId): TrainingSession
    {
        $session = TrainingSession::findOrFail($sessionId);

        if ($session->status !== TrainingSession::STATUS_ACTIVE) {
            throw new \InvalidArgumentException('Only active sessions can be paused.');
        }

        $session->pause();
        $session->logAction('session_paused', [
            'time_spent' => $session->time_spent_seconds,
        ]);

        Log::info('Training session paused', [
            'session_id' => $session->id,
            'session_uuid' => $session->uuid,
        ]);

        return $session;
    }

    /**
     * Resume a paused training session.
     *
     * @param int $sessionId
     * @return TrainingSession
     */
    public function resumeSession(int $sessionId): TrainingSession
    {
        $session = TrainingSession::findOrFail($sessionId);

        if ($session->status !== TrainingSession::STATUS_PAUSED) {
            throw new \InvalidArgumentException('Only paused sessions can be resumed.');
        }

        // Check if session has expired while paused
        if ($session->is_expired) {
            $session->markExpired();
            throw new \InvalidArgumentException('This session has expired.');
        }

        $session->resume();
        $session->logAction('session_resumed', [
            'time_spent' => $session->time_spent_seconds,
        ]);

        Log::info('Training session resumed', [
            'session_id' => $session->id,
            'session_uuid' => $session->uuid,
        ]);

        return $session;
    }

    /**
     * Complete a training session and calculate final results.
     *
     * @param int $sessionId
     * @return TrainingSession
     */
    public function completeSession(int $sessionId): TrainingSession
    {
        $session = TrainingSession::with('environment')->findOrFail($sessionId);

        if (!in_array($session->status, [TrainingSession::STATUS_ACTIVE, TrainingSession::STATUS_PAUSED])) {
            throw new \InvalidArgumentException('This session cannot be completed.');
        }

        return DB::transaction(function () use ($session): TrainingSession {
            // Update time spent
            $session->updateTimeSpent();

            // Evaluate objectives
            $this->evaluateObjectives($session->id);

            // Calculate final score
            $this->calculateScore($session->id);

            // Refresh session to get updated data
            $session->refresh();

            // Complete the session
            $session->complete();

            // Generate feedback
            $feedback = $this->generateFeedback($session);
            $session->feedback = $feedback;
            $session->save();

            // Award achievements
            $this->awardAchievements($session->id);

            $session->logAction('session_completed', [
                'final_score' => $session->score,
                'max_score' => $session->max_score,
                'score_percentage' => $session->score_percentage,
                'time_spent' => $session->time_spent_seconds,
            ]);

            Log::info('Training session completed', [
                'session_id' => $session->id,
                'session_uuid' => $session->uuid,
                'score' => $session->score,
                'score_percentage' => $session->score_percentage,
            ]);

            return $session;
        });
    }

    /**
     * Abandon a training session.
     *
     * @param int $sessionId
     * @return TrainingSession
     */
    public function abandonSession(int $sessionId): TrainingSession
    {
        $session = TrainingSession::findOrFail($sessionId);

        if (!in_array($session->status, [TrainingSession::STATUS_ACTIVE, TrainingSession::STATUS_PAUSED])) {
            throw new \InvalidArgumentException('This session cannot be abandoned.');
        }

        $session->abandon();
        $session->logAction('session_abandoned', [
            'time_spent' => $session->time_spent_seconds,
            'score' => $session->score,
        ]);

        Log::info('Training session abandoned', [
            'session_id' => $session->id,
            'session_uuid' => $session->uuid,
        ]);

        // Cleanup sandbox data
        $this->cleanupSandboxData($sessionId);

        return $session;
    }

    /**
     * Log a user action during a session.
     *
     * @param int $sessionId
     * @param string $action
     * @param array $data
     * @return TrainingSession
     */
    public function logAction(int $sessionId, string $action, array $data = []): TrainingSession
    {
        $session = TrainingSession::findOrFail($sessionId);

        if ($session->status !== TrainingSession::STATUS_ACTIVE) {
            throw new \InvalidArgumentException('Actions can only be logged for active sessions.');
        }

        $session->logAction($action, $data);

        return $session;
    }

    /**
     * Evaluate objectives completion for a session.
     *
     * @param int $sessionId
     * @return array Evaluation results
     */
    public function evaluateObjectives(int $sessionId): array
    {
        $session = TrainingSession::with('environment')->findOrFail($sessionId);
        $environment = $session->environment;

        if (!$environment->objectives) {
            return ['completed' => 0, 'total' => 0, 'percentage' => 100];
        }

        $objectivesCompleted = $session->objectives_completed ?? [];
        $results = [];
        $completedCount = 0;
        $totalObjectives = count($environment->objectives);

        foreach ($environment->objectives as $index => $objective) {
            $objectiveId = $objective['id'] ?? "obj_{$index}";
            $isCompleted = $objectivesCompleted[$objectiveId]['completed'] ?? false;

            $results[$objectiveId] = [
                'title' => $objective['title'] ?? "Objective {$index}",
                'description' => $objective['description'] ?? null,
                'completed' => $isCompleted,
                'points' => $objective['points'] ?? 0,
            ];

            if ($isCompleted) {
                $completedCount++;
            }
        }

        $percentage = $totalObjectives > 0 ? ($completedCount / $totalObjectives) * 100 : 0;

        return [
            'completed' => $completedCount,
            'total' => $totalObjectives,
            'percentage' => round($percentage, 2),
            'objectives' => $results,
        ];
    }

    /**
     * Calculate the final score for a session.
     *
     * @param int $sessionId
     * @return float The calculated score
     */
    public function calculateScore(int $sessionId): float
    {
        $session = TrainingSession::with('environment')->findOrFail($sessionId);
        $environment = $session->environment;

        $score = 0.0;
        $maxScore = $session->max_score;

        // Calculate score from completed objectives
        if ($environment->objectives && $session->objectives_completed) {
            foreach ($environment->objectives as $index => $objective) {
                $objectiveId = $objective['id'] ?? "obj_{$index}";
                $objectiveData = $session->objectives_completed[$objectiveId] ?? null;

                if ($objectiveData && ($objectiveData['completed'] ?? false)) {
                    $score += (float) ($objective['points'] ?? 0);
                }
            }
        }

        // Apply time bonus if completed within time limit
        if ($environment->hasTimeLimit()) {
            $timeLimitSeconds = $environment->time_limit_minutes * 60;
            $percentageUsed = ($session->time_spent_seconds / $timeLimitSeconds) * 100;

            if ($percentageUsed <= self::FAST_COMPLETION_THRESHOLD) {
                // Bonus for fast completion (up to 10%)
                $timeBonus = $score * 0.1;
                $score = min($score + $timeBonus, $maxScore);
            }
        }

        // Ensure score doesn't exceed max
        $score = min($score, $maxScore);

        $session->score = $score;
        $session->save();

        return $score;
    }

    /**
     * Award achievements earned during a session.
     *
     * @param int $sessionId
     * @return array<TrainingAchievement> Awarded achievements
     */
    public function awardAchievements(int $sessionId): array
    {
        $session = TrainingSession::with(['environment', 'achievements'])->findOrFail($sessionId);
        $environment = $session->environment;
        $awarded = [];

        // Check for existing achievements to avoid duplicates
        $existingTypes = $session->achievements->pluck('achievement_type')->toArray();

        // Completed exercise achievement
        if (
            $session->status === TrainingSession::STATUS_COMPLETED
            && !in_array(TrainingAchievement::TYPE_COMPLETED_EXERCISE, $existingTypes)
        ) {
            $awarded[] = $this->awardAchievement($session, TrainingAchievement::TYPE_COMPLETED_EXERCISE, [
                'environment_name' => $environment->name,
                'score' => $session->score,
                'time_spent' => $session->time_spent_seconds,
            ]);
        }

        // Perfect score achievement
        if (
            $session->score_percentage >= 100
            && !in_array(TrainingAchievement::TYPE_PERFECT_SCORE, $existingTypes)
        ) {
            $awarded[] = $this->awardAchievement($session, TrainingAchievement::TYPE_PERFECT_SCORE, [
                'score' => $session->score,
                'max_score' => $session->max_score,
            ]);
        }

        // High score achievement
        if (
            $session->score_percentage >= self::HIGH_SCORE_THRESHOLD
            && !in_array(TrainingAchievement::TYPE_HIGH_SCORE, $existingTypes)
        ) {
            $awarded[] = $this->awardAchievement($session, TrainingAchievement::TYPE_HIGH_SCORE, [
                'score_percentage' => $session->score_percentage,
            ]);
        }

        // Fast completion achievement
        if ($environment->hasTimeLimit()) {
            $timeLimitSeconds = $environment->time_limit_minutes * 60;
            $percentageUsed = ($session->time_spent_seconds / $timeLimitSeconds) * 100;

            if (
                $percentageUsed <= self::FAST_COMPLETION_THRESHOLD
                && !in_array(TrainingAchievement::TYPE_FAST_COMPLETION, $existingTypes)
            ) {
                $awarded[] = $this->awardAchievement($session, TrainingAchievement::TYPE_FAST_COMPLETION, [
                    'time_used_percentage' => round($percentageUsed, 2),
                    'time_spent_seconds' => $session->time_spent_seconds,
                ]);
            }
        }

        // All objectives completed achievement
        $objectivesEval = $this->evaluateObjectives($sessionId);
        if (
            $objectivesEval['total'] > 0
            && $objectivesEval['completed'] === $objectivesEval['total']
            && !in_array(TrainingAchievement::TYPE_ALL_OBJECTIVES, $existingTypes)
        ) {
            $awarded[] = $this->awardAchievement($session, TrainingAchievement::TYPE_ALL_OBJECTIVES, [
                'objectives_count' => $objectivesEval['total'],
            ]);
        }

        // Certification passed achievement
        if (
            $environment->isCertification()
            && $session->score_percentage >= self::CERTIFICATION_PASSING_SCORE
            && !in_array(TrainingAchievement::TYPE_PASSED_CERTIFICATION, $existingTypes)
        ) {
            $awarded[] = $this->awardAchievement($session, TrainingAchievement::TYPE_PASSED_CERTIFICATION, [
                'certification_name' => $environment->name,
                'score' => $session->score,
                'passing_score' => self::CERTIFICATION_PASSING_SCORE,
            ]);
        }

        // Expert level achievement
        if (
            $environment->difficulty === TrainingEnvironment::DIFFICULTY_EXPERT
            && $session->status === TrainingSession::STATUS_COMPLETED
            && $session->score_percentage >= 80
            && !in_array(TrainingAchievement::TYPE_EXPERT_LEVEL, $existingTypes)
        ) {
            $awarded[] = $this->awardAchievement($session, TrainingAchievement::TYPE_EXPERT_LEVEL, [
                'environment_name' => $environment->name,
                'difficulty' => $environment->difficulty,
            ]);
        }

        // No hints used achievement (check action log)
        $actionsLog = $session->actions_log ?? [];
        $hintsUsed = array_filter($actionsLog, fn($a) => ($a['action'] ?? '') === 'hint_requested');

        if (
            empty($hintsUsed)
            && $session->status === TrainingSession::STATUS_COMPLETED
            && !in_array(TrainingAchievement::TYPE_NO_HINTS_USED, $existingTypes)
        ) {
            $awarded[] = $this->awardAchievement($session, TrainingAchievement::TYPE_NO_HINTS_USED, [
                'environment_name' => $environment->name,
            ]);
        }

        return array_filter($awarded);
    }

    /**
     * Award a single achievement to a session.
     *
     * @param TrainingSession $session
     * @param string $type
     * @param array $details
     * @return TrainingAchievement|null
     */
    private function awardAchievement(TrainingSession $session, string $type, array $details = []): ?TrainingAchievement
    {
        try {
            $achievement = TrainingAchievement::award($session, $type, $details);

            Log::info('Training achievement awarded', [
                'session_id' => $session->id,
                'achievement_type' => $type,
                'user_id' => $session->user_id,
            ]);

            return $achievement;
        } catch (\Exception $e) {
            Log::error('Failed to award training achievement', [
                'session_id' => $session->id,
                'achievement_type' => $type,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get the sandbox data prefix for a session.
     *
     * @param int $sessionId
     * @return string
     */
    public function getSandboxPrefix(int $sessionId): string
    {
        $session = TrainingSession::findOrFail($sessionId);
        return $session->sandbox_data_prefix;
    }

    /**
     * Cleanup sandbox data for a session.
     *
     * @param int $sessionId
     * @return bool
     */
    public function cleanupSandboxData(int $sessionId): bool
    {
        $session = TrainingSession::findOrFail($sessionId);
        $prefix = $session->sandbox_data_prefix;

        try {
            // Clean up any sandbox-prefixed data
            // This would clean up sandbox-specific records from various tables
            // Implementation depends on how sandbox data is stored

            // Clean up any sandbox files
            $sandboxPath = "sandbox/{$prefix}";
            if (Storage::exists($sandboxPath)) {
                Storage::deleteDirectory($sandboxPath);
            }

            Log::info('Sandbox data cleaned up', [
                'session_id' => $session->id,
                'prefix' => $prefix,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to cleanup sandbox data', [
                'session_id' => $session->id,
                'prefix' => $prefix,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate a certificate for a passed certification session.
     *
     * @param int $sessionId
     * @return array|null Certificate data or null if not eligible
     */
    public function generateCertificate(int $sessionId): ?array
    {
        $session = TrainingSession::with(['environment', 'user'])->findOrFail($sessionId);

        // Verify this is a completed certification with passing score
        if (!$session->environment->isCertification()) {
            return null;
        }

        if ($session->status !== TrainingSession::STATUS_COMPLETED) {
            return null;
        }

        if ($session->score_percentage < self::CERTIFICATION_PASSING_SCORE) {
            return null;
        }

        $certificateId = Str::upper(Str::random(12));
        $issuedAt = now();

        $certificate = [
            'certificate_id' => $certificateId,
            'certification_name' => $session->environment->name,
            'recipient_name' => $session->user->name,
            'recipient_email' => $session->user->email,
            'score' => $session->score,
            'score_percentage' => $session->score_percentage,
            'issued_at' => $issuedAt->toIso8601String(),
            'valid_until' => $issuedAt->addYear()->toIso8601String(),
            'session_uuid' => $session->uuid,
            'environment_uuid' => $session->environment->uuid,
            'difficulty' => $session->environment->difficulty,
            'time_spent_formatted' => $session->formatted_time_spent,
            'verification_url' => url("/api/training/certificates/{$certificateId}/verify"),
        ];

        // Store certificate reference in session feedback
        $feedback = $session->feedback ?? [];
        $feedback['certificate'] = $certificate;
        $session->feedback = $feedback;
        $session->save();

        Log::info('Certificate generated', [
            'certificate_id' => $certificateId,
            'session_id' => $session->id,
            'user_id' => $session->user_id,
        ]);

        return $certificate;
    }

    /**
     * Reset a session to start fresh with the same environment.
     *
     * @param int $sessionId
     * @return TrainingSession New session
     */
    public function resetSession(int $sessionId): TrainingSession
    {
        $oldSession = TrainingSession::with('environment')->findOrFail($sessionId);

        // Abandon the old session if still in progress
        if (in_array($oldSession->status, [TrainingSession::STATUS_ACTIVE, TrainingSession::STATUS_PAUSED])) {
            $this->abandonSession($sessionId);
        }

        // Start a new session in the same environment
        return $this->startSession($oldSession->environment_id, $oldSession->user_id);
    }

    /**
     * Generate feedback for a completed session.
     *
     * @param TrainingSession $session
     * @return array
     */
    private function generateFeedback(TrainingSession $session): array
    {
        $environment = $session->environment;
        $objectivesEval = $this->evaluateObjectives($session->id);

        $feedback = [
            'summary' => [
                'score' => $session->score,
                'max_score' => $session->max_score,
                'score_percentage' => $session->score_percentage,
                'time_spent_seconds' => $session->time_spent_seconds,
                'time_spent_formatted' => $session->formatted_time_spent,
                'objectives_completed' => $objectivesEval['completed'],
                'objectives_total' => $objectivesEval['total'],
            ],
            'objectives' => $objectivesEval['objectives'] ?? [],
            'strengths' => [],
            'areas_for_improvement' => [],
            'recommendations' => [],
        ];

        // Analyze performance
        if ($session->score_percentage >= 90) {
            $feedback['strengths'][] = 'Excellent overall performance';
        } elseif ($session->score_percentage >= 70) {
            $feedback['strengths'][] = 'Good understanding of the material';
        }

        if ($objectivesEval['percentage'] === 100) {
            $feedback['strengths'][] = 'Completed all objectives';
        } elseif ($objectivesEval['percentage'] < 50) {
            $feedback['areas_for_improvement'][] = 'Focus on completing more objectives';
        }

        if ($environment->hasTimeLimit()) {
            $timeLimitSeconds = $environment->time_limit_minutes * 60;
            $percentageUsed = ($session->time_spent_seconds / $timeLimitSeconds) * 100;

            if ($percentageUsed <= 50) {
                $feedback['strengths'][] = 'Excellent time management';
            } elseif ($percentageUsed >= 90) {
                $feedback['areas_for_improvement'][] = 'Work on improving speed and efficiency';
            }
        }

        // Add recommendations based on difficulty
        if ($session->score_percentage >= 80 && $environment->difficulty !== TrainingEnvironment::DIFFICULTY_EXPERT) {
            $nextDifficulty = $this->getNextDifficulty($environment->difficulty);
            $feedback['recommendations'][] = "Consider trying a {$nextDifficulty} level exercise";
        }

        if ($session->score_percentage < 60) {
            $feedback['recommendations'][] = 'Review the training material and try again';
            $feedback['recommendations'][] = 'Consider starting with a lower difficulty level';
        }

        return $feedback;
    }

    /**
     * Get the next difficulty level.
     *
     * @param string $currentDifficulty
     * @return string
     */
    private function getNextDifficulty(string $currentDifficulty): string
    {
        return match ($currentDifficulty) {
            TrainingEnvironment::DIFFICULTY_BEGINNER => TrainingEnvironment::DIFFICULTY_INTERMEDIATE,
            TrainingEnvironment::DIFFICULTY_INTERMEDIATE => TrainingEnvironment::DIFFICULTY_ADVANCED,
            TrainingEnvironment::DIFFICULTY_ADVANCED => TrainingEnvironment::DIFFICULTY_EXPERT,
            default => TrainingEnvironment::DIFFICULTY_EXPERT,
        };
    }

    /**
     * Get leaderboard for an environment.
     *
     * @param int $environmentId
     * @param int $limit
     * @return \Illuminate\Support\Collection<int, array>
     */
    public function getLeaderboard(int $environmentId, int $limit = 10): \Illuminate\Support\Collection
    {
        return TrainingSession::with('user')
            ->where('environment_id', $environmentId)
            ->where('status', TrainingSession::STATUS_COMPLETED)
            ->orderByDesc('score')
            ->orderBy('time_spent_seconds')
            ->limit($limit)
            ->get()
            ->map(function ($session, $index) {
                return [
                    'rank' => $index + 1,
                    'user' => [
                        'uuid' => $session->user->uuid,
                        'name' => $session->user->name,
                    ],
                    'score' => $session->score,
                    'score_percentage' => $session->score_percentage,
                    'time_spent_seconds' => $session->time_spent_seconds,
                    'time_spent_formatted' => $session->formatted_time_spent,
                    'completed_at' => $session->completed_at?->toIso8601String(),
                ];
            });
    }

    /**
     * Get user's training statistics.
     *
     * @param int $userId
     * @return array
     */
    public function getUserStats(int $userId): array
    {
        $sessions = TrainingSession::where('user_id', $userId)->get();
        $completedSessions = $sessions->where('status', TrainingSession::STATUS_COMPLETED);
        $achievements = TrainingAchievement::forUser($userId)->get();

        return [
            'total_sessions' => $sessions->count(),
            'completed_sessions' => $completedSessions->count(),
            'abandoned_sessions' => $sessions->where('status', TrainingSession::STATUS_ABANDONED)->count(),
            'active_sessions' => $sessions->where('status', TrainingSession::STATUS_ACTIVE)->count(),
            'average_score' => round($completedSessions->avg('score') ?? 0, 2),
            'average_score_percentage' => round($completedSessions->avg('score_percentage') ?? 0, 2),
            'total_time_spent_seconds' => $sessions->sum('time_spent_seconds'),
            'achievements_count' => $achievements->count(),
            'certifications_passed' => $achievements->where('achievement_type', TrainingAchievement::TYPE_PASSED_CERTIFICATION)->count(),
            'perfect_scores' => $achievements->where('achievement_type', TrainingAchievement::TYPE_PERFECT_SCORE)->count(),
        ];
    }

    /**
     * Check if a session has expired and update status if needed.
     *
     * @param int $sessionId
     * @return bool
     */
    public function checkAndExpireSession(int $sessionId): bool
    {
        $session = TrainingSession::with('environment')->findOrFail($sessionId);

        if (!in_array($session->status, [TrainingSession::STATUS_ACTIVE, TrainingSession::STATUS_PAUSED])) {
            return false;
        }

        if ($session->is_expired) {
            $session->markExpired();
            $session->logAction('session_expired', [
                'time_spent' => $session->time_spent_seconds,
                'time_limit' => $session->environment->time_limit_minutes * 60,
            ]);

            Log::info('Training session expired', [
                'session_id' => $session->id,
                'session_uuid' => $session->uuid,
            ]);

            return true;
        }

        return false;
    }
}
