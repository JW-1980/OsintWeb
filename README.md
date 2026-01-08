# OsintWeb - Military Conflict Tracking Platform

A comprehensive Open Source Intelligence (OSINT) platform for tracking, analyzing, and documenting military conflicts and geopolitical events. Built for analysts, researchers, journalists, and the OSINT community.

## 📑 Table of Contents
- [🚀 Quick Start (Local Demo)](#-quick-start-local-demo)
- [✨ Features](#-features)
- [🛠️ Technology Stack](#-technology-stack)
- [📚 Documentation](#-documentation)
- [📦 Installation](#-installation)

## 🚀 Quick Start (Local Demo)

Want to see OsintWeb in action immediately? You can run a fully functional demo with mock data using a single command (requires Docker).

```bash
# Clone the repository
git clone https://github.com/your-org/osintweb.git
cd osintweb

# Run the demo script
./start-demo.sh
```

**What this does:**
1.  Builds the application container (PHP, Composer, Node.js).
2.  Starts a local MySQL database.
3.  Installs all dependencies.
4.  **Seeds the database** with sample actors, conflicts, and equipment.
5.  Launches the platform at `http://localhost:8000`.

## Overview

OsintWeb provides powerful tools for:
- **Interactive Mapping** - Draw control zones, track territorial changes, add events with a click
- **Military Equipment Database** - Comprehensive catalog of ships, vehicles, aircraft, helicopters, and missile systems
- **Event Documentation** - 49 event types for tracking combat, equipment losses, sightings, and more
- **Actor Attribution** - Track perpetrators and victims (countries AND non-state groups)
- **Timeline Analysis** - Historical playback, date comparisons, and change tracking
- **Collaborative Intelligence** - Multi-source verification, community contributions, and audit trails

## ✨ Features

### 🗺️ Interactive Mapping
*   **Control Zones**: Draw and color-code control zones with faction assignments to track territorial changes.
*   **Dynamic Legend**: Automatically generated legend based on visible map elements.
*   **Export Options**: Export high-quality maps as PNG, JPG, PDF, or SVG (up to 300 DPI).
*   **Satellite Layers**: Toggle between OpenStreetMap, Satellite, and hybrid views.

### 🚜 Military Equipment Database
*   **Comprehensive Catalog**: 140+ pre-loaded items including ships, tanks, aircraft, and drones.
*   **Detailed Specs**: Track dimensions, armament, range, and production numbers.
*   **Inventory Tracking**: Monitor per-country inventory levels and loss statistics.
*   **CRUD Operations**: Full management capabilities via API and admin panel.

### 💥 Event Documentation
*   **49+ Event Types**: Templates for combat, sightings, infrastructure damage, and more.
*   **Attribution**: Link events to specific perpetrators, victims, and equipment owners.
*   **Evidence Handling**: Attach images, videos, and documents to verify events.

### 🕵️ Actor Attribution
*   **Multi-Actor Support**: Track state actors (197 countries) and non-state groups (militias, terrorist orgs).
*   **Smart Autocomplete**: Priority-based sorting for active conflict parties.
*   **Aliases & Search**: Real-time fuzzy search with support for alternate names.

### ⏱️ Timeline Analysis
*   **Historical Playback**: Animate map changes over time to visualize conflict progression.
*   **Diff Views**: Compare map states between any two dates.
*   **Chronological Builder**: Construct custom investigation timelines linked to events.

### 🔒 Role-Based Access Control (RBAC)
*   **Granular Permissions**: 50+ permission types for fine-grained access control.
*   **Hierarchical Roles**: Admin, Moderator, Analyst, and Viewer roles with inheritance.
*   **Team Management**: Group-based permissions and time-limited access grants.

### 📝 News & CMS
*   **Publishing System**: Create analysis, reports, and tutorials with rich text editing.
*   **Premium Content**: Monetize content with subscription-based access barriers.
*   **SEO Optimization**: Built-in support for meta tags, slugs, and social sharing.

### 💬 Community & Engagement
*   **Threaded Comments**: Deep nesting support with moderation tools.
*   **Anti-Spam**: Advanced protection using honeypots, rate limiting, and spam scoring.
*   **User Profiles**: Customizable profiles with unique generated avatars.

### 🤖 AI & Automation
*   **AI Agents**: Deploy agents for tasks like geolocation and image verification.
*   **Smart Skills**: Keyword-triggered capabilities that enhance investigation workflows.
*   **Transcription**: AI-powered audio transcription with speaker identification (via OpenRouter).

### 🛡️ Privacy & Security
*   **GDPR Compliance**: Built-in tools for consent management, data export, and "right to be forgotten".
*   **Audit Trail**: Cryptographic logging of all changes for data integrity.
*   **Session Management**: Track and terminate active user sessions.

### 🚀 Deployment & Config
*   **Installation Wizard**: Web-based setup for database, admin user, and basic settings.
*   **Docker Ready**: Compatible with containerized environments.
*   **Customizable**: Configure branding, map defaults, and system preferences easily.

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

## 📦 Installation

This section covers detailed installation for production or development environments without Docker.

### Prerequisites

**Minimum Requirements:**
- PHP 8.2+ with extensions: mysql, mbstring, xml, curl, zip, bcmath, gd
- MySQL 8.0+ with spatial support
- Node.js 18+ and npm
- Composer 2.0+

**Optional (for advanced features):**
- Meilisearch (for better search, can use MySQL full-text instead)
- Supervisor (for queue workers on VPS)

### Installation Methods

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

### Version 1.6.0 (Current)

**AI Skills & Agents System**
- Keyword-triggered skill matching with configurable thresholds
- Skills activate based on name, slug, keywords, and aliases in prompts
- Match score calculation with weighted keyword importance
- 15 pre-built OSINT skills:
  - Geolocation Analysis, Image Verification, Video Analysis
  - Military Equipment Identification, Social Media Investigation
  - Timeline Reconstruction, Language Translation, Satellite Imagery Analysis
  - Document Analysis, Conflict Mapping, Casualty Verification
  - Network Analysis, Open Source Research, Weather/Environmental Analysis
  - Unit & Insignia Identification
- Skill-based AI agents with automatic generation from skills
- Composite agents combining multiple skills
- OpenRouter.ai integration for AI-powered responses
- User skill preferences with priority overrides
- Full execution tracking with audit trail
- Token usage, cost tracking, and confidence scoring

**New Database Tables**
- skills: OSINT skill definitions with keywords and configuration
- skill_dependencies: Skill relationship mapping
- agents: AI agent configurations
- agent_skill: Agent-skill pivot with proficiency
- agent_executions: Execution records and results
- skill_triggers: Skill activation logging
- agent_templates: Pre-built agent templates
- user_skill_preferences: User preference overrides

**API Endpoints**
- Skills: list, show, match, suggest, trigger, categories, stats, preferences
- Agents: list, create, update, delete, add/remove skills, run, executions, stats

### Version 1.5.0

**Audio & Transcription System**
- Audio file upload and management with visibility controls
- Support for 7 audio formats: MP3, WAV, OGG, FLAC, AAC, M4A, WebM
- Audio collections/playlists for organization
- Link audio files to conflict events as evidence
- Manual transcription with timed segments
- Speaker identification and diarization
- AI transcription via OpenRouter.ai integration
- Multiple AI model support (OpenAI Whisper, Google Gemini)
- Transcript revision history with diff tracking
- Export to VTT, SRT, and plain text formats
- Confidence scoring for AI transcriptions
- Cost tracking per AI transcription job

**New Database Tables**
- audio_files: Audio file storage and metadata
- transcriptions: Transcription records with type and status
- transcript_segments: Timed text segments with speaker info
- transcript_revisions: Edit history for transcripts
- transcription_jobs: AI transcription job queue
- speakers: Speaker profiles for identification
- audio_collections: Playlist/folder organization
- audio_collection_items: Audio-collection pivot
- audio_event: Link audio to events

**GDPR Compliance in CLAUDE.md**
- Added comprehensive GDPR & Privacy Compliance section
- Data minimization requirements
- Consent management guidelines
- Right to access, portability, and deletion
- Privacy by design principles

### Version 1.4.0

**User Account System**
- Profile management with avatar, bio, organization, location, website
- Timezone and locale preferences
- Session management with device detection
- Active session listing with terminate capabilities
- Activity log tracking all user actions
- Account security with password change functionality
- Failed login attempt tracking with automatic account locking

**Default Random Avatar Generation**
- Unique geometric pattern avatars generated for every user
- Four pattern types: circles, polygons, grid, waves
- Initials-based avatar option
- Gravatar integration for email-linked avatars
- Custom avatar upload with image cropping
- Multiple color palettes: vibrant, pastel, earth, ocean
- Regenerate avatar with new random seed
- SVG-based for perfect scaling at any size

**Onboarding System**
- 9-step guided onboarding flow for new users
- Steps: Welcome, Profile, Avatar, Privacy, Preferences, Explore Map, Browse Events, Explore Equipment, Complete
- Step-by-step progress tracking with completion percentage
- Save progress between sessions
- Skip onboarding option
- Reset and re-run onboarding at any time
- Step-specific content and instructions

**Privacy & GDPR Compliance**
- Consent logging with full audit trail
- Consent types: Terms of service, privacy policy, marketing, analytics, data processing, cookies, third-party sharing
- Data export requests (Right to Portability)
  - Export formats: JSON, CSV, XML
  - Selectable data types: profile, comments, articles, events, bookmarks, activity, consent, sessions
  - Download links with 7-day expiration
- Account deletion requests (Right to be Forgotten)
  - 14-day grace period before deletion
  - Email confirmation required
  - Cancel request during grace period
  - Deletion summary after completion
- Consent history viewing
- Email preference management
- Cookie consent tracking

**New Database Tables**
- consent_logs: Track all consent changes
- data_export_requests: GDPR data export workflow
- account_deletion_requests: GDPR deletion workflow
- user_sessions: Active session management
- user_activities: Activity logging
- onboarding_progress: Track onboarding steps
- user_preference_changes: Preference audit trail

**API Endpoints**
- Account: profile, password, avatar, preferences, privacy, consent, data export, deletion, sessions, activity
- Onboarding: status, step details, complete step, skip, reset

### Version 1.3.0

**News, Articles & Premium Content System**
- Full content management for news, articles, analysis, reports, and tutorials
- Premium content with subscription-based access control
- Article categories: Breaking News, Conflict Analysis, Equipment & Technology, OSINT Techniques, Geopolitics, Investigative Reports, Humanitarian, Cyber & Information Warfare
- Tag system with automatic slug generation
- Featured and pinned articles
- Reading time calculation
- View count and share tracking
- Bookmarks with folder organization
- Reading progress tracking
- SEO metadata (title, description, keywords)
- Publishing workflow: draft, pending review, published, archived
- Scheduled publishing support

**Advanced Threaded Comment System**
- Threaded/nested comments with configurable max depth (default: 5 levels)
- Limited edit window (configurable, default: 15 minutes)
- Complete edit revision history with diff tracking
- Upvote/downvote system with score calculation
- Comment reporting with multiple reason types
- Moderation queue for pending comments
- Bulk moderation actions (approve, reject, spam, delete)

**Anti-Spam & Anti-Bot Protection**
- Honeypot field detection
- Form timing analysis (blocks submissions under 3 seconds)
- Spam score calculation with multiple heuristics:
  - Keyword matching (configurable patterns)
  - Regex pattern detection
  - Domain/URL blocking
  - Excessive caps detection
  - Link count limits
  - Repeated character detection
  - Common spam phrase detection
- Rate limiting: cooldown between comments, hourly/daily limits
- IP and user-based blocking
- Browser fingerprint tracking
- Auto-approval threshold (low spam score = auto-approve)

**Comment Moderation Features**
- Pending queue with spam score visibility
- Report management with action tracking
- Bulk moderation operations
- Author reply highlighting
- Pinned comments
- Comment status: pending, approved, rejected, spam, hidden

**Subscription System**
- User subscription management
- Plan tiers: Free, Basic, Premium, Enterprise
- Feature-based access control
- Subscription status tracking

**18 New Comment/Article Settings**
- Edit window duration
- Max thread depth
- Content length limits
- Rate limiting thresholds
- Guest commenting toggle
- Approval requirements
- Spam detection thresholds
- Voting toggle
- Edit history visibility

### Version 1.2.0

**Expanded Military Equipment Database**
- 140+ real-world military equipment entries with detailed specifications
- Naval vessels: Ford-class, Nimitz-class carriers, Type 055 destroyer, Arleigh Burke, Zumwalt, Virginia-class submarines
- Modern fighter jets: F-22 Raptor, Su-57 Felon, J-20, Gripen E, Eurofighter Typhoon
- Main battle tanks: K2 Black Panther, Type 10, Arjun Mk.2, Altay, T-14 Armata, Strv 122
- Artillery systems: Archer, Koalitsiya-SV, TOS-1A, MLRS, HIMARS, PrSM
- Attack helicopters: Tiger, Mi-28N, Ka-52, AH-1Z Viper
- Strategic bombers: B-2 Spirit, B-21 Raider, Tu-160
- Missiles: Kinzhal, Kh-101, Iskander-M, BrahMos, ATACMS
- Drones: MQ-9 Reaper, Akinci, Bayraktar TB2, Orlan-10, Lancet
- Coverage: 15+ countries (USA, Russia, China, UK, France, Germany, Sweden, South Korea, Japan, India, Turkey, and more)
- All entries include: dimensions, weight, armament, propulsion, range, production numbers
- Full CRUD functionality for managing equipment database

**Map Export System**
- Export maps as PNG, JPG, PDF, or SVG images
- Multiple resolution options: Screen (72 DPI), Print (150 DPI), High Quality (300 DPI)
- Configurable page sizes for PDF (A4, A3, Letter, Legal)
- Include legend, scale bar, title, and timestamp in exports
- Server-side storage for generated exports

**OSINT Skills & Training System**
- 10 OSINT skill categories with comprehensive methodology
- 15+ detailed skills with tools, best practices, and resources
- Skills covering: Geolocation, Image Analysis, Video Verification, Flight Tracking, Maritime Intelligence, Satellite Imagery, Social Media OSINT, Weapons Identification, Unit Identification, Verification Tools
- User skill assessments and certification tracking
- Training exercises with scoring and hints

**Intelligence Agents**
- 18 pre-configured intelligence agents for automated data collection
- Agents for: ADS-B flight tracking, AIS maritime monitoring, satellite change detection, social media monitoring, news aggregation
- Configurable triggers, filters, and notification channels
- Agent execution logging and data point storage
- Support for scheduled and manual agent runs

**Case Management & Investigation**
- Investigation case workspaces with collaboration
- Timeline entries linked to cases
- Network analysis entities and relationships
- Case notes and attachments
- Priority and classification levels

**Chronological Timeline Builder**
- Create custom investigation timelines
- Link entries to events, zones, and entities
- Duration-based and point-in-time entries
- Verification workflow for timeline entries

**Real-time Alert System**
- Configurable alerts for events, zones, and thresholds
- Multiple notification channels (email, webhook, push)
- Alert logging and trigger tracking
- Immediate, daily, or weekly frequency options

**Report Generation**
- Generate event summaries, equipment loss reports, and timelines
- Multiple output formats (PDF, HTML, DOCX, CSV)
- Configurable sections and filters
- Public/private report visibility

**Saved Searches**
- Save frequently used search filters
- Notifications for new matching content
- Default search per entity type

**Data Sources Configuration**
- Configure external data sources (ADS-B, AIS, satellite, social, news)
- Rate limiting and sync tracking
- Credential management with encryption

**Customizable Actor Emojis (Admin Panel)**
- Admin-configurable emojis for each actor type
- Settings for: countries, terrorists, militias, PMCs, separatists, rebels, cartels, insurgents, political organizations, international orgs, coalitions, proxies, paramilitaries

### Version 1.1.0

**Role-Based Access Control (RBAC)**
- Added comprehensive permission system with 50+ granular permissions
- Hierarchical role system (Admin, Moderator, Editor, Analyst, Viewer)
- Direct user permission grants and denials
- Time-limited permissions with automatic expiration
- User groups for team-based access management
- Permission caching for high performance

**Event Workflow & Visibility**
- Draft events for work-in-progress content
- Approval workflow with moderator review
- Scheduled publishing for future publication
- Event versioning with full change history
- Visibility controls: public, private, restricted, internal
- Access grants by user, group, or role with expiration

**Extended Equipment Properties**
- Flexible property system with 11 data types
- Property categories (Performance, Armament, Protection, Electronics, etc.)
- Full version history with rollback capability
- Image galleries with primary image selection
- Video embeds (YouTube, Vimeo, Dailymotion)
- External link management with health checking
- Property verification workflow

**Configurable Application**
- Application name configurable during installation
- Customizable branding (logo, favicon)
- Configurable defaults (map center, timezone, locale)
- Admin-editable settings panel

**Actor Emoji Improvements**
- Updated emoji assignments for actor types
- Cartels: 💊 (pill)
- PMCs: 🏴 (black flag)
- Separatists: 🏳️ (white flag)
- Rebels: 🚩 (red flag)
- Terrorists: 🏴‍☠️ (pirate flag)

### Version 1.0.0

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
