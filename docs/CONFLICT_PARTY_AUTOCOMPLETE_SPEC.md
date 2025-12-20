# Conflict Party Autocomplete System - Complete Specification

## Table of Contents
1. [Current Active Conflicts (2024-2025)](#current-active-conflicts-2024-2025)
2. [Conflict Parties Database](#conflict-parties-database)
3. [Database Schema Design](#database-schema-design)
4. [Autocomplete System Design](#autocomplete-system-design)
5. [API Endpoints](#api-endpoints)
6. [Frontend Implementation](#frontend-implementation)
7. [Seed Data Specification](#seed-data-specification)

---

## 1. Current Active Conflicts (2024-2025)

### Major Active Conflicts

#### 1.1 Russia-Ukraine War (2022-Present)
**Status**: High intensity
**Parties**:
- **State Actors**:
  - Russia (Russian Federation)
  - Ukraine
  - Belarus (supporting Russia)
- **Groups**:
  - Wagner Group (Russian PMC)
  - Donetsk People's Republic (DPR)
  - Luhansk People's Republic (LPR)
  - Chechen Forces (Kadyrovites)
  - Ukrainian Armed Forces
  - Ukrainian Territorial Defense Forces
  - International Legion for the Defense of Ukraine
  - Azov Regiment

#### 1.2 Israel-Gaza Conflict (2023-Present)
**Status**: High intensity
**Parties**:
- **State Actors**:
  - Israel
  - Palestine (Palestinian Authority - limited involvement)
- **Groups**:
  - Hamas (Izz ad-Din al-Qassam Brigades)
  - Palestinian Islamic Jihad (PIJ)
  - Popular Resistance Committees
  - Hezbollah (Lebanon-based, supporting Gaza)
  - Houthis/Ansar Allah (Yemen-based, supporting Gaza)
  - Israel Defense Forces (IDF)

#### 1.3 Sudan Civil War (2023-Present)
**Status**: High intensity
**Parties**:
- **State Actors**:
  - Sudan (Government forces)
- **Groups**:
  - Sudanese Armed Forces (SAF)
  - Rapid Support Forces (RSF)
  - Sudan Liberation Movement/Army (SLM/A)
  - Justice and Equality Movement (JEM)

#### 1.4 Myanmar Civil War (2021-Present)
**Status**: High intensity
**Parties**:
- **State Actors**:
  - Myanmar (Military Junta - State Administration Council)
- **Groups**:
  - Tatmadaw (Myanmar Armed Forces)
  - People's Defense Force (PDF)
  - Kachin Independence Army (KIA)
  - Karen National Liberation Army (KNLA)
  - Arakan Army (AA)
  - Ta'ang National Liberation Army (TNLA)
  - Myanmar National Democratic Alliance Army (MNDAA)
  - Karenni Army
  - Chin National Front/Army

#### 1.5 Yemen Conflict (2014-Present)
**Status**: Medium-high intensity
**Parties**:
- **State Actors**:
  - Yemen (Internationally recognized government)
  - Saudi Arabia (coalition leader)
  - United Arab Emirates
  - Iran (supporting Houthis)
- **Groups**:
  - Houthis/Ansar Allah
  - Southern Transitional Council (STC)
  - Al-Qaeda in the Arabian Peninsula (AQAP)
  - Islamic State in Yemen
  - Yemeni Armed Forces (pro-government)
  - Giant Brigades
  - National Resistance Forces

#### 1.6 Syrian Conflict (2011-Present)
**Status**: Low-medium intensity (frozen in parts)
**Parties**:
- **State Actors**:
  - Syria (Assad government)
  - Turkey
  - United States (limited presence)
  - Russia (supporting Syria)
  - Iran (supporting Syria)
- **Groups**:
  - Syrian Arab Army (SAA)
  - Syrian Democratic Forces (SDF/YPG)
  - Hayat Tahrir al-Sham (HTS, formerly Al-Nusra)
  - Turkish-backed Syrian National Army (SNA)
  - Islamic State (ISIS remnants)
  - Free Syrian Army (FSA remnants)
  - Hezbollah (Lebanon-based)
  - Various Iranian-backed militias

#### 1.7 Ethiopian Conflicts (Intermittent 2020-Present)
**Status**: Low-medium intensity
**Parties**:
- **State Actors**:
  - Ethiopia (Federal Government)
  - Eritrea (supporting Ethiopia in Tigray)
- **Groups**:
  - Ethiopian National Defense Force (ENDF)
  - Tigray Defense Forces (TDF)
  - Oromo Liberation Army (OLA)
  - Fano (Amhara militia)
  - Eritrean Defense Forces

#### 1.8 Sahel Region Conflicts

##### 1.8.1 Mali Conflict (2012-Present)
**Status**: Medium intensity
**Parties**:
- **State Actors**:
  - Mali (Military Junta)
  - France (withdrawn 2022)
  - Russia (Wagner Group support)
- **Groups**:
  - Malian Armed Forces
  - Wagner Group
  - Jama'at Nasr al-Islam wal Muslimin (JNIM)
  - Islamic State in the Greater Sahara (ISGS)
  - Tuareg separatist groups (MNLA, CMA)

##### 1.8.2 Burkina Faso Insurgency (2015-Present)
**Status**: Medium-high intensity
**Parties**:
- **State Actors**:
  - Burkina Faso (Military Government)
- **Groups**:
  - Burkinabe Armed Forces
  - Jama'at Nasr al-Islam wal Muslimin (JNIM)
  - Islamic State in the Greater Sahara (ISGS)
  - Volunteers for the Defense of the Homeland (VDP)

##### 1.8.3 Niger Conflict (2015-Present)
**Status**: Medium intensity
**Parties**:
- **State Actors**:
  - Niger (Military Junta)
- **Groups**:
  - Niger Armed Forces
  - Jama'at Nasr al-Islam wal Muslimin (JNIM)
  - Islamic State in the Greater Sahara (ISGS)
  - Boko Haram (spillover from Nigeria)

#### 1.9 Nigeria Conflicts

##### 1.9.1 Boko Haram Insurgency (2009-Present)
**Status**: Medium intensity
**Parties**:
- **State Actors**:
  - Nigeria
  - Chad
  - Niger
  - Cameroon
- **Groups**:
  - Nigerian Armed Forces
  - Boko Haram
  - Islamic State West Africa Province (ISWAP)
  - Civilian Joint Task Force (CJTF)

##### 1.9.2 Fulani-Farmer Conflicts
**Status**: Low-medium intensity
**Parties**:
- Various ethnic militias and self-defense groups

#### 1.10 Somalia Conflict (2006-Present)
**Status**: Medium intensity
**Parties**:
- **State Actors**:
  - Somalia (Federal Government)
  - United States (counterterrorism operations)
  - Kenya
  - Ethiopia
- **Groups**:
  - Somali National Army
  - Al-Shabaab
  - Islamic State in Somalia
  - Various clan militias
  - AMISOM/ATMIS (African Union forces)

#### 1.11 Democratic Republic of Congo (Ongoing)
**Status**: Medium intensity
**Parties**:
- **State Actors**:
  - Democratic Republic of Congo
  - Rwanda (alleged support to M23)
  - Uganda
- **Groups**:
  - Armed Forces of the DRC (FARDC)
  - M23 (March 23 Movement)
  - Allied Democratic Forces (ADF)
  - CODECO
  - Mai-Mai militias
  - FDLR (Democratic Forces for the Liberation of Rwanda)

#### 1.12 Mexican Drug War (2006-Present)
**Status**: High intensity
**Parties**:
- **State Actors**:
  - Mexico (Government forces)
- **Criminal Organizations**:
  - Sinaloa Cartel
  - Jalisco New Generation Cartel (CJNG)
  - Gulf Cartel
  - Los Zetas
  - Cartel del Noreste (CDN)
  - Beltrán-Leyva Organization
  - Juárez Cartel
  - Tijuana Cartel
  - La Familia Michoacana
  - Knights Templar Cartel

#### 1.13 Afghanistan Conflict (1978-Present)
**Status**: Medium intensity
**Parties**:
- **State Actors**:
  - Afghanistan (Taliban government)
  - Pakistan (alleged support to Taliban)
- **Groups**:
  - Taliban (Islamic Emirate of Afghanistan)
  - Islamic State - Khorasan Province (IS-KP/ISIS-K)
  - National Resistance Front of Afghanistan (NRF)
  - Al-Qaeda

#### 1.14 Pakistan Insurgencies
**Status**: Low-medium intensity
**Parties**:
- **State Actors**:
  - Pakistan
- **Groups**:
  - Pakistani Armed Forces
  - Tehrik-i-Taliban Pakistan (TTP)
  - Balochistan Liberation Army (BLA)
  - Islamic State - Khorasan Province

#### 1.15 Colombia-Venezuela Border
**Status**: Low intensity
**Parties**:
- **State Actors**:
  - Colombia
  - Venezuela
- **Groups**:
  - National Liberation Army (ELN)
  - FARC dissidents
  - Clan del Golfo
  - Venezuelan Armed Forces

#### 1.16 India-Pakistan (Kashmir)
**Status**: Low intensity (frozen conflict)
**Parties**:
- **State Actors**:
  - India
  - Pakistan
- **Groups**:
  - Indian Armed Forces
  - Lashkar-e-Taiba
  - Jaish-e-Mohammed
  - Hizbul Mujahideen

#### 1.17 Armenia-Azerbaijan (Nagorno-Karabakh)
**Status**: Resolved 2023 (monitoring status)
**Parties**:
- **State Actors**:
  - Armenia
  - Azerbaijan
  - Russia (peacekeepers)

#### 1.18 Mozambique Insurgency (2017-Present)
**Status**: Medium intensity
**Parties**:
- **State Actors**:
  - Mozambique
  - Rwanda (deployed forces)
  - South Africa (SADC forces)
- **Groups**:
  - Mozambican Armed Forces
  - Ansar al-Sunna (local ISIS affiliate)
  - Islamic State - Central Africa Province

#### 1.19 Libya Conflict (2011-Present)
**Status**: Low intensity (frozen)
**Parties**:
- **State Actors**:
  - Libya (Government of National Unity - Tripoli)
  - Libya (House of Representatives - Tobruk)
  - Turkey (supporting GNA)
  - UAE (supporting LNA)
  - Egypt (supporting LNA)
  - Russia (Wagner Group)
- **Groups**:
  - Libyan National Army (LNA)
  - Government of National Accord forces
  - Various tribal militias

#### 1.20 Iraq (ISIS Remnants)
**Status**: Low intensity
**Parties**:
- **State Actors**:
  - Iraq
  - United States (advisory role)
- **Groups**:
  - Iraqi Security Forces
  - Popular Mobilization Forces (PMF/Hashd al-Shaabi)
  - Islamic State remnants
  - Kurdish Peshmerga

---

## 2. Conflict Parties Database

### 2.1 Entity Classification System

Conflict parties are classified into the following types:

1. **STATE** - Sovereign nations (e.g., Russia, United States)
2. **SEPARATIST** - Groups seeking independence or autonomy (e.g., DPR, LPR)
3. **INSURGENT** - Groups fighting against government (e.g., Taliban, Al-Shabaab)
4. **TERRORIST** - Designated terrorist organizations (e.g., ISIS, Al-Qaeda)
5. **MILITIA** - Armed non-state groups (e.g., Hezbollah, Wagner Group)
6. **PMC** - Private Military Companies (e.g., Wagner Group)
7. **CARTEL** - Drug trafficking organizations (e.g., Sinaloa Cartel, CJNG)
8. **REBEL** - Rebel movements (e.g., Free Syrian Army)
9. **ETHNIC_MILITIA** - Ethnically-based armed groups (e.g., Fano, Mai-Mai)
10. **GOVERNMENT_FORCES** - Official military of a state (e.g., IDF, SAF)
11. **COALITION** - Alliance of multiple parties (e.g., Saudi-led coalition)
12. **PROXY** - Groups acting on behalf of state actors

### 2.2 Designation Systems

Organizations are tracked against multiple designation lists:

- **US State Department** - Foreign Terrorist Organizations (FTO)
- **EU** - European Union Terrorist List
- **UN** - United Nations Security Council Sanctions Lists
- **UK** - Proscribed Organizations
- **National Designations** - Country-specific terror lists

### 2.3 Active Conflict Status

Each party has an "active in conflict" flag with metadata:
- **is_active**: Boolean indicating current involvement
- **activity_level**: `high`, `medium`, `low`, `inactive`
- **last_verified**: Date of last confirmed activity
- **conflicts**: Array of conflict IDs they're involved in

---

## 3. Database Schema Design

### 3.1 Core Tables

#### Table: `actors`
Stores all conflict parties (countries, groups, organizations).

```sql
CREATE TABLE actors (
    id SERIAL PRIMARY KEY,
    uuid UUID UNIQUE DEFAULT gen_random_uuid(),

    -- Basic Information
    name VARCHAR(500) NOT NULL,
    short_name VARCHAR(100),
    alias_names TEXT[], -- Array of alternative names
    actor_type VARCHAR(50) NOT NULL, -- STATE, INSURGENT, TERRORIST, etc.

    -- Geographic
    country_id INTEGER REFERENCES countries(id) NULL, -- For state actors
    primary_region VARCHAR(100), -- Middle East, Sahel, Eastern Europe, etc.
    operational_areas TEXT[], -- Array of regions/countries where active

    -- Classification
    is_state_actor BOOLEAN DEFAULT false,
    is_designated_terrorist BOOLEAN DEFAULT false,
    designations JSONB, -- {us: true, eu: false, un: true, uk: true, etc.}

    -- Activity Status
    is_active_in_conflict BOOLEAN DEFAULT false,
    activity_level VARCHAR(20) CHECK (activity_level IN ('high', 'medium', 'low', 'inactive')),
    last_activity_date DATE,

    -- Autocomplete Priority
    autocomplete_priority INTEGER DEFAULT 0, -- Higher = shown first
    priority_score DECIMAL(5,2) DEFAULT 0.0, -- Computed score for sorting

    -- Visual
    logo_url VARCHAR(500),
    flag_url VARCHAR(500),
    color_hex VARCHAR(7), -- For map visualization
    icon VARCHAR(100), -- Font Awesome icon name

    -- Metadata
    description TEXT,
    founded_date DATE,
    dissolved_date DATE,
    successor_id INTEGER REFERENCES actors(id), -- If dissolved
    parent_organization_id INTEGER REFERENCES actors(id), -- For sub-groups

    -- External Links
    wikipedia_url VARCHAR(500),
    official_website VARCHAR(500),

    -- Search Optimization
    search_vector tsvector, -- Full-text search

    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    deleted_at TIMESTAMP
);

-- Indexes
CREATE INDEX idx_actors_type ON actors(actor_type);
CREATE INDEX idx_actors_active ON actors(is_active_in_conflict, activity_level);
CREATE INDEX idx_actors_priority ON actors(autocomplete_priority DESC, priority_score DESC);
CREATE INDEX idx_actors_search ON actors USING GIN(search_vector);
CREATE INDEX idx_actors_name_trgm ON actors USING GIN(name gin_trgm_ops);
CREATE INDEX idx_actors_short_name_trgm ON actors USING GIN(short_name gin_trgm_ops);

-- Trigger to update search_vector
CREATE TRIGGER actors_search_vector_update BEFORE INSERT OR UPDATE
ON actors FOR EACH ROW EXECUTE FUNCTION
tsvector_update_trigger(search_vector, 'pg_catalog.english', name, short_name, description);
```

#### Table: `conflicts`
Defines active conflicts and their metadata.

```sql
CREATE TABLE conflicts (
    id SERIAL PRIMARY KEY,
    uuid UUID UNIQUE DEFAULT gen_random_uuid(),

    -- Basic Information
    name VARCHAR(500) NOT NULL,
    short_name VARCHAR(100),
    alias_names TEXT[],

    -- Classification
    conflict_type VARCHAR(50), -- CIVIL_WAR, INTERSTATE, INSURGENCY, etc.
    intensity_level VARCHAR(20) CHECK (intensity_level IN ('high', 'medium', 'low', 'frozen')),

    -- Geographic
    primary_country_id INTEGER REFERENCES countries(id),
    affected_countries INTEGER[] DEFAULT '{}', -- Array of country IDs
    region VARCHAR(100),

    -- Timeline
    start_date DATE NOT NULL,
    end_date DATE,
    is_active BOOLEAN DEFAULT true,

    -- Casualties (estimates)
    estimated_casualties JSONB, -- {military: 10000, civilian: 5000, total: 15000}

    -- Metadata
    description TEXT,
    background TEXT,

    -- External Links
    wikipedia_url VARCHAR(500),

    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    deleted_at TIMESTAMP
);

CREATE INDEX idx_conflicts_active ON conflicts(is_active, intensity_level);
CREATE INDEX idx_conflicts_dates ON conflicts(start_date, end_date);
```

#### Table: `conflict_parties`
Links actors to conflicts with role information.

```sql
CREATE TABLE conflict_parties (
    id SERIAL PRIMARY KEY,

    conflict_id INTEGER NOT NULL REFERENCES conflicts(id) ON DELETE CASCADE,
    actor_id INTEGER NOT NULL REFERENCES actors(id) ON DELETE CASCADE,

    -- Role in Conflict
    side VARCHAR(100), -- Government, Opposition, Neutral, Coalition, etc.
    role VARCHAR(100), -- Primary Combatant, Support, Mediator, etc.

    -- Participation Details
    joined_date DATE,
    left_date DATE,
    is_currently_active BOOLEAN DEFAULT true,

    -- External Support
    supported_by INTEGER[] DEFAULT '{}', -- Array of actor IDs providing support
    opposing INTEGER[] DEFAULT '{}', -- Array of actor IDs they're fighting

    notes TEXT,

    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),

    UNIQUE(conflict_id, actor_id)
);

CREATE INDEX idx_conflict_parties_conflict ON conflict_parties(conflict_id);
CREATE INDEX idx_conflict_parties_actor ON conflict_parties(actor_id);
CREATE INDEX idx_conflict_parties_active ON conflict_parties(is_currently_active);
```

#### Table: `actor_aliases`
Alternative names and spellings for search optimization.

```sql
CREATE TABLE actor_aliases (
    id SERIAL PRIMARY KEY,
    actor_id INTEGER NOT NULL REFERENCES actors(id) ON DELETE CASCADE,

    alias VARCHAR(500) NOT NULL,
    alias_type VARCHAR(50), -- ACRONYM, TRANSLATION, FORMER_NAME, etc.
    language_code CHAR(2), -- ISO 639-1 code
    is_primary BOOLEAN DEFAULT false,

    created_at TIMESTAMP DEFAULT NOW(),

    UNIQUE(actor_id, alias)
);

CREATE INDEX idx_actor_aliases_actor ON actor_aliases(actor_id);
CREATE INDEX idx_actor_aliases_alias_trgm ON actor_aliases USING GIN(alias gin_trgm_ops);
```

#### Table: `actor_relationships`
Tracks relationships between actors (alliances, support, opposition).

```sql
CREATE TABLE actor_relationships (
    id SERIAL PRIMARY KEY,

    actor_id INTEGER NOT NULL REFERENCES actors(id) ON DELETE CASCADE,
    related_actor_id INTEGER NOT NULL REFERENCES actors(id) ON DELETE CASCADE,

    relationship_type VARCHAR(50) NOT NULL, -- ALLY, ENEMY, SUPPORTER, PROXY, PARENT, CHILD
    strength VARCHAR(20), -- STRONG, MODERATE, WEAK

    start_date DATE,
    end_date DATE,
    is_active BOOLEAN DEFAULT true,

    notes TEXT,

    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),

    UNIQUE(actor_id, related_actor_id, relationship_type)
);

CREATE INDEX idx_actor_relationships_actor ON actor_relationships(actor_id);
CREATE INDEX idx_actor_relationships_related ON actor_relationships(related_actor_id);
CREATE INDEX idx_actor_relationships_type ON actor_relationships(relationship_type, is_active);
```

### 3.2 Priority Scoring System

The `priority_score` field in the `actors` table is computed based on:

1. **Active Conflict Participation** (0-40 points)
   - High intensity conflict: 40 points
   - Medium intensity conflict: 25 points
   - Low intensity conflict: 10 points
   - Multiple conflicts: Highest score + 5 per additional conflict

2. **Activity Level** (0-20 points)
   - High: 20 points
   - Medium: 12 points
   - Low: 5 points
   - Inactive: 0 points

3. **Recent Activity** (0-15 points)
   - Active in last 7 days: 15 points
   - Active in last 30 days: 10 points
   - Active in last 90 days: 5 points
   - Older: 0 points

4. **Actor Type** (0-15 points)
   - State actor: 15 points
   - Government forces: 12 points
   - Major insurgent/militia: 10 points
   - Terrorist organization: 8 points
   - Others: 5 points

5. **Global Significance** (0-10 points)
   - UN designated: 5 points
   - Multiple country designations: 3 points
   - Regional significance: 2 points

**Total Maximum Score**: 100 points

#### SQL Function for Priority Calculation

```sql
CREATE OR REPLACE FUNCTION calculate_actor_priority(actor_id INTEGER)
RETURNS DECIMAL(5,2) AS $$
DECLARE
    score DECIMAL(5,2) := 0;
    conflict_score DECIMAL(5,2) := 0;
    activity_score DECIMAL(5,2) := 0;
    recency_score DECIMAL(5,2) := 0;
    type_score DECIMAL(5,2) := 0;
    significance_score DECIMAL(5,2) := 0;
    designation_count INTEGER := 0;
BEGIN
    -- Active Conflict Participation Score
    SELECT COALESCE(SUM(
        CASE c.intensity_level
            WHEN 'high' THEN 40
            WHEN 'medium' THEN 25
            WHEN 'low' THEN 10
            ELSE 0
        END
    ), 0) INTO conflict_score
    FROM conflict_parties cp
    JOIN conflicts c ON cp.conflict_id = c.id
    WHERE cp.actor_id = actor_id
    AND cp.is_currently_active = true
    AND c.is_active = true;

    -- Cap at 40 for highest conflict, plus 5 per additional
    IF conflict_score > 40 THEN
        conflict_score := 40 + ((conflict_score - 40) / 10);
    END IF;

    -- Activity Level Score
    SELECT CASE a.activity_level
        WHEN 'high' THEN 20
        WHEN 'medium' THEN 12
        WHEN 'low' THEN 5
        ELSE 0
    END INTO activity_score
    FROM actors a WHERE a.id = actor_id;

    -- Recent Activity Score
    SELECT CASE
        WHEN a.last_activity_date >= CURRENT_DATE - INTERVAL '7 days' THEN 15
        WHEN a.last_activity_date >= CURRENT_DATE - INTERVAL '30 days' THEN 10
        WHEN a.last_activity_date >= CURRENT_DATE - INTERVAL '90 days' THEN 5
        ELSE 0
    END INTO recency_score
    FROM actors a WHERE a.id = actor_id;

    -- Actor Type Score
    SELECT CASE a.actor_type
        WHEN 'STATE' THEN 15
        WHEN 'GOVERNMENT_FORCES' THEN 12
        WHEN 'INSURGENT' THEN 10
        WHEN 'MILITIA' THEN 10
        WHEN 'TERRORIST' THEN 8
        ELSE 5
    END INTO type_score
    FROM actors a WHERE a.id = actor_id;

    -- Global Significance Score
    SELECT
        CASE WHEN (a.designations->>'un')::boolean THEN 5 ELSE 0 END +
        CASE WHEN (a.designations->>'us')::boolean THEN 2 ELSE 0 END +
        CASE WHEN (a.designations->>'eu')::boolean THEN 2 ELSE 0 END +
        CASE WHEN (a.designations->>'uk')::boolean THEN 1 ELSE 0 END
    INTO significance_score
    FROM actors a WHERE a.id = actor_id;

    score := conflict_score + activity_score + recency_score + type_score + significance_score;

    RETURN LEAST(score, 100.00);
END;
$$ LANGUAGE plpgsql;

-- Trigger to auto-update priority_score
CREATE OR REPLACE FUNCTION update_actor_priority_score()
RETURNS TRIGGER AS $$
BEGIN
    NEW.priority_score := calculate_actor_priority(NEW.id);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER actor_priority_update
BEFORE INSERT OR UPDATE ON actors
FOR EACH ROW
EXECUTE FUNCTION update_actor_priority_score();
```

---

## 4. Autocomplete System Design

### 4.1 Search Algorithm

The autocomplete system uses a multi-stage search approach:

#### Stage 1: Exact Prefix Match (Highest Priority)
```sql
-- Matches beginning of name
WHERE name ILIKE 'query%' OR short_name ILIKE 'query%'
```

#### Stage 2: Fuzzy Match (Trigram Similarity)
```sql
-- Uses PostgreSQL pg_trgm extension
WHERE similarity(name, 'query') > 0.3
OR similarity(short_name, 'query') > 0.3
```

#### Stage 3: Full-Text Search
```sql
-- Uses tsvector for semantic matching
WHERE search_vector @@ plainto_tsquery('english', 'query')
```

#### Stage 4: Alias Match
```sql
-- Searches alternative names
WHERE id IN (
    SELECT actor_id FROM actor_aliases
    WHERE alias ILIKE '%query%'
)
```

### 4.2 Sorting Algorithm

Results are sorted by multiple factors:

```sql
ORDER BY
    -- 1. Active in conflict flag (boolean)
    is_active_in_conflict DESC,

    -- 2. Priority score (computed)
    priority_score DESC,

    -- 3. Exact match bonus
    CASE
        WHEN name ILIKE 'query' THEN 100
        WHEN short_name ILIKE 'query' THEN 90
        WHEN name ILIKE 'query%' THEN 80
        WHEN short_name ILIKE 'query%' THEN 70
        ELSE 0
    END DESC,

    -- 4. Similarity score
    GREATEST(
        similarity(name, 'query'),
        similarity(short_name, 'query')
    ) DESC,

    -- 5. Activity level
    CASE activity_level
        WHEN 'high' THEN 3
        WHEN 'medium' THEN 2
        WHEN 'low' THEN 1
        ELSE 0
    END DESC,

    -- 6. Alphabetical
    name ASC
```

### 4.3 Response Format

```json
{
  "data": [
    {
      "id": 123,
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "name": "Hamas",
      "short_name": "Hamas",
      "actor_type": "TERRORIST",
      "is_active_in_conflict": true,
      "activity_level": "high",
      "priority_score": 85.5,
      "logo_url": "https://...",
      "flag_url": null,
      "color_hex": "#00AA00",
      "icon": "users",
      "conflicts": [
        {
          "id": 5,
          "name": "Israel-Gaza Conflict",
          "intensity_level": "high"
        }
      ],
      "designations": {
        "us": true,
        "eu": true,
        "un": false,
        "uk": true
      },
      "is_state_actor": false,
      "primary_region": "Middle East",
      "match_type": "exact_prefix",
      "similarity_score": 1.0
    }
  ],
  "meta": {
    "query": "hamas",
    "total": 1,
    "active_conflicts_count": 15,
    "showing_active_first": true
  }
}
```

### 4.4 Caching Strategy

```php
// Cache key structure
$cacheKey = "autocomplete:actors:{$query}:{$filters_hash}";

// Cache for 5 minutes for active data
Cache::remember($cacheKey, 300, function() use ($query, $filters) {
    return Actor::autocomplete($query, $filters)->get();
});

// Invalidate on actor updates
Actor::saved(function($actor) {
    Cache::tags(['autocomplete:actors'])->flush();
});
```

### 4.5 Performance Optimizations

1. **Database Indexes**:
   - GIN index on `name` and `short_name` with `gin_trgm_ops`
   - Composite index on `(is_active_in_conflict, priority_score)`
   - Full-text search index on `search_vector`

2. **Query Optimization**:
   - Limit results to 50 max
   - Use prepared statements
   - Select only needed fields

3. **Frontend Debouncing**:
   - 300ms delay before search
   - Minimum 2 characters to trigger

4. **Redis Caching**:
   - Cache top 1000 most searched actors
   - 5-minute TTL for search results
   - Tag-based cache invalidation

---

## 5. API Endpoints

### 5.1 Autocomplete Endpoint

```
GET /api/actors/autocomplete
```

**Query Parameters**:
```
?q=hamas                      # Search query (required, min 2 chars)
?type=TERRORIST,INSURGENT     # Filter by actor types (optional)
?active_only=true             # Only active in conflict (optional)
&region=Middle East           # Filter by region (optional)
&country_id=123               # Filter by country (optional)
&limit=20                     # Results limit (default: 20, max: 50)
&include_aliases=true         # Include alternative names (optional)
```

**Response**:
```json
{
  "data": [
    {
      "id": 123,
      "uuid": "...",
      "name": "Hamas",
      "short_name": "Hamas",
      "display_name": "Hamas (Izz ad-Din al-Qassam Brigades)",
      "actor_type": "TERRORIST",
      "is_active_in_conflict": true,
      "activity_level": "high",
      "priority_score": 85.5,
      "logo_url": "https://...",
      "color_hex": "#00AA00",
      "conflicts": [
        {
          "id": 5,
          "name": "Israel-Gaza Conflict",
          "intensity_level": "high",
          "region": "Middle East"
        }
      ],
      "designations": {
        "us": true,
        "eu": true,
        "un": false,
        "uk": true
      },
      "badge": {
        "text": "Active - High Intensity",
        "color": "red",
        "icon": "exclamation-triangle"
      }
    }
  ],
  "meta": {
    "query": "hamas",
    "total": 1,
    "limit": 20,
    "filters_applied": {
      "active_only": false,
      "types": [],
      "region": null
    },
    "performance": {
      "query_time_ms": 12,
      "cached": false
    }
  }
}
```

### 5.2 Actor Detail Endpoint

```
GET /api/actors/{uuid}
```

**Response**:
```json
{
  "data": {
    "id": 123,
    "uuid": "...",
    "name": "Hamas",
    "short_name": "Hamas",
    "alias_names": ["Harakat al-Muqawama al-Islamiya", "Islamic Resistance Movement"],
    "actor_type": "TERRORIST",
    "description": "...",
    "founded_date": "1987-12-14",
    "primary_region": "Middle East",
    "operational_areas": ["Gaza Strip", "West Bank"],
    "is_active_in_conflict": true,
    "activity_level": "high",
    "last_activity_date": "2024-12-19",
    "designations": {
      "us": true,
      "eu": true,
      "un": false,
      "uk": true
    },
    "conflicts": [
      {
        "id": 5,
        "name": "Israel-Gaza Conflict",
        "role": "Primary Combatant",
        "side": "Opposition",
        "joined_date": "2023-10-07",
        "is_currently_active": true
      }
    ],
    "relationships": {
      "allies": [
        {"id": 45, "name": "Palestinian Islamic Jihad"},
        {"id": 67, "name": "Hezbollah"}
      ],
      "supported_by": [
        {"id": 89, "name": "Iran"}
      ],
      "opposing": [
        {"id": 12, "name": "Israel"}
      ]
    },
    "statistics": {
      "events_count": 1234,
      "equipment_destroyed": 56,
      "equipment_captured": 12,
      "last_event_date": "2024-12-19"
    },
    "external_links": {
      "wikipedia": "https://en.wikipedia.org/wiki/Hamas",
      "official_website": null
    }
  }
}
```

### 5.3 Conflicts List Endpoint

```
GET /api/conflicts
```

**Query Parameters**:
```
?active_only=true
&intensity=high,medium
&region=Middle East
&limit=20
```

**Response**:
```json
{
  "data": [
    {
      "id": 5,
      "uuid": "...",
      "name": "Israel-Gaza Conflict",
      "short_name": "Gaza War 2023",
      "conflict_type": "INTERSTATE",
      "intensity_level": "high",
      "region": "Middle East",
      "start_date": "2023-10-07",
      "end_date": null,
      "is_active": true,
      "parties_count": 8,
      "parties": [
        {
          "actor_id": 123,
          "actor_name": "Hamas",
          "side": "Opposition",
          "role": "Primary Combatant"
        },
        {
          "actor_id": 12,
          "actor_name": "Israel",
          "side": "Government",
          "role": "Primary Combatant"
        }
      ],
      "estimated_casualties": {
        "military": 5000,
        "civilian": 15000,
        "total": 20000
      }
    }
  ],
  "meta": {
    "total": 25,
    "active_count": 20,
    "page": 1,
    "per_page": 20
  }
}
```

---

## 6. Frontend Implementation

### 6.1 TypeScript Interfaces

```typescript
// types/actors.ts

export type ActorType =
  | 'STATE'
  | 'SEPARATIST'
  | 'INSURGENT'
  | 'TERRORIST'
  | 'MILITIA'
  | 'PMC'
  | 'CARTEL'
  | 'REBEL'
  | 'ETHNIC_MILITIA'
  | 'GOVERNMENT_FORCES'
  | 'COALITION'
  | 'PROXY';

export type ActivityLevel = 'high' | 'medium' | 'low' | 'inactive';
export type IntensityLevel = 'high' | 'medium' | 'low' | 'frozen';

export interface ActorDesignations {
  us?: boolean;
  eu?: boolean;
  un?: boolean;
  uk?: boolean;
  [key: string]: boolean | undefined;
}

export interface ConflictSummary {
  id: number;
  name: string;
  intensity_level: IntensityLevel;
  region: string;
}

export interface ActorBadge {
  text: string;
  color: 'red' | 'orange' | 'yellow' | 'green' | 'gray';
  icon: string;
}

export interface Actor {
  id: number;
  uuid: string;
  name: string;
  short_name: string | null;
  display_name: string;
  actor_type: ActorType;
  is_active_in_conflict: boolean;
  activity_level: ActivityLevel;
  priority_score: number;
  logo_url: string | null;
  flag_url: string | null;
  color_hex: string;
  icon: string;
  primary_region: string;
  conflicts: ConflictSummary[];
  designations: ActorDesignations;
  is_state_actor: boolean;
  badge?: ActorBadge;
}

export interface AutocompleteResponse {
  data: Actor[];
  meta: {
    query: string;
    total: number;
    limit: number;
    filters_applied: {
      active_only: boolean;
      types: ActorType[];
      region: string | null;
    };
    performance: {
      query_time_ms: number;
      cached: boolean;
    };
  };
}

export interface ActorFilters {
  types?: ActorType[];
  active_only?: boolean;
  region?: string;
  country_id?: number;
}
```

### 6.2 Vue Composable

```typescript
// composables/useActorAutocomplete.ts

import { ref, computed, watch } from 'vue';
import { debounce } from 'lodash-es';
import type { Actor, ActorFilters, AutocompleteResponse } from '@/types/actors';
import axios from 'axios';

export function useActorAutocomplete(filters: Ref<ActorFilters> = ref({})) {
  const query = ref('');
  const results = ref<Actor[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const selectedActor = ref<Actor | null>(null);
  const meta = ref<AutocompleteResponse['meta'] | null>(null);

  const hasResults = computed(() => results.value.length > 0);
  const isEmpty = computed(() => query.value.length >= 2 && !loading.value && results.value.length === 0);

  const activeConflictActors = computed(() =>
    results.value.filter(actor => actor.is_active_in_conflict)
  );

  const inactiveActors = computed(() =>
    results.value.filter(actor => !actor.is_active_in_conflict)
  );

  const search = debounce(async () => {
    if (query.value.length < 2) {
      results.value = [];
      meta.value = null;
      return;
    }

    loading.value = true;
    error.value = null;

    try {
      const params = new URLSearchParams({
        q: query.value,
        limit: '20',
        include_aliases: 'true',
        ...Object.fromEntries(
          Object.entries(filters.value).map(([key, val]) => [
            key,
            Array.isArray(val) ? val.join(',') : String(val)
          ])
        )
      });

      const response = await axios.get<AutocompleteResponse>(
        `/api/actors/autocomplete?${params}`
      );

      results.value = response.data.data;
      meta.value = response.data.meta;
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to search actors';
      results.value = [];
    } finally {
      loading.value = false;
    }
  }, 300);

  const selectActor = (actor: Actor) => {
    selectedActor.value = actor;
    query.value = actor.display_name;
  };

  const clear = () => {
    query.value = '';
    results.value = [];
    selectedActor.value = null;
    meta.value = null;
    error.value = null;
  };

  watch(query, () => {
    if (query.value.length >= 2) {
      search();
    } else {
      results.value = [];
      meta.value = null;
    }
  });

  return {
    query,
    results,
    loading,
    error,
    selectedActor,
    meta,
    hasResults,
    isEmpty,
    activeConflictActors,
    inactiveActors,
    search,
    selectActor,
    clear
  };
}
```

### 6.3 Vue Component

```vue
<!-- components/ActorAutocomplete.vue -->

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useActorAutocomplete } from '@/composables/useActorAutocomplete';
import type { Actor, ActorFilters } from '@/types/actors';
import {
  Combobox,
  ComboboxInput,
  ComboboxButton,
  ComboboxOptions,
  ComboboxOption,
  TransitionRoot,
} from '@headlessui/vue';
import {
  ChevronUpDownIcon,
  CheckIcon,
  ExclamationTriangleIcon,
  ShieldCheckIcon,
} from '@heroicons/vue/20/solid';

interface Props {
  modelValue?: Actor | null;
  filters?: ActorFilters;
  placeholder?: string;
  label?: string;
  required?: boolean;
  error?: string;
  showBadges?: boolean;
  showConflicts?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Search for countries, groups, organizations...',
  label: 'Select Actor',
  required: false,
  showBadges: true,
  showConflicts: true,
});

const emit = defineEmits<{
  'update:modelValue': [value: Actor | null];
  'select': [value: Actor];
}>();

const filtersRef = ref(props.filters || {});
const {
  query,
  results,
  loading,
  error,
  selectedActor,
  activeConflictActors,
  inactiveActors,
  isEmpty,
  selectActor,
  clear,
} = useActorAutocomplete(filtersRef);

const handleSelect = (actor: Actor | null) => {
  if (actor) {
    selectActor(actor);
    emit('update:modelValue', actor);
    emit('select', actor);
  }
};

const getActorTypeBadgeColor = (type: string): string => {
  const colors: Record<string, string> = {
    STATE: 'bg-blue-100 text-blue-800',
    TERRORIST: 'bg-red-100 text-red-800',
    INSURGENT: 'bg-orange-100 text-orange-800',
    MILITIA: 'bg-purple-100 text-purple-800',
    PMC: 'bg-gray-100 text-gray-800',
    CARTEL: 'bg-pink-100 text-pink-800',
    GOVERNMENT_FORCES: 'bg-green-100 text-green-800',
  };
  return colors[type] || 'bg-gray-100 text-gray-800';
};

const getActivityBadgeColor = (level: string): string => {
  const colors: Record<string, string> = {
    high: 'bg-red-500',
    medium: 'bg-orange-500',
    low: 'bg-yellow-500',
    inactive: 'bg-gray-400',
  };
  return colors[level] || 'bg-gray-400';
};

const formatActorType = (type: string): string => {
  return type.split('_').map(w =>
    w.charAt(0) + w.slice(1).toLowerCase()
  ).join(' ');
};
</script>

<template>
  <div class="w-full">
    <Combobox v-model="selectedActor" @update:modelValue="handleSelect">
      <div class="relative">
        <!-- Label -->
        <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1">
          {{ label }}
          <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Input Container -->
        <div class="relative">
          <ComboboxInput
            class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-10 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': error || props.error }"
            :displayValue="(actor: any) => actor?.display_name || ''"
            :placeholder="placeholder"
            @change="query = $event.target.value"
          />

          <ComboboxButton class="absolute inset-y-0 right-0 flex items-center pr-2">
            <ChevronUpDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
          </ComboboxButton>

          <!-- Loading Spinner -->
          <div v-if="loading" class="absolute inset-y-0 right-8 flex items-center pr-2">
            <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
        </div>

        <!-- Error Message -->
        <p v-if="error || props.error" class="mt-1 text-sm text-red-600">
          {{ error || props.error }}
        </p>

        <!-- Results Dropdown -->
        <TransitionRoot
          leave="transition ease-in duration-100"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
        >
          <ComboboxOptions
            class="absolute z-10 mt-1 max-h-96 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
          >
            <!-- Empty State -->
            <div v-if="isEmpty" class="relative cursor-default select-none py-2 px-4 text-gray-700">
              No actors found matching "{{ query }}"
            </div>

            <!-- Active Conflict Actors Section -->
            <template v-if="activeConflictActors.length > 0">
              <div class="bg-red-50 px-3 py-2 text-xs font-semibold text-red-900 uppercase tracking-wide border-b border-red-100">
                <ExclamationTriangleIcon class="inline h-4 w-4 mr-1" />
                Active in Conflicts ({{ activeConflictActors.length }})
              </div>
              <ComboboxOption
                v-for="actor in activeConflictActors"
                :key="actor.id"
                :value="actor"
                v-slot="{ active, selected }"
                as="template"
              >
                <li
                  :class="[
                    'relative cursor-pointer select-none py-3 pl-3 pr-9',
                    active ? 'bg-indigo-50 text-indigo-900' : 'text-gray-900'
                  ]"
                >
                  <div class="flex items-start space-x-3">
                    <!-- Logo/Flag -->
                    <div class="flex-shrink-0">
                      <img
                        v-if="actor.logo_url || actor.flag_url"
                        :src="actor.logo_url || actor.flag_url"
                        :alt="actor.name"
                        class="h-8 w-8 rounded object-cover"
                      />
                      <div
                        v-else
                        class="h-8 w-8 rounded flex items-center justify-center text-white text-xs font-bold"
                        :style="{ backgroundColor: actor.color_hex }"
                      >
                        {{ actor.short_name?.substring(0, 2) || actor.name.substring(0, 2) }}
                      </div>
                    </div>

                    <!-- Actor Info -->
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center space-x-2">
                        <span :class="['block truncate font-medium', selected && 'font-semibold']">
                          {{ actor.name }}
                        </span>

                        <!-- Activity Indicator -->
                        <span
                          :class="['inline-block h-2 w-2 rounded-full', getActivityBadgeColor(actor.activity_level)]"
                          :title="`Activity: ${actor.activity_level}`"
                        />
                      </div>

                      <!-- Badges -->
                      <div v-if="showBadges" class="mt-1 flex flex-wrap gap-1">
                        <span
                          :class="['inline-flex items-center rounded px-2 py-0.5 text-xs font-medium', getActorTypeBadgeColor(actor.actor_type)]"
                        >
                          {{ formatActorType(actor.actor_type) }}
                        </span>

                        <!-- Designations -->
                        <span
                          v-if="actor.is_designated_terrorist"
                          class="inline-flex items-center rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800"
                          title="Designated Terrorist Organization"
                        >
                          <ShieldCheckIcon class="h-3 w-3 mr-0.5" />
                          Designated
                        </span>

                        <span
                          v-if="actor.priority_score >= 80"
                          class="inline-flex items-center rounded bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800"
                        >
                          High Priority
                        </span>
                      </div>

                      <!-- Conflicts -->
                      <div v-if="showConflicts && actor.conflicts.length > 0" class="mt-1 text-xs text-gray-500">
                        <span class="font-medium">Conflicts:</span>
                        {{ actor.conflicts.map(c => c.name).join(', ') }}
                      </div>
                    </div>

                    <!-- Selected Check -->
                    <span
                      v-if="selected"
                      :class="[
                        'absolute inset-y-0 right-0 flex items-center pr-4',
                        active ? 'text-indigo-600' : 'text-indigo-600'
                      ]"
                    >
                      <CheckIcon class="h-5 w-5" aria-hidden="true" />
                    </span>
                  </div>
                </li>
              </ComboboxOption>
            </template>

            <!-- Other Actors Section -->
            <template v-if="inactiveActors.length > 0">
              <div
                v-if="activeConflictActors.length > 0"
                class="bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 uppercase tracking-wide border-b border-gray-200"
              >
                Other Actors ({{ inactiveActors.length }})
              </div>
              <ComboboxOption
                v-for="actor in inactiveActors"
                :key="actor.id"
                :value="actor"
                v-slot="{ active, selected }"
                as="template"
              >
                <li
                  :class="[
                    'relative cursor-pointer select-none py-3 pl-3 pr-9',
                    active ? 'bg-gray-50 text-gray-900' : 'text-gray-700'
                  ]"
                >
                  <!-- Same structure as above -->
                  <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                      <img
                        v-if="actor.logo_url || actor.flag_url"
                        :src="actor.logo_url || actor.flag_url"
                        :alt="actor.name"
                        class="h-8 w-8 rounded object-cover opacity-70"
                      />
                      <div
                        v-else
                        class="h-8 w-8 rounded flex items-center justify-center text-white text-xs font-bold opacity-70"
                        :style="{ backgroundColor: actor.color_hex }"
                      >
                        {{ actor.short_name?.substring(0, 2) || actor.name.substring(0, 2) }}
                      </div>
                    </div>

                    <div class="flex-1 min-w-0">
                      <div class="flex items-center space-x-2">
                        <span :class="['block truncate', selected && 'font-semibold']">
                          {{ actor.name }}
                        </span>
                      </div>

                      <div v-if="showBadges" class="mt-1 flex flex-wrap gap-1">
                        <span
                          :class="['inline-flex items-center rounded px-2 py-0.5 text-xs font-medium', getActorTypeBadgeColor(actor.actor_type)]"
                        >
                          {{ formatActorType(actor.actor_type) }}
                        </span>
                      </div>
                    </div>

                    <span
                      v-if="selected"
                      class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600"
                    >
                      <CheckIcon class="h-5 w-5" aria-hidden="true" />
                    </span>
                  </div>
                </li>
              </ComboboxOption>
            </template>
          </ComboboxOptions>
        </TransitionRoot>
      </div>
    </Combobox>
  </div>
</template>
```

### 6.4 Usage Example

```vue
<!-- Example usage in event creation form -->
<script setup lang="ts">
import { ref } from 'vue';
import ActorAutocomplete from '@/components/ActorAutocomplete.vue';
import type { Actor } from '@/types/actors';

const attacker = ref<Actor | null>(null);
const victim = ref<Actor | null>(null);

const handleAttackerSelect = (actor: Actor) => {
  console.log('Attacker selected:', actor);
  // Additional logic
};
</script>

<template>
  <form>
    <ActorAutocomplete
      v-model="attacker"
      label="Attacker"
      :required="true"
      :filters="{ types: ['STATE', 'INSURGENT', 'TERRORIST', 'MILITIA'] }"
      @select="handleAttackerSelect"
    />

    <ActorAutocomplete
      v-model="victim"
      label="Victim/Target"
      :required="true"
      class="mt-4"
    />
  </form>
</template>
```

---

## 7. Seed Data Specification

### 7.1 Countries Seed

Major countries involved in active conflicts (high priority for autocomplete):

```php
// database/seeders/CountriesSeeder.php

$activeConflictCountries = [
    ['name' => 'Russia', 'iso_code' => 'RU', 'iso_code3' => 'RUS', 'flag_url' => '...'],
    ['name' => 'Ukraine', 'iso_code' => 'UA', 'iso_code3' => 'UKR', 'flag_url' => '...'],
    ['name' => 'Israel', 'iso_code' => 'IL', 'iso_code3' => 'ISR', 'flag_url' => '...'],
    ['name' => 'Palestine', 'iso_code' => 'PS', 'iso_code3' => 'PSE', 'flag_url' => '...'],
    ['name' => 'Sudan', 'iso_code' => 'SD', 'iso_code3' => 'SDN', 'flag_url' => '...'],
    ['name' => 'Myanmar', 'iso_code' => 'MM', 'iso_code3' => 'MMR', 'flag_url' => '...'],
    ['name' => 'Yemen', 'iso_code' => 'YE', 'iso_code3' => 'YEM', 'flag_url' => '...'],
    ['name' => 'Syria', 'iso_code' => 'SY', 'iso_code3' => 'SYR', 'flag_url' => '...'],
    ['name' => 'Ethiopia', 'iso_code' => 'ET', 'iso_code3' => 'ETH', 'flag_url' => '...'],
    ['name' => 'Mali', 'iso_code' => 'ML', 'iso_code3' => 'MLI', 'flag_url' => '...'],
    ['name' => 'Burkina Faso', 'iso_code' => 'BF', 'iso_code3' => 'BFA', 'flag_url' => '...'],
    ['name' => 'Niger', 'iso_code' => 'NE', 'iso_code3' => 'NER', 'flag_url' => '...'],
    ['name' => 'Nigeria', 'iso_code' => 'NG', 'iso_code3' => 'NGA', 'flag_url' => '...'],
    ['name' => 'Somalia', 'iso_code' => 'SO', 'iso_code3' => 'SOM', 'flag_url' => '...'],
    ['name' => 'Afghanistan', 'iso_code' => 'AF', 'iso_code3' => 'AFG', 'flag_url' => '...'],
    ['name' => 'Pakistan', 'iso_code' => 'PK', 'iso_code3' => 'PAK', 'flag_url' => '...'],
    ['name' => 'Iraq', 'iso_code' => 'IQ', 'iso_code3' => 'IRQ', 'flag_url' => '...'],
    ['name' => 'Libya', 'iso_code' => 'LY', 'iso_code3' => 'LBY', 'flag_url' => '...'],
    ['name' => 'Mexico', 'iso_code' => 'MX', 'iso_code3' => 'MEX', 'flag_url' => '...'],
    ['name' => 'Colombia', 'iso_code' => 'CO', 'iso_code3' => 'COL', 'flag_url' => '...'],
    ['name' => 'Iran', 'iso_code' => 'IR', 'iso_code3' => 'IRN', 'flag_url' => '...'],
    ['name' => 'Saudi Arabia', 'iso_code' => 'SA', 'iso_code3' => 'SAU', 'flag_url' => '...'],
    ['name' => 'Turkey', 'iso_code' => 'TR', 'iso_code3' => 'TUR', 'flag_url' => '...'],
];
```

### 7.2 Actors Seed Data

Complete seed data with priority scoring:

```php
// database/seeders/ActorsSeeder.php

$actors = [
    // RUSSIA-UKRAINE WAR
    [
        'name' => 'Russian Federation',
        'short_name' => 'Russia',
        'actor_type' => 'STATE',
        'is_state_actor' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Eastern Europe',
        'operational_areas' => ['Ukraine', 'Syria', 'Africa'],
        'autocomplete_priority' => 100,
        'color_hex' => '#0039A6',
        'designations' => json_encode([]),
    ],
    [
        'name' => 'Ukraine',
        'short_name' => 'Ukraine',
        'actor_type' => 'STATE',
        'is_state_actor' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Eastern Europe',
        'operational_areas' => ['Ukraine'],
        'autocomplete_priority' => 100,
        'color_hex' => '#0057B7',
        'designations' => json_encode([]),
    ],
    [
        'name' => 'Wagner Group',
        'short_name' => 'Wagner',
        'alias_names' => ['PMC Wagner', 'Wagner PMC'],
        'actor_type' => 'PMC',
        'is_state_actor' => false,
        'is_active_in_conflict' => true,
        'activity_level' => 'medium',
        'primary_region' => 'Global',
        'operational_areas' => ['Ukraine', 'Syria', 'Africa', 'Mali'],
        'autocomplete_priority' => 85,
        'color_hex' => '#8B4513',
        'designations' => json_encode(['us' => true, 'eu' => true]),
    ],
    [
        'name' => 'Donetsk People\'s Republic',
        'short_name' => 'DPR',
        'actor_type' => 'SEPARATIST',
        'is_state_actor' => false,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Eastern Europe',
        'operational_areas' => ['Ukraine - Donetsk Oblast'],
        'autocomplete_priority' => 75,
        'color_hex' => '#000000',
        'designations' => json_encode([]),
    ],
    [
        'name' => 'Luhansk People\'s Republic',
        'short_name' => 'LPR',
        'actor_type' => 'SEPARATIST',
        'is_state_actor' => false,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Eastern Europe',
        'operational_areas' => ['Ukraine - Luhansk Oblast'],
        'autocomplete_priority' => 75,
        'color_hex' => '#0056A7',
        'designations' => json_encode([]),
    ],

    // ISRAEL-GAZA CONFLICT
    [
        'name' => 'Israel',
        'short_name' => 'Israel',
        'actor_type' => 'STATE',
        'is_state_actor' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Middle East',
        'operational_areas' => ['Israel', 'Gaza', 'West Bank', 'Lebanon', 'Syria'],
        'autocomplete_priority' => 100,
        'color_hex' => '#0038B8',
        'designations' => json_encode([]),
    ],
    [
        'name' => 'Hamas',
        'short_name' => 'Hamas',
        'alias_names' => ['Harakat al-Muqawama al-Islamiya', 'Islamic Resistance Movement', 'Izz ad-Din al-Qassam Brigades'],
        'actor_type' => 'TERRORIST',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Middle East',
        'operational_areas' => ['Gaza Strip'],
        'autocomplete_priority' => 95,
        'color_hex' => '#00AA00',
        'designations' => json_encode(['us' => true, 'eu' => true, 'uk' => true, 'un' => false]),
        'founded_date' => '1987-12-14',
    ],
    [
        'name' => 'Palestinian Islamic Jihad',
        'short_name' => 'PIJ',
        'alias_names' => ['Islamic Jihad Movement in Palestine', 'Al-Quds Brigades'],
        'actor_type' => 'TERRORIST',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Middle East',
        'operational_areas' => ['Gaza Strip', 'West Bank'],
        'autocomplete_priority' => 85,
        'color_hex' => '#009900',
        'designations' => json_encode(['us' => true, 'eu' => true, 'uk' => true]),
    ],
    [
        'name' => 'Hezbollah',
        'short_name' => 'Hezbollah',
        'alias_names' => ['Party of God', 'Hizbullah'],
        'actor_type' => 'MILITIA',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Middle East',
        'operational_areas' => ['Lebanon', 'Syria', 'Yemen'],
        'autocomplete_priority' => 90,
        'color_hex' => '#FFCC00',
        'designations' => json_encode(['us' => true, 'uk' => true, 'eu' => false]),
    ],
    [
        'name' => 'Houthis',
        'short_name' => 'Houthis',
        'alias_names' => ['Ansar Allah', 'Ansarullah'],
        'actor_type' => 'INSURGENT',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Middle East',
        'operational_areas' => ['Yemen', 'Red Sea'],
        'autocomplete_priority' => 88,
        'color_hex' => '#007A3D',
        'designations' => json_encode(['us' => true]),
    ],

    // SUDAN CIVIL WAR
    [
        'name' => 'Sudanese Armed Forces',
        'short_name' => 'SAF',
        'actor_type' => 'GOVERNMENT_FORCES',
        'is_state_actor' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Africa - Northeast',
        'operational_areas' => ['Sudan'],
        'autocomplete_priority' => 85,
        'color_hex' => '#007229',
    ],
    [
        'name' => 'Rapid Support Forces',
        'short_name' => 'RSF',
        'alias_names' => ['Janjaweed'],
        'actor_type' => 'MILITIA',
        'is_state_actor' => false,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Africa - Northeast',
        'operational_areas' => ['Sudan', 'Darfur'],
        'autocomplete_priority' => 85,
        'color_hex' => '#D21034',
    ],

    // MYANMAR CIVIL WAR
    [
        'name' => 'Tatmadaw',
        'short_name' => 'Tatmadaw',
        'alias_names' => ['Myanmar Armed Forces', 'Burma Army'],
        'actor_type' => 'GOVERNMENT_FORCES',
        'is_state_actor' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Southeast Asia',
        'operational_areas' => ['Myanmar'],
        'autocomplete_priority' => 80,
        'color_hex' => '#FECB00',
    ],
    [
        'name' => 'People\'s Defense Force',
        'short_name' => 'PDF',
        'alias_names' => ['PDF Myanmar'],
        'actor_type' => 'REBEL',
        'is_state_actor' => false,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Southeast Asia',
        'operational_areas' => ['Myanmar'],
        'autocomplete_priority' => 75,
        'color_hex' => '#EA2839',
    ],
    [
        'name' => 'Arakan Army',
        'short_name' => 'AA',
        'actor_type' => 'INSURGENT',
        'is_state_actor' => false,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Southeast Asia',
        'operational_areas' => ['Myanmar - Rakhine State'],
        'autocomplete_priority' => 70,
        'color_hex' => '#034694',
    ],

    // SYRIA
    [
        'name' => 'Syrian Arab Army',
        'short_name' => 'SAA',
        'actor_type' => 'GOVERNMENT_FORCES',
        'is_state_actor' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'medium',
        'primary_region' => 'Middle East',
        'operational_areas' => ['Syria'],
        'autocomplete_priority' => 70,
        'color_hex' => '#CE1126',
    ],
    [
        'name' => 'Syrian Democratic Forces',
        'short_name' => 'SDF',
        'alias_names' => ['YPG', 'Kurdish Forces'],
        'actor_type' => 'MILITIA',
        'is_state_actor' => false,
        'is_active_in_conflict' => true,
        'activity_level' => 'medium',
        'primary_region' => 'Middle East',
        'operational_areas' => ['Syria - Northeast'],
        'autocomplete_priority' => 70,
        'color_hex' => '#FFD700',
    ],
    [
        'name' => 'Hayat Tahrir al-Sham',
        'short_name' => 'HTS',
        'alias_names' => ['Al-Nusra Front', 'Jabhat al-Nusra'],
        'actor_type' => 'TERRORIST',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'medium',
        'primary_region' => 'Middle East',
        'operational_areas' => ['Syria - Idlib'],
        'autocomplete_priority' => 75,
        'color_hex' => '#000000',
        'designations' => json_encode(['us' => true, 'un' => true]),
    ],

    // SAHEL - TERRORISM
    [
        'name' => 'Jama\'at Nasr al-Islam wal Muslimin',
        'short_name' => 'JNIM',
        'alias_names' => ['GSIM', 'Al-Qaeda in the Sahel'],
        'actor_type' => 'TERRORIST',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Africa - Sahel',
        'operational_areas' => ['Mali', 'Burkina Faso', 'Niger'],
        'autocomplete_priority' => 80,
        'color_hex' => '#000000',
        'designations' => json_encode(['us' => true, 'un' => true]),
    ],
    [
        'name' => 'Islamic State in the Greater Sahara',
        'short_name' => 'ISGS',
        'actor_type' => 'TERRORIST',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Africa - Sahel',
        'operational_areas' => ['Mali', 'Burkina Faso', 'Niger'],
        'autocomplete_priority' => 80,
        'color_hex' => '#000000',
        'designations' => json_encode(['us' => true, 'un' => true]),
    ],
    [
        'name' => 'Boko Haram',
        'short_name' => 'Boko Haram',
        'alias_names' => ['Jama\'atu Ahlis Sunna Lidda\'awati wal-Jihad'],
        'actor_type' => 'TERRORIST',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Africa - West',
        'operational_areas' => ['Nigeria', 'Chad', 'Cameroon', 'Niger'],
        'autocomplete_priority' => 82,
        'color_hex' => '#000000',
        'designations' => json_encode(['us' => true, 'un' => true, 'eu' => true, 'uk' => true]),
    ],
    [
        'name' => 'Islamic State West Africa Province',
        'short_name' => 'ISWAP',
        'actor_type' => 'TERRORIST',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Africa - West',
        'operational_areas' => ['Nigeria', 'Chad', 'Niger'],
        'autocomplete_priority' => 82,
        'color_hex' => '#000000',
        'designations' => json_encode(['us' => true, 'un' => true]),
    ],
    [
        'name' => 'Al-Shabaab',
        'short_name' => 'Al-Shabaab',
        'alias_names' => ['Harakat al-Shabaab al-Mujahideen'],
        'actor_type' => 'TERRORIST',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Africa - East',
        'operational_areas' => ['Somalia', 'Kenya'],
        'autocomplete_priority' => 85,
        'color_hex' => '#000000',
        'designations' => json_encode(['us' => true, 'un' => true, 'eu' => true, 'uk' => true]),
    ],

    // GLOBAL TERRORISM
    [
        'name' => 'Islamic State',
        'short_name' => 'ISIS',
        'alias_names' => ['ISIL', 'Daesh', 'Islamic State of Iraq and Syria'],
        'actor_type' => 'TERRORIST',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'medium',
        'primary_region' => 'Global',
        'operational_areas' => ['Iraq', 'Syria', 'Afghanistan', 'Africa', 'Southeast Asia'],
        'autocomplete_priority' => 90,
        'color_hex' => '#000000',
        'designations' => json_encode(['us' => true, 'un' => true, 'eu' => true, 'uk' => true]),
    ],
    [
        'name' => 'Islamic State - Khorasan Province',
        'short_name' => 'ISIS-K',
        'alias_names' => ['IS-KP', 'ISKP'],
        'actor_type' => 'TERRORIST',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'South Asia',
        'operational_areas' => ['Afghanistan', 'Pakistan'],
        'autocomplete_priority' => 85,
        'color_hex' => '#000000',
        'designations' => json_encode(['us' => true, 'un' => true]),
    ],
    [
        'name' => 'Al-Qaeda',
        'short_name' => 'AQ',
        'alias_names' => ['Al-Qaida', 'The Base'],
        'actor_type' => 'TERRORIST',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'low',
        'primary_region' => 'Global',
        'operational_areas' => ['Afghanistan', 'Yemen', 'Syria', 'Sahel'],
        'autocomplete_priority' => 75,
        'color_hex' => '#000000',
        'designations' => json_encode(['us' => true, 'un' => true, 'eu' => true, 'uk' => true]),
    ],
    [
        'name' => 'Al-Qaeda in the Arabian Peninsula',
        'short_name' => 'AQAP',
        'actor_type' => 'TERRORIST',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'medium',
        'primary_region' => 'Middle East',
        'operational_areas' => ['Yemen'],
        'autocomplete_priority' => 78,
        'color_hex' => '#000000',
        'designations' => json_encode(['us' => true, 'un' => true]),
    ],
    [
        'name' => 'Taliban',
        'short_name' => 'Taliban',
        'alias_names' => ['Islamic Emirate of Afghanistan'],
        'actor_type' => 'INSURGENT',
        'is_state_actor' => true, // De facto government
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'medium',
        'primary_region' => 'South Asia',
        'operational_areas' => ['Afghanistan'],
        'autocomplete_priority' => 85,
        'color_hex' => '#000000',
        'designations' => json_encode(['us' => true, 'un' => true]),
    ],
    [
        'name' => 'Tehrik-i-Taliban Pakistan',
        'short_name' => 'TTP',
        'alias_names' => ['Pakistani Taliban'],
        'actor_type' => 'TERRORIST',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'South Asia',
        'operational_areas' => ['Pakistan', 'Afghanistan'],
        'autocomplete_priority' => 80,
        'color_hex' => '#000000',
        'designations' => json_encode(['us' => true, 'un' => true]),
    ],

    // MEXICAN CARTELS
    [
        'name' => 'Sinaloa Cartel',
        'short_name' => 'Sinaloa',
        'actor_type' => 'CARTEL',
        'is_state_actor' => false,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'North America',
        'operational_areas' => ['Mexico', 'United States'],
        'autocomplete_priority' => 85,
        'color_hex' => '#8B0000',
        'designations' => json_encode(['us' => true]),
    ],
    [
        'name' => 'Jalisco New Generation Cartel',
        'short_name' => 'CJNG',
        'alias_names' => ['Cartel Jalisco Nueva Generacion'],
        'actor_type' => 'CARTEL',
        'is_state_actor' => false,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'North America',
        'operational_areas' => ['Mexico'],
        'autocomplete_priority' => 88,
        'color_hex' => '#DC143C',
        'designations' => json_encode(['us' => true]),
    ],
    [
        'name' => 'Los Zetas',
        'short_name' => 'Zetas',
        'actor_type' => 'CARTEL',
        'is_state_actor' => false,
        'is_active_in_conflict' => true,
        'activity_level' => 'medium',
        'primary_region' => 'North America',
        'operational_areas' => ['Mexico'],
        'autocomplete_priority' => 75,
        'color_hex' => '#800000',
        'designations' => json_encode(['us' => true]),
    ],
    [
        'name' => 'Gulf Cartel',
        'short_name' => 'CDG',
        'alias_names' => ['Cartel del Golfo'],
        'actor_type' => 'CARTEL',
        'is_state_actor' => false,
        'is_active_in_conflict' => true,
        'activity_level' => 'medium',
        'primary_region' => 'North America',
        'operational_areas' => ['Mexico'],
        'autocomplete_priority' => 75,
        'color_hex' => '#B22222',
        'designations' => json_encode(['us' => true]),
    ],

    // COLOMBIA
    [
        'name' => 'National Liberation Army',
        'short_name' => 'ELN',
        'alias_names' => ['Ejercito de Liberacion Nacional'],
        'actor_type' => 'INSURGENT',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'medium',
        'primary_region' => 'South America',
        'operational_areas' => ['Colombia', 'Venezuela'],
        'autocomplete_priority' => 70,
        'color_hex' => '#FF0000',
        'designations' => json_encode(['us' => true, 'eu' => true]),
    ],

    // DRC
    [
        'name' => 'M23',
        'short_name' => 'M23',
        'alias_names' => ['March 23 Movement', 'Mouvement du 23-Mars'],
        'actor_type' => 'REBEL',
        'is_state_actor' => false,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Africa - Central',
        'operational_areas' => ['Democratic Republic of Congo'],
        'autocomplete_priority' => 78,
        'color_hex' => '#007FFF',
    ],
    [
        'name' => 'Allied Democratic Forces',
        'short_name' => 'ADF',
        'actor_type' => 'TERRORIST',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'high',
        'primary_region' => 'Africa - Central',
        'operational_areas' => ['Democratic Republic of Congo', 'Uganda'],
        'autocomplete_priority' => 75,
        'color_hex' => '#000000',
        'designations' => json_encode(['us' => true]),
    ],

    // KASHMIR
    [
        'name' => 'Lashkar-e-Taiba',
        'short_name' => 'LeT',
        'alias_names' => ['Army of the Good'],
        'actor_type' => 'TERRORIST',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'low',
        'primary_region' => 'South Asia',
        'operational_areas' => ['Pakistan', 'India - Kashmir'],
        'autocomplete_priority' => 65,
        'color_hex' => '#000000',
        'designations' => json_encode(['us' => true, 'un' => true, 'eu' => true, 'uk' => true]),
    ],
    [
        'name' => 'Jaish-e-Mohammed',
        'short_name' => 'JeM',
        'alias_names' => ['Army of Mohammed'],
        'actor_type' => 'TERRORIST',
        'is_state_actor' => false,
        'is_designated_terrorist' => true,
        'is_active_in_conflict' => true,
        'activity_level' => 'low',
        'primary_region' => 'South Asia',
        'operational_areas' => ['Pakistan', 'India - Kashmir'],
        'autocomplete_priority' => 65,
        'color_hex' => '#000000',
        'designations' => json_encode(['us' => true, 'un' => true, 'eu' => true, 'uk' => true]),
    ],

    // Add 50+ more actors...
];
```

### 7.3 Conflicts Seed Data

```php
// database/seeders/ConflictsSeeder.php

$conflicts = [
    [
        'name' => 'Russia-Ukraine War',
        'short_name' => 'Ukraine War',
        'alias_names' => ['Russo-Ukrainian War', 'War in Ukraine'],
        'conflict_type' => 'INTERSTATE',
        'intensity_level' => 'high',
        'region' => 'Eastern Europe',
        'start_date' => '2022-02-24',
        'is_active' => true,
        'description' => 'Large-scale military conflict between Russia and Ukraine...',
    ],
    [
        'name' => 'Israel-Gaza Conflict',
        'short_name' => 'Gaza War 2023',
        'alias_names' => ['Israel-Hamas War', 'Operation Iron Swords'],
        'conflict_type' => 'INTERSTATE',
        'intensity_level' => 'high',
        'region' => 'Middle East',
        'start_date' => '2023-10-07',
        'is_active' => true,
        'description' => 'Armed conflict between Israel and Hamas...',
    ],
    [
        'name' => 'Sudan Civil War',
        'short_name' => 'Sudan War',
        'conflict_type' => 'CIVIL_WAR',
        'intensity_level' => 'high',
        'region' => 'Northeast Africa',
        'start_date' => '2023-04-15',
        'is_active' => true,
        'description' => 'Armed conflict between SAF and RSF...',
    ],
    // ... more conflicts
];
```

---

## 8. Implementation Summary

### 8.1 Key Features

1. **Priority-Based Sorting**:
   - Active conflict parties appear first
   - Sorted by computed priority score (0-100)
   - Recent activity weighted heavily
   - Intensity of conflict factored in

2. **Smart Search**:
   - Fuzzy matching for typos
   - Alias/alternative name support
   - Full-text search capabilities
   - Multi-language support ready

3. **Visual Indicators**:
   - Activity level badges (red/orange/yellow dots)
   - Conflict type badges (color-coded)
   - Designation badges (terrorist lists)
   - Conflict names listed

4. **Performance**:
   - Database indexes optimized
   - Redis caching (5-min TTL)
   - Frontend debouncing (300ms)
   - Maximum 50 results returned

5. **Flexibility**:
   - Filterable by type, region, country
   - Active-only filter
   - Composable for reuse
   - Fully typed (TypeScript)

### 8.2 Database Migrations Required

1. `create_actors_table.php`
2. `create_conflicts_table.php`
3. `create_conflict_parties_table.php`
4. `create_actor_aliases_table.php`
5. `create_actor_relationships_table.php`
6. `add_priority_scoring_function.php`

### 8.3 Seeders Required

1. `CountriesSeeder.php` (with active conflict countries)
2. `ActorsSeeder.php` (200+ actors)
3. `ConflictsSeeder.php` (20+ active conflicts)
4. `ConflictPartiesSeeder.php` (link actors to conflicts)
5. `ActorAliasesSeeder.php` (alternative names)
6. `ActorRelationshipsSeeder.php` (alliances, oppositions)

### 8.4 API Controllers Required

1. `ActorController.php`
   - `autocomplete()` - Main autocomplete endpoint
   - `show()` - Actor details
   - `index()` - List actors

2. `ConflictController.php`
   - `index()` - List conflicts
   - `show()` - Conflict details
   - `parties()` - Get parties in conflict

### 8.5 Frontend Components

1. `ActorAutocomplete.vue` - Main autocomplete component
2. `ActorBadge.vue` - Display actor type/status badges
3. `ConflictBadge.vue` - Display conflict info
4. `ActorCard.vue` - Detailed actor information card

---

## Appendix: Maintenance & Updates

### Keeping Data Current

1. **Weekly Updates**:
   - Review conflict intensity levels
   - Update activity_level for actors
   - Add new events/actors as they emerge

2. **Monthly Updates**:
   - Recalculate priority scores
   - Update casualty estimates
   - Review designation lists

3. **Data Sources**:
   - ACLED (Armed Conflict Location & Event Data Project)
   - UCDP (Uppsala Conflict Data Program)
   - US State Department FTO list
   - UN Security Council sanctions lists
   - News aggregation (Reuters, AP, BBC)

4. **Automated Updates** (Future Enhancement):
   - API integration with ACLED
   - Webhook for designation list changes
   - Automated priority score recalculation (daily cron job)

---

**Document Version**: 1.0
**Last Updated**: December 19, 2024
**Next Review**: January 2025
