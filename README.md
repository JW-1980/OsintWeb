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

### Installation Wizard
- **One-time setup wizard** for easy deployment (locked after completion)
- Automatic requirements check (PHP 8.2+, extensions, directory permissions)
- Database configuration with live connection testing
- Interactive migration runner with real-time progress
- Admin account creation with strong password validation
- Optional email configuration (SMTP, Mailgun, Postmark, SES) with test email
- Optional search engine setup (MySQL full-text, Meilisearch, Algolia)
- Application settings (timezone, map defaults, user registration, security)
- CLI alternative for automated deployments and CI/CD pipelines

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
- [Installation Wizard Specification](docs/INSTALLATION_WIZARD_SPEC.md) - One-time setup wizard

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

#### Option 1: Web-Based Installation Wizard (Recommended)

The easiest way to install OsintWeb is through the interactive web-based wizard:

```bash
# Clone repository
git clone https://github.com/your-org/osintweb.git
cd osintweb

# Install dependencies
composer install
npm install && npm run build

# Copy environment file
cp .env.example .env

# Start development server
php artisan serve
```

Then visit `http://localhost:8000` - the installation wizard will automatically start and guide you through:

**Step 1: Welcome & Requirements**
- Checks PHP version (8.2+), required extensions, and directory permissions
- Displays pass/fail status for all requirements

**Step 2: Database Configuration**
- Enter MySQL connection details
- Test connection before proceeding
- Option to create database automatically

**Step 3: Run Migrations**
- View migration progress in real-time
- Optional: Seed initial data (countries, equipment categories)
- Optional: Load sample conflict data for testing

**Step 4: Application Settings**
- Set application name, URL, timezone, and language
- Configure default map center and zoom level
- User registration and security settings

**Step 5: Admin Account**
- Create administrator account
- Enforces strong password requirements (12+ chars, mixed case, numbers, symbols)

**Step 6: Email Configuration** (Optional - can skip)
- Choose email provider (SMTP, Mailgun, Postmark, SES, or Log)
- Test email delivery before saving
- Can be configured later from admin panel

**Step 7: Search Configuration** (Optional)
- Choose search engine: MySQL Full-Text (default), Meilisearch, or Algolia
- Test connection for external services

**Step 8: Finish**
- Finalizes installation and creates lock file
- Clears caches and optimizes application
- Redirects to homepage - ready to log in!

> **Note:** After installation completes, the wizard is locked and cannot be accessed again unless you delete `storage/installed`

#### Option 2: Command Line Installation

```bash
# Clone and install
git clone https://github.com/your-org/osintweb.git
cd osintweb
composer install
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

# Run migrations with seed data
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

#### Option 3: CLI Installation (Interactive or Automated)

For server environments or automated deployments:

**Interactive CLI Mode:**
```bash
# Prompts you for all configuration values
php artisan osint:install
```

**Non-Interactive (Automated) Mode:**
```bash
# Perfect for CI/CD pipelines and automated deployments
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
    --seed \
    --skip-email
```

**Available Options:**
- `--db-host` - Database host (default: 127.0.0.1)
- `--db-port` - Database port (default: 3306)
- `--db-name` - Database name (default: osintweb)
- `--db-user` - Database username
- `--db-pass` - Database password
- `--admin-name` - Admin user name
- `--admin-email` - Admin email address
- `--admin-pass` - Admin password (min 12 chars)
- `--app-url` - Application URL
- `--seed` - Seed initial data
- `--skip-email` - Skip email configuration
- `--force` - Force reinstallation (deletes lock file)

**Reinstalling:**
```bash
# Delete lock file and run installer again
rm storage/installed
php artisan osint:install --force
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

## Changelog

### Version 1.0.0 (Current)

**Installation Wizard**
- Added one-time installation wizard for easy deployment
- Web-based setup interface with 8-step guided process
- Automatic system requirements verification
- Live database connection testing
- Interactive migration runner with progress tracking
- Secure admin account creation with password strength validation
- Optional email and search engine configuration
- CLI installer with interactive and automated modes
- Installation lock file prevents re-running wizard
- Full specification available in `docs/INSTALLATION_WIZARD_SPEC.md`

**Previous Features**
- Complete OSINT platform with 35 intelligence features
- Actor attribution system with 200+ pre-loaded entities
- Conflict party autocomplete with smart prioritization
- Audit trail system with blockchain-style integrity
- Interactive mapping with control zones and timeline
- Military equipment database with 5 categories
- 49 event types for comprehensive conflict tracking

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
