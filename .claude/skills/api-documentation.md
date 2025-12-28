---
name: api-documentation
description: Generate and maintain OpenAPI/Swagger documentation for RESTful APIs
version: 1.0.1
tags: [api, documentation, swagger, openapi, rest]
trigger_keywords: [sk-api-documentation, api documentation, swagger docs, openapi spec, api schema, endpoint documentation, swagger annotation, api reference]
---

# API Documentation Generator

This skill helps generate and maintain comprehensive API documentation using OpenAPI (Swagger) specifications.

## When to Use

- After creating new API endpoints
- Before releasing API updates
- When onboarding external developers
- For client SDK generation
- During API versioning

## Setup L5-Swagger

### Installation

```bash
cd bookkeeping-app
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
```

### Configuration

Edit `config/l5-swagger.php`:

```php
return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'Boekhouder API Documentation',
                'version' => '1.0.0',
                'description' => 'RESTful API for Dutch bookkeeping SaaS application',
            ],
            'routes' => [
                'api' => 'api/documentation',
            ],
            'paths' => [
                'docs' => storage_path('api-docs'),
                'docs_json' => 'api-docs.json',
                'annotations' => [
                    base_path('app/Http/Controllers/Api'),
                    base_path('app/Models'),
                    base_path('app/Http/Requests'),
                ],
            ],
        ],
    ],
];
```

## Documenting Controllers

### Example: Invoice API

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Invoices",
 *     description="Invoice management endpoints"
 * )
 */
class InvoiceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/invoices",
     *     tags={"Invoices"},
     *     summary="List all invoices",
     *     description="Returns paginated list of invoices for the current company",
     *     operationId="getInvoices",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"draft", "sent", "paid", "overdue", "cancelled"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Invoice")
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="per_page", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $invoices = Invoice::query()
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->paginate($request->per_page ?? 15);

        return response()->json($invoices);
    }

    /**
     * @OA\Post(
     *     path="/api/invoices",
     *     tags={"Invoices"},
     *     summary="Create new invoice",
     *     description="Creates a new invoice for the current company",
     *     operationId="storeInvoice",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreInvoiceRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Invoice created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Invoice")
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = Invoice::create($request->validated());

        return response()->json($invoice, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/invoices/{id}",
     *     tags={"Invoices"},
     *     summary="Get invoice details",
     *     operationId="getInvoice",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Invoice")
     *     ),
     *     @OA\Response(response=404, description="Invoice not found")
     * )
     */
    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json($invoice);
    }
}
```

## Documenting Models

### Example: Invoice Schema

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Invoice",
 *     title="Invoice",
 *     description="Invoice model",
 *     required={"client_id", "number", "amount", "vat_rate"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="company_id", type="integer", example=5),
 *     @OA\Property(property="client_id", type="integer", example=10),
 *     @OA\Property(property="number", type="string", example="INV-2025-001"),
 *     @OA\Property(property="amount", type="number", format="float", example=1000.00),
 *     @OA\Property(property="vat_rate", type="integer", example=21, enum={0, 9, 21}),
 *     @OA\Property(property="vat_amount", type="number", format="float", example=210.00),
 *     @OA\Property(property="total_amount", type="number", format="float", example=1210.00),
 *     @OA\Property(property="status", type="string", example="sent",
 *         enum={"draft", "sent", "paid", "overdue", "cancelled"}
 *     ),
 *     @OA\Property(property="invoice_date", type="string", format="date", example="2025-01-15"),
 *     @OA\Property(property="due_date", type="string", format="date", example="2025-02-14"),
 *     @OA\Property(property="paid_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Invoice extends Model
{
    // Model implementation
}
```

## Documenting Request Validation

```php
<?php

namespace App\Http\Requests;

/**
 * @OA\Schema(
 *     schema="StoreInvoiceRequest",
 *     title="Store Invoice Request",
 *     required={"client_id", "amount", "vat_rate", "invoice_date"},
 *     @OA\Property(property="client_id", type="integer", example=10,
 *         description="ID of the client"
 *     ),
 *     @OA\Property(property="amount", type="number", format="float", example=1000.00,
 *         description="Invoice amount excluding VAT"
 *     ),
 *     @OA\Property(property="vat_rate", type="integer", example=21,
 *         description="VAT rate percentage", enum={0, 9, 21}
 *     ),
 *     @OA\Property(property="invoice_date", type="string", format="date", example="2025-01-15",
 *         description="Date of the invoice"
 *     ),
 *     @OA\Property(property="due_date", type="string", format="date", example="2025-02-14",
 *         description="Payment due date"
 *     ),
 *     @OA\Property(property="notes", type="string", nullable=true,
 *         description="Additional notes"
 *     ),
 *     @OA\Property(property="items", type="array",
 *         @OA\Items(ref="#/components/schemas/InvoiceItem")
 *     )
 * )
 */
class StoreInvoiceRequest extends FormRequest
{
    // Request implementation
}
```

## Main API Info

Add to `app/Http/Controllers/Controller.php`:

```php
/**
 * @OA\Info(
 *     title="Boekhouder API",
 *     version="1.0.0",
 *     description="RESTful API voor Nederlandse boekhoud SaaS applicatie.
 *                  Biedt endpoints voor facturatie, BTW, salarisadministratie en meer.",
 *     @OA\Contact(
 *         email="api@boekhouder.nl",
 *         name="API Support"
 *     ),
 *     @OA\License(
 *         name="Proprietary",
 *         url="https://boekhouder.nl/terms"
 *     )
 * )
 *
 * @OA\Server(
 *     url="https://api.boekhouder.nl",
 *     description="Production API Server"
 * )
 *
 * @OA\Server(
 *     url="https://staging-api.boekhouder.nl",
 *     description="Staging API Server"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local Development Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="token",
 *     description="Laravel Sanctum authentication token"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and authorization"
 * )
 *
 * @OA\Tag(
 *     name="Invoices",
 *     description="Invoice management"
 * )
 *
 * @OA\Tag(
 *     name="Clients",
 *     description="Client management"
 * )
 *
 * @OA\Tag(
 *     name="Expenses",
 *     description="Expense tracking"
 * )
 *
 * @OA\Tag(
 *     name="VAT",
 *     description="VAT declarations and calculations"
 * )
 *
 * @OA\Tag(
 *     name="Payroll",
 *     description="Payroll and salary administration"
 * )
 */
class Controller extends BaseController
{
    // Controller implementation
}
```

## Generate Documentation

### Commands

```bash
# Generate Swagger documentation
php artisan l5-swagger:generate

# View documentation
# Visit: http://localhost:8000/api/documentation

# Generate for specific API version
php artisan l5-swagger:generate default
```

### Automated Generation

Add to `composer.json`:

```json
{
    "scripts": {
        "post-update-cmd": [
            "php artisan l5-swagger:generate"
        ],
        "docs": "php artisan l5-swagger:generate"
    }
}
```

## API Documentation Checklist

### For Each Endpoint
- [ ] Operation summary and description
- [ ] All parameters documented (path, query, body)
- [ ] Request body schema defined
- [ ] All response codes documented (200, 201, 400, 401, 403, 404, 422, 500)
- [ ] Response schemas defined
- [ ] Authentication requirements specified
- [ ] Rate limiting documented
- [ ] Examples provided

