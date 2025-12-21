<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Skill;
use App\Models\SkillAgentExecution;
use App\Services\AgentRunnerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AgentController extends Controller
{
    public function __construct(
        protected AgentRunnerService $agentRunner
    ) {}

    /**
     * List all agents.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Agent::active()->with('skills:id,name,slug');

        if ($request->has('type')) {
            $query->type($request->type);
        }

        if ($request->boolean('public_only')) {
            $query->public();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $agents = $query->orderBy('usage_count', 'desc')
            ->paginate($request->get('per_page', 25));

        return response()->json([
            'data' => $agents->map(fn ($a) => $this->formatAgent($a)),
            'meta' => [
                'current_page' => $agents->currentPage(),
                'last_page' => $agents->lastPage(),
                'total' => $agents->total(),
            ],
        ]);
    }

    /**
     * Get a specific agent.
     */
    public function show(string $slug): JsonResponse
    {
        $agent = Agent::where('slug', $slug)
            ->with(['skills', 'creator:id,name'])
            ->firstOrFail();

        return response()->json([
            'data' => $this->formatAgent($agent, true),
        ]);
    }

    /**
     * Create a new agent.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'type' => 'nullable|in:skill-based,workflow,composite,specialist',
            'system_prompt' => 'nullable|string|max:20000',
            'personality' => 'nullable|array',
            'capabilities' => 'nullable|array',
            'limitations' => 'nullable|array',
            'model' => 'nullable|string|max:100',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:100|max:128000',
            'tools' => 'nullable|array',
            'is_public' => 'nullable|boolean',
            'skill_ids' => 'nullable|array',
            'skill_ids.*' => 'integer|exists:skills,id',
        ]);

        $agent = Agent::create([
            'uuid' => Str::uuid(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'] ?? Agent::TYPE_SKILL_BASED,
            'system_prompt' => $validated['system_prompt'] ?? null,
            'personality' => $validated['personality'] ?? null,
            'capabilities' => $validated['capabilities'] ?? null,
            'limitations' => $validated['limitations'] ?? null,
            'model' => $validated['model'] ?? null,
            'temperature' => $validated['temperature'] ?? 0.7,
            'max_tokens' => $validated['max_tokens'] ?? null,
            'tools' => $validated['tools'] ?? null,
            'is_active' => true,
            'is_public' => $validated['is_public'] ?? false,
            'created_by' => $request->user()->id,
        ]);

        // Attach skills
        if (!empty($validated['skill_ids'])) {
            foreach ($validated['skill_ids'] as $index => $skillId) {
                $agent->skills()->attach($skillId, [
                    'proficiency' => 100,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        return response()->json([
            'message' => 'Agent created successfully',
            'data' => $this->formatAgent($agent->load('skills'), true),
        ], 201);
    }

    /**
     * Create an agent from multiple skills.
     */
    public function createFromSkills(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'skill_ids' => 'required|array|min:1',
            'skill_ids.*' => 'integer|exists:skills,id',
        ]);

        $agent = $this->agentRunner->createAgentFromSkills(
            $validated['skill_ids'],
            $validated['name'],
            $validated['description'] ?? null,
            $request->user()
        );

        return response()->json([
            'message' => 'Agent created from skills',
            'data' => $this->formatAgent($agent->load('skills'), true),
        ], 201);
    }

    /**
     * Update an agent.
     */
    public function update(Request $request, string $slug): JsonResponse
    {
        $agent = Agent::where('slug', $slug)->firstOrFail();

        // Check ownership or admin
        if ($agent->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string|max:1000',
            'system_prompt' => 'nullable|string|max:20000',
            'personality' => 'nullable|array',
            'capabilities' => 'nullable|array',
            'limitations' => 'nullable|array',
            'model' => 'nullable|string|max:100',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:100|max:128000',
            'is_active' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
        ]);

        $agent->update($validated);

        return response()->json([
            'message' => 'Agent updated',
            'data' => $this->formatAgent($agent->fresh()->load('skills'), true),
        ]);
    }

    /**
     * Delete an agent.
     */
    public function destroy(Request $request, string $slug): JsonResponse
    {
        $agent = Agent::where('slug', $slug)->firstOrFail();

        if ($agent->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $agent->delete();

        return response()->json(['message' => 'Agent deleted']);
    }

    /**
     * Add a skill to an agent.
     */
    public function addSkill(Request $request, string $slug): JsonResponse
    {
        $agent = Agent::where('slug', $slug)->firstOrFail();

        if ($agent->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'skill_id' => 'required|integer|exists:skills,id',
            'proficiency' => 'nullable|integer|min:0|max:100',
            'is_primary' => 'nullable|boolean',
        ]);

        $skill = Skill::findOrFail($validated['skill_id']);

        $agent->addSkill(
            $skill,
            $validated['proficiency'] ?? 100,
            $validated['is_primary'] ?? false
        );

        return response()->json([
            'message' => 'Skill added to agent',
            'data' => $this->formatAgent($agent->fresh()->load('skills')),
        ]);
    }

    /**
     * Remove a skill from an agent.
     */
    public function removeSkill(Request $request, string $slug, int $skillId): JsonResponse
    {
        $agent = Agent::where('slug', $slug)->firstOrFail();

        if ($agent->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $skill = Skill::findOrFail($skillId);
        $agent->removeSkill($skill);

        return response()->json([
            'message' => 'Skill removed from agent',
        ]);
    }

    /**
     * Run an agent with a prompt.
     */
    public function run(Request $request, string $slug): JsonResponse
    {
        $agent = Agent::where('slug', $slug)->firstOrFail();

        if (!$agent->is_active) {
            return response()->json(['message' => 'Agent is not active'], 400);
        }

        if (!$agent->is_public && $agent->created_by !== $request->user()?->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'prompt' => 'required|string|min:2|max:50000',
            'context' => 'nullable|array',
        ]);

        $execution = $this->agentRunner->run(
            $agent,
            $validated['prompt'],
            $request->user(),
            $validated['context'] ?? []
        );

        return response()->json([
            'data' => $this->formatExecution($execution),
        ]);
    }

    /**
     * Get agent execution history.
     */
    public function executions(Request $request, string $slug): JsonResponse
    {
        $agent = Agent::where('slug', $slug)->firstOrFail();

        $executions = $this->agentRunner->getExecutionHistory(
            $agent,
            $request->get('limit', 20)
        );

        return response()->json([
            'data' => $executions->map(fn ($e) => $this->formatExecution($e)),
        ]);
    }

    /**
     * Get a specific execution.
     */
    public function execution(string $slug, string $executionUuid): JsonResponse
    {
        $agent = Agent::where('slug', $slug)->firstOrFail();

        $execution = SkillAgentExecution::where('uuid', $executionUuid)
            ->where('agent_id', $agent->id)
            ->with('skillTriggers.skill:id,name,slug')
            ->firstOrFail();

        return response()->json([
            'data' => $this->formatExecution($execution, true),
        ]);
    }

    /**
     * Get agent statistics.
     */
    public function stats(string $slug): JsonResponse
    {
        $agent = Agent::where('slug', $slug)->firstOrFail();

        return response()->json([
            'data' => $this->agentRunner->getAgentStats($agent),
        ]);
    }

    /**
     * Get agent types.
     */
    public function types(): JsonResponse
    {
        return response()->json([
            'data' => Agent::getTypes(),
        ]);
    }

    protected function formatAgent(Agent $agent, bool $detailed = false): array
    {
        $data = [
            'uuid' => $agent->uuid,
            'name' => $agent->name,
            'slug' => $agent->slug,
            'description' => $agent->description,
            'type' => $agent->type,
            'model' => $agent->model,
            'is_public' => $agent->is_public,
            'usage_count' => $agent->usage_count,
            'avg_response_time' => $agent->avg_response_time,
            'skills' => $agent->relationLoaded('skills')
                ? $agent->skills->map(fn ($s) => [
                    'uuid' => $s->uuid,
                    'name' => $s->name,
                    'slug' => $s->slug,
                    'is_primary' => $s->pivot->is_primary,
                    'proficiency' => $s->pivot->proficiency,
                ])->values()
                : null,
        ];

        if ($detailed) {
            $data['system_prompt'] = $agent->system_prompt;
            $data['personality'] = $agent->personality;
            $data['capabilities'] = $agent->capabilities;
            $data['limitations'] = $agent->limitations;
            $data['temperature'] = $agent->temperature;
            $data['max_tokens'] = $agent->max_tokens;
            $data['tools'] = $agent->tools;
            $data['memory_config'] = $agent->memory_config;
            $data['creator'] = $agent->relationLoaded('creator') && $agent->creator
                ? ['name' => $agent->creator->name]
                : null;
            $data['created_at'] = $agent->created_at;
        }

        return $data;
    }

    protected function formatExecution(SkillAgentExecution $execution, bool $detailed = false): array
    {
        $data = [
            'uuid' => $execution->uuid,
            'status' => $execution->status,
            'input_prompt' => $detailed ? $execution->input_prompt : Str::limit($execution->input_prompt, 200),
            'matched_skills' => $execution->matched_skills,
            'output' => $detailed ? $execution->output : Str::limit($execution->output ?? '', 500),
            'execution_time' => $execution->execution_time,
            'tokens_used' => $execution->tokens_used,
            'confidence_score' => $execution->confidence_score,
            'created_at' => $execution->created_at,
        ];

        if ($detailed) {
            $data['context'] = $execution->context;
            $data['output_metadata'] = $execution->output_metadata;
            $data['steps'] = $execution->steps;
            $data['error_message'] = $execution->error_message;
            $data['cost'] = $execution->cost;
            $data['started_at'] = $execution->started_at;
            $data['completed_at'] = $execution->completed_at;
            $data['user'] = $execution->relationLoaded('user') && $execution->user
                ? ['name' => $execution->user->name]
                : null;
            $data['skill_triggers'] = $execution->relationLoaded('skillTriggers')
                ? $execution->skillTriggers->map(fn ($t) => [
                    'skill' => $t->skill ? [
                        'name' => $t->skill->name,
                        'slug' => $t->skill->slug,
                    ] : null,
                    'matched_keywords' => $t->matched_keywords,
                    'match_score' => $t->match_score,
                ])
                : null;
        }

        return $data;
    }
}
