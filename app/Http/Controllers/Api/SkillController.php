<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Skill;
use App\Models\SkillTrigger;
use App\Models\UserSkillPreference;
use App\Services\AgentRunnerService;
use App\Services\SkillMatcherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SkillController extends Controller
{
    public function __construct(
        protected SkillMatcherService $skillMatcher,
        protected AgentRunnerService $agentRunner
    ) {}

    /**
     * List all skills.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Skill::active()->byPriority();

        if ($request->has('category')) {
            $query->category($request->category);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $skills = $query->paginate($request->get('per_page', 25));

        return response()->json([
            'data' => $skills->map(fn ($s) => $this->formatSkill($s)),
            'meta' => [
                'current_page' => $skills->currentPage(),
                'last_page' => $skills->lastPage(),
                'total' => $skills->total(),
            ],
        ]);
    }

    /**
     * Get a specific skill.
     */
    public function show(string $slug): JsonResponse
    {
        $skill = Skill::where('slug', $slug)->firstOrFail();

        return response()->json([
            'data' => $this->formatSkill($skill, true),
        ]);
    }

    /**
     * Get skill categories.
     */
    public function categories(): JsonResponse
    {
        $categories = Skill::getCategories();
        $counts = Skill::active()
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');

        return response()->json([
            'data' => collect($categories)->map(fn ($label, $key) => [
                'key' => $key,
                'label' => $label,
                'count' => $counts[$key] ?? 0,
            ])->values(),
        ]);
    }

    /**
     * Match skills against a prompt.
     */
    public function match(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string|min:2|max:5000',
        ]);

        $matches = $this->skillMatcher->findMatchingSkills(
            $request->prompt,
            $request->user()
        );

        return response()->json([
            'data' => [
                'prompt' => $request->prompt,
                'matches' => $matches->map(fn ($m) => [
                    'skill' => $this->formatSkill($m['skill']),
                    'score' => $m['score'],
                    'matched_keywords' => $m['matched_keywords'],
                ])->values(),
                'categories' => $this->skillMatcher->analyzePromptCategories($request->prompt),
            ],
        ]);
    }

    /**
     * Suggest skills based on partial input.
     */
    public function suggest(Request $request): JsonResponse
    {
        $request->validate([
            'input' => 'required|string|min:2|max:100',
        ]);

        $suggestions = $this->skillMatcher->suggestSkills(
            $request->input,
            $request->user(),
            $request->get('limit', 5)
        );

        return response()->json([
            'data' => $suggestions->map(fn ($s) => [
                'skill' => $this->formatSkill($s['skill']),
                'matched_keywords' => $s['matched_keywords'],
            ])->values(),
        ]);
    }

    /**
     * Trigger skills and run with an agent.
     */
    public function trigger(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string|min:2|max:10000',
            'context' => 'nullable|array',
        ]);

        $execution = $this->agentRunner->runWithAutoSkillMatch(
            $request->prompt,
            $request->user(),
            $request->context ?? []
        );

        if (!$execution) {
            return response()->json([
                'message' => 'No matching skills found for your prompt',
                'data' => [
                    'suggestions' => $this->skillMatcher->suggestSkills(
                        substr($request->prompt, 0, 50),
                        $request->user()
                    )->map(fn ($s) => $this->formatSkill($s['skill']))->values(),
                ],
            ], 404);
        }

        return response()->json([
            'data' => [
                'execution_uuid' => $execution->uuid,
                'status' => $execution->status,
                'matched_skills' => $execution->matched_skills,
                'output' => $execution->output,
                'execution_time' => $execution->execution_time,
            ],
        ]);
    }

    /**
     * Create a skill (admin only).
     */
    public function store(Request $request): JsonResponse
    {
        if (!$request->user()?->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'keywords' => 'required|array|min:1',
            'keywords.*' => 'string|max:50',
            'aliases' => 'nullable|array',
            'aliases.*' => 'string|max:100',
            'category' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:50',
            'capabilities' => 'nullable|array',
            'required_inputs' => 'nullable|array',
            'output_format' => 'nullable|array',
            'system_prompt' => 'nullable|string|max:10000',
            'instructions' => 'nullable|string|max:5000',
            'example_prompts' => 'nullable|array',
            'tools' => 'nullable|array',
            'priority' => 'nullable|integer|min:0|max:100',
            'match_threshold' => 'nullable|numeric|min:0|max:1',
            'requires_auth' => 'nullable|boolean',
            'permission_required' => 'nullable|string|max:100',
        ]);

        $skill = Skill::create([
            'uuid' => Str::uuid(),
            ...$validated,
        ]);

        return response()->json([
            'message' => 'Skill created successfully',
            'data' => $this->formatSkill($skill, true),
        ], 201);
    }

    /**
     * Update a skill (admin only).
     */
    public function update(Request $request, string $slug): JsonResponse
    {
        if (!$request->user()?->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $skill = Skill::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string|max:1000',
            'keywords' => 'sometimes|array|min:1',
            'keywords.*' => 'string|max:50',
            'aliases' => 'nullable|array',
            'category' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:50',
            'capabilities' => 'nullable|array',
            'system_prompt' => 'nullable|string|max:10000',
            'instructions' => 'nullable|string|max:5000',
            'priority' => 'nullable|integer|min:0|max:100',
            'match_threshold' => 'nullable|numeric|min:0|max:1',
            'is_active' => 'nullable|boolean',
        ]);

        $skill->update($validated);

        return response()->json([
            'message' => 'Skill updated',
            'data' => $this->formatSkill($skill->fresh(), true),
        ]);
    }

    /**
     * Delete a skill (admin only).
     */
    public function destroy(Request $request, string $slug): JsonResponse
    {
        if (!$request->user()?->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $skill = Skill::where('slug', $slug)->firstOrFail();
        $skill->delete();

        return response()->json(['message' => 'Skill deleted']);
    }

    /**
     * Create an agent from a skill.
     */
    public function createAgent(Request $request, string $slug): JsonResponse
    {
        $skill = Skill::where('slug', $slug)->firstOrFail();

        $agent = $this->agentRunner->createAgentFromSkill($skill, $request->user());

        return response()->json([
            'message' => 'Agent created from skill',
            'data' => [
                'agent_uuid' => $agent->uuid,
                'agent_name' => $agent->name,
                'agent_slug' => $agent->slug,
            ],
        ], 201);
    }

    /**
     * Get user preferences for skills.
     */
    public function preferences(Request $request): JsonResponse
    {
        $user = $request->user();

        $preferences = UserSkillPreference::where('user_id', $user->id)
            ->with('skill:id,uuid,name,slug')
            ->get();

        return response()->json([
            'data' => $preferences->map(fn ($p) => [
                'skill' => [
                    'uuid' => $p->skill->uuid,
                    'name' => $p->skill->name,
                    'slug' => $p->skill->slug,
                ],
                'is_enabled' => $p->is_enabled,
                'priority_override' => $p->priority_override,
                'custom_settings' => $p->custom_settings,
            ]),
        ]);
    }

    /**
     * Update user preference for a skill.
     */
    public function updatePreference(Request $request, string $slug): JsonResponse
    {
        $skill = Skill::where('slug', $slug)->firstOrFail();
        $user = $request->user();

        $validated = $request->validate([
            'is_enabled' => 'nullable|boolean',
            'priority_override' => 'nullable|integer|min:0|max:100',
            'custom_settings' => 'nullable|array',
        ]);

        $preference = UserSkillPreference::updateOrCreate(
            ['user_id' => $user->id, 'skill_id' => $skill->id],
            $validated
        );

        return response()->json([
            'message' => 'Preference updated',
            'data' => $preference,
        ]);
    }

    /**
     * Get skill matching statistics.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'data' => $this->skillMatcher->getMatchingStats(),
        ]);
    }

    /**
     * Get trigger history for a skill.
     */
    public function triggers(Request $request, string $slug): JsonResponse
    {
        $skill = Skill::where('slug', $slug)->firstOrFail();

        $triggers = SkillTrigger::where('skill_id', $skill->id)
            ->orderBy('created_at', 'desc')
            ->limit($request->get('limit', 50))
            ->get();

        return response()->json([
            'data' => $triggers->map(fn ($t) => [
                'matched_keywords' => $t->matched_keywords,
                'match_score' => $t->match_score,
                'was_executed' => $t->was_executed,
                'trigger_source' => $t->trigger_source,
                'created_at' => $t->created_at,
            ]),
        ]);
    }

    protected function formatSkill(Skill $skill, bool $detailed = false): array
    {
        $data = [
            'uuid' => $skill->uuid,
            'name' => $skill->name,
            'slug' => $skill->slug,
            'description' => $skill->description,
            'category' => $skill->category,
            'icon' => $skill->icon,
            'priority' => $skill->priority,
            'usage_count' => $skill->usage_count,
        ];

        if ($detailed) {
            $data['keywords'] = $skill->keywords;
            $data['aliases'] = $skill->aliases;
            $data['capabilities'] = $skill->capabilities;
            $data['required_inputs'] = $skill->required_inputs;
            $data['output_format'] = $skill->output_format;
            $data['instructions'] = $skill->instructions;
            $data['example_prompts'] = $skill->example_prompts;
            $data['tools'] = $skill->tools;
            $data['match_threshold'] = $skill->match_threshold;
            $data['requires_auth'] = $skill->requires_auth;
            $data['created_at'] = $skill->created_at;
        }

        return $data;
    }
}
