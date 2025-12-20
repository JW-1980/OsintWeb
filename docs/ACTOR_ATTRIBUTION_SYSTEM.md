# Actor Attribution System - Complete Specification

## Overview

The Actor Attribution System enables precise tracking of **WHO** participated in each military event, distinguishing between perpetrators/attackers and victims/targets. The system supports both **state actors** (countries) and **non-state actors** (terrorist organizations, militias, rebel groups, paramilitary forces, criminal organizations, etc.).

---

## 1. System Requirements

### Core Capabilities

1. **Dual Actor Types**: Support both countries and non-state groups as actors
2. **Role-Based Attribution**: Link actors to events with specific roles (perpetrator, victim, operator, etc.)
3. **Equipment Ownership**: Track which country/group owns destroyed/damaged equipment
4. **Multi-Actor Events**: Support events involving multiple perpetrators or victims
5. **Historical Tracking**: Maintain actor information across timeline
6. **Flexible Categorization**: Hierarchical actor types and subtypes

### Use Cases

```yaml
Example 1: Russian airstrike on Ukrainian position
  - Perpetrator: Russia (country)
  - Victim: Ukraine (country)
  - Equipment Used: Su-34 (Russian)
  - Target: Military installation

Example 2: ISIS suicide bombing in Baghdad
  - Perpetrator: ISIS (terrorist organization)
  - Victim: Iraq (country)
  - Target: Civilian area

Example 3: Ukrainian capture of Russian tank
  - Perpetrator: Ukraine (country)
  - Victim: Russia (country)
  - Equipment: T-90M tank (Russian equipment)
  - Status: Captured

Example 4: Wagner Group attack on Syrian rebels
  - Perpetrator: Wagner Group (paramilitary/private military)
  - Victim: Free Syrian Army (rebel group)
  - Context: Proxy warfare

Example 5: Hezbollah rocket strike on Israeli position
  - Perpetrator: Hezbollah (non-state armed group)
  - Victim: Israel (country)
  - Equipment Used: Katyusha rockets
```

---

## 2. Database Schema

### 2.1 Core Tables

#### Table: `actors`

Unified table for all actors (countries AND non-state groups).

```sql
-- MySQL/PostgreSQL compatible
CREATE TABLE actors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) UNIQUE NOT NULL,

    -- Basic Information
    name VARCHAR(255) NOT NULL,
    short_name VARCHAR(100) NULL,
    official_name VARCHAR(500) NULL,

    -- Actor Type Classification
    actor_type ENUM(
        'country',
        'terrorist_org',
        'rebel_group',
        'militia',
        'paramilitary',
        'private_military',
        'separatist_group',
        'insurgent_group',
        'criminal_org',
        'coalition',
        'international_org',
        'unknown'
    ) NOT NULL,

    actor_subtype VARCHAR(100) NULL, -- Additional classification

    -- Associations
    parent_country_id BIGINT UNSIGNED NULL, -- For non-state actors linked to a country
    parent_actor_id BIGINT UNSIGNED NULL,   -- For sub-groups or coalitions

    -- Identification
    also_known_as JSON NULL,  -- Array of aliases/alternate names
    /* Example: ["ISIS", "ISIL", "Daesh", "Islamic State"] */

    -- Visual/Display
    color_hex VARCHAR(7) NULL,     -- For map display (#FF5722)
    flag_url VARCHAR(500) NULL,
    logo_url VARCHAR(500) NULL,
    emblem_url VARCHAR(500) NULL,

    -- Geographic
    primary_region VARCHAR(100) NULL,  -- Middle East, Eastern Europe, etc.
    operating_areas JSON NULL,         -- Array of regions/countries
    /* Example: ["Syria", "Iraq", "Libya"] */

    -- Classification Details
    designation_status ENUM(
        'none',
        'terrorist_us',
        'terrorist_un',
        'terrorist_eu',
        'sanctioned',
        'recognized_state',
        'unrecognized_state'
    ) DEFAULT 'none',

    -- Status
    is_active BOOLEAN DEFAULT true,
    founded_date DATE NULL,
    dissolved_date DATE NULL,

    -- Additional Information
    ideology VARCHAR(255) NULL,        -- Islamist, nationalist, separatist, etc.
    estimated_size VARCHAR(100) NULL,  -- "5,000-10,000 fighters"
    description TEXT NULL,
    notes TEXT NULL,

    -- ISO Codes (for countries only)
    iso_code_alpha2 CHAR(2) NULL,
    iso_code_alpha3 CHAR(3) NULL,
    iso_code_numeric CHAR(3) NULL,

    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    -- Indexes
    INDEX idx_actor_type (actor_type),
    INDEX idx_active (is_active),
    INDEX idx_parent_country (parent_country_id),
    INDEX idx_parent_actor (parent_actor_id),

    -- Foreign Keys
    FOREIGN KEY (parent_country_id) REFERENCES actors(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_actor_id) REFERENCES actors(id) ON DELETE SET NULL,

    -- Constraints
    CHECK (
        (actor_type = 'country' AND iso_code_alpha2 IS NOT NULL) OR
        (actor_type != 'country')
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Table: `event_actors`

Pivot table linking actors to events with roles.

```sql
CREATE TABLE event_actors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Relationships
    event_id BIGINT UNSIGNED NOT NULL,
    actor_id BIGINT UNSIGNED NOT NULL,

    -- Role Classification
    role ENUM(
        'perpetrator',      -- Who carried out the action
        'victim',           -- Who was targeted/affected
        'equipment_owner',  -- Whose equipment was involved
        'operator',         -- Who was operating equipment
        'ally',             -- Allied force participating
        'mediator',         -- For diplomatic events
        'witness',          -- Witnessing party
        'claimed_by',       -- Who claimed responsibility
        'attributed_to',    -- Who it's attributed to (may differ from claimed_by)
        'other'
    ) NOT NULL,

    -- Additional Context
    role_description VARCHAR(500) NULL,  -- "Led the attack", "Provided air support"
    certainty ENUM(
        'confirmed',
        'likely',
        'possible',
        'alleged',
        'disputed',
        'unconfirmed'
    ) DEFAULT 'unconfirmed',

    -- Quantitative Data
    personnel_count INT UNSIGNED NULL,     -- Number of personnel involved
    casualties_claimed INT UNSIGNED NULL,   -- Casualties this actor claims to have inflicted
    losses_suffered INT UNSIGNED NULL,      -- Casualties/losses this actor suffered

    -- Sources & Verification
    source_type ENUM(
        'visual_evidence',
        'official_statement',
        'intelligence',
        'media_report',
        'social_media',
        'witness_account',
        'intercepted_communication',
        'other'
    ) NULL,
    source_url VARCHAR(1000) NULL,
    verified_by VARCHAR(255) NULL,  -- Who verified this attribution

    notes TEXT NULL,

    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_event (event_id),
    INDEX idx_actor (actor_id),
    INDEX idx_role (role),
    INDEX idx_event_role (event_id, role),
    UNIQUE KEY unique_event_actor_role (event_id, actor_id, role),

    -- Foreign Keys
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES actors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Table: `actor_relationships`

