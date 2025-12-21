# Development Guidelines

## Core Rules

### Documentation Requirements
- **After every feature commit**, the `README.md` in the root of the repository MUST be updated to reflect the new feature
- Keep README.md sections current: Features, Installation, Usage, API Documentation, Changelog

### Code Attribution
- **NEVER** mention AI assistants, language models, or automated code generation tools in:
  - Source code comments
  - Documentation files
  - Commit messages
  - UI text or about pages
  - Configuration files
  - Package descriptions
  - License files
  - Any user-facing content

### Commit Standards
- Use conventional commit format: `type(scope): description`
- Types: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`
- Keep commits atomic and focused on single changes
- Update README.md in the same commit or immediately following feature commits

## Project Architecture

### Technology Stack
- **Backend**: Laravel 11+ (PHP 8.2+)
- **Frontend**: Vue.js 3 with TypeScript
- **Maps**: Leaflet.js with OpenStreetMap
- **Database**: MySQL 8.0+ with spatial extensions
- **Cache**: Laravel file/database cache (no Redis required)
- **Search**: Meilisearch (or MySQL full-text for shared hosting)
- **Queue**: Laravel database queues

### Directory Structure
```
/app
  /Models          - Eloquent models
  /Http
    /Controllers   - API and web controllers
    /Requests      - Form request validation
    /Resources     - API resources
  /Services        - Business logic services
  /Events          - Event classes
  /Listeners       - Event listeners
  /Jobs            - Queue jobs
  /Policies        - Authorization policies
/resources
  /js              - Vue.js frontend
    /components    - Vue components
    /composables   - Vue composables
    /stores        - Pinia stores
    /types         - TypeScript types
  /views           - Blade templates
/database
  /migrations      - Database migrations
  /seeders         - Database seeders
/tests
  /Feature         - Feature tests
  /Unit            - Unit tests
```

## Code Quality

### PHP Standards
- Follow PSR-12 coding standards
- Use strict types in all PHP files
- Type hint all parameters and return types
- Use dependency injection
- Write PHPDoc blocks for public methods

### TypeScript Standards
- Enable strict mode
- No `any` types unless absolutely necessary
- Use interfaces for object shapes
- Use composition API with `<script setup>`

### Testing Requirements
- Minimum 80% code coverage for new features
- Write feature tests for all API endpoints
- Write unit tests for services and complex logic
- Use factories for test data

## Security Guidelines

### Data Handling
- Sanitize all user inputs
- Use parameterized queries (Eloquent handles this)
- Validate file uploads strictly
- Implement rate limiting on all endpoints

### Authentication
- Use Laravel Sanctum for API authentication
- Implement proper RBAC (Role-Based Access Control)
- Log all authentication events
- Enforce strong password policies

### OSINT-Specific Security
- Never store credentials for external services in code
- Implement audit logs for sensitive operations
- Allow users to export and delete their data
- Anonymize user data in analytics

### GDPR & Privacy Compliance
All features MUST be designed with GDPR compliance in mind:
- **Data Minimization**: Only collect data that is strictly necessary
- **Consent Management**: Track and log all user consent with timestamps
- **Right to Access**: Users must be able to view all their personal data
- **Right to Portability**: Implement data export in machine-readable formats (JSON, CSV)
- **Right to be Forgotten**: Provide account deletion with complete data removal
- **Data Retention**: Define and enforce retention periods for all data types
- **Privacy by Design**: Build privacy controls into every feature from the start
- **Audit Trail**: Log all data access and modifications for compliance
- **Cookie Consent**: Implement proper cookie consent for analytics/marketing
- **Third-Party Data Sharing**: Document and allow opt-out of any data sharing
- **Breach Notification**: Implement mechanisms to detect and report data breaches
- **Age Verification**: Consider age restrictions where applicable

## Database Conventions

### Naming
- Tables: plural, snake_case (e.g., `military_equipment`)
- Columns: singular, snake_case (e.g., `created_at`)
- Foreign keys: `{table_singular}_id` (e.g., `country_id`)
- Pivot tables: alphabetical order (e.g., `equipment_event`)

### Required Fields
- All tables must have: `id`, `created_at`, `updated_at`
- Soft deletes for user-generated content
- `uuid` for public-facing identifiers

## API Design

### REST Conventions
- Use plural nouns for resources
- Use HTTP methods correctly (GET, POST, PUT, PATCH, DELETE)
- Return appropriate status codes
- Paginate list endpoints (default: 25 per page)

### Response Format
```json
{
  "data": {},
  "meta": {
    "pagination": {}
  },
  "links": {}
}
```

### Error Format
```json
{
  "message": "Human readable message",
  "errors": {
    "field": ["Error details"]
  }
}
```

## Git Workflow

### Branches
- `main` - Production-ready code
- `develop` - Integration branch
- `feature/*` - New features
- `fix/*` - Bug fixes
- `hotfix/*` - Emergency production fixes

### Pull Requests
- Require at least one review
- All tests must pass
- Update documentation
- Squash commits when merging

## Performance

### Caching Strategy
- Cache expensive database queries
- Development: File-based cache (storage/framework/cache)
- Production: Database cache table
- Implement query result caching for map data
- Cache external API responses
- No Redis required - simplified hosting

### Database Optimization
- Index foreign keys
- Use eager loading to prevent N+1 queries
- Implement database query logging in development
- Use database transactions for multi-step operations

## Deployment

### Environment Variables
- Never commit `.env` files
- Document all required variables in `.env.example`
- Use different values for each environment

### Health Checks
- Implement `/health` endpoint
- Check database connectivity
- Check cache functionality
- Check search service availability (if using Meilisearch)
- Check external service dependencies
