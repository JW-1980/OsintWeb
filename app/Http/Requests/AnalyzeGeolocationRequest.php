<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Analyze Geolocation Request
 *
 * Validates data for running geolocation analysis on an image.
 */
class AnalyzeGeolocationRequest extends FormRequest
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
            // Analysis type
            'analysis_type' => [
                'required',
                'string',
                'in:satellite_match,shadow_analysis,metadata_extraction',
            ],

            // Image input (either file or URL)
            'image' => [
                'required_without:image_url',
                'file',
                'mimes:jpeg,jpg,png,gif,webp,tiff',
                'max:20480', // 20MB
            ],
            'image_url' => [
                'required_without:image',
                'url',
                'max:2048',
            ],

            // For satellite matching - search region
            'region' => ['required_if:analysis_type,satellite_match', 'array'],
            'region.sw_lat' => ['required_with:region', 'numeric', 'between:-90,90'],
            'region.sw_lng' => ['required_with:region', 'numeric', 'between:-180,180'],
            'region.ne_lat' => ['required_with:region', 'numeric', 'between:-90,90'],
            'region.ne_lng' => ['required_with:region', 'numeric', 'between:-180,180'],

            // For shadow analysis - date of image
            'date' => ['nullable', 'date', 'before_or_equal:today'],

            // Approximate location (helps narrow down shadow analysis)
            'approximate_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'approximate_longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // Additional options
            'options' => ['nullable', 'array'],
            'options.include_raw_exif' => ['nullable', 'boolean'],
            'options.detailed_shadows' => ['nullable', 'boolean'],
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
            'analysis_type.required' => 'Please specify the type of analysis to perform.',
            'analysis_type.in' => 'Analysis type must be satellite_match, shadow_analysis, or metadata_extraction.',
            'image.required_without' => 'Please provide an image file or image URL.',
            'image.mimes' => 'Image must be a JPEG, PNG, GIF, WebP, or TIFF file.',
            'image.max' => 'Image file size cannot exceed 20MB.',
            'image_url.required_without' => 'Please provide an image file or image URL.',
            'image_url.url' => 'Please provide a valid image URL.',
            'region.required_if' => 'A search region is required for satellite matching.',
            'region.sw_lat.required_with' => 'All region coordinates must be provided.',
            'date.before_or_equal' => 'Date cannot be in the future.',
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
            'analysis_type' => 'analysis type',
            'image' => 'image file',
            'image_url' => 'image URL',
            'region' => 'search region',
            'region.sw_lat' => 'southwest latitude',
            'region.sw_lng' => 'southwest longitude',
            'region.ne_lat' => 'northeast latitude',
            'region.ne_lng' => 'northeast longitude',
            'approximate_latitude' => 'approximate latitude',
            'approximate_longitude' => 'approximate longitude',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If region is provided as flat parameters, restructure
        if ($this->has('sw_lat') && !$this->has('region')) {
            $this->merge([
                'region' => [
                    'sw_lat' => $this->input('sw_lat'),
                    'sw_lng' => $this->input('sw_lng'),
                    'ne_lat' => $this->input('ne_lat'),
                    'ne_lng' => $this->input('ne_lng'),
                ],
            ]);
        }
    }
}
