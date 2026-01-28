<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Inference Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Multi-provider AI inference configuration supporting OpenAI-compatible
    | endpoints. All providers offer free tiers for cost-effective usage.
    |
    */

    'default_provider' => env('INFERENCE_DEFAULT_PROVIDER', 'openrouter'),

    /*
    |--------------------------------------------------------------------------
    | Available Providers
    |--------------------------------------------------------------------------
    |
    | Each provider uses OpenAI-compatible endpoints for seamless switching.
    | All listed providers offer free tiers with generous limits.
    |
    */

    'providers' => [

        /*
        |----------------------------------------------------------------------
        | OpenRouter - Multi-Model Gateway
        |----------------------------------------------------------------------
        | Website: https://openrouter.ai/
        | Free tier: Access to multiple free models
        | Features: Model aggregation, automatic fallbacks
        */
        'openrouter' => [
            'name' => 'OpenRouter',
            'enabled' => env('OPENROUTER_ENABLED', true),
            'api_key' => env('OPENROUTER_API_KEY'),
            'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
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
            'rate_limits' => [
                'requests_per_minute' => (int) env('OPENROUTER_RATE_LIMIT_RPM', 20),
                'tokens_per_minute' => (int) env('OPENROUTER_RATE_LIMIT_TPM', 100000),
            ],
        ],

        /*
        |----------------------------------------------------------------------
        | Google Gemini - AI Studio
        |----------------------------------------------------------------------
        | Website: https://ai.google.dev/
        | Free tier: 15 RPM, 1M tokens/min, 1500 RPD
        | Features: 1M context window, multimodal support
        */
        'gemini' => [
            'name' => 'Google Gemini',
            'enabled' => env('GEMINI_ENABLED', true),
            'api_key' => env('GEMINI_API_KEY'),
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta/openai'),
            'models' => [
                'chat' => env('GEMINI_MODEL_CHAT', 'gemini-2.0-flash-exp'),
                'vision' => env('GEMINI_MODEL_VISION', 'gemini-2.0-flash-exp'),
                'translation' => env('GEMINI_MODEL_TRANSLATION', 'gemini-2.0-flash-exp'),
                'ner' => env('GEMINI_MODEL_NER', 'gemini-2.0-flash-exp'),
                'classification' => env('GEMINI_MODEL_CLASSIFICATION', 'gemini-2.0-flash-exp'),
                'language_detection' => env('GEMINI_MODEL_LANG_DETECT', 'gemini-2.0-flash-exp'),
            ],
            'free_models' => [
                'gemini-2.0-flash-exp',
                'gemini-1.5-flash',
                'gemini-1.5-flash-8b',
                'gemini-1.5-pro',
            ],
            'context_window' => 1000000, // 1M tokens
            'rate_limits' => [
                'requests_per_minute' => (int) env('GEMINI_RATE_LIMIT_RPM', 15),
                'tokens_per_minute' => (int) env('GEMINI_RATE_LIMIT_TPM', 1000000),
                'requests_per_day' => (int) env('GEMINI_RATE_LIMIT_RPD', 1500),
            ],
        ],

        /*
        |----------------------------------------------------------------------
        | Groq - LPU Inference
        |----------------------------------------------------------------------
        | Website: https://console.groq.com/
        | Free tier: Ultra-fast inference, generous limits
        | Features: Fastest inference speed, Llama & Mixtral models
        */
        'groq' => [
            'name' => 'Groq',
            'enabled' => env('GROQ_ENABLED', true),
            'api_key' => env('GROQ_API_KEY'),
            'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
            'models' => [
                'chat' => env('GROQ_MODEL_CHAT', 'llama-3.3-70b-versatile'),
                'vision' => env('GROQ_MODEL_VISION', 'llama-3.2-90b-vision-preview'),
                'translation' => env('GROQ_MODEL_TRANSLATION', 'llama-3.3-70b-versatile'),
                'ner' => env('GROQ_MODEL_NER', 'llama-3.1-8b-instant'),
                'classification' => env('GROQ_MODEL_CLASSIFICATION', 'llama-3.1-8b-instant'),
                'language_detection' => env('GROQ_MODEL_LANG_DETECT', 'llama-3.1-8b-instant'),
            ],
            'free_models' => [
                'llama-3.3-70b-versatile',
                'llama-3.1-70b-versatile',
                'llama-3.1-8b-instant',
                'llama-3.2-90b-vision-preview',
                'llama-3.2-11b-vision-preview',
                'llama-3.2-3b-preview',
                'llama-3.2-1b-preview',
                'mixtral-8x7b-32768',
                'gemma2-9b-it',
            ],
            'rate_limits' => [
                'requests_per_minute' => (int) env('GROQ_RATE_LIMIT_RPM', 30),
                'tokens_per_minute' => (int) env('GROQ_RATE_LIMIT_TPM', 15000),
                'requests_per_day' => (int) env('GROQ_RATE_LIMIT_RPD', 14400),
            ],
        ],

        /*
        |----------------------------------------------------------------------
        | Cerebras - Fast Inference
        |----------------------------------------------------------------------
        | Website: https://cloud.cerebras.ai/
        | Free tier: 30 RPM, 60K tokens/min, 1B tokens/month
        | Features: Fastest inference, Llama models
        */
        'cerebras' => [
            'name' => 'Cerebras',
            'enabled' => env('CEREBRAS_ENABLED', true),
            'api_key' => env('CEREBRAS_API_KEY'),
            'base_url' => env('CEREBRAS_BASE_URL', 'https://api.cerebras.ai/v1'),
            'models' => [
                'chat' => env('CEREBRAS_MODEL_CHAT', 'llama-3.3-70b'),
                'vision' => null, // Cerebras does not support vision models
                'translation' => env('CEREBRAS_MODEL_TRANSLATION', 'llama-3.3-70b'),
                'ner' => env('CEREBRAS_MODEL_NER', 'llama-3.1-8b'),
                'classification' => env('CEREBRAS_MODEL_CLASSIFICATION', 'llama-3.1-8b'),
                'language_detection' => env('CEREBRAS_MODEL_LANG_DETECT', 'llama-3.1-8b'),
            ],
            'free_models' => [
                'llama-3.3-70b',
                'llama-3.1-70b',
                'llama-3.1-8b',
            ],
            'rate_limits' => [
                'requests_per_minute' => (int) env('CEREBRAS_RATE_LIMIT_RPM', 30),
                'tokens_per_minute' => (int) env('CEREBRAS_RATE_LIMIT_TPM', 60000),
                'tokens_per_month' => (int) env('CEREBRAS_RATE_LIMIT_TPMonth', 1000000000),
            ],
        ],

        /*
        |----------------------------------------------------------------------
        | Mistral AI - La Plateforme
        |----------------------------------------------------------------------
        | Website: https://console.mistral.ai/
        | Free tier: Access to experimental models
        | Features: European AI provider, strong multilingual support
        */
        'mistral' => [
            'name' => 'Mistral AI',
            'enabled' => env('MISTRAL_ENABLED', true),
            'api_key' => env('MISTRAL_API_KEY'),
            'base_url' => env('MISTRAL_BASE_URL', 'https://api.mistral.ai/v1'),
            'models' => [
                'chat' => env('MISTRAL_MODEL_CHAT', 'mistral-small-latest'),
                'vision' => env('MISTRAL_MODEL_VISION', 'pixtral-12b-2409'),
                'translation' => env('MISTRAL_MODEL_TRANSLATION', 'mistral-small-latest'),
                'ner' => env('MISTRAL_MODEL_NER', 'mistral-small-latest'),
                'classification' => env('MISTRAL_MODEL_CLASSIFICATION', 'mistral-small-latest'),
                'language_detection' => env('MISTRAL_MODEL_LANG_DETECT', 'mistral-small-latest'),
            ],
            'free_models' => [
                'mistral-small-latest',
                'mistral-large-latest',
                'pixtral-12b-2409',
                'codestral-latest',
                'mistral-embed',
            ],
            'rate_limits' => [
                'requests_per_minute' => (int) env('MISTRAL_RATE_LIMIT_RPM', 60),
                'tokens_per_minute' => (int) env('MISTRAL_RATE_LIMIT_TPM', 500000),
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Provider Fallback Chain
    |--------------------------------------------------------------------------
    |
    | When the primary provider fails, the system will try these providers
    | in order. Only enabled providers with valid API keys are used.
    |
    */

    'fallback_chain' => [
        'groq',      // Ultra-fast inference
        'cerebras',  // Fast inference
        'gemini',    // Large context window
        'mistral',   // European provider
        'openrouter', // Multi-model gateway (last resort)
    ],

    /*
    |--------------------------------------------------------------------------
    | Task-Specific Provider Preferences
    |--------------------------------------------------------------------------
    |
    | Override default provider for specific tasks. Useful for tasks that
    | benefit from certain provider characteristics.
    |
    */

    'task_providers' => [
        // Vision tasks require vision-capable models
        'vision' => env('INFERENCE_VISION_PROVIDER', null), // null = use default

        // Translation benefits from Gemini's large context for long documents
        'translation' => env('INFERENCE_TRANSLATION_PROVIDER', null),

        // Fast tasks can use Groq for speed
        'classification' => env('INFERENCE_CLASSIFICATION_PROVIDER', null),
        'language_detection' => env('INFERENCE_LANGDETECT_PROVIDER', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Settings
    |--------------------------------------------------------------------------
    */

    'http' => [
        'timeout' => (int) env('INFERENCE_TIMEOUT', 120),
        'connect_timeout' => (int) env('INFERENCE_CONNECT_TIMEOUT', 10),
        'retry_attempts' => (int) env('INFERENCE_RETRY_ATTEMPTS', 3),
        'retry_delay_ms' => (int) env('INFERENCE_RETRY_DELAY_MS', 1000),
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
    | Rate Limiting
    |--------------------------------------------------------------------------
    */

    'rate_limiting' => [
        'enabled' => env('INFERENCE_RATE_LIMIT_ENABLED', true),
        'cooldown_seconds' => (int) env('INFERENCE_RATE_LIMIT_COOLDOWN', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'enabled' => env('INFERENCE_CACHE_ENABLED', true),
        'ttl_minutes' => (int) env('INFERENCE_CACHE_TTL', 60),
        'prefix' => 'inference_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */

    'logging' => [
        'enabled' => env('INFERENCE_LOGGING_ENABLED', true),
        'channel' => env('INFERENCE_LOG_CHANNEL', 'stack'),
        'log_requests' => env('INFERENCE_LOG_REQUESTS', false),
        'log_responses' => env('INFERENCE_LOG_RESPONSES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider Comparison Summary
    |--------------------------------------------------------------------------
    |
    | Quick reference for provider selection:
    |
    | Provider     | Speed    | Context  | Vision | Free Tier Limits
    | -------------|----------|----------|--------|---------------------------
    | OpenRouter   | Medium   | Varies   | Yes    | Multiple free models
    | Gemini       | Fast     | 1M       | Yes    | 15 RPM, 1500 RPD
    | Groq         | Fastest  | 128K     | Yes    | 30 RPM, 14.4K RPD
    | Cerebras     | Fastest  | 128K     | No     | 30 RPM, 1B tokens/month
    | Mistral      | Fast     | 128K     | Yes    | Experimental tier
    |
    */

];
