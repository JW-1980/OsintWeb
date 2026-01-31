<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use App\Http\Controllers\Api\Auth\PasswordResetLinkController;
use App\Http\Controllers\Api\Auth\NewPasswordController;
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
use App\Http\Controllers\Api\SourceController;
use App\Http\Controllers\Api\EvidenceController;
use App\Http\Controllers\Api\WeatherController;
use App\Http\Controllers\Api\AudioAnalysisController;
use App\Http\Controllers\Api\ReverseImageController;
use App\Http\Controllers\Api\VideoAnalysisController;
use App\Http\Controllers\Api\SocialMediaController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DisinformationController;
use App\Http\Controllers\Api\FlightTrackingController;
use App\Http\Controllers\Api\MaritimeController;
use App\Http\Controllers\Api\CaseController;
use App\Http\Controllers\Api\SitrepController;
use App\Http\Controllers\Api\NetworkAnalysisController;
use App\Http\Controllers\Api\OfflineController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\TrainingController;
use App\Http\Controllers\Api\ScheduledReportController;
use App\Http\Controllers\Api\UserActivityController;
use App\Http\Controllers\Api\Admin\SiteAnalyticsController;
use App\Http\Controllers\Api\Admin\ABTestingController;
use App\Http\Controllers\Api\Admin\CaptchaSettingsController;
use App\Http\Controllers\Api\ABTestingPublicController;

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

// Public authentication routes - strict rate limiting to prevent brute force
Route::prefix('auth')->middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('captcha');
    Route::post('/login', [AuthController::class, 'login'])->middleware('captcha');
    // Verify Email Route (Signed, Public access via signature)
    Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('captcha');
    Route::post('/reset-password', [NewPasswordController::class, 'store']);
});

// Helper route for email link redirection
Route::get('/reset-password/{token}', function (string $token) {
    return redirect(url('/reset-password/' . $token . '?email=' . request('email')));
})->name('password.reset');

