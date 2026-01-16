<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\GeolocationVerification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Store Geolocation Verification Request
 *
 * Validates data for creating a new geolocation verification.
 */
class StoreGeolocationVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],

            'verification_method' => [
                'required',
                'string',
                Rule::in(GeolocationVerification::getMethods()),
            ],

            // Verified coordinates (optional - may be determined later)
            'verified_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'verified_longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // Image URLs
            'satellite_image_url' => ['nullable', 'url', 'max:2048'],
            'ground_image_url' => ['nullable', 'url', 'max:2048'],

            // Landmark matches
            'landmark_matches' => ['nullable', 'array'],
            'landmark_matches.*.name' => ['required_with:landmark_matches', 'string', 'max:255'],
            'landmark_matches.*.latitude' => ['required_with:landmark_matches', 'numeric', 'between:-90,90'],
            'landmark_matches.*.longitude' => ['required_with:landmark_matches', 'numeric', 'between:-180,180'],
            'landmark_matches.*.confidence' => ['nullable', 'integer', 'between:0,100'],
            'landmark_matches.*.description' => ['nullable', 'string', 'max:1000'],

            // Shadow analysis data
            'shadow_analysis_data' => ['nullable', 'array'],
            'shadow_analysis_data.sun_altitude' => ['nullable', 'numeric', 'between:-90,90'],
            'shadow_analysis_data.sun_azimuth' => ['nullable', 'numeric', 'between:0,360'],
            'shadow_analysis_data.estimated_time' => ['nullable', 'string', 'max:255'],
            'shadow_analysis_data.angle_match_confidence' => ['nullable', 'numeric', 'between:0,100'],

            // Metadata from image
            'metadata' => ['nullable', 'array'],
            'metadata.gps_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'metadata.gps_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'metadata.date_time_original' => ['nullable', 'string', 'max:255'],
            'metadata.camera_make' => ['nullable', 'string', 'max:255'],
            'metadata.camera_model' => ['nullable', 'string', 'max:255'],

            // Notes
            'verification_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'event_id.required' => 'An event ID is required for verification.',
            'event_id.exists' => 'The specified event does not exist.',
            'verification_method.required' => 'A verification method is required.',
            'verification_method.in' => 'Invalid verification method. Must be one of: ' . implode(', ', GeolocationVerification::getMethods()),
            'verified_latitude.between' => 'Latitude must be between -90 and 90 degrees.',
            'verified_longitude.between' => 'Longitude must be between -180 and 180 degrees.',
            'satellite_image_url.url' => 'Satellite image URL must be a valid URL.',
            'ground_image_url.url' => 'Ground image URL must be a valid URL.',
            'landmark_matches.*.name.required_with' => 'Each landmark must have a name.',
            'landmark_matches.*.latitude.required_with' => 'Each landmark must have coordinates.',
            'landmark_matches.*.longitude.required_with' => 'Each landmark must have coordinates.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'event_id' => 'event',
            'verification_method' => 'verification method',
            'verified_latitude' => 'verified latitude',
            'verified_longitude' => 'verified longitude',
            'satellite_image_url' => 'satellite image URL',
            'ground_image_url' => 'ground image URL',
            'landmark_matches' => 'landmark matches',
            'shadow_analysis_data' => 'shadow analysis data',
            'verification_notes' => 'verification notes',
        ];
    }
}