Tracks relationships between actors (alliances, hostilities, affiliations).

```sql
CREATE TABLE actor_relationships (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    actor_from_id BIGINT UNSIGNED NOT NULL,
    actor_to_id BIGINT UNSIGNED NOT NULL,

    relationship_type ENUM(
        'allied',
        'hostile',
        'sponsor',          -- From sponsors To
        'affiliated',
        'splinter_group',   -- To is a splinter of From
        'merged_into',      -- From merged into To
        'rival',
        'neutral',
        'unknown'
    ) NOT NULL,

    -- Temporal Tracking
    valid_from DATE NOT NULL,
    valid_to DATE NULL,  -- NULL = ongoing

    -- Details
    description TEXT NULL,
    strength ENUM('strong', 'moderate', 'weak') DEFAULT 'moderate',
    source_url VARCHAR(1000) NULL,

    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_from (actor_from_id),
    INDEX idx_to (actor_to_id),
    INDEX idx_type (relationship_type),
    INDEX idx_temporal (valid_from, valid_to),

    -- Foreign Keys
    FOREIGN KEY (actor_from_id) REFERENCES actors(id) ON DELETE CASCADE,
    FOREIGN KEY (actor_to_id) REFERENCES actors(id) ON DELETE CASCADE,

    -- Constraints
    CHECK (actor_from_id != actor_to_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Table: `actor_aliases`

Additional table for managing aliases and alternate names.

```sql
CREATE TABLE actor_aliases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_id BIGINT UNSIGNED NOT NULL,

    alias VARCHAR(255) NOT NULL,
    alias_type ENUM(
        'abbreviation',
        'local_name',
        'western_name',
        'propaganda_name',
        'former_name',
        'code_name',
        'other'
    ) NOT NULL,

    language VARCHAR(10) NULL,  -- ISO 639-1 code (en, ar, ru, etc.)
    is_primary BOOLEAN DEFAULT false,

    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_actor (actor_id),
    INDEX idx_alias (alias),
    INDEX idx_primary (is_primary),

    -- Foreign Keys
    FOREIGN KEY (actor_id) REFERENCES actors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2.2 Modified Equipment Table

Update the existing `event_equipment` table to include operator information:

```sql
-- Add to existing event_equipment table
ALTER TABLE event_equipment
ADD COLUMN equipment_owner_actor_id BIGINT UNSIGNED NULL AFTER operator_faction_id,
ADD COLUMN captured_by_actor_id BIGINT UNSIGNED NULL AFTER equipment_owner_actor_id,
ADD INDEX idx_owner (equipment_owner_actor_id),
ADD INDEX idx_captured_by (captured_by_actor_id),
ADD FOREIGN KEY (equipment_owner_actor_id) REFERENCES actors(id) ON DELETE SET NULL,
ADD FOREIGN KEY (captured_by_actor_id) REFERENCES actors(id) ON DELETE SET NULL;

-- Migration to move faction data to actors
-- Note: operator_faction_id should be deprecated in favor of event_actors with role='operator'
```

---

## 3. Laravel Migrations