// Protected routes (require authentication) - standard API rate limiting
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/user', [AuthController::class, 'profile']);
        Route::put('/user', [AuthController::class, 'updateProfile']);
        Route::post('/email/verification-notification', [VerifyEmailController::class, 'resend'])
            ->middleware('throttle:6,1')
            ->name('verification.send');
    });

    // Events
    Route::prefix('events')->group(function () {
        Route::post('/', [EventController::class, 'store']);
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
        Route::post('/', [MilitaryEquipmentController::class, 'store']);
        Route::put('/{id}', [MilitaryEquipmentController::class, 'update']);
        Route::delete('/{id}', [MilitaryEquipmentController::class, 'destroy']);
        Route::post('/{id}/restore', [MilitaryEquipmentController::class, 'restore']);
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
        // CRUD operations if any
    });

    // Export - more restrictive rate limiting for heavy operations
    Route::prefix('export')->middleware('throttle:exports')->group(function () {
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

    // User Activity Logging (for frontend tracking)
    Route::prefix('activity')->group(function () {
        Route::post('/log', [UserActivityController::class, 'log']);
        Route::post('/log/batch', [UserActivityController::class, 'logBatch']);
        Route::get('/my', [UserActivityController::class, 'myActivity']);
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

    // Sources (Verified Information Sources)
    Route::prefix('sources')->group(function () {
        // List and search
        Route::get('/', [SourceController::class, 'index']);
        Route::get('/autocomplete', [SourceController::class, 'autocomplete']);
        Route::get('/statistics', [SourceController::class, 'statistics']);
        Route::get('/types', [SourceController::class, 'types']);
        Route::get('/statuses', [SourceController::class, 'statuses']);

        // CRUD
        Route::post('/', [SourceController::class, 'store']);
        Route::get('/{uuid}', [SourceController::class, 'show']);
        Route::put('/{uuid}', [SourceController::class, 'update']);
        Route::delete('/{uuid}', [SourceController::class, 'destroy']);

        // Verification (requires elevated permissions)
        Route::post('/{uuid}/verify', [SourceController::class, 'verify']);
        Route::post('/{uuid}/record-report', [SourceController::class, 'recordReport']);

        // Cross-references
        Route::get('/{uuid}/cross-references', [SourceController::class, 'getCrossReferences']);
        Route::post('/{uuid}/cross-reference', [SourceController::class, 'crossReference']);

        // Related data
        Route::get('/{uuid}/events', [SourceController::class, 'events']);

        // Admin actions
        Route::post('/recalculate-scores', [SourceController::class, 'recalculateScores']);
    });

    // Evidence Preservation
    Route::prefix('evidence')->group(function () {
        // List and search
        Route::get('/', [EvidenceController::class, 'index']);
        Route::get('/stats', [EvidenceController::class, 'stats']);
        Route::get('/types', [EvidenceController::class, 'types']);

        // Preserve new URL
        Route::post('/preserve', [EvidenceController::class, 'preserve']);

        // Specific evidence operations
        Route::get('/{uuid}', [EvidenceController::class, 'show']);
        Route::get('/{uuid}/status', [EvidenceController::class, 'status']);
        Route::get('/{uuid}/verify', [EvidenceController::class, 'verify']);
        Route::get('/{uuid}/download', [EvidenceController::class, 'download']);
        Route::delete('/{uuid}', [EvidenceController::class, 'destroy']);

        // Snapshots
        Route::get('/{uuid}/snapshots', [EvidenceController::class, 'snapshots']);
        Route::post('/{uuid}/snapshots', [EvidenceController::class, 'createSnapshot']);

        // Retry failed preservation
        Route::post('/{uuid}/retry', [EvidenceController::class, 'retry']);
    });

    // Geolocation Verification
    Route::prefix('geolocation')->group(function () {
        // List and search verifications
        Route::get('/', [GeolocationController::class, 'index']);
        Route::get('/methods', [GeolocationController::class, 'methods']);

        // Create new verification
        Route::post('/', [GeolocationController::class, 'store']);

        // Analysis tools
        Route::post('/analyze', [GeolocationController::class, 'analyze']);
        Route::get('/sun-position', [GeolocationController::class, 'sunPosition']);

        // Event-specific stats
        Route::get('/events/{eventId}/stats', [GeolocationController::class, 'eventStats']);

        // Specific verification operations
        Route::get('/{uuid}', [GeolocationController::class, 'show']);
        Route::put('/{uuid}', [GeolocationController::class, 'update']);
        Route::delete('/{uuid}', [GeolocationController::class, 'destroy']);

        // Verification actions
        Route::post('/{uuid}/verify', [GeolocationController::class, 'verify']);
        Route::post('/{uuid}/dispute', [GeolocationController::class, 'dispute']);
        Route::post('/{uuid}/reject', [GeolocationController::class, 'reject']);

        // Reports
        Route::get('/{uuid}/report', [GeolocationController::class, 'report']);
    });

    // Weather & Environmental Data
    Route::prefix('weather')->group(function () {
        // List and search weather data
        Route::get('/', [WeatherController::class, 'index']);
        Route::get('/providers', [WeatherController::class, 'providers']);
        Route::get('/conditions', [WeatherController::class, 'conditions']);

        // Get weather for location/time
        Route::get('/location', [WeatherController::class, 'getForLocation']);

        // Sun position and shadow analysis
        Route::get('/sun-position', [WeatherController::class, 'getSunPosition']);
        Route::post('/verify-shadow', [WeatherController::class, 'verifyShadow']);
        Route::post('/find-matching-times', [WeatherController::class, 'findMatchingTimes']);

        // Specific weather record
        Route::get('/{uuid}', [WeatherController::class, 'show']);

        // Event-related weather
        Route::get('/event/{eventUuid}', [WeatherController::class, 'getForEvent']);
        Route::post('/event/{eventUuid}/link', [WeatherController::class, 'linkToEvent']);
        Route::delete('/event/{eventUuid}/link/{weatherUuid}', [WeatherController::class, 'unlinkFromEvent']);
    });

    // Audio Analysis (Forensic Audio Analysis & Spectrogram) - AI rate limiting
    Route::prefix('audio-analysis')->middleware('throttle:ai')->group(function () {
        // List and statistics
        Route::get('/', [AudioAnalysisController::class, 'index']);
        Route::get('/stats', [AudioAnalysisController::class, 'stats']);

        // Create new analysis
        Route::post('/', [AudioAnalysisController::class, 'analyze']);

        // Compare two audio files
        Route::post('/compare', [AudioAnalysisController::class, 'compare']);

        // Specific analysis operations
        Route::get('/{uuid}', [AudioAnalysisController::class, 'show']);
        Route::delete('/{uuid}', [AudioAnalysisController::class, 'destroy']);
        Route::post('/{uuid}/retry', [AudioAnalysisController::class, 'retry']);

        // Visualizations
        Route::get('/{uuid}/spectrogram', [AudioAnalysisController::class, 'spectrogram']);
        Route::get('/{uuid}/waveform', [AudioAnalysisController::class, 'waveform']);

        // Analysis details
        Route::get('/{uuid}/edits', [AudioAnalysisController::class, 'edits']);
        Route::get('/{uuid}/voice-activity', [AudioAnalysisController::class, 'voiceActivity']);
        Route::get('/{uuid}/silence', [AudioAnalysisController::class, 'silence']);
        Route::get('/{uuid}/frequencies', [AudioAnalysisController::class, 'frequencies']);
    });

    // Reverse Image Search Aggregator - AI rate limiting
    Route::prefix('reverse-image')->middleware('throttle:ai')->group(function () {
        // List user's search history
        Route::get('/', [ReverseImageController::class, 'index']);

        // Reference data
        Route::get('/engines', [ReverseImageController::class, 'engines']);
        Route::get('/match-types', [ReverseImageController::class, 'matchTypes']);
        Route::get('/history', [ReverseImageController::class, 'history']);
        Route::get('/stats', [ReverseImageController::class, 'stats']);

        // Initiate new search
        Route::post('/search', [ReverseImageController::class, 'search']);

        // Image comparison utilities
        Route::post('/compare', [ReverseImageController::class, 'compare']);
        Route::post('/find-similar', [ReverseImageController::class, 'findSimilar']);

        // Specific search operations
        Route::get('/{uuid}', [ReverseImageController::class, 'show']);
        Route::get('/{uuid}/status', [ReverseImageController::class, 'status']);
        Route::get('/{uuid}/results', [ReverseImageController::class, 'results']);
        Route::post('/{uuid}/retry', [ReverseImageController::class, 'retry']);
        Route::delete('/{uuid}', [ReverseImageController::class, 'destroy']);
    });

    // Video Frame Analysis - AI rate limiting
    Route::prefix('video-analysis')->middleware('throttle:ai')->group(function () {
        // List and statistics
        Route::get('/', [VideoAnalysisController::class, 'index']);
        Route::get('/stats', [VideoAnalysisController::class, 'stats']);
        Route::get('/formats', [VideoAnalysisController::class, 'formats']);

        // Upload and analyze
        Route::post('/upload', [VideoAnalysisController::class, 'upload']);

        // Specific video operations
        Route::get('/{uuid}', [VideoAnalysisController::class, 'show']);
        Route::delete('/{uuid}', [VideoAnalysisController::class, 'destroy']);
        Route::post('/{uuid}/analyze', [VideoAnalysisController::class, 'analyze']);
        Route::get('/{uuid}/metadata', [VideoAnalysisController::class, 'metadata']);

        // Keyframes
        Route::get('/{uuid}/keyframes', [VideoAnalysisController::class, 'keyframes']);
        Route::post('/{uuid}/keyframes/extract', [VideoAnalysisController::class, 'extractFrame']);
        Route::get('/{uuid}/keyframes/{keyframeId}', [VideoAnalysisController::class, 'keyframe']);
        Route::post('/{uuid}/keyframes/{keyframeId}/representative', [VideoAnalysisController::class, 'markRepresentative']);
        Route::post('/{uuid}/keyframes/{keyframeId}/analyze', [VideoAnalysisController::class, 'analyzeKeyframe']);

        // Event linking
        Route::post('/{uuid}/link-event', [VideoAnalysisController::class, 'linkToEvent']);
        Route::delete('/{uuid}/link-event', [VideoAnalysisController::class, 'unlinkFromEvent']);
    });

    // Social Media Monitoring
    Route::prefix('social-media')->group(function () {
        // Utility endpoints (reference data)
        Route::get('/platforms', [SocialMediaController::class, 'platforms']);
        Route::get('/post-types', [SocialMediaController::class, 'postTypes']);
        Route::get('/alert-types', [SocialMediaController::class, 'alertTypes']);
        Route::get('/priorities', [SocialMediaController::class, 'priorities']);
        Route::get('/stats', [SocialMediaController::class, 'stats']);

        // Accounts
        Route::get('/accounts', [SocialMediaController::class, 'index']);
        Route::post('/accounts', [SocialMediaController::class, 'store']);
        Route::get('/accounts/{uuid}', [SocialMediaController::class, 'show']);
        Route::put('/accounts/{uuid}', [SocialMediaController::class, 'update']);
        Route::delete('/accounts/{uuid}', [SocialMediaController::class, 'destroy']);
        Route::post('/accounts/{uuid}/toggle-monitoring', [SocialMediaController::class, 'toggleMonitoring']);
        Route::get('/accounts/{uuid}/stats', [SocialMediaController::class, 'accountStats']);
        Route::post('/accounts/{uuid}/fetch', [SocialMediaController::class, 'fetchPosts']);

        // Posts for specific account
        Route::get('/accounts/{uuid}/posts', [SocialMediaController::class, 'posts']);

        // Global posts (search across all accounts)
        Route::get('/posts/search', [SocialMediaController::class, 'search']);
        Route::get('/posts/{uuid}', [SocialMediaController::class, 'showPost']);
        Route::post('/posts/{uuid}/archive', [SocialMediaController::class, 'archive']);
        Route::post('/posts/{uuid}/link-event', [SocialMediaController::class, 'linkToEvent']);
        Route::delete('/posts/{uuid}/link-event', [SocialMediaController::class, 'unlinkFromEvent']);
        Route::post('/posts/{uuid}/detect-keywords', [SocialMediaController::class, 'detectKeywords']);

        // Alerts
        Route::get('/alerts', [SocialMediaController::class, 'alerts']);
        Route::post('/alerts', [SocialMediaController::class, 'createAlert']);
        Route::get('/alerts/{uuid}', [SocialMediaController::class, 'showAlert']);
        Route::put('/alerts/{uuid}', [SocialMediaController::class, 'updateAlert']);
        Route::delete('/alerts/{uuid}', [SocialMediaController::class, 'deleteAlert']);
        Route::post('/alerts/{uuid}/toggle', [SocialMediaController::class, 'toggleAlert']);
        Route::get('/alerts/{uuid}/triggers', [SocialMediaController::class, 'alertTriggers']);
    });

    // Document OCR & Analysis - AI rate limiting
    Route::prefix('documents')->middleware('throttle:ai')->group(function () {
        // List and statistics
        Route::get('/', [DocumentController::class, 'index']);
        Route::get('/stats', [DocumentController::class, 'stats']);

        // Reference data
        Route::get('/languages', [DocumentController::class, 'languages']);
        Route::get('/types', [DocumentController::class, 'documentTypes']);
        Route::get('/ai-status', [DocumentController::class, 'aiStatus']);

        // Upload and analyze
        Route::post('/upload', [DocumentController::class, 'upload']);

        // Specific document operations
        Route::get('/{uuid}', [DocumentController::class, 'show']);
        Route::delete('/{uuid}', [DocumentController::class, 'destroy']);
        Route::post('/{uuid}/analyze', [DocumentController::class, 'analyze']);
        Route::post('/{uuid}/retry', [DocumentController::class, 'retry']);
        Route::get('/{uuid}/download', [DocumentController::class, 'download']);

        // Extracted content
        Route::get('/{uuid}/text', [DocumentController::class, 'text']);
        Route::get('/{uuid}/entities', [DocumentController::class, 'entities']);
        Route::get('/{uuid}/tables', [DocumentController::class, 'tables']);

        // Translations
        Route::post('/{uuid}/translate', [DocumentController::class, 'translate']);
        Route::get('/{uuid}/translations', [DocumentController::class, 'translations']);
        Route::get('/{uuid}/translations/{language}', [DocumentController::class, 'translation']);
    });

    // Disinformation Detection & Analysis - AI rate limiting
    Route::prefix('disinformation')->middleware('throttle:ai')->group(function () {
        // Reference data
        Route::get('/analysis-types', [DisinformationController::class, 'analysisTypes']);
        Route::get('/pattern-types', [DisinformationController::class, 'patternTypes']);
        Route::get('/flag-reasons', [DisinformationController::class, 'flagReasons']);
        Route::get('/resolution-statuses', [DisinformationController::class, 'resolutionStatuses']);
        Route::get('/severity-levels', [DisinformationController::class, 'severityLevels']);
        Route::get('/priority-levels', [DisinformationController::class, 'priorityLevels']);

        // Statistics and trends
        Route::get('/statistics', [DisinformationController::class, 'statistics']);
        Route::get('/trends', [DisinformationController::class, 'trends']);

        // Analysis operations
        Route::get('/analyses', [DisinformationController::class, 'index']);
        Route::post('/analyze', [DisinformationController::class, 'analyze']);
        Route::post('/analyze-coordinated', [DisinformationController::class, 'analyzeCoordinated']);
        Route::get('/analyses/{uuid}', [DisinformationController::class, 'show']);
        Route::post('/analyses/{uuid}/review', [DisinformationController::class, 'review']);

        // Flagging operations
        Route::get('/flagged', [DisinformationController::class, 'flaggedContent']);
        Route::post('/flag', [DisinformationController::class, 'flag']);
        Route::get('/flagged/{uuid}', [DisinformationController::class, 'showFlagged']);
        Route::post('/flagged/{uuid}/resolve', [DisinformationController::class, 'resolveFlagged']);
        Route::post('/flagged/{uuid}/appeal', [DisinformationController::class, 'handleAppeal']);

        // Pattern management (admin)
        Route::get('/patterns', [DisinformationController::class, 'patterns']);
        Route::post('/patterns', [DisinformationController::class, 'storePattern']);
        Route::get('/patterns/{slug}', [DisinformationController::class, 'showPattern']);
        Route::put('/patterns/{slug}', [DisinformationController::class, 'updatePattern']);
        Route::delete('/patterns/{slug}', [DisinformationController::class, 'destroyPattern']);
        Route::post('/patterns/{slug}/toggle', [DisinformationController::class, 'togglePattern']);
    });

    // Maritime Vessel Tracking (AIS)
    Route::prefix('maritime')->group(function () {
        // Reference data
        Route::get('/vessel-types', [MaritimeController::class, 'vesselTypes']);
        Route::get('/statistics', [MaritimeController::class, 'statistics']);

        // Vessel management
        Route::get('/vessels', [MaritimeController::class, 'index']);
        Route::post('/vessels', [MaritimeController::class, 'store']);
        Route::get('/vessels/{uuid}', [MaritimeController::class, 'show']);
        Route::put('/vessels/{uuid}', [MaritimeController::class, 'update']);
        Route::delete('/vessels/{uuid}', [MaritimeController::class, 'destroy']);

        // Vessel positions
        Route::get('/vessels/{uuid}/positions', [MaritimeController::class, 'positions']);
        Route::post('/vessels/{uuid}/positions', [MaritimeController::class, 'recordPosition']);

        // Vessel tracks
        Route::get('/vessels/{uuid}/tracks', [MaritimeController::class, 'tracks']);
        Route::post('/vessels/{uuid}/tracks/build', [MaritimeController::class, 'buildTrack']);

        // Port calls
        Route::get('/vessels/{uuid}/port-calls', [MaritimeController::class, 'portCalls']);
        Route::post('/vessels/{uuid}/port-calls', [MaritimeController::class, 'recordPortCall']);

        // Anomaly detection
        Route::get('/vessels/{uuid}/detect-dark', [MaritimeController::class, 'detectDarkActivity']);
        Route::get('/vessels/{uuid}/detect-sts', [MaritimeController::class, 'detectSTSTransfer']);

        // Specific track operations
        Route::post('/tracks/{uuid}/link-event', [MaritimeController::class, 'linkToEvent']);

        // Live data
        Route::get('/live', [MaritimeController::class, 'live']);
        Route::post('/in-zone', [MaritimeController::class, 'inZone']);

        // Alerts
        Route::get('/alerts', [MaritimeController::class, 'alerts']);
        Route::post('/alerts', [MaritimeController::class, 'createAlert']);
        Route::put('/alerts/{uuid}', [MaritimeController::class, 'updateAlert']);
        Route::delete('/alerts/{uuid}', [MaritimeController::class, 'deleteAlert']);
        Route::post('/alerts/{uuid}/toggle', [MaritimeController::class, 'toggleAlert']);
    });

    // Flight Tracking (ADS-B)
    Route::prefix('flight-tracking')->group(function () {
        // Reference data
        Route::get('/providers', [FlightTrackingController::class, 'providers']);
        Route::get('/data-sources', [FlightTrackingController::class, 'dataSources']);
        Route::get('/alert-types', [FlightTrackingController::class, 'alertTypes']);
        Route::get('/priorities', [FlightTrackingController::class, 'priorities']);
        Route::get('/track-statuses', [FlightTrackingController::class, 'trackStatuses']);
        Route::get('/stats', [FlightTrackingController::class, 'stats']);

        // Aircraft management
        Route::get('/aircraft', [FlightTrackingController::class, 'index']);
        Route::post('/aircraft', [FlightTrackingController::class, 'store']);
        Route::get('/aircraft/{uuid}', [FlightTrackingController::class, 'show']);
        Route::put('/aircraft/{uuid}', [FlightTrackingController::class, 'update']);
        Route::delete('/aircraft/{uuid}', [FlightTrackingController::class, 'destroy']);

        // Aircraft positions
        Route::get('/aircraft/{uuid}/positions', [FlightTrackingController::class, 'positions']);
        Route::post('/aircraft/{uuid}/positions', [FlightTrackingController::class, 'recordPosition']);

        // Aircraft tracks
        Route::get('/aircraft/{uuid}/tracks', [FlightTrackingController::class, 'tracks']);
        Route::post('/aircraft/{uuid}/tracks/build', [FlightTrackingController::class, 'buildTrack']);

        // Unusual activity detection
        Route::get('/aircraft/{uuid}/detect-unusual', [FlightTrackingController::class, 'detectUnusual']);

        // Specific track operations
        Route::get('/tracks/{uuid}', [FlightTrackingController::class, 'showTrack']);
        Route::post('/tracks/{uuid}/link-event', [FlightTrackingController::class, 'linkToEvent']);
        Route::delete('/tracks/{uuid}/link-event', [FlightTrackingController::class, 'unlinkFromEvent']);

        // Live data
        Route::get('/live', [FlightTrackingController::class, 'live']);
        Route::post('/in-zone', [FlightTrackingController::class, 'inZone']);

        // Alerts
        Route::get('/alerts', [FlightTrackingController::class, 'alerts']);
        Route::post('/alerts', [FlightTrackingController::class, 'createAlert']);
        Route::get('/alerts/{uuid}', [FlightTrackingController::class, 'showAlert']);
        Route::put('/alerts/{uuid}', [FlightTrackingController::class, 'updateAlert']);
        Route::delete('/alerts/{uuid}', [FlightTrackingController::class, 'deleteAlert']);
        Route::post('/alerts/{uuid}/toggle', [FlightTrackingController::class, 'toggleAlert']);
        Route::get('/alerts/{uuid}/triggers', [FlightTrackingController::class, 'alertTriggers']);
    });

    // Network Analysis & Visualization
    Route::prefix('network-analysis')->group(function () {
        // Reference data
        Route::get('/graph-types', [NetworkAnalysisController::class, 'graphTypes']);
        Route::get('/entity-types', [NetworkAnalysisController::class, 'entityTypes']);
        Route::get('/statistics', [NetworkAnalysisController::class, 'statistics']);

        // Graph CRUD
        Route::get('/graphs', [NetworkAnalysisController::class, 'index']);
        Route::post('/graphs', [NetworkAnalysisController::class, 'store']);
        Route::get('/graphs/{uuid}', [NetworkAnalysisController::class, 'show']);
        Route::put('/graphs/{uuid}', [NetworkAnalysisController::class, 'update']);
        Route::delete('/graphs/{uuid}', [NetworkAnalysisController::class, 'destroy']);

        // Graph building and data
        Route::post('/build', [NetworkAnalysisController::class, 'build']);
        Route::get('/graphs/{uuid}/nodes', [NetworkAnalysisController::class, 'nodes']);
        Route::get('/graphs/{uuid}/edges', [NetworkAnalysisController::class, 'edges']);
        Route::post('/graphs/{uuid}/nodes', [NetworkAnalysisController::class, 'addNode']);
        Route::post('/graphs/{uuid}/edges', [NetworkAnalysisController::class, 'addEdge']);
        Route::put('/graphs/{uuid}/layout', [NetworkAnalysisController::class, 'updateLayout']);
        Route::get('/graphs/{uuid}/metrics', [NetworkAnalysisController::class, 'metrics']);

        // Analysis operations
        Route::post('/shortest-path', [NetworkAnalysisController::class, 'shortestPath']);
        Route::post('/clusters', [NetworkAnalysisController::class, 'clusters']);
        Route::post('/centrality', [NetworkAnalysisController::class, 'centrality']);
        Route::post('/bridges', [NetworkAnalysisController::class, 'bridges']);
        Route::post('/communities', [NetworkAnalysisController::class, 'communities']);
        Route::post('/influence-score', [NetworkAnalysisController::class, 'influenceScore']);

        // Snapshots
        Route::get('/graphs/{uuid}/snapshots', [NetworkAnalysisController::class, 'snapshots']);
        Route::post('/graphs/{uuid}/snapshots', [NetworkAnalysisController::class, 'createSnapshot']);
        Route::get('/snapshots/{uuid}', [NetworkAnalysisController::class, 'showSnapshot']);
        Route::post('/snapshots/compare', [NetworkAnalysisController::class, 'compareSnapshots']);

        // Export
        Route::get('/graphs/{uuid}/export', [NetworkAnalysisController::class, 'export']);
    });

    // Scheduled Reports
    Route::prefix('scheduled-reports')->group(function () {
        // Reference data
        Route::get('/report-types', [\App\Http\Controllers\Api\ScheduledReportController::class, 'reportTypes']);
        Route::get('/schedule-types', [\App\Http\Controllers\Api\ScheduledReportController::class, 'scheduleTypes']);
        Route::post('/validate-schedule', [\App\Http\Controllers\Api\ScheduledReportController::class, 'validateSchedule']);
        Route::get('/stats', [\App\Http\Controllers\Api\ScheduledReportController::class, 'stats']);

        // CRUD
        Route::get('/', [\App\Http\Controllers\Api\ScheduledReportController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\ScheduledReportController::class, 'store']);
        Route::get('/{uuid}', [\App\Http\Controllers\Api\ScheduledReportController::class, 'show']);
        Route::put('/{uuid}', [\App\Http\Controllers\Api\ScheduledReportController::class, 'update']);
        Route::delete('/{uuid}', [\App\Http\Controllers\Api\ScheduledReportController::class, 'destroy']);

        // Report actions
        Route::post('/{uuid}/run-now', [\App\Http\Controllers\Api\ScheduledReportController::class, 'runNow']);
        Route::post('/{uuid}/pause', [\App\Http\Controllers\Api\ScheduledReportController::class, 'pause']);
        Route::post('/{uuid}/resume', [\App\Http\Controllers\Api\ScheduledReportController::class, 'resume']);
        Route::post('/{uuid}/toggle', [\App\Http\Controllers\Api\ScheduledReportController::class, 'toggle']);
        Route::get('/{uuid}/preview', [\App\Http\Controllers\Api\ScheduledReportController::class, 'preview']);
        Route::post('/{uuid}/duplicate', [\App\Http\Controllers\Api\ScheduledReportController::class, 'duplicate']);

        // Execution history
        Route::get('/{uuid}/runs', [\App\Http\Controllers\Api\ScheduledReportController::class, 'runs']);
        Route::get('/{reportUuid}/runs/{runUuid}', [\App\Http\Controllers\Api\ScheduledReportController::class, 'showRun']);
    });

    // Case Management Workspaces (Digital War Rooms)
    Route::prefix('cases')->group(function () {
        // Reference data
        Route::get('/types', [CaseController::class, 'caseTypes']);
        Route::get('/priorities', [CaseController::class, 'priorities']);
        Route::get('/statuses', [CaseController::class, 'statuses']);
        Route::get('/classification-levels', [CaseController::class, 'classificationLevels']);
        Route::get('/team-roles', [CaseController::class, 'teamRoles']);
        Route::get('/note-types', [CaseController::class, 'noteTypes']);
        Route::get('/task-types', [CaseController::class, 'taskTypes']);
        Route::get('/task-statuses', [CaseController::class, 'taskStatuses']);
        Route::get('/evidence-statuses', [CaseController::class, 'evidenceStatuses']);
        Route::get('/evidence-types', [CaseController::class, 'evidenceTypes']);
        Route::get('/timeline-entry-types', [CaseController::class, 'timelineEntryTypes']);
        Route::get('/stats', [CaseController::class, 'stats']);

        // Case CRUD
        Route::get('/', [CaseController::class, 'index']);
        Route::post('/', [CaseController::class, 'store']);
        Route::get('/{uuid}', [CaseController::class, 'show']);
        Route::put('/{uuid}', [CaseController::class, 'update']);
        Route::delete('/{uuid}', [CaseController::class, 'destroy']);

        // Case status actions
        Route::post('/{uuid}/start', [CaseController::class, 'start']);
        Route::post('/{uuid}/close', [CaseController::class, 'close']);
        Route::post('/{uuid}/reopen', [CaseController::class, 'reopen']);
        Route::post('/{uuid}/archive', [CaseController::class, 'archive']);

        // Team management
        Route::get('/{uuid}/team', [CaseController::class, 'team']);
        Route::post('/{uuid}/team', [CaseController::class, 'addTeamMember']);
        Route::delete('/{uuid}/team/{userId}', [CaseController::class, 'removeTeamMember']);
        Route::put('/{uuid}/team/{userId}', [CaseController::class, 'updateTeamMemberRole']);

        // Evidence management
        Route::get('/{uuid}/evidence', [CaseController::class, 'evidence']);
        Route::post('/{uuid}/evidence', [CaseController::class, 'linkEvidence']);
        Route::put('/{uuid}/evidence/{evidenceId}', [CaseController::class, 'updateEvidence']);
        Route::post('/{uuid}/evidence/{evidenceId}/review', [CaseController::class, 'reviewEvidence']);
        Route::delete('/{uuid}/evidence/{evidenceId}', [CaseController::class, 'unlinkEvidence']);

        // Notes management
        Route::get('/{uuid}/notes', [CaseController::class, 'notes']);
        Route::post('/{uuid}/notes', [CaseController::class, 'addNote']);
        Route::put('/{uuid}/notes/{noteId}', [CaseController::class, 'updateNote']);
        Route::delete('/{uuid}/notes/{noteId}', [CaseController::class, 'deleteNote']);
        Route::post('/{uuid}/notes/{noteId}/toggle-pin', [CaseController::class, 'toggleNotePin']);

        // Task management
        Route::get('/{uuid}/tasks', [CaseController::class, 'tasks']);
        Route::post('/{uuid}/tasks', [CaseController::class, 'createTask']);
        Route::put('/{uuid}/tasks/{taskUuid}', [CaseController::class, 'updateTask']);
        Route::post('/{uuid}/tasks/{taskUuid}/complete', [CaseController::class, 'completeTask']);
        Route::post('/{uuid}/tasks/{taskUuid}/reopen', [CaseController::class, 'reopenTask']);
        Route::delete('/{uuid}/tasks/{taskUuid}', [CaseController::class, 'deleteTask']);

        // Timeline
        Route::get('/{uuid}/timeline', [CaseController::class, 'timeline']);
        Route::post('/{uuid}/timeline/comment', [CaseController::class, 'addTimelineComment']);

        // Reporting & Export
        Route::get('/{uuid}/report', [CaseController::class, 'report']);
        Route::get('/{uuid}/export', [CaseController::class, 'export']);
    });

    // SITREP (Situation Report) Builder
    Route::prefix('sitreps')->group(function () {
        // Reference data
        Route::get('/sections', [SitrepController::class, 'sections']);
        Route::get('/classifications', [SitrepController::class, 'classifications']);
        Route::get('/report-types', [SitrepController::class, 'reportTypes']);
        Route::get('/statuses', [SitrepController::class, 'statuses']);
        Route::get('/stats', [SitrepController::class, 'stats']);

        // Templates
        Route::get('/templates', [SitrepController::class, 'templates']);
        Route::post('/templates', [SitrepController::class, 'storeTemplate']);
        Route::get('/templates/{uuid}', [SitrepController::class, 'showTemplate']);
        Route::put('/templates/{uuid}', [SitrepController::class, 'updateTemplate']);
        Route::delete('/templates/{uuid}', [SitrepController::class, 'destroyTemplate']);

        // SITREP generation
        Route::post('/generate', [SitrepController::class, 'generate']);
        Route::post('/preview', [SitrepController::class, 'preview']);

        // SITREP CRUD
        Route::get('/', [SitrepController::class, 'index']);
        Route::post('/', [SitrepController::class, 'store']);
        Route::get('/{uuid}', [SitrepController::class, 'show']);
        Route::put('/{uuid}', [SitrepController::class, 'update']);
        Route::delete('/{uuid}', [SitrepController::class, 'destroy']);

        // AI generation
        Route::post('/{uuid}/generate-summary', [SitrepController::class, 'generateSummary']);
        Route::post('/{uuid}/generate-findings', [SitrepController::class, 'generateFindings']);

        // Workflow
        Route::post('/{uuid}/submit', [SitrepController::class, 'submit']);
        Route::post('/{uuid}/approve', [SitrepController::class, 'approve']);
        Route::post('/{uuid}/reject', [SitrepController::class, 'reject']);
        Route::post('/{uuid}/publish', [SitrepController::class, 'publish']);
        Route::post('/{uuid}/archive', [SitrepController::class, 'archive']);

        // Export
        Route::post('/{uuid}/export', [SitrepController::class, 'export']);
        Route::get('/{uuid}/download/{format}', [SitrepController::class, 'download']);
    });

    // Training & Simulation Mode (Sandbox)
    Route::prefix('training')->group(function () {
        // Reference data
        Route::get('/environment-types', [TrainingController::class, 'environmentTypes']);
        Route::get('/difficulty-levels', [TrainingController::class, 'difficultyLevels']);
        Route::get('/session-statuses', [TrainingController::class, 'sessionStatuses']);
        Route::get('/achievement-types', [TrainingController::class, 'achievementTypes']);
        Route::get('/stats', [TrainingController::class, 'stats']);

        // Active session check
        Route::get('/active-session', [TrainingController::class, 'activeSession']);

        // Environments CRUD
        Route::get('/environments', [TrainingController::class, 'environments']);
        Route::post('/environments', [TrainingController::class, 'createEnvironment']);
        Route::get('/environments/{uuid}', [TrainingController::class, 'showEnvironment']);
        Route::put('/environments/{uuid}', [TrainingController::class, 'updateEnvironment']);
        Route::delete('/environments/{uuid}', [TrainingController::class, 'deleteEnvironment']);
        Route::get('/environments/{uuid}/leaderboard', [TrainingController::class, 'leaderboard']);

        // Sessions
        Route::get('/sessions', [TrainingController::class, 'sessions']);
        Route::post('/sessions/start', [TrainingController::class, 'start']);
        Route::get('/sessions/{uuid}', [TrainingController::class, 'status']);
        Route::post('/sessions/{uuid}/pause', [TrainingController::class, 'pause']);
        Route::post('/sessions/{uuid}/resume', [TrainingController::class, 'resume']);
        Route::post('/sessions/{uuid}/complete', [TrainingController::class, 'complete']);
        Route::post('/sessions/{uuid}/abandon', [TrainingController::class, 'abandon']);
        Route::post('/sessions/{uuid}/reset', [TrainingController::class, 'reset']);
        Route::post('/sessions/{uuid}/log-action', [TrainingController::class, 'logAction']);
        Route::post('/sessions/{uuid}/complete-objective', [TrainingController::class, 'completeObjective']);

        // Achievements
        Route::get('/achievements', [TrainingController::class, 'achievements']);

        // Certificates
        Route::get('/sessions/{uuid}/certificate', [TrainingController::class, 'certificate']);
        Route::get('/certificates/{certificateId}/verify', [TrainingController::class, 'verifyCertificate']);
    });

    // Offline Capability & Sync
    Route::prefix('offline')->group(function () {
        // Reference data
        Route::get('/entity-types', [OfflineController::class, 'entityTypes']);
        Route::get('/operations', [OfflineController::class, 'operations']);

        // Cache manifest and data
        Route::get('/manifest', [OfflineController::class, 'manifest']);
        Route::post('/cache-data', [OfflineController::class, 'cacheData']);
        Route::get('/priority-entities', [OfflineController::class, 'priorityEntities']);

        // Sync queue
        Route::post('/queue', [OfflineController::class, 'queueChange']);
        Route::post('/queue/batch', [OfflineController::class, 'queueBatch']);
        Route::get('/pending', [OfflineController::class, 'pending']);
        Route::get('/history', [OfflineController::class, 'history']);

        // Sync operations
        Route::post('/sync', [OfflineController::class, 'sync']);
        Route::get('/status', [OfflineController::class, 'status']);
        Route::get('/storage', [OfflineController::class, 'storage']);
        Route::post('/clear', [OfflineController::class, 'clear']);

        // Conflict resolution
        Route::get('/conflicts', [OfflineController::class, 'conflicts']);
        Route::get('/conflicts/{uuid}', [OfflineController::class, 'showConflict']);
        Route::post('/conflicts/{uuid}/resolve', [OfflineController::class, 'resolveConflict']);

        // Individual sync items
        Route::post('/{uuid}/retry', [OfflineController::class, 'retry']);
        Route::delete('/{uuid}', [OfflineController::class, 'cancel']);
    });

    // Admin Routes
    Route::prefix('admin')->middleware(['role:admin', 'log.admin'])->group(function () {
        Route::apiResource('achievements', \App\Http\Controllers\Api\Admin\AchievementController::class);

        // Audit Logs (requires audit.view permission)
        Route::prefix('audit-logs')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'index']);
            Route::get('/stats', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'stats']);
            Route::get('/filter-options', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'filterOptions']);
            Route::get('/verify-chain', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'verifyChain']);
            Route::get('/export', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'export']);
            Route::post('/clear-old', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'clearOld']);
            Route::get('/{id}', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'show']);
        });

        // Site Analytics (requires analytics.view permission)
        Route::prefix('analytics')->group(function () {
            Route::get('/dashboard', [SiteAnalyticsController::class, 'dashboard']);
            Route::get('/page-views', [SiteAnalyticsController::class, 'pageViews']);
            Route::get('/feature-usage', [SiteAnalyticsController::class, 'featureUsage']);
            Route::get('/sessions', [SiteAnalyticsController::class, 'sessions']);
            Route::get('/user-flows', [SiteAnalyticsController::class, 'userFlows']);
            Route::get('/search-analytics', [SiteAnalyticsController::class, 'searchAnalytics']);
            Route::get('/top-pages', [SiteAnalyticsController::class, 'topPages']);
            Route::get('/hourly-distribution', [SiteAnalyticsController::class, 'hourlyDistribution']);
            Route::get('/daily-trends', [SiteAnalyticsController::class, 'dailyTrends']);
            Route::get('/device-stats', [SiteAnalyticsController::class, 'deviceStats']);
            Route::get('/export', [SiteAnalyticsController::class, 'export']);
        });

        // A/B Testing Experiments (requires experiments.view/manage permission)
        Route::prefix('experiments')->group(function () {
            Route::get('/', [ABTestingController::class, 'index']);
            Route::post('/', [ABTestingController::class, 'store']);
            Route::get('/stats', [ABTestingController::class, 'stats']);
            Route::get('/locations', [ABTestingController::class, 'locations']);
            Route::get('/goals', [ABTestingController::class, 'goals']);
            Route::get('/{experiment}', [ABTestingController::class, 'show']);
            Route::put('/{experiment}', [ABTestingController::class, 'update']);
            Route::delete('/{experiment}', [ABTestingController::class, 'destroy']);
            Route::post('/{experiment}/start', [ABTestingController::class, 'start']);
            Route::post('/{experiment}/pause', [ABTestingController::class, 'pause']);
            Route::post('/{experiment}/complete', [ABTestingController::class, 'complete']);
            Route::post('/{experiment}/calculate-results', [ABTestingController::class, 'calculateResults']);
            Route::get('/{experiment}/results', [ABTestingController::class, 'results']);
            Route::get('/{experiment}/daily-stats', [ABTestingController::class, 'dailyStats']);

            // Variant management
            Route::post('/{experiment}/variants', [ABTestingController::class, 'addVariant']);
            Route::put('/{experiment}/variants/{variant}', [ABTestingController::class, 'updateVariant']);
            Route::delete('/{experiment}/variants/{variant}', [ABTestingController::class, 'deleteVariant']);
        });

        // CAPTCHA Settings
        Route::prefix('captcha')->group(function () {
            Route::get('/', [CaptchaSettingsController::class, 'index']);
            Route::put('/', [CaptchaSettingsController::class, 'update']);
            Route::post('/test', [CaptchaSettingsController::class, 'test']);
        });

        // Email Templates Management
        Route::prefix('email-templates')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Admin\EmailTemplateController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\Admin\EmailTemplateController::class, 'store']);
            Route::get('/categories', [\App\Http\Controllers\Api\Admin\EmailTemplateController::class, 'categories']);
            Route::get('/stats', [\App\Http\Controllers\Api\Admin\EmailTemplateController::class, 'stats']);
            Route::get('/logs', [\App\Http\Controllers\Api\Admin\EmailTemplateController::class, 'logs']);
            Route::get('/unsubscribes', [\App\Http\Controllers\Api\Admin\EmailTemplateController::class, 'unsubscribes']);
            Route::get('/{template}', [\App\Http\Controllers\Api\Admin\EmailTemplateController::class, 'show']);
            Route::put('/{template}', [\App\Http\Controllers\Api\Admin\EmailTemplateController::class, 'update']);
            Route::delete('/{template}', [\App\Http\Controllers\Api\Admin\EmailTemplateController::class, 'destroy']);
            Route::get('/{template}/preview', [\App\Http\Controllers\Api\Admin\EmailTemplateController::class, 'preview']);
            Route::post('/{template}/test', [\App\Http\Controllers\Api\Admin\EmailTemplateController::class, 'sendTest']);
            Route::post('/{template}/duplicate', [\App\Http\Controllers\Api\Admin\EmailTemplateController::class, 'duplicate']);
            Route::post('/{template}/reset', [\App\Http\Controllers\Api\Admin\EmailTemplateController::class, 'reset']);
        });
    });

    // User Email Preferences
    Route::prefix('email-preferences')->group(function () {
        Route::get('/', [\App\Http\Controllers\EmailUnsubscribeController::class, 'preferences']);
        Route::put('/', [\App\Http\Controllers\EmailUnsubscribeController::class, 'updatePreferences']);
        Route::post('/resubscribe', [\App\Http\Controllers\EmailUnsubscribeController::class, 'resubscribe']);
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
        // Admin operations for conflicts
    });
});