### For Each Model
- [ ] Schema definition with all properties
- [ ] Required fields marked
- [ ] Data types specified
- [ ] Enums documented
- [ ] Example values provided
- [ ] Relationships documented

### General
- [ ] API versioning strategy
- [ ] Base URLs for all environments
- [ ] Authentication methods
- [ ] Error response format
- [ ] Pagination format
- [ ] Rate limiting rules
- [ ] Changelog maintained

## Common Annotations

### HTTP Methods
```php
@OA\Get()      // GET request
@OA\Post()     // POST request
@OA\Put()      // PUT request
@OA\Patch()    // PATCH request
@OA\Delete()   // DELETE request
```

### Parameters
```php
@OA\Parameter(
    name="id",
    in="path",           // path, query, header, cookie
    required=true,
    description="Resource ID",
    @OA\Schema(type="integer")
)
```

### Request Bodies
```php
@OA\RequestBody(
    required=true,
    description="Invoice data",
    @OA\JsonContent(ref="#/components/schemas/Invoice")
)
```

### Responses
```php
@OA\Response(
    response=200,
    description="Successful operation",
    @OA\JsonContent(
        @OA\Property(property="data", ref="#/components/schemas/Invoice")
    )
)
```

## Example: Complete API Endpoint

```php
/**
 * @OA\Post(
 *     path="/api/invoices/{id}/send",
 *     tags={"Invoices"},
 *     summary="Send invoice to client",
 *     description="Sends the invoice via email to the client",
 *     operationId="sendInvoice",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Invoice ID",
 *         @OA\Schema(type="integer", example=123)
 *     ),
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="email",
 *                 type="string",
 *                 format="email",
 *                 description="Override default client email",
 *                 example="client@example.com"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 description="Custom message to include",
 *                 example="Thank you for your business!"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Invoice sent successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string",
 *                 example="Invoice sent to client@example.com"
 *             ),
 *             @OA\Property(property="data", ref="#/components/schemas/Invoice")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Invoice not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Invoice not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */
public function send(Request $request, Invoice $invoice): JsonResponse
{
    // Implementation
}
```

## Testing API Documentation

```bash
# Validate OpenAPI spec
./vendor/bin/swagger-cli validate storage/api-docs/api-docs.json

# Generate client SDK
openapi-generator-cli generate \
    -i storage/api-docs/api-docs.json \
    -g php \
    -o client-sdk/
```

## Export Options

```bash
# Export as JSON
curl http://localhost:8000/docs/api-docs.json > api-docs.json

# Export as YAML
php artisan l5-swagger:generate --format=yaml
```

## Integration with Postman

1. Generate documentation: `php artisan l5-swagger:generate`
2. Visit: `http://localhost:8000/api/documentation`
3. Download JSON: `http://localhost:8000/docs/api-docs.json`
4. Import into Postman: File → Import → Paste URL

---

## API Versioning Strategy

### 1. URL-Based Versioning (Recommended)

```php
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::apiResource('invoices', Api\V1\InvoiceController::class);
});

Route::prefix('v2')->group(function () {
    Route::apiResource('invoices', Api\V2\InvoiceController::class);
});
```

**Document versioning in OpenAPI**:
```php
/**
 * @OA\Info(
 *     title="Boekhouder API",
 *     version="2.0.0",
 *     description="v2.0 introduces breaking changes to invoice endpoints"
 * )
 *
 * @OA\Server(url="https://api.boekhouder.nl/v2", description="API v2")
 * @OA\Server(url="https://api.boekhouder.nl/v1", description="API v1 (deprecated)")
 */
```

### 2. Version Deprecation

```php
/**
 * @OA\Get(
 *     path="/api/v1/invoices",
 *     deprecated=true,
 *     summary="[DEPRECATED] List invoices - Use v2 endpoint instead",
 *     description="⚠️ This endpoint is deprecated and will be removed on 2025-06-01.
 *                  Please migrate to /api/v2/invoices",
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\Header(
 *             header="Sunset",
 *             description="Deprecation date",
 *             @OA\Schema(type="string", example="Sat, 01 Jun 2025 00:00:00 GMT")
 *         ),
 *         @OA\Header(
 *             header="Deprecation",
 *             description="Deprecation notice",
 *             @OA\Schema(type="string", example="true")
 *         )
 *     )
 * )
 */
```

### 3. Migration Guide Template

```markdown
# API v1 to v2 Migration Guide

## Breaking Changes

| v1 Endpoint | v2 Endpoint | Change Description |
|------------|-------------|-------------------|
| GET /invoices | GET /invoices | Response format changed |
| POST /invoices | POST /invoices | New required field: `currency` |
| DELETE /invoices/{id} | DELETE /invoices/{id} | Returns 204 instead of 200 |

## Response Format Changes

### v1 Response:
```json
{ "invoice": { "id": 1, "total": 100 } }
```

### v2 Response:
```json
{ "data": { "id": 1, "total": "100.00", "currency": "EUR" } }
```
```

---

## Security Schemes

### 1. OAuth 2.0 with Bearer Token

```php
/**
 * @OA\SecurityScheme(
 *     securityScheme="oauth2",
 *     type="oauth2",
 *     @OA\Flow(
 *         flow="authorizationCode",
 *         authorizationUrl="https://auth.boekhouder.nl/oauth/authorize",
 *         tokenUrl="https://auth.boekhouder.nl/oauth/token",
 *         refreshUrl="https://auth.boekhouder.nl/oauth/token/refresh",
 *         scopes={
 *             "read:invoices": "Read invoice data",
 *             "write:invoices": "Create and modify invoices",
 *             "read:clients": "Read client data",
 *             "admin": "Full administrative access"
 *         }
 *     )
 * )
 */
```

### 2. API Key Authentication

```php
/**
 * @OA\SecurityScheme(
 *     securityScheme="api_key",
 *     type="apiKey",
 *     in="header",
 *     name="X-API-Key",
 *     description="API key for machine-to-machine authentication"
 * )
 */

// Using API Key
/**
 * @OA\Get(
 *     path="/api/webhooks/receive",
 *     security={{"api_key":{}}},
 *     ...
 * )
 */
```

### 3. Multiple Authentication Methods

```php
/**
 * @OA\Get(
 *     path="/api/invoices",
 *     security={
 *         {"sanctum":{}},
 *         {"oauth2":{"read:invoices"}},
 *         {"api_key":{}}
 *     },
 *     description="Accepts Sanctum tokens, OAuth2 tokens, or API keys"
 * )
 */
```

---

## Error Response Standardization

### 1. Error Schema Definition

```php
/**
 * @OA\Schema(
 *     schema="Error",
 *     title="Error Response",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="error", type="object",
 *         @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
 *         @OA\Property(property="message", type="string", example="The given data was invalid"),
 *         @OA\Property(property="details", type="object", additionalProperties=true)
 *     ),
 *     @OA\Property(property="request_id", type="string", example="req_abc123")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationError",
 *     allOf={@OA\Schema(ref="#/components/schemas/Error")},
 *     @OA\Property(property="error", type="object",
 *         @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
 *         @OA\Property(property="fields", type="object",
 *             @OA\Property(property="email", type="array",
 *                 @OA\Items(type="string", example="The email field is required")
 *             )
 *         )
 *     )
 * )
 */
```

### 2. Standard Error Codes