### Migration: Create Actors Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Basic Information
            $table->string('name');
            $table->string('short_name', 100)->nullable();
            $table->string('official_name', 500)->nullable();

            // Actor Type Classification
            $table->enum('actor_type', [
                'country',
                'terrorist_org',
                'rebel_group',
                'militia',
                'paramilitary',
                'private_military',
                'separatist_group',
                'insurgent_group',
                'criminal_org',
                'coalition',
                'international_org',
                'unknown',
            ]);
            $table->string('actor_subtype', 100)->nullable();

            // Associations
            $table->foreignId('parent_country_id')->nullable()->constrained('actors')->nullOnDelete();
            $table->foreignId('parent_actor_id')->nullable()->constrained('actors')->nullOnDelete();

            // Identification
            $table->json('also_known_as')->nullable();

            // Visual/Display
            $table->string('color_hex', 7)->nullable();
            $table->string('flag_url', 500)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->string('emblem_url', 500)->nullable();

            // Geographic
            $table->string('primary_region', 100)->nullable();
            $table->json('operating_areas')->nullable();

            // Classification Details
            $table->enum('designation_status', [
                'none',
                'terrorist_us',
                'terrorist_un',
                'terrorist_eu',
                'sanctioned',
                'recognized_state',
                'unrecognized_state',
            ])->default('none');

            // Status
            $table->boolean('is_active')->default(true);
            $table->date('founded_date')->nullable();
            $table->date('dissolved_date')->nullable();

            // Additional Information
            $table->string('ideology')->nullable();
            $table->string('estimated_size', 100)->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            // ISO Codes (for countries)
            $table->char('iso_code_alpha2', 2)->nullable();
            $table->char('iso_code_alpha3', 3)->nullable();
            $table->char('iso_code_numeric', 3)->nullable();

            // Metadata
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('actor_type');
            $table->index('is_active');
            $table->index('parent_country_id');
            $table->index('parent_actor_id');
            $table->index('iso_code_alpha2');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actors');
    }
};
```

### Migration: Create Event Actors Pivot Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_actors', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained()->cascadeOnDelete();

            // Role Classification
            $table->enum('role', [
                'perpetrator',
                'victim',
                'equipment_owner',
                'operator',
                'ally',
                'mediator',
                'witness',
                'claimed_by',
                'attributed_to',
                'other',
            ]);

            // Additional Context
            $table->string('role_description', 500)->nullable();
            $table->enum('certainty', [
                'confirmed',
                'likely',
                'possible',
                'alleged',
                'disputed',
                'unconfirmed',
            ])->default('unconfirmed');

            // Quantitative Data
            $table->unsignedInteger('personnel_count')->nullable();
            $table->unsignedInteger('casualties_claimed')->nullable();
            $table->unsignedInteger('losses_suffered')->nullable();

            // Sources & Verification
            $table->enum('source_type', [
                'visual_evidence',
                'official_statement',
                'intelligence',
                'media_report',
                'social_media',
                'witness_account',
                'intercepted_communication',
                'other',
            ])->nullable();
            $table->string('source_url', 1000)->nullable();
            $table->string('verified_by')->nullable();

            $table->text('notes')->nullable();

            // Metadata
            $table->timestamps();

            // Indexes
            $table->index('role');
            $table->index(['event_id', 'role']);
            $table->unique(['event_id', 'actor_id', 'role'], 'unique_event_actor_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_actors');
    }
};
```

### Migration: Create Actor Relationships Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actor_relationships', function (Blueprint $table) {
            $table->id();

            $table->foreignId('actor_from_id')->constrained('actors')->cascadeOnDelete();
            $table->foreignId('actor_to_id')->constrained('actors')->cascadeOnDelete();

            $table->enum('relationship_type', [
                'allied',
                'hostile',
                'sponsor',
                'affiliated',
                'splinter_group',
                'merged_into',
                'rival',
                'neutral',
                'unknown',
            ]);

            // Temporal Tracking
            $table->date('valid_from');
            $table->date('valid_to')->nullable();

            // Details
            $table->text('description')->nullable();
            $table->enum('strength', ['strong', 'moderate', 'weak'])->default('moderate');
            $table->string('source_url', 1000)->nullable();

            // Metadata
            $table->timestamps();

            // Indexes
            $table->index('actor_from_id');
            $table->index('actor_to_id');
            $table->index('relationship_type');
            $table->index(['valid_from', 'valid_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actor_relationships');
    }
};
```

### Migration: Create Actor Aliases Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actor_aliases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('actor_id')->constrained()->cascadeOnDelete();

            $table->string('alias');
            $table->enum('alias_type', [
                'abbreviation',
                'local_name',
                'western_name',
                'propaganda_name',
                'former_name',
                'code_name',
                'other',
            ]);

            $table->string('language', 10)->nullable();
            $table->boolean('is_primary')->default(false);

            // Metadata
            $table->timestamps();

            // Indexes
            $table->index('actor_id');
            $table->index('alias');
            $table->index('is_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actor_aliases');
    }
};
```

---

## 4. Actor Categories & Types

### 4.1 Actor Type Definitions

| Actor Type | Description | Examples |
|------------|-------------|----------|
| **country** | Recognized nation-state | Russia, Ukraine, United States, Israel |
| **terrorist_org** | Designated terrorist organization | ISIS, Al-Qaeda, Boko Haram, Al-Shabaab |
| **rebel_group** | Armed opposition to government | Free Syrian Army, NRF (Myanmar), FARC |
| **militia** | Irregular armed forces | Popular Mobilization Forces (Iraq), Shabab |
| **paramilitary** | Semi-military organization | Wagner Group, Blackwater (historical) |
| **private_military** | Private military company | Wagner PMC, Executive Outcomes |
| **separatist_group** | Seeking territorial independence | DPR, LPR, Kurdistan Workers' Party (PKK) |
| **insurgent_group** | Armed uprising against authority | Taliban (pre-2021), Houthis |
| **criminal_org** | Organized crime with military capabilities | Cartels (when conducting military operations) |
| **coalition** | Alliance of multiple actors | Coalition forces, Joint Task Force |
| **international_org** | Multinational organization | NATO, UN Peacekeepers, Arab League |
| **unknown** | Unidentified actor | Unknown attackers, Unattributed |

### 4.2 Designation Status Options

- **none**: No special designation
- **terrorist_us**: Designated as terrorist by US
- **terrorist_un**: Designated as terrorist by UN
- **terrorist_eu**: Designated as terrorist by EU
- **sanctioned**: Under international sanctions
- **recognized_state**: Internationally recognized country
- **unrecognized_state**: Self-declared state (e.g., Somaliland, Transnistria)

### 4.3 Relationship Types

