<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\GeolocationVerification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Update Geolocation Verification Request
 *
 * Validates data for updating an existing geolocation verification.
 */
class UpdateGeolocationVerificationRequest extends FormRequest
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
            'verification_method' => [
                'sometimes',
                'string',
                Rule::in(GeolocationVerification::getMethods()),
            ],

            // Verified coordinates
            'verified_latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'verified_longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],

            // Image URLs
            'satellite_image_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'ground_image_url' => ['sometimes', 'nullable', 'url', 'max:2048'],

            // Landmark matches
            'landmark_matches' => ['sometimes', 'nullable', 'array'],
            'landmark_matches.*.name' => ['required_with:landmark_matches', 'string', 'max:255'],
            'landmark_matches.*.latitude' => ['required_with:landmark_matches', 'numeric', 'between:-90,90'],
            'landmark_matches.*.longitude' => ['required_with:landmark_matches', 'numeric', 'between:-180,180'],
            'landmark_matches.*.confidence' => ['nullable', 'integer', 'between:0,100'],
            'landmark_matches.*.description' => ['nullable', 'string', 'max:1000'],

            // Shadow analysis data
            'shadow_analysis_data' => ['sometimes', 'nullable', 'array'],
            'shadow_analysis_data.sun_altitude' => ['nullable', 'numeric', 'between:-90,90'],
            'shadow_analysis_data.sun_azimuth' => ['nullable', 'numeric', 'between:0,360'],
            'shadow_analysis_data.estimated_time' => ['nullable', 'string', 'max:255'],
            'shadow_analysis_data.angle_match_confidence' => ['nullable', 'numeric', 'between:0,100'],

            // Metadata
            'metadata' => ['sometimes', 'nullable', 'array'],

            // Notes
            'verification_notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
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
            'verification_method.in' => 'Invalid verification method.',
            'verified_latitude.between' => 'Latitude must be between -90 and 90 degrees.',
            'verified_longitude.between' => 'Longitude must be between -180 and 180 degrees.',
        ];
    }
}
