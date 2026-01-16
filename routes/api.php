<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventMediaController;
use App\Http\Controllers\Api\EventSourceController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\MilitaryEquipmentController;
use App\Http\Controllers\Api\ControlZoneController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\FactionController;
use App\Http\Controllers\Api\ActorController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\UserAccountController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\AudioController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TimelineController;
use App\Http\Controllers\Api\SourceVerificationController;
use App\Http\Controllers\Api\GeolocationController;
use App\Http\Controllers\Api\TipController;
use App\Http\Controllers\Api\ConflictController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/user', [AuthController::class, 'profile']);
        Route::put('/user', [AuthController::class, 'updateProfile']);
    });

    // Events
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index']);
        Route::post('/', [EventController::class, 'store']);
        Route::get('/{uuid}', [EventController::class, 'show']);
        Route::put('/{uuid}', [EventController::class, 'update']);
        Route::delete('/{uuid}', [EventController::class, 'destroy']);

        // Event custom actions
        Route::post('/{uuid}/verify', [EventController::class, 'verify']);
        Route::post('/{uuid}/dispute', [EventController::class, 'dispute']);
        Route::get('/{uuid}/history', [EventController::class, 'history']);

        // Event media
        Route::post('/{uuid}/media', [EventMediaController::class, 'store']);
        Route::delete('/{uuid}/media/{mediaId}', [EventMediaController::class, 'destroy']);

        // Event sources
        Route::post('/{uuid}/sources', [EventSourceController::class, 'store']);
        Route::delete('/{uuid}/sources/{sourceId}', [EventSourceController::class, 'destroy']);
    });

    // Equipment - Full CRUD Operations
    Route::prefix('equipment')->group(function () {
        Route::get('/', [MilitaryEquipmentController::class, 'index']);
        Route::post('/', [MilitaryEquipmentController::class, 'store']);
        Route::get('/categories', [MilitaryEquipmentController::class, 'categories']);
        Route::get('/autocomplete', [MilitaryEquipmentController::class, 'autocomplete']);
        Route::get('/stats', [MilitaryEquipmentController::class, 'stats']);
        Route::get('/by-country/{countryId}', [MilitaryEquipmentController::class, 'byCountry']);
        Route::get('/{id}', [MilitaryEquipmentController::class, 'show']);
        Route::put('/{id}', [MilitaryEquipmentController::class, 'update']);
        Route::delete('/{id}', [MilitaryEquipmentController::class, 'destroy']);
        Route::post('/{id}/restore', [MilitaryEquipmentController::class, 'restore']);
        Route::get('/{uuid}/events', [EquipmentController::class, 'events']);
    });

    // Control Zones
    Route::prefix('zones')->group(function () {
        Route::get('/', [ControlZoneController::class, 'index']);
        Route::post('/', [ControlZoneController::class, 'store']);
        Route::get('/at-date', [ControlZoneController::class, 'atDate']);
        Route::get('/{uuid}', [ControlZoneController::class, 'show']);
        Route::put('/{uuid}', [ControlZoneController::class, 'update']);
        Route::delete('/{uuid}', [ControlZoneController::class, 'destroy']);
        Route::get('/{uuid}/history', [ControlZoneController::class, 'history']);
    });

    // Countries
    Route::prefix('countries')->group(function () {
        Route::get('/', [CountryController::class, 'index']);
        Route::get('/{id}', [CountryController::class, 'show']);
        Route::get('/{id}/equipment', [CountryController::class, 'equipment']);
        Route::get('/{id}/factions', [CountryController::class, 'factions']);
    });

    // Factions
    Route::prefix('factions')->group(function () {
        Route::get('/', [FactionController::class, 'index']);
        Route::get('/{id}', [FactionController::class, 'show']);
        Route::get('/{id}/zones', [FactionController::class, 'zones']);
    });

    // Actors
    Route::prefix('actors')->group(function () {
        Route::get('/', [ActorController::class, 'index']);
        Route::get('/autocomplete', [ActorController::class, 'autocomplete']);
        Route::get('/search', [ActorController::class, 'search']);
        Route::get('/{id}', [ActorController::class, 'show']);
    });

    // Export
    Route::prefix('export')->group(function () {
        Route::get('/kml', [ExportController::class, 'kml']);
        Route::get('/geojson', [ExportController::class, 'geojson']);
        Route::get('/csv', [ExportController::class, 'csv']);
        Route::get('/equipment.csv', [ExportController::class, 'equipmentCsv']);

        // Map export (JPG, PNG, PDF, SVG)
        Route::get('/map/options', [ExportController::class, 'mapExportOptions']);
        Route::get('/map/stats', [ExportController::class, 'mapExportStats']);
        Route::post('/map/image', [ExportController::class, 'storeMapImage']);
        Route::post('/map/pdf', [ExportController::class, 'generateMapPdf']);
        Route::get('/download/{filename}', [ExportController::class, 'downloadMapExport'])->name('api.export.download');
    });

    // Statistics
    Route::prefix('stats')->group(function () {
        Route::get('/overview', [StatsController::class, 'overview']);
        Route::get('/losses', [StatsController::class, 'losses']);
        Route::get('/events', [StatsController::class, 'events']);
        Route::get('/timeline', [StatsController::class, 'timeline']);
        Route::get('/heatmap', [StatsController::class, 'heatmap']);
    });

    // Articles / News / Premium Content
    Route::prefix('articles')->group(function () {
        Route::get('/', [ArticleController::class, 'index']);
        Route::post('/', [ArticleController::class, 'store']);
        Route::get('/categories', [ArticleController::class, 'categories']);
        Route::get('/tags', [ArticleController::class, 'tags']);
        Route::get('/bookmarks', [ArticleController::class, 'myBookmarks']);
        Route::get('/{uuid}', [ArticleController::class, 'show']);
        Route::put('/{uuid}', [ArticleController::class, 'update']);
        Route::delete('/{uuid}', [ArticleController::class, 'destroy']);
        Route::post('/{uuid}/bookmark', [ArticleController::class, 'bookmark']);
        Route::delete('/{uuid}/bookmark', [ArticleController::class, 'unbookmark']);
        Route::post('/{uuid}/progress', [ArticleController::class, 'trackProgress']);

        // Article comments
        Route::get('/{uuid}/comments', [CommentController::class, 'index']);
        Route::post('/{uuid}/comments', [CommentController::class, 'store']);
    });

    // Comments
    Route::prefix('comments')->group(function () {
        Route::put('/{uuid}', [CommentController::class, 'update']);
        Route::delete('/{uuid}', [CommentController::class, 'destroy']);
        Route::post('/{uuid}/vote', [CommentController::class, 'vote']);
        Route::post('/{uuid}/report', [CommentController::class, 'report']);
        Route::get('/{uuid}/revisions', [CommentController::class, 'revisions']);
        Route::get('/{uuid}/replies', [CommentController::class, 'replies']);

        // Moderation (requires permission)
        Route::get('/pending', [CommentController::class, 'pending']);
        Route::post('/{uuid}/approve', [CommentController::class, 'approve']);
        Route::post('/{uuid}/reject', [CommentController::class, 'reject']);
        Route::post('/{uuid}/spam', [CommentController::class, 'markSpam']);
        Route::post('/bulk-moderate', [CommentController::class, 'bulkModerate']);
        Route::get('/reports', [CommentController::class, 'reports']);
        Route::post('/reports/{uuid}/review', [CommentController::class, 'reviewReport']);
    });

    // User Account Management
    Route::prefix('account')->group(function () {
        // Profile
        Route::get('/profile', [UserAccountController::class, 'profile']);
        Route::put('/profile', [UserAccountController::class, 'updateProfile']);
        Route::put('/password', [UserAccountController::class, 'changePassword']);

        // Avatar
        Route::get('/avatar/options', [UserAccountController::class, 'avatarOptions']);
        Route::put('/avatar', [UserAccountController::class, 'updateAvatar']);
        Route::post('/avatar/regenerate', [UserAccountController::class, 'regenerateAvatar']);
        Route::post('/avatar/upload', [UserAccountController::class, 'uploadAvatar']);
        Route::delete('/avatar', [UserAccountController::class, 'deleteAvatar']);

        // Preferences
        Route::get('/preferences', [UserAccountController::class, 'preferences']);
        Route::put('/preferences', [UserAccountController::class, 'updatePreferences']);

        // Privacy & Consent (GDPR)
        Route::get('/privacy', [UserAccountController::class, 'privacySettings']);
        Route::put('/consent', [UserAccountController::class, 'updateConsent']);
        Route::get('/consent/history', [UserAccountController::class, 'consentHistory']);

        // Data Export (GDPR Right to Data Portability)
        Route::post('/data-export', [UserAccountController::class, 'requestDataExport']);
        Route::get('/data-export', [UserAccountController::class, 'dataExportRequests']);

        // Account Deletion (GDPR Right to be Forgotten)
        Route::post('/deletion', [UserAccountController::class, 'requestAccountDeletion']);
        Route::delete('/deletion', [UserAccountController::class, 'cancelAccountDeletion']);

        // Sessions
        Route::get('/sessions', [UserAccountController::class, 'sessions']);
        Route::delete('/sessions/{uuid}', [UserAccountController::class, 'terminateSession']);
        Route::delete('/sessions', [UserAccountController::class, 'terminateOtherSessions']);

        // Activity Log
        Route::get('/activity', [UserAccountController::class, 'activityLog']);
    });

    // Onboarding
    Route::prefix('onboarding')->group(function () {
        Route::get('/status', [OnboardingController::class, 'status']);
        Route::get('/step/{stepKey}', [OnboardingController::class, 'step']);
        Route::post('/step/{stepKey}/complete', [OnboardingController::class, 'completeStep']);
        Route::post('/skip', [OnboardingController::class, 'skip']);
        Route::post('/reset', [OnboardingController::class, 'reset']);
    });

    // Audio & Transcription
    Route::prefix('audio')->group(function () {
        // Audio files
        Route::get('/', [AudioController::class, 'index']);
        Route::post('/', [AudioController::class, 'store']);
        Route::get('/ai-models', [AudioController::class, 'aiModels']);
        Route::get('/{uuid}', [AudioController::class, 'show']);
        Route::put('/{uuid}', [AudioController::class, 'update']);
        Route::delete('/{uuid}', [AudioController::class, 'destroy']);
        Route::post('/{uuid}/play', [AudioController::class, 'recordPlay']);

        // Transcriptions for audio file
        Route::get('/{uuid}/transcriptions', [AudioController::class, 'transcriptions']);
        Route::post('/{uuid}/transcriptions/manual', [AudioController::class, 'createManualTranscription']);
        Route::post('/{uuid}/transcriptions/ai', [AudioController::class, 'requestAiTranscription']);
        Route::get('/{uuid}/transcriptions/jobs/{jobUuid}', [AudioController::class, 'transcriptionJobStatus']);

        // Specific transcription
        Route::get('/{uuid}/transcriptions/{transcriptionUuid}', [AudioController::class, 'getTranscription']);
        Route::put('/{uuid}/transcriptions/{transcriptionUuid}', [AudioController::class, 'updateTranscription']);
        Route::post('/{uuid}/transcriptions/{transcriptionUuid}/primary', [AudioController::class, 'setPrimary']);
        Route::get('/{uuid}/transcriptions/{transcriptionUuid}/export', [AudioController::class, 'exportTranscription']);
        Route::get('/{uuid}/transcriptions/{transcriptionUuid}/revisions', [AudioController::class, 'revisions']);

        // Transcript segments
        Route::post('/{uuid}/transcriptions/{transcriptionUuid}/segments', [AudioController::class, 'addSegment']);
        Route::put('/{uuid}/transcriptions/{transcriptionUuid}/segments/{segmentId}', [AudioController::class, 'updateSegment']);
        Route::delete('/{uuid}/transcriptions/{transcriptionUuid}/segments/{segmentId}', [AudioController::class, 'deleteSegment']);
    });

    // Skills (AI Skills with keyword triggering)
    Route::prefix('skills')->group(function () {
        Route::get('/', [SkillController::class, 'index']);
        Route::get('/categories', [SkillController::class, 'categories']);
        Route::get('/stats', [SkillController::class, 'stats']);
        Route::post('/match', [SkillController::class, 'match']);
        Route::post('/suggest', [SkillController::class, 'suggest']);
        Route::post('/trigger', [SkillController::class, 'trigger']);
        Route::get('/preferences', [SkillController::class, 'preferences']);
        Route::get('/{slug}', [SkillController::class, 'show']);
        Route::put('/{slug}/preference', [SkillController::class, 'updatePreference']);
        Route::get('/{slug}/triggers', [SkillController::class, 'triggers']);
        Route::post('/{slug}/agent', [SkillController::class, 'createAgent']);

        // Admin only
        Route::post('/', [SkillController::class, 'store']);
        Route::put('/{slug}', [SkillController::class, 'update']);
        Route::delete('/{slug}', [SkillController::class, 'destroy']);
    });

    // Agents (AI Agents built from skills)
    Route::prefix('agents')->group(function () {
        Route::get('/', [AgentController::class, 'index']);
        Route::get('/types', [AgentController::class, 'types']);
        Route::post('/', [AgentController::class, 'store']);
        Route::post('/from-skills', [AgentController::class, 'createFromSkills']);
        Route::get('/{slug}', [AgentController::class, 'show']);
        Route::put('/{slug}', [AgentController::class, 'update']);
        Route::delete('/{slug}', [AgentController::class, 'destroy']);

        // Agent skills
        Route::post('/{slug}/skills', [AgentController::class, 'addSkill']);
        Route::delete('/{slug}/skills/{skillId}', [AgentController::class, 'removeSkill']);

        // Agent execution
        Route::post('/{slug}/run', [AgentController::class, 'run']);
        Route::get('/{slug}/executions', [AgentController::class, 'executions']);
        Route::get('/{slug}/executions/{executionUuid}', [AgentController::class, 'execution']);
        Route::get('/{slug}/stats', [AgentController::class, 'stats']);
    });

    // Admin Routes
    Route::prefix('admin')->middleware(['role:admin'])->group(function () {
        Route::apiResource('achievements', \App\Http\Controllers\Api\Admin\AchievementController::class);
    });

    // User Achievements
    Route::get('/user/achievements', function (Request $request) {
        return $request->user()->achievements;
    });

    // Alerts & Notifications
    Route::prefix('alerts')->group(function () {
        Route::get('/', [AlertController::class, 'index']);
        Route::post('/', [AlertController::class, 'store']);
        Route::get('/{uuid}', [AlertController::class, 'show']);
        Route::put('/{uuid}', [AlertController::class, 'update']);
        Route::delete('/{uuid}', [AlertController::class, 'destroy']);
        Route::get('/{uuid}/history', [AlertController::class, 'history']);
        Route::post('/{uuid}/test', [AlertController::class, 'test']);
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/', [AlertController::class, 'notifications']);
        Route::post('/{uuid}/read', [AlertController::class, 'markRead']);
        Route::post('/read-all', [AlertController::class, 'markAllRead']);
    });

    // Reports & SITREP Generation
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::get('/templates', [ReportController::class, 'templates']);
        Route::post('/generate', [ReportController::class, 'generate']);
        Route::get('/{uuid}', [ReportController::class, 'show']);
        Route::get('/{uuid}/download', [ReportController::class, 'download']);
        Route::delete('/{uuid}', [ReportController::class, 'destroy']);
        Route::post('/schedule', [ReportController::class, 'schedule']);
        Route::get('/schedules', [ReportController::class, 'schedules']);
        Route::delete('/schedules/{uuid}', [ReportController::class, 'deleteSchedule']);
    });

    // Timeline & Temporal Analysis
    Route::prefix('timeline')->group(function () {
        Route::get('/', [TimelineController::class, 'index']);
        Route::post('/compare', [TimelineController::class, 'compare']);
        Route::get('/territorial', [TimelineController::class, 'territorial']);
        Route::get('/playback', [TimelineController::class, 'playback']);
        Route::post('/investigation', [TimelineController::class, 'investigation']);
        Route::get('/milestones', [TimelineController::class, 'milestones']);
    });

    // Source Verification
    Route::prefix('sources')->group(function () {
        Route::get('/trusted', [SourceVerificationController::class, 'trustedSources']);
        Route::post('/check', [SourceVerificationController::class, 'checkSource']);
        Route::post('/submit', [SourceVerificationController::class, 'submitSource']);
        Route::post('/grade', [SourceVerificationController::class, 'gradeInformation']);
        Route::post('/cross-reference', [SourceVerificationController::class, 'crossReference']);
        Route::get('/types', [SourceVerificationController::class, 'sourceTypes']);
    });

    // Geolocation & Verification Tools
    Route::prefix('geolocation')->group(function () {
        Route::get('/projects', [GeolocationController::class, 'projects']);
        Route::post('/projects', [GeolocationController::class, 'createProject']);
        Route::get('/projects/{uuid}', [GeolocationController::class, 'showProject']);
        Route::post('/projects/{uuid}/markers', [GeolocationController::class, 'addMarker']);
        Route::post('/projects/{uuid}/result', [GeolocationController::class, 'submitResult']);
        Route::post('/sun-position', [GeolocationController::class, 'sunPosition']);
        Route::post('/reverse-geocode', [GeolocationController::class, 'reverseGeocode']);
        Route::post('/weather', [GeolocationController::class, 'weatherData']);
        Route::get('/satellite-layers', [GeolocationController::class, 'satelliteLayers']);
    });

    // Tips (moderation - requires auth)
    Route::prefix('tips')->group(function () {
        Route::get('/', [TipController::class, 'index']);
        Route::get('/{uuid}', [TipController::class, 'show']);
        Route::put('/{uuid}/status', [TipController::class, 'updateStatus']);
        Route::post('/{uuid}/convert', [TipController::class, 'convertToEvent']);
    });

    // Conflicts
    Route::prefix('conflicts')->group(function () {
        Route::get('/', [ConflictController::class, 'index']);
        Route::get('/active', [ConflictController::class, 'active']);
        Route::get('/regions', [ConflictController::class, 'regions']);
        Route::get('/types', [ConflictController::class, 'types']);
        Route::get('/search', [ConflictController::class, 'search']);
        Route::get('/{slug}', [ConflictController::class, 'show']);
        Route::get('/{slug}/events', [ConflictController::class, 'events']);
        Route::get('/{slug}/zones', [ConflictController::class, 'zones']);
        Route::get('/{slug}/statistics', [ConflictController::class, 'statistics']);
        Route::get('/{slug}/actors', [ConflictController::class, 'actors']);
    });
});

// ===================================
// PUBLIC ROUTES (No Authentication)
// ===================================

// Public tip submission
Route::prefix('tips')->group(function () {
    Route::post('/submit', [TipController::class, 'submit']);
    Route::get('/types', [TipController::class, 'types']);
    Route::get('/status/{uuid}', [TipController::class, 'status']);
});

// Public conflict information
Route::prefix('public')->group(function () {
    Route::get('/conflicts', [ConflictController::class, 'index']);
    Route::get('/conflicts/active', [ConflictController::class, 'active']);
    Route::get('/conflicts/{slug}', [ConflictController::class, 'show']);
});

// Health check endpoint (public)
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'service' => 'OsintWeb API',
    ]);
});