```php
/**
 * @OA\Schema(
 *     schema="ErrorCodes",
 *     title="Error Codes Reference",
 *     description="
 * | Code | HTTP Status | Description |
 * |------|-------------|-------------|
 * | VALIDATION_ERROR | 422 | Request validation failed |
 * | AUTHENTICATION_REQUIRED | 401 | No valid authentication provided |
 * | PERMISSION_DENIED | 403 | User lacks required permissions |
 * | RESOURCE_NOT_FOUND | 404 | Requested resource does not exist |
 * | RATE_LIMIT_EXCEEDED | 429 | Too many requests |
 * | INTERNAL_ERROR | 500 | Unexpected server error |
 * | SERVICE_UNAVAILABLE | 503 | Service temporarily unavailable |
 * | DUPLICATE_RESOURCE | 409 | Resource already exists |
 * | CONFLICT | 409 | Action conflicts with current state |
 * "
 * )
 */
```

### 3. Using Error Responses

```php
/**
 * @OA\Post(
 *     path="/api/invoices",
 *     @OA\Response(
 *         response=201,
 *         description="Created",
 *         @OA\JsonContent(ref="#/components/schemas/Invoice")
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request",
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="object",
 *                 @OA\Property(property="code", type="string", example="AUTHENTICATION_REQUIRED"),
 *                 @OA\Property(property="message", type="string", example="No valid authentication token provided")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation Error",
 *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
 *     ),
 *     @OA\Response(
 *         response=429,
 *         description="Rate Limit Exceeded",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="object",
 *                 @OA\Property(property="code", type="string", example="RATE_LIMIT_EXCEEDED"),
 *                 @OA\Property(property="message", type="string", example="Rate limit exceeded. Retry after 60 seconds"),
 *                 @OA\Property(property="retry_after", type="integer", example=60)
 *             )
 *         ),
 *         @OA\Header(header="Retry-After", @OA\Schema(type="integer"), description="Seconds until rate limit resets")
 *     )
 * )
 */
```

---

## Rate Limiting Documentation

### 1. Rate Limit Headers

```php
/**
 * @OA\Schema(
 *     schema="RateLimitHeaders",
 *     title="Rate Limit Headers",
 *     description="All API responses include rate limit headers:
 *
 * | Header | Description | Example |
 * |--------|-------------|---------|
 * | X-RateLimit-Limit | Max requests per window | 1000 |
 * | X-RateLimit-Remaining | Requests remaining | 999 |
 * | X-RateLimit-Reset | Unix timestamp when limit resets | 1704067200 |
 * "
 * )
 */

/**
 * @OA\Get(
 *     path="/api/invoices",
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\Header(header="X-RateLimit-Limit", @OA\Schema(type="integer"), description="Rate limit ceiling"),
 *         @OA\Header(header="X-RateLimit-Remaining", @OA\Schema(type="integer"), description="Requests remaining"),
 *         @OA\Header(header="X-RateLimit-Reset", @OA\Schema(type="integer"), description="Reset timestamp")
 *     )
 * )
 */
```

### 2. Rate Limit Tiers

```php
/**
 * @OA\Schema(
 *     schema="RateLimitTiers",
 *     title="Rate Limit Tiers",
 *     description="
 * ## Rate Limits by Plan
 *
 * | Plan | Requests/minute | Requests/day | Burst |
 * |------|-----------------|--------------|-------|
 * | Free | 60 | 1,000 | 10 |
 * | Basic | 300 | 10,000 | 50 |
 * | Professional | 1,000 | 100,000 | 100 |
 * | Enterprise | 5,000 | Unlimited | 500 |
 *
 * ## Endpoint-Specific Limits
 *
 * Some endpoints have stricter limits:
 * - POST /api/invoices/send: 10/minute (email sending)
 * - POST /api/digipoort/submit: 5/hour (government submission)
 * - GET /api/reports/generate: 20/hour (expensive operations)
 * "
 * )
 */
```

---

## Pagination Documentation

### 1. Cursor-Based Pagination (Recommended)

```php
/**
 * @OA\Schema(
 *     schema="CursorPagination",
 *     title="Cursor Pagination",
 *     @OA\Property(property="data", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="meta", type="object",
 *         @OA\Property(property="cursor", type="string", example="eyJpZCI6MTAwfQ=="),
 *         @OA\Property(property="has_more", type="boolean", example=true),
 *         @OA\Property(property="per_page", type="integer", example=25)
 *     ),
 *     @OA\Property(property="links", type="object",
 *         @OA\Property(property="next", type="string", nullable=true,
 *             example="https://api.boekhouder.nl/api/invoices?cursor=eyJpZCI6MTAwfQ=="),
 *         @OA\Property(property="prev", type="string", nullable=true, example=null)
 *     )
 * )
 */

/**
 * @OA\Get(
 *     path="/api/invoices",
 *     @OA\Parameter(
 *         name="cursor",
 *         in="query",
 *         description="Pagination cursor from previous response",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         description="Items per page (max 100)",
 *         @OA\Schema(type="integer", default=25, maximum=100)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         @OA\JsonContent(ref="#/components/schemas/CursorPagination")
 *     )
 * )
 */
```

### 2. Offset-Based Pagination

```php
/**
 * @OA\Schema(
 *     schema="OffsetPagination",
 *     title="Offset Pagination",
 *     @OA\Property(property="data", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="meta", type="object",
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="from", type="integer", example=1),
 *         @OA\Property(property="last_page", type="integer", example=10),
 *         @OA\Property(property="per_page", type="integer", example=25),
 *         @OA\Property(property="to", type="integer", example=25),
 *         @OA\Property(property="total", type="integer", example=250)
 *     ),
 *     @OA\Property(property="links", type="object",
 *         @OA\Property(property="first", type="string"),
 *         @OA\Property(property="last", type="string"),
 *         @OA\Property(property="prev", type="string", nullable=true),
 *         @OA\Property(property="next", type="string", nullable=true)
 *     )
 * )
 */
```

---

## Webhook Documentation

### 1. Webhook Event Types

```php
/**
 * @OA\Schema(
 *     schema="WebhookEvent",
 *     title="Webhook Event",
 *     @OA\Property(property="id", type="string", example="evt_abc123"),
 *     @OA\Property(property="type", type="string",
 *         enum={"invoice.created", "invoice.sent", "invoice.paid", "invoice.overdue",
 *               "payment.received", "client.created", "expense.approved"}),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="data", type="object")
 * )
 */

/**
 * @OA\Post(
 *     path="/webhooks/receive",
 *     tags={"Webhooks"},
 *     summary="Receive webhook events",
 *     description="Endpoint to receive webhook events from Boekhouder.
 *                  Verify the signature using the X-Webhook-Signature header.",
 *     @OA\Header(header="X-Webhook-Signature",
 *         @OA\Schema(type="string"),
 *         description="HMAC-SHA256 signature of the request body"
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/WebhookEvent")
 *     ),
 *     @OA\Response(response=200, description="Event processed successfully"),
 *     @OA\Response(response=400, description="Invalid signature")
 * )
 */
```

### 2. Webhook Signature Verification

