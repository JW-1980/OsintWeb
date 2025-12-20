# OsintWeb - Complete Feature Specification

## Table of Contents
1. [Project Overview](#project-overview)
2. [Technology Recommendation](#technology-recommendation)
3. [Core Features](#core-features)
4. [Military Equipment Database](#military-equipment-database)
5. [Event System](#event-system)
6. [Timeline System](#timeline-system)
7. [Map Features](#map-features)
8. [Export & Integration](#export--integration)
9. [User Management](#user-management)
10. [Installation Wizard](#installation-wizard)
11. [15 Major OSINT Features](#15-major-osint-features)
12. [Database Schema](#database-schema)
13. [API Endpoints](#api-endpoints)
14. [UI/UX Specifications](#uiux-specifications)
15. [Implementation Phases](#implementation-phases)

---

## 1. Project Overview

OsintWeb is a comprehensive Open Source Intelligence (OSINT) platform designed for tracking, analyzing, and documenting military conflicts and geopolitical events. The platform provides tools for mapping territorial control, tracking military equipment, documenting events, and analyzing changes over time.

### Target Users
- OSINT analysts and researchers
- Journalists covering conflicts
- Academic researchers
- NGOs and humanitarian organizations
- Defense analysts
- Intelligence professionals

### Key Objectives
- Provide accurate, verifiable conflict mapping
- Enable collaborative intelligence gathering
- Maintain historical records for analysis
- Support evidence-based reporting

---

## 2. Technology Recommendation

### Recommended: Laravel (Not WordPress)

**Why Laravel over WordPress:**

| Aspect | Laravel | WordPress Plugin |
|--------|---------|------------------|
| **Custom Data Models** | Full flexibility with Eloquent ORM | Limited by WP post/meta structure |
| **Geospatial Queries** | Native PostGIS support | Would require custom implementations |
| **API Performance** | Optimized REST/GraphQL APIs | REST API has overhead |
| **Real-time Features** | Laravel Echo + WebSockets | Limited without heavy customization |
| **Scalability** | Horizontal scaling, queue workers | Single-server limitations |
| **Complex Relationships** | Many-to-many, polymorphic relations | Meta tables become unwieldy |
| **Testing** | PHPUnit, Pest built-in | Testing is an afterthought |
| **Security** | CSRF, XSS, SQL injection protection | Varies by plugin ecosystem |
| **Version Control** | Code-first, migrations | Database-heavy, harder to version |

**Conclusion**: WordPress is designed for content management. This application requires complex relational data, geospatial queries, real-time updates, and custom business logic that Laravel handles natively.

### Recommended Stack

```
Backend:
- Laravel 11+ (PHP 8.2+)
- MySQL 8.0+ with spatial extensions
- Laravel file/database cache (no Redis required)
- Meilisearch (full-text search, optional)

Frontend:
- Vue.js 3 + TypeScript
- Pinia (state management)
- Leaflet.js (mapping)
- TailwindCSS

Infrastructure:
- Nginx or Apache (shared hosting compatible)
- Laravel database queues
- Laravel Telescope (debugging)
- Optional: Docker for local development
```

**Note:** See [MySQL Stack Specification](MYSQL_STACK_SPECIFICATION.md) for complete database architecture, hosting recommendations, and migration guides.

---

## 3. Core Features

### 3.1 Interactive Map System

#### Area Control Mapping
```
Feature: Territory Control Zones
- Draw polygons on map using drawing tools
- Assign control to factions/countries
- Color-code areas by controlling entity
- Hatching/patterns for disputed areas
- Opacity control for overlapping claims
- Auto-calculate area in km²
```

**Implementation Details:**
```javascript
// Zone data structure
interface ControlZone {
  id: string;
  name: string;
  geometry: GeoJSON.Polygon;
  controller_id: number;      // Reference to faction/country
  control_type: 'full' | 'contested' | 'claimed';
  valid_from: DateTime;
  valid_to: DateTime | null;  // null = current
  source_url: string;
  confidence: 'confirmed' | 'likely' | 'unconfirmed';
  notes: string;
}
```

#### Legend System
```
Feature: Dynamic Map Legend
- Auto-generated from active layers
- Color swatches with entity names
- Toggle visibility per legend item
- Collapsible legend panel
- Print-friendly legend export
- Custom ordering of legend items
```

### 3.2 Timeline System

#### Core Timeline Features
```
Feature: Historical Playback
- Date range selector (from/to)
- Play/pause animation through dates
- Speed control (1x, 2x, 5x, 10x)
- Step forward/backward by day/week/month
- Current date display
- Events list for current date
```

#### Timeline Data Structure
```javascript
interface TimelineState {
  currentDate: Date;
  startDate: Date;
  endDate: Date;
  isPlaying: boolean;
  playbackSpeed: number;
  activeFilters: {
    eventTypes: string[];
    factions: number[];
    equipmentTypes: string[];
  };
}
```

#### Timeline Snapshots
```
Feature: State Snapshots
- Save map state at any point
- Compare two dates side-by-side
- Generate diff reports between dates
- Export timeline as video/GIF
- Share snapshot via URL
```

---

## 4. Military Equipment Database

### 4.1 Equipment Categories

#### Naval Vessels
```
Categories:
- Aircraft Carriers
- Battleships
- Cruisers
- Destroyers
- Frigates
- Corvettes
- Submarines (Attack, Ballistic, Cruise Missile)
- Amphibious Assault Ships
- Landing Ships
- Patrol Boats
- Minesweepers
- Support Ships
- Hospital Ships
```

#### Land Vehicles
```
Categories:
- Main Battle Tanks
- Light Tanks
- Infantry Fighting Vehicles
- Armored Personnel Carriers
- Mine-Resistant Vehicles (MRAP)
- Self-Propelled Artillery
- Towed Artillery
- Multiple Launch Rocket Systems (MLRS)
- Surface-to-Air Missile Systems
- Anti-Tank Missile Systems
- Radar Systems
- Engineering Vehicles
- Recovery Vehicles
- Logistics Trucks
```

#### Aircraft
```
Categories:
- Fighter Jets (Air Superiority, Multirole)
- Bomber Aircraft
- Attack Aircraft
- Reconnaissance Aircraft
- Electronic Warfare Aircraft
- AWACS/AEW Aircraft
- Transport Aircraft
- Tanker Aircraft
- Maritime Patrol Aircraft
- Trainer Aircraft
```

#### Helicopters
```
Categories:
- Attack Helicopters
- Transport Helicopters
- Utility Helicopters
- Naval Helicopters
- Search and Rescue
- Electronic Warfare Helicopters
```

#### Missile Systems (Hand-Launched/Portable)
```
Categories:
- MANPADS (Man-Portable Air Defense)
- Anti-Tank Guided Missiles (ATGM)
- Shoulder-Launched Rocket Systems
- Grenade Launchers (Mounted)
- Portable SAM Systems
```

### 4.2 Equipment Data Model

```php
// Database Schema
Schema::create('military_equipment', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('designation');           // e.g., "T-90M"
    $table->string('nato_designation')->nullable();  // e.g., "Flanker"
    $table->string('common_name');           // e.g., "Proryv-3"
    $table->foreignId('country_id');         // Origin country
    $table->foreignId('category_id');        // Equipment category
    $table->foreignId('subcategory_id');

    // Specifications (JSON for flexibility)
    $table->json('specifications');
    /*
    {
        "crew": 3,
        "weight_kg": 48000,
        "length_m": 9.53,
        "width_m": 3.78,
        "height_m": 2.22,
        "max_speed_kmh": 60,
        "range_km": 550,
        "armament": [...],
        "armor_type": "Composite + ERA",
        "engine": "V-92S2F diesel, 1130hp"
    }
    */

    $table->year('introduced_year');
    $table->integer('estimated_units_produced')->nullable();
    $table->text('description');
    $table->string('image_url')->nullable();
    $table->json('variant_of')->nullable();  // Parent equipment IDs

    $table->timestamps();
    $table->softDeletes();
});

// Equipment per country inventory
Schema::create('country_equipment', function (Blueprint $table) {
    $table->id();
    $table->foreignId('country_id');
    $table->foreignId('equipment_id');
    $table->integer('quantity_active');
    $table->integer('quantity_reserve')->nullable();
    $table->integer('quantity_ordered')->nullable();
    $table->date('data_as_of');
    $table->string('source_url');
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### 4.3 Equipment Features

```
Feature: Equipment Catalog
- Searchable database with filters
- Category/subcategory browsing
- Comparison tool (side-by-side specs)
- Country inventory view
- Equipment family trees (variants)
- Image gallery per equipment
- External links (Wikipedia, manufacturer)

Feature: Equipment Status Tracking
- Track individual units when identified
- Serial numbers/unit markings
- Current status (active, damaged, destroyed, captured)
- Location history
- Associated events
```

---

## 5. Event System

### 5.1 Event Types (20+ Templates)

Each event type has specific fields and validation rules.

#### Combat Events

**1. Airstrike**
```yaml
name: Airstrike
icon: plane-departure
color: "#FF5722"
fields:
  - target_type: [military, infrastructure, civilian, unknown]
  - aircraft_type: equipment_reference (nullable)
  - munitions_used: text (nullable)
  - damage_assessment: [destroyed, heavy, moderate, light, unknown]
  - casualties_reported: integer (nullable)
required_media: false
supports:
  - images: true
  - videos: true
  - equipment_status: true
  - source_links: true
  - casualties: true
```

**2. Artillery Strike**
```yaml
name: Artillery Strike
icon: crosshairs
color: "#E91E63"
fields:
  - weapon_system: equipment_reference (nullable)
  - estimated_rounds: integer (nullable)
  - target_type: [military, infrastructure, civilian, unknown]
  - crater_analysis: boolean
required_media: false
supports:
  - images: true
  - videos: true
  - equipment_status: true
  - source_links: true
```

**3. Missile Strike**
```yaml
name: Missile Strike
icon: rocket
color: "#9C27B0"
fields:
  - missile_type: equipment_reference (nullable)
  - launch_location: coordinates (nullable)
  - intercepted: boolean
  - intercept_system: equipment_reference (nullable)
  - target_type: [military, infrastructure, civilian, unknown]
required_media: false
supports:
  - images: true
  - videos: true
  - equipment_status: true
  - source_links: true
```

**4. Drone Strike/Attack**
```yaml
name: Drone Strike
icon: drone
color: "#673AB7"
fields:
  - drone_type: [reconnaissance, attack, loitering, unknown]
  - drone_model: equipment_reference (nullable)
  - attack_type: [kamikaze, munition_drop, other]
  - target_type: [military, infrastructure, civilian, unknown]
required_media: false
supports:
  - images: true
  - videos: true
  - equipment_status: true
  - source_links: true
```

**5. Ground Battle/Engagement**
```yaml
name: Ground Battle
icon: shield-alt
color: "#3F51B5"
fields:
  - battle_name: text (nullable)
  - factions_involved: faction_references[]
  - battle_type: [offensive, defensive, ambush, meeting_engagement]
  - duration_hours: integer (nullable)
  - outcome: [ongoing, victory_faction1, victory_faction2, stalemate, withdrawal]
required_media: false
supports:
  - images: true
  - videos: true
  - equipment_status: true
  - source_links: true
  - casualties: true
```

**6. Naval Engagement**
```yaml
name: Naval Engagement
icon: ship
color: "#2196F3"
fields:
  - vessels_involved: equipment_reference[]
  - engagement_type: [surface, submarine, air_naval, coastal]
  - location_type: [open_sea, coastal, port, strait]
required_media: false
supports:
  - images: true
  - videos: true
  - equipment_status: true
  - source_links: true
```

#### Equipment Events

**7. Equipment Destroyed**
```yaml
name: Equipment Destroyed
icon: fire-alt
color: "#F44336"
fields:
  - equipment: equipment_reference (required)
  - destruction_method: [combat, accident, sabotage, scuttled, unknown]
  - destroyed_by: faction_reference (nullable)
  - confirmed_by: [visual, satellite, official, multiple_sources]
required_media: true  # Visual confirmation preferred
supports:
  - images: true
  - videos: true
  - equipment_status: true
  - source_links: true
```

**8. Equipment Damaged**
```yaml
name: Equipment Damaged
icon: tools
color: "#FF9800"
fields:
  - equipment: equipment_reference (required)
  - damage_level: [light, moderate, heavy, mission_kill]
  - damage_type: [mobility, firepower, sensors, structural]
  - repairable: [yes, no, unknown]
required_media: true
supports:
  - images: true
  - videos: true
  - equipment_status: true
  - source_links: true
```

**9. Equipment Captured**
```yaml
name: Equipment Captured
icon: hand-holding
color: "#4CAF50"
fields:
  - equipment: equipment_reference (required)
  - captured_by: faction_reference (required)
  - condition: [operational, damaged, destroyed_after]
  - quantity: integer (default: 1)
required_media: true
supports:
  - images: true
  - videos: true
  - equipment_status: true
  - source_links: true
```

**10. Equipment Abandoned**
```yaml
name: Equipment Abandoned
icon: sign-out-alt
color: "#795548"
fields:
  - equipment: equipment_reference (required)
  - abandoned_by: faction_reference (required)
  - condition: [operational, damaged, booby_trapped, unknown]
  - reason: [retreat, mechanical, tactical, unknown]
required_media: false
supports:
  - images: true
  - videos: true
  - equipment_status: true
  - source_links: true
```

#### Sightings & Intelligence

**11. Equipment Sighting**
```yaml
name: Equipment Sighting
icon: binoculars
color: "#00BCD4"
fields:
  - equipment: equipment_reference (required)
  - quantity: integer (default: 1)
  - operator: faction_reference (nullable)
  - activity: [stationary, moving, combat, training, transport]
  - direction_of_travel: text (nullable)
required_media: true
supports:
  - images: true
  - videos: true
  - source_links: true
```

**12. Troop Movement**
```yaml
name: Troop Movement
icon: users
color: "#009688"
fields:
  - faction: faction_reference (required)
  - estimated_size: [squad, platoon, company, battalion, regiment, division]
  - movement_type: [advance, retreat, redeployment, reinforcement]
  - from_location: coordinates (nullable)
  - to_location: coordinates (nullable)
required_media: false
supports:
  - images: true
  - videos: true
  - source_links: true
```

**13. Convoy Spotted**
```yaml
name: Convoy Spotted
icon: truck-moving
color: "#8BC34A"
fields:
  - convoy_type: [military, humanitarian, mixed, unknown]
  - vehicle_count: integer (nullable)
  - equipment_types: equipment_reference[]
  - direction: text (nullable)
  - faction: faction_reference (nullable)
required_media: true
supports:
  - images: true
  - videos: true
  - source_links: true
```

#### Infrastructure Events

**14. Infrastructure Damage**
```yaml
name: Infrastructure Damage
icon: building
color: "#607D8B"
fields:
  - infrastructure_type: [bridge, power_plant, factory, hospital, school,
                          government, residential, transport_hub, dam, other]
  - damage_level: [destroyed, heavy, moderate, light]
  - cause: [airstrike, artillery, missile, ground_attack, unknown]
  - civilian_impact: text (nullable)
required_media: true
supports:
  - images: true
  - videos: true
  - source_links: true
```

**15. Fortification/Defense Position**
```yaml
name: Fortification
icon: chess-rook
color: "#455A64"
fields:
  - fortification_type: [trench, bunker, checkpoint, minefield,
                         anti_tank_obstacles, defensive_line]
  - constructed_by: faction_reference
  - status: [under_construction, active, abandoned, destroyed]
required_media: false
supports:
  - images: true
  - videos: true
  - source_links: true
```

#### Humanitarian Events

**16. Civilian Casualty Incident**
```yaml
name: Civilian Casualties
icon: heart-broken
color: "#B71C1C"
fields:
  - casualty_count: integer (nullable)
  - injury_count: integer (nullable)
  - cause: [airstrike, artillery, missile, ground_combat, ied, unknown]
  - verified_by: text[]
required_media: false
supports:
  - images: true
  - videos: true
  - source_links: true
```

**17. Evacuation/Displacement**
```yaml
name: Evacuation
icon: running
color: "#1565C0"
fields:
  - evacuation_type: [military, civilian, humanitarian_corridor]
  - estimated_people: integer (nullable)
  - from_location: text
  - to_location: text (nullable)
  - status: [announced, ongoing, completed, blocked]
required_media: false
supports:
  - images: true
  - videos: true
  - source_links: true
```

#### Political/Strategic Events

**18. Territory Change**
```yaml
name: Territory Change
icon: flag
color: "#1B5E20"
fields:
  - change_type: [captured, recaptured, ceded, disputed]
  - previous_controller: faction_reference
  - new_controller: faction_reference
  - location_name: text
  - strategic_importance: [critical, high, medium, low]
required_media: false
supports:
  - images: true
  - videos: true
  - source_links: true
  - control_zone_update: true
```

**19. Ceasefire/Agreement**
```yaml
name: Ceasefire
icon: handshake
color: "#43A047"
fields:
  - agreement_type: [ceasefire, prisoner_exchange, humanitarian_pause,
                     surrender, peace_agreement]
  - parties: faction_reference[]
  - duration: text (nullable)
  - terms: text (nullable)
  - status: [announced, in_effect, violated, expired]
required_media: false
supports:
  - images: false
  - videos: false
  - source_links: true
```

**20. Military Announcement**
```yaml
name: Military Announcement
icon: bullhorn
color: "#FFC107"
fields:
  - announcement_type: [mobilization, operation_start, operation_end,
                        casualty_report, equipment_claim, other]
  - source_faction: faction_reference
  - claim_verified: [verified, unverified, disputed, false]
required_media: false
supports:
  - images: true
  - videos: true
  - source_links: true
```

#### Special Events

**21. Explosion (Unknown Cause)**
```yaml
name: Explosion
icon: bomb
color: "#D32F2F"
fields:
  - explosion_size: [small, medium, large, massive]
  - suspected_cause: [ammunition_depot, fuel_depot, industrial,
                      sabotage, combat, unknown]
  - secondary_explosions: boolean
required_media: true
supports:
  - images: true
  - videos: true
  - source_links: true
```

**22. Fire/Blaze**
```yaml
name: Fire
icon: fire
color: "#FF5722"
fields:
  - fire_type: [wildfire, structural, industrial, military_target]
  - suspected_cause: [combat, accident, arson, natural, unknown]
  - area_affected: text (nullable)
required_media: true
supports:
  - images: true
  - videos: true
  - source_links: true
```

**23. Cyber Attack**
```yaml
name: Cyber Attack
icon: laptop-code
color: "#6A1B9A"
fields:
  - target_type: [government, military, infrastructure, media, financial]
  - attack_type: [ddos, ransomware, data_breach, defacement, other]
  - attributed_to: faction_reference (nullable)
  - impact_level: [critical, high, medium, low, unknown]
required_media: false
supports:
  - images: true
  - videos: false
  - source_links: true
```

**24. POW/Hostage Event**
```yaml
name: POW Event
icon: user-lock
color: "#4A148C"
fields:
  - event_type: [capture, release, exchange, death_in_custody]
  - personnel_count: integer (nullable)
  - faction_captured: faction_reference (nullable)
  - faction_holding: faction_reference (nullable)
required_media: false
supports:
  - images: true
  - videos: true
  - source_links: true
```

### 5.2 Event Data Model

```php
Schema::create('events', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('event_type_id');
    $table->foreignId('user_id');              // Creator
    $table->string('title');
    $table->text('description')->nullable();

    // Location
    $table->geography('location', 'point');    // PostGIS point
    $table->string('location_name')->nullable();
    $table->string('location_accuracy')->default('exact'); // exact, approximate, area

    // Timing
    $table->dateTime('occurred_at');
    $table->dateTime('occurred_at_end')->nullable();  // For duration events
    $table->string('date_accuracy')->default('exact'); // exact, day, week, month

    // Verification
    $table->enum('status', ['pending', 'verified', 'disputed', 'rejected']);
    $table->integer('verification_score')->default(0);

    // Custom fields (JSON, validated against event type schema)
    $table->json('custom_fields');

    $table->timestamps();
    $table->softDeletes();

    // Indexes
    $table->index('occurred_at');
    $table->index('status');
    $table->spatialIndex('location');
});

// Event media
Schema::create('event_media', function (Blueprint $table) {
    $table->id();
    $table->foreignId('event_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['image', 'video', 'document']);
    $table->string('file_path');
    $table->string('original_filename');
    $table->string('mime_type');
    $table->integer('file_size');
    $table->json('metadata')->nullable();  // EXIF, dimensions, etc.
    $table->text('caption')->nullable();
    $table->string('source_url')->nullable();
    $table->timestamps();
});

// Event sources
Schema::create('event_sources', function (Blueprint $table) {
    $table->id();
    $table->foreignId('event_id')->constrained()->cascadeOnDelete();
    $table->string('url');
    $table->string('source_type');  // social_media, news, official, satellite
    $table->string('source_name')->nullable();
    $table->dateTime('accessed_at');
    $table->text('archive_url')->nullable();  // Wayback machine, etc.
    $table->enum('reliability', ['high', 'medium', 'low', 'unknown']);
    $table->timestamps();
});

// Equipment status in events
Schema::create('event_equipment', function (Blueprint $table) {
    $table->id();
    $table->foreignId('event_id')->constrained()->cascadeOnDelete();
    $table->foreignId('equipment_id')->constrained('military_equipment');
    $table->integer('quantity')->default(1);
    $table->enum('status', ['destroyed', 'damaged', 'captured', 'abandoned', 'sighted']);
    $table->foreignId('operator_faction_id')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### 5.3 Event Input Methods

#### Click on Map
```
Feature: Map Click Event Creation
1. User clicks "Add Event" button or uses keyboard shortcut
2. Cursor changes to crosshair
3. User clicks location on map
4. Modal opens with:
   - Auto-filled coordinates
   - Reverse-geocoded location name
   - Event type selector
   - Date/time picker (defaults to now)
   - Type-specific fields
5. User fills required fields
6. Optional: Add media, sources, equipment
7. Submit creates event and marker appears on map
```

#### Coordinate Entry
```
Feature: Manual Coordinate Entry
1. User opens "Add Event" modal
2. Location input accepts:
   - Decimal degrees: 48.8566, 2.3522
   - DMS: 48°51'23.8"N 2°21'7.9"E
   - UTM: 31U 448252 5411935
   - MGRS: 31UDQ4825211935
   - What3Words: ///filled.count.soap
3. Map preview shows pin at location
4. User can adjust by dragging pin
5. Continue with event details
```

---

## 6. Timeline System

### 6.1 Core Timeline Features

```
Feature: Interactive Timeline
- Horizontal timeline bar at bottom of map
- Draggable position indicator
- Tick marks for events
- Zoom levels: day, week, month, year
- Event density visualization (heatmap)
```

### 6.2 Historical Data Tracking

```
Feature: Version History
- All entities maintain full history
- Changes tracked with:
  - Previous value
  - New value
  - Changed by (user)
  - Changed at (timestamp)
  - Change reason (optional)

Feature: Point-in-Time Queries
- View map as it was on any date
- Control zones show historical boundaries
- Equipment shows historical status
- Events filter to selected date range
```

### 6.3 Comparison Tools

```
Feature: Date Comparison
- Split-screen view of two dates
- Synchronized navigation
- Highlight differences
- Generate comparison report

Feature: Change Reports
- Select date range
- List all changes:
  - Territory changes
  - Equipment losses
  - Events by type
- Export as PDF/Excel
```

---

## 7. Map Features

### 7.1 Base Layers

```
Layers Available:
- OpenStreetMap Standard
- OpenStreetMap Humanitarian
- Satellite (Mapbox/Esri)
- Terrain/Topographic
- Dark mode
- Custom uploaded layers
```

### 7.2 Drawing Tools

```
Tools:
- Point marker
- Line (for front lines, movements)
- Polygon (for control zones)
- Circle (for radius around point)
- Rectangle (for quick area selection)

Each with:
- Color picker
- Fill opacity
- Stroke width
- Pattern/hatching (for disputed areas)
- Label placement
```

### 7.3 Data Layers

```
Toggleable Layers:
- Control zones (by faction)
- Events (by type, filterable)
- Equipment positions
- Front lines
- Supply routes (if known)
- Defensive positions
- User custom layers

Per-layer controls:
- Visibility toggle
- Opacity slider
- Date filter
- Quick filters
```

---

## 8. Export & Integration

### 8.1 Google Maps/Earth Export

```
Feature: KML/KMZ Export
- Export visible layers to KML
- Include:
  - Control zones as polygons
  - Events as placemarks
  - Custom styling preserved
  - Time-enabled for animation

Feature: GeoJSON Export
- Full GeoJSON with properties
- Filtered by current view
- Include all metadata

Feature: CSV Export
- Tabular data for events
- Equipment inventories
- Loss tracking
```

### 8.2 API Integration

```
REST API Endpoints:
- Full CRUD for all entities
- Bulk import/export
- Webhook notifications
- Rate limiting per user tier

Public API (read-only):
- Event feed (filterable)
- Equipment database
- Country inventories
- Embeddable widgets
```

### 8.3 Data Import

```
Supported Formats:
- KML/KMZ
- GeoJSON
- GPX
- CSV (with coordinate columns)
- Shapefile
- Excel (with coordinate columns)
```

---

## 9. User Management

### 9.1 Authentication

```
Methods:
- Email/password
- OAuth (Google, GitHub, Twitter)
- Two-factor authentication (TOTP)
- API key for integrations
```

### 9.2 Roles & Permissions

```
Roles:
- Viewer: Read-only access
- Contributor: Create/edit own content
- Editor: Edit any content, moderate
- Admin: Full access, user management
- API User: Programmatic access only

Permissions:
- view_events
- create_events
- edit_own_events
- edit_all_events
- delete_events
- manage_equipment
- manage_zones
- manage_users
- access_api
- export_data
```

### 9.3 User Features

```
Profile:
- Avatar
- Bio
- Contribution statistics
- Expertise areas
- Social links

Preferences:
- Default map view
- Preferred units (metric/imperial)
- Email notifications
- UI language
- Timezone
```

### 9.4 CRUD Operations

```
Standard CRUD for:
- Events
- Equipment entries
- Control zones
- Factions/Countries
- Sources
- Media
- User profiles
- API keys
- Saved views
- Custom layers
```

---

## 10. Installation Wizard

A one-time setup wizard that runs automatically on first access and guides users through the complete installation process.

### 10.1 Wizard Steps

```
Step 1: Welcome & Requirements Check
- PHP version check (8.2+ required)
- Extension verification (mysql, mbstring, xml, curl, zip, bcmath, gd)
- Directory permission checks (storage/, .env)
- Display pass/fail status with solutions

Step 2: Database Configuration
- MySQL host, port, database name
- Username and password
- Connection test before proceeding
- Option to create database if not exists
- MySQL 8.0+ version verification

Step 3: Run Migrations
- Display list of migrations
- Progress bar during execution
- Success/error status per migration
- Option to seed initial data (countries, equipment categories)
- Option to load sample conflict data

Step 4: Application Settings
- Application name and URL
- Timezone and language
- Default map center (lat/lng) and zoom
- Registration settings (public/private, email verification)
- Security settings (session lifetime, rate limits, 2FA)

Step 5: Admin Account Creation
- Name, email, password
- Password requirements: 12+ chars, mixed case, numbers, symbols
- Email verification bypass for admin

Step 6: Email Configuration (Optional)
- SMTP settings or mail provider (Mailgun, SES, Postmark)
- Test email functionality
- Can skip for later configuration

Step 7: Search Configuration (Optional)
- MySQL Full-Text (default, no extra setup)
- Meilisearch (connection test)
- Algolia (API keys)

Step 8: Installation Complete
- Generate app key
- Clear and rebuild caches
- Create lock file (storage/installed)
- Display next steps and important URLs
```

### 10.2 Security Features

```
Installation Lock:
- Lock file created at storage/installed after completion
- Contains: installed_at, version, installer_ip, config_checksum
- Middleware blocks /install/* routes after installation
- Re-installation requires server access to delete lock file

Password Requirements:
- Minimum 12 characters
- Mixed case (upper + lower)
- At least 1 number
- At least 1 special character
- Real-time validation feedback
```

### 10.3 CLI Alternative

```bash
# Interactive CLI installer
php artisan osint:install

# Non-interactive with parameters
php artisan osint:install \
    --db-host=localhost \
    --db-port=3306 \
    --db-name=osintweb \
    --db-user=root \
    --db-pass=secret \
    --admin-name="Admin User" \
    --admin-email=admin@example.com \
    --admin-pass=SecurePassword123! \
    --app-url=https://osint.example.com \
    --skip-email \
    --seed \
    --force
```

**For complete implementation details, see [Installation Wizard Specification](INSTALLATION_WIZARD_SPEC.md).**

---

## 11. 15 Major OSINT Features

### 1. Source Verification System
```
Feature: Multi-Source Correlation
- Each event requires at least one source
- Source reliability scoring:
  - Automatic: Known reliable sources get boost
  - Manual: Editors can verify sources
  - Community: Upvote/downvote system
- Cross-reference detection:
  - Alert when same event reported from multiple sources
  - Highlight single-source events
- Archive integration:
  - Automatic Wayback Machine archiving
  - Local screenshot capture
  - Evidence preservation chain
```

### 2. Geolocation Verification Tools
```
Feature: Built-in Geolocation Helpers
- Satellite imagery comparison
- Sun position calculator
- Shadow analysis tools
- Building/landmark matching
- Metadata extraction from images
- Coordinate converter (all formats)
- Measurement tools (distance, area)
- Line-of-sight analysis
```

### 3. Equipment Loss Tracking (Oryx-style)
```
Feature: Visual Equipment Database
- Photo evidence required for losses
- Categories: Destroyed, Damaged, Abandoned, Captured
- Running totals by:
  - Country/faction
  - Equipment type
  - Time period
- Public leaderboard
- Exportable statistics
- Duplicate detection
- Visual evidence gallery per item
```

### 4. Collaborative Verification Workflow
```
Feature: Multi-step Verification
- New events start as "Unverified"
- Verification workflow:
  1. Initial report (auto or user)
  2. Source check
  3. Geolocation verification
  4. Cross-reference check
  5. Editor approval
- Dispute resolution system
- Verification badges for trusted users
- Audit trail for all changes
```

### 5. Real-time Alert System
```
Feature: Custom Alerts
- Alert rules based on:
  - Geographic area (draw custom zone)
  - Event type
  - Equipment type
  - Faction
  - Keywords
- Delivery methods:
  - Email digest
  - Push notifications
  - Telegram bot
  - Discord webhook
  - RSS feed
- Alert management dashboard
```

### 6. Advanced Search & Filtering
```
Feature: Powerful Search
- Full-text search across all content
- Filter combinations:
  - Date range
  - Event type
  - Location (radius, polygon)
  - Faction
  - Equipment
  - Verification status
  - Source type
- Save search as alert
- Export search results
- Search history
```

### 7. Attribution & Chronolocation
```
Feature: Time-Based Analysis
- Event clustering by time
- Pattern detection:
  - Attack patterns
  - Movement patterns
  - Supply patterns
- Timeline anomaly detection
- Date/time verification tools:
  - EXIF extraction
  - Solar position
  - Weather correlation
  - Satellite pass times
```

### 8. Satellite Imagery Integration
```
Feature: Satellite Analysis
- Integration with:
  - Sentinel Hub
  - Planet Labs (if available)
  - Google Earth historical
  - Maxar (if available)
- Change detection between dates
- Side-by-side comparison
- Annotation tools
- Before/after sliders
```

### 9. Social Media Monitoring
```
Feature: Social Feed Integration
- Connect accounts:
  - Twitter/X lists
  - Telegram channels
  - Reddit subreddits
- Auto-suggest events from feeds
- Source credibility tracking
- Viral content detection
- Misinformation flagging
- Thread unrolling
```

### 10. Network Analysis
```
Feature: Entity Relationships
- Track relationships between:
  - Units and equipment
  - Personnel and units
  - Factions and alliances
- Visualize networks
- Timeline of relationship changes
- Identify key nodes
- Export for analysis tools
```

### 11. Report Generation
```
Feature: Automated Reports
- Templates for:
  - Daily situation report
  - Weekly summary
  - Equipment loss report
  - Territorial changes
  - Custom templates
- Formats:
  - PDF (styled)
  - Word (editable)
  - Markdown
  - HTML
- Scheduled generation
- Distribution lists
```

### 12. API & Data Pipeline
```
Feature: Developer Access
- Full REST API
- GraphQL endpoint
- Bulk data downloads
- Webhook notifications
- SDK libraries:
  - Python
  - JavaScript
  - R
- Rate limits by tier
- Usage analytics
```

### 13. Crowdsourced Intelligence
```
Feature: Community Contributions
- Public submission portal
- Anonymous submissions (optional)
- Tip line with encryption
- Bounty system for verification
- Contribution leaderboard
- Expertise badges
- Translation network
```

### 14. Offline Capability
```
Feature: Fieldwork Mode
- Download regions for offline use
- Create events offline
- Queue uploads for sync
- GPS integration
- Low-bandwidth mode
- Encrypted local storage
```

### 15. Evidence Preservation
```
Feature: Legal-Grade Archiving
- Chain of custody tracking
- Cryptographic hashing (SHA-256)
- Timestamp authority
- Immutable audit logs
- Export for legal proceedings
- GDPR-compliant deletion
- Long-term storage options
```

---

## 12. Database Schema

### Core Tables

```sql
-- Countries and factions
CREATE TABLE countries (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    iso_code CHAR(2) UNIQUE,
    iso_code3 CHAR(3) UNIQUE,
    flag_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE factions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    short_name VARCHAR(50),
    country_id INTEGER REFERENCES countries(id),
    parent_faction_id INTEGER REFERENCES factions(id),
    color VARCHAR(7),  -- Hex color
    logo_url VARCHAR(255),
    description TEXT,
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Equipment categories
CREATE TABLE equipment_categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_id INTEGER REFERENCES equipment_categories(id),
    icon VARCHAR(50),
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Military equipment
CREATE TABLE military_equipment (
    id SERIAL PRIMARY KEY,
    uuid UUID UNIQUE DEFAULT gen_random_uuid(),
    designation VARCHAR(255) NOT NULL,
    nato_designation VARCHAR(255),
    common_name VARCHAR(255),
    country_id INTEGER REFERENCES countries(id),
    category_id INTEGER REFERENCES equipment_categories(id),
    specifications JSONB,
    introduced_year INTEGER,
    estimated_produced INTEGER,
    description TEXT,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    deleted_at TIMESTAMP
);

-- Event types
CREATE TABLE event_types (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    icon VARCHAR(50),
    color VARCHAR(7),
    schema JSONB NOT NULL,  -- Field definitions
    supports_media BOOLEAN DEFAULT true,
    supports_equipment BOOLEAN DEFAULT true,
    supports_sources BOOLEAN DEFAULT true,
    active BOOLEAN DEFAULT true,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Events
CREATE TABLE events (
    id SERIAL PRIMARY KEY,
    uuid UUID UNIQUE DEFAULT gen_random_uuid(),
    event_type_id INTEGER REFERENCES event_types(id),
    user_id INTEGER REFERENCES users(id),
    title VARCHAR(500) NOT NULL,
    description TEXT,
    location GEOGRAPHY(POINT, 4326),
    location_name VARCHAR(255),
    location_accuracy VARCHAR(50) DEFAULT 'exact',
    occurred_at TIMESTAMP NOT NULL,
    occurred_at_end TIMESTAMP,
    date_accuracy VARCHAR(50) DEFAULT 'exact',
    status VARCHAR(50) DEFAULT 'pending',
    verification_score INTEGER DEFAULT 0,
    custom_fields JSONB,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    deleted_at TIMESTAMP
);

CREATE INDEX events_location_idx ON events USING GIST(location);
CREATE INDEX events_occurred_at_idx ON events(occurred_at);
CREATE INDEX events_status_idx ON events(status);

-- Control zones
CREATE TABLE control_zones (
    id SERIAL PRIMARY KEY,
    uuid UUID UNIQUE DEFAULT gen_random_uuid(),
    name VARCHAR(255),
    geometry GEOGRAPHY(POLYGON, 4326),
    controller_id INTEGER REFERENCES factions(id),
    control_type VARCHAR(50) DEFAULT 'full',
    valid_from TIMESTAMP NOT NULL,
    valid_to TIMESTAMP,
    source_url VARCHAR(500),
    confidence VARCHAR(50) DEFAULT 'confirmed',
    notes TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    deleted_at TIMESTAMP
);

CREATE INDEX control_zones_geometry_idx ON control_zones USING GIST(geometry);
CREATE INDEX control_zones_valid_idx ON control_zones(valid_from, valid_to);

-- Users
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    uuid UUID UNIQUE DEFAULT gen_random_uuid(),
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255),
    name VARCHAR(255),
    avatar_url VARCHAR(500),
    role VARCHAR(50) DEFAULT 'contributor',
    email_verified_at TIMESTAMP,
    two_factor_enabled BOOLEAN DEFAULT false,
    preferences JSONB,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    deleted_at TIMESTAMP
);

-- Audit log
CREATE TABLE audit_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    action VARCHAR(50) NOT NULL,
    auditable_type VARCHAR(255) NOT NULL,
    auditable_id INTEGER NOT NULL,
    old_values JSONB,
    new_values JSONB,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX audit_logs_auditable_idx ON audit_logs(auditable_type, auditable_id);
```

---

## 13. API Endpoints

### Authentication
```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout
POST   /api/auth/refresh
POST   /api/auth/forgot-password
POST   /api/auth/reset-password
GET    /api/auth/user
PUT    /api/auth/user
POST   /api/auth/two-factor/enable
POST   /api/auth/two-factor/disable
```

### Events
```
GET    /api/events                    # List (paginated, filterable)
POST   /api/events                    # Create
GET    /api/events/{uuid}             # Show
PUT    /api/events/{uuid}             # Update
DELETE /api/events/{uuid}             # Delete
POST   /api/events/{uuid}/verify      # Verify event
POST   /api/events/{uuid}/dispute     # Dispute event
GET    /api/events/{uuid}/history     # Version history
POST   /api/events/{uuid}/media       # Upload media
DELETE /api/events/{uuid}/media/{id}  # Delete media
POST   /api/events/{uuid}/sources     # Add source
DELETE /api/events/{uuid}/sources/{id}# Remove source
```

### Equipment
```
GET    /api/equipment                 # List
POST   /api/equipment                 # Create (admin)
GET    /api/equipment/{uuid}          # Show
PUT    /api/equipment/{uuid}          # Update (admin)
DELETE /api/equipment/{uuid}          # Delete (admin)
GET    /api/equipment/categories      # List categories
GET    /api/equipment/search          # Search
GET    /api/equipment/{uuid}/events   # Related events
```

### Control Zones
```
GET    /api/zones                     # List
POST   /api/zones                     # Create
GET    /api/zones/{uuid}              # Show
PUT    /api/zones/{uuid}              # Update
DELETE /api/zones/{uuid}              # Delete
GET    /api/zones/{uuid}/history      # Version history
GET    /api/zones/at-date             # Zones at specific date
```

### Countries & Factions
```
GET    /api/countries                 # List
GET    /api/countries/{id}            # Show
GET    /api/countries/{id}/equipment  # Country equipment
GET    /api/factions                  # List
GET    /api/factions/{id}             # Show
GET    /api/factions/{id}/zones       # Faction zones
```

### Export
```
GET    /api/export/kml                # Export as KML
GET    /api/export/geojson            # Export as GeoJSON
GET    /api/export/csv                # Export as CSV
GET    /api/export/events.csv         # Events as CSV
GET    /api/export/equipment.csv      # Equipment as CSV
```

### Statistics
```
GET    /api/stats/overview            # Dashboard stats
GET    /api/stats/losses              # Loss statistics
GET    /api/stats/events              # Event statistics
GET    /api/stats/timeline            # Timeline data
```

---

## 14. UI/UX Specifications

### Main Layout
```
+--------------------------------------------------+
|  Logo  | Search Bar          | Notifications | User |
+--------+---------------------------------------------+
| Sidebar |                                           |
| Layers  |              Map View                     |
| Events  |                                           |
| Tools   |                                           |
|         |                                           |
|         +-------------------------------------------+
|         |            Timeline Bar                   |
+---------+-------------------------------------------+
```

### Key Pages
```
1. Dashboard
   - Activity feed
   - Recent events
   - Personal statistics
   - Saved views

2. Map View (Main)
   - Full-screen map
   - Collapsible sidebars
   - Layer controls
   - Drawing tools
   - Event list

3. Event Detail
   - Event information
   - Location on map
   - Media gallery
   - Sources
   - Equipment involved
   - Comments/discussion
   - Edit history

4. Equipment Browser
   - Category tree
   - Search/filter
   - Equipment cards
   - Comparison tool
   - Country inventories

5. Analytics
   - Loss charts
   - Timeline graphs
   - Heatmaps
   - Export tools

6. Admin Panel
   - User management
   - Content moderation
   - System settings
   - Audit logs
```

### Responsive Breakpoints
```
- Mobile: < 768px
- Tablet: 768px - 1024px
- Desktop: 1024px - 1440px
- Large: > 1440px
```

---

## 15. Implementation Phases

### Phase 1: Foundation (Weeks 1-4)
```
Goals: Basic infrastructure and core features

Tasks:
□ Project setup (Laravel, Vue, Docker)
□ Authentication system
□ User management
□ Database schema implementation
□ Basic map view with layers
□ Country/faction management
□ Equipment category structure
```

### Phase 2: Core Mapping (Weeks 5-8)
```
Goals: Map interaction and zones

Tasks:
□ Drawing tools implementation
□ Control zone management
□ Zone history tracking
□ Legend system
□ Layer toggle controls
□ Base layer selection
□ Coordinate input system
```

### Phase 3: Events System (Weeks 9-12)
```
Goals: Full event management

Tasks:
□ Event type system
□ All 20+ event templates
□ Click-to-add events
□ Media upload system
□ Source management
□ Equipment status tracking
□ Event verification workflow
```

### Phase 4: Timeline (Weeks 13-16)
```
Goals: Historical tracking

Tasks:
□ Timeline UI component
□ Date-based filtering
□ Historical playback
□ Snapshot system
□ Version history for entities
□ Date comparison tools
□ Change reports
```

### Phase 5: Equipment Database (Weeks 17-20)
```
Goals: Comprehensive equipment system

Tasks:
□ Equipment data entry
□ Country inventory tracking
□ Loss tracking system
□ Comparison tools
□ Image galleries
□ Search optimization
□ Data import tools
```

### Phase 6: OSINT Features (Weeks 21-26)
```
Goals: Advanced intelligence features

Tasks:
□ Source verification system
□ Geolocation tools
□ Alert system
□ Report generation
□ API development
□ Export features
□ Satellite imagery integration
```

### Phase 7: Polish & Launch (Weeks 27-30)
```
Goals: Production readiness

Tasks:
□ Performance optimization
□ Security audit
□ Mobile responsiveness
□ User documentation
□ API documentation
□ Testing & QA
□ Deployment pipeline
□ Monitoring setup
```

---

## Appendix A: Technology Details

### Required PHP Extensions
```
- mysql, pdo_mysql (MySQL 8.0+ support)
- gd or imagick (image processing)
- mbstring (multibyte string handling)
- xml (XML processing)
- curl (HTTP requests)
- zip (file compression)
- bcmath (precision math)
- json (JSON processing)
- tokenizer (Laravel routing)
- Optional: intl (internationalization)
- Optional: opcache (performance optimization)
```

**Note:** For complete installation instructions and hosting requirements, see [MySQL Stack Specification](MYSQL_STACK_SPECIFICATION.md).

### Required JavaScript Libraries
```json
{
  "dependencies": {
    "vue": "^3.4",
    "pinia": "^2.1",
    "vue-router": "^4.2",
    "@vueuse/core": "^10.7",
    "leaflet": "^1.9",
    "@vue-leaflet/vue-leaflet": "^0.10",
    "leaflet-draw": "^1.0",
    "axios": "^1.6",
    "date-fns": "^3.0",
    "@headlessui/vue": "^1.7",
    "chart.js": "^4.4",
    "tailwindcss": "^3.4"
  }
}
```

### MySQL Spatial Support

MySQL 8.0+ includes native spatial data types and functions:

```sql
-- Point data (events, equipment locations)
POINT SRID 4326

-- Polygon data (control zones, areas)
POLYGON SRID 4326

-- Spatial indexes for performance
CREATE SPATIAL INDEX idx_location ON events(location);
CREATE SPATIAL INDEX idx_geometry ON control_zones(geometry);

-- Distance calculations (meters)
ST_Distance_Sphere(point1, point2)

-- Contains checks (point in polygon)
ST_Contains(polygon, point)
```

**For complete MySQL spatial reference and migration examples, see [MySQL Stack Specification](MYSQL_STACK_SPECIFICATION.md).**

---

## Appendix B: Glossary

| Term | Definition |
|------|------------|
| OSINT | Open Source Intelligence |
| KML | Keyhole Markup Language (Google Earth format) |
| GeoJSON | JSON format for geographic data |
| PostGIS | PostgreSQL extension for geographic data |
| MANPADS | Man-Portable Air Defense System |
| ATGM | Anti-Tank Guided Missile |
| MLRS | Multiple Launch Rocket System |
| ERA | Explosive Reactive Armor |
| IFV | Infantry Fighting Vehicle |
| APC | Armored Personnel Carrier |
| MBT | Main Battle Tank |
| AWACS | Airborne Warning and Control System |
| DMS | Degrees Minutes Seconds (coordinate format) |
| UTM | Universal Transverse Mercator (coordinate system) |
| MGRS | Military Grid Reference System |

---

*Document Version: 1.0*
*Last Updated: December 2024*
