<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Geolocation Verification Model
 *
 * Represents a verification attempt for an event's location using various OSINT techniques.
 *
 * @property int $id
 * @property string $uuid
 * @property int $event_id
 * @property mixed $original_coordinates
 * @property mixed|null $verified_coordinates
 * @property string $verification_method
 * @property int $confidence_score
 * @property string|null $satellite_image_url
 * @property string|null $ground_image_url
 * @property array|null $landmark_matches
 * @property array|null $shadow_analysis_data
 * @property array|null $metadata
 * @property string|null $verification_notes
 * @property int|null $verified_by
 * @property Carbon|null $verified_at
 * @property string $status
 * @property float|null $distance_from_original
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read Event $event
 * @property-read User|null $verifier
 */
class GeolocationVerification extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Verification methods
    public const METHOD_SATELLITE_MATCH = 'satellite_match';
    public const METHOD_LANDMARK_MATCH = 'landmark_match';
    public const METHOD_SHADOW_ANALYSIS = 'shadow_analysis';
    public const METHOD_METADATA_EXTRACTION = 'metadata_extraction';
    public const METHOD_CROWD_VERIFIED = 'crowd_verified';

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_DISPUTED = 'disputed';
    public const STATUS_REJECTED = 'rejected';

    // Confidence thresholds
    public const CONFIDENCE_LOW = 30;
    public const CONFIDENCE_MEDIUM = 60;
    public const CONFIDENCE_HIGH = 85;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'event_id',
        'original_coordinates',
        'verified_coordinates',
        'verification_method',
        'confidence_score',
        'satellite_image_url',
        'ground_image_url',
        'landmark_matches',
        'shadow_analysis_data',
        'metadata',
        'verification_notes',
        'verified_by',
        'verified_at',
        'status',
        'distance_from_original',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'confidence_score' => 'integer',
        'landmark_matches' => 'array',
        'shadow_analysis_data' => 'array',
        'metadata' => 'array',
        'verified_at' => 'datetime',
        'distance_from_original' => 'decimal:2',
    ];

    /**
     * Get the event that this verification belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who verified this geolocation.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get all available verification methods.
     *
     * @return array<string>
     */
    public static function getMethods(): array
    {
        return [
            self::METHOD_SATELLITE_MATCH,
            self::METHOD_LANDMARK_MATCH,
            self::METHOD_SHADOW_ANALYSIS,
            self::METHOD_METADATA_EXTRACTION,
            self::METHOD_CROWD_VERIFIED,
        ];
    }

    /**
     * Get all available statuses.
     *
     * @return array<string>
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_VERIFIED,
            self::STATUS_DISPUTED,
            self::STATUS_REJECTED,
        ];
    }

    /**
     * Scope to filter by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter pending verifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter verified records.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    /**
     * Scope to filter disputed records.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisputed($query)
    {
        return $query->where('status', self::STATUS_DISPUTED);
    }

    /**
     * Scope to filter rejected records.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope to filter by verification method.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $method
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMethod($query, string $method)
    {
        return $query->where('verification_method', $method);
    }

    /**
     * Scope to filter by minimum confidence score.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $minScore
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMinConfidence($query, int $minScore)
    {
        return $query->where('confidence_score', '>=', $minScore);
    }

    /**
     * Scope to filter high confidence verifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighConfidence($query)
    {
        return $query->where('confidence_score', '>=', self::CONFIDENCE_HIGH);
    }

    /**
     * Scope to filter verifications within a radius of a point.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float $latitude
     * @param float $longitude
     * @param float $radiusKm
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithinRadius($query, float $latitude, float $longitude, float $radiusKm)
    {
        return $query->whereRaw(
            'ST_Distance_Sphere(original_coordinates, ST_GeomFromText(?, 4326)) <= ?',
            ["POINT({$longitude} {$latitude})", $radiusKm * 1000]
        );
    }

    /**
     * Scope to filter verifications within a bounding box.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float $swLat Southwest latitude
     * @param float $swLng Southwest longitude
     * @param float $neLat Northeast latitude
     * @param float $neLng Northeast longitude
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithinBounds($query, float $swLat, float $swLng, float $neLat, float $neLng)
    {
        $polygon = "POLYGON(({$swLng} {$swLat}, {$neLng} {$swLat}, {$neLng} {$neLat}, {$swLng} {$neLat}, {$swLng} {$swLat}))";

        return $query->whereRaw(
            'ST_Contains(ST_GeomFromText(?, 4326), original_coordinates)',
            [$polygon]
        );
    }

    /**
     * Scope to filter by event.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $eventId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope to filter by verifier.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerifiedBy($query, int $userId)
    {
        return $query->where('verified_by', $userId);
    }

    /**
     * Calculate and update the confidence score based on various factors.
     *
     * @return int The calculated confidence score
     */
    public function calculateConfidence(): int
    {
        $score = 0;
        $weights = [];

        // Method-specific base scores
        $methodScores = [
            self::METHOD_SATELLITE_MATCH => 40,
            self::METHOD_LANDMARK_MATCH => 35,
            self::METHOD_SHADOW_ANALYSIS => 30,
            self::METHOD_METADATA_EXTRACTION => 45,
            self::METHOD_CROWD_VERIFIED => 25,
        ];

        $score += $methodScores[$this->verification_method] ?? 20;

        // Landmark matches contribute to confidence
        if (!empty($this->landmark_matches) && is_array($this->landmark_matches)) {
            $landmarkCount = count($this->landmark_matches);
            $avgLandmarkConfidence = 0;

            foreach ($this->landmark_matches as $landmark) {
                $avgLandmarkConfidence += ($landmark['confidence'] ?? 50);
            }

            if ($landmarkCount > 0) {
                $avgLandmarkConfidence /= $landmarkCount;
                // Up to 20 points for landmarks
                $score += min(20, ($landmarkCount * 5) * ($avgLandmarkConfidence / 100));
            }
        }

        // Shadow analysis contributes to confidence
        if (!empty($this->shadow_analysis_data) && is_array($this->shadow_analysis_data)) {
            $shadowData = $this->shadow_analysis_data;

            // If sun angle matches expected, add points
            if (isset($shadowData['angle_match_confidence'])) {
                $score += (int) ($shadowData['angle_match_confidence'] * 0.15);
            }

            // If time estimation is present, add points
            if (isset($shadowData['estimated_time'])) {
                $score += 5;
            }
        }

        // Metadata extraction (EXIF) confidence boost
        if (!empty($this->metadata) && is_array($this->metadata)) {
            // GPS data from EXIF is very reliable
            if (isset($this->metadata['gps_latitude']) && isset($this->metadata['gps_longitude'])) {
                $score += 15;
            }

            // Timestamp data adds confidence
            if (isset($this->metadata['date_time_original'])) {
                $score += 5;
            }

            // Camera make/model adds slight confidence (helps identify authenticity)
            if (isset($this->metadata['camera_make'])) {
                $score += 2;
            }
        }

        // Distance penalty: if verified coords differ significantly from original
        if ($this->distance_from_original !== null) {
            if ($this->distance_from_original > 1000) {
                // More than 1km difference - significant penalty
                $score -= 15;
            } elseif ($this->distance_from_original > 100) {
                // More than 100m difference - moderate penalty
                $score -= 5;
            }
        }

        // Ensure score is within bounds
        $score = max(0, min(100, $score));

        $this->confidence_score = $score;

        return $score;
    }

    /**
     * Get the original coordinates as an array [lat, lng].
     *
     * @return array|null
     */
    public function getOriginalCoordinatesArray(): ?array
    {
        if (!$this->original_coordinates) {
            return null;
        }

        $result = DB::selectOne(
            'SELECT ST_X(original_coordinates) as lng, ST_Y(original_coordinates) as lat FROM geolocation_verifications WHERE id = ?',
            [$this->id]
        );

        if (!$result) {
            return null;
        }

        return [
            'lat' => (float) $result->lat,
            'lng' => (float) $result->lng,
        ];
    }

    /**
     * Get the verified coordinates as an array [lat, lng].
     *
     * @return array|null
     */
    public function getVerifiedCoordinatesArray(): ?array
    {
        if (!$this->verified_coordinates) {
            return null;
        }

        $result = DB::selectOne(
            'SELECT ST_X(verified_coordinates) as lng, ST_Y(verified_coordinates) as lat FROM geolocation_verifications WHERE id = ?',
            [$this->id]
        );

        if (!$result || $result->lat === null) {
            return null;
        }

        return [
            'lat' => (float) $result->lat,
            'lng' => (float) $result->lng,
        ];
    }

    /**
     * Set the original coordinates from latitude and longitude.
     *
     * @param float $latitude
     * @param float $longitude
     * @return void
     */
    public function setOriginalCoordinates(float $latitude, float $longitude): void
    {
        $this->original_coordinates = DB::raw(
            sprintf("ST_GeomFromText('POINT(%f %f)', 4326)", $longitude, $latitude)
        );
    }

    /**
     * Set the verified coordinates from latitude and longitude.
     *
     * @param float $latitude
     * @param float $longitude
     * @return void
     */
    public function setVerifiedCoordinates(float $latitude, float $longitude): void
    {
        $this->verified_coordinates = DB::raw(
            sprintf("ST_GeomFromText('POINT(%f %f)', 4326)", $longitude, $latitude)
        );
    }

    /**
     * Calculate and store the distance between original and verified coordinates.
     *
     * @return float|null Distance in meters
     */
    public function calculateDistanceFromOriginal(): ?float
    {
        if (!$this->verified_coordinates) {
            return null;
        }

        $result = DB::selectOne(
            'SELECT ST_Distance_Sphere(original_coordinates, verified_coordinates) as distance FROM geolocation_verifications WHERE id = ?',
            [$this->id]
        );

        if (!$result) {
            return null;
        }

        $this->distance_from_original = (float) $result->distance;

        return $this->distance_from_original;
    }

    /**
     * Mark this verification as verified.
     *
     * @param User $verifier
     * @param string|null $notes
     * @return void
     */
    public function markAsVerified(User $verifier, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_VERIFIED,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
            'verification_notes' => $notes ?? $this->verification_notes,
        ]);
    }

    /**
     * Mark this verification as disputed.
     *
     * @param string|null $reason
     * @return void
     */
    public function markAsDisputed(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_DISPUTED,
            'verification_notes' => $reason ?? $this->verification_notes,
        ]);
    }

    /**
     * Mark this verification as rejected.
     *
     * @param User $rejector
     * @param string|null $reason
     * @return void
     */
    public function markAsRejected(User $rejector, ?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'verified_by' => $rejector->id,
            'verified_at' => now(),
            'verification_notes' => $reason ?? $this->verification_notes,
        ]);
    }

    /**
     * Check if this verification is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if this verification is verified.
     *
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    /**
     * Check if this verification is disputed.
     *
     * @return bool
     */
    public function isDisputed(): bool
    {
        return $this->status === self::STATUS_DISPUTED;
    }

    /**
     * Check if this verification is rejected.
     *
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Get the confidence level label.
     *
     * @return string
     */
    public function getConfidenceLevel(): string
    {
        if ($this->confidence_score >= self::CONFIDENCE_HIGH) {
            return 'high';
        }

        if ($this->confidence_score >= self::CONFIDENCE_MEDIUM) {
            return 'medium';
        }

        if ($this->confidence_score >= self::CONFIDENCE_LOW) {
            return 'low';
        }

        return 'very_low';
    }

    /**
     * Get verification method display name.
     *
     * @return string
     */
    public function getMethodDisplayName(): string
    {
        $names = [
            self::METHOD_SATELLITE_MATCH => 'Satellite Imagery Match',
            self::METHOD_LANDMARK_MATCH => 'Landmark Matching',
            self::METHOD_SHADOW_ANALYSIS => 'Shadow Analysis',
            self::METHOD_METADATA_EXTRACTION => 'Image Metadata (EXIF)',
            self::METHOD_CROWD_VERIFIED => 'Crowd Verification',
        ];

        return $names[$this->verification_method] ?? ucfirst(str_replace('_', ' ', $this->verification_method));
    }

    /**
     * Add a landmark match to the verification.
     *
     * @param string $name
     * @param float $latitude
     * @param float $longitude
     * @param int $confidence
     * @param string|null $description
     * @return void
     */
    public function addLandmarkMatch(
        string $name,
        float $latitude,
        float $longitude,
        int $confidence = 50,
        ?string $description = null
    ): void {
        $matches = $this->landmark_matches ?? [];

        $matches[] = [
            'name' => $name,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'confidence' => max(0, min(100, $confidence)),
            'description' => $description,
            'matched_at' => now()->toIso8601String(),
        ];

        $this->landmark_matches = $matches;
    }

    /**
     * Set shadow analysis data.
     *
     * @param float $sunAltitude Sun altitude angle in degrees
     * @param float $sunAzimuth Sun azimuth angle in degrees
     * @param string|null $estimatedTime Estimated time based on shadow analysis
     * @param float|null $angleMatchConfidence How well the angles match (0-100)
     * @return void
     */
    public function setShadowAnalysis(
        float $sunAltitude,
        float $sunAzimuth,
        ?string $estimatedTime = null,
        ?float $angleMatchConfidence = null
    ): void {
        $this->shadow_analysis_data = [
            'sun_altitude' => $sunAltitude,
            'sun_azimuth' => $sunAzimuth,
            'estimated_time' => $estimatedTime,
            'angle_match_confidence' => $angleMatchConfidence,
            'analyzed_at' => now()->toIso8601String(),
        ];
    }
}
