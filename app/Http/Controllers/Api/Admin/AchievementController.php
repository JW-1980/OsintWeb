<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class AchievementController extends Controller
{
    /**
     * List all achievements.
     */
    public function index(): JsonResponse
    {
        return response()->json(Achievement::all());
    }

    /**
     * Store a new achievement.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:achievements',
            'description' => 'nullable|string',
            'icon' => 'required|string|max:50',
            'color' => 'required|string|max:20',
            'points' => 'required|integer|min:0',
            'threshold' => 'required|integer|min:1',
            'type' => 'required|string|max:50',
            'is_secret' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $achievement = Achievement::create($validated);

        return response()->json($achievement, 201);
    }

    /**
     * Show a specific achievement.
     */
    public function show(Achievement $achievement): JsonResponse
    {
        return response()->json($achievement);
    }

    /**
     * Update an achievement.
     */
    public function update(Request $request, Achievement $achievement): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('achievements')->ignore($achievement->id)],
            'description' => 'nullable|string',
            'icon' => 'sometimes|string|max:50',
            'color' => 'sometimes|string|max:20',
            'points' => 'sometimes|integer|min:0',
            'threshold' => 'sometimes|integer|min:1',
            'type' => 'sometimes|string|max:50',
            'is_secret' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $achievement->update($validated);

        return response()->json($achievement);
    }

    /**
     * Delete an achievement.
     */
    public function destroy(Achievement $achievement): JsonResponse
    {
        $achievement->delete();
        return response()->json(null, 204);
    }
}