```php
/**
 * Verify webhook signature in your application:
 *
 * ```php
 * $payload = file_get_contents('php://input');
 * $signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'];
 * $secret = config('services.boekhouder.webhook_secret');
 *
 * $expected = hash_hmac('sha256', $payload, $secret);
 *
 * if (!hash_equals($expected, $signature)) {
 *     abort(400, 'Invalid signature');
 * }
 * ```
 */
```

---

## SDK Generation Guide

### 1. Generate PHP SDK

```bash
# Install OpenAPI Generator
npm install @openapitools/openapi-generator-cli -g

# Generate PHP SDK
openapi-generator-cli generate \
    -i storage/api-docs/api-docs.json \
    -g php \
    -o sdk/php \
    --additional-properties=invokerPackage=Boekhouder\\Client,packageName=boekhouder-php

# Generated structure:
# sdk/php/
# ├── lib/
# │   ├── Api/
# │   │   ├── InvoicesApi.php
# │   │   ├── ClientsApi.php
# │   │   └── ...
# │   ├── Model/
# │   │   ├── Invoice.php
# │   │   └── ...
# │   └── Configuration.php
# ├── docs/
# └── composer.json
```

### 2. Generate JavaScript/TypeScript SDK

```bash
# Generate TypeScript SDK with Axios
openapi-generator-cli generate \
    -i storage/api-docs/api-docs.json \
    -g typescript-axios \
    -o sdk/typescript \
    --additional-properties=npmName=@boekhouder/client,supportsES6=true

# Usage example:
# import { InvoicesApi, Configuration } from '@boekhouder/client';
#
# const config = new Configuration({
#     basePath: 'https://api.boekhouder.nl/v1',
#     accessToken: 'your-token'
# });
# const invoicesApi = new InvoicesApi(config);
# const invoices = await invoicesApi.getInvoices();
```

### 3. Generate Flutter/Dart SDK

```bash
# Generate Dart SDK
openapi-generator-cli generate \
    -i storage/api-docs/api-docs.json \
    -g dart-dio \
    -o sdk/dart \
    --additional-properties=pubName=boekhouder_client

# Add to pubspec.yaml:
# dependencies:
#   boekhouder_client:
#     path: ../sdk/dart
```

---

## API Testing Integration

### 1. Swagger UI Testing

```php
// config/l5-swagger.php
return [
    'defaults' => [
        'routes' => [
            'api' => 'api/documentation',
        ],
        'securityDefinitions' => [
            'securitySchemes' => [
                'sanctum' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                ],
            ],
        ],
        'ui' => [
            'display' => [
                'doc_expansion' => 'none',
                'filter' => true,
                'try_it_out_enabled' => true, // Enable "Try it out" button
            ],
            'authorization' => [
                'persist_authorization' => true, // Remember auth tokens
            ],
        ],
    ],
];
```

### 2. Newman (Postman CLI) Integration

```bash
# Export Postman collection from OpenAPI
openapi-generator-cli generate \
    -i storage/api-docs/api-docs.json \
    -g postman-collection \
    -o tests/postman

# Run tests
newman run tests/postman/collection.json \
    --environment tests/postman/environment.json \
    --reporters cli,htmlextra \
    --reporter-htmlextra-export tests/postman/report.html
```

### 3. PHPUnit API Tests with Swagger Validation

```php
use App\Testing\ValidatesOpenApiResponse;

class InvoiceApiTest extends TestCase
{
    use ValidatesOpenApiResponse;

    public function test_list_invoices_matches_openapi_spec(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/invoices');

        $response->assertStatus(200);

        // Validate response matches OpenAPI schema
        $this->assertResponseMatchesOpenApiSpec($response, '/api/v1/invoices', 'get', 200);
    }
}
```

---

## Troubleshooting

### Issue 1: Swagger Generation Fails

**Error**: `Required @OA\Info() not found`

**Solution**:
```php
// Add to app/Http/Controllers/Controller.php or dedicated file

/**
 * @OA\Info(
 *     title="Boekhouder API",
 *     version="1.0.0"
 * )
 */
class Controller extends BaseController {}
```

### Issue 2: Annotations Not Found

**Error**: `No annotations found`

**Solution**: Check paths in config/l5-swagger.php:
```php
'annotations' => [
    base_path('app/Http/Controllers/Api'), // Correct path
    base_path('app/Models'),
],
```

### Issue 3: Circular Reference

**Error**: `Swagger spec contains circular reference`

**Solution**: Break the cycle with nullable or separate schemas:
```php
/**
 * @OA\Schema(
 *     schema="InvoiceWithItems",
 *     @OA\Property(property="items", type="array",
 *         @OA\Items(ref="#/components/schemas/InvoiceItemSimple") // Not full InvoiceItem
 *     )
 * )
 */
```

### Issue 4: Response Mismatch

**Error**: Actual API response doesn't match documentation

**Solution**: Generate documentation from actual code:
```bash
# Use response factories
php artisan make:factory InvoiceFactory

# In controller, return consistent resources
return new InvoiceResource($invoice);
```

### Issue 5: Authentication in Swagger UI

**Problem**: Can't authenticate in Swagger UI

**Solution**:
1. Click "Authorize" button
2. For Sanctum: Enter `Bearer your-token-here`
3. For API Key: Enter key directly

---

## Best Practices Checklist

### Documentation Quality
- [ ] All endpoints have summary and description
- [ ] All parameters documented with types and examples
- [ ] All responses documented (200, 400, 401, 403, 404, 422, 500)
- [ ] Examples provided for request/response bodies
- [ ] Error codes and messages documented

### API Design
- [ ] Consistent naming conventions (plural nouns for resources)
- [ ] Proper HTTP methods (GET read, POST create, PUT/PATCH update, DELETE remove)
- [ ] Consistent response format across endpoints
- [ ] Pagination for list endpoints
- [ ] Rate limiting documented

### Security
- [ ] Authentication requirements specified
- [ ] Authorization scopes documented
- [ ] Sensitive data marked appropriately
- [ ] HTTPS enforced

### Maintenance
- [ ] Version strategy defined
- [ ] Deprecation process documented
- [ ] Changelog maintained
- [ ] SDK generation automated

---

## Dutch Bookkeeping-Specific Examples

### Example 1: BTW Declaration Endpoint

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VatDeclaration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="BTW Declarations",
 *     description="VAT (BTW) declaration management for Dutch tax authorities"
 * )
 */
class VatDeclarationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/vat-declarations",
     *     tags={"BTW Declarations"},
     *     summary="List BTW declarations",
     *     description="Returns list of VAT declarations for quarterly or monthly filing periods",
     *     operationId="getVatDeclarations",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="Filter by year",
     *         required=false,
     *         @OA\Schema(type="integer", example=2025)
     *     ),
     *     @OA\Parameter(
     *         name="quarter",
     *         in="query",
     *         description="Filter by quarter (1-4)",
     *         required=false,
     *         @OA\Schema(type="integer", enum={1, 2, 3, 4})
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by declaration status",
     *         required=false,
     *         @OA\Schema(type="string",
     *             enum={"draft", "submitted", "approved", "rejected"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/VatDeclaration")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $declarations = VatDeclaration::query()
            ->when($request->year, fn($q) => $q->where('year', $request->year))
            ->when($request->quarter, fn($q) => $q->where('quarter', $request->quarter))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($declarations);
    }

    /**
     * @OA\Post(
     *     path="/api/vat-declarations/{id}/submit",
     *     tags={"BTW Declarations"},
     *     summary="Submit BTW declaration to Digipoort",
     *     description="Submits the VAT declaration to Dutch tax authorities via Digipoort",
     *     operationId="submitVatDeclaration",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="VAT declaration ID",
     *         @OA\Schema(type="integer", example=42)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="test_mode",
     *                 type="boolean",
     *                 description="Submit to test environment",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="auto_correct",
     *                 type="boolean",
     *                 description="Auto-correct minor validation errors",
     *                 example=true
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Declaration submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string",
     *                 example="BTW-aangifte succesvol ingediend bij Belastingdienst"
     *             ),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="declaration_id", type="integer", example=42),
     *                 @OA\Property(property="digipoort_reference", type="string",
     *                     example="DP-2025-Q1-ABC123"
     *                 ),
     *                 @OA\Property(property="submitted_at", type="string", format="date-time"),
     *                 @OA\Property(property="total_vat_due", type="number", format="float",
     *                     description="Total BTW to pay (can be negative for refund)",
     *                     example=4532.75
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VAT_VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string",
     *                     example="BTW-aangifte bevat validatiefouten"
     *                 ),
     *                 @OA\Property(property="errors", type="array",
     *                     @OA\Items(type="string",
     *                         example="Rubric 5b: BTW hoog tarief niet correct berekend"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Declaration not found")
     * )
     */
    public function submit(Request $request, VatDeclaration $declaration): JsonResponse
    {
        // Implementation
        return response()->json([
            'success' => true,
            'message' => 'BTW-aangifte succesvol ingediend',
            'data' => $declaration,
        ]);
    }
}

/**
 * @OA\Schema(
 *     schema="VatDeclaration",
 *     title="BTW Declaration",
 *     description="VAT declaration model for Dutch tax filing",
 *     required={"company_id", "year", "period_type", "period"},
 *     @OA\Property(property="id", type="integer", example=42),
 *     @OA\Property(property="company_id", type="integer", example=5),
 *     @OA\Property(property="year", type="integer", example=2025),
 *     @OA\Property(property="period_type", type="string",
 *         enum={"month", "quarter"}, example="quarter"
 *     ),
 *     @OA\Property(property="period", type="integer",
 *         description="Month (1-12) or Quarter (1-4)", example=1
 *     ),
 *     @OA\Property(property="status", type="string",
 *         enum={"draft", "submitted", "approved", "rejected"}, example="submitted"
 *     ),
 *     @OA\Property(property="rubric_1a", type="number", format="float",
 *         description="Leveringen/diensten belast met hoog tarief", example=50000.00
 *     ),
 *     @OA\Property(property="rubric_1b", type="number", format="float",
 *         description="Leveringen/diensten belast met laag tarief", example=10000.00
 *     ),
 *     @OA\Property(property="rubric_1c", type="number", format="float",
 *         description="Leveringen/diensten belast met overige tarieven", example=0.00
 *     ),
 *     @OA\Property(property="rubric_1d", type="number", format="float",
 *         description="Privégebruik", example=0.00
 *     ),
 *     @OA\Property(property="rubric_1e", type="number", format="float",
 *         description="Leveringen/diensten belast met 0% of niet bij u belast", example=5000.00
 *     ),
 *     @OA\Property(property="rubric_5b", type="number", format="float",
 *         description="Verschuldigde omzetbelasting (hoog tarief)", example=10500.00
 *     ),
 *     @OA\Property(property="rubric_5c", type="number", format="float",
 *         description="Verschuldigde omzetbelasting (laag tarief)", example=900.00
 *     ),
 *     @OA\Property(property="rubric_5g", type="number", format="float",
 *         description="Subtotaal (5b + 5c + ...)", example=11400.00
 *     ),
 *     @OA\Property(property="total_vat_owed", type="number", format="float",
 *         description="Total VAT owed to tax authority", example=4532.75
 *     ),
 *     @OA\Property(property="total_vat_refund", type="number", format="float",
 *         description="Total VAT to be refunded", example=0.00
 *     ),
 *     @OA\Property(property="digipoort_reference", type="string", nullable=true,
 *         example="DP-2025-Q1-ABC123"
 *     ),
 *     @OA\Property(property="submitted_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
```

### Example 2: Dutch Invoice Endpoint with BTW Fields

```php
<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Post(
 *     path="/api/invoices",
 *     tags={"Invoices"},
 *     summary="Create Dutch invoice",
 *     description="Creates a new invoice compliant with Dutch invoice requirements",
 *     operationId="createInvoice",
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/CreateDutchInvoiceRequest")
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Invoice created successfully",
 *         @OA\JsonContent(ref="#/components/schemas/DutchInvoice")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="object",
 *                 @OA\Property(property="code", type="string", example="INVOICE_VALIDATION_ERROR"),
 *                 @OA\Property(property="fields", type="object",
 *                     @OA\Property(property="btw_number", type="array",
 *                         @OA\Items(type="string",
 *                             example="BTW-nummer moet het formaat NL000000000B00 hebben"
 *                         )
 *                     ),
 *                     @OA\Property(property="kvk_number", type="array",
 *                         @OA\Items(type="string",
 *                             example="KVK-nummer moet 8 cijfers zijn"
 *                         )
 *                     )
 *                 )
 *             )
 *         )
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="CreateDutchInvoiceRequest",
 *     title="Create Dutch Invoice Request",
 *     required={"client_id", "invoice_date", "line_items"},
 *     @OA\Property(property="client_id", type="integer", example=10),
 *     @OA\Property(property="invoice_number", type="string", nullable=true,
 *         description="Auto-generated if not provided", example="2025-001"
 *     ),
 *     @OA\Property(property="invoice_date", type="string", format="date", example="2025-01-15"),
 *     @OA\Property(property="due_date", type="string", format="date", example="2025-02-14"),
 *     @OA\Property(property="payment_terms_days", type="integer",
 *         description="Payment terms in days (default: 30)", example=30
 *     ),
 *     @OA\Property(property="line_items", type="array",
 *         @OA\Items(
 *             @OA\Property(property="description", type="string",
 *                 example="Webdesign diensten januari 2025"
 *             ),
 *             @OA\Property(property="quantity", type="number", format="float", example=40.0),
 *             @OA\Property(property="unit", type="string", example="uur"),
 *             @OA\Property(property="unit_price", type="number", format="float", example=75.00),
 *             @OA\Property(property="vat_rate", type="integer",
 *                 description="BTW percentage (0, 9, or 21)", enum={0, 9, 21}, example=21
 *             ),
 *             @OA\Property(property="vat_type", type="string",
 *                 description="Type of VAT application",
 *                 enum={"standard", "reversed", "exempt", "zero_rated"},
 *                 example="standard"
 *             )
 *         )
 *     ),
 *     @OA\Property(property="notes", type="string", nullable=true,
 *         example="Betaling binnen 30 dagen op NL12ABNA0123456789"
 *     ),
 *     @OA\Property(property="reference", type="string", nullable=true,
 *         example="Project XYZ - Sprint 3"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="DutchInvoice",
 *     title="Dutch Invoice",
 *     description="Invoice model compliant with Dutch requirements",
 *     allOf={@OA\Schema(ref="#/components/schemas/Invoice")},
 *     @OA\Property(property="company", type="object",
 *         description="Supplier company information",
 *         @OA\Property(property="name", type="string", example="Mijn Bedrijf B.V."),
 *         @OA\Property(property="kvk_number", type="string", example="12345678"),
 *         @OA\Property(property="btw_number", type="string", example="NL123456789B01"),
 *         @OA\Property(property="iban", type="string", example="NL12ABNA0123456789")
 *     ),
 *     @OA\Property(property="client", type="object",
 *         description="Client company information",
 *         @OA\Property(property="name", type="string", example="Klant B.V."),
 *         @OA\Property(property="kvk_number", type="string", nullable=true, example="87654321"),
 *         @OA\Property(property="btw_number", type="string", nullable=true,
 *             example="NL987654321B01"
 *         ),
 *         @OA\Property(property="address", type="object",
 *             @OA\Property(property="street", type="string", example="Kerkstraat 1"),
 *             @OA\Property(property="postal_code", type="string", example="1234 AB"),
 *             @OA\Property(property="city", type="string", example="Amsterdam"),
 *             @OA\Property(property="country", type="string", example="Nederland")
 *         )
 *     ),
 *     @OA\Property(property="vat_summary", type="object",
 *         description="BTW breakdown",
 *         @OA\Property(property="subtotal_excl_vat", type="number", format="float",
 *             example=3000.00
 *         ),
 *         @OA\Property(property="vat_breakdown", type="array",
 *             @OA\Items(
 *                 @OA\Property(property="rate", type="integer", example=21),
 *                 @OA\Property(property="base", type="number", format="float", example=3000.00),
 *                 @OA\Property(property="amount", type="number", format="float", example=630.00)
 *             )
 *         ),
 *         @OA\Property(property="total_vat", type="number", format="float", example=630.00),
 *         @OA\Property(property="total_incl_vat", type="number", format="float", example=3630.00)
 *     )
 * )
 */
```

### Example 3: Dutch Payroll Endpoint

```php
<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Tag(
 *     name="Payroll",
 *     description="Dutch payroll (salarisadministratie) management"
 * )
 */
class PayrollController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/payroll/calculate",
     *     tags={"Payroll"},
     *     summary="Calculate Dutch payroll",
     *     description="Calculates gross-to-net salary including Dutch tax and social contributions",
     *     operationId="calculatePayroll",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"employee_id", "period", "gross_salary"},
     *             @OA\Property(property="employee_id", type="integer", example=5),
     *             @OA\Property(property="period", type="string", format="date",
     *                 description="Salary period (YYYY-MM)", example="2025-01"
     *             ),
     *             @OA\Property(property="gross_salary", type="number", format="float",
     *                 description="Gross monthly salary in EUR", example=4500.00
     *             ),
     *             @OA\Property(property="vacation_days", type="number", format="float",
     *                 description="Vacation days taken this period", example=0
     *             ),
     *             @OA\Property(property="overtime_hours", type="number", format="float",
     *                 description="Overtime hours", example=8.0
     *             ),
     *             @OA\Property(property="bonuses", type="number", format="float",
 *                 description="Additional bonuses", example=0.00
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payroll calculated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PayrollCalculation")
     *     ),
     *     @OA\Response(response=404, description="Employee not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function calculate(Request $request): JsonResponse
    {
        // Implementation
        return response()->json([
            'gross_salary' => 4500.00,
            'net_salary' => 3234.50,
            // ... detailed breakdown
        ]);
    }
}

