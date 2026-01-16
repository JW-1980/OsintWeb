<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | OpenRouter API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenRouter.ai API used for OCR, document analysis,
    | entity extraction, and translation services using free LLM models.
    |
    */

    'api_key' => env('OPENROUTER_API_KEY'),
    'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Settings
    |--------------------------------------------------------------------------
    */
    'http' => [
        'timeout' => (int) env('OPENROUTER_TIMEOUT', 120),
        'connect_timeout' => (int) env('OPENROUTER_CONNECT_TIMEOUT', 10),
        'retry_attempts' => (int) env('OPENROUTER_RETRY_ATTEMPTS', 3),
        'retry_delay_ms' => (int) env('OPENROUTER_RETRY_DELAY_MS', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limiting' => [
        'enabled' => env('OPENROUTER_RATE_LIMIT_ENABLED', true),
        'requests_per_minute' => (int) env('OPENROUTER_RATE_LIMIT_RPM', 20),
        'tokens_per_minute' => (int) env('OPENROUTER_RATE_LIMIT_TPM', 100000),
        'cooldown_seconds' => (int) env('OPENROUTER_RATE_LIMIT_COOLDOWN', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Free Models Configuration
    |--------------------------------------------------------------------------
    |
    | These are free-tier models available on OpenRouter.ai.
    | Check https://openrouter.ai/docs#models for the latest free models.
    |
    */
    'free_models' => [
        // Meta Llama models (free)
        'meta-llama/llama-3.2-3b-instruct:free',
        'meta-llama/llama-3.2-1b-instruct:free',
        'meta-llama/llama-3.1-8b-instruct:free',

        // Mistral models (free)
        'mistralai/mistral-7b-instruct:free',

        // Google models (free)
        'google/gemma-2-9b-it:free',

        // Qwen models (free)
        'qwen/qwen-2-7b-instruct:free',

        // Microsoft models (free)
        'microsoft/phi-3-mini-128k-instruct:free',

        // Vision models (free)
        'meta-llama/llama-3.2-11b-vision-instruct:free',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Models by Task
    |--------------------------------------------------------------------------
    */
    'models' => [
        // General chat/text completion
        'chat' => env('OPENROUTER_MODEL_CHAT', 'meta-llama/llama-3.1-8b-instruct:free'),

        // Vision/image analysis (OCR)
        'vision' => env('OPENROUTER_MODEL_VISION', 'meta-llama/llama-3.2-11b-vision-instruct:free'),

        // Translation tasks
        'translation' => env('OPENROUTER_MODEL_TRANSLATION', 'meta-llama/llama-3.1-8b-instruct:free'),

        // Named Entity Recognition (NER)
        'ner' => env('OPENROUTER_MODEL_NER', 'meta-llama/llama-3.1-8b-instruct:free'),

        // Document classification
        'classification' => env('OPENROUTER_MODEL_CLASSIFICATION', 'mistralai/mistral-7b-instruct:free'),

        // Language detection
        'language_detection' => env('OPENROUTER_MODEL_LANG_DETECT', 'meta-llama/llama-3.2-3b-instruct:free'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Parameters
    |--------------------------------------------------------------------------
    */
    'model_params' => [
        'default' => [
            'temperature' => 0.1,
            'max_tokens' => 4096,
            'top_p' => 0.9,
        ],
        'translation' => [
            'temperature' => 0.2,
            'max_tokens' => 8192,
            'top_p' => 0.95,
        ],
        'ocr' => [
            'temperature' => 0.1,
            'max_tokens' => 8192,
            'top_p' => 0.9,
        ],
        'ner' => [
            'temperature' => 0.0,
            'max_tokens' => 2048,
            'top_p' => 0.9,
        ],
        'classification' => [
            'temperature' => 0.0,
            'max_tokens' => 256,
            'top_p' => 0.9,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    |
    | ISO 639-1 language codes and their names for translation support.
    |
    */
    'supported_languages' => [
        'en' => 'English',
        'uk' => 'Ukrainian',
        'ru' => 'Russian',
        'de' => 'German',
        'fr' => 'French',
        'es' => 'Spanish',
        'it' => 'Italian',
        'pt' => 'Portuguese',
        'nl' => 'Dutch',
        'pl' => 'Polish',
        'cs' => 'Czech',
        'sk' => 'Slovak',
        'hu' => 'Hungarian',
        'ro' => 'Romanian',
        'bg' => 'Bulgarian',
        'hr' => 'Croatian',
        'sr' => 'Serbian',
        'sl' => 'Slovenian',
        'et' => 'Estonian',
        'lv' => 'Latvian',
        'lt' => 'Lithuanian',
        'fi' => 'Finnish',
        'sv' => 'Swedish',
        'no' => 'Norwegian',
        'da' => 'Danish',
        'el' => 'Greek',
        'tr' => 'Turkish',
        'ar' => 'Arabic',
        'he' => 'Hebrew',
        'fa' => 'Persian',
        'zh' => 'Chinese',
        'ja' => 'Japanese',
        'ko' => 'Korean',
        'vi' => 'Vietnamese',
        'th' => 'Thai',
        'id' => 'Indonesian',
        'ms' => 'Malay',
        'hi' => 'Hindi',
        'bn' => 'Bengali',
    ],

    /*
    |--------------------------------------------------------------------------
    | OCR Configuration
    |--------------------------------------------------------------------------
    */
    'ocr' => [
        // Use Tesseract for basic OCR (if available)
        'use_tesseract' => env('OCR_USE_TESSERACT', true),
        'tesseract_path' => env('TESSERACT_PATH', '/usr/bin/tesseract'),
        'tesseract_languages' => env('TESSERACT_LANGUAGES', 'eng+ukr+rus'),

        // Fallback to AI for complex/handwritten documents
        'ai_fallback' => env('OCR_AI_FALLBACK', true),

        // Image preprocessing options
        'preprocess' => [
            'enabled' => true,
            'deskew' => true,
            'denoise' => true,
            'enhance_contrast' => true,
        ],

        // Confidence threshold to trigger AI fallback
        'ai_fallback_threshold' => (int) env('OCR_AI_FALLBACK_THRESHOLD', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Processing
    |--------------------------------------------------------------------------
    */
    'documents' => [
        'max_file_size' => (int) env('DOCUMENT_MAX_FILE_SIZE', 20971520), // 20MB
        'max_pages' => (int) env('DOCUMENT_MAX_PAGES', 50),
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'tiff', 'bmp'],
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/tiff',
            'image/bmp',
            'application/pdf',
        ],
        'storage_disk' => 'public',
        'storage_path' => 'documents',
    ],

    /*
    |--------------------------------------------------------------------------
    | Prompts
    |--------------------------------------------------------------------------
    |
    | System prompts used for various AI tasks.
    |
    */
    'prompts' => [
        'ocr' => 'You are an expert OCR system. Extract ALL text from the provided image accurately. Maintain the original structure including paragraphs, lists, and tables. For tables, use markdown table format. If text is unclear, indicate with [unclear]. Output only the extracted text, no explanations.',

        'ocr_handwriting' => 'You are an expert at reading handwritten documents. Carefully extract all handwritten text from this image. Maintain line breaks and paragraph structure. If a word is unclear, provide your best interpretation followed by [?]. Output only the extracted text.',

        'entity_extraction' => 'You are a named entity recognition expert. Extract all named entities from the following text and categorize them. Return a JSON object with these keys: names (person names), dates (dates and times), locations (places, addresses, countries), organizations (companies, institutions, groups). Each should be an array of unique strings. Return ONLY valid JSON, no explanation.',

        'document_classification' => 'You are a document classification expert. Analyze the following text and classify the document type. Choose exactly ONE from: invoice, letter, report, id_document, contract, receipt, form, certificate, news_article, screenshot, handwritten_note, unknown. Return ONLY the classification word, nothing else.',

        'language_detection' => 'You are a language detection expert. Analyze the following text and identify all languages present. Return a JSON array of objects with "language" (ISO 639-1 code) and "confidence" (0-100). Return ONLY valid JSON.',

        'translation' => 'You are a professional translator. Translate the following text from {source_language} to {target_language}. Maintain the original formatting, paragraph structure, and tone. Do not add explanations or notes. Output ONLY the translation.',

        'table_extraction' => 'You are a table extraction expert. Extract all tables from the following text or image. Return a JSON array of tables, each with "headers" (array of column names) and "rows" (array of row arrays). Return ONLY valid JSON.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'HTTP-Referer' => env('APP_URL', 'http://localhost'),
        'X-Title' => 'OsintWeb Document Analysis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('OPENROUTER_CACHE_ENABLED', true),
        'ttl_minutes' => (int) env('OPENROUTER_CACHE_TTL', 60),
        'prefix' => 'openrouter_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('OPENROUTER_LOGGING_ENABLED', true),
        'channel' => env('OPENROUTER_LOG_CHANNEL', 'stack'),
        'log_requests' => env('OPENROUTER_LOG_REQUESTS', false),
        'log_responses' => env('OPENROUTER_LOG_RESPONSES', false),
    ],

];
