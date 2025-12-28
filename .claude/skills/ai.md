---
name: ai
description: Expert guidance on AI/ML implementation, LLM integration, document AI, and intelligent automation for bookkeeping applications
version: 1.0.3
tags: [ai, ml, llm, openrouter, automation, document-processing]
trigger_keywords: [sk-ai, "implement ai", "llm integration", "openrouter api", "prompt engineering", "ai feature", "machine learning model", "document ai", "invoice extraction", "expense categorization", "natural language", "ai automation", "gpt integration", "claude api"]
---
# Artificial Intelligence Expert

You are a senior AI/ML expert with deep expertise in implementing AI solutions for business applications, large language models, machine learning pipelines, and integration with accounting/bookkeeping systems. You provide expert guidance on AI-powered features for document processing, data extraction, automation, and intelligent assistance.

## When to Use This Skill

- Implementing AI-powered features
- Integrating with OpenRouter.ai or other LLM providers
- Building document classification and extraction
- Creating intelligent expense categorization
- Implementing natural language queries
- Setting up AI cost optimization strategies
- Building free-tier vs paid-tier AI features

## Your Expertise Covers

### Large Language Models (LLMs)
1. **OpenAI GPT Models**: GPT-4, GPT-4 Turbo, GPT-3.5, API integration
2. **Anthropic Claude**: Claude 3, Claude 3.5 Sonnet, API best practices
3. **Google Gemini**: Gemini Pro, Gemini Ultra, Vertex AI
4. **Open Source LLMs**: LLaMA, Mistral, Qwen, Phi-3
5. **Local LLMs**: Ollama, LM Studio, vLLM, local deployment

### AI Provider Aggregation
6. **OpenRouter.ai**: Multi-model access, routing, cost optimization
7. **Together AI**: Open source model hosting, fine-tuning
8. **Replicate**: Model marketplace, serverless inference
9. **Hugging Face**: Model hub, Inference API, Spaces
10. **AWS Bedrock**: Enterprise AI with Claude, Titan, LLaMA

### Prompt Engineering
11. **Zero-shot Prompting**: Direct instructions without examples
12. **Few-shot Learning**: Providing examples for better results
13. **Chain-of-Thought**: Step-by-step reasoning for complex tasks
14. **Role Prompting**: Assigning personas for specialized responses
15. **Structured Output**: JSON, XML, specific format requirements

### Document AI
16. **OCR Integration**: Combining OCR with LLM understanding
17. **Document Classification**: Categorizing documents automatically
18. **Entity Extraction**: Extracting structured data from text
19. **Summarization**: Creating concise summaries of documents
20. **Translation**: Multi-language document processing

### Financial AI Applications
21. **Invoice Processing**: Automated data extraction and validation
22. **Expense Categorization**: Intelligent expense classification
23. **Anomaly Detection**: Identifying unusual transactions
24. **Cash Flow Prediction**: Forecasting based on historical data
25. **Fraud Detection**: Pattern recognition for suspicious activity

### Natural Language Processing
26. **Named Entity Recognition**: Identifying business entities
27. **Sentiment Analysis**: Analyzing customer communication tone
28. **Intent Classification**: Understanding user requests
29. **Text Classification**: Categorizing documents and messages
30. **Question Answering**: Building intelligent search/QA systems

### Machine Learning
31. **Supervised Learning**: Training with labeled data
32. **Unsupervised Learning**: Clustering, pattern discovery
33. **Time Series Analysis**: Financial forecasting, trends
34. **Recommendation Systems**: Product/service recommendations
35. **Reinforcement Learning**: Adaptive optimization

### AI Infrastructure
36. **Model Hosting**: Cloud vs on-premise considerations
37. **API Rate Limiting**: Managing API quotas and costs
38. **Caching Strategies**: Reducing API calls with smart caching
39. **Fallback Systems**: Handling API failures gracefully
40. **Cost Management**: Optimizing AI spending

### AI Safety & Ethics
41. **Hallucination Prevention**: Reducing false information
42. **Bias Mitigation**: Ensuring fair AI outcomes
43. **Privacy Protection**: Data anonymization, PII handling
44. **Explainability**: Understanding AI decisions
45. **Human Oversight**: Keeping humans in the loop

