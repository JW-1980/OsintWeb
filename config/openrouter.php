<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | AI Provider Selection
    |--------------------------------------------------------------------------
    |
    | Choose the AI inference provider for OCR, document analysis, entity
    | extraction, and translation. All providers use OpenAI-compatible APIs.
    |
    | Supported: "openrouter", "huggingface", "aimlapi"
    |
    */

    'provider' => env('AI_PROVIDER', 'openrouter'),

    /*
    |--------------------------------------------------------------------------
    | Provider Configurations
    |--------------------------------------------------------------------------
    |
    | Each provider requires an API key and base URL. All providers use the
    | same OpenAI-compatible /v1/chat/completions endpoint format.
    |
    */

    'providers' => [
        'openrouter' => [
            'name' => 'OpenRouter',
            'api_key' => env('OPENROUTER_API_KEY'),
            'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
            'description' => 'Access 100+ models including free-tier Llama, Mistral, and Gemma models.',
            'docs_url' => 'https://openrouter.ai/',
            'models' => [
                'chat' => env('OPENROUTER_MODEL_CHAT', 'meta-llama/llama-3.1-8b-instruct:free'),
                'vision' => env('OPENROUTER_MODEL_VISION', 'meta-llama/llama-3.2-11b-vision-instruct:free'),
                'translation' => env('OPENROUTER_MODEL_TRANSLATION', 'meta-llama/llama-3.1-8b-instruct:free'),
                'ner' => env('OPENROUTER_MODEL_NER', 'meta-llama/llama-3.1-8b-instruct:free'),
                'classification' => env('OPENROUTER_MODEL_CLASSIFICATION', 'mistralai/mistral-7b-instruct:free'),
                'language_detection' => env('OPENROUTER_MODEL_LANG_DETECT', 'meta-llama/llama-3.2-3b-instruct:free'),
            ],
            'free_models' => [
                'meta-llama/llama-3.2-3b-instruct:free',
                'meta-llama/llama-3.2-1b-instruct:free',
                'meta-llama/llama-3.1-8b-instruct:free',
                'mistralai/mistral-7b-instruct:free',
                'google/gemma-2-9b-it:free',
                'qwen/qwen-2-7b-instruct:free',
                'microsoft/phi-3-mini-128k-instruct:free',
                'meta-llama/llama-3.2-11b-vision-instruct:free',
            ],
            'headers' => [
                'HTTP-Referer' => env('APP_URL', 'http://localhost'),
                'X-Title' => 'OsintWeb Document Analysis',
            ],
        ],

        'huggingface' => [
            'name' => 'Hugging Face',
            'api_key' => env('HUGGINGFACE_API_KEY'),
            'base_url' => env('HUGGINGFACE_BASE_URL', 'https://router.huggingface.co/v1'),
            'description' => 'Access open-source models via Hugging Face Inference Providers.',
            'docs_url' => 'https://huggingface.co/docs/api-inference/',
            'models' => [
                'chat' => env('HUGGINGFACE_MODEL_CHAT', 'meta-llama/Llama-3.1-8B-Instruct'),
                'vision' => env('HUGGINGFACE_MODEL_VISION', 'meta-llama/Llama-3.2-11B-Vision-Instruct'),
                'translation' => env('HUGGINGFACE_MODEL_TRANSLATION', 'meta-llama/Llama-3.1-8B-Instruct'),
                'ner' => env('HUGGINGFACE_MODEL_NER', 'meta-llama/Llama-3.1-8B-Instruct'),
                'classification' => env('HUGGINGFACE_MODEL_CLASSIFICATION', 'mistralai/Mistral-7B-Instruct-v0.3'),
                'language_detection' => env('HUGGINGFACE_MODEL_LANG_DETECT', 'meta-llama/Llama-3.2-3B-Instruct'),
            ],
            'free_models' => [
                'meta-llama/Llama-3.2-3B-Instruct',
                'meta-llama/Llama-3.1-8B-Instruct',
                'meta-llama/Llama-3.2-11B-Vision-Instruct',
                'mistralai/Mistral-7B-Instruct-v0.3',
                'Qwen/Qwen2.5-7B-Instruct',
                'HuggingFaceTB/SmolLM2-1.7B-Instruct',
                'deepseek-ai/DeepSeek-V3',
            ],
            'headers' => [],
        ],

        'aimlapi' => [
            'name' => 'AIML API',
            'api_key' => env('AIMLAPI_API_KEY'),
            'base_url' => env('AIMLAPI_BASE_URL', 'https://api.aimlapi.com/v1'),
            'description' => 'Access 400+ AI models with a free tier including DeepSeek, Llama, and Mistral.',
            'docs_url' => 'https://docs.aimlapi.com/',
            'models' => [
                'chat' => env('AIMLAPI_MODEL_CHAT', 'meta-llama/Llama-3.3-70B-Instruct-Turbo'),
                'vision' => env('AIMLAPI_MODEL_VISION', 'meta-llama/Llama-3.2-11B-Vision-Instruct-Turbo'),
                'translation' => env('AIMLAPI_MODEL_TRANSLATION', 'meta-llama/Llama-3.3-70B-Instruct-Turbo'),
                'ner' => env('AIMLAPI_MODEL_NER', 'meta-llama/Llama-3.3-70B-Instruct-Turbo'),
                'classification' => env('AIMLAPI_MODEL_CLASSIFICATION', 'mistralai/Mistral-7B-Instruct-v0.3'),
                'language_detection' => env('AIMLAPI_MODEL_LANG_DETECT', 'meta-llama/Llama-3.2-3B-Instruct-Turbo'),
            ],
            'free_models' => [
                'meta-llama/Llama-3.3-70B-Instruct-Turbo',
                'meta-llama/Llama-3.2-3B-Instruct-Turbo',
                'meta-llama/Llama-3.2-11B-Vision-Instruct-Turbo',
                'mistralai/Mistral-7B-Instruct-v0.3',
                'deepseek-chat',
                'Qwen/Qwen2.5-7B-Instruct-Turbo',
            ],
            'headers' => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy Keys (backward compatibility)
    |--------------------------------------------------------------------------
    |
    | These are resolved at runtime from the active provider. Kept here so
    | existing code referencing config('openrouter.api_key') still works.
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
    | Active Provider Models (resolved at runtime)
    |--------------------------------------------------------------------------
    |
    | These are populated from the active provider config in the service.
    | The env vars below serve as fallback when accessed via config() directly.
    |
    */
    'free_models' => [],
    'models' => [
        'chat' => env('OPENROUTER_MODEL_CHAT', 'meta-llama/llama-3.1-8b-instruct:free'),
        'vision' => env('OPENROUTER_MODEL_VISION', 'meta-llama/llama-3.2-11b-vision-instruct:free'),
        'translation' => env('OPENROUTER_MODEL_TRANSLATION', 'meta-llama/llama-3.1-8b-instruct:free'),
        'ner' => env('OPENROUTER_MODEL_NER', 'meta-llama/llama-3.1-8b-instruct:free'),
        'classification' => env('OPENROUTER_MODEL_CLASSIFICATION', 'mistralai/mistral-7b-instruct:free'),
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
