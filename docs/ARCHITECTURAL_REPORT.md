# OsintWeb: Enterprise Architecture Assessment & Future Roadmap

**Date:** 2026-02-26
**Status:** 100% Feature Complete -> transitioning to Enterprise Scale
**Architect:** Jules (Ultimate Enterprise AI Architect)

---

## 1. Strategic Overview

**Current State:**
The platform has achieved "Feature Complete" status, boasting an impressive array of OSINT capabilities (Geolocation, Source Verification, Social Media Monitoring, etc.). The codebase is a robust Laravel Monolith with a rich domain model and extensive API surface. It successfully integrates complex logic (astronomical calculations, media analysis) directly into Service classes.

**Strategic Direction:**
To evolve from a "Feature-Rich Product" to a "Global Enterprise Platform", the focus must shift from *accretion* (adding features) to *refinement* and *scale*. The next phase targets **Observability**, **Resilience**, and **Modularization**. We will transition specific high-load domains (Ingestion, Analysis) into isolated contexts to prevent monolithic bloat and enable independent scaling.

**Primary Goal:** Enable multi-team development velocity and sub-second global query performance while maintaining the current feature set.

---

## 2. Architectural Design

**Current Pattern:** Layered Monolith (Controller -> Service -> Model).
**Target Pattern:** Modular Monolith with Event-Driven Communication for cross-boundary interactions.

**Key Decisions:**
-   **Bounded Contexts:** Strictly define boundaries for `Intelligence`, `Operations`, `Identity`, and `Analysis`.
-   **Communication:**
    -   *Intra-module:* Direct Service calls (synchronous).
    -   *Inter-module:* Domain Events (asynchronous) via `spatie/laravel-event-sourcing` or standard Laravel Events + Queue.
-   **Anti-Corruption Layer:** Introduce DTOs at Service boundaries to decouple internal domain logic from HTTP/API contracts.

**Justification:**
A full Microservices rewrite is premature and introduces unnecessary distributed system complexity. A Modular Monolith allows us to refactor boundaries logically before physically separating services if scale demands it later.

---

## 3. Backend Layer Structure (Laravel)

**Refactoring Targets:**
-   **Controllers:** Currently contain some business logic (e.g., `EventController` handling spatial query construction).
    -   *Change:* Controllers must strictly be HTTP Gateways. They accept a Request, map it to a DTO/Command, dispatch it, and map the Result to a Response.
-   **Services:** Currently implement "Transaction Scripts" (e.g., `GeolocationService` mixes astronomical math with DB transactions).
    -   *Change:* Extract pure domain logic (Math, parsing) into `Domain\Logic` classes (e.g., `SunPositionCalculator`). Keep Application Services focused on orchestration (Flow, Transaction, Event Dispatch).
-   **Domain Models:** Rich models are good, but ensure they don't become "God Objects".
    -   *Change:* Use dedicated Query Builders or Repository pattern for complex spatial queries (e.g., `Event::nearLocation(...)`) to keep Models clean.

**Proposed Structure:**
```
src/
  Domains/
    Intelligence/
      Actions/      (Single-purpose business logic)
      DataTransfer/ (DTOs)
      Models/
      Services/
    Operations/
    Shared/
```

---

## 4. Database & Query Design

**Current:** MySQL 8.0 with Spatial Extensions.
**Optimization:**
-   **Spatial Indexing:** The `ST_Distance_Sphere` queries in `EventController` are computationally expensive on large datasets.
    -   *Action:* Ensure `SPATIAL INDEX` is verified on `location` columns. Consider pre-calculating `geohash` for rough bounding-box filtering before precise spatial calculation.
-   **CQRS (Read Models):** The `Event` model is heavy.
    -   *Action:* Introduce a specific "Map Read Model" (flattened JSON or specialized table) optimized purely for the high-traffic Map View, decoupling it from the complex Write Model.
-   **Soft Deletes:** heavily used; ensure all unique constraints account for `deleted_at`.

---

## 5. API Design & Integration

**Current:** REST API with Sanctum.
**Evolution:**
-   **Contract-First:** Adopt OpenAPI (Swagger) as the source of truth, not just documentation generated from code. This allows frontend teams to work in parallel with backend.
-   **Versioning:** Implement URI Versioning (`/api/v1/...`) immediately to prepare for breaking changes during the Modularization refactor.
-   **Rate Limiting:** Differentiate limits by "Complexity Cost" (e.g., `GET /events` costs 1 token, `POST /geolocation/analyze` costs 50 tokens).