/**
 * @OA\Schema(
 *     schema="PayrollCalculation",
 *     title="Payroll Calculation",
 *     description="Detailed payroll calculation for Dutch employee",
 *     @OA\Property(property="employee_id", type="integer", example=5),
 *     @OA\Property(property="period", type="string", example="2025-01"),
 *     @OA\Property(property="gross_salary", type="number", format="float", example=4500.00),
 *     @OA\Property(property="taxable_income", type="number", format="float", example=4500.00),
 *     @OA\Property(property="deductions", type="object",
 *         @OA\Property(property="loonheffing", type="number", format="float",
 *             description="Wage tax (loonbelasting)", example=945.00
 *         ),
 *         @OA\Property(property="employee_insurance", type="number", format="float",
 *             description="Employee national insurance contributions", example=320.50
 *         ),
 *         @OA\Property(property="pension", type="number", format="float",
 *             description="Pension contribution", example=0.00
 *         ),
 *         @OA\Property(property="total_deductions", type="number", format="float",
 *             example=1265.50
 *         )
 *     ),
 *     @OA\Property(property="employer_costs", type="object",
 *         description="Employer contributions (not deducted from employee)",
 *         @OA\Property(property="employer_insurance", type="number", format="float",
 *             description="Employer social security contributions", example=675.00
 *         ),
 *         @OA\Property(property="total_employer_costs", type="number", format="float",
 *             example=675.00
 *         )
 *     ),
 *     @OA\Property(property="net_salary", type="number", format="float",
 *         description="Net salary to be paid out", example=3234.50
 *     ),
 *     @OA\Property(property="total_cost", type="number", format="float",
 *         description="Total cost for employer", example=5175.00
 *     ),
 *     @OA\Property(property="payment_date", type="string", format="date",
 *         description="Expected payment date", example="2025-01-25"
 *     )
 * )
 */
```

## Extended Best Practices

### 1. Use Consistent Dutch Terminology

When documenting Dutch bookkeeping APIs, use correct Dutch financial terms:

```php
/**
 * @OA\Schema(
 *     schema="DutchFinancialTerms",
 *     title="Dutch Financial Terminology",
 *     description="
 * Use these consistent Dutch terms in your API documentation:
 *
 * | English | Dutch | API Field Name |
 * |---------|-------|----------------|
 * | Invoice | Factuur | invoice |
 * | VAT | BTW (Belasting Toegevoegde Waarde) | vat / btw |
 * | Chamber of Commerce | KVK (Kamer van Koophandel) | kvk_number |
 * | VAT Number | BTW-nummer | btw_number |
 * | Salary | Salaris | salary |
 * | Wage Tax | Loonheffing | wage_tax |
 * | Social Security | Sociale verzekeringen | social_insurance |
 * | Expense | Uitgave / Onkosten | expense |
 * | Revenue | Omzet | revenue |
 * | Profit | Winst | profit |
 * | Balance Sheet | Balans | balance_sheet |
 * | Profit & Loss | Winst-en-verliesrekening | profit_loss |
 * "
 * )
 */
```

### 2. Document Currency Precision

Always use proper decimal precision for financial amounts:

```php
/**
 * @OA\Schema(
 *     schema="MonetaryAmount",
 *     title="Monetary Amount",
 *     description="All monetary amounts must be represented with 2 decimal places.
 *                  Example: 1250.50 (not 1250.5 or 1250).
 *                  Currency is always EUR unless explicitly stated.",
 *     @OA\Property(property="amount", type="number", format="float",
 *         description="Amount in EUR with 2 decimals", example=1250.50
 *     ),
 *     @OA\Property(property="currency", type="string", default="EUR", example="EUR")
 * )
 */

// Bad - inconsistent precision
@OA\Property(property="amount", type="number", example=100)