- **allied**: Formal or informal alliance
- **hostile**: Active conflict or enmity
- **sponsor**: One actor sponsors/supports another
- **affiliated**: Organizational affiliation
- **splinter_group**: Breakaway faction
- **merged_into**: Actor merged with another
- **rival**: Competing for same goals
- **neutral**: No significant relationship
- **unknown**: Relationship unclear

---

## 5. TypeScript Interfaces

### 5.1 Core Actor Interfaces

```typescript
/**
 * Actor Type Enumeration
 */
export enum ActorType {
  COUNTRY = 'country',
  TERRORIST_ORG = 'terrorist_org',
  REBEL_GROUP = 'rebel_group',
  MILITIA = 'militia',
  PARAMILITARY = 'paramilitary',
  PRIVATE_MILITARY = 'private_military',
  SEPARATIST_GROUP = 'separatist_group',
  INSURGENT_GROUP = 'insurgent_group',
  CRIMINAL_ORG = 'criminal_org',
  COALITION = 'coalition',
  INTERNATIONAL_ORG = 'international_org',
  UNKNOWN = 'unknown',
}

/**
 * Designation Status
 */
export enum DesignationStatus {
  NONE = 'none',
  TERRORIST_US = 'terrorist_us',
  TERRORIST_UN = 'terrorist_un',
  TERRORIST_EU = 'terrorist_eu',
  SANCTIONED = 'sanctioned',
  RECOGNIZED_STATE = 'recognized_state',
  UNRECOGNIZED_STATE = 'unrecognized_state',
}

/**
 * Actor Entity
 */
export interface Actor {
  id: number;
  uuid: string;

  // Basic Information
  name: string;
  short_name: string | null;
  official_name: string | null;

  // Classification
  actor_type: ActorType;
  actor_subtype: string | null;

  // Associations
  parent_country_id: number | null;
  parent_actor_id: number | null;
  parent_country?: Actor;
  parent_actor?: Actor;

  // Aliases
  also_known_as: string[] | null;

  // Visual
  color_hex: string | null;
  flag_url: string | null;
  logo_url: string | null;
  emblem_url: string | null;

  // Geographic
  primary_region: string | null;
  operating_areas: string[] | null;

  // Classification
  designation_status: DesignationStatus;

  // Status
  is_active: boolean;
  founded_date: string | null; // ISO date
  dissolved_date: string | null; // ISO date

  // Details
  ideology: string | null;
  estimated_size: string | null;
  description: string | null;
  notes: string | null;

  // ISO Codes (countries only)
  iso_code_alpha2: string | null;
  iso_code_alpha3: string | null;
  iso_code_numeric: string | null;

  // Metadata
  created_at: string;
  updated_at: string;
  deleted_at: string | null;

  // Relationships (loaded when needed)
  aliases?: ActorAlias[];
  relationships_from?: ActorRelationship[];
  relationships_to?: ActorRelationship[];
  events?: EventActor[];
}

/**
 * Event Actor Role
 */
export enum EventActorRole {
  PERPETRATOR = 'perpetrator',
  VICTIM = 'victim',
  EQUIPMENT_OWNER = 'equipment_owner',
  OPERATOR = 'operator',
  ALLY = 'ally',
  MEDIATOR = 'mediator',
  WITNESS = 'witness',
  CLAIMED_BY = 'claimed_by',
  ATTRIBUTED_TO = 'attributed_to',
  OTHER = 'other',
}

/**
 * Certainty Level
 */
export enum CertaintyLevel {
  CONFIRMED = 'confirmed',
  LIKELY = 'likely',
  POSSIBLE = 'possible',
  ALLEGED = 'alleged',
  DISPUTED = 'disputed',
  UNCONFIRMED = 'unconfirmed',
}

/**
 * Source Type
 */
export enum SourceType {
  VISUAL_EVIDENCE = 'visual_evidence',
  OFFICIAL_STATEMENT = 'official_statement',
  INTELLIGENCE = 'intelligence',
  MEDIA_REPORT = 'media_report',
  SOCIAL_MEDIA = 'social_media',
  WITNESS_ACCOUNT = 'witness_account',
  INTERCEPTED_COMMUNICATION = 'intercepted_communication',
  OTHER = 'other',
}

/**
 * Event Actor Pivot
 */
export interface EventActor {
  id: number;
  event_id: number;
  actor_id: number;

  // Role
  role: EventActorRole;
  role_description: string | null;
  certainty: CertaintyLevel;

  // Quantitative
  personnel_count: number | null;
  casualties_claimed: number | null;
  losses_suffered: number | null;

  // Verification
  source_type: SourceType | null;
  source_url: string | null;
  verified_by: string | null;

  notes: string | null;

  // Metadata
  created_at: string;
  updated_at: string;

  // Relationships
  actor?: Actor;
  event?: Event;
}

/**
 * Actor Relationship Type
 */
export enum RelationshipType {
  ALLIED = 'allied',
  HOSTILE = 'hostile',
  SPONSOR = 'sponsor',
  AFFILIATED = 'affiliated',
  SPLINTER_GROUP = 'splinter_group',
  MERGED_INTO = 'merged_into',
  RIVAL = 'rival',
  NEUTRAL = 'neutral',
  UNKNOWN = 'unknown',
}

/**
 * Relationship Strength
 */
export enum RelationshipStrength {
  STRONG = 'strong',
  MODERATE = 'moderate',
  WEAK = 'weak',
}

/**
 * Actor Relationship
 */
export interface ActorRelationship {
  id: number;
  actor_from_id: number;
  actor_to_id: number;

  relationship_type: RelationshipType;

  // Temporal
  valid_from: string; // ISO date
  valid_to: string | null; // ISO date

  // Details
  description: string | null;
  strength: RelationshipStrength;
  source_url: string | null;

  // Metadata
  created_at: string;
  updated_at: string;

  // Relationships
  actor_from?: Actor;
  actor_to?: Actor;
}

/**
 * Actor Alias Type
 */
export enum AliasType {
  ABBREVIATION = 'abbreviation',
  LOCAL_NAME = 'local_name',
  WESTERN_NAME = 'western_name',
  PROPAGANDA_NAME = 'propaganda_name',
  FORMER_NAME = 'former_name',
  CODE_NAME = 'code_name',
  OTHER = 'other',
}

/**
 * Actor Alias
 */
export interface ActorAlias {
  id: number;
  actor_id: number;

  alias: string;
  alias_type: AliasType;
  language: string | null; // ISO 639-1
  is_primary: boolean;

  // Metadata
  created_at: string;
  updated_at: string;

  // Relationships
  actor?: Actor;
}
```