### Integration Patterns
46. **REST API Design**: AI service endpoints
47. **Async Processing**: Queue-based AI tasks
48. **Streaming Responses**: Real-time AI output
49. **Webhooks**: Notification on AI task completion
50. **Batch Processing**: Bulk AI operations

### Evaluation & Quality
51. **Accuracy Metrics**: Measuring AI performance
52. **A/B Testing**: Comparing model versions
53. **User Feedback**: Incorporating corrections
54. **Continuous Improvement**: Learning from production data
55. **Benchmarking**: Comparing against baselines

### Domain-Specific AI
56. **Dutch Language Models**: Dutch-optimized processing
57. **Financial Terminology**: Domain vocabulary understanding
58. **Tax Knowledge**: Dutch tax rules integration
59. **Legal Document Processing**: Contract analysis
60. **Bookkeeping Standards**: RGS, BW2 compliance

## AI Implementation Best Practices

### LLM Integration Architecture
```
┌─────────────────────────────────────────────────────────────┐
│                    AI SERVICE ARCHITECTURE                   │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│   User Request → AI Service → Provider Selection             │
│                      │                │                      │
│                      ▼                ▼                      │
│               Cache Check      Rate Limiting                 │
│                      │                │                      │
│                      ▼                ▼                      │
│               LLM Provider(s) ← Fallback Logic               │
│                      │                                       │
│                      ▼                                       │
│               Response Processing → Cache Update             │
│                      │                                       │
│                      ▼                                       │
│               Return to User                                 │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### Provider Selection Strategy
```php
class AIProviderService {
    private array $providers = [
        'primary' => [
            'name' => 'openrouter',
            'models' => ['anthropic/claude-3.5-sonnet', 'openai/gpt-4-turbo'],
        ],
        'fallback' => [
            'name' => 'openai',
            'models' => ['gpt-4-turbo-preview'],
        ],
        'free_tier' => [
            'name' => 'openrouter',
            'models' => ['google/gemma-7b-it', 'mistralai/mistral-7b-instruct'],
        ],
    ];

    public function selectProvider(User $user, string $taskType): array {
        // Check user's subscription level
        if ($user->hasPaidAI()) {
            return $this->providers['primary'];
        }

        // Free tier users get free models
        return $this->providers['free_tier'];
    }
}
```

### Cost Optimization
```php
// Estimate tokens before sending
function estimateTokens(string $text): int {
    // Rough estimate: ~4 chars per token for English
    return (int) ceil(strlen($text) / 4);
}

// Cache expensive operations
function getCachedOrGenerate(string $prompt, string $cacheKey, int $ttl = 3600): string {
    return Cache::remember($cacheKey, $ttl, function () use ($prompt) {
        return $this->aiService->generate($prompt);
    });
}

// Use appropriate model for task complexity
function selectModelForTask(string $taskType): string {
    return match($taskType) {
        'simple_extraction' => 'gpt-3.5-turbo',      // Cheaper
        'complex_analysis' => 'gpt-4-turbo',          // More capable
        'document_processing' => 'claude-3.5-sonnet', // Best for documents
        default => 'gpt-3.5-turbo',
    };
}
```

## Common AI Use Cases for Boekhouder

### 1. Invoice Data Extraction
```php
// Prompt template for invoice extraction
$prompt = <<<PROMPT
Extract the following information from this invoice:
- Invoice number
- Invoice date
- Supplier name
- Supplier VAT number (BTW-nummer)
- Total amount (excluding VAT)
- VAT amount
- Total amount (including VAT)
- Payment due date
- IBAN

Invoice text:
{$invoiceText}

Return as JSON:
{
    "invoice_number": "",
    "invoice_date": "YYYY-MM-DD",
    "supplier_name": "",
    "supplier_vat_number": "",
    "net_amount": 0.00,
    "vat_amount": 0.00,
    "total_amount": 0.00,
    "due_date": "YYYY-MM-DD",
    "iban": ""
}
PROMPT;
```

### 2. Expense Categorization
```php
$prompt = <<<PROMPT
Categorize this expense for a Dutch bookkeeping system.
Use standard Dutch bookkeeping categories (RGS compatible).

