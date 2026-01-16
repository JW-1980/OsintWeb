<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AlertController extends Controller
{
    /**
     * List user's alerts.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DB::table('alerts')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('type')) {
            $query->where('alert_type', $request->input('type'));
        }

        $alerts = $query->paginate($request->input('per_page', 25));

        return response()->json([
            'data' => $alerts->items(),
            'meta' => [
                'current_page' => $alerts->currentPage(),
                'total' => $alerts->total(),
                'per_page' => $alerts->perPage(),
            ],
        ]);
    }

    /**
     * Create a new alert.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'alert_type' => 'required|in:region,keyword,event_type,actor,equipment',
            'conditions' => 'required|array',
            'conditions.keywords' => 'nullable|array',
            'conditions.region' => 'nullable|array',
            'conditions.event_types' => 'nullable|array',
            'conditions.actors' => 'nullable|array',
            'notification_channels' => 'required|array',
            'notification_channels.*' => 'in:email,push,in_app',
            'frequency' => 'required|in:instant,hourly,daily,weekly',
            'is_active' => 'boolean',
        ]);

        $alertId = DB::table('alerts')->insertGetId([
            'uuid' => Str::uuid(),
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'alert_type' => $validated['alert_type'],
            'conditions' => json_encode($validated['conditions']),
            'notification_channels' => json_encode($validated['notification_channels']),
            'frequency' => $validated['frequency'],
            'is_active' => $validated['is_active'] ?? true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $alert = DB::table('alerts')->where('id', $alertId)->first();

        return response()->json([
            'message' => 'Alert created successfully',
            'data' => $alert,
        ], 201);
    }

    /**
     * Show a specific alert.
     */
    public function show(string $uuid): JsonResponse
    {
        $alert = DB::table('alerts')
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->first();

        if (!$alert) {
            return response()->json(['message' => 'Alert not found'], 404);
        }

        // Get triggered history
        $triggers = DB::table('alert_triggers')
            ->where('alert_id', $alert->id)
            ->orderBy('triggered_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $alert,
            'triggers' => $triggers,
        ]);
    }

    /**
     * Update an alert.
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $alert = DB::table('alerts')
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->first();

        if (!$alert) {
            return response()->json(['message' => 'Alert not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'conditions' => 'array',
            'notification_channels' => 'array',
            'notification_channels.*' => 'in:email,push,in_app',
            'frequency' => 'in:instant,hourly,daily,weekly',
            'is_active' => 'boolean',
        ]);

        $updateData = ['updated_at' => now()];

        if (isset($validated['name'])) $updateData['name'] = $validated['name'];
        if (isset($validated['description'])) $updateData['description'] = $validated['description'];
        if (isset($validated['conditions'])) $updateData['conditions'] = json_encode($validated['conditions']);
        if (isset($validated['notification_channels'])) $updateData['notification_channels'] = json_encode($validated['notification_channels']);
        if (isset($validated['frequency'])) $updateData['frequency'] = $validated['frequency'];
        if (isset($validated['is_active'])) $updateData['is_active'] = $validated['is_active'];

        DB::table('alerts')->where('uuid', $uuid)->update($updateData);

        return response()->json([
            'message' => 'Alert updated successfully',
            'data' => DB::table('alerts')->where('uuid', $uuid)->first(),
        ]);
    }

    /**
     * Delete an alert.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $alert = DB::table('alerts')
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->first();

        if (!$alert) {
            return response()->json(['message' => 'Alert not found'], 404);
        }

        DB::table('alerts')->where('uuid', $uuid)->delete();

        return response()->json(['message' => 'Alert deleted successfully']);
    }

    /**
     * Get alert trigger history.
     */
    public function history(string $uuid): JsonResponse
    {
        $alert = DB::table('alerts')
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->first();

        if (!$alert) {
            return response()->json(['message' => 'Alert not found'], 404);
        }

        $triggers = DB::table('alert_triggers')
            ->where('alert_id', $alert->id)
            ->orderBy('triggered_at', 'desc')
            ->paginate(50);

        return response()->json([
            'data' => $triggers->items(),
            'meta' => [
                'current_page' => $triggers->currentPage(),
                'total' => $triggers->total(),
            ],
        ]);
    }

    /**
     * Get user's notifications.
     */
    public function notifications(Request $request): JsonResponse
    {
        $query = DB::table('notifications')
            ->where('notifiable_id', Auth::id())
            ->where('notifiable_type', 'App\\Models\\User')
            ->orderBy('created_at', 'desc');

        if ($request->input('unread_only')) {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate($request->input('per_page', 25));

        return response()->json([
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'total' => $notifications->total(),
                'unread_count' => DB::table('notifications')
                    ->where('notifiable_id', Auth::id())
                    ->whereNull('read_at')
                    ->count(),
            ],
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markRead(string $uuid): JsonResponse
    {
        DB::table('notifications')
            ->where('id', $uuid)
            ->where('notifiable_id', Auth::id())
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(): JsonResponse
    {
        DB::table('notifications')
            ->where('notifiable_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    /**
     * Test alert (send test notification).
     */
    public function test(string $uuid): JsonResponse
    {
        $alert = DB::table('alerts')
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->first();

        if (!$alert) {
            return response()->json(['message' => 'Alert not found'], 404);
        }

        // In a real implementation, this would trigger the notification
        // For now, we just record a test trigger
        DB::table('alert_triggers')->insert([
            'uuid' => Str::uuid(),
            'alert_id' => $alert->id,
            'triggered_at' => now(),
            'trigger_data' => json_encode(['type' => 'test']),
            'notification_sent' => true,
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'Test notification sent']);
    }
}