### 5.2 Form & UI Interfaces

```typescript
/**
 * Actor Search Filters
 */
export interface ActorFilters {
  search?: string;
  actor_types?: ActorType[];
  designation_status?: DesignationStatus[];
  is_active?: boolean;
  primary_region?: string;
  parent_country_id?: number;
}

/**
 * Event Actor Assignment Form
 */
export interface EventActorForm {
  actor_id: number;
  role: EventActorRole;
  role_description?: string;
  certainty: CertaintyLevel;
  personnel_count?: number;
  casualties_claimed?: number;
  losses_suffered?: number;
  source_type?: SourceType;
  source_url?: string;
  verified_by?: string;
  notes?: string;
}

/**
 * Actor Create/Update Form
 */
export interface ActorForm {
  // Required
  name: string;
  actor_type: ActorType;

  // Optional Basic
  short_name?: string;
  official_name?: string;
  actor_subtype?: string;

  // Associations
  parent_country_id?: number | null;
  parent_actor_id?: number | null;

  // Aliases
  also_known_as?: string[];

  // Visual
  color_hex?: string;
  flag_url?: string;
  logo_url?: string;
  emblem_url?: string;

  // Geographic
  primary_region?: string;
  operating_areas?: string[];

  // Classification
  designation_status?: DesignationStatus;

  // Status
  is_active?: boolean;
  founded_date?: string;
  dissolved_date?: string;

  // Details
  ideology?: string;
  estimated_size?: string;
  description?: string;
  notes?: string;

  // ISO (countries)
  iso_code_alpha2?: string;
  iso_code_alpha3?: string;
  iso_code_numeric?: string;
}

/**
 * Actor Select Option (for dropdowns)
 */
export interface ActorSelectOption {
  value: number;
  label: string;
  actor_type: ActorType;
  color_hex?: string;
  flag_url?: string;
  is_active: boolean;
  designation_status?: DesignationStatus;
}

/**
 * Event with Actors (Extended Event interface)
 */
export interface EventWithActors extends Event {
  perpetrators?: EventActor[];
  victims?: EventActor[];
  equipment_owners?: EventActor[];
  operators?: EventActor[];
  all_actors?: EventActor[];
}
```

### 5.3 Vue Composable Types

```typescript
/**
 * Actor Store State
 */
export interface ActorStoreState {
  actors: Actor[];
  currentActor: Actor | null;
  filters: ActorFilters;
  loading: boolean;
  error: string | null;
}

/**
 * Use Actor Composable Return Type
 */
export interface UseActorReturn {
  // State
  actors: Ref<Actor[]>;
  actor: Ref<Actor | null>;
  loading: Ref<boolean>;
  error: Ref<string | null>;

  // Actions
  fetchActors: (filters?: ActorFilters) => Promise<Actor[]>;
  fetchActor: (id: number) => Promise<Actor>;
  createActor: (data: ActorForm) => Promise<Actor>;
  updateActor: (id: number, data: Partial<ActorForm>) => Promise<Actor>;
  deleteActor: (id: number) => Promise<void>;
  searchActors: (query: string) => Promise<Actor[]>;

  // Helpers
  getActorsByType: (type: ActorType) => Actor[];
  getCountries: () => Actor[];
  getNonStateActors: () => Actor[];
  getActiveActors: () => Actor[];
}
```

---

## 6. Example Data & Use Cases

### 6.1 Sample Actor Records

