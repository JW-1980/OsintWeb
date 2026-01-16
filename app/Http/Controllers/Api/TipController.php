<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TipController extends Controller
{
    /**
     * Submit an anonymous tip (public endpoint).
     */
    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'tip_type' => 'required|in:military_movement,equipment_sighting,infrastructure_damage,humanitarian,air_activity,naval_activity,disinformation,war_crime,equipment_identification,strike_verification,other',
            'location_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'date_observed' => 'nullable|date',
            'contact_info' => 'nullable|string|max:255',
            'attachments' => 'nullable|array',
            'attachments.*' => 'string',
            'honeypot' => 'nullable|string|max:0', // Anti-spam honeypot
        ]);

        // Honeypot check
        if (!empty($validated['honeypot'])) {
            return response()->json(['message' => 'Tip submitted successfully'], 201);
        }

        // Rate limiting check
        $recentTips = DB::table('tips')
            ->where('ip_hash', hash('sha256', $request->ip() . config('app.key')))
            ->where('created_at', '>', now()->subHour())
            ->count();

        if ($recentTips >= 5) {
            return response()->json([
                'message' => 'Rate limit exceeded. Please try again later.',
            ], 429);
        }

        $tipId = DB::table('tips')->insertGetId([
            'uuid' => Str::uuid(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'tip_type' => $validated['tip_type'],
            'location_name' => $validated['location_name'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'date_observed' => $validated['date_observed'] ?? null,
            'contact_info' => $validated['contact_info'] ?? null,
            'attachments' => isset($validated['attachments']) ? json_encode($validated['attachments']) : null,
            'priority' => $this->calculatePriority($validated['tip_type']),
            'status' => 'pending_review',
            'source_reliability' => 'unknown',
            'ip_hash' => hash('sha256', $request->ip() . config('app.key')),
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tip = DB::table('tips')->where('id', $tipId)->first();

        return response()->json([
            'message' => 'Tip submitted successfully. Thank you for contributing.',
            'reference_id' => $tip->uuid,
        ], 201);
    }

    /**
     * Get tip submission types.
     */
    public function types(): JsonResponse
    {
        return response()->json([
            'types' => [
                'military_movement' => [
                    'name' => 'Military Movement',
                    'description' => 'Troop or vehicle movements observed',
                    'icon' => 'truck',
                ],
                'equipment_sighting' => [
                    'name' => 'Equipment Sighting',
                    'description' => 'Military equipment spotted',
                    'icon' => 'tank',
                ],
                'infrastructure_damage' => [
                    'name' => 'Infrastructure Damage',
                    'description' => 'Damage to bridges, buildings, utilities',
                    'icon' => 'building',
                ],
                'humanitarian' => [
                    'name' => 'Humanitarian Situation',
                    'description' => 'Civilian impact, displacement, aid needs',
                    'icon' => 'heart',
                ],
                'air_activity' => [
                    'name' => 'Air Activity',
                    'description' => 'Aircraft or drone observations',
                    'icon' => 'plane',
                ],
                'naval_activity' => [
                    'name' => 'Naval Activity',
                    'description' => 'Ship or submarine movements',
                    'icon' => 'ship',
                ],
                'disinformation' => [
                    'name' => 'Disinformation',
                    'description' => 'False information or manipulation',
                    'icon' => 'alert',
                ],
                'war_crime' => [
                    'name' => 'Potential War Crime',
                    'description' => 'Evidence of violations of IHL',
                    'icon' => 'flag',
                ],
                'equipment_identification' => [
                    'name' => 'Equipment Identification',
                    'description' => 'Request help identifying equipment',
                    'icon' => 'search',
                ],
                'strike_verification' => [
                    'name' => 'Strike Verification',
                    'description' => 'Geolocated strike footage or images',
                    'icon' => 'target',
                ],
                'other' => [
                    'name' => 'Other',
                    'description' => 'Other intelligence tip',
                    'icon' => 'info',
                ],
            ],
        ]);
    }

    /**
     * Check tip status (by reference ID).
     */
    public function status(string $uuid): JsonResponse
    {
        $tip = DB::table('tips')
            ->select(['uuid', 'status', 'submitted_at', 'verified_at'])
            ->where('uuid', $uuid)
            ->first();

        if (!$tip) {
            return response()->json(['message' => 'Tip not found'], 404);
        }

        return response()->json([
            'reference_id' => $tip->uuid,
            'status' => $tip->status,
            'submitted_at' => $tip->submitted_at,
            'verified_at' => $tip->verified_at,
            'status_description' => $this->getStatusDescription($tip->status),
        ]);
    }

    /**
     * List tips (authenticated, moderators only).
     */
    public function index(Request $request): JsonResponse
    {
        // Check permission
        if (!auth()->user()->can('moderate_tips')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = DB::table('tips')
            ->orderBy('priority', 'desc')
            ->orderBy('submitted_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('type')) {
            $query->where('tip_type', $request->input('type'));
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        $tips = $query->paginate($request->input('per_page', 25));

        return response()->json([
            'data' => $tips->items(),
            'meta' => [
                'current_page' => $tips->currentPage(),
                'total' => $tips->total(),
                'pending_count' => DB::table('tips')->where('status', 'pending_review')->count(),
            ],
        ]);
    }

    /**
     * Show a specific tip (authenticated, moderators only).
     */
    public function show(string $uuid): JsonResponse
    {
        if (!auth()->user()->can('moderate_tips')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $tip = DB::table('tips')->where('uuid', $uuid)->first();

        if (!$tip) {
            return response()->json(['message' => 'Tip not found'], 404);
        }

        return response()->json(['data' => $tip]);
    }

    /**
     * Update tip status (authenticated, moderators only).
     */
    public function updateStatus(Request $request, string $uuid): JsonResponse
    {
        if (!auth()->user()->can('moderate_tips')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending_review,under_investigation,verified,rejected,archived,escalated',
            'source_reliability' => 'nullable|in:unknown,low,credible,high',
            'moderator_notes' => 'nullable|string',
        ]);

        $updateData = [
            'status' => $validated['status'],
            'updated_at' => now(),
        ];

        if (isset($validated['source_reliability'])) {
            $updateData['source_reliability'] = $validated['source_reliability'];
        }

        if (isset($validated['moderator_notes'])) {
            $updateData['moderator_notes'] = $validated['moderator_notes'];
        }

        if ($validated['status'] === 'verified') {
            $updateData['verified_at'] = now();
            $updateData['verified_by'] = auth()->id();
        }

        DB::table('tips')->where('uuid', $uuid)->update($updateData);

        return response()->json([
            'message' => 'Tip status updated',
            'data' => DB::table('tips')->where('uuid', $uuid)->first(),
        ]);
    }

    /**
     * Convert tip to event (authenticated, moderators only).
     */
    public function convertToEvent(Request $request, string $uuid): JsonResponse
    {
        if (!auth()->user()->can('moderate_tips')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $tip = DB::table('tips')->where('uuid', $uuid)->first();

        if (!$tip) {
            return response()->json(['message' => 'Tip not found'], 404);
        }

        $validated = $request->validate([
            'event_type' => 'required|string',
            'conflict_id' => 'nullable|exists:conflicts,id',
            'additional_data' => 'nullable|array',
        ]);

        // Create event from tip
        $eventId = DB::table('events')->insertGetId([
            'uuid' => Str::uuid(),
            'title' => $tip->title,
            'description' => $tip->description,
            'event_type' => $validated['event_type'],
            'date' => $tip->date_observed ?? now()->toDateString(),
            'latitude' => $tip->latitude,
            'longitude' => $tip->longitude,
            'location_name' => $tip->location_name,
            'conflict_id' => $validated['conflict_id'] ?? null,
            'verified' => true,
            'verification_level' => 'confirmed',
            'sources' => json_encode(['Crowdsourced tip: ' . $tip->uuid]),
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update tip status
        DB::table('tips')->where('uuid', $uuid)->update([
            'status' => 'converted_to_event',
            'converted_event_id' => $eventId,
            'updated_at' => now(),
        ]);

        $event = DB::table('events')->where('id', $eventId)->first();

        return response()->json([
            'message' => 'Tip converted to event',
            'event' => $event,
        ], 201);
    }

    /**
     * Calculate priority based on tip type.
     */
    private function calculatePriority(string $type): string
    {
        $priorities = [
            'war_crime' => 'critical',
            'military_movement' => 'high',
            'strike_verification' => 'high',
            'equipment_sighting' => 'medium',
            'air_activity' => 'medium',
            'naval_activity' => 'medium',
            'humanitarian' => 'high',
            'infrastructure_damage' => 'medium',
            'disinformation' => 'low',
            'equipment_identification' => 'medium',
            'other' => 'low',
        ];

        return $priorities[$type] ?? 'medium';
    }

    /**
     * Get status description.
     */
    private function getStatusDescription(string $status): string
    {
        $descriptions = [
            'pending_review' => 'Your tip is in the queue for review',
            'under_investigation' => 'Analysts are investigating your tip',
            'verified' => 'Your tip has been verified and may be used',
            'rejected' => 'Your tip could not be verified',
            'archived' => 'Your tip has been archived',
            'escalated' => 'Your tip has been escalated for urgent review',
            'converted_to_event' => 'Your tip has been converted to a verified event',
        ];

        return $descriptions[$status] ?? 'Unknown status';
    }
}