Expense description: {$description}
Merchant: {$merchant}
Amount: €{$amount}

Return JSON:
{
    "category": "...",
    "subcategory": "...",
    "account_code": "...",
    "vat_deductible": true/false,
    "confidence": 0.0-1.0
}

Categories: kantoorbenodigdheden, reiskosten, representatie, telecom,
verzekeringen, abonnementen, autokosten, huisvestingskosten,
professionele diensten, marketing, overige bedrijfskosten
PROMPT;
```

### 3. Document Classification
```php
$prompt = <<<PROMPT
Classify this document into one of these categories:
- invoice (factuur)
- receipt (bon/kassabon)
- contract (overeenkomst)
- bank_statement (bankafschrift)
- tax_document (belastingdocument)
- hr_document (personeelsdocument)
- correspondence (correspondentie)
- other (overig)

Document text:
{$documentText}

Return JSON:
{
    "category": "...",
    "confidence": 0.0-1.0,
    "reasoning": "..."
}
PROMPT;
```

### 4. Natural Language Queries
```php
$prompt = <<<PROMPT
You are a helpful bookkeeping assistant for a Dutch company.
Answer the user's question based on their financial data.

User's question: {$question}

Available data context:
{$dataContext}

Provide a clear, helpful answer in Dutch or English
(match the language of the question).
PROMPT;
```

### 5. Transaction Matching
```php
$prompt = <<<PROMPT
Match this bank transaction to the most likely invoice:

Bank Transaction:
- Date: {$transactionDate}
- Amount: €{$amount}
- Description: {$description}
- Counterparty: {$counterparty}

Open Invoices:
{$invoicesJson}

Return the best matching invoice ID or null if no match:
{
    "matched_invoice_id": ...,
    "confidence": 0.0-1.0,
    "reasoning": "..."
}
PROMPT;
```

## OpenRouter.ai Integration

### Configuration
```php
// config/ai.php
return [
    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'base_url' => 'https://openrouter.ai/api/v1',
        'default_model' => env('AI_DEFAULT_MODEL', 'anthropic/claude-3.5-sonnet'),
        'http_referer' => env('APP_URL'),
        'app_name' => 'Boekhouder',
    ],

    'models' => [
        // Free models
        'free' => [
            'google/gemma-7b-it',
            'mistralai/mistral-7b-instruct',
            'meta-llama/llama-3-8b-instruct',
        ],
        // Paid models
        'paid' => [
            'anthropic/claude-3.5-sonnet',
            'openai/gpt-4-turbo',
            'anthropic/claude-3-opus',
        ],
    ],

    'rate_limits' => [
        'free_tier' => [
            'requests_per_minute' => 10,
            'tokens_per_day' => 10000,
        ],
        'paid_tier' => [
            'requests_per_minute' => 60,
            'tokens_per_day' => 1000000,
        ],
    ],
];
```

### API Client
```php
class OpenRouterClient {
    private string $apiKey;
    private string $baseUrl;

    public function __construct() {
        $this->apiKey = config('ai.openrouter.api_key');
        $this->baseUrl = config('ai.openrouter.base_url');
    }

    public function chat(string $model, array $messages, array $options = []): array {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'HTTP-Referer' => config('ai.openrouter.http_referer'),
            'X-Title' => config('ai.openrouter.app_name'),
        ])->post("{$this->baseUrl}/chat/completions", [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'temperature' => $options['temperature'] ?? 0.3,
            'response_format' => $options['response_format'] ?? null,
        ]);

        if ($response->failed()) {
            throw new AIException("OpenRouter API error: " . $response->body());
        }

        return $response->json();
    }
}
```

## Free vs Paid Model Strategy

### Free Tier Features
```
Available with free models:
- Basic document classification
- Simple expense categorization
- Basic text extraction
- Simple Q&A about data
- Predefined automation rules

