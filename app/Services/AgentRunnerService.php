<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Agent;
use App\Models\Skill;
use App\Models\SkillAgentExecution;
use App\Models\SkillTrigger;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AgentRunnerService
{
    protected SkillMatcherService $skillMatcher;
    protected string $openRouterApiKey;
    protected string $openRouterBaseUrl = 'https://openrouter.ai/api/v1';

    public function __construct(SkillMatcherService $skillMatcher)
    {
        $this->skillMatcher = $skillMatcher;
        $this->openRouterApiKey = config('services.openrouter.api_key', '');
    }

    /**
     * Run an agent with the given prompt.
     */
    public function run(
        Agent $agent,
        string $prompt,
        ?User $user = null,
        array $context = []
    ): SkillAgentExecution {
        // Create execution record
        $execution = SkillAgentExecution::create([
            'uuid' => Str::uuid(),
            'agent_id' => $agent->id,
            'user_id' => $user?->id,
            'input_prompt' => $prompt,
            'context' => $context,
            'status' => SkillAgentExecution::STATUS_PENDING,
        ]);

        try {
            // Start execution
            $execution->start();
            $execution->addStep('started', ['agent' => $agent->name]);

            // Find matching skills
            $matchedSkills = $agent->findMatchingSkills($prompt);
            $execution->update([
                'matched_skills' => collect($matchedSkills)->map(fn ($m) => [
                    'skill_id' => $m['skill']->id,
                    'skill_name' => $m['skill']->name,
                    'score' => $m['score'],
                    'keywords' => $m['matched_keywords'],
                ])->toArray(),
            ]);

            $execution->addStep('skills_matched', [
                'count' => count($matchedSkills),
                'skills' => collect($matchedSkills)->pluck('skill.name')->toArray(),
            ]);

            // Log skill triggers
            foreach ($matchedSkills as $match) {
                SkillTrigger::log(
                    $match['skill'],
                    $prompt,
                    $match['matched_keywords'],
                    $match['score'],
                    $execution,
                    $user,
                    true,
                    'agent'
                );

                $match['skill']->recordUsage();
            }

            // Build system prompt
            $systemPrompt = $agent->buildSystemPrompt();
            $execution->addStep('prompt_built');

            // Call AI model
            $result = $this->callAiModel(
                $agent,
                $systemPrompt,
                $prompt,
                $matchedSkills,
                $context
            );

            $execution->addStep('ai_response_received');

            // Complete execution
            $execution->complete(
                $result['content'],
                [
                    'model' => $result['model'],
                    'usage' => $result['usage'] ?? null,
                    'matched_skill_count' => count($matchedSkills),
                ],
                $result['usage']['total_tokens'] ?? null,
                $result['cost'] ?? null,
                $result['confidence'] ?? null
            );

            return $execution;

        } catch (\Exception $e) {
            Log::error('Agent execution failed', [
                'execution_id' => $execution->id,
                'agent' => $agent->name,
                'error' => $e->getMessage(),
            ]);

            $execution->fail($e->getMessage());

            return $execution;
        }
    }

    /**
     * Run agent based on prompt with automatic skill matching.
     */
    public function runWithAutoSkillMatch(
        string $prompt,
        ?User $user = null,
        array $context = []
    ): ?SkillAgentExecution {
        // Find matching skills from global pool
        $matchedSkills = $this->skillMatcher->findMatchingSkills($prompt, $user);

        if ($matchedSkills->isEmpty()) {
            return null;
        }

        // Find or create an agent for these skills
        $agent = $this->findOrCreateAgentForSkills($matchedSkills, $user);

        return $this->run($agent, $prompt, $user, $context);
    }

    /**
     * Create an agent from a skill.
     */
    public function createAgentFromSkill(Skill $skill, ?User $creator = null): Agent
    {
        return Agent::createFromSkill($skill, $creator);
    }

    /**
     * Create an agent from multiple skills.
     */
    public function createAgentFromSkills(
        array $skillIds,
        string $name,
        ?string $description = null,
        ?User $creator = null
    ): Agent {
        $skills = Skill::whereIn('id', $skillIds)->get()->all();

        return Agent::createFromSkills($skills, $name, $description, $creator);
    }

    /**
     * Find or create an agent for matched skills.
     */
    protected function findOrCreateAgentForSkills(Collection $matchedSkills, ?User $user): Agent
    {
        // Get the top skill
        $topSkill = $matchedSkills->first()['skill'];

        // Look for an existing specialist agent for this skill
        $existingAgent = Agent::whereHas('skills', function ($q) use ($topSkill) {
            $q->where('skills.id', $topSkill->id)
                ->where('agent_skill.is_primary', true);
        })->active()->first();

        if ($existingAgent) {
            return $existingAgent;
        }

        // Create a new agent for this skill
        return Agent::createFromSkill($topSkill, $user);
    }

    /**
     * Call the AI model via OpenRouter.
     */
    protected function callAiModel(
        Agent $agent,
        string $systemPrompt,
        string $userPrompt,
        array $matchedSkills,
        array $context
    ): array {
        $model = $agent->model ?? 'openai/gpt-4o-mini';

        // Enhance user prompt with skill context
        $enhancedPrompt = $this->enhancePromptWithSkillContext($userPrompt, $matchedSkills);

        // If no API key, return a simulated response
        if (empty($this->openRouterApiKey)) {
            return $this->simulateResponse($agent, $matchedSkills, $userPrompt);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openRouterApiKey,
            'HTTP-Referer' => config('app.url'),
            'X-Title' => config('app.name'),
            'Content-Type' => 'application/json',
        ])->timeout(120)->post("{$this->openRouterBaseUrl}/chat/completions", [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $enhancedPrompt],
            ],
            'temperature' => $agent->temperature,
            'max_tokens' => $agent->max_tokens ?? 2000,
        ]);

        if (!$response->successful()) {
            throw new \Exception('AI model call failed: ' . $response->body());
        }

        $data = $response->json();

        return [
            'content' => $data['choices'][0]['message']['content'] ?? '',
            'model' => $model,
            'usage' => $data['usage'] ?? null,
            'cost' => $data['usage']['total_cost'] ?? null,
            'confidence' => null,
        ];
    }

    /**
     * Enhance prompt with matched skill context.
     */
    protected function enhancePromptWithSkillContext(string $prompt, array $matchedSkills): string
    {
        if (empty($matchedSkills)) {
            return $prompt;
        }

        $skillContext = "Based on your request, the following skills are relevant:\n";

        foreach ($matchedSkills as $match) {
            $skill = $match['skill'];
            $skillContext .= "- {$skill->name}";
            if (!empty($match['matched_keywords'])) {
                $keywords = implode(', ', array_slice($match['matched_keywords'], 0, 3));
                $skillContext .= " (matched: {$keywords})";
            }
            $skillContext .= "\n";
        }

        $skillContext .= "\nUser request: {$prompt}";

        return $skillContext;
    }

    /**
     * Simulate a response when no API key is available.
     */
    protected function simulateResponse(Agent $agent, array $matchedSkills, string $prompt): array
    {
        $skillNames = collect($matchedSkills)->pluck('skill.name')->implode(', ');

        $content = "# {$agent->name} Response\n\n";
        $content .= "Based on your request, I've identified the following relevant skills: **{$skillNames}**.\n\n";

        foreach ($matchedSkills as $match) {
            $skill = $match['skill'];
            $content .= "## {$skill->name}\n";
            $content .= $skill->description ?? "This skill helps with {$skill->name} tasks.";
            $content .= "\n\n";

            if (!empty($skill->capabilities)) {
                $content .= "**Capabilities:**\n";
                foreach ($skill->capabilities as $cap) {
                    $content .= "- {$cap}\n";
                }
                $content .= "\n";
            }

            if (!empty($skill->instructions)) {
                $content .= "**Instructions:**\n{$skill->instructions}\n\n";
            }
        }

        $content .= "---\n*Note: This is a simulated response. Configure an OpenRouter API key for full AI-powered responses.*";

        return [
            'content' => $content,
            'model' => 'simulated',
            'usage' => ['total_tokens' => 0],
            'cost' => 0,
            'confidence' => 0.5,
        ];
    }

    /**
     * Get execution history for an agent.
     */
    public function getExecutionHistory(Agent $agent, int $limit = 20): Collection
    {
        return SkillAgentExecution::where('agent_id', $agent->id)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get execution statistics for an agent.
     */
    public function getAgentStats(Agent $agent): array
    {
        $executions = SkillAgentExecution::where('agent_id', $agent->id);

        return [
            'total_executions' => $executions->count(),
            'completed' => (clone $executions)->completed()->count(),
            'failed' => (clone $executions)->status(SkillAgentExecution::STATUS_FAILED)->count(),
            'avg_execution_time' => (clone $executions)->completed()->avg('execution_time'),
            'total_tokens' => (clone $executions)->completed()->sum('tokens_used'),
            'total_cost' => (clone $executions)->completed()->sum('cost'),
        ];
    }
}
