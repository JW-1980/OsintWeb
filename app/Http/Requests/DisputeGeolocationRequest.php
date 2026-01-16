<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Dispute Geolocation Request
 *
 * Validates data for disputing a geolocation verification.
 */
class DisputeGeolocationRequest extends FormRequest
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
            'reason' => ['required', 'string', 'min:10', 'max:5000'],

            // Evidence supporting the dispute
            'evidence' => ['nullable', 'array'],
            'evidence.*.type' => ['required_with:evidence', 'string', 'in:url,description,coordinates'],
            'evidence.*.value' => ['required_with:evidence', 'string', 'max:2048'],

            // Alternative coordinates if disputing location
            'alternative_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'alternative_longitude' => ['nullable', 'numeric', 'between:-180,180'],
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
            'reason.required' => 'A reason for the dispute is required.',
            'reason.min' => 'Please provide a more detailed reason (at least 10 characters).',
            'reason.max' => 'Dispute reason cannot exceed 5000 characters.',
            'evidence.*.type.in' => 'Evidence type must be url, description, or coordinates.',
            'alternative_latitude.between' => 'Alternative latitude must be between -90 and 90 degrees.',
            'alternative_longitude.between' => 'Alternative longitude must be between -180 and 180 degrees.',
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
            'reason' => 'dispute reason',
            'evidence' => 'supporting evidence',
            'alternative_latitude' => 'alternative latitude',
            'alternative_longitude' => 'alternative longitude',
        ];
    }
}