// Good - always 2 decimals
@OA\Property(property="amount", type="number", format="float", example=100.00)
```

### 3. Document Date Formats Explicitly

Specify date formats clearly for Dutch users:

```php
/**
 * @OA\Schema(
 *     schema="DateFormats",
 *     title="Date Format Standards",
 *     description="
 * ## Date Formats in API
 *
 * ### Input (Request)
 * - **Format**: ISO 8601 (YYYY-MM-DD)
 * - **Example**: `2025-01-15`
 * - **Timezone**: Dates are interpreted in Europe/Amsterdam timezone
 *
 * ### Output (Response)
 * - **Date only**: ISO 8601 (YYYY-MM-DD)
 * - **DateTime**: ISO 8601 with timezone (YYYY-MM-DDTHH:mm:ss+02:00)
 * - **Example**: `2025-01-15T14:30:00+01:00`
 *
 * ### Localized Display
 * - For Dutch users, format dates as `dd-MM-yyyy` in UI
 * - API always uses ISO format
 * "
 * )
 *
 * @OA\Property(property="invoice_date", type="string", format="date",
 *     description="Invoice date in ISO 8601 format (YYYY-MM-DD)",
 *     example="2025-01-15"
 * )
 */
```

### 4. Version All Breaking Changes

Implement proper API versioning for breaking changes:

```php
/**
 * @OA\Info(
 *     title="Boekhouder API",
 *     version="2.1.0",
 *     description="
 * ## Version History
 *
 * ### v2.1.0 (Current)
 * - Added: Automated VAT declaration submission
 * - Changed: Invoice line items now support multiple VAT rates
 *
 * ### v2.0.0
 * - **BREAKING**: Changed response format from `{invoice: {...}}` to `{data: {...}}`
 * - **BREAKING**: Required field added: `client.btw_number` for B2B invoices
 * - Added: Support for reverse charge VAT
 *
 * ### v1.0.0 (Deprecated - End of life: 2025-06-01)
 * - Initial release
 *
 * ## Migration Guides
 * - [v1 to v2 Migration Guide](/docs/migration/v1-to-v2)
 * "
 * )
 */
```

### 5. Include Comprehensive Examples

Provide full request/response examples:

```php
/**
 * @OA\RequestBody(
 *     required=true,
 *     description="Example invoice creation",
 *     @OA\JsonContent(
 *         example={
 *             "client_id": 42,
 *             "invoice_date": "2025-01-15",
 *             "due_date": "2025-02-14",
 *             "line_items": {
 *                 {
 *                     "description": "Webdesign diensten",
 *                     "quantity": 40,
 *                     "unit": "uur",
 *                     "unit_price": 75.00,
 *                     "vat_rate": 21
 *                 },
 *                 {
 *                     "description": "Hosting kosten",
 *                     "quantity": 1,
 *                     "unit": "maand",
 *                     "unit_price": 50.00,
 *                     "vat_rate": 21
 *                 }
 *             },
 *             "notes": "Betaling binnen 30 dagen op NL12ABNA0123456789",
 *             "reference": "Project Website Redesign"
 *         }
 *     )
 * )
 */
```

### 6. Document All Error Scenarios

Document common error scenarios specific to Dutch bookkeeping:

```php
/**
 * @OA\Response(
 *     response=422,
 *     description="Common validation errors",
 *     @OA\JsonContent(
 *         oneOf={
 *             @OA\Schema(
 *                 description="Invalid BTW number format",
 *                 @OA\Property(property="error", type="object",
 *                     @OA\Property(property="code", type="string",
 *                         example="INVALID_BTW_NUMBER"
 *                     ),
 *                     @OA\Property(property="message", type="string",
 *                         example="BTW-nummer moet het formaat NL000000000B00 hebben"
 *                     )
 *                 )
 *             ),
 *             @OA\Schema(
 *                 description="Invalid KVK number",
 *                 @OA\Property(property="error", type="object",
 *                     @OA\Property(property="code", type="string",
 *                         example="INVALID_KVK_NUMBER"
 *                     ),
 *                     @OA\Property(property="message", type="string",
 *                         example="KVK-nummer moet precies 8 cijfers zijn"
 *                     )
 *                 )
 *             ),
 *             @OA\Schema(
 *                 description="Invalid IBAN",
 *                 @OA\Property(property="error", type="object",
 *                     @OA\Property(property="code", type="string",
 *                         example="INVALID_IBAN"
 *                     ),
 *                     @OA\Property(property="message", type="string",
 *                         example="IBAN is niet geldig voor Nederlandse banken"
 *                     )
 *                 )
 *             )
 *         }
 *     )
 * )
 */
```

### 7. Use Schemas for Reusable Components

Create reusable schemas for common Dutch data structures:

```php
/**
 * @OA\Schema(
 *     schema="DutchAddress",
 *     title="Dutch Address",
 *     required={"street", "house_number", "postal_code", "city"},
 *     @OA\Property(property="street", type="string", example="Kerkstraat"),
 *     @OA\Property(property="house_number", type="string", example="123"),
 *     @OA\Property(property="house_number_addition", type="string", nullable=true,
 *         example="A"
 *     ),
 *     @OA\Property(property="postal_code", type="string",
 *         description="Dutch postal code format: 1234 AB",
 *         pattern="^\d{4}\s?[A-Z]{2}$",
 *         example="1234 AB"
 *     ),
 *     @OA\Property(property="city", type="string", example="Amsterdam"),
 *     @OA\Property(property="country", type="string", default="NL", example="NL")
 * )
 *
 * @OA\Schema(
 *     schema="DutchCompanyInfo",
 *     title="Dutch Company Information",
 *     required={"name", "kvk_number"},
 *     @OA\Property(property="name", type="string", example="Mijn Bedrijf B.V."),
 *     @OA\Property(property="kvk_number", type="string",
 *         description="8-digit KVK number",
 *         pattern="^\d{8}$",
 *         example="12345678"
 *     ),
 *     @OA\Property(property="btw_number", type="string", nullable=true,
 *         description="Dutch VAT number format: NL000000000B00",
 *         pattern="^NL\d{9}B\d{2}$",
 *         example="NL123456789B01"
 *     ),
 *     @OA\Property(property="iban", type="string", nullable=true,
 *         description="Dutch IBAN (starts with NL)",
 *         pattern="^NL\d{2}[A-Z]{4}\d{10}$",
 *         example="NL12ABNA0123456789"
 *     ),
 *     @OA\Property(property="address", ref="#/components/schemas/DutchAddress")
 * )
 */
```

### 8. Document Rate Limits for Government APIs

Specify rate limits for sensitive operations:

```php
/**
 * @OA\Post(
 *     path="/api/digipoort/submit",
 *     summary="Submit to Digipoort",
 *     description="
 * Submits a declaration to the Dutch tax authority via Digipoort.
 *
 * **Rate Limits:**
 * - 5 submissions per hour per company
 * - 100 submissions per day per company
 * - Test environment: 20 submissions per hour
 *
 * **Important:** Exceeding rate limits may result in temporary blocking by Belastingdienst.
 * ",
 *     @OA\Response(
 *         response=429,
 *         description="Rate limit exceeded",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="object",
 *                 @OA\Property(property="code", type="string",
 *                     example="DIGIPOORT_RATE_LIMIT"
 *                 ),
 *                 @OA\Property(property="message", type="string",
 *                     example="Maximaal 5 aangifte per uur toegestaan"
 *                 ),
 *                 @OA\Property(property="retry_after", type="integer",
 *                     description="Seconds until rate limit resets",
 *                     example=1800
 *                 )
 *             )
 *         )
 *     )
 * )
 */
