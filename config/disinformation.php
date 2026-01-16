<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Disinformation Detection Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the disinformation pattern detection system including
    | AI analysis via OpenRouter, pattern matching, and content moderation.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | OpenRouter AI Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for AI-powered disinformation analysis using OpenRouter.ai
    | which provides access to various free and paid AI models.
    |
    | Free models available (as of 2024):
    | - meta-llama/llama-3.2-3b-instruct:free
    | - meta-llama/llama-3.2-1b-instruct:free
    | - google/gemma-2-9b-it:free
    | - qwen/qwen-2-7b-instruct:free
    | - mistralai/mistral-7b-instruct:free
    |
    */

    'openrouter' => [
        'enabled' => env('DISINFORMATION_AI_ENABLED', false),
        'api_key' => env('OPENROUTER_API_KEY'),
        'model' => env('OPENROUTER_MODEL', 'meta-llama/llama-3.2-3b-instruct:free'),
        'timeout' => (int) env('OPENROUTER_TIMEOUT', 30),
        'max_tokens' => (int) env('OPENROUTER_MAX_TOKENS', 500),
        'temperature' => (float) env('OPENROUTER_TEMPERATURE', 0.3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Flag Threshold
    |--------------------------------------------------------------------------
    |
    | Threat score threshold for automatic flagging of content.
    | Content with threat scores >= this value will be automatically flagged.
    |
    */

    'auto_flag_threshold' => (int) env('DISINFORMATION_AUTO_FLAG_THRESHOLD', 70),

    /*
    |--------------------------------------------------------------------------
    | Threat Score Weights
    |--------------------------------------------------------------------------
    |
    | Weights for different indicator types when calculating threat scores.
    |
    */

    'threat_weights' => [
        'pattern_match' => 10,
        'propaganda_indicator' => 5,
        'coordinated_behavior' => 20,
        'ai_detection' => 15,
        'source_credibility_issue' => 10,
        'temporal_anomaly' => 8,
        'network_anomaly' => 12,
    ],

    /*
    |--------------------------------------------------------------------------
    | Loaded Language Terms
    |--------------------------------------------------------------------------
    |
    | Emotionally charged words and phrases that may indicate propaganda.
    | This list is used for linguistic pattern detection.
    |
    */

    'loaded_language' => [
        // Sensationalism
        'shocking', 'outrageous', 'unbelievable', 'incredible', 'stunning',
        'explosive', 'bombshell', 'breaking', 'urgent', 'critical',
        'must see', 'must read', 'you won\'t believe',

        // Anti-establishment
        'they don\'t want you to know', 'mainstream media lies', 'wake up',
        'sheep', 'brainwashed', 'truth bomb', 'hidden truth', 'cover-up',
        'suppressed information', 'censored', 'what they\'re hiding',

        // Fear-based
        'danger', 'threat', 'warning', 'beware', 'alert',
        'emergency', 'crisis', 'catastrophe', 'disaster', 'collapse',

        // Us vs Them
        'enemy', 'traitor', 'patriot', 'real americans', 'true believers',
        'our people', 'them', 'those people', 'the elite', 'globalists',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fear Language Terms
    |--------------------------------------------------------------------------
    |
    | Words and phrases that appeal to fear, commonly used in propaganda.
    |
    */

    'fear_language' => [
        'danger', 'threat', 'warning', 'urgent', 'crisis',
        'catastrophe', 'disaster', 'emergency', 'terrifying',
        'deadly', 'fatal', 'collapse', 'destruction', 'apocalypse',
        'invasion', 'takeover', 'epidemic', 'pandemic', 'extinction',
    ],

    /*
    |--------------------------------------------------------------------------
    | Known Unreliable Domains
    |--------------------------------------------------------------------------
    |
    | List of domains known for publishing unreliable or false information.
    | Used for source credibility checking.
    |
    */

    'unreliable_domains' => [
        // Placeholder - add actual domains based on research
        // Example: 'example-fake-news.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Known Reliable Domains
    |--------------------------------------------------------------------------
    |
    | List of domains known for reliable, fact-checked information.
    | Used for source credibility boosting.
    |
    */

    'reliable_domains' => [
        // Major news agencies
        'reuters.com',
        'apnews.com',
        'afp.com',

        // Fact-checking organizations
        'snopes.com',
        'factcheck.org',
        'politifact.com',

        // Academic and research
        'nature.com',
        'science.org',
        'thelancet.com',

        // Government and official sources
        'who.int',
        'un.org',
    ],

    /*
    |--------------------------------------------------------------------------
    | Coordination Detection Settings
    |--------------------------------------------------------------------------
    |
    | Settings for detecting coordinated inauthentic behavior.
    |
    */

    'coordination' => [
        // Minimum posts required for coordination analysis
        'min_posts' => 2,

        // Time window for burst detection (seconds)
        'burst_window' => 3600,

        // Threshold for posts in burst window to be suspicious
        'burst_threshold' => 10,

        // Similarity threshold for content matching (0-1)
        'similarity_threshold' => 0.7,

        // Hashtag coordination threshold (% of posts with same hashtag)
        'hashtag_threshold' => 0.7,
    ],

    /*
    |--------------------------------------------------------------------------
    | Temporal Analysis Settings
    |--------------------------------------------------------------------------
    |
    | Settings for analyzing posting time patterns.
    |
    */

    'temporal' => [
        // Hours considered unusual for human posting (e.g., 2-5 AM)
        'unusual_hours_start' => 2,
        'unusual_hours_end' => 5,

        // Maximum standard deviation for regular posting intervals (seconds)
        'regular_interval_max_std' => 10,

        // Minimum average interval for suspicious automation (seconds)
        'suspicious_min_interval' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pattern Categories
    |--------------------------------------------------------------------------
    |
    | Descriptions and metadata for pattern categories.
    |
    */

    'pattern_categories' => [
        'linguistic' => [
            'name' => 'Linguistic Patterns',
            'description' => 'Text-based patterns including propaganda phrases, loaded language, and emotional manipulation.',
            'color' => '#8b5cf6',
        ],
        'behavioral' => [
            'name' => 'Behavioral Patterns',
            'description' => 'Account behavior patterns such as rapid posting, unusual activity, and bot-like behavior.',
            'color' => '#3b82f6',
        ],
        'temporal' => [
            'name' => 'Temporal Patterns',
            'description' => 'Time-based patterns including unusual posting times and coordinated timing.',
            'color' => '#10b981',
        ],
        'network' => [
            'name' => 'Network Patterns',
            'description' => 'Network-based patterns including bot networks, sock puppets, and coordinated amplification.',
            'color' => '#f59e0b',
        ],
        'media' => [
            'name' => 'Media Manipulation',
            'description' => 'Media-related patterns including deepfakes, doctored images, and manipulated content.',
            'color' => '#ef4444',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Analysis Type Descriptions
    |--------------------------------------------------------------------------
    |
    | Detailed descriptions for each analysis type.
    |
    */

    'analysis_types' => [
        'coordinated_behavior' => [
            'name' => 'Coordinated Inauthentic Behavior',
            'description' => 'Detection of coordinated campaigns using multiple accounts or sources to artificially amplify messages.',
            'indicators' => ['synchronized posting', 'similar content across accounts', 'unusual engagement patterns'],
        ],
        'propaganda' => [
            'name' => 'Propaganda',
            'description' => 'Detection of propaganda techniques including emotional manipulation, loaded language, and misleading framing.',
            'indicators' => ['loaded language', 'fear appeals', 'bandwagon effects', 'black-and-white thinking'],
        ],
        'fake_account' => [
            'name' => 'Fake Account',
            'description' => 'Detection of fake or inauthentic accounts based on behavior patterns and profile characteristics.',
            'indicators' => ['new account', 'incomplete profile', 'bot-like behavior', 'unusual activity times'],
        ],
        'manipulated_media' => [
            'name' => 'Manipulated Media',
            'description' => 'Detection of manipulated images, videos, or other media content.',
            'indicators' => ['metadata inconsistencies', 'visual artifacts', 'context mismatch'],
        ],
        'false_narrative' => [
            'name' => 'False Narrative',
            'description' => 'Detection of false narratives, conspiracy theories, and misinformation patterns.',
            'indicators' => ['unverified claims', 'contradicts established facts', 'circular sourcing'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Moderation Defaults
    |--------------------------------------------------------------------------
    |
    | Default settings for content moderation.
    |
    */

    'moderation' => [
        // Auto-hide content above this threat score
        'auto_hide_threshold' => 90,

        // Require review for content above this threat score
        'require_review_threshold' => 50,

        // Days to retain resolved flags
        'resolved_retention_days' => 90,

        // Maximum appeals allowed per flagged content
        'max_appeals' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limits for disinformation analysis endpoints.
    |
    */

    'rate_limits' => [
        // Maximum analyses per user per hour
        'analyses_per_hour' => (int) env('DISINFORMATION_RATE_LIMIT_ANALYSES', 20),

        // Maximum flags per user per hour
        'flags_per_hour' => (int) env('DISINFORMATION_RATE_LIMIT_FLAGS', 10),
    ],

];