// ===================================
// PUBLIC ROUTES (No Authentication)
// ===================================

// Events (Public Access)
Route::prefix('events')->middleware('throttle:public')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::get('/{uuid}', [EventController::class, 'show']);
    Route::get('/{uuid}/history', [EventController::class, 'history']);
});

// Equipment (Public Access)
Route::prefix('equipment')->middleware('throttle:public')->group(function () {
    Route::get('/', [MilitaryEquipmentController::class, 'index']);
    Route::get('/categories', [MilitaryEquipmentController::class, 'categories']);
    Route::get('/autocomplete', [MilitaryEquipmentController::class, 'autocomplete']);
    Route::get('/stats', [MilitaryEquipmentController::class, 'stats']);
    Route::get('/by-country/{countryId}', [MilitaryEquipmentController::class, 'byCountry']);
    Route::get('/{id}', [MilitaryEquipmentController::class, 'show']);
    Route::get('/{uuid}/events', [EquipmentController::class, 'events']);
});

// Actors (Public Access)
Route::prefix('actors')->middleware('throttle:public')->group(function () {
    Route::get('/', [ActorController::class, 'index']);
    Route::get('/autocomplete', [ActorController::class, 'autocomplete']);
    Route::get('/search', [ActorController::class, 'search']);
    Route::get('/{id}', [ActorController::class, 'show']);
});

