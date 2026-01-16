<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Verify Geolocation Request
 *
 * Validates data for marking a verification as verified.
 */
class VerifyGeolocationRequest extends FormRequest
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
            'notes' => ['nullable', 'string', 'max:5000'],

            // Optionally update verified coordinates during verification
            'verified_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'verified_longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // Optionally override confidence score
            'confidence_score' => ['nullable', 'integer', 'between:0,100'],
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
            'notes.max' => 'Verification notes cannot exceed 5000 characters.',
            'verified_latitude.between' => 'Latitude must be between -90 and 90 degrees.',
            'verified_longitude.between' => 'Longitude must be between -180 and 180 degrees.',
            'confidence_score.between' => 'Confidence score must be between 0 and 100.',
        ];
    }
}