```

## Anti-Patterns to Avoid

### 1. Inconsistent Response Formats

**Bad**: Different response structures for similar endpoints

```php
// Bad - Invoice endpoint returns object directly
/**
 * @OA\Response(
 *     response=200,
 *     @OA\JsonContent(ref="#/components/schemas/Invoice")
 * )
 */

// Bad - Expense endpoint wraps in "data"
/**
 * @OA\Response(
 *     response=200,
 *     @OA\JsonContent(
 *         @OA\Property(property="data", ref="#/components/schemas/Expense")
 *     )
 * )
 */
```

**Good**: Consistent structure across all endpoints

```php
/**
 * @OA\Schema(
 *     schema="ApiResponse",
 *     title="Standard API Response",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="data", type="object", description="Response data"),
 *     @OA\Property(property="meta", type="object", nullable=true,
 *         description="Metadata (pagination, etc.)"
 *     )
 * )
 *
 * // All endpoints use this format
 * @OA\Response(
 *     response=200,
 *     @OA\JsonContent(
 *         allOf={@OA\Schema(ref="#/components/schemas/ApiResponse")},
 *         @OA\Property(property="data", ref="#/components/schemas/Invoice")
 *     )
 * )
 */
```

### 2. Missing or Incomplete Error Documentation

**Bad**: Only documenting success responses

```php
/**
 * @OA\Post(
 *     path="/api/invoices",
 *     @OA\Response(response=201, description="Created")
 * )
 */
```

**Good**: Document all possible error responses

```php
/**
 * @OA\Post(
 *     path="/api/invoices",
 *     @OA\Response(response=201, description="Invoice created successfully",
 *         @OA\JsonContent(ref="#/components/schemas/Invoice")
 *     ),
 *     @OA\Response(response=400, description="Bad request",
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden - Insufficient permissions"),
 *     @OA\Response(response=422, description="Validation error",
 *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
 *     ),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 */
```

### 3. Hardcoding URLs and Environment-Specific Values

**Bad**: Hardcoded production URLs in examples

```php
/**
 * @OA\Server(url="https://api.boekhouder.nl")
 *
 * @OA\Property(property="webhook_url", type="string",
 *     example="https://myapp.com/webhook" // Bad - hardcoded
 * )
 */
```

**Good**: Use server variables and placeholder examples

```php
/**
 * @OA\Server(
 *     url="{protocol}://{environment}.boekhouder.nl",
 *     description="Configurable API Server",
 *     @OA\ServerVariable(
 *         serverVariable="protocol",
 *         enum={"https", "http"},
 *         default="https"
 *     ),
 *     @OA\ServerVariable(
 *         serverVariable="environment",
 *         enum={"api", "staging-api", "localhost:8000"},
 *         default="api"
 *     )
 * )
 *
 * @OA\Property(property="webhook_url", type="string",
 *     format="uri",
 *     description="Your webhook endpoint URL",
 *     example="https://your-domain.com/webhooks/boekhouder"
 * )
 */
```

### 4. Not Using Enums for Fixed Values

**Bad**: Free-form strings for known values

```php
/**
 * @OA\Property(property="status", type="string",
 *     description="Invoice status"
 * )
 */
```

**Good**: Use enums to document allowed values

```php
/**
 * @OA\Property(property="status", type="string",
 *     description="Invoice status",
 *     enum={"draft", "sent", "paid", "overdue", "cancelled"},
 *     example="sent"
 * )
 */
```

### 5. Vague or Missing Descriptions

**Bad**: No context or explanation

```php
/**
 * @OA\Get(
 *     path="/api/invoices",
 *     summary="Get invoices"
 * )
 */
```

**Good**: Comprehensive descriptions

```php
/**
 * @OA\Get(
 *     path="/api/invoices",
 *     summary="List all invoices for current company",
 *     description="
 * Returns a paginated list of invoices for the authenticated user's current company.
 * Results can be filtered by status, date range, and client.
 * Default sort order is by invoice date (newest first).
 *
 * **Permissions Required:** `invoices.read`
 *
 * **Rate Limit:** 300 requests per minute
 * "
 * )
 */
```

### 6. Not Documenting Authentication

**Bad**: No authentication information

```php
/**
 * @OA\Get(path="/api/invoices")
 */
```

**Good**: Clear authentication requirements

```php
/**
 * @OA\Get(
 *     path="/api/invoices",
 *     security={{"sanctum":{}}},
 *     description="Requires valid Sanctum authentication token in Bearer header"
 * )
 *
 * // Or for specific scopes:
 * @OA\Get(
 *     path="/api/admin/users",
 *     security={{"oauth2":{"admin"}}},
 *     description="Requires OAuth2 token with 'admin' scope"
 * )
 */
```

## Integration with Other Skills

This skill integrates with:

- **laravel-test-suite**: Write API tests that validate OpenAPI schema compliance
- **permission-audit**: Document authorization requirements and scopes for each endpoint
- **security-expert**: Implement and document API security measures (rate limiting, auth)
- **dutch-tax-compliance**: Document VAT calculations, payroll endpoints, Digipoort integration
- **database-migration-check**: Ensure schema changes are reflected in API documentation
- **frontend-debugger**: Use generated OpenAPI spec for client-side SDK generation
- **flutter-dart-expert**: Generate Dart/Flutter SDK from OpenAPI specification

## API Documentation Maintenance Checklist

### Pre-Deployment Checklist

- [ ] All new endpoints documented with @OA annotations
- [ ] All request parameters have types, descriptions, and examples
- [ ] All response codes documented (200, 201, 400, 401, 403, 404, 422, 500)
- [ ] Error responses include error codes and messages
- [ ] Examples provided for all request/response bodies
- [ ] Authentication requirements specified for all protected endpoints
- [ ] Rate limits documented for sensitive operations
- [ ] Breaking changes noted in version description
- [ ] Generate fresh documentation: `php artisan l5-swagger:generate`
- [ ] Test documentation UI at `/api/documentation`
- [ ] Validate OpenAPI spec with swagger-cli

### Monthly Maintenance

- [ ] Review deprecated endpoints and set sunset dates
- [ ] Update version numbers for any breaking changes
- [ ] Verify all examples still work
- [ ] Check for outdated descriptions
- [ ] Regenerate SDKs if schema changed
- [ ] Update migration guides
- [ ] Review and update rate limits

### Quarterly Review

- [ ] Audit all endpoints for consistency
- [ ] Review error code coverage
- [ ] Update documentation based on user feedback
- [ ] Check for missing security documentation
- [ ] Validate against actual API responses
- [ ] Update changelog with all changes

## Resources

- **OpenAPI Specification**: https://swagger.io/specification/
- **L5-Swagger Documentation**: https://github.com/DarkaOnLine/L5-Swagger
- **Swagger Editor**: https://editor.swagger.io/
- **OpenAPI Generator**: https://openapi-generator.tech/
- **Postman Learning Center**: https://learning.postman.com/
- **API Design Guidelines**: https://docs.microsoft.com/en-us/azure/architecture/best-practices/api-design

---

*Version 3.0.0 - Enhanced with Dutch bookkeeping examples, extended best practices, anti-patterns, and skill integrations*
