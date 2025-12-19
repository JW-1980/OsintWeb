# OsintWeb - Military Conflict Tracking Platform

A comprehensive Open Source Intelligence (OSINT) platform for tracking, analyzing, and documenting military conflicts and geopolitical events. Built for analysts, researchers, journalists, and the OSINT community.

## Overview

OsintWeb provides powerful tools for:
- **Interactive Mapping** - Draw control zones, track territorial changes, add events with a click
- **Military Equipment Database** - Comprehensive catalog of ships, vehicles, aircraft, helicopters, and missile systems
- **Event Documentation** - 24+ event types for tracking combat, equipment losses, sightings, and more
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

### Event Types (24 Templates)
Combat events, equipment status changes, sightings, infrastructure damage, humanitarian incidents, and political developments. Each with specialized fields, media support, and source tracking.

### Timeline System
- Historical playback with date range selection
- Compare map states between dates
- Track all changes over time
- Export timeline as reports

### 15 OSINT-Focused Features
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

## Technology Stack

- **Backend**: Laravel 11+ (PHP 8.2+)
- **Frontend**: Vue.js 3 + TypeScript
- **Database**: PostgreSQL with PostGIS
- **Maps**: Leaflet.js
- **Cache**: Redis
- **Search**: Meilisearch

## Documentation

- [Complete Feature Specification](docs/SPECIFICATION.md) - Detailed development guide
- [Development Guidelines](CLAUDE.md) - Code standards and conventions

## Getting Started

### Prerequisites
- PHP 8.2+
- PostgreSQL 15+ with PostGIS
- Node.js 18+
- Redis 7+
- Composer

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

# Run migrations
php artisan migrate --seed

# Build frontend
npm run build

# Start development server
php artisan serve
```

## Project Structure

```
/app                 # Laravel application
  /Models            # Eloquent models
  /Http/Controllers  # API & web controllers
  /Services          # Business logic
/resources/js        # Vue.js frontend
  /components        # Vue components
  /composables       # Vue composables
  /stores            # Pinia stores
/database
  /migrations        # Database migrations
  /seeders           # Initial data
/docs                # Documentation
/tests               # Feature & unit tests
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