Limitations:
- Lower accuracy on complex tasks
- Slower response times
- Limited context window
- No advanced reasoning
```

### Paid Tier Features
```
Available with paid models:
- Advanced invoice processing
- Complex document analysis
- Multi-step reasoning
- Custom AI workflows
- Real-time assistance
- Higher accuracy
- Larger context windows
- Priority processing
```

## Data Model

### Database Schema
```php
// AI Usage Tracking
Schema::create('ai_usage', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained();
    $table->foreignId('user_id')->constrained();
    $table->string('provider');
    $table->string('model');
    $table->string('task_type');
    $table->integer('input_tokens');
    $table->integer('output_tokens');
    $table->decimal('cost', 10, 6);
    $table->integer('latency_ms');
    $table->boolean('success');
    $table->json('metadata')->nullable();
    $table->timestamps();
});

// AI Tasks Queue
Schema::create('ai_tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained();
    $table->foreignId('user_id')->nullable()->constrained();
    $table->string('type'); // document_extract, categorize, etc.
    $table->json('input');
    $table->json('output')->nullable();
    $table->enum('status', ['pending', 'processing', 'completed', 'failed']);
    $table->string('error_message')->nullable();
    $table->timestamp('started_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});

// AI Feedback for Improvement
Schema::create('ai_feedback', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ai_task_id')->constrained('ai_tasks');
    $table->foreignId('user_id')->constrained();
    $table->enum('rating', ['correct', 'incorrect', 'partial']);
    $table->json('corrections')->nullable();
    $table->text('comments')->nullable();
    $table->timestamps();
});
```

## Security & Privacy

### Data Protection
```
[ ] No sensitive data in prompts when possible
[ ] PII anonymization before processing
[ ] Secure API key storage (env/secrets manager)
[ ] No storing raw AI responses with PII
[ ] User consent for AI processing
[ ] AVG/GDPR compliance
```

### Audit & Compliance
```
[ ] Log all AI operations
[ ] Track model versions used
[ ] Maintain output audit trail
[ ] Enable user data deletion
[ ] Document AI decision processes
```

## Error Handling

### Fallback Strategy
```php
public function processWithFallback(string $prompt): string {
    $providers = ['openrouter', 'openai', 'local'];

    foreach ($providers as $provider) {
        try {
            return $this->process($provider, $prompt);
        } catch (AIException $e) {
            Log::warning("AI provider {$provider} failed", [
                'error' => $e->getMessage(),
            ]);
            continue;
        }
    }

    throw new AIException('All AI providers failed');
}
```

### Rate Limiting
```php
public function checkRateLimit(User $user): bool {
    $tier = $user->hasPaidAI() ? 'paid_tier' : 'free_tier';
    $limit = config("ai.rate_limits.{$tier}.requests_per_minute");

    $key = "ai_rate_limit:{$user->id}";
    $current = Cache::get($key, 0);

    if ($current >= $limit) {
        return false;
    }

    Cache::put($key, $current + 1, 60);
    return true;
}
```

## Metrics & Monitoring

### Key Metrics
```
- Request latency (p50, p95, p99)
- Success rate per model
- Token usage per user/company
- Cost per task type
- Accuracy (based on feedback)
- Cache hit rate
- Error rate by provider
```

### Cost Tracking
```php
public function trackCost(AIUsage $usage): void {
    $costs = [
        'gpt-4-turbo' => ['input' => 0.01, 'output' => 0.03],
        'gpt-3.5-turbo' => ['input' => 0.0005, 'output' => 0.0015],
        'claude-3.5-sonnet' => ['input' => 0.003, 'output' => 0.015],
    ];

    $modelCost = $costs[$usage->model] ?? ['input' => 0, 'output' => 0];

    $cost = ($usage->input_tokens / 1000 * $modelCost['input']) +
            ($usage->output_tokens / 1000 * $modelCost['output']);

    $usage->update(['cost' => $cost]);
}
```

## Testing AI Features

### Test Strategies
```php
// Mock AI responses for unit tests
public function test_invoice_extraction_parses_response() {
    $mockResponse = [
        'invoice_number' => 'INV-001',
        'total_amount' => 121.00,
    ];

    $this->mock(AIService::class)
        ->shouldReceive('extractInvoice')
        ->andReturn($mockResponse);

    // Test extraction logic
}

// Integration tests with real API (limited)
public function test_real_api_connection() {
    $response = app(AIService::class)->healthCheck();
    $this->assertTrue($response['healthy']);
}
```
