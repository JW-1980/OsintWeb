# OsintWeb - Military Conflict Tracking Platform

A comprehensive Open Source Intelligence (OSINT) platform for tracking, analyzing, and documenting military conflicts and geopolitical events. Built for analysts, researchers, journalists, and the OSINT community.

## Overview

OsintWeb provides powerful tools for:
- **Interactive Mapping** - Draw control zones, track territorial changes, add events with a click
- **Military Equipment Database** - Comprehensive catalog of ships, vehicles, aircraft, helicopters, and missile systems
- **Event Documentation** - 49 event types for tracking combat, equipment losses, sightings, and more
- **Actor Attribution** - Track perpetrators and victims (countries AND non-state groups)
- **Timeline Analysis** - Historical playback, date comparisons, and change tracking
- **Collaborative Intelligence** - Multi-source verification, community contributions, and audit trails

## Key Features

### Core Mapping
- Interactive map with OpenStreetMap and satellite layers
- Draw and color-code control zones with faction assignments
- Dynamic legend system
- Click-to-add events or enter coordinates manually
- Export to Google Maps/KML/GeoJSON

### Military Equipment Database
- **Naval**: Aircraft carriers, submarines, destroyers, frigates, patrol boats
- **Land**: Tanks, IFVs, APCs, artillery, MLRS, SAM systems
- **Aircraft**: Fighters, bombers, transports, AWACS
- **Helicopters**: Attack, transport, utility, naval
- **Missiles**: MANPADS, ATGMs, portable rocket systems

Per-country inventory tracking with loss statistics.

### Event Types (49 Templates)

**Original 24 Events:** Combat events (airstrikes, artillery, missiles, drones, ground battles, naval engagements), equipment status (destroyed, damaged, captured, abandoned, sightings), troop/convoy movements, infrastructure damage, fortifications, civilian casualties, evacuations, territory changes, ceasefires, announcements, explosions, fires, cyber attacks, POW events.

**25 New Events:** Assassination, chemical/biological attacks, IED/landmine incidents, sabotage operations, electronic warfare, reconnaissance missions, economic sanctions, naval blockades, airspace violations, border incidents, propaganda campaigns, foreign fighter movements, military defections, refugee movements, weapons shipments, arms cache discoveries, humanitarian aid delivery, hostage situations, war crime allegations, siege operations, military base construction, training exercises, protests/civil unrest.

Each event includes **actor attribution** (perpetrator, victim, equipment owner).

### Actor Attribution System
- Track **WHO attacked** (perpetrator) and **WHO was targeted** (victim)
- Support for both countries AND non-state actors:
  - State actors (197 countries)
  - Terrorist organizations (Hamas, ISIS, Al-Qaeda, Hezbollah, etc.)
  - Militias and PMCs (Wagner Group, RSF, etc.)
  - Rebel groups, cartels, separatist movements
- Equipment ownership tracking
- 6 certainty levels (confirmed → unconfirmed)
- Multi-actor events (coalitions, proxy warfare)

### Conflict Party Autocomplete System
- **Smart actor selection** with priority-based sorting
- Active conflict parties appear first (Russia, Ukraine, Hamas, Wagner Group, etc.)
- 200+ pre-loaded actors: countries, terrorist organizations, militias, cartels, rebel groups
- Real-time fuzzy search with alias support (Hamas = "Harakat al-Muqawama al-Islamiya")
- Visual indicators: activity levels, designations (US/EU/UN terror lists), conflict badges
- Automatic priority scoring based on:
  - Current conflict involvement (high/medium/low intensity)
  - Recent activity (last 7/30/90 days)
  - Global significance (UN/US/EU designations)
  - Actor type (state actors, terrorist groups, militias)
- Covers 20+ active conflicts: Russia-Ukraine War, Israel-Gaza, Sudan Civil War, Myanmar, Sahel insurgency, Mexican cartels, and more

### Timeline System
- Historical playback with date range selection
- Compare map states between dates
- Track all changes over time
- Export timeline as reports

### Audit Trail System
- **Complete change tracking** for all entities
- Cryptographic chain (blockchain-style tamper detection)
- Point-in-time queries (view any entity at any date)
- Version comparison with diff visualization
- Safe rollback functionality
- Session and export logging
- GDPR-compliant with retention policies

### 35 OSINT-Focused Features

**Original 15 Features:**
1. Source Verification System
2. Geolocation Verification Tools
3. Equipment Loss Tracking (Oryx-style)
4. Collaborative Verification Workflow
5. Real-time Alert System
6. Advanced Search & Filtering
7. Attribution & Chronolocation
8. Satellite Imagery Integration
9. Social Media Monitoring
10. Network Analysis
11. Report Generation
12. API & Data Pipeline
13. Crowdsourced Intelligence
14. Offline Capability
15. Evidence Preservation (Legal-Grade)

