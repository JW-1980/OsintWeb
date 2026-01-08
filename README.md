# OsintWeb - The Future of Conflict Intelligence

## Turn Chaos into Clarity. Master the Battlefield of Information.

In an era of rapid geopolitical shifts and information overload, understanding the true state of global conflicts is not just an advantage—it's a necessity. From verified equipment losses to real-time territorial shifts, the difference between noise and intelligence is the tool you use to analyze it.

## 👁️ Attention: See What Others Miss

**OsintWeb** is the premier Open Source Intelligence platform designed for the modern analyst. Whether you are tracking the movement of naval carrier groups, verifying the destruction of armored vehicles, or mapping the fluctuating frontlines of asymmetric warfare, OsintWeb brings every data point into a single, cohesive operational picture.

Stop wrestling with spreadsheets and disjointed maps. Start seeing the conflict as it truly is.

## 🧠 Interest: Why OsintWeb?

Intelligence is only as good as its organization. OsintWeb transforms raw reports into verifiable, structured intelligence. It doesn't just show you *where* something happened; it tells you *who* did it, *what* was used, and *how* it impacts the broader conflict.

Imagine a system that:
*   Automatically connects an event to the specific military unit involved.
*   Visualizes the historical progression of a frontline with a simple slider.
*   Lets you attribute attacks to state actors or non-state militias with granular precision.
*   Empowers your community to crowdsource verification while maintaining a rigorous audit trail.

This is not just a map. It's a living history of the conflict.

## 💡 Desire: Extensive Feature Suite

Unlock a comprehensive arsenal of analysis tools designed for depth and precision.

### 🗺️ Interactive & Satellite Mapping
Dominate the visual landscape. Our advanced mapping engine allows you to draw complex control zones, color-coded by faction, to visualize territorial control instantly. Toggle between high-resolution satellite imagery and topographic maps to analyze terrain features. The dynamic legend adapts in real-time, ensuring your reports are always clear and professional. Export your operational picture as high-resolution (300 DPI) images for briefings and publications.

### 🚜 Military Equipment Database
Access a military encyclopedia at your fingertips. The platform comes pre-loaded with over **140 detailed equipment profiles**, ranging from *Gerald R. Ford-class* aircraft carriers to *T-90M* main battle tanks and *Switchblade* drones. Each entry tracks critical specifications like range, armament, and dimensions. More importantly, it tracks **inventory and attrition**—allowing you to monitor verified losses and remaining stock for every country in a conflict.

### 💥 Event Documentation & Forensics
Document the reality of war with precision. Utilize **49+ specialized event templates** covering everything from airstrikes and naval engagements to cyber attacks and humanitarian crises. Every event supports rich media attachments—images, videos, and documents—creating a verifiable chain of evidence. Link specific equipment losses to events to build a forensic record of the battlefield.

### 🕵️ Advanced Actor Attribution
Modern warfare is complex. OsintWeb creates a clear web of attribution, tracking **197 state actors** alongside hundreds of non-state groups, PMCs (like Wagner), militias, and terrorist organizations. Our smart system understands alliances and proxies, allowing you to attribute actions correctly—whether it's a state army or a shadow group. Real-time fuzzy search ensures you never miss a connection.

### ⏱️ Temporal Analysis & Time-Travel
History is a sequence of events. Our **Timeline Analysis** tools let you scrub through time, watching frontlines shift and battles unfold day-by-day. Use the **Chronological Builder** to construct custom investigation timelines, linking disparate events into a cohesive narrative to understand the cause and effect of military operations.

### 📝 Intelligence Reporting & Publishing
Turn analysis into influence. The built-in **CMS** allows you to publish deep-dive situation reports, daily briefings, and investigative articles. With support for premium content gates, SEO optimization, and rich media embedding, your analysis reaches the right audience with maximum impact.

### 🔒 Enterprise-Grade Security & RBAC
Trust is paramount. Protect your intelligence with **Role-Based Access Control (RBAC)**. Assign granular permissions (over 50 types) to analysts, moderators, and viewers. Every action is logged in a cryptographic **Audit Trail**, ensuring data integrity and accountability. The system includes full **GDPR compliance** tools, ensuring you meet privacy standards while managing sensitive data.

### 🏆 Achievement & Reputation System
Build a community of trusted analysts. The configurable **Achievement System** rewards high-quality contributions with medals, titles, and ranks. Gamify the verification process to encourage engagement and recognize your most valuable contributors.

## 🎬 Action: Deploy Your Intelligence Platform Today

Ready to start tracking? You can deploy a fully functional demonstration environment in minutes.

### 🚀 Quick Start (Local Demo)

See OsintWeb in action immediately with our single-command deployment. This will set up the entire stack and populate it with mock conflict data so you can explore the features right away.

*(Requires Docker)*

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

Then visit `http://localhost:8000`. The installation wizard will guide you through database configuration, admin account creation, and system settings.

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
# ...

# Create database & Run migrations
php artisan migrate --seed

# Build frontend
npm run build

# Start server
php artisan serve
```

---

*OsintWeb is built for the community. Join us in bringing transparency to global conflicts.*