---

## 6. UX Flow & Information Architecture

**Philosophy:** "Progressive Disclosure".
**Observation:** The "Digital War Room" and "SITREP Builder" are complex power-user tools.
**Recommendation:**
-   **Workspace Context:** Users should select a "Mission" or "Operation" context upon login, filtering all global data to relevant operational theaters.
-   **Command Palette:** Implement a global `Cmd+K` interface for rapid navigation ("Jump to Event...", "Create SITREP..."), essential for power users.

---

## 7. UI System & Visual Design Considerations

**Current:** Vue 3 + Tailwind (inferred).
**Strategy:**
-   **Design Tokens:** Abstract colors, spacing, and typography into a `theme.json`. This supports white-labeling (critical for SaaS/Gov clients) and Dark/Light/High-Contrast modes (Accessibility).
-   **Component Library:** Extract "Map Widgets" (Time Slider, Layer Toggle) into a standalone library. This allows third-party integrations or "Mini-Maps" to be embedded in external sites.

---

## 8. Performance & Scaling Considerations

**Bottlenecks:**
-   **Geospatial Queries:** MySQL is good, but PostGIS is better. If spatial query load > 30% of DB CPU, migrate to PostgreSQL + PostGIS.
-   **Ingestion Pipelines:** Social Media/Flight Tracking ingestion can spike.
    -   *Action:* Isolate Ingestion Workers to a separate auto-scaling group (e.g., AWS Fargate/K8s) to protect the API's responsiveness.
-   **Caching:** aggressive edge caching (Cloudflare/Varnish) for public read-only endpoints (`/api/public/events`).

---

## 9. Security Considerations

**Audit Trail:**
-   **Current:** `AuditLog::create` in Services.
-   **Evolution:** Move to **Event Sourcing** for audit logs. Every state change emits an event; a listener writes the Audit Log. This guarantees the log is never "forgotten" and decoupling improves performance.
-   **Row Level Security (RLS):** For multi-agency usage, implement RLS (or Tenant Scope) at the database or Model Global Scope level to strictly enforce data segregation.

---

## 10. Testing Strategy

**Shift Left:**
-   **Unit:** Test pure logic (Math, Parsers) in isolation (PestPHP).
-   **Integration:** Test Service classes with an in-memory SQLite database.
-   **E2E:** Cypress/Playwright tests for critical "Mission Flows" (e.g., "Submit Tip -> Verify -> Publish").
-   **Mutation Testing:** Introduce Infection PHP to ensure tests are actually verifying logic, not just coverage.

---

## 11. Extensibility / Plugin Architecture

**Requirement:** Allow third-party data providers (e.g., Maxar Satellite feeds).
**Design:**
-   **Provider Pattern:** Define strictly typed Interfaces (`SatelliteProvider`, `SocialMediaProvider`).
-   **Adapter Registry:** The main application queries a Registry. Plugins register their Adapters at boot.
-   **Webhooks:** Outbound webhooks for "Event Verified" triggers to integrate with external Command & Control systems.

---

## 12. SaaS / E-Commerce / Platform-specific Considerations

**Monetization / Access:**
-   **Tiered Access:** "Public" (Delayed/Low-Res), "Pro" (Real-time/High-Res), "Enterprise" (API Access + SSO).
-   **Usage Metering:** Track "Analysis Operations" (e.g., AI Credits) using Redis atomic counters.
-   **White-Labeling:** Allow Enterprise clients to host the platform on their own domain (CNAME) with custom branding (see UI System).

---

## 13. Trade-offs & Future Evolution

**Trade-off:**
-   *Complexity vs. Speed:* Moving to a Modular Monolith increases initial development friction (DTO mapping) but prevents "Spaghetti Code" in the long run.
-   *Performance vs. Freshness:* Read Models introduce "Eventual Consistency" (milliseconds delay). Acceptable for Map View, maybe not for "Edit" screens.

**Future Evolution:**
1.  **Phase 1:** Refactor `Event` and `Intelligence` into Bounded Contexts.
2.  **Phase 2:** Extract Ingestion Pipelines to separate worker services.
3.  **Phase 3:** Introduce PostGIS if MySQL spatial limits are reached.