```json
[
  {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Russian Federation",
    "short_name": "Russia",
    "official_name": "Russian Federation",
    "actor_type": "country",
    "actor_subtype": null,
    "parent_country_id": null,
    "parent_actor_id": null,
    "also_known_as": ["Russia", "РФ", "RF"],
    "color_hex": "#0039A6",
    "flag_url": "/flags/ru.svg",
    "iso_code_alpha2": "RU",
    "iso_code_alpha3": "RUS",
    "iso_code_numeric": "643",
    "primary_region": "Eastern Europe",
    "operating_areas": null,
    "designation_status": "recognized_state",
    "is_active": true,
    "founded_date": "1991-12-25",
    "dissolved_date": null,
    "ideology": null,
    "estimated_size": null,
    "description": "Successor state to the Soviet Union"
  },
  {
    "id": 2,
    "uuid": "660e8400-e29b-41d4-a716-446655440001",
    "name": "Ukraine",
    "short_name": "Ukraine",
    "official_name": "Ukraine",
    "actor_type": "country",
    "iso_code_alpha2": "UA",
    "iso_code_alpha3": "UKR",
    "color_hex": "#0057B7",
    "designation_status": "recognized_state",
    "is_active": true
  },
  {
    "id": 10,
    "uuid": "770e8400-e29b-41d4-a716-446655440010",
    "name": "Islamic State of Iraq and Syria",
    "short_name": "ISIS",
    "official_name": "ad-Dawlah al-Islāmiyah fī 'l-ʿIrāq wa-sh-Shām",
    "actor_type": "terrorist_org",
    "actor_subtype": "Salafist jihadist",
    "parent_country_id": null,
    "parent_actor_id": null,
    "also_known_as": ["ISIS", "ISIL", "Daesh", "IS", "Islamic State"],
    "color_hex": "#000000",
    "flag_url": "/emblems/isis.png",
    "primary_region": "Middle East",
    "operating_areas": ["Syria", "Iraq", "Libya", "Afghanistan"],
    "designation_status": "terrorist_un",
    "is_active": true,
    "founded_date": "2013-04-08",
    "dissolved_date": null,
    "ideology": "Salafist jihadism, Islamic fundamentalism",
    "estimated_size": "20,000-30,000 fighters (est. 2023)",
    "description": "Salafi jihadist militant organization"
  },
  {
    "id": 15,
    "uuid": "880e8400-e29b-41d4-a716-446655440015",
    "name": "Wagner Group",
    "short_name": "Wagner",
    "official_name": "PMC Wagner",
    "actor_type": "private_military",
    "actor_subtype": "Private military company",
    "parent_country_id": 1,
    "parent_actor_id": null,
    "also_known_as": ["Wagner PMC", "ChVK Wagner", "Группа Вагнера"],
    "color_hex": "#8B4513",
    "logo_url": "/emblems/wagner.png",
    "primary_region": "International",
    "operating_areas": ["Syria", "Libya", "CAR", "Mali", "Ukraine"],
    "designation_status": "sanctioned",
    "is_active": false,
    "founded_date": "2014-01-01",
    "dissolved_date": "2023-06-24",
    "ideology": "Mercenary",
    "estimated_size": "50,000 fighters (peak)",
    "description": "Russian paramilitary organization"
  },
  {
    "id": 20,
    "uuid": "990e8400-e29b-41d4-a716-446655440020",
    "name": "Donetsk People's Republic",
    "short_name": "DPR",
    "official_name": "Donetsk People's Republic",
    "actor_type": "separatist_group",
    "actor_subtype": "Pro-Russian separatists",
    "parent_country_id": 1,
    "parent_actor_id": null,
    "also_known_as": ["DPR", "DNR", "ДНР"],
    "color_hex": "#000000",
    "flag_url": "/flags/dpr.svg",
    "primary_region": "Eastern Europe",
    "operating_areas": ["Donetsk Oblast"],
    "designation_status": "unrecognized_state",
    "is_active": true,
    "founded_date": "2014-04-07",
    "ideology": "Pro-Russian separatism",
    "estimated_size": "20,000 fighters"
  }
]
```

### 6.2 Example: Russian Airstrike on Ukrainian Position

```json
{
  "event": {
    "id": 1001,
    "uuid": "event-001-uuid",
    "event_type_id": 1,
    "title": "Russian airstrike on Ukrainian military depot near Kharkiv",
    "description": "Multiple explosions reported at military facility",
    "location": {
      "type": "Point",
      "coordinates": [36.2527, 49.9808]
    },
    "occurred_at": "2024-12-15T14:30:00Z",
    "status": "verified"
  },
  "event_actors": [
    {
      "id": 5001,
      "event_id": 1001,
      "actor_id": 1,
      "role": "perpetrator",
      "role_description": "Conducted airstrike using Su-34 fighter-bombers",
      "certainty": "confirmed",
      "personnel_count": null,
      "casualties_claimed": null,
      "losses_suffered": null,
      "source_type": "visual_evidence",
      "source_url": "https://example.com/source1",
      "verified_by": "Geolocation analysis",
      "notes": "Two Su-34 aircraft observed on satellite imagery"
    },
    {
      "id": 5002,
      "event_id": 1001,
      "actor_id": 2,
      "role": "victim",
      "role_description": "Military depot targeted",
      "certainty": "confirmed",
      "personnel_count": null,
      "casualties_claimed": null,
      "losses_suffered": 3,
      "source_type": "official_statement",
      "source_url": "https://example.com/ukraine-mod",
      "verified_by": "Ukrainian Ministry of Defense",
      "notes": "3 casualties confirmed"
    },
    {
      "id": 5003,
      "event_id": 1001,
      "actor_id": 1,
      "role": "equipment_owner",
      "role_description": "Su-34 fighter-bomber",
      "certainty": "confirmed",
      "source_type": "visual_evidence"
    }
  ]
}
```

### 6.3 Example: Wagner Group Attack on Syrian Rebels

```json
{
  "event": {
    "id": 1002,
    "title": "Wagner Group assault on FSA position in Deir ez-Zor",
    "event_type_id": 5,
    "occurred_at": "2024-11-20T08:00:00Z"
  },
  "event_actors": [
    {
      "event_id": 1002,
      "actor_id": 15,
      "role": "perpetrator",
      "role_description": "Wagner PMC forces led the ground assault",
      "certainty": "likely",
      "personnel_count": 200,
      "source_type": "intelligence"
    },
    {
      "event_id": 1002,
      "actor_id": 1,
      "role": "ally",
      "role_description": "Provided air support and artillery",
      "certainty": "confirmed",
      "source_type": "visual_evidence"
    },
    {
      "event_id": 1002,
      "actor_id": 25,
      "role": "victim",
      "role_description": "Free Syrian Army position was attacked",
      "certainty": "confirmed",
      "losses_suffered": 12,
      "source_type": "media_report"
    }
  ]
}
```

