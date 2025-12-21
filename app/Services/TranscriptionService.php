<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AudioFile;
use App\Models\Transcription;
use App\Models\TranscriptionJob;
use App\Models\TranscriptSegment;
use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TranscriptionService
{
    protected string $openRouterApiKey;
    protected string $openRouterBaseUrl = 'https://openrouter.ai/api/v1';

    public function __construct()
    {
        $this->openRouterApiKey = config('services.openrouter.api_key', '');
    }

    /**
     * Create a manual transcription.
     */
    public function createManualTranscription(
        AudioFile $audioFile,
        User $user,
        ?string $title = null,
        string $language = 'en'
    ): Transcription {
        return Transcription::create([
            'uuid' => Str::uuid(),
            'audio_file_id' => $audioFile->id,
            'user_id' => $user->id,
            'type' => Transcription::TYPE_MANUAL,
            'language' => $language,
            'title' => $title ?? "Transcription of {$audioFile->title}",
            'status' => Transcription::STATUS_DRAFT,
            'is_primary' => !$audioFile->transcriptions()->exists(),
            'transcription_started_at' => now(),
        ]);
    }

    /**
     * Add segment to transcription.
     */
    public function addSegment(
        Transcription $transcription,
        float $startTime,
        float $endTime,
        string $text,
        ?string $speakerId = null,
        ?string $speakerName = null
    ): TranscriptSegment {
        $lastIndex = $transcription->segments()->max('segment_index') ?? -1;

        $segment = $transcription->segments()->create([
            'segment_index' => $lastIndex + 1,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'text' => $text,
            'speaker_id' => $speakerId,
            'speaker_name' => $speakerName,
        ]);

        $transcription->syncFullTextFromSegments();

        return $segment;
    }

    /**
     * Update segment.
     */
    public function updateSegment(
        TranscriptSegment $segment,
        array $data
    ): TranscriptSegment {
        $segment->update($data);
        $segment->transcription->syncFullTextFromSegments();

        return $segment->fresh();
    }

    /**
     * Delete segment and reindex.
     */
    public function deleteSegment(TranscriptSegment $segment): void
    {
        $transcription = $segment->transcription;
        $deletedIndex = $segment->segment_index;

        $segment->delete();

        // Reindex remaining segments
        $transcription->segments()
            ->where('segment_index', '>', $deletedIndex)
            ->decrement('segment_index');

        $transcription->syncFullTextFromSegments();
    }

    /**
     * Queue AI transcription job via OpenRouter.
     */
    public function queueAiTranscription(
        AudioFile $audioFile,
        User $user,
        string $language = 'en',
        ?string $model = null,
        array $options = []
    ): TranscriptionJob {
        $job = TranscriptionJob::create([
            'uuid' => Str::uuid(),
            'audio_file_id' => $audioFile->id,
            'user_id' => $user->id,
            'provider' => TranscriptionJob::PROVIDER_OPENROUTER,
            'model' => $model ?? 'openai/whisper-1',
            'status' => TranscriptionJob::STATUS_QUEUED,
            'language' => $language,
            'options' => $options,
        ]);

        // Dispatch job for async processing
        // In a real implementation, this would be dispatched to a queue
        // dispatch(new ProcessTranscriptionJob($job));

        return $job;
    }

    /**
     * Process AI transcription job.
     */
    public function processAiTranscription(TranscriptionJob $job): Transcription
    {
        $job->start();

        try {
            $audioFile = $job->audioFile;

            // Get audio file content
            $audioPath = Storage::disk('public')->path($audioFile->file_path);

            if (!file_exists($audioPath)) {
                throw new \Exception("Audio file not found: {$audioPath}");
            }

            // Call OpenRouter API for transcription
            $result = $this->callOpenRouterTranscription(
                $audioPath,
                $job->language,
                $job->model,
                $job->options ?? []
            );

            // Create transcription from result
            $transcription = $this->createTranscriptionFromAiResult(
                $audioFile,
                $job->user,
                $result,
                $job
            );

            $job->complete($transcription, $result['cost'] ?? null);

            return $transcription;

        } catch (\Exception $e) {
            Log::error('AI Transcription failed', [
                'job_id' => $job->id,
                'error' => $e->getMessage(),
            ]);

            $job->fail($e->getMessage());
            throw $e;
        }
    }

    /**
     * Call OpenRouter API for transcription.
     */
    protected function callOpenRouterTranscription(
        string $audioPath,
        string $language,
        string $model,
        array $options
    ): array {
        if (empty($this->openRouterApiKey)) {
            throw new \Exception('OpenRouter API key is not configured');
        }

        // Read audio file and encode as base64
        $audioContent = file_get_contents($audioPath);
        $audioBase64 = base64_encode($audioContent);
        $mimeType = mime_content_type($audioPath);

        // OpenRouter uses chat completion with audio support
        // This is a simplified example - actual implementation depends on the model
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openRouterApiKey,
            'HTTP-Referer' => config('app.url'),
            'X-Title' => config('app.name'),
            'Content-Type' => 'application/json',
        ])->timeout(300)->post("{$this->openRouterBaseUrl}/chat/completions", [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "Please transcribe this audio file. Language: {$language}. " .
                                "Provide the transcription with timestamps in the format [MM:SS] text. " .
                                "Identify different speakers if possible.",
                        ],
                        [
                            'type' => 'audio_url',
                            'audio_url' => [
                                'url' => "data:{$mimeType};base64,{$audioBase64}",
                            ],
                        ],
                    ],
                ],
            ],
            'temperature' => 0,
            ...$options,
        ]);

        if (!$response->successful()) {
            throw new RequestException($response);
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';

        return [
            'text' => $content,
            'segments' => $this->parseTranscriptionSegments($content),
            'model' => $model,
            'cost' => $data['usage']['total_cost'] ?? null,
        ];
    }

    /**
     * Parse transcription text into segments.
     */
    protected function parseTranscriptionSegments(string $text): array
    {
        $segments = [];
        $pattern = '/\[(\d{1,2}):(\d{2})(?::(\d{2}))?\]\s*(?:([^:]+):\s*)?(.+?)(?=\[|$)/s';

        if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $index => $match) {
                $hours = isset($match[3]) ? (int) $match[1] : 0;
                $minutes = isset($match[3]) ? (int) $match[2] : (int) $match[1];
                $seconds = isset($match[3]) ? (int) $match[3] : (int) $match[2];

                $startTime = $hours * 3600 + $minutes * 60 + $seconds;
                $endTime = $startTime + 5; // Default segment length

                // Calculate end time from next segment if available
                if (isset($matches[$index + 1])) {
                    $nextMatch = $matches[$index + 1];
                    $nextHours = isset($nextMatch[3]) ? (int) $nextMatch[1] : 0;
                    $nextMinutes = isset($nextMatch[3]) ? (int) $nextMatch[2] : (int) $nextMatch[1];
                    $nextSeconds = isset($nextMatch[3]) ? (int) $nextMatch[3] : (int) $nextMatch[2];
                    $endTime = $nextHours * 3600 + $nextMinutes * 60 + $nextSeconds;
                }

                $speaker = !empty($match[4]) ? trim($match[4]) : null;
                $segmentText = trim($match[5]);

                $segments[] = [
                    'start_time' => (float) $startTime,
                    'end_time' => (float) $endTime,
                    'text' => $segmentText,
                    'speaker_name' => $speaker,
                    'speaker_id' => $speaker ? Str::slug($speaker) : null,
                ];
            }
        }

        // If no timestamps found, create a single segment with the full text
        if (empty($segments)) {
            $segments[] = [
                'start_time' => 0.0,
                'end_time' => 0.0,
                'text' => trim($text),
                'speaker_name' => null,
                'speaker_id' => null,
            ];
        }

        return $segments;
    }

    /**
     * Create transcription from AI result.
     */
    protected function createTranscriptionFromAiResult(
        AudioFile $audioFile,
        User $user,
        array $result,
        TranscriptionJob $job
    ): Transcription {
        $transcription = Transcription::create([
            'uuid' => Str::uuid(),
            'audio_file_id' => $audioFile->id,
            'user_id' => $user->id,
            'type' => Transcription::TYPE_AI,
            'language' => $job->language,
            'title' => "AI Transcription of {$audioFile->title}",
            'full_text' => $result['text'],
            'status' => Transcription::STATUS_COMPLETED,
            'is_primary' => !$audioFile->transcriptions()->exists(),
            'ai_model' => $result['model'],
            'ai_provider' => TranscriptionJob::PROVIDER_OPENROUTER,
            'transcription_started_at' => $job->started_at,
            'transcription_completed_at' => now(),
        ]);

        // Create segments
        foreach ($result['segments'] as $index => $segment) {
            $transcription->segments()->create([
                'segment_index' => $index,
                'start_time' => $segment['start_time'],
                'end_time' => $segment['end_time'],
                'text' => $segment['text'],
                'speaker_id' => $segment['speaker_id'],
                'speaker_name' => $segment['speaker_name'],
            ]);
        }

        $transcription->syncFullTextFromSegments();

        return $transcription;
    }

    /**
     * Export transcription to VTT format.
     */
    public function exportToVtt(Transcription $transcription): string
    {
        $output = "WEBVTT\n\n";

        foreach ($transcription->segments as $segment) {
            $output .= $segment->toVtt() . "\n\n";
        }

        return $output;
    }

    /**
     * Export transcription to SRT format.
     */
    public function exportToSrt(Transcription $transcription): string
    {
        $output = '';

        foreach ($transcription->segments as $index => $segment) {
            $output .= $segment->toSrt($index + 1) . "\n\n";
        }

        return $output;
    }

    /**
     * Export transcription to plain text.
     */
    public function exportToText(Transcription $transcription, bool $includeTimestamps = false): string
    {
        if (!$includeTimestamps) {
            return $transcription->full_text ?? '';
        }

        return $transcription->getFormattedTranscript();
    }

    /**
     * Import transcription from VTT format.
     */
    public function importFromVtt(
        AudioFile $audioFile,
        User $user,
        string $vttContent,
        string $language = 'en'
    ): Transcription {
        $transcription = $this->createManualTranscription($audioFile, $user, null, $language);

        $pattern = '/(\d{2}:\d{2}:\d{2}\.\d{3})\s*-->\s*(\d{2}:\d{2}:\d{2}\.\d{3})\n((?:(?!(\d{2}:\d{2}:\d{2}\.\d{3})).)*)/s';

        if (preg_match_all($pattern, $vttContent, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $index => $match) {
                $startTime = $this->parseVttTime($match[1]);
                $endTime = $this->parseVttTime($match[2]);
                $text = trim($match[3]);

                // Check for speaker tag
                $speaker = null;
                if (preg_match('/<v\s+([^>]+)>/', $text, $speakerMatch)) {
                    $speaker = $speakerMatch[1];
                    $text = preg_replace('/<v\s+[^>]+>/', '', $text);
                }

                $transcription->segments()->create([
                    'segment_index' => $index,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'text' => $text,
                    'speaker_name' => $speaker,
                    'speaker_id' => $speaker ? Str::slug($speaker) : null,
                ]);
            }
        }

        $transcription->syncFullTextFromSegments();
        $transcription->update(['status' => Transcription::STATUS_COMPLETED]);

        return $transcription;
    }

    /**
     * Parse VTT timestamp to seconds.
     */
    protected function parseVttTime(string $time): float
    {
        $parts = explode(':', $time);
        $seconds = 0;

        if (count($parts) === 3) {
            $seconds += (int) $parts[0] * 3600;
            $seconds += (int) $parts[1] * 60;
            $seconds += (float) $parts[2];
        } elseif (count($parts) === 2) {
            $seconds += (int) $parts[0] * 60;
            $seconds += (float) $parts[1];
        }

        return $seconds;
    }

    /**
     * Get available AI models for transcription.
     */
    public function getAvailableModels(): array
    {
        return [
            'openai/whisper-1' => [
                'name' => 'OpenAI Whisper',
                'description' => 'Industry-leading speech recognition',
                'languages' => ['en', 'es', 'fr', 'de', 'it', 'pt', 'ru', 'ja', 'ko', 'zh'],
                'cost_per_minute' => 0.006,
            ],
            'google/gemini-pro-1.5' => [
                'name' => 'Google Gemini Pro 1.5',
                'description' => 'Multimodal model with audio understanding',
                'languages' => ['en', 'es', 'fr', 'de', 'it', 'pt', 'ru', 'ja', 'ko', 'zh'],
                'cost_per_minute' => 0.01,
            ],
        ];
    }
}
