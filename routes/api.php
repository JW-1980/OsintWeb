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
        Route::get('/user', [AuthController::class, 'user']);
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
});

// Health check endpoint (public)
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'service' => 'OsintWeb API',
    ]);
});