### 6.4 Example: Ukrainian Capture of Russian Tank

```json
{
  "event": {
    "id": 1003,
    "title": "Ukrainian forces capture intact T-90M tank",
    "event_type_id": 9,
    "occurred_at": "2024-12-10T11:45:00Z"
  },
  "event_actors": [
    {
      "event_id": 1003,
      "actor_id": 2,
      "role": "perpetrator",
      "role_description": "Captured the vehicle during counteroffensive",
      "certainty": "confirmed",
      "source_type": "visual_evidence"
    },
    {
      "event_id": 1003,
      "actor_id": 1,
      "role": "victim",
      "role_description": "Lost T-90M tank",
      "certainty": "confirmed",
      "source_type": "visual_evidence"
    },
    {
      "event_id": 1003,
      "actor_id": 1,
      "role": "equipment_owner",
      "role_description": "Original owner of T-90M",
      "certainty": "confirmed"
    }
  ],
  "event_equipment": [
    {
      "event_id": 1003,
      "equipment_id": 501,
      "quantity": 1,
      "status": "captured",
      "equipment_owner_actor_id": 1,
      "captured_by_actor_id": 2,
      "notes": "Tank appears to be in operational condition"
    }
  ]
}
```

---

## 7. API Endpoints

### Actors

```
GET    /api/actors                          # List all actors (paginated)
POST   /api/actors                          # Create new actor
GET    /api/actors/{id}                     # Get actor details
PUT    /api/actors/{id}                     # Update actor
DELETE /api/actors/{id}                     # Delete actor
GET    /api/actors/search?q={query}         # Search actors
GET    /api/actors/countries                # Get only countries
GET    /api/actors/non-state                # Get only non-state actors
GET    /api/actors/{id}/events              # Get all events involving actor
GET    /api/actors/{id}/relationships       # Get actor relationships
POST   /api/actors/{id}/relationships       # Create relationship
DELETE /api/actors/{id}/relationships/{rel_id} # Delete relationship
```

### Event Actors

```
GET    /api/events/{id}/actors              # Get all actors for event
POST   /api/events/{id}/actors              # Add actor to event
PUT    /api/events/{id}/actors/{actor_id}   # Update actor role
DELETE /api/events/{id}/actors/{actor_id}   # Remove actor from event
GET    /api/events/{id}/perpetrators        # Get perpetrators
GET    /api/events/{id}/victims             # Get victims
```

### Query Parameters

```
# Actor Listing
?actor_type=country,terrorist_org
?designation_status=terrorist_un
?is_active=true
?primary_region=Middle East
?search=wagner
?parent_country_id=1
?sort=name
?order=asc
?per_page=25
?page=1

# Event Listing with Actor Filters
?perpetrator_id=1
?victim_id=2
?actor_id=15                    # Any role
?actor_type=terrorist_org       # Events involving this type
```

---

## 8. UI Components & Workflows

### 8.1 Actor Selection Component

```vue
<template>
  <div class="actor-selector">
    <label>{{ label }}</label>

    <!-- Search/Filter -->
    <input
      v-model="search"
      type="text"
      placeholder="Search actors..."
      @input="searchActors"
    />

    <!-- Type Filter -->
    <select v-model="typeFilter">
      <option value="">All Types</option>
      <option value="country">Countries</option>
      <option value="terrorist_org">Terrorist Organizations</option>
      <option value="rebel_group">Rebel Groups</option>
      <!-- ... -->
    </select>

    <!-- Results -->
    <div class="results">
      <div
        v-for="actor in filteredActors"
        :key="actor.id"
        class="actor-option"
        @click="selectActor(actor)"
      >
        <img
          v-if="actor.flag_url || actor.logo_url"
          :src="actor.flag_url || actor.logo_url"
          class="actor-icon"
        />
        <div class="actor-info">
          <div class="actor-name">{{ actor.name }}</div>
          <div class="actor-type">{{ formatActorType(actor.actor_type) }}</div>
        </div>
        <span
          v-if="actor.designation_status !== 'none'"
          class="badge"
        >
          {{ actor.designation_status }}
        </span>
      </div>
    </div>
  </div>
</template>
```

### 8.2 Event Actor Assignment Workflow

```
Event Creation/Edit Form
└── Perpetrator Section
    ├── [Add Perpetrator Button]
    ├── Actor Selector (with filters)
    ├── Role Description (text input)
    ├── Certainty Level (dropdown)
    ├── Personnel Count (number input)
    └── Source URL (text input)

└── Victim Section
    ├── [Add Victim Button]
    └── (same fields as perpetrator)

└── Equipment Owner (for equipment events)
    └── Auto-populated based on equipment database

└── Other Actors (optional)
    ├── Role selector (ally, mediator, etc.)
    └── Same fields
```

### 8.3 Actor Detail Page Layout

```
+--------------------------------------------------+
| [Flag/Logo]  Actor Name                          |
| Short Name | Actor Type Badge | Status Badge     |
+--------------------------------------------------+
| Also Known As: alias1, alias2, alias3            |
+--------------------------------------------------+
| DETAILS                                          |
| Official Name: ....                              |
| Founded: YYYY-MM-DD                              |
| Primary Region: ....                             |
| Ideology: ....                                   |
| Estimated Size: ....                             |
+--------------------------------------------------+
| RELATIONSHIPS                                    |
| Allied with: [Actor A] [Actor B]                 |
| Hostile to: [Actor C] [Actor D]                  |
| Sponsored by: [Actor E]                          |
+--------------------------------------------------+
| EVENTS INVOLVING THIS ACTOR                      |
| [Timeline visualization]                         |
| As Perpetrator: 45 events                        |
| As Victim: 23 events                             |
| [View All Events →]                              |
+--------------------------------------------------+
```