// Conflicts (Public Access)
Route::prefix('conflicts')->middleware('throttle:public')->group(function () {
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

// Public tip submission - strict rate limiting to prevent spam
Route::prefix('tips')->middleware('throttle:tips')->group(function () {
    Route::post('/submit', [TipController::class, 'submit'])->middleware('captcha');
    Route::get('/types', [TipController::class, 'types']);
    Route::get('/status/{uuid}', [TipController::class, 'status']);
});

// Public user activity feed (for homepage)
Route::prefix('activity')->middleware('throttle:public')->group(function () {
    Route::get('/feed', [UserActivityController::class, 'publicFeed']);
    Route::get('/stats', [UserActivityController::class, 'stats']);
    Route::get('/types', [UserActivityController::class, 'types']);
});

// A/B Testing public endpoints (for visitors to get variants and track conversions)
Route::prefix('ab')->middleware('throttle:public')->group(function () {
    Route::get('/variant/{location}', [ABTestingPublicController::class, 'getVariant']);
    Route::post('/variants', [ABTestingPublicController::class, 'getVariants']);
    Route::post('/conversion/{experimentSlug}', [ABTestingPublicController::class, 'trackConversion']);
    Route::post('/conversion-location/{location}', [ABTestingPublicController::class, 'trackConversionByLocation']);
});

// Analytics tracking public endpoint (for frontend feature tracking)
Route::prefix('track')->middleware('throttle:public')->group(function () {
    Route::post('/feature', [\App\Http\Controllers\Api\AnalyticsTrackingController::class, 'trackFeature']);
    Route::post('/search', [\App\Http\Controllers\Api\AnalyticsTrackingController::class, 'trackSearch']);
    Route::post('/batch', [\App\Http\Controllers\Api\AnalyticsTrackingController::class, 'trackBatch']);
});

// Public conflict information - moderate rate limiting
Route::prefix('public')->middleware('throttle:public')->group(function () {
    Route::get('/conflicts', [ConflictController::class, 'index']);
    Route::get('/conflicts/active', [ConflictController::class, 'active']);
    Route::get('/conflicts/{slug}', [ConflictController::class, 'show']);
});

// Health check endpoints - allow frequent checks for monitoring
// Public endpoints (no authentication required)
Route::prefix('health')->middleware('throttle:health')->group(function () {
    Route::get('/', [HealthController::class, 'health']);
    Route::get('/live', [HealthController::class, 'liveness']);
    Route::get('/ready', [HealthController::class, 'readiness']);
});

// Protected health endpoints (require authentication)
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('health')->group(function () {
    Route::get('/full', [HealthController::class, 'healthFull']);
    Route::get('/metrics', [HealthController::class, 'metrics']);
    Route::get('/history', [HealthController::class, 'history']);
    Route::get('/statistics', [HealthController::class, 'statistics']);
    Route::get('/system', [HealthController::class, 'systemMetrics']);
    Route::get('/types', [HealthController::class, 'types']);
    Route::get('/component/{component}', [HealthController::class, 'healthComponent']);
    Route::post('/cleanup', [HealthController::class, 'cleanup']);
});