**20 New Features:**
16. Flight Tracking Integration (ADS-B)
17. Maritime Vessel Tracking (AIS)
18. Supply Chain Disruption Tracking
19. Video Frame Analysis & Extraction
20. Reverse Image Search Aggregator
21. Audio Analysis & Authentication
22. Multi-Language Translation Engine
23. OCR & Document Analysis Suite
24. Facial Recognition Assistant
25. Vehicle Identification Database
26. Weather & Environmental Data Overlay
27. Data Quality & Confidence Scoring
28. Disinformation Pattern Detection
29. Cross-Platform Content Correlation
30. Radio Frequency Signal Monitoring
31. Dark Web & Alternative Platform Monitoring
32. Case Management Workspaces
33. Chronological Timeline Builder
34. Communication Network Mapping
35. OSINT Training & Simulation Mode

## Technology Stack

- **Backend**: Laravel 11+ (PHP 8.2+)
- **Frontend**: Vue.js 3 + TypeScript
- **Database**: MySQL 8.0+ with spatial extensions
- **Maps**: Leaflet.js with OpenStreetMap
- **Cache**: Laravel file/database cache (no Redis)
- **Search**: Meilisearch or MySQL full-text
- **Hosting**: Shared hosting compatible ($5-15/month)

## Documentation

### Core Documentation
- [Complete Feature Specification](docs/SPECIFICATION.md) - Detailed development guide
- [Development Guidelines](CLAUDE.md) - Code standards and conventions

### System Specifications
- [Actor Attribution System](docs/ACTOR_ATTRIBUTION_SYSTEM.md) - Country/group tracking for events
- [Conflict Party Autocomplete](docs/CONFLICT_PARTY_AUTOCOMPLETE_SPEC.md) - Smart actor selection with active conflicts
- [Audit Trail Specification](docs/AUDIT_TRAIL_SPECIFICATION.md) - Complete change tracking system
- [Additional OSINT Features](docs/ADDITIONAL_OSINT_FEATURES.md) - 20 new intelligence features

### Technical Documentation
- [MySQL Stack Specification](docs/MYSQL_STACK_SPECIFICATION.md) - Database architecture and hosting guide
- [Stack Migration Summary](docs/STACK_MIGRATION_SUMMARY.md) - Quick reference for tech stack
- [Audit Implementation Guide](docs/AUDIT_IMPLEMENTATION_GUIDE.md) - Audit trail quick start

### Quick References
- [Actor Attribution Quick Reference](docs/ACTOR_ATTRIBUTION_QUICK_REFERENCE.md) - Developer cheat sheet

## Getting Started

### Prerequisites

**Minimum Requirements:**
- PHP 8.2+ with extensions: mysql, mbstring, xml, curl, zip, bcmath, gd
- MySQL 8.0+ with spatial support
- Node.js 18+ and npm
- Composer 2.0+

**Optional (for advanced features):**
- Meilisearch (for better search, can use MySQL full-text instead)
- Supervisor (for queue workers on VPS)

### Installation

```bash
# Clone repository
git clone https://github.com/your-org/osintweb.git
cd osintweb

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Configure database in .env:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=osintweb
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Create database
mysql -u root -p -e "CREATE DATABASE osintweb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate --seed

# Create cache and session tables
php artisan cache:table
php artisan session:table
php artisan queue:table
php artisan migrate

# Build frontend
npm run build

# Start development server
php artisan serve
```

## Project Structure

```
/app                    # Laravel application
  /Models               # Eloquent models (with Auditable trait)
  /Http/Controllers     # API & web controllers
  /Services             # Business logic (Audit, Diff, Rollback)
  /Traits               # Reusable traits (Auditable)
/resources/js           # Vue.js frontend
  /components           # Vue components
  /composables          # Vue composables
  /stores               # Pinia stores
/database
  /migrations           # Database migrations (13 audit + 6 actors)
  /seeders              # Initial data (Actors, Conflicts)
/docs                   # Documentation (14 specification files)
/tests                  # Feature & unit tests
```

## Contributing

1. Read the [Development Guidelines](CLAUDE.md)
2. Create a feature branch
3. Write tests for new features
4. Update documentation
5. Submit pull request

## License

[MIT License](LICENSE)

## Acknowledgments

Built for the OSINT community. Inspired by projects like Oryx, LiveUAMap, and the open-source intelligence movement.

## Current Conflict Coverage (2024-2025)

Based on research from [ACLED](https://acleddata.com/), [CFR Global Conflict Tracker](https://www.cfr.org/global-conflict-tracker), and [Crisis Group](https://www.crisisgroup.org/crisiswatch):

- **Russia-Ukraine War** - Russia, Ukraine, Wagner Group, DPR, LPR
- **Israel-Gaza Conflict** - Israel, Hamas, PIJ, Hezbollah, Houthis
- **Sudan Civil War** - SAF, RSF
- **Myanmar Civil War** - Tatmadaw, PDF, Arakan Army, ethnic militias
- **Sahel Insurgency** - JNIM, ISGS, Boko Haram, ISWAP
- **Syrian Conflict** - SAA, HTS, SDF, various militias
- **Yemen Conflict** - Houthis (Ansar Allah), Saudi-led coalition
- **Mexican Cartel Violence** - Sinaloa, CJNG, Los Zetas, Gulf Cartel
- **And 15+ more active conflicts...**