---

## 9. Implementation Checklist

### Phase 1: Database & Models
- [ ] Create `actors` migration
- [ ] Create `event_actors` migration
- [ ] Create `actor_relationships` migration
- [ ] Create `actor_aliases` migration
- [ ] Create `Actor` Eloquent model
- [ ] Create `ActorRelationship` Eloquent model
- [ ] Create `ActorAlias` Eloquent model
- [ ] Update `Event` model with actor relationships
- [ ] Create seeders for initial actor data

### Phase 2: Backend API
- [ ] Create `ActorController` with CRUD operations
- [ ] Create `EventActorController` for pivot operations
- [ ] Create `ActorRelationshipController`
- [ ] Add validation rules for actor data
- [ ] Create API resources for actor serialization
- [ ] Add actor filtering and search endpoints
- [ ] Update event API to include actor data

### Phase 3: Frontend Types & Stores
- [ ] Create TypeScript interfaces (as above)
- [ ] Create Pinia store for actors
- [ ] Create actor composables
- [ ] Add actor utilities and helpers

### Phase 4: UI Components
- [ ] Create `ActorSelector` component
- [ ] Create `ActorCard` component
- [ ] Create `ActorBadge` component
- [ ] Create `EventActorForm` component
- [ ] Create `ActorRelationshipGraph` component
- [ ] Update `EventForm` with actor assignment

### Phase 5: Pages & Features
- [ ] Create Actor List page
- [ ] Create Actor Detail page
- [ ] Create Actor Create/Edit page
- [ ] Add actor filtering to Event List
- [ ] Add actor attribution to Event Detail page
- [ ] Add actor statistics dashboard

### Phase 6: Testing & Documentation
- [ ] Write unit tests for Actor model
- [ ] Write feature tests for actor API
- [ ] Write frontend component tests
- [ ] Update API documentation
- [ ] Update user documentation
- [ ] Create actor data import scripts

---

## 10. Migration Path from Old System

If migrating from the existing `factions` table:

```sql
-- Step 1: Migrate countries from old system
INSERT INTO actors (
    name, short_name, actor_type, color_hex,
    iso_code_alpha2, iso_code_alpha3, designation_status,
    is_active, created_at, updated_at
)
SELECT
    name,
    short_name,
    'country' as actor_type,
    color,
    iso_code,
    iso_code3,
    'recognized_state' as designation_status,
    active,
    created_at,
    updated_at
FROM countries;

-- Step 2: Migrate factions to actors
INSERT INTO actors (
    name, short_name, actor_type, parent_country_id,
    color_hex, logo_url, description, is_active,
    created_at, updated_at
)
SELECT
    name,
    short_name,
    CASE
        WHEN description LIKE '%terrorist%' THEN 'terrorist_org'
        WHEN description LIKE '%rebel%' THEN 'rebel_group'
        WHEN description LIKE '%militia%' THEN 'militia'
        ELSE 'unknown'
    END as actor_type,
    country_id,
    color,
    logo_url,
    description,
    active,
    created_at,
    updated_at
FROM factions;

-- Step 3: Migrate event-faction relationships to event-actors
INSERT INTO event_actors (
    event_id, actor_id, role, certainty, created_at, updated_at
)
SELECT
    event_id,
    new_actor_id_from_old_faction,
    'perpetrator' as role,  -- Default, will need manual review
    'unconfirmed' as certainty,
    NOW(),
    NOW()
FROM old_event_faction_table;
```

---

## 11. Advanced Features (Future Enhancements)

### 11.1 Network Analysis
- Graph visualization of actor relationships
- Identify key players and intermediaries
- Track changes in alliances over time

### 11.2 Attribution Confidence Scoring
- Algorithm to calculate attribution confidence
- Based on: source count, source reliability, visual evidence, official confirmations
- Display confidence score with each attribution

### 11.3 Actor Timeline
- Visual timeline of actor activities
- Track evolution (founded, splits, mergers, dissolved)
- Show relationship changes over time

### 11.4 Automated Attribution Suggestions
- NLP analysis of event descriptions
- Suggest likely actors based on location, event type, historical patterns
- Require human verification before accepting

### 11.5 Actor Comparison Tool
- Side-by-side comparison of two or more actors
- Compare: size, activity level, losses, territorial control
- Generate comparison reports

---

## 12. Security & Privacy Considerations

### 12.1 Sensitive Actor Data
- Mark certain actors as "sensitive" (e.g., intelligence sources)
- Implement role-based access control for viewing sensitive actors
- Audit log all access to sensitive actor profiles

### 12.2 Attribution Disputes
- Allow users to dispute actor attributions
- Track dispute history
- Require elevated permissions to modify disputed attributions

### 12.3 Data Sources
- Always record source of attribution
- Maintain chain of custody for attribution claims
- Archive sources (screenshots, wayback machine)

---

## Conclusion

This Actor Attribution System provides a comprehensive framework for tracking state and non-state actors involved in military events. The system is:

- **Flexible**: Supports all types of actors from countries to terrorist organizations
- **Detailed**: Captures roles, certainty levels, and supporting evidence
- **Relational**: Tracks relationships between actors over time
- **Timeline-aware**: Maintains historical accuracy
- **Verifiable**: Emphasizes source citation and evidence
- **Scalable**: Database design supports millions of events and actors

The schema is designed to be **MySQL and PostgreSQL compatible**, uses **Laravel best practices**, and provides **complete TypeScript definitions** for frontend development.
