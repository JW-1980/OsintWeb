<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\GeolocationVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Geolocation Service
 *
 * Provides methods for verifying and analyzing geolocation data for OSINT events.
 */
class GeolocationService
{
    /**
     * Earth's radius in meters for distance calculations.
     */
    private const EARTH_RADIUS_METERS = 6371000;

    /**
     * Create a new verification for an event.
     *
     * @param int $eventId
     * @param string $method
     * @param array $data
     * @param User|null $user
     * @return GeolocationVerification
     *
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function verifyCoordinates(int $eventId, string $method, array $data, ?User $user = null): GeolocationVerification
    {
        $event = Event::findOrFail($eventId);

        // Validate verification method
        if (!in_array($method, GeolocationVerification::getMethods())) {
            throw new \InvalidArgumentException("Invalid verification method: {$method}");
        }

        // Get original coordinates from event
        $originalLat = $event->latitude;
        $originalLng = $event->longitude;

        if ($originalLat === null || $originalLng === null) {
            throw new \InvalidArgumentException('Event does not have valid coordinates');
        }

        return DB::transaction(function () use ($event, $method, $data, $user, $originalLat, $originalLng) {
            $verification = new GeolocationVerification();
            $verification->uuid = Str::uuid()->toString();
            $verification->event_id = $event->id;
            $verification->verification_method = $method;
            $verification->status = GeolocationVerification::STATUS_PENDING;

            // Set original coordinates using raw SQL for spatial data
            $verification->original_coordinates = DB::raw(
                sprintf("ST_GeomFromText('POINT(%f %f)', 4326)", $originalLng, $originalLat)
            );

            // Set verified coordinates if provided
            if (isset($data['verified_latitude']) && isset($data['verified_longitude'])) {
                $verification->verified_coordinates = DB::raw(
                    sprintf(
                        "ST_GeomFromText('POINT(%f %f)', 4326)",
                        (float) $data['verified_longitude'],
                        (float) $data['verified_latitude']
                    )
                );
            }

            // Set image URLs
            $verification->satellite_image_url = $data['satellite_image_url'] ?? null;
            $verification->ground_image_url = $data['ground_image_url'] ?? null;

            // Set analysis data
            $verification->landmark_matches = $data['landmark_matches'] ?? null;
            $verification->shadow_analysis_data = $data['shadow_analysis_data'] ?? null;
            $verification->metadata = $data['metadata'] ?? null;
            $verification->verification_notes = $data['verification_notes'] ?? null;

            $verification->save();

            // Calculate distance if verified coordinates exist
            if (isset($data['verified_latitude']) && isset($data['verified_longitude'])) {
                $verification->calculateDistanceFromOriginal();
                $verification->save();
            }

            // Calculate initial confidence score
            $verification->calculateConfidence();
            $verification->save();

            // Log the verification attempt
            $this->logVerificationAttempt($verification, $user);

            return $verification->fresh();
        });
    }

    /**
     * Attempt to match satellite imagery with a ground image.
     *
     * This method prepares data for satellite image comparison.
     * Actual image matching would require integration with external services.
     *
     * @param string $groundImagePath Path or URL to the ground image
     * @param array $region Bounding box [sw_lat, sw_lng, ne_lat, ne_lng]
     * @param array $options Additional options for matching
     * @return array Analysis results with potential matches
     */
    public function matchSatelliteImagery(string $groundImagePath, array $region, array $options = []): array
    {
        // Validate region bounds
        if (count($region) !== 4) {
            throw new \InvalidArgumentException('Region must contain [sw_lat, sw_lng, ne_lat, ne_lng]');
        }

        [$swLat, $swLng, $neLat, $neLng] = $region;

        // Calculate search area
        $centerLat = ($swLat + $neLat) / 2;
        $centerLng = ($swLng + $neLng) / 2;

        $searchAreaKm2 = $this->calculateBoundingBoxArea($swLat, $swLng, $neLat, $neLng);

        return [
            'analysis_type' => 'satellite_match',
            'ground_image' => $groundImagePath,
            'search_region' => [
                'southwest' => ['lat' => $swLat, 'lng' => $swLng],
                'northeast' => ['lat' => $neLat, 'lng' => $neLng],
                'center' => ['lat' => $centerLat, 'lng' => $centerLng],
            ],
            'search_area_km2' => round($searchAreaKm2, 2),
            'status' => 'ready_for_processing',
            'recommended_sources' => [
                'sentinel-2' => 'Free, 10m resolution, good for terrain matching',
                'landsat-8' => 'Free, 30m resolution, long historical archive',
                'google-earth' => 'High resolution, requires API access',
                'maxar' => 'Very high resolution, commercial',
            ],
            'processing_notes' => [
                'For best results, ensure the ground image has identifiable landmarks',
                'Consider the image capture date for seasonal vegetation changes',
                'Match distinctive features like road intersections, building shapes, water bodies',
            ],
            'options' => $options,
            'analyzed_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Analyze shadows in an image to estimate location and time.
     *
     * @param string $imagePath Path or URL to the image
     * @param string|null $dateString Estimated date of image capture (YYYY-MM-DD)
     * @param array $options Additional options
     * @return array Shadow analysis results
     */
    public function analyzeShadows(string $imagePath, ?string $dateString = null, array $options = []): array
    {
        $date = $dateString ? Carbon::parse($dateString) : now();

        // Shadow analysis requires image processing
        // This provides the framework and calculations needed
        return [
            'analysis_type' => 'shadow_analysis',
            'image_path' => $imagePath,
            'reference_date' => $date->format('Y-m-d'),
            'methodology' => [
                'step_1' => 'Identify objects with clear shadows (buildings, poles, people)',
                'step_2' => 'Measure shadow length and direction',
                'step_3' => 'Calculate sun altitude from shadow length ratio',
                'step_4' => 'Calculate sun azimuth from shadow direction',
                'step_5' => 'Cross-reference with sun position data for location/time',
            ],
            'required_inputs' => [
                'shadow_length' => 'Length of shadow in image units',
                'object_height' => 'Height of object casting shadow',
                'shadow_direction' => 'Compass direction shadow points (degrees from north)',
                'approximate_latitude' => 'Rough latitude for sun position calculation',
            ],
            'sun_position_formula' => [
                'altitude' => 'atan(object_height / shadow_length) * (180 / PI)',
                'azimuth' => 'shadow_direction + 180 (normalized to 0-360)',
            ],
            'tools' => [
                'suncalc' => 'Calculate sun position for any time/location',
                'timeanddate' => 'Sun position calculator web service',
                'stellarium' => 'Desktop planetarium for sun position verification',
            ],
            'limitations' => [
                'Requires clear shadows with known object heights',
                'Cloud cover affects shadow clarity',
                'Near-equator locations have shorter shadows at noon',
                'Time estimation is most accurate near sunrise/sunset',
            ],
            'options' => $options,
            'analyzed_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Calculate sun position for shadow analysis verification.
     *
     * @param float $latitude
     * @param float $longitude
     * @param Carbon $dateTime
     * @return array Sun position data
     */
    public function calculateSunPosition(float $latitude, float $longitude, Carbon $dateTime): array
    {
        // Julian date calculation
        $jd = $this->toJulianDate($dateTime);

        // Time since J2000.0
        $T = ($jd - 2451545.0) / 36525;

        // Mean longitude of the Sun
        $L0 = fmod(280.46646 + 36000.76983 * $T + 0.0003032 * $T * $T, 360);

        // Mean anomaly of the Sun
        $M = fmod(357.52911 + 35999.05029 * $T - 0.0001537 * $T * $T, 360);
        $M_rad = deg2rad($M);

        // Equation of center
        $C = (1.914602 - 0.004817 * $T - 0.000014 * $T * $T) * sin($M_rad)
            + (0.019993 - 0.000101 * $T) * sin(2 * $M_rad)
            + 0.000289 * sin(3 * $M_rad);

        // Sun's true longitude
        $sunLong = $L0 + $C;

        // Obliquity of the ecliptic
        $eps = 23.439291 - 0.0130042 * $T;
        $eps_rad = deg2rad($eps);

        // Sun's right ascension and declination
        $sunLong_rad = deg2rad($sunLong);
        $ra = atan2(cos($eps_rad) * sin($sunLong_rad), cos($sunLong_rad));
        $dec = asin(sin($eps_rad) * sin($sunLong_rad));

        // Hour angle
        $lst = $this->calculateLocalSiderealTime($longitude, $dateTime);
        $ha = deg2rad($lst - rad2deg($ra));

        // Convert to altitude and azimuth
        $lat_rad = deg2rad($latitude);
        $alt = asin(sin($lat_rad) * sin($dec) + cos($lat_rad) * cos($dec) * cos($ha));
        $az = atan2(
            -sin($ha),
            cos($lat_rad) * tan($dec) - sin($lat_rad) * cos($ha)
        );

        $altitude = rad2deg($alt);
        $azimuth = fmod(rad2deg($az) + 360, 360);

        return [
            'altitude' => round($altitude, 2),
            'azimuth' => round($azimuth, 2),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'datetime' => $dateTime->toIso8601String(),
            'datetime_utc' => $dateTime->utc()->toIso8601String(),
            'is_daytime' => $altitude > 0,
            'solar_noon_distance' => abs(180 - $azimuth),
        ];
    }

    /**
     * Extract metadata from an image file.
     *
     * @param UploadedFile|string $image Image file or path
     * @return array Extracted metadata including GPS coordinates if available
     */
    public function extractImageMetadata($image): array
    {
        $path = $image instanceof UploadedFile ? $image->getPathname() : $image;

        if (!file_exists($path)) {
            return [
                'error' => 'File not found',
                'path' => $path,
            ];
        }

        $metadata = [
            'file_info' => [
                'path' => $path,
                'size' => filesize($path),
                'mime_type' => mime_content_type($path) ?: null,
            ],
            'exif' => null,
            'gps' => null,
            'camera' => null,
            'datetime' => null,
        ];

        // Try to read EXIF data
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($path, 'ANY_TAG', true);

            if ($exif !== false) {
                $metadata['exif'] = $this->sanitizeExifData($exif);

                // Extract GPS coordinates
                if (isset($exif['GPS'])) {
                    $gps = $this->parseGpsFromExif($exif['GPS']);
                    if ($gps) {
                        $metadata['gps'] = $gps;
                    }
                }

                // Extract camera information
                $metadata['camera'] = [
                    'make' => $exif['IFD0']['Make'] ?? null,
                    'model' => $exif['IFD0']['Model'] ?? null,
                    'software' => $exif['IFD0']['Software'] ?? null,
                ];

                // Extract datetime
                $metadata['datetime'] = [
                    'original' => $exif['EXIF']['DateTimeOriginal'] ?? null,
                    'digitized' => $exif['EXIF']['DateTimeDigitized'] ?? null,
                    'modified' => $exif['IFD0']['DateTime'] ?? null,
                    'gps_date' => $exif['GPS']['GPSDateStamp'] ?? null,
                    'gps_time' => isset($exif['GPS']['GPSTimeStamp'])
                        ? $this->parseGpsTimestamp($exif['GPS']['GPSTimeStamp'])
                        : null,
                ];
            }
        }

        // Add image dimensions
        $imageInfo = @getimagesize($path);
        if ($imageInfo !== false) {
            $metadata['dimensions'] = [
                'width' => $imageInfo[0],
                'height' => $imageInfo[1],
            ];
        }

        $metadata['analyzed_at'] = now()->toIso8601String();

        return $metadata;
    }

    /**
     * Calculate the distance between original and verified coordinates.
     *
     * @param float $lat1 Original latitude
     * @param float $lng1 Original longitude
     * @param float $lat2 Verified latitude
     * @param float $lng2 Verified longitude
     * @return float Distance in meters
     */
    public function calculateDistanceFromOriginal(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        return $this->haversineDistance($lat1, $lng1, $lat2, $lng2);
    }

    /**
     * Generate a comprehensive verification report.
     *
     * @param GeolocationVerification $verification
     * @return array Report data
     */
    public function generateVerificationReport(GeolocationVerification $verification): array
    {
        $verification->load(['event', 'verifier']);

        $originalCoords = $verification->getOriginalCoordinatesArray();
        $verifiedCoords = $verification->getVerifiedCoordinatesArray();

        $report = [
            'report_id' => Str::uuid()->toString(),
            'generated_at' => now()->toIso8601String(),
            'verification' => [
                'uuid' => $verification->uuid,
                'status' => $verification->status,
                'method' => $verification->verification_method,
                'method_display' => $verification->getMethodDisplayName(),
                'confidence_score' => $verification->confidence_score,
                'confidence_level' => $verification->getConfidenceLevel(),
                'created_at' => $verification->created_at->toIso8601String(),
                'verified_at' => $verification->verified_at?->toIso8601String(),
            ],
            'event' => [
                'id' => $verification->event->id,
                'uuid' => $verification->event->uuid,
                'title' => $verification->event->title,
                'occurred_at' => $verification->event->occurred_at?->toIso8601String(),
            ],
            'coordinates' => [
                'original' => $originalCoords,
                'verified' => $verifiedCoords,
                'distance_meters' => $verification->distance_from_original,
                'distance_formatted' => $this->formatDistance($verification->distance_from_original),
            ],
            'analysis_data' => [
                'landmark_matches' => $verification->landmark_matches,
                'landmark_count' => $verification->landmark_matches
                    ? count($verification->landmark_matches)
                    : 0,
                'shadow_analysis' => $verification->shadow_analysis_data,
                'metadata' => $verification->metadata,
            ],
            'images' => [
                'satellite_url' => $verification->satellite_image_url,
                'ground_url' => $verification->ground_image_url,
            ],
            'verifier' => $verification->verifier ? [
                'id' => $verification->verifier->id,
                'name' => $verification->verifier->name,
            ] : null,
            'notes' => $verification->verification_notes,
            'assessment' => $this->generateAssessment($verification),
        ];

        return $report;
    }

    /**
     * Get verification statistics for an event.
     *
     * @param int $eventId
     * @return array Statistics
     */
    public function getEventVerificationStats(int $eventId): array
    {
        $verifications = GeolocationVerification::forEvent($eventId)->get();

        if ($verifications->isEmpty()) {
            return [
                'event_id' => $eventId,
                'total_verifications' => 0,
                'has_verified' => false,
            ];
        }

        $byStatus = $verifications->groupBy('status');
        $byMethod = $verifications->groupBy('verification_method');

        return [
            'event_id' => $eventId,
            'total_verifications' => $verifications->count(),
            'has_verified' => $verifications->where('status', GeolocationVerification::STATUS_VERIFIED)->isNotEmpty(),
            'by_status' => [
                'pending' => $byStatus->get(GeolocationVerification::STATUS_PENDING)?->count() ?? 0,
                'verified' => $byStatus->get(GeolocationVerification::STATUS_VERIFIED)?->count() ?? 0,
                'disputed' => $byStatus->get(GeolocationVerification::STATUS_DISPUTED)?->count() ?? 0,
                'rejected' => $byStatus->get(GeolocationVerification::STATUS_REJECTED)?->count() ?? 0,
            ],
            'by_method' => $byMethod->map(fn($group) => $group->count())->toArray(),
            'average_confidence' => round($verifications->avg('confidence_score'), 1),
            'highest_confidence' => $verifications->max('confidence_score'),
            'average_distance' => round(
                $verifications->whereNotNull('distance_from_original')->avg('distance_from_original'),
                2
            ),
        ];
    }

    /**
     * Calculate Haversine distance between two points.
     *
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return float Distance in meters
     */
    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2)
            + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLng / 2) * sin($deltaLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }

    /**
     * Calculate bounding box area in square kilometers.
     *
     * @param float $swLat
     * @param float $swLng
     * @param float $neLat
     * @param float $neLng
     * @return float Area in km2
     */
    private function calculateBoundingBoxArea(float $swLat, float $swLng, float $neLat, float $neLng): float
    {
        // Width at average latitude
        $avgLat = ($swLat + $neLat) / 2;
        $width = $this->haversineDistance($avgLat, $swLng, $avgLat, $neLng) / 1000;

        // Height
        $height = $this->haversineDistance($swLat, $swLng, $neLat, $swLng) / 1000;

        return $width * $height;
    }

    /**
     * Parse GPS coordinates from EXIF data.
     *
     * @param array $gpsData
     * @return array|null
     */
    private function parseGpsFromExif(array $gpsData): ?array
    {
        if (!isset($gpsData['GPSLatitude']) || !isset($gpsData['GPSLongitude'])) {
            return null;
        }

        $latitude = $this->convertGpsCoordinate(
            $gpsData['GPSLatitude'],
            $gpsData['GPSLatitudeRef'] ?? 'N'
        );

        $longitude = $this->convertGpsCoordinate(
            $gpsData['GPSLongitude'],
            $gpsData['GPSLongitudeRef'] ?? 'E'
        );

        if ($latitude === null || $longitude === null) {
            return null;
        }

        return [
            'latitude' => round($latitude, 8),
            'longitude' => round($longitude, 8),
            'altitude' => isset($gpsData['GPSAltitude'])
                ? $this->parseRational($gpsData['GPSAltitude'])
                : null,
            'accuracy' => $gpsData['GPSDOP'] ?? null,
        ];
    }

    /**
     * Convert GPS coordinate from EXIF format to decimal degrees.
     *
     * @param array $coordinate
     * @param string $ref
     * @return float|null
     */
    private function convertGpsCoordinate(array $coordinate, string $ref): ?float
    {
        if (count($coordinate) !== 3) {
            return null;
        }

        $degrees = $this->parseRational($coordinate[0]);
        $minutes = $this->parseRational($coordinate[1]);
        $seconds = $this->parseRational($coordinate[2]);

        if ($degrees === null || $minutes === null || $seconds === null) {
            return null;
        }

        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        if ($ref === 'S' || $ref === 'W') {
            $decimal *= -1;
        }

        return $decimal;
    }

    /**
     * Parse a rational number from EXIF format.
     *
     * @param mixed $value
     * @return float|null
     */
    private function parseRational($value): ?float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value) && str_contains($value, '/')) {
            [$numerator, $denominator] = explode('/', $value);
            if ((float) $denominator !== 0.0) {
                return (float) $numerator / (float) $denominator;
            }
        }

        return null;
    }

    /**
     * Parse GPS timestamp from EXIF format.
     *
     * @param array $timestamp
     * @return string|null
     */
    private function parseGpsTimestamp(array $timestamp): ?string
    {
        if (count($timestamp) !== 3) {
            return null;
        }

        $hours = (int) $this->parseRational($timestamp[0]);
        $minutes = (int) $this->parseRational($timestamp[1]);
        $seconds = (int) $this->parseRational($timestamp[2]);

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Sanitize EXIF data by removing binary data.
     *
     * @param array $exif
     * @return array
     */
    private function sanitizeExifData(array $exif): array
    {
        $sanitized = [];

        foreach ($exif as $section => $data) {
            if (!is_array($data)) {
                continue;
            }

            $sanitized[$section] = [];

            foreach ($data as $key => $value) {
                // Skip binary data and thumbnails
                if (in_array($key, ['THUMBNAIL', 'MakerNote', 'UserComment'])) {
                    continue;
                }

                // Skip if value is too long (likely binary)
                if (is_string($value) && strlen($value) > 1000) {
                    continue;
                }

                $sanitized[$section][$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Convert to Julian Date.
     *
     * @param Carbon $date
     * @return float
     */
    private function toJulianDate(Carbon $date): float
    {
        $y = $date->year;
        $m = $date->month;
        $d = $date->day + ($date->hour + $date->minute / 60 + $date->second / 3600) / 24;

        if ($m <= 2) {
            $y--;
            $m += 12;
        }

        $A = (int) ($y / 100);
        $B = 2 - $A + (int) ($A / 4);

        return (int) (365.25 * ($y + 4716)) + (int) (30.6001 * ($m + 1)) + $d + $B - 1524.5;
    }

    /**
     * Calculate Local Sidereal Time.
     *
     * @param float $longitude
     * @param Carbon $dateTime
     * @return float LST in degrees
     */
    private function calculateLocalSiderealTime(float $longitude, Carbon $dateTime): float
    {
        $jd = $this->toJulianDate($dateTime->utc());
        $T = ($jd - 2451545.0) / 36525;

        // Greenwich Mean Sidereal Time
        $gmst = 280.46061837
            + 360.98564736629 * ($jd - 2451545)
            + 0.000387933 * $T * $T
            - $T * $T * $T / 38710000;

        $gmst = fmod($gmst, 360);
        if ($gmst < 0) {
            $gmst += 360;
        }

        // Local Sidereal Time
        return fmod($gmst + $longitude, 360);
    }

    /**
     * Format distance for display.
     *
     * @param float|null $meters
     * @return string|null
     */
    private function formatDistance(?float $meters): ?string
    {
        if ($meters === null) {
            return null;
        }

        if ($meters < 1000) {
            return round($meters, 1) . ' m';
        }

        return round($meters / 1000, 2) . ' km';
    }

    /**
     * Generate assessment text based on verification data.
     *
     * @param GeolocationVerification $verification
     * @return array
     */
    private function generateAssessment(GeolocationVerification $verification): array
    {
        $assessment = [
            'overall' => 'inconclusive',
            'factors' => [],
            'recommendations' => [],
        ];

        // Assess confidence level
        $confidence = $verification->confidence_score;
        if ($confidence >= GeolocationVerification::CONFIDENCE_HIGH) {
            $assessment['overall'] = 'high_confidence';
            $assessment['factors'][] = 'High confidence score indicates reliable verification';
        } elseif ($confidence >= GeolocationVerification::CONFIDENCE_MEDIUM) {
            $assessment['overall'] = 'moderate_confidence';
            $assessment['factors'][] = 'Moderate confidence - additional verification recommended';
        } else {
            $assessment['overall'] = 'low_confidence';
            $assessment['factors'][] = 'Low confidence - requires additional verification methods';
            $assessment['recommendations'][] = 'Consider using additional verification methods';
        }

        // Assess distance
        if ($verification->distance_from_original !== null) {
            $distance = $verification->distance_from_original;
            if ($distance < 50) {
                $assessment['factors'][] = 'Verified location very close to original (< 50m)';
            } elseif ($distance < 500) {
                $assessment['factors'][] = 'Verified location within reasonable proximity (< 500m)';
            } else {
                $assessment['factors'][] = 'Significant distance between original and verified location';
                $assessment['recommendations'][] = 'Review original coordinates for potential errors';
            }
        }

        // Assess method-specific factors
        switch ($verification->verification_method) {
            case GeolocationVerification::METHOD_METADATA_EXTRACTION:
                if ($verification->metadata && isset($verification->metadata['gps'])) {
                    $assessment['factors'][] = 'GPS coordinates extracted from image metadata';
                }
                break;

            case GeolocationVerification::METHOD_LANDMARK_MATCH:
                $landmarkCount = $verification->landmark_matches
                    ? count($verification->landmark_matches)
                    : 0;
                if ($landmarkCount >= 3) {
                    $assessment['factors'][] = "Multiple landmarks matched ({$landmarkCount})";
                } elseif ($landmarkCount > 0) {
                    $assessment['recommendations'][] = 'Identify additional landmarks for stronger verification';
                }
                break;

            case GeolocationVerification::METHOD_SHADOW_ANALYSIS:
                if ($verification->shadow_analysis_data) {
                    $assessment['factors'][] = 'Shadow analysis data available';
                    if (!isset($verification->shadow_analysis_data['estimated_time'])) {
                        $assessment['recommendations'][] = 'Complete time estimation from shadow analysis';
                    }
                }
                break;
        }

        return $assessment;
    }

    /**
     * Log verification attempt to audit log.
     *
     * @param GeolocationVerification $verification
     * @param User|null $user
     * @return void
     */
    private function logVerificationAttempt(GeolocationVerification $verification, ?User $user): void
    {
        AuditLog::create([
            'user_id' => $user?->id,
            'action' => 'geolocation.verification.created',
            'auditable_type' => GeolocationVerification::class,
            'auditable_id' => $verification->id,
            'properties' => [
                'event_id' => $verification->event_id,
                'method' => $verification->verification_method,
                'confidence_score' => $verification->confidence_score,
            ],
        ]);
    }
}
